<?php

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * WooCommerce API Manager Order Admin Class
 *
 * @since       1.1.1
 *
 * @author      Todd Lahman LLC
 * @copyright   Copyright (c) Todd Lahman LLC
 * @package     WooCommerce API Manager/Order Admin
 */
class WC_AM_Order_Admin {

	/**
	 * @var null
	 */
	private static $_instance = null;

	/**
	 * @static
	 * @return \WC_AM_Order_Admin
	 */
	public static function instance() {

		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	private function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_action( 'wp_ajax_wc_api_manager_delete_activation', array( $this, 'delete_activation' ) );
		add_action( 'wp_ajax_wc_api_manager_toggle_activation', array( $this, 'toggle_activation_status' ) );
		add_action( 'woocommerce_process_shop_order_meta', array( $this, 'save' ), 10, 2 );
		add_filter( 'manage_edit-shop_order_columns', array( $this, 'render_contains_api_product_column' ) );
		add_action( 'manage_shop_order_posts_custom_column', array( $this, 'render_contains_api_product_column_content' ), 10, 2 );
	}

	public function add_meta_boxes() {
		add_meta_box( 'wc_am_master_api_key', esc_html__( 'Master API Key', 'woocommerce-api-manager' ), array(
			$this,
			'master_api_key_meta_box'
		), 'shop_order', 'normal', 'high' );
		add_meta_box( 'wc_am_api_resource', esc_html__( 'API Resources', 'woocommerce-api-manager' ), array(
			$this,
			'api_resource_meta_box'
		), 'shop_order', 'normal', 'high' );
		add_meta_box( 'wc_am_api_resource_activations', esc_html__( 'API Resource Activations', 'woocommerce-api-manager' ), array(
			$this,
			'api_resource_activation_meta_box'
		), 'shop_order', 'normal', 'high' );
	}

	/**
	 * Master API Key Meta Box*
	 *
	 * @since 2.0
	 */
	public function master_api_key_meta_box() {
		global $post;

		if ( ! WC_AM_ORDER_DATA_STORE()->has_api_product( $post->ID ) ) {
			?>
            <p style="padding:0 8px;"><?php esc_html_e( 'Contains no API Product.', 'woocommerce-api-manager' ) ?></p>
			<?php
		} else {
			$user_id = WC_AM_API_RESOURCE_DATA_STORE()->get_user_id_by_order_id( $post->ID );

			/**
			 * Every customer must have a Master API Key, and it is missing, so create it now.
			 */
			if ( empty( WC_AM_USER()->get_master_api_key( $user_id ) ) ) {
				WC_AM_USER()->set_registration_master_key_and_status( $user_id );
			}

			$mak = WC_AM_USER()->get_master_api_key( $user_id );

			if ( ! empty( $mak ) ) {
				?>
                <div class="api_order_licence_keys wc-metaboxes-wrapper">
					<?php
					include( WCAM()->plugin_path() . '/includes/admin/meta-boxes/html-order-master-api-key.php' );
					?>
                </div>
				<?php
			} else {
				?><p style="padding:0 8px;"><?php esc_html_e( 'No API resources for this order.', 'woocommerce-api-manager' ) ?></p><?php
			}
		}
	}

	/**
	 * API Resources Meta Box*
	 *
	 * @since 2.0
	 */
	public function api_resource_meta_box() {
		global $post;

		if ( ! WC_AM_ORDER_DATA_STORE()->has_api_product( $post->ID ) ) {
			?>
            <p style="padding:0 8px;"><?php esc_html_e( 'Contains no API Product.', 'woocommerce-api-manager' ) ?></p>
			<?php
		} else {
			$resources     = array();
			$sub_parent_id = 0;
			$sub_resources = array();

			/**
			 * Subscription resources should be displayed on the Subscription parent order only.
			 */
			if ( WCAM()->get_wc_subs_exist() ) {
				$sub_parent_id = WC_AM_SUBSCRIPTION()->get_parent_id( $post->ID );

				if ( (int) $sub_parent_id == (int) $post->ID ) {
					// Use $sub_parent_id, since $post_id would get results only for the current post, not the parent.
					$sub_resources = WC_AM_API_RESOURCE_DATA_STORE()->get_all_api_resources_for_sub_parent_id( $sub_parent_id );
				}
			}

			if ( ! empty( $sub_resources ) ) {
				$non_sub_resources = WC_AM_API_RESOURCE_DATA_STORE()->get_all_api_non_wc_subscription_resources_for_order_id( $post->ID );
				$resources         = array_merge( $non_sub_resources, $sub_resources );
			} else {
				// If WC Subs exist, but WC Subs is deactvated, the Expires field will display required.
				$resources = WC_AM_API_RESOURCE_DATA_STORE()->get_all_api_resources_for_order_id( $post->ID );
			}

			if ( ! empty( $resources ) ) {
				?>
                <div class="api_order_licence_keys wc-metaboxes-wrapper">
					<?php
					$i = 0;

					foreach ( $resources as $resource ) {
						// Delete excess API Key activations by activation resource ID.
						WC_AM_API_ACTIVATION_DATA_STORE()->delete_excess_api_key_activations_by_activation_id( $resource->activation_ids, $resource->activations_purchased_total );

						// This prevents Subscription orders that were switched away from, from displaying API Resources meant for the new Switched order.
						if ( $post->ID == $resource->order_id ) {
							include( WCAM()->plugin_path() . '/includes/admin/meta-boxes/html-order-api-resources.php' );

							$i ++;
						} else {
							?><p style="padding:0 8px;"><?php esc_html_e( 'No API resources for this order.', 'woocommerce-api-manager' ) ?></p><?php
						}
					}
					?>
                </div>
				<?php
				/**
				 * Javascript
				 */
				ob_start();
				?>
                /**
                * Expand API Key Text Input on mouseover
                */
                jQuery('.am_expand_text_box').mouseenter(function(){
                var $this = jQuery(this);
                if (!$this.data('expand')) {
                $this.data('expand', true);
                $this.animate({width:'+=140',left:'-=6px'}, 'linear');
                $this.siblings('.s').animate({width:'-=140',left:'+=6px'}, 'linear')
                }
                $this.focus();
                $this.select();
                }).mouseleave(function(){
                var $this = jQuery(this);
                $this.data('expand', false);
                $this.animate({width:'-=140',left:'+=6px'}, 'linear');
                $this.siblings('.s').animate({width:'+=140',left:'-=6px'}, 'linear')
                });

				<?php
				$javascript = ob_get_clean();
				WCAM()->wc_print_js( $javascript );

				if ( WCAM()->get_wc_version() >= '3.2' ) :
					ob_start();
					?>
                    jQuery('select.add_api_license_key').selectWoo({allowClear:true});
					<?php
					$javascript = ob_get_clean();
					WCAM()->wc_print_js( $javascript );
				else :
					ob_start();
					?>
                    jQuery('select.add_api_license_key').select2({allowClear:true});
					<?php
					$javascript = ob_get_clean();
					WCAM()->wc_print_js( $javascript );
				endif;
			} else {
				?><p style="padding:0 8px;"><?php esc_html_e( 'No API resources for this order.', 'woocommerce-api-manager' ) ?></p><?php
				return;
			}
		}
	}

	/**
	 * API Resources Meta Box*
	 *
	 * @since 2.0
	 */
	public function api_resource_activation_meta_box() {
		global $post;

		if ( ! WC_AM_ORDER_DATA_STORE()->has_api_product( $post->ID ) ) {
			?>
            <p style="padding:0 8px;"><?php esc_html_e( 'Contains no API Product.', 'woocommerce-api-manager' ) ?></p>
			<?php
		} else {
			$activation_resources  = WC_AM_API_ACTIVATION_DATA_STORE()->get_activation_resources_by_order_id( $post->ID );
			$order_contains_switch = ! empty( $activation_resources[ 0 ]->sub_item_id ) && WC_AM_SUBSCRIPTION()->is_subscription_switch_order( $post->ID );

			/**
			 * Subscription activations should be displayed on the Subscription parent, or Switched Subscription, order only.
			 */
			if ( ! empty( $activation_resources[ 0 ]->sub_parent_id ) && ! $order_contains_switch && $activation_resources[ 0 ]->sub_parent_id != $post->ID ) {
				?>
                <p style="padding:0 8px;"><?php esc_html_e( 'No activations yet.', 'woocommerce-api-manager' ) ?></p>
				<?php
			} elseif ( ! empty( $activation_resources ) ) {
				include( WCAM()->plugin_path() . '/includes/admin/meta-boxes/html-order-api-activations.php' );
				/**
				 * Delete Activation Javascript
				 */
				ob_start();
				?>
                jQuery( '#activations-table' ).on( 'click', 'button.delete_api_key', function( e ){
                e.preventDefault();

                var answer = confirm('<?php echo esc_js( __( 'Are you sure you want to delete this activation?', 'woocommerce-api-manager' ) ); ?>');

                if ( answer ){
                var el              = jQuery( this ).parent().parent();
                var instance        = jQuery( this ).attr( 'instance' );
                var order_id        = jQuery( this ).attr( 'order_id' );
                var sub_parent_id   = jQuery( this ).attr( 'sub_parent_id' );
                var api_key         = jQuery( this ).attr( 'api_key' );
                var product_id      = jQuery( this ).attr( 'product_id' );
                var user_id         = jQuery( this ).attr( 'user_id' );

                if ( instance ) {
                jQuery(el).block({
                message: null,
                overlayCSS: {
                background: '#fff',
                opacity: 0.6
                }
                });

                var data = {
                action:         'wc_api_manager_delete_activation',
                instance:       instance,
                order_id:       order_id,
                sub_parent_id:  sub_parent_id,
                api_key:        api_key,
                product_id:     product_id,
                user_id:        user_id,
                security:       '<?php echo wp_create_nonce( "am-delete-activation" ); ?>'
                };

                jQuery.post('<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>', data, function( response ) {
                // Success
                jQuery(el).fadeOut('300', function(){
                jQuery(el).remove();
                });

                location.reload(true);
                });

                } else {
                jQuery( el ).fadeOut('300', function(){
                jQuery( el ).remove();
                });
                }

                }

                return false;
                });

				<?php
				$javascript = ob_get_clean();
				WCAM()->wc_print_js( $javascript );
			} else {
				?>
                <p style="padding:0 8px;"><?php esc_html_e( 'No activations yet.', 'woocommerce-api-manager' ) ?></p>
				<?php
			}
		}
	}

	/**
	 * Delete activation using the Delete button in API Resources Activations meta box.
	 *
	 * @since 2.0
	 */
	public function delete_activation() {
		check_ajax_referer( 'am-delete-activation', 'security' );

		// Delete activation.
		WC_AM_API_ACTIVATION_DATA_STORE()->delete_api_key_activation_by_instance_id( wc_clean( $_POST[ 'instance' ] ) );

		/**
		 * Delete cache.
		 *
		 * @since 2.1.7
		 */
		WC_AM_SMART_CACHE()->delete_cache( wc_clean( array(
			                                             'admin_resources' => array(
				                                             'instance'      => $_POST[ 'instance' ],
				                                             'order_id'      => $_POST[ 'order_id' ],
				                                             'sub_parent_id' => $_POST[ 'sub_parent_id' ],
				                                             'api_key'       => $_POST[ 'api_key' ],
				                                             'product_id'    => $_POST[ 'product_id' ],
				                                             'user_id'       => $_POST[ 'user_id' ]
			                                             )
		                                             ) ), true );

		wp_die();
	}

	/**
	 * Save the data from the API Resources meta box
	 *
	 * @since 2.0
	 *
	 * @param int    $post_id
	 * @param object $post
	 *
	 * @throws \Exception
	 */
	public function save( $post_id, $post ) {
		global $wpdb;

		if ( isset( $_POST[ 'activations_purchased_total' ] ) && isset( $_POST[ 'product_id' ] ) && isset( $_POST[ 'product_order_api_key' ] ) ) {
			$product_order_api_key       = $_POST[ 'product_order_api_key' ];
			$activations_purchased_total = $_POST[ 'activations_purchased_total' ];
			$max_loop                    = max( array_keys( $product_order_api_key ) );

			for ( $i = 0; $i <= $max_loop; $i ++ ) {
				if ( ! isset( $product_order_api_key[ $i ] ) ) {
					continue;
				}

				$product_id = (int) $_POST[ 'product_id' ][ $i ];

				$data = array(
					//'active'                      => (int) $_POST[ 'active' ][ $i ],
					'activations_purchased_total' => ! empty( $activations_purchased_total[ $i ] ) ? (int) $activations_purchased_total[ $i ] : apply_filters( 'wc_api_manager_custom_default_api_activations', 1, $product_id )
					//'activations_purchased'       => ! empty( $activations_purchased_total[ $i ] ) ? (int) $activations_purchased_total[ $i ] : apply_filters( 'wc_api_manager_custom_default_api_activations', 1, $product_id )
				);

				$where = array(
					'order_id'   => $post_id,
					'product_id' => $product_id
				);

				$data_format = array(
					//'%d',
					//'%d',
					'%d'
				);

				$where_format = array(
					'%d',
					'%d'
				);

				$wpdb->update( $wpdb->prefix . WC_AM_USER()->get_api_resource_table_name(), $data, $where, $data_format, $where_format );
			}

			WC_AM_SMART_CACHE()->refresh_cache_by_order_id( $post_id );
		}
	}

	/**
	 * Add a column to the WooCommerce Orders admin screen to indicate whether an order contains an API Product.
	 *
	 * @since 2.1.2
	 *
	 * @param array $columns The current list of columns
	 *
	 * @return array
	 */
	public function render_contains_api_product_column( $columns ) {
		$column_header = '<span class="api_product_head tips" data-tip="' . esc_attr__( 'Contains API Product', 'woocommerce-api-manager' ) . '">' . esc_attr__( 'API Product', 'woocommerce-api-manager' ) . '</span>';
		$new_columns   = WC_AM_ARRAY()->array_insert_after( 'shipping_address', $columns, 'api_product', $column_header );

		return $new_columns;
	}

	/**
	 * Add a column to the WooCommerce Orders admin screen to indicate whether an order contains an API Product.
	 *
	 * @since 2.1.2
	 *
	 * @param string $column The string of the current column
	 * @param int    $post_id
	 */
	public function render_contains_api_product_column_content( $column, $post_id ) {
		if ( 'api_product' == $column ) {
			if ( WC_AM_ORDER_DATA_STORE()->has_api_product( $post_id ) ) {
				$has_activations = WC_AM_API_ACTIVATION_DATA_STORE()->has_activations_for_order_id( $post_id );

				if ( $has_activations ) {
					echo '<span class="api_product_order_has_activations tips" data-tip="' . esc_attr__( 'Has activations.', 'woocommerce-api-manager' ) . '"></span>';
				} else {
					echo '<span class="api_product_order_no_activations tips" data-tip="' . esc_attr__( 'No activations.', 'woocommerce-api-manager' ) . '"></span>';
				}
			} else {
				echo '<span class="normal_order">&ndash;</span>';
			}
		}
	}

} // End of class