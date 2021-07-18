<?php

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * WooCommerce API Manager Product Manager Admin Class
 *
 *
 * @since       1.0.0
 *
 * @author      Todd Lahman LLC
 * @copyright   Copyright (c) Todd Lahman LLC
 * @package     WooCommerce API Manager/Product Admin
 */

/**
 * WC_API_Manager_Product_Admin class.
 */
class WC_AM_Product_Admin {

	/**
	 * @var array
	 */
	private $product_fields = array();

	/**
	 * @var null
	 */
	private static $_instance = null;

	/**
	 * @static
	 * @return \WC_AM_Product_Admin
	 */
	public static function instance() {

		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	private function __construct() {
		/**
		 * Prevent API product associated with orders from being deleted, and other harmful actions.
		 *
		 * @since 2.0
		 */
		// Prevent users from deleting API products. It causes too many problems with WooCommerce and other plugins.
		add_filter( 'user_has_cap', array( $this, 'user_can_not_delete_api_product' ), 10, 3 );
		// Make sure API products in the trash can be restored.
		add_filter( 'post_row_actions', array( $this, 'api_row_actions' ), 10, 2 );
		// Remove the "Delete Permanently" bulk action on the Edit Products screen.
		add_filter( 'bulk_actions-edit-product', array( $this, 'api_bulk_actions' ), 10 );
		// Do not allow API products to be automatically purged on the 'wp_scheduled_delete' hook.
		add_action( 'wp_scheduled_delete', array( $this, 'prevent_scheduled_deletion' ), 9 );
		// Trash variations instead of deleting them to prevent headaches from deleted products.
		add_action( 'wp_ajax_woocommerce_remove_variation', array( $this, 'remove_variations' ), 9, 2 );
		add_action( 'wp_ajax_woocommerce_remove_variations', array( $this, 'remove_variations' ), 9, 2 );

		add_action( 'woocommerce_product_options_product_type', array( $this, 'is_api' ) );
		add_filter( 'product_type_options', array( $this, 'product_type_options' ) );
		add_action( 'woocommerce_product_write_panel_tabs', array( $this, 'product_write_panel_tab' ) );
		add_action( 'woocommerce_product_data_panels', array( $this, 'data_panel' ) );
		add_action( 'woocommerce_update_product_variation', array( $this, 'save_product' ) );
		add_action( 'woocommerce_process_product_meta_variable', array( $this, 'save_product' ) );
		add_action( 'woocommerce_process_product_meta', array( $this, 'save_product' ) );
		add_action( 'woocommerce_ajax_save_product_variations', array( $this, 'ajax_save_product' ) );
		add_action( 'woocommerce_product_after_variable_attributes', array( $this, 'variable_api_fields' ), 10, 3 );
		// If the writepanel is a variable subscription product
		if ( WCAM()->is_wc_subscriptions_active() ) {
			add_action( 'woocommerce_process_product_meta_variable-subscription', array( $this, 'save_product' ) ); // < wc 2.4
		}
	}

	/**
	 * Do not allow any user to delete a API product if it is associated with an order.
	 *
	 * Those with appropriate capabilities can still trash the product, but they will not be able to permanently
	 * delete the product if it is associated with an order (i.e. been purchased).
	 *
	 * @since 2.0
	 *
	 * @param array $allcaps An array of all the user's capabilities.
	 * @param array $caps    Actual capabilities for meta capability.
	 * @param array $args    Optional parameters passed to has_cap(), typically object ID.
	 *
	 * @return mixed
	 */
	public function user_can_not_delete_api_product( $allcaps, $caps, $args ) {
		global $wpdb;

		if ( isset( $args[ 0 ] ) && in_array( $args[ 0 ], array(
				'delete_post',
				'delete_product'
			) ) && isset( $args[ 2 ] ) && ( ! isset( $_GET[ 'action' ] ) || 'untrash' != $_GET[ 'action' ] ) && 0 === strpos( get_post_type( $args[ 2 ] ), 'product' ) ) {

			$user_id    = $args[ 2 ];
			$post_id    = $args[ 2 ];
			$product    = WC_AM_PRODUCT_DATA_STORE()->get_product_object( $post_id );
			$product_id = ( $product->is_type( 'variable' ) ) ? $product->get_parent_id() : $post_id;
			$is_api     = WC_AM_PRODUCT_DATA_STORE()->is_api_product( $product_id );

			if ( $product !== false && $product->get_status() == 'trash' && $product->is_type( array(
				                                                                                   'simple',
				                                                                                   'variable',
				                                                                                   'subscription',
				                                                                                   'simple-subscription',
				                                                                                   'variable-subscription',
				                                                                                   'subscription_variation'
			                                                                                   ) ) && $is_api ) {
				$product_count = $wpdb->get_var( $wpdb->prepare( "
                    SELECT COUNT(*)
                    FROM `{$wpdb->prefix}woocommerce_order_itemmeta`
                    WHERE `meta_key` = '_product_id'
                    AND `meta_value` = %d
                ", $product_id ) );

				if ( $product_count > 0 ) {
					$allcaps[ $caps[ 0 ] ] = false;
				}
			}
		}

		return $allcaps;
	}

	/**
	 * Make sure the 'untrash' (i.e. "Restore") row action is displayed.
	 *
	 * In @see $this->user_can_not_delete_api_product() we prevent a store manager being able to delete an API product.
	 * However, WooCommerce also uses the `delete_post` capability to check whether to display the 'trash' and 'untrash' row actions.
	 * We want a store manager to be able to trash and untrash API products, so this function adds them again.
	 *
	 * @since 2.0
	 *
	 * @param array   $actions Array of actions that can be performed on the post.
	 * @param WP_Post $post    The post object.
	 *
	 * @return mixed
	 */
	public function api_row_actions( $actions, $post ) {
		global $the_product;

		if ( ! is_object( $the_product ) || ( is_object( $the_product ) && $the_product->get_id() != $post->ID ) ) {
			$the_product = WC_AM_PRODUCT_DATA_STORE()->get_product_object( $post->ID );
		}

		if ( is_object( $the_product ) && ! isset( $actions[ 'untrash' ] ) && $the_product->is_type( array(
			                                                                                             'simple',
			                                                                                             'variable',
			                                                                                             'subscription',
			                                                                                             'simple-subscription',
			                                                                                             'variable-subscription',
			                                                                                             'subscription_variation'
		                                                                                             ) ) ) {
			$post_type_object = get_post_type_object( $post->post_type );

			if ( $post->post_status == 'trash' && current_user_can( $post_type_object->cap->edit_post, $post->ID ) ) {
				$actions[ 'untrash' ] = "<a
				title='" . esc_attr__( 'Restore this item from the Trash', 'woocommerce-api-manager' ) . "'
				href='" . wp_nonce_url( admin_url( sprintf( $post_type_object->_edit_link . '&amp;action=untrash', $post->ID ) ), 'untrash-post_' . $post->ID ) . "'>" . esc_attr__( 'Restore', 'woocommerce-api-manager' ) . '</a>';
			}
		}

		return $actions;
	}

	/**
	 * Remove the "Delete Permanently" action from the bulk actions select element on the Products admin screen.
	 *
	 * Because any API products associated with an order can not be permanently deleted, as a result of
	 *
	 * @see   $this->user_can_not_delete_api_product(), leaving the bulk action in can lead to the store manager
	 * hitting the "You are not allowed to delete this item" brick wall and not being able to continue with the
	 * deletion, or get any more detailed information about which item can't be deleted and why.
	 *
	 * @since 2.0
	 *
	 * @param array $actions
	 *
	 * @return mixed $actions Array of actions that can be performed on the post.
	 */
	public function api_bulk_actions( $actions ) {
		unset( $actions[ 'delete' ] );

		return $actions;
	}

	/**
	 * Hooked to the @see 'wp_scheduled_delete' WP-Cron scheduled task to rename the '_wp_trash_meta_time' meta value
	 * as '_wc_trash_meta_time'. This is the flag used by WordPress to determine which posts should be automatically
	 * purged from the trash. We want to make sure API products are not automatically purged (but still want
	 * to keep a record of when the product was trashed).
	 *
	 * @since 2.0
	 */
	public function prevent_scheduled_deletion() {
		global $wpdb;

		$query = "UPDATE $wpdb->postmeta
					INNER JOIN $wpdb->posts ON $wpdb->postmeta.post_id = $wpdb->posts.ID
					SET $wpdb->postmeta.meta_key = '_wc_trash_meta_time'
					WHERE $wpdb->postmeta.meta_key = '_wp_trash_meta_time'
					AND $wpdb->posts.post_type IN ( 'product', 'product_variation')
					AND $wpdb->posts.post_status = 'trash'
                ";

		$wpdb->query( $query );
	}

	/**
	 * Trash API variations - don't delete them permanently.
	 *
	 * This is hooked to 'wp_ajax_woocommerce_remove_variation' & 'wp_ajax_woocommerce_remove_variations'
	 * before WooCommerce's WC_AJAX::remove_variation() or WC_AJAX::remove_variations() functions are run.
	 * The WooCommerce functions will still run after this, but if the variation is an API product, the
	 * request will either terminate or in the case of bulk deleting, the variation's ID will be removed
	 * from the $_POST.
	 *
	 * @since 2.0
	 */
	public function remove_variations() {
		if ( isset( $_POST[ 'variation_id' ] ) ) { // removing single variation
			check_ajax_referer( 'delete-variation', 'security' );

			$variation_ids = array( $_POST[ 'variation_id' ] );
		} else {  // Removing multiple variations.
			check_ajax_referer( 'delete-variations', 'security' );

			$variation_ids = (array) $_POST[ 'variation_ids' ];
		}

		foreach ( $variation_ids as $index => $variation_id ) {
			$variation_post = get_post( $variation_id );

			if ( $variation_post && $variation_post->post_type == 'product_variation' ) {
				$variation_product = WC_AM_PRODUCT_DATA_STORE()->get_product_object( $variation_id );

				if ( ( is_object( $variation_product ) && $variation_product->is_type( 'variation' ) ) || ( is_object( $variation_product ) && $variation_product->is_type( 'subscription_variation' ) ) ) {
					wp_trash_post( $variation_id );

					// Prevent WooCommerce from deleting the variation.
					if ( isset( $_POST[ 'variation_id' ] ) ) {
						die();
					} else {
						unset( $_POST[ 'variation_ids' ][ $index ] );
					}
				}
			}
		}
	}

	/**
	 * Add an API checkbox to the product edit screen.
	 *
	 * @since 1.0
	 *
	 * @param array $options
	 *
	 * @return mixed
	 */
	public function product_type_options( $options ) {
		global $post;

		$product_type = WC_AM_PRODUCT_DATA_STORE()->get_product_type( $post->ID );

		if ( ! in_array( $product_type, array( 'grouped', 'external' ) ) ) {
			$options[ 'is_api' ] = array(
				'id'            => '_is_api',
				'wrapper_class' => 'display_api_checkbox',
				'label'         => esc_html__( 'API', 'woocommerce-api-manager' ),
				'description'   => esc_html__( 'Enable this option if this is a resource that requires the API Manager.', 'woocommerce-api-manager' )
			);
		}

		return $options;
	}

	/**
	 * Render the API checkbox.
	 *
	 * @since 1.0
	 */
	public function is_api() {
		global $post;

		$product_type = WC_AM_PRODUCT_DATA_STORE()->get_product_type( $post->ID );

		if ( ! in_array( $product_type, array( 'grouped', 'external' ) ) ) {
			woocommerce_wp_checkbox( array(
				                         'id'            => '_is_api',
				                         'wrapper_class' => 'display_api_checkbox',
				                         'label'         => esc_html__( 'API', 'woocommerce-api-manager' ),
				                         'description'   => esc_html__( 'Enable this option if this is a resource that requires the API Manager.', 'woocommerce-api-manager' ),
				                         'value'         => WC_AM_PRODUCT_DATA_STORE()->is_api_product( $post->ID ) ? 'yes' : 'no',
			                         ) );
		}
	}

	public function define_fields() {
		if ( empty( $this->product_fields ) ) {
			// Fields
			$this->product_fields = array(
				'start_group',
				array(
					'id'          => '_api_resource_product_id',
					'label'       => esc_html__( 'Product ID', 'woocommerce-api-manager' ),
					'description' => esc_html__( 'Unique ID used to indentify this API resource. Do NOT delete this product or ID.', 'woocommerce-api-manager' ),
					'class'       => 'readonly',
					'type'        => 'text'
				),
				array(
					'id'          => '_api_new_version',
					'label'       => esc_html__( 'Version', 'woocommerce-api-manager' ),
					'description' => esc_html__( 'The software version number.', 'woocommerce-api-manager' ),
					'placeholder' => esc_html__( 'e.g. 1.2.5', 'woocommerce-api-manager' ),
					'class'       => '',
					'type'        => 'text'
				),
				array(
					'id'          => '_api_plugin_url',
					'label'       => esc_html__( 'Page URL', 'woocommerce-api-manager' ),
					'description' => esc_html__( 'The software page URL.', 'woocommerce-api-manager' ),
					'placeholder' => esc_html__( 'http://myplugin.com', 'woocommerce-api-manager' ),
					'class'       => '',
					'type'        => 'text'
				),
				array(
					'id'          => '_api_author',
					'label'       => esc_html__( 'Author', 'woocommerce-api-manager' ),
					'description' => esc_html__( 'The author of the software.', 'woocommerce-api-manager' ),
					'placeholder' => esc_html__( 'Todd Lahman LLC', 'woocommerce-api-manager' ),
					'class'       => '',
					'type'        => 'text'
				),
				array(
					'id'          => '_api_version_required',
					'label'       => esc_html__( 'Version Required', 'woocommerce-api-manager' ),
					'description' => esc_html__( 'Minimum version of platform/framework, such as WordPress, software requires.', 'woocommerce-api-manager' ),
					'placeholder' => esc_html__( 'e.g. 3.6', 'woocommerce-api-manager' ),
					'class'       => '',
					'type'        => 'text'
				),
				array(
					'id'          => '_api_tested_up_to',
					'label'       => esc_html__( 'Version Tested Up To', 'woocommerce-api-manager' ),
					'description' => esc_html__( 'Highest version platform/framework, such as WordPress, software was tested on.', 'woocommerce-api-manager' ),
					'placeholder' => esc_html__( 'e.g. 4.0', 'woocommerce-api-manager' ),
					'class'       => '',
					'type'        => 'text'
				),
				array(
					'id'          => '_api_requires_php',
					'label'       => esc_html__( 'Requires PHP Version', 'woocommerce-api-manager' ),
					'description' => esc_html__( 'Minimum version of PHP software requires.', 'woocommerce-api-manager' ),
					'placeholder' => esc_html__( 'e.g. 7.3', 'woocommerce-api-manager' ),
					'class'       => '',
					'type'        => 'text'
				),
				array(
					'id'          => '_api_last_updated',
					'label'       => esc_html__( 'Last Updated', 'woocommerce-api-manager' ),
					'description' => esc_html__( 'When the software was last updated.', 'woocommerce-api-manager' ),
					'placeholder' => esc_html__( 'YYYY-MM-DD', 'woocommerce-api-manager' ),
					'class'       => '',
					'type'        => 'text'
				),
				array(
					'id'          => '_api_upgrade_notice',
					'label'       => esc_html__( 'Upgrade Notice', 'woocommerce-api-manager' ),
					'description' => esc_html__( 'A notice displayed when an update is available.', 'woocommerce-api-manager' ),
					'placeholder' => esc_html__( 'Optional', 'woocommerce-api-manager' ),
					'class'       => '',
					'type'        => 'text'
				),
				'end_group',
			);
		}
	}

	/**
	 * adds a new tab to the product interface
	 */
	public function product_write_panel_tab() {
		global $post;

		$product_type = WC_AM_PRODUCT_DATA_STORE()->get_product_type( $post->ID );

		if ( ! in_array( $product_type, array( 'grouped', 'external' ) ) ) {
			?>
            <li class="api_tab show_if_api"><a href="#api_data"><span><?php esc_html_e( 'API', 'woocommerce-api-manager' ); ?></span></a></li>
			<?php
		}
	}

	/**
	 * adds the panel to the product interface
	 */
	public function data_panel() {
		global $post;

		$product_type = WC_AM_PRODUCT_DATA_STORE()->get_product_type( $post->ID );

		if ( ! in_array( $product_type, array( 'grouped', 'external' ) ) ) {
			// If the _api_resource_product_id meta value is missing on the product, add it now.
			WC_AM_PRODUCT_DATA_STORE()->update_missing_api_resource_product_id( $post->ID, $post->ID );

			$this->define_fields();
			?>

            <div id="api_data" class="panel woocommerce_options_panel">
                <div id="api_chbx" class="options_group show_if_variable" style="padding:2em">
                    <strong class="attribute_name"><?php esc_html_e( 'All data below is copied to all variations, unless the checkbox is selected per variation labeled "Set API options for this variable product only." Activation limit, not listed below, must be set per variation.', 'woocommerce-api-manager' ) ?></strong>
                </div>

				<?php

				foreach ( $this->product_fields as $field ) {
					if ( ! is_array( $field ) ) {
						if ( $field == 'start_group' ) {
							echo '<div class="options_group">';
						} elseif ( $field == 'end_group' ) {
							$act_parent = WC_AM_PRODUCT_DATA_STORE()->get_api_activations( $post->ID );

							woocommerce_wp_checkbox( array(
								                         'id'            => '_api_activations_unlimited',
								                         'wrapper_class' => 'show_if_simple',
								                         'label'         => esc_html__( 'Unlimited Activations', 'woocommerce-api-manager' ),
								                         'desc_tip'      => true,
								                         'description'   => esc_html__( 'Enable for unlimited number of activations.', 'woocommerce-api-manager' ),
								                         'value'         => WC_AM_PRODUCT_DATA_STORE()->is_api_product_unlimited_activations( $post->ID ) ? 'yes' : 'no',
							                         ) );

							woocommerce_wp_text_input( array(
								                           'id'                => '_api_activations',
								                           'name'              => '_api_activations',
								                           'class'             => 'wc_api_activations',
								                           'wrapper_class'     => '_api_activations_field show_if_simple',
								                           'label'             => esc_html__( 'Activation Limit', 'woocommerce-api-manager' ),
								                           'placeholder'       => esc_html__( '1', 'woocommerce-api-manager' ),
								                           'value'             => ( ! empty( $act_parent ) && is_numeric( $act_parent ) ) ? absint( $act_parent ) : '',
								                           'type'              => 'number',
								                           'description'       => esc_html__( 'Limits the number of activations. Default is 1 when left empty. If the product activation limit is increased, all API resources with the product will have the activation limit increased to match.', 'woocommerce-api-manager' ),
								                           'desc_tip'          => true,
								                           'custom_attributes' => array(
									                           'step' => '1',
									                           'min'  => '1',
								                           ),
							                           ) );

							$is_wc_sub = false;

							if ( WCAM()->get_wc_subs_exist() ) {
								$is_wc_sub = WCAM()->is_wc_subscriptions_active() && WC_AM_SUBSCRIPTION()->is_wc_subscription( $post->ID );
							}

							if ( ! in_array( $product_type, array(
									'subscription',
									'simple-subscription',
									'variable-subscription',
									'subscription_variation'
								) ) && ! $is_wc_sub ) {
								$access_expires = WC_AM_PRODUCT_DATA_STORE()->get_api_access_expires( $post->ID );

								woocommerce_wp_text_input( array(
									                           'id'                => '_access_expires',
									                           'name'              => '_access_expires',
									                           'class'             => 'wc_api_access_expires',
									                           'wrapper_class'     => '_access_expires_field show_if_simple',
									                           'label'             => esc_html__( 'API Access Expires', 'woocommerce-api-manager' ),
									                           'placeholder'       => esc_html__( 'Never', 'woocommerce-api-manager' ),
									                           'value'             => ( ! empty( $access_expires ) && is_numeric( $access_expires ) ) ? absint( $access_expires ) : '',
									                           'type'              => 'number',
									                           'description'       => esc_html__( 'Enter the number of days before API access expires, or leave blank to never expire.', 'woocommerce-api-manager' ),
									                           'desc_tip'          => true,
									                           'custom_attributes' => array(
										                           'step' => '1',
										                           'min'  => '1',
									                           ),
								                           ) );
							}

							echo '</div>';
						}
					} elseif ( in_array( $product_type, array(
							'variable',
							'variable-subscription'
						) ) && ( $field[ 'id' ] == '_api_resource_product_id' || $field[ 'id' ] == '_api_resource_title' ) ) {
						// Do not display the Product ID or Software Title on variable parent product API tab.
						continue;
					} elseif ( $field[ 'id' ] == '_api_resource_title' && empty( WC_AM_PRODUCT_DATA_STORE()->get_meta( $post->ID, '_api_resource_title' ) ) ) {
						// If Software Title is empty, do not display.
						continue;
					} else {
						$func = 'woocommerce_wp_' . $field[ 'type' ] . '_input';

						if ( function_exists( $func ) ) {
							$func( $field );
						}
					}
				}

				echo '<div class="options_group">';

				echo '<p class="form-field ' . esc_attr( '_api_product_documentation' ) . '_field ' . esc_attr( '_api_product_documentation_field' ) . '"><label for="' . esc_attr( '_api_product_documentation' ) . '">' . esc_html__( 'Documentation', 'woocommerce-api-manager' ) . '</label>';
				$doc       = absint( WC_AM_PRODUCT_DATA_STORE()->get_meta( $post->ID, '_api_product_documentation' ) );
				$desc_args = array(
					'name'             => '_api_product_documentation',
					'id'               => '_api_product_documentation',
					'sort_column'      => 'menu_order',
					'sort_order'       => 'ASC',
					'show_option_none' => ' ',
					'echo'             => false,
					'selected'         => absint( WC_AM_PRODUCT_DATA_STORE()->get_meta( $post->ID, '_api_product_documentation' ) )
				);

				if ( ! empty( $doc ) ) {
					echo '&nbsp;<a href="' . esc_url( admin_url( 'post.php?post=' . $doc ) ) . '&action=edit" target="_blank" ><span style="text-decoration:none;" class="dashicons dashicons-admin-links"></span></a>';
				}

				echo str_replace( ' id=', " data-placeholder='" . esc_html__( 'Select a page &hellip;', 'woocommerce-api-manager' ) . "' data-width='50%' class='wc-am-select' id=", wp_dropdown_pages( $desc_args ) ) . '<span class="description">' . esc_html__( 'Documentation link for My Account.', 'woocommerce-api-manager' ) . '</span>';
				echo '</p>';

				echo '<p class="form-field ' . esc_attr( '_api_description' ) . '_field ' . esc_attr( '_api_description_field' ) . '"><label for="' . esc_attr( '_api_description' ) . '">' . esc_html__( 'Description', 'woocommerce-api-manager' ) . '</label>';
				$doc       = absint( WC_AM_PRODUCT_DATA_STORE()->get_meta( $post->ID, '_api_description' ) );
				$desc_args = array(
					'name'             => '_api_description',
					'id'               => '_api_description',
					'sort_column'      => 'menu_order',
					'sort_order'       => 'ASC',
					'show_option_none' => ' ',
					'echo'             => false,
					'selected'         => absint( WC_AM_PRODUCT_DATA_STORE()->get_meta( $post->ID, '_api_description' ) )
				);

				if ( ! empty( $doc ) ) {
					echo '&nbsp;<a href="' . esc_url( admin_url( 'post.php?post=' . $doc ) ) . '&action=edit" target="_blank" ><span style="text-decoration:none;" class="dashicons dashicons-admin-links"></span></a>';
				}

				echo str_replace( ' id=', " data-placeholder='" . esc_html__( 'Select a page &hellip;', 'woocommerce-api-manager' ) . "' data-width='50%' class='wc-am-select' id=", wp_dropdown_pages( $desc_args ) ) . '<span class="description">' . esc_html__( 'A description of the software.', 'woocommerce-api-manager' ) . '</span>';
				echo '</p>';

				echo '<p class="form-field ' . esc_attr( '_api_changelog' ) . '_field ' . esc_attr( '_api_changelog_field' ) . '"><label for="' . esc_attr( '_api_changelog' ) . '">' . esc_html__( 'Changelog', 'woocommerce-api-manager' ) . '</label>';
				$doc       = absint( WC_AM_PRODUCT_DATA_STORE()->get_meta( $post->ID, '_api_changelog' ) );
				$desc_args = array(
					'name'             => '_api_changelog',
					'id'               => '_api_changelog',
					'sort_column'      => 'menu_order',
					'sort_order'       => 'ASC',
					'show_option_none' => ' ',
					'echo'             => false,
					'selected'         => absint( WC_AM_PRODUCT_DATA_STORE()->get_meta( $post->ID, '_api_changelog' ) )
				);

				if ( ! empty( $doc ) ) {
					echo '&nbsp;<a href="' . esc_url( admin_url( 'post.php?post=' . $doc ) ) . '&action=edit" target="_blank" ><span style="text-decoration:none;" class="dashicons dashicons-admin-links"></span></a>';
				}

				echo str_replace( ' id=', " data-placeholder='" . esc_html__( 'Select a page &hellip;', 'woocommerce-api-manager' ) . "' data-width='50%' class='wc-am-select' id=", wp_dropdown_pages( $desc_args ) ) . '<span class="description">' . esc_html__( 'Changes in the software.', 'woocommerce-api-manager' ) . '</span>';
				echo '</p>';

				echo '<p class="form-field ' . esc_attr( '_api_installation' ) . '_field ' . esc_attr( '_api_installation_field' ) . '"><label for="' . esc_attr( '_api_installation' ) . '">' . esc_html__( 'Installation', 'woocommerce-api-manager' ) . '</label>';
				$doc       = absint( WC_AM_PRODUCT_DATA_STORE()->get_meta( $post->ID, '_api_installation' ) );
				$desc_args = array(
					'name'             => '_api_installation',
					'id'               => '_api_installation',
					'sort_column'      => 'menu_order',
					'sort_order'       => 'ASC',
					'show_option_none' => ' ',
					'echo'             => false,
					'selected'         => absint( WC_AM_PRODUCT_DATA_STORE()->get_meta( $post->ID, '_api_installation' ) )
				);

				if ( ! empty( $doc ) ) {
					echo '&nbsp;<a href="' . esc_url( admin_url( 'post.php?post=' . $doc ) ) . '&action=edit" target="_blank" ><span style="text-decoration:none;" class="dashicons dashicons-admin-links"></span></a>';
				}

				echo str_replace( ' id=', " data-placeholder='" . esc_html__( 'Select a page &hellip;', 'woocommerce-api-manager' ) . "' data-width='50%' class='wc-am-select' id=", wp_dropdown_pages( $desc_args ) ) . '<span class="description">' . esc_html__( 'How to install the software.', 'woocommerce-api-manager' ) . '</span>';
				echo '</p>';

				echo '<p class="form-field ' . esc_attr( '_api_faq' ) . '_field ' . esc_attr( '_api_faq_field' ) . '"><label for="' . esc_attr( '_api_faq' ) . '">' . esc_html__( 'FAQ', 'woocommerce-api-manager' ) . '</label>';
				$doc       = absint( WC_AM_PRODUCT_DATA_STORE()->get_meta( $post->ID, '_api_faq' ) );
				$desc_args = array(
					'name'             => '_api_faq',
					'id'               => '_api_faq',
					'sort_column'      => 'menu_order',
					'sort_order'       => 'ASC',
					'show_option_none' => ' ',
					'echo'             => false,
					'selected'         => absint( WC_AM_PRODUCT_DATA_STORE()->get_meta( $post->ID, '_api_faq' ) )
				);

				if ( ! empty( $doc ) ) {
					echo '&nbsp;<a href="' . esc_url( admin_url( 'post.php?post=' . $doc ) ) . '&action=edit" target="_blank" ><span style="text-decoration:none;" class="dashicons dashicons-admin-links"></span></a>';
				}

				echo str_replace( ' id=', " data-placeholder='" . esc_html__( 'Select a page &hellip;', 'woocommerce-api-manager' ) . "' data-width='50%' class='wc-am-select' id=", wp_dropdown_pages( $desc_args ) ) . '<span class="description">' . esc_html__( 'Frequently Asked Questions.', 'woocommerce-api-manager' ) . '</span>';
				echo '</p>';

				echo '<p class="form-field ' . esc_attr( '_api_screenshots' ) . '_field ' . esc_attr( '_api_screenshots_field' ) . '"><label for="' . esc_attr( '_api_screenshots' ) . '">' . esc_html__( 'Screenshots', 'woocommerce-api-manager' ) . '</label>';
				$doc       = absint( WC_AM_PRODUCT_DATA_STORE()->get_meta( $post->ID, '_api_screenshots' ) );
				$desc_args = array(
					'name'             => '_api_screenshots',
					'id'               => '_api_screenshots',
					'sort_column'      => 'menu_order',
					'sort_order'       => 'ASC',
					'show_option_none' => ' ',
					'echo'             => false,
					'selected'         => absint( WC_AM_PRODUCT_DATA_STORE()->get_meta( $post->ID, '_api_screenshots' ) )
				);

				if ( ! empty( $doc ) ) {
					echo '&nbsp;<a href="' . esc_url( admin_url( 'post.php?post=' . $doc ) ) . '&action=edit" target="_blank" ><span style="text-decoration:none;" class="dashicons dashicons-admin-links"></span></a>';
				}

				echo str_replace( ' id=', " data-placeholder='" . esc_html__( 'Select a page &hellip;', 'woocommerce-api-manager' ) . "' data-width='50%' class='wc-am-select' id=", wp_dropdown_pages( $desc_args ) ) . '<span class="description">' . esc_html__( 'Screenshots of the software.', 'woocommerce-api-manager' ) . '</span>';
				echo '</p>';

				echo '<p class="form-field ' . esc_attr( '_api_other_notes' ) . '_field ' . esc_attr( '_api_other_notes_field' ) . '"><label for="' . esc_attr( '_api_other_notes' ) . '">' . esc_html__( 'Other Notes', 'woocommerce-api-manager' ) . '</label>';
				$doc       = absint( WC_AM_PRODUCT_DATA_STORE()->get_meta( $post->ID, '_api_other_notes' ) );
				$desc_args = array(
					'name'             => '_api_other_notes',
					'id'               => '_api_other_notes',
					'sort_column'      => 'menu_order',
					'sort_order'       => 'ASC',
					'show_option_none' => ' ',
					'echo'             => false,
					'selected'         => absint( WC_AM_PRODUCT_DATA_STORE()->get_meta( $post->ID, '_api_other_notes' ) )
				);

				if ( ! empty( $doc ) ) {
					echo '&nbsp;<a href="' . esc_url( admin_url( 'post.php?post=' . $doc ) ) . '&action=edit" target="_blank" ><span style="text-decoration:none;" class="dashicons dashicons-admin-links"></span></a>';
				}

				echo str_replace( ' id=', " data-placeholder='" . esc_html__( 'Select a page &hellip;', 'woocommerce-api-manager' ) . "' data-width='50%' class='wc-am-select' id=", wp_dropdown_pages( $desc_args ) ) . '<span class="description">' . esc_html__( 'Other details or special facts.', 'woocommerce-api-manager' ) . '</span>';
				echo '</p>';

				echo '</div>';

				?>
            </div>

			<?php

			WCAM()->wc_print_js( "
			/* Only display API Tab if API checkbox is checked */
			jQuery( 'input#_is_api' ).change( function(){
				jQuery( '.show_if_api' ).hide();
				if ( jQuery( 'select#product-type' ).val() == 'simple' || jQuery( 'select#product-type' ).val() == 'variable' || jQuery( 'select#product-type' ).val() == 'subscription' || jQuery( 'select#product-type' ).val() == 'variable-subscription' ) {
					if ( jQuery( '#_is_api' ).is( ':checked' ) ) {
						jQuery( '.show_if_api' ).show();
					} else {
						if ( jQuery( '.api_tab' ).is( '.active' ) ) jQuery( 'ul.tabs li:visible' ).eq(0).find( 'a' ).click();
					}
				}
			}).change();

            /* Hide Activation Limit if set for Unlimited Activations */

			jQuery('input#_api_activations_unlimited').change(function() {
			    if ( jQuery( 'select#product-type' ).val() == 'simple' || jQuery( 'select#product-type' ).val() == 'subscription' ) {
                    if (jQuery('input#_api_activations_unlimited').is(':checked')) {
                        jQuery('._api_activations_field').hide();
                    } else {
                        jQuery('._api_activations_field').show();
                    }
				}
			});

			jQuery('input#_api_activations_unlimited').trigger('change');

			/* Only display API checkbox for certain product types */
			jQuery( 'select#product-type' ).change( function(){
				jQuery( '.display_api_checkbox' ).hide();
				if ( jQuery( 'select#product-type' ).val() == 'simple' || jQuery( 'select#product-type' ).val() == 'variable' || jQuery( 'select#product-type' ).val() == 'subscription' || jQuery( 'select#product-type' ).val() == 'variable-subscription' ) {
					jQuery( '.display_api_checkbox' ).show();
				} else {
					jQuery( '.display_api_checkbox' ).hide();
				}
			}).change();

			/* Datepicker for API tab */
			jQuery( '#_api_last_updated' ).datepicker({
				dateFormat: 'yy-mm-dd',
				numberOfMonths: 1,
				showButtonPanel: true
			});

			// Tooltips
			jQuery('.tips, .help_tip').tipTip({
		    	'attribute' : 'data-tip',
		    	'fadeIn' : 50,
		    	'fadeOut' : 50,
		    	'delay' : 200
		    });

		" );

			?>
            <script type="text/javascript">
                jQuery('input#_is_api').click(function () {
                    alert('<?php echo esc_js( __( 'A checked, and saved, API checkbox CANNOT be unchecked later. This product will remain a permanent API Manager product, and CANNOT be deleted from the store. This product will be made available as an API Resource on existing, and future, purchases.', 'woocommerce-api-manager' ) ); ?>');
                });
            </script>
			<?php

			if ( WCAM()->get_wc_version() >= '3.2' ) :
				WCAM()->wc_print_js( "
				jQuery('select.wc-am-select').selectWoo({allowClear:true});
			" );
			else :
				WCAM()->wc_print_js( "
				jQuery('select.wc-am-select').select2({allowClear:true});
			" );
			endif;
		}
	}

	/**
	 * Writepanel for variable product fields
	 *
	 * @param int    $loop
	 * @param array  $variation_data
	 * @param object $variation
	 */
	public function variable_api_fields( $loop, $variation_data, $variation ) {
		global $thepostid;

		$product_type = WC_AM_PRODUCT_DATA_STORE()->get_product_type( $variation->ID );

		// When called via Ajax
		if ( ! function_exists( 'woocommerce_wp_text_input' ) ) {
			require_once( WC()->plugin_path() . '/admin/post-types/writepanels/writepanels-init.php' );
		}

		if ( ! isset( $thepostid ) ) {
			$thepostid = $variation->post_parent;
		}

		// If the _api_resource_product_id meta value is missing on the product, add it now.
		if ( ! empty( $thepostid ) ) {
			WC_AM_PRODUCT_DATA_STORE()->update_missing_api_resource_product_id( $variation->ID, $thepostid );
		} else {
			WC_AM_PRODUCT_DATA_STORE()->update_missing_api_resource_product_id( $variation->ID );
		}
		?>

        <style>
            .woocommerce-help-tip {
                color: #666;
                display: inline-block;
                font-size: 1.5em;
                font-style: normal;
                height: 16px;
                line-height: 16px;
                position: relative;
                vertical-align: middle;
                width: 16px;
            }
        </style>

        <div class="show_if_api">
            <p class="api_var_heading form-row form-row-full">
				<?php esc_html_e( 'API Manager Options', 'woocommerce-api-manager' ); ?>
            </p>

            <div>
                <p class="form-row">
                    <label>
					<span><input type="checkbox" class="am_checkbox api_global_data_set_var<?php echo $loop; ?>"
                                 name="_api_data_is_global_override[<?php echo $loop; ?>]"
                                 value='yes' <?php checked( sanitize_text_field( WC_AM_PRODUCT_DATA_STORE()->get_meta( $variation->ID, '_api_data_is_global_override' ) ), 'yes' ); ?> /> <?php esc_html_e( 'Set API options for this variable product only.', 'woocommerce-api-manager' ); ?>
                        <span class="woocommerce-help-tip"
                              data-tip="<?php esc_html_e( 'The information set here will only apply to this variable product.', 'woocommerce-api-manager' ); ?>"
						   </span></span>
                    </label>
                </p>
                <p class="form-row">
                    <label>
					<span><input type="checkbox" id="_api_activations_unlimited_var<?php echo $loop; ?>"
                                 name="_api_activations_unlimited_var[<?php echo $loop; ?>]"
                                 value='yes' <?php checked( sanitize_text_field( WC_AM_PRODUCT_DATA_STORE()->get_meta( $variation->ID, '_api_activations_unlimited' ) ), 'yes' ); ?> /> <?php esc_html_e( 'Unlimited Activations', 'woocommerce-api-manager' ); ?>
                        <span class="woocommerce-help-tip"
                              data-tip="<?php esc_html_e( 'Enable for unlimited number of activations.', 'woocommerce-api-manager' ); ?>"
						   </span></span>
                    </label>
                </p>
            </div>
            <div id="api_override_chkbx<?php echo $loop; ?>">
                <p class="form-row form-row-first" id="_api_activations_var<?php echo $loop; ?>">
                    <label><?php esc_html_e( 'Activation Limit:', 'woocommerce-api-manager' ); ?> <span class="woocommerce-help-tip"
                                                                                                        data-tip="<?php esc_html_e( 'Limits the number of activations. Default is 1 when left empty. If the product activation limit is increased, all API resources with the product will have the activation limit increased to match.', 'woocommerce-api-manager' ); ?>"
                        </span></label>
                    <input type="number" name="_api_activations_var[<?php echo $loop; ?>]" step="1" min="1"
                           value="<?php echo esc_attr( WC_AM_PRODUCT_DATA_STORE()->get_meta( $variation->ID, '_api_activations' ) ); ?>"
                           placeholder="<?php esc_html_e( '1', 'woocommerce-api-manager' ); ?>"/>
                </p>

                <p class="form-row form-row-last">
                    <label for="api_resources_product_id<?php echo $loop; ?>"><?php esc_html_e( 'Product ID:', 'woocommerce-api-manager' ); ?> <span
                                class="woocommerce-help-tip"
                                data-tip="<?php esc_html_e( 'Unique ID used to indentify this API resource. Do NOT delete this product.', 'woocommerce-api-manager' ); ?>"
                        </span></label>
                    <input type="text" name="_api_resource_product_id[<?php echo $loop; ?>]"
                           id="api_resources_product_id<?php echo $loop; ?>"
                           value="<?php echo esc_attr( WC_AM_PRODUCT_DATA_STORE()->get_meta( $variation->ID, '_api_resource_product_id' ) ); ?>"
                           readonly/>
                </p>

                <p class="form-row form-row-first show_if_api_global_data_set_var<?php echo $loop; ?> api_global_data_set_hide_onload_var<?php echo $loop; ?>">
                    <label><?php esc_html_e( 'Upgrade Notice:', 'woocommerce-api-manager' ); ?> <span class="woocommerce-help-tip"
                                                                                                      data-tip="<?php esc_html_e( 'A notice displayed when an update is available.', 'woocommerce-api-manager' ); ?>"
                        </span></label>
                    <input type="text" name="_api_upgrade_notice_var[<?php echo $loop; ?>]"
                           value="<?php echo esc_html( WC_AM_PRODUCT_DATA_STORE()->get_meta( $variation->ID, '_api_upgrade_notice' ) ); ?>"
                           placeholder="<?php esc_html_e( 'Optional', 'woocommerce-api-manager' ); ?>"/>
                </p>

	            <?php

	            $is_wc_sub = false;

	            if ( WCAM()->get_wc_subs_exist() ) {
		            $is_wc_sub = WCAM()->is_wc_subscriptions_active() && WC_AM_SUBSCRIPTION()->is_wc_subscription( $variation->ID );
	            }

	            if ( ! in_array( $product_type, array(
			            'subscription',
			            'simple-subscription',
			            'variable-subscription',
			            'subscription_variation'
		            ) ) && ! $is_wc_sub ) :
		            $expires = WC_AM_PRODUCT_DATA_STORE()->get_api_access_expires( $variation->ID );
		            ?>
                    <p class="form-row form-row-last show_if_api_global_data_set_var<?php echo $loop; ?> api_global_data_set_hide_onload_var<?php echo $loop; ?>">
                        <label><?php esc_html_e( 'API Access Expires:', 'woocommerce-api-manager' ); ?> <span class="woocommerce-help-tip"
                                                                                                              data-tip="<?php esc_html_e( 'Enter the number of days before API access expires, or leave blank to never expire.', 'woocommerce-api-manager' ); ?>"
                            </span></label>
                        <input type="number" name="_access_expires_var[<?php echo $loop; ?>]" step="1" min="1"
                               value="<?php ! empty( $expires ) ? esc_attr_e( $expires ) : esc_html_e( 'Never', 'woocommerce-api-manager' ); ?>"
                               placeholder="<?php esc_html_e( 'Never', 'woocommerce-api-manager' ); ?>"/>
                    </p>
	            <?php
	            endif;
	            ?>

                <p class="form-row form-row-first show_if_api_global_data_set_var<?php echo $loop; ?> api_global_data_set_hide_onload_var<?php echo $loop; ?>">
                    <label><?php esc_html_e( 'Version:', 'woocommerce-api-manager' ); ?> <span class="woocommerce-help-tip"
                                                                                               data-tip="<?php esc_html_e( 'The current software version number, which triggers an update notification if the customer has an older version installed.', 'woocommerce-api-manager' ); ?>"
                        </span></label>
                    <input type="text" name="_api_new_version_var[<?php echo $loop; ?>]"
                           value="<?php echo esc_attr( WC_AM_PRODUCT_DATA_STORE()->get_meta( $variation->ID, '_api_new_version' ) ); ?>"
                           placeholder="<?php esc_html_e( 'e.g. 1.2.5', 'woocommerce-api-manager' ); ?>"/>
                </p>

                <p class="form-row form-row-last show_if_api_global_data_set_var<?php echo $loop; ?> api_global_data_set_hide_onload_var<?php echo $loop; ?>">
                    <label><?php esc_html_e( 'Version Required:', 'woocommerce-api-manager' ); ?> <span class="woocommerce-help-tip"
                                                                                                           data-tip="<?php esc_html_e( 'The minimum version of platform/framework, such as WordPress, required to run the software.', 'woocommerce-api-manager' ); ?>"
                        </span></label>
                    <input type="text" name="_api_version_required_var[<?php echo $loop; ?>]"
                           value="<?php echo esc_attr( WC_AM_PRODUCT_DATA_STORE()->get_meta( $variation->ID, '_api_version_required' ) ); ?>"
                           placeholder="<?php esc_html_e( 'e.g. 3.3', 'woocommerce-api-manager' ); ?>"/>
                </p>

                <p class="form-row form-row-first show_if_api_global_data_set_var<?php echo $loop; ?> api_global_data_set_hide_onload_var<?php echo $loop; ?>">
                    <label><?php esc_html_e( 'Version Tested Up To:', 'woocommerce-api-manager' ); ?> <span class="woocommerce-help-tip"
                                                                                                               data-tip="<?php esc_html_e( 'The highest version of platform/framework, such as WordPress, the software has been tested on.', 'woocommerce-api-manager' ); ?>"
                        </span></label>
                    <input type="text" name="_api_tested_up_to_var[<?php echo $loop; ?>]"
                           value="<?php echo esc_attr( WC_AM_PRODUCT_DATA_STORE()->get_meta( $variation->ID, '_api_tested_up_to' ) ); ?>"
                           placeholder="<?php esc_html_e( 'e.g. 4.0', 'woocommerce-api-manager' ); ?>"/>
                </p>

                <p class="form-row form-row-last show_if_api_global_data_set_var<?php echo $loop; ?> api_global_data_set_hide_onload_var<?php echo $loop; ?>">
                    <label><?php esc_html_e( 'Requires PHP Version:', 'woocommerce-api-manager' ); ?> <span class="woocommerce-help-tip"
                                                                                                            data-tip="<?php esc_html_e( 'Minimum version of PHP software requires.', 'woocommerce-api-manager' ); ?>"
                        </span></label>
                    <input type="text" name="_api_requires_php_var[<?php echo $loop; ?>]"
                           value="<?php echo esc_attr( WC_AM_PRODUCT_DATA_STORE()->get_meta( $variation->ID, '_api_requires_php' ) ); ?>"
                           placeholder="<?php esc_html_e( 'e.g. 7.3', 'woocommerce-api-manager' ); ?>"/>
                </p>

                <p class="form-row form-row-first show_if_api_global_data_set_var<?php echo $loop; ?> api_global_data_set_hide_onload_var<?php echo $loop; ?>">
                    <label><?php esc_html_e( 'Last Updated:', 'woocommerce-api-manager' ); ?> <span class="woocommerce-help-tip"
                                                                                                    data-tip="<?php esc_html_e( 'The date the software was last updated.', 'woocommerce-api-manager' ); ?>"
                        </span></label>
                    <input type="text" name="_api_last_updated_var[<?php echo $loop; ?>]"
                           value="<?php echo esc_attr( WC_AM_PRODUCT_DATA_STORE()->get_meta( $variation->ID, '_api_last_updated' ) ); ?>"
                           class="wc_api_last_updated_var"
                           placeholder="<?php esc_html_e( 'YYYY-MM-DD', 'woocommerce-api-manager' ); ?>"/>
                </p>

                <p class="form-row form-row-last show_if_api_global_data_set_var<?php echo $loop; ?> api_global_data_set_hide_onload_var<?php echo $loop; ?>">
                    <label><?php esc_html_e( 'Author:', 'woocommerce-api-manager' ); ?> <span class="woocommerce-help-tip"
                                                                                              data-tip="<?php esc_html_e( 'The name of the software author.', 'woocommerce-api-manager' ); ?>"
                        </span></label>
                    <input type="text" name="_api_author_var[<?php echo $loop; ?>]"
                           value="<?php echo esc_attr( WC_AM_PRODUCT_DATA_STORE()->get_meta( $variation->ID, '_api_author' ) ); ?>"
                           placeholder="<?php esc_html_e( 'Todd Lahman', 'woocommerce-api-manager' ); ?>"/>
                </p>

                <p class="form-row form-row-first show_if_api_global_data_set_var<?php echo $loop; ?> api_global_data_set_hide_onload_var<?php echo $loop; ?>">
                    <label><?php esc_html_e( 'Page URL:', 'woocommerce-api-manager' ); ?> <span class="woocommerce-help-tip"
                                                                                                data-tip="<?php esc_html_e( 'The software page URL.', 'woocommerce-api-manager' ); ?>"
                        </span></label>
                    <input type="text" name="_api_plugin_url_var[<?php echo $loop; ?>]"
                           value="<?php echo esc_attr( WC_AM_PRODUCT_DATA_STORE()->get_meta( $variation->ID, '_api_plugin_url' ) ); ?>"
                           placeholder="<?php esc_html_e( 'http://myplugin.com', 'woocommerce-api-manager' ); ?>"/>
                </p>
				<?php
				echo '<p class="form-row form-row-last show_if_api_global_data_set_var' . $loop . ' api_global_data_set_hide_onload_var' . $loop . ' ' . esc_attr( '_api_description_var[' . $loop . ']' ) . '_field ' . esc_attr( '_api_description_var_field' ) . '"><label for="' . esc_attr( '_api_description_var[' . $loop . ']' ) . '">' . esc_html__( 'Description', 'woocommerce-api-manager' ) . '<span class="woocommerce-help-tip" data-tip="' . esc_html__( 'A description of the software, and how it works.', 'woocommerce-api-manager' ) . '"></span></label>';
				$doc       = absint( WC_AM_PRODUCT_DATA_STORE()->get_meta( $variation->ID, '_api_description' ) );
				$desc_args = array(
					'name'             => '_api_description_var[' . $loop . ']',
					'id'               => '_api_description_var[' . $loop . ']',
					'sort_column'      => 'menu_order',
					'sort_order'       => 'ASC',
					'show_option_none' => ' ',
					'echo'             => false,
					'selected'         => ! empty( $doc ) ? $doc : ''
				);

				if ( ! empty( $doc ) ) :
					echo '<span>&nbsp;<a href="' . esc_url( admin_url( 'post.php?post=' . $doc ) ) . '&action=edit" target="_blank" ><span style="text-decoration:none;" class="dashicons dashicons-admin-links"></span></a></span>';
				endif;

				echo '</label><br>';

				echo str_replace( ' id=', " data-placeholder='" . esc_html__( 'Select a page &hellip; (Optional)', 'woocommerce-api-manager' ) . "' data-width='100%' class='wc-am-var-select' id=", wp_dropdown_pages( $desc_args ) );
				echo '</p>';

				echo '<p class="form-row form-row-first show_if_api_global_data_set_var' . $loop . ' api_global_data_set_hide_onload_var' . $loop . ' ' . esc_attr( '_api_changelog_var[' . $loop . ']' ) . '_field ' . esc_attr( '_api_changelog_var_field' ) . '"><label for="' . esc_attr( '_api_changelog_var[' . $loop . ']' ) . '">' . esc_html__( 'Changelog', 'woocommerce-api-manager' ) . '<span class="woocommerce-help-tip" data-tip="' . esc_html__( 'A list of changes to the software that should be grouped by date.', 'woocommerce-api-manager' ) . '"></span></label>';
				$doc       = absint( WC_AM_PRODUCT_DATA_STORE()->get_meta( $variation->ID, '_api_changelog' ) );
				$desc_args = array(
					'name'             => '_api_changelog_var[' . $loop . ']',
					'id'               => '_api_changelog_var[' . $loop . ']',
					'sort_column'      => 'menu_order',
					'sort_order'       => 'ASC',
					'show_option_none' => ' ',
					'echo'             => false,
					'selected'         => ! empty( $doc ) ? $doc : ''
				);

				if ( ! empty( $doc ) ) :
					echo '<span>&nbsp;<a href="' . esc_url( admin_url( 'post.php?post=' . $doc ) ) . '&action=edit" target="_blank" ><span style="text-decoration:none;" class="dashicons dashicons-admin-links"></span></a></span>';
				endif;

				echo '</label><br>';

				echo str_replace( ' id=', " data-placeholder='" . esc_html__( 'Select a page &hellip; (Recommended)', 'woocommerce-api-manager' ) . "' data-width='100%' class='wc-am-var-select' id=", wp_dropdown_pages( $desc_args ) );
				echo '</p>';

				echo '<p class="form-row form-row-last show_if_api_global_data_set_var' . $loop . ' api_global_data_set_hide_onload_var' . $loop . ' ' . esc_attr( '_api_installation_var[' . $loop . ']' ) . '_field ' . esc_attr( '_api_installation_var_field' ) . '"><label for="' . esc_attr( '_api_installation_var[' . $loop . ']' ) . '">' . esc_html__( 'Installation', 'woocommerce-api-manager' ) . '<span class="woocommerce-help-tip" data-tip="' . esc_html__( 'Instructions on how to install the software, and notes regarding installation.', 'woocommerce-api-manager' ) . '"></span></label>';
				$doc       = absint( WC_AM_PRODUCT_DATA_STORE()->get_meta( $variation->ID, '_api_installation' ) );
				$desc_args = array(
					'name'             => '_api_installation_var[' . $loop . ']',
					'id'               => '_api_installation_var[' . $loop . ']',
					'sort_column'      => 'menu_order',
					'sort_order'       => 'ASC',
					'show_option_none' => ' ',
					'echo'             => false,
					'selected'         => ! empty( $doc ) ? $doc : ''
				);

				if ( ! empty( $doc ) ) :
					echo '<span>&nbsp;<a href="' . esc_url( admin_url( 'post.php?post=' . $doc ) ) . '&action=edit" target="_blank" ><span style="text-decoration:none;" class="dashicons dashicons-admin-links"></span></a></span>';
				endif;

				echo '</label><br>';

				echo str_replace( ' id=', " data-placeholder='" . esc_html__( 'Select a page &hellip; (Optional)', 'woocommerce-api-manager' ) . "' data-width='100%' class='wc-am-var-select' id=", wp_dropdown_pages( $desc_args ) );
				echo '</p>';

				echo '<p class="form-row form-row-first show_if_api_global_data_set_var' . $loop . ' api_global_data_set_hide_onload_var' . $loop . ' ' . esc_attr( '_api_faq_var[' . $loop . ']' ) . '_field ' . esc_attr( '_api_faq_var_field' ) . '"><label for="' . esc_attr( '_api_faq_var[' . $loop . ']' ) . '">' . esc_html__( 'FAQ', 'woocommerce-api-manager' ) . '<span class="woocommerce-help-tip" data-tip="' . esc_html__( 'Frequently Asked Questions about the software.', 'woocommerce-api-manager' ) . '"></span></label>';
				$doc       = absint( WC_AM_PRODUCT_DATA_STORE()->get_meta( $variation->ID, '_api_faq' ) );
				$desc_args = array(
					'name'             => '_api_faq_var[' . $loop . ']',
					'id'               => '_api_faq_var[' . $loop . ']',
					'sort_column'      => 'menu_order',
					'sort_order'       => 'ASC',
					'show_option_none' => ' ',
					'echo'             => false,
					'selected'         => ! empty( $doc ) ? $doc : ''
				);

				if ( ! empty( $doc ) ) :
					echo '<span>&nbsp;<a href="' . esc_url( admin_url( 'post.php?post=' . $doc ) ) . '&action=edit" target="_blank" ><span style="text-decoration:none;" class="dashicons dashicons-admin-links"></span></a></span>';
				endif;

				echo '</label><br>';

				echo str_replace( ' id=', " data-placeholder='" . esc_html__( 'Select a page &hellip; (Optional)', 'woocommerce-api-manager' ) . "' data-width='100%' class='wc-am-var-select' id=", wp_dropdown_pages( $desc_args ) );
				echo '</p>';

				echo '<p class="form-row form-row-last show_if_api_global_data_set_var' . $loop . ' api_global_data_set_hide_onload_var' . $loop . ' ' . esc_attr( '_api_screenshots_var[' . $loop . ']' ) . '_field ' . esc_attr( '_api_screenshots_var_field' ) . '"><label for="' . esc_attr( '_api_screenshots_var[' . $loop . ']' ) . '">' . esc_html__( 'Screenshots', 'woocommerce-api-manager' ) . '<span class="woocommerce-help-tip" data-tip="' . esc_html__( 'Screenshots of the software.', 'woocommerce-api-manager' ) . '"></span></label>';
				$doc       = absint( WC_AM_PRODUCT_DATA_STORE()->get_meta( $variation->ID, '_api_screenshots' ) );
				$desc_args = array(
					'name'             => '_api_screenshots_var[' . $loop . ']',
					'id'               => '_api_screenshots_var[' . $loop . ']',
					'sort_column'      => 'menu_order',
					'sort_order'       => 'ASC',
					'show_option_none' => ' ',
					'echo'             => false,
					'selected'         => ! empty( $doc ) ? $doc : ''
				);

				if ( ! empty( $doc ) ) :
					echo '<span>&nbsp;<a href="' . esc_url( admin_url( 'post.php?post=' . $doc ) ) . '&action=edit" target="_blank" ><span style="text-decoration:none;" class="dashicons dashicons-admin-links"></span></a></span>';
				endif;

				echo '</label><br>';

				echo str_replace( ' id=', " data-placeholder='" . esc_html__( 'Select a page &hellip; (Optional)', 'woocommerce-api-manager' ) . "' data-width='100%' class='wc-am-var-select' id=", wp_dropdown_pages( $desc_args ) );
				echo '</p>';

				echo '<p class="form-row form-row-first show_if_api_global_data_set_var' . $loop . ' api_global_data_set_hide_onload_var' . $loop . ' ' . esc_attr( '_api_other_notes_var[' . $loop . ']' ) . '_field ' . esc_attr( '_api_other_notes_var_field' ) . '"><label for="' . esc_attr( '_api_other_notes_var[' . $loop . ']' ) . '">' . esc_html__( 'Other Notes', 'woocommerce-api-manager' ) . '<span class="woocommerce-help-tip" data-tip="' . esc_html__( 'Other notes about the software.', 'woocommerce-api-manager' ) . '"></span></label>';
				$doc       = absint( WC_AM_PRODUCT_DATA_STORE()->get_meta( $variation->ID, '_api_other_notes' ) );
				$desc_args = array(
					'name'             => '_api_other_notes_var[' . $loop . ']',
					'id'               => '_api_other_notes_var[' . $loop . ']',
					'sort_column'      => 'menu_order',
					'sort_order'       => 'ASC',
					'show_option_none' => ' ',
					'echo'             => false,
					'selected'         => ! empty( $doc ) ? $doc : ''
				);

				if ( ! empty( $doc ) ) :
					echo '<span>&nbsp;<a href="' . esc_url( admin_url( 'post.php?post=' . $doc ) ) . '&action=edit" target="_blank" ><span style="text-decoration:none;" class="dashicons dashicons-admin-links"></span></a></span>';
				endif;

				echo '</label><br>';

				echo str_replace( ' id=', " data-placeholder='" . esc_html__( 'Select a page &hellip; (Optional)', 'woocommerce-api-manager' ) . "' data-width='100%' class='wc-am-var-select' id=", wp_dropdown_pages( $desc_args ) );
				echo '</p>';

				echo '<p class="form-row form-row-last show_if_api_global_data_set_var' . $loop . ' api_global_data_set_hide_onload_var' . $loop . ' ' . esc_attr( '_api_product_documentation_var[' . $loop . ']' ) . '_field ' . esc_attr( '_api_product_documentation_var_field' ) . '"><label for="' . esc_attr( '_api_product_documentation_var[' . $loop . ']' ) . '">' . esc_html__( 'My Account Documentation', 'woocommerce-api-manager' ) . '<span class="woocommerce-help-tip" data-tip="' . esc_html__( 'Documentation link for My Account dashboard.', 'woocommerce-api-manager' ) . '"></span></label>';
				$doc       = absint( WC_AM_PRODUCT_DATA_STORE()->get_meta( $variation->ID, '_api_product_documentation' ) );
				$desc_args = array(
					'name'             => '_api_product_documentation_var[' . $loop . ']',
					'id'               => '_api_product_documentation_var[' . $loop . ']',
					'sort_column'      => 'menu_order',
					'sort_order'       => 'ASC',
					'show_option_none' => ' ',
					'echo'             => false,
					'selected'         => ! empty( $doc ) ? $doc : ''
				);

				if ( ! empty( $doc ) ) :
					echo '<span>&nbsp;<a href="' . esc_url( admin_url( 'post.php?post=' . $doc ) ) . '&action=edit" target="_blank" ><span style="text-decoration:none;" class="dashicons dashicons-admin-links"></span></a></span>';
				endif;

				echo '</label><br>';

				echo str_replace( ' id=', " data-placeholder='" . esc_html__( 'Select a page &hellip; (Optional)', 'woocommerce-api-manager' ) . "' data-width='100%' class='wc-am-var-select' id=", wp_dropdown_pages( $desc_args ) );
				echo '</p>';
				?>

            </div>
        </div>

		<?php
		WCAM()->wc_print_js( "
			/* Datepicker for Variations writepanel */
			jQuery( '.wc_api_last_updated_var' ).datepicker({
				defaultDate: '',
				dateFormat: 'yy-mm-dd',
				numberOfMonths: 1,
				showButtonPanel: true
			});

			/* API Variable Variable Product Writepanel Checkboxes */

			jQuery('input.api_global_data_set_var" . $loop . "').change(function() {
				if (jQuery('input.api_global_data_set_var" . $loop . "').is(':checked')) {
					jQuery('.api_global_data_set_hide_onload_var" . $loop . "').show();
				} else {
					jQuery('.api_global_data_set_hide_onload_var" . $loop . "').hide();
				}
			});

			jQuery('input.api_global_data_set_var" . $loop . "').trigger('change');

			jQuery('#api_override_chkbx" . $loop . "').on('change', 'input.api_global_data_set_var" . $loop . "', function() {
				jQuery('.show_if_api_global_data_set_var" . $loop . "').hide();

				if (jQuery(this).is(':checked')) {
					jQuery('.show_if_api_global_data_set_var" . $loop . "').show();
				}
			});

			/* Hide Activation Limit if set for Unlimited Activations */

			jQuery('input#_api_activations_unlimited_var" . $loop . "').change(function() {
                if (jQuery('input#_api_activations_unlimited_var" . $loop . "').is(':checked')) {
                    jQuery('#_api_activations_var" . $loop . "').hide();
                } else {
                    jQuery('#_api_activations_var" . $loop . "').show();
                }
			});

			jQuery('input#_api_activations_unlimited_var" . $loop . "').trigger('change');

		" );

		if ( WCAM()->get_wc_version() >= '3.2' ) :
			WCAM()->wc_print_js( "
				jQuery('select.wc-am-var-select').selectWoo({allowClear:true});
			" );
		else :
			WCAM()->wc_print_js( "
				jQuery('select.wc-am-var-select').select2({allowClear:true});
			" );
		endif;
	}

	/**
	 * Saves Variable product variation data via AJAX.
	 *
	 * @param int $post_id
	 *
	 * @throws \Exception
	 */
	public function ajax_save_product( $post_id ) {
		$this->save_common_variation_fields( $post_id, $_POST );

		WC_AM_PRODUCT_DATA_STORE()->clear_caches( $post_id );
	}

	/**
	 * Save data for Simple product, and Variable product variation, data via the Update button. This save includes the API tab displayed on the
	 * Simple product, and the parent product API tab of a Variable product.
	 *
	 * @param int $post_id
	 *
	 * @throws \Exception
	 */
	public function save_product( $post_id ) {
		$api_data_is_global = WC_AM_PRODUCT_DATA_STORE()->get_meta( $post_id, '_api_data_is_global' );

		if ( ! empty( $api_data_is_global ) ) {
			WC_AM_PRODUCT_DATA_STORE()->delete_meta( $post_id, '_api_data_is_global' );
		}

		$product_type = empty( $_POST[ 'product-type' ] ) ? 'simple' : sanitize_title( stripslashes( $_POST[ 'product-type' ] ) );

		if ( in_array( $product_type, array(
			'simple',
			'variable',
			'subscription',
			'simple-subscription',
			'variable-subscription',
			'subscription_variation'
		) ) ) {
			//Save the data for the API Tab product writepanel input boxes

			/**
			 * Once the API checkbox is checked, the product becomes an API product,
			 * and the API checkbox cannot be unchecked, making this a permanent API product.
			 *
			 * @since 2.0
			 */
			$is_api_product = WC_AM_PRODUCT_DATA_STORE()->get_meta( $post_id, '_is_api' );

			if ( $is_api_product != 'yes' && isset( $_POST[ '_is_api' ] ) ) {
				WC_AM_PRODUCT_DATA_STORE()->update_meta( $post_id, '_is_api', 'yes' );

				/**
				 * If this product was purchased before it was an API product, then add the product data from those orders to the
				 * API Resource table, to enable API resource access for the customer.
				 * Flag this product as '_api_orders_processed' to prevent the background process from running more than once.
				 *
				 * @since 2.0
				 */
				$api_orders_processed = WC_AM_PRODUCT_DATA_STORE()->get_meta( $post_id, '_api_orders_processed' );

				if ( $api_orders_processed != 'yes' ) {
					WC_AM_ORDER()->add_new_api_product_orders( $post_id );
					WC_AM_PRODUCT_DATA_STORE()->update_meta( $post_id, '_api_orders_processed', 'yes' );
				}
			}

			// API Tab writepanel checkbox
			if ( in_array( $product_type, array( 'simple', 'subscription', 'simple-subscription' ) ) ) {
				/**
				 * Set unlimited activations value.
				 *
				 * @since 2.2.0
				 */
				$is_unlimited = WC_AM_PRODUCT_DATA_STORE()->is_api_product_unlimited_activations( $post_id );

				if ( ! $is_unlimited && isset( $_POST[ '_api_activations_unlimited' ] ) ) {
					WC_AM_PRODUCT_DATA_STORE()->update_meta( $post_id, '_api_activations_unlimited', 'yes' );
				} else {
					WC_AM_PRODUCT_DATA_STORE()->update_meta( $post_id, '_api_activations_unlimited', 'no' );
				}

				/**
				 * If current activations are less than the new activations, then update all API resources with the increased activations.
				 *
				 * @since 2.0.1
				 */
				$update_product_orders = false;
				$current_activations   = WC_AM_PRODUCT_DATA_STORE()->get_meta( $post_id, '_api_activations' );
				$current_activations   = ! empty( $current_activations ) ? $current_activations : 0;

				if ( isset( $_POST[ '_api_activations' ] ) && (int) $current_activations < (int) $_POST[ '_api_activations' ] ) {
					$update_product_orders = true;
				}

				WC_AM_PRODUCT_DATA_STORE()->update_meta( $post_id, '_api_activations', ! empty( $_POST[ '_api_activations' ] ) ? absint( $_POST[ '_api_activations' ] ) : apply_filters( 'wc_api_manager_custom_default_api_activations', 1, $post_id ) );

				/**
				 * If current activations are less than the new activations, then update all API resources with the increased activations.
				 * Now that _api_activations has been updated, the orders for this product can be updated.
				 *
				 * @since 2.0.1
				 */
				if ( $update_product_orders ) {
					WC_AM_ORDER()->update_api_resource_activations_for_product( $post_id );
				}
			}

			WC_AM_PRODUCT_DATA_STORE()->update_meta( $post_id, '_access_expires', ! empty( $_POST[ '_access_expires' ] ) ? absint( $_POST[ '_access_expires' ] ) : '' );
			WC_AM_PRODUCT_DATA_STORE()->update_meta( $post_id, '_api_upgrade_notice', isset( $_POST[ '_api_upgrade_notice' ] ) ? sanitize_text_field( $_POST[ '_api_upgrade_notice' ] ) : '' );

			// Create the product_fields variable array
			$this->define_fields();

			//Writepanel text fields
			foreach ( $this->product_fields as $field ) {
				if ( is_array( $field ) && $field[ 'id' ] != '_api_resource_title' ) { // Software Title/Resource Title is deprecated and cannot be changed.
					$data = isset( $_POST[ $field[ 'id' ] ] ) ? esc_attr( wc_clean( $_POST[ $field[ 'id' ] ] ) ) : '';

					// Update the fields.
					WC_AM_PRODUCT_DATA_STORE()->update_meta( $post_id, $field[ 'id' ], $data );

					/**
					 * @since 2.0
					 */
					if ( in_array( $product_type, array( 'simple', 'subscription', 'simple-subscription' ) ) ) {
						if ( $field[ 'id' ] == '_api_resource_product_id' ) {
							$api_resource_product_id = WC_AM_PRODUCT_DATA_STORE()->get_meta( $post_id, '_api_resource_product_id' );

							if ( empty( $api_resource_product_id ) ) {
								WC_AM_PRODUCT_DATA_STORE()->update_meta( $post_id, $field[ 'id' ], absint( $post_id ) );
							}
						}
					}
				}
			}

			// Writepanel page fields.
			$pages = array(
				'_api_description',
				'_api_changelog',
				'_api_installation',
				'_api_faq',
				'_api_screenshots',
				'_api_other_notes',
				'_api_product_documentation'
			);

			foreach ( $pages as $key => $page ) {
				if ( isset( $_POST[ "$page" ] ) ) {
					WC_AM_PRODUCT_DATA_STORE()->update_meta( $post_id, "$page", absint( $_POST[ "$page" ] ) );
				}
			}
		}

		/**
		 * Save variation product data.
		 */
		if ( in_array( $product_type, array( 'variable', 'variable-subscription', 'subscription_variation' ) ) ) {
			$this->save_common_variation_fields( $post_id, $_POST );
		}

		WC_AM_PRODUCT_DATA_STORE()->clear_caches( $post_id );
	}

	/**
	 * Save variation field data.
	 *
	 * @since 2.0
	 *
	 * @param int   $post_id
	 * @param array $post
	 *
	 * @throws \Exception
	 */
	public function save_common_variation_fields( $post_id, $post ) {
		$variable_post_ids = ! empty( $post[ 'variable_post_id' ] ) ? $post[ 'variable_post_id' ] : '';

		if ( ! empty( $variable_post_ids ) && is_array( $variable_post_ids ) ) {
			$max_loop = max( array_keys( $variable_post_ids ) );

			for ( $i = 0; $i <= $max_loop; $i ++ ) {
				if ( ! isset( $variable_post_ids[ $i ] ) ) {
					continue;
				}

				$variation_id = absint( $variable_post_ids[ $i ] );

				/**
				 * Once the API checkbox is checked, the product becomes an API product,
				 * and the API checkbox cannot be unchecked, making this a permanent API product.
				 *
				 * @since 2.0
				 */
				$is_parent_api_product  = WC_AM_PRODUCT_DATA_STORE()->get_meta( $post_id, '_is_api' );
				$is_variabl_api_product = WC_AM_PRODUCT_DATA_STORE()->get_meta( $variation_id, '_is_api' );

				// If the parent was already an API product, and perhpas this product variation is just now becoming an API product.
				if ( ( $is_parent_api_product == 'yes' && $is_variabl_api_product != 'yes' ) || ( isset( $post[ '_is_api' ] ) && $is_variabl_api_product != 'yes' ) ) {
					WC_AM_PRODUCT_DATA_STORE()->update_meta( $variation_id, '_is_api', 'yes' );
					/**
					 * If this product was purchased before it was an API product, then add the product data from those orders to the
					 * API Resource table, to enable API resource access for the customer.
					 * Flag this product as '_api_orders_processed' to prevent the background process from running more than once.
					 *
					 * @since 2.0
					 */
					$api_orders_processed = WC_AM_PRODUCT_DATA_STORE()->get_meta( $variation_id, '_api_orders_processed' );

					if ( $api_orders_processed != 'yes' ) {
						WC_AM_ORDER()->add_new_api_product_orders( $variation_id );
						WC_AM_PRODUCT_DATA_STORE()->update_meta( $variation_id, '_api_orders_processed', 'yes' );
					}
				}

				/**
				 * @since 2.0
				 */
				$api_resource_product_id_parent = WC_AM_PRODUCT_DATA_STORE()->get_meta( $post_id, '_api_resource_product_id' );

				if ( empty( $api_resource_product_id_parent ) ) {
					WC_AM_PRODUCT_DATA_STORE()->update_meta( $post_id, '_api_resource_product_id', $post_id );
				}

				/**
				 * @since 2.0
				 */
				$api_resource_product_id_var = WC_AM_PRODUCT_DATA_STORE()->get_meta( $variation_id, '_api_resource_product_id' );

				if ( empty( $api_resource_product_id_var ) ) {
					WC_AM_PRODUCT_DATA_STORE()->update_meta( $variation_id, '_api_resource_product_id', $variation_id );
				}

				// Check if checkbox on variable product is on for "Set API options for this variable product only"
				if ( ! empty( $post[ '_api_data_is_global_override' ][ $i ] ) ) {
					WC_AM_PRODUCT_DATA_STORE()->update_meta( $variation_id, '_api_data_is_global_override', 'yes' );
				} else {
					WC_AM_PRODUCT_DATA_STORE()->update_meta( $variation_id, '_api_data_is_global_override', 'no' );
				}

				$global_override = WC_AM_PRODUCT_DATA_STORE()->get_meta( $variation_id, '_api_data_is_global_override' );

				/**
				 * Set unlimited activations value.
				 *
				 * @since 2.2.0
				 */
				//$is_unlimited = WC_AM_PRODUCT_DATA_STORE()->is_api_product_unlimited_activations( $variation_id );

				if ( ! empty( $post[ '_api_activations_unlimited_var' ][ $i ] ) ) {
					WC_AM_PRODUCT_DATA_STORE()->update_meta( $variation_id, '_api_activations_unlimited', 'yes' );
				} else {
					WC_AM_PRODUCT_DATA_STORE()->update_meta( $variation_id, '_api_activations_unlimited', 'no' );
				}

				/**
				 * If current activations are less than the new activations, then update all API resources with the increased activations.
				 *
				 * @since 2.0.1
				 */
				$update_product_orders = false;
				$current_activations   = WC_AM_PRODUCT_DATA_STORE()->get_meta( $variation_id, '_api_activations' );
				$current_activations   = ! empty( $current_activations ) ? $current_activations : 0;

				if ( isset( $post[ '_api_activations_var' ] ) && (int) $current_activations < (int) $post[ '_api_activations_var' ][ $i ] ) {
					$update_product_orders = true;
				}

				// Save variable product data directly. Ignore API Tab global settings.
				if ( $global_override == 'yes' || isset( $post[ '_api_data_is_global_override' ][ $i ] ) ) {
					$lean_fields = array(
						'_api_new_version',
						'_api_plugin_url',
						'_api_author',
						'_api_version_required',
						'_api_tested_up_to',
						'_api_requires_php',
						'_api_last_updated',
						'_api_upgrade_notice',
					);

					foreach ( $lean_fields as $key => $clean_field ) {
						if ( isset( $post[ $clean_field . '_var' ][ $i ] ) ) {
							WC_AM_PRODUCT_DATA_STORE()->update_meta( $variation_id, $clean_field, wc_clean( $post[ $clean_field . '_var' ][ $i ] ) );
						}
					}

					// Value set per variation if override is checked.
					$absint_fields = array(
						'_api_activations',
						'_access_expires',
						'_api_description',
						'_api_changelog',
						'_api_installation',
						'_api_faq',
						'_api_screenshots',
						'_api_other_notes',
						'_api_product_documentation'
					);

					foreach ( $absint_fields as $key => $absint_field ) {
						if ( isset( $post[ $absint_field . '_var' ][ $i ] ) ) {
							WC_AM_PRODUCT_DATA_STORE()->update_meta( $variation_id, $absint_field, absint( $post[ $absint_field . '_var' ][ $i ] ) );
						}
					}

					/**
					 * If current activations are less than the new activations, then update all API resources with the increased activations.
					 * Now that _api_activations has been updated, the orders for this product can be updated.
					 *
					 * @since 2.0.1
					 */
					if ( $update_product_orders ) {
						WC_AM_ORDER()->update_api_resource_activations_for_product( $variation_id );
					}
				} else { // Use API Tab global settings for variable products.
					/**
					 * If current activations are less than the new activations, then update all API resources with the increased activations.
					 *
					 * @since 2.0.1
					 */
					$update_product_orders = false;
					$current_activations   = WC_AM_PRODUCT_DATA_STORE()->get_meta( $variation_id, '_api_activations' );
					$current_activations   = ! empty( $current_activations ) ? $current_activations : 0;

					if ( isset( $post[ '_api_activations_var' ] ) && (int) $current_activations < (int) $post[ '_api_activations_var' ][ $i ] ) {
						$update_product_orders = true;
					}

					if ( isset( $post[ '_api_activations_var' ][ $i ] ) ) {
						WC_AM_PRODUCT_DATA_STORE()->update_meta( $variation_id, '_api_activations', wc_clean( $post[ '_api_activations_var' ][ $i ] ) );
					}

					/**
					 * If current activations are less than the new activations, then update all API resources with the increased activations.
					 * Now that _api_activations has been updated, the orders for this product can be updated.
					 *
					 * @since 2.0.1
					 */
					if ( $update_product_orders ) {
						WC_AM_ORDER()->update_api_resource_activations_for_product( $variation_id );
					}

					// Values inherited from Parent API tab form fields.
					$lean_fields = array(
						'_api_new_version',
						'_api_plugin_url',
						'_api_author',
						'_api_version_required',
						'_api_tested_up_to',
						'_api_requires_php',
						'_api_last_updated',
						'_api_upgrade_notice',
					);

					foreach ( $lean_fields as $key => $clean_field ) {
						if ( isset( $post[ $clean_field ] ) ) {
							WC_AM_PRODUCT_DATA_STORE()->update_meta( $variation_id, $clean_field, wc_clean( $post[ $clean_field ] ) );
						}
					}

					// Values inherited from Parent API tab form fields.
					$absint_fields = array(
						'_access_expires',
						'_api_description',
						'_api_changelog',
						'_api_installation',
						'_api_faq',
						'_api_screenshots',
						'_api_other_notes',
						'_api_product_documentation'
					);

					foreach ( $absint_fields as $key => $absint_field ) {
						if ( isset( $post[ $absint_field ] ) ) {
							WC_AM_PRODUCT_DATA_STORE()->update_meta( $variation_id, $absint_field, absint( $post[ $absint_field ] ) );
						}
					}
				} // End if
			} // end for loop
		} // end if is_array
	}
}