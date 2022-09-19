<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Store Admin Class.
 *
 * General admin class to handle all things admin side for store.
 *
 * @category Admin
 * @package  WooCommerce Product Vendors/Admin
 * @version  2.0.0
 */
class WC_Product_Vendors_Store_Admin {
	public static $self;

	/**
	 * Initialize.
	 *
	 * @since 2.0.0
	 * @version 2.0.35
	 */
	public static function init() {
		self::$self = new self();

		// enqueues scripts and styles.
		add_action( 'admin_enqueue_scripts', array( self::$self, 'enqueue_scripts_styles' ) );

		// displays count bubble on pending items such as products or vendors.
		add_filter( 'add_menu_classes', array( self::$self, 'pending_items_count' ) );

		// Perform some actions when a user is deleted.
		add_action( 'delete_user', array( self::$self, 'delete_user' ) );

		// adds the screen ids to WooCommerce so WooCommerce scripts and styles will load.
		add_filter( 'woocommerce_screen_ids', array( self::$self, 'add_screen_ids_to_wc' ) );

		// add fields to taxonomy edit page.
		add_action( WC_PRODUCT_VENDORS_TAXONOMY . '_edit_form_fields', array( self::$self, 'edit_vendor_fields' ) );

		// add fields to taxonomy on create page.
		add_action( WC_PRODUCT_VENDORS_TAXONOMY . '_add_form_fields', array( self::$self, 'add_vendor_fields' ) );

		// save custom fields from taxonomy.
		add_action( 'edited_' . WC_PRODUCT_VENDORS_TAXONOMY, array( self::$self, 'save_vendor_fields' ) );

		// save custom fields from taxonomy.
		add_action( 'created_' . WC_PRODUCT_VENDORS_TAXONOMY, array( self::$self, 'save_vendor_fields' ) );

		/**
		 * TODO:WCY
         *
         * 禁止向快速编辑添加字段
		 */
		// modify taxonomy columns.
		//add_filter( 'manage_edit-' . WC_PRODUCT_VENDORS_TAXONOMY . '_columns', array( self::$self, 'modify_vendor_columns' ) );

		// modify taxonomy columns.
		//add_filter( 'manage_' . WC_PRODUCT_VENDORS_TAXONOMY . '_custom_column', array( self::$self, 'render_vendor_columns' ), 10, 3 );

		// add a new column to users.
		add_filter( 'manage_users_columns', array( self::$self, 'add_custom_user_column' ) );

		// modify user columns.
		add_action( 'manage_users_custom_column', array( self::$self, 'add_user_column_data' ), 10, 3 );

		// add vendor section to user profile.
		add_action( 'edit_user_profile', array( self::$self, 'add_product_vendor_user_profile_section' ) );
		add_action( 'show_user_profile', array( self::$self, 'add_product_vendor_user_profile_section' ) );

		// save user profile.
		add_action( 'edit_user_profile_update', array( self::$self, 'save_product_vendor_user_profile_section' ) );

		// add commission top level menu item.
		add_action( 'admin_menu', array( self::$self, 'register_commissions_menu_item' ) );

		// set the screen option.
		add_filter( 'set-screen-option', array( self::$self, 'set_screen_option' ), 99, 3 );

		// adds fields to attachments.
		add_filter( 'attachment_fields_to_edit', array( self::$self, 'add_attachments_field' ), 10, 2 );

		// save fields to attachments.
		add_filter( 'attachment_fields_to_save', array( self::$self, 'save_attachments_field' ), 10, 2 );

		// add vendor settings section to products tab.
		add_filter( 'woocommerce_get_sections_products', array( self::$self, 'add_vendor_settings_section' ) );

		// get vendor settings.
		add_filter( 'woocommerce_get_settings_products', array( self::$self, 'add_vendor_settings' ), 10, 2 );

		// save vendor settings.
		add_filter( 'woocommerce_admin_settings_sanitize_option', array( self::$self, 'save_vendor_settings' ), 10, 3 );

		// add sold by vendor on order item.
		add_action( 'woocommerce_after_order_itemmeta', array( self::$self, 'add_sold_by_order_item_detail' ), 10, 3 );

		// add a commission field to the product general tab.
		add_action( 'woocommerce_product_options_general_product_data', array( self::$self, 'add_product_commission_field_general' ) );

		// save commission field for the product general tab.
		add_action( 'woocommerce_process_product_meta_simple', array( self::$self, 'save_product_commission_field_general' ) );
		add_action( 'woocommerce_process_product_meta_booking', array( self::$self, 'save_product_commission_field_general' ) );
		add_action( 'woocommerce_process_product_meta_subscription', array( self::$self, 'save_product_commission_field_general' ) );
		add_action( 'woocommerce_process_product_meta_variable-subscription', array( self::$self, 'save_product_commission_field_general' ) );

		// add a commission field for the product variation.
		add_action( 'woocommerce_product_after_variable_attributes', array( self::$self, 'add_product_commission_field_variation' ), 10, 3 );

		// save commission field for product variation.
		add_action( 'woocommerce_process_product_meta_variable', array( self::$self, 'save_product_commission_field_variable' ) );
		add_action( 'woocommerce_save_product_variation', array( self::$self, 'save_product_commission_field_variation' ), 10, 2 );

		// add variation commission bulk edit.
		add_action( 'woocommerce_variable_product_bulk_edit_actions', array( self::$self, 'add_variation_vendor_bulk_edit' ) );

		// add a pass shipping/tax field to the product general tab.
		add_action( 'woocommerce_product_options_general_product_data', array( self::$self, 'add_product_pass_shipping_tax_field_general' ) );

		// save pass shipping/tax field for the product general tab.
		add_action( 'woocommerce_process_product_meta', array( self::$self, 'save_product_pass_shipping_tax_field_general' ) );

		// add pass shipping/tax to product bulk edit menu.
		add_action( 'woocommerce_product_bulk_edit_end', array( self::$self, 'add_product_bulk_edit_pass_shipping_tax' ) );

		// save pass shipping/tax to product bulk edit.
		add_action( 'woocommerce_product_bulk_edit_save', array( self::$self, 'save_product_bulk_edit_pass_shipping_tax' ) );

		// clear reports transients.
		add_action( 'woocommerce_new_order', array( self::$self, 'clear_reports_transients' ) );
		add_action( 'save_post', array( self::$self, 'clear_reports_transients' ) );
		add_action( 'delete_post', array( self::$self, 'clear_reports_transients' ) );
		add_action( 'woocommerce_order_status_changed', array( self::$self, 'clear_reports_transients' ) );
		add_action( 'wcpv_commissions_status_changed', array( self::$self, 'clear_reports_transients' ) );

		// reports ajax search for vendors.
		add_action( 'wp_ajax_wcpv_vendor_search_ajax', array( self::$self, 'vendor_search_ajax' ) );

		// exports commissions for the current view.
		add_action( 'wp_ajax_wcpv_export_commissions_ajax', array( self::$self, 'export_commissions_ajax' ) );

		// exports unpaid commissions.
		add_action( 'wp_ajax_wcpv_export_unpaid_commissions_ajax', array( self::$self, 'export_unpaid_commissions_ajax' ) );

		// process when vendor role is updated from pending to admin or manager.
		add_action( 'set_user_role', array( self::$self, 'role_update' ), 10, 3 );

		// add clear transients button in WC system tools.
		add_filter( 'woocommerce_debug_tools', array( self::$self, 'add_debug_tool' ) );

		// Filter order item meta label.
		add_filter( 'woocommerce_order_item_display_meta_key', array( self::$self, 'filter_order_attribute_label' ) );
		add_filter( 'woocommerce_order_item_display_meta_value', array( self::$self, 'filter_order_attribute_value' ), 10, 2 );

		// add quick edit items and process save.
		/**
		 * TODO:WCY
         *
         * 禁止向快速编辑添加内容
		 */
		//add_action( 'quick_edit_custom_box', array( self::$self, 'quick_edit' ), 10, 2 );
		//add_action( 'bulk_edit_custom_box', array( self::$self, 'quick_edit' ), 10, 2 );
		//add_action( 'save_post', array( self::$self, 'bulk_and_quick_edit_save_post' ), 10, 2 );

		// saves the vendor to the product.
		add_action( 'save_post', array( self::$self, 'save_product_vendor' ) );

		return true;
	}

	/**
	 * Returns the current class object.
	 *
	 * @since 2.0.35
	 * @version 2.0.35
	 */
	public static function get_instance() {
		return self::$self;
	}

	/**
	 * Adds our screen ids to WC so scripts can load
	 *
	 * @access public
	 * @since 2.0.0
	 * @version 2.0.0
	 * @return array $screen_ids
	 */
	public function add_screen_ids_to_wc( $screen_ids ) {
		$screen_ids[] = 'edit-wcpv_product_vendors';

		return $screen_ids;
	}

	/**
	 * Gets the screen ids that needs styles or scripts
	 *
	 * @access public
	 * @since 2.0.0
	 * @version 2.0.0
	 * @return bool
	 */
	public function get_screen_ids() {
		return apply_filters( 'wcpv_store_admin_screen_ids', array(
			'edit-wcpv_product_vendors',
			'toplevel_page_wcpv-commissions',
			'product',
			'woocommerce_page_wc-reports',
			'woocommerce_page_wc-settings',
		) );
	}

	/**
	 * Enqueue scripts and styles
	 *
	 * @access public
	 * @since 2.0.0
	 * @version 2.0.0
	 * @return bool
	 */
	public function enqueue_scripts_styles() {
		$current_screen = get_current_screen();

		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		wp_register_script( 'wcpv-admin-scripts', WC_PRODUCT_VENDORS_PLUGIN_URL . '/assets/js/wcpv-admin-scripts' . $suffix . '.js', array( 'jquery' ), WC_PRODUCT_VENDORS_VERSION, true );

		wp_register_style( 'wcpv-admin-styles', WC_PRODUCT_VENDORS_PLUGIN_URL . '/assets/css/wcpv-admin-styles.css', array(), WC_PRODUCT_VENDORS_VERSION );

		$localized_vars = array(
			'isPendingVendor'           => current_user_can( 'wc_product_vendors_pending_vendor' ) ? true : false,
			'pending_vendor_message'    => __( 'Thanks for registering to become a vendor.  Your application is being reviewed at this time.', 'woocommerce-product-vendors' ),
			'modalLogoTitle'            => __( 'Add Logo', 'woocommerce-product-vendors' ),
			'buttonLogoText'            => __( 'Add Logo', 'woocommerce-product-vendors' ),
			'ajaxurl'                   => admin_url( 'admin-ajax.php' ),
			'vendor_search_nonce'       => wp_create_nonce( '_wcpv_vendor_search_nonce' ),
			'i18n_matches_1'            => _x( 'One result is available, press enter to select it.', 'enhanced select', 'woocommerce-product-vendors' ),
			'i18n_matches_n'            => _x( '%qty% results are available, use up and down arrow keys to navigate.', 'enhanced select', 'woocommerce-product-vendors' ),
			'i18n_no_matches'           => _x( 'No matches found', 'enhanced select', 'woocommerce-product-vendors' ),
			'i18n_ajax_error'           => _x( 'Loading failed', 'enhanced select', 'woocommerce-product-vendors' ),
			'i18n_input_too_short_1'    => _x( 'Please enter 1 or more characters', 'enhanced select', 'woocommerce-product-vendors' ),
			'i18n_input_too_short_n'    => _x( 'Please enter %qty% or more characters', 'enhanced select', 'woocommerce-product-vendors' ),
			'i18n_input_too_long_1'     => _x( 'Please delete 1 character', 'enhanced select', 'woocommerce-product-vendors' ),
			'i18n_input_too_long_n'     => _x( 'Please delete %qty% characters', 'enhanced select', 'woocommerce-product-vendors' ),
			'i18n_selection_too_long_1' => _x( 'You can only select 1 item', 'enhanced select', 'woocommerce-product-vendors' ),
			'i18n_selection_too_long_n' => _x( 'You can only select %qty% items', 'enhanced select', 'woocommerce-product-vendors' ),
			'i18n_load_more'            => _x( 'Loading more results&hellip;', 'enhanced select', 'woocommerce-product-vendors' ),
			'i18n_searching'            => _x( 'Searching&hellip;', 'enhanced select', 'woocommerce-product-vendors' ),
		);

		wp_localize_script( 'wcpv-admin-scripts', 'wcpv_admin_local', $localized_vars );

		if ( in_array( $current_screen->id, $this->get_screen_ids() ) ) {

			if ( ! WC_Product_Vendors_Utils::is_vendor() ) {
				wp_enqueue_script( 'wcpv-admin-scripts' );
			}

			wp_enqueue_style( 'wcpv-admin-styles' );

			wp_enqueue_script( 'wc-users', WC()->plugin_url() . '/assets/js/admin/users' . $suffix . '.js', array( 'jquery', 'wc-enhanced-select' ), WC_VERSION, true );

			$countries = array_merge( WC()->countries->get_allowed_country_states(), WC()->countries->get_shipping_country_states() );
			$countries = function_exists( 'wc_esc_json' ) ? wc_esc_json( wp_json_encode( $countries ) ) : _wp_specialchars( wp_json_encode( $countries ), ENT_QUOTES, 'UTF-8', true );

			wp_localize_script(
				'wc-users',
				'wc_users_params',
				array(
					'countries'              => $countries,
					'i18n_select_state_text' => esc_attr__( 'Select an option&hellip;', 'woocommerce-product-vendors' ),
				)
			);
		}

		return true;
	}

	/**
	 * Role update / send email
	 *
	 * @access public
	 * @since 2.0.0
	 * @version 2.0.31
	 * @param int $user_id
	 * @param string $new_role
	 * @param array $old_roles
	 * @return bool
	 */
	public function role_update( $user_id, $new_role, $old_roles ) {
		if ( ! current_user_can( 'manage_vendors' ) ) {
			return;
		}

		$vendor_roles = array( 'wc_product_vendors_admin_vendor', 'wc_product_vendors_manager_vendor' );

		$approved_already = get_user_meta( $user_id, '_wcpv_vendor_approval', true );

		// Remove vendor approval if vendor role is changed to non vendor role.
		foreach ( $old_roles as $old_role ) {
			if ( in_array( $old_role, $vendor_roles ) && ! in_array( $new_role, $vendor_roles ) ) {
				delete_user_meta( $user_id, '_wcpv_vendor_approval' );
			}
		}

		if (
			! in_array( $new_role, $old_roles ) &&
			in_array( $new_role, $vendor_roles ) &&
			'yes' !== $approved_already
		) {

			$emails = WC()->mailer()->get_emails();

			if ( ! empty( $emails ) ) {
				$emails['WC_Product_Vendors_Approval']->trigger( $user_id, $new_role, $old_roles );
			}
		}

		// Remove pending new vendor from saved list.
		WC_Product_Vendors_Utils::delete_new_pending_vendor( $user_id );

		return true;
	}

	/**
	 * Shows the pending count bubble on sidebar menu items for pending vendors
	 * and pending product approvals.
	 *
	 * @access public
	 * @since 2.0.0
	 * @version 2.0.31
	 * @param object $menu
	 * @return array $menu modified menu
	 */
	public function pending_items_count( $menu ) {
		// pending vendors
		$users = count_users();

		$pending_vendors_count = ( false !== get_transient( 'wcpv_new_pending_vendor_list' ) ) ? count( get_transient( 'wcpv_new_pending_vendor_list' ) ) : array();

		// draft products from vendors pending review
		$products = wp_count_posts( 'product', 'readable' );

		$pending_products_count = ! empty( $products->pending ) ? $products->pending : '';

		foreach ( $menu as $menu_key => $menu_data ) {
			if ( 'users.php' === $menu_data[2] && ! empty( $pending_vendors_count ) && current_user_can( 'manage_vendors' ) ) {
				$menu[ $menu_key ][0] .= ' <span class="update-plugins count-' . $pending_vendors_count . '" title="' . esc_attr__( 'Vendors awaiting review', 'woocommerce-product-vendors' ) . '"><span class="plugin-count">' . number_format_i18n( $pending_vendors_count ) . '</span></span>';
			}

			if ( 'edit.php?post_type=product' === $menu_data[2] && ! empty( $products->pending ) && current_user_can( 'manage_vendors' ) ) {
				$menu[ $menu_key ][0] .= ' <span class="update-plugins count-' . $pending_products_count . '" title="' . esc_attr__( 'Products awaiting review', 'woocommerce-product-vendors' ) . '"><span class="plugin-count">' . number_format_i18n( $pending_products_count ) . '</span></span>';
			}
		}

		return $menu;
	}

	/**
	 * Perform action when user is deleted.
	 *
	 * @since 2.0.31
	 * @version 2.0.31
	 * @param int $user_id
	 * @return void
	 */
	public function delete_user( $user_id ) {
		WC_Product_Vendors_Utils::delete_new_pending_vendor( $user_id );
	}

	/**
	 * Adds vendor fields to vendor create page
	 *
	 * @access public
	 * @since 2.0.0
	 * @version 2.0.16
	 * @return bool
	 */
	public function add_vendor_fields() {
		$tzstring = WC_Product_Vendors_Utils::get_default_timezone_string();

		include_once( 'views/html-create-vendor-fields-page.php' );

		return true;
	}

	/**
	 * Adds additional fields for product vendor term
	 *
	 * @access public
	 * @since 2.0.0
	 * @version 2.0.16
	 * @param object $taxonomy
	 * @return bool
	 */
	public function edit_vendor_fields( $term ) {
		wp_enqueue_script( 'wc-enhanced-select' );
		wp_enqueue_script( 'jquery-tiptip' );

		$vendor_data = get_term_meta( $term->term_id, 'vendor_data', true );

		$description          = ! empty( $vendor_data['description'] ) ? $vendor_data['description'] : '';
		$notes                = ! empty( $vendor_data['notes'] ) ? $vendor_data['notes'] : '';
		$logo                 = ! empty( $vendor_data['logo'] ) ? $vendor_data['logo'] : '';
		$profile              = ! empty( $vendor_data['profile'] ) ? $vendor_data['profile'] : '';
		$email                = ! empty( $vendor_data['email'] ) ? $vendor_data['email'] : '';
		$commission           = is_numeric( $vendor_data['commission'] ) ? $vendor_data['commission'] : '';
		$commission_type      = ! empty( $vendor_data['commission_type'] ) ? $vendor_data['commission_type'] : 'percentage';
		$instant_payout       = ! empty( $vendor_data['instant_payout'] ) ? $vendor_data['instant_payout'] : 'no';
		$paypal               = ! empty( $vendor_data['paypal'] ) ? $vendor_data['paypal'] : '';
		$per_product_shipping = ! empty( $vendor_data['per_product_shipping'] ) ? $vendor_data['per_product_shipping'] : 'no';
		$enable_bookings      = ! empty( $vendor_data['enable_bookings'] ) ? $vendor_data['enable_bookings'] : 'no';
		$admins               = ! empty( $vendor_data['admins'] ) ? $vendor_data['admins'] : array();
		$tzstring             = ! empty( $vendor_data['timezone'] ) ? $vendor_data['timezone'] : '';
		$pass_shipping        = ! empty( $vendor_data['pass_shipping'] ) ? $vendor_data['pass_shipping'] : 'no';
		$taxes                = ! empty( $vendor_data['taxes'] ) ? $vendor_data['taxes'] : 'keep-tax';

		$selected_admins = array();

		if ( empty( $tzstring ) ) {
			$tzstring = WC_Product_Vendors_Utils::get_default_timezone_string();
		}

		if ( ! empty( $admins ) ) {
			if ( version_compare( WC_VERSION, '3.0.0', '>=' ) && is_array( $vendor_data['admins'] ) ) {
				$admin_ids = array_map( 'absint', $vendor_data['admins'] );
			} else {
				$admin_ids = array_filter( array_map( 'absint', explode( ',', $vendor_data['admins'] ) ) );
			}

			foreach ( $admin_ids as $admin_id ) {
				$admin = get_user_by( 'id', $admin_id );

				if ( is_object( $admin ) ) {
					$selected_admins[ $admin_id ] = esc_html( $admin->display_name ) . ' (#' . absint( $admin->ID ) . ') &ndash; ' . esc_html( $admin->user_email );
				}
			}
		}

		$hide_remove_image_link = '';

		$logo_image_url = wp_get_attachment_image_src( $logo, 'thumbnail' );

		if ( empty( $logo_image_url ) ) {
			$hide_remove_image_link = 'display:none;';
		}

		include_once( 'views/html-edit-vendor-fields-page.php' );

		return true;
	}

	/**
	 * Saves additional fields for product vendor term
	 *
	 * @since 2.0.0
	 * @version 2.0.0
	 * @param object $term_id
	 * @return bool
	 */
	public function save_vendor_fields( $term_id ) {
		if ( ! empty( $_POST['vendor_data'] ) ) {
			$posted_vendor_data    = $_POST['vendor_data'];
			$sanitized_vendor_data = array();

			foreach ( $posted_vendor_data as $data_key => $data_value ) {
				if ( version_compare( WC_VERSION, '3.0.0', '>=' ) ) {
					// Previous to WC 3.0, Select 2 needed saved values as string.
					// Now no longer so we sanitize it as multidimensional array.
					if ( 'admins' === $data_key ) {
						$sanitized_vendor_data[ $data_key ] = array_map( 'absint', $data_value );

						continue;
					}
				}

				if ( 'description' === $data_key ) {
					$sanitized_vendor_data[ $data_key ] = ! empty( $data_value ) ? sanitize_text_field( $data_value ) : '';

					continue;
				}

				if ( 'profile' === $data_key ) {
					// Sanitize html editor content.
					$sanitized_vendor_data[ $data_key ] = ! empty( $data_value ) ? wp_kses_post( $data_value ) : '';

					continue;
				}

				if ( 'commission' === $data_key ) {
					// validate commission as it takes an absolute number
					$sanitized_vendor_data[ $data_key ] = WC_Product_Vendors_Utils::sanitize_commission( $data_value );

					continue;
				}

				$sanitized_vendor_data[ $data_key ] = sanitize_text_field( $data_value );
				$sanitized_vendor_data[ $data_key ] = stripslashes( $data_value );
			}

			$sanitized_vendor_data['pass_shipping'] = ! isset( $posted_vendor_data['pass_shipping'] ) ? 'no' : 'yes';

			// account for checkbox fields
			$sanitized_vendor_data['enable_bookings'] = ! isset( $posted_vendor_data['enable_bookings'] ) ? 'no' : 'yes';

			// account for checkbox fields
			$sanitized_vendor_data['per_product_shipping'] = ! isset( $posted_vendor_data['per_product_shipping'] ) ? 'no' : 'yes';

			// account for checkbox fields
			$sanitized_vendor_data['instant_payout'] = ! isset( $posted_vendor_data['instant_payout'] ) ? 'no' : 'yes';

			if ( version_compare( WC_VERSION, '3.0.0', '>=' ) && empty( $posted_vendor_data['admins'] ) ) {
				$sanitized_vendor_data['admins'] = array();
			}

			update_term_meta( $term_id, 'vendor_data', $sanitized_vendor_data );
		}

		return true;
	}

	/**
	 * Modifies the vendor columns
	 *
	 * @access public
	 * @since 2.0.0
	 * @version 2.0.0
	 * @return array $columns modified columns
	 */
	public function modify_vendor_columns( $columns ) {
		unset( $columns['description'] );

		// add admins to column
		$columns['admins'] = __( 'Admins', 'woocommerce-product-vendors' );

		// rename count column
		$columns['posts'] = __( 'Products', 'woocommerce-product-vendors' );

		// add notes to column
		$columns['notes'] = __( 'Notes', 'woocommerce-product-vendors' );

		// add vendor id to column
		$columns['vendor_id'] = __( 'Vendor ID', 'woocommerce-product-vendors' );

		return $columns;
	}

	/**
	 * Renders the modified vendor column
	 *
	 * @access public
	 * @since 2.0.0
	 * @version 2.0.0
	 * @param string $value current value
	 * @param string $column_name current column name
	 * @param int $term_id current term id
	 * @return string $value
	 */
	public function render_vendor_columns( $value, $column_name, $term_id ) {
		$vendor_data = get_term_meta( $term_id, 'vendor_data', true );

		if ( 'vendor_id' === $column_name ) {
			$value .= esc_html( $term_id );
		}

		if ( 'admins' === $column_name && ! empty( $vendor_data['admins'] ) ) {
			if ( version_compare( WC_VERSION, '3.0.0', '>=' ) && is_array( $vendor_data['admins'] ) ) {
				$admin_ids = array_map( 'absint', $vendor_data['admins'] );
			} else {
				$admin_ids = array_filter( array_map( 'absint', explode( ',', $vendor_data['admins'] ) ) );
			}

			foreach ( $admin_ids as $admin_id ) {
				$admin = get_user_by( 'id', $admin_id );

				if ( is_object( $admin ) ) {
					$value .= '<a href="' . get_edit_user_link( $admin_id ) . '" class="wcpv-vendor-column-user">' . esc_html( $admin->display_name ) . ' (#' . absint( $admin->ID ) . ' &ndash; ' . esc_html( $admin->user_email ) . ')</a><br />';
				}
			}
		}

		if ( 'notes' === $column_name && ! empty( $vendor_data['notes'] ) ) {
			$value .= esc_html( $vendor_data['notes'] );
		}

		return $value;
	}

	/**
	 * Add column to the user taxonomy columns
	 *
	 * @access public
	 * @since 2.0.0
	 * @version 2.0.0
	 * @param array $columns
	 * @return bool
	 */
	public function add_custom_user_column( $columns ) {
		$columns['vendors'] = __( 'Managed Vendors', 'woocommerce-product-vendors' );

		return $columns;
	}

	/**
	 * Modifies the user taxonomy columns
	 *
	 * @access public
	 * @since 2.0.0
	 * @version 2.0.0
	 * @param string $content
	 * @return string $vendors
	 */
	public function add_user_column_data( $content, $column_slug, $user_id ) {
		$vendor_data = WC_Product_Vendors_Utils::get_all_vendor_data( $user_id );
		$vendors = '';

		if ( 'vendors' === $column_slug && ! empty( $vendor_data ) ) {
			$vendor_names = array();

			foreach ( $vendor_data as $data ) {
				$vendor_names[] = $data['name'];
			}

			$vendors = implode( '<br />', $vendor_names );
		}

		return $vendors;
	}

	/**
	 * Add vendor section fields to user profile
	 *
	 * @access public
	 * @since 2.0.0
	 * @version 2.1.0
	 * @param object $user
	 * @return bool
	 */
	public function add_product_vendor_user_profile_section( $user ) {
		// display section only if current user is an admin and the editing user is a vendor
		if ( ! WC_Product_Vendors_Utils::is_vendor( $user->ID ) || ! current_user_can( 'manage_vendors' ) ) {
			return;
		}

		$publish_products = 'disallow';
		$manage_customers = 'disallow';

		// check for user publish products capability
		if ( $user->has_cap( 'publish_products' ) ) {
			$publish_products = 'allow';
		}

		// check for create users capability
		if ( $user->has_cap( 'create_users' ) && $user->has_cap( 'edit_users' ) ) {
			$manage_customers = 'allow';
		}

		include_once( 'views/html-edit-user-profile-page.php' );

		return true;
	}

	/**
	 * Save vendor section fields to user profile
	 *
	 * @access public
	 * @since 2.0.0
	 * @version 2.0.0
	 * @param object $user
	 * @return bool
	 */
	public function save_product_vendor_user_profile_section( $user_id ) {
		$publish_products = ! empty( $_POST['wcpv_publish_products'] ) ? sanitize_text_field( $_POST['wcpv_publish_products'] ) : 'disallow';
		$manage_customers = ! empty( $_POST['wcpv_manage_customers'] ) ? sanitize_text_field( $_POST['wcpv_manage_customers'] ) : 'disallow';

		$roles_caps = new WC_Product_Vendors_Roles_Caps;

		// update user capability
		if ( 'disallow' === $publish_products ) {
			$roles_caps->remove_publish_products( $user_id );
		} else {
			$roles_caps->add_publish_products( $user_id );
		}

		// update user capability
		if ( 'disallow' === $manage_customers ) {
			$roles_caps->remove_manage_users( $user_id );
		} else {
			$roles_caps->add_manage_users( $user_id );
		}

		return true;
	}

	/**
	 * Register the commission menu item
	 *
	 * @access public
	 * @since 2.0.0
	 * @version 2.0.0
	 * @return bool
	 */
	public function register_commissions_menu_item() {
		$hook = add_menu_page( __( 'Vendor Commission', 'woocommerce-product-vendors' ), __( 'Commission', 'woocommerce-product-vendors' ), 'manage_vendors', 'wcpv-commissions', array( $this, 'render_commission_page' ), 'dashicons-chart-pie', 56.77 );

		add_action( "load-$hook", array( $this, 'add_screen_options' ) );

		return true;
	}

	/**
	 * Adds screen options for this page
	 *
	 * @access public
	 * @since 2.0.0
	 * @version 2.0.0
	 * @return bool
	 */
	public function add_screen_options() {
		$option = 'per_page';

		$args = array(
			'label'   => __( 'Commissions', 'woocommerce-product-vendors' ),
			'default' => apply_filters( 'wcpv_commission_list_default_item_per_page', 20 ),
			'option'  => 'commissions_per_page',
		);

		add_screen_option( $option, $args );

		return true;
	}

	/**
	 * Sets screen options for this page
	 *
	 * @access public
	 * @since 2.0.0
	 * @version 2.0.0
	 * @return mixed
	 */
	public function set_screen_option( $status, $option, $value ) {
		if ( 'commissions_per_page' === $option ) {
			return $value;
		}

		return $status;
	}

	/**
	 * Renders the commission page
	 *
	 * @access public
	 * @since 2.0.0
	 * @version 2.0.0
	 * @return bool
	 */
	public function render_commission_page() {
		$this->maybe_render_bulk_update_notifications();
		$commissions_list = new WC_Product_Vendors_Store_Admin_Commission_List( new WC_Product_Vendors_Commission( new WC_Product_Vendors_PayPal_MassPay ) );

		$commissions_list->prepare_items();
	?>
		<div class="wrap">

			<h2><?php esc_html_e( 'Vendor Commission', 'woocommerce-product-vendors' ); ?>
				<?php
				if ( ! empty( $_REQUEST['s'] ) ) {
					echo '<span class="subtitle">' . esc_html__( 'Search results for', 'woocommerce-product-vendors' ) . ' "' . sanitize_text_field( $_REQUEST['s'] ) . '"</span>';
				}
				?>
			</h2>

			<?php $commissions_list->views(); ?>

			<form id="wcpv-commission-list" action="" method="get">
				<input type="hidden" name="page" value="wcpv-commissions" />
				<?php $commissions_list->search_box( esc_html__( 'Search Order #', 'woocommerce-product-vendors' ), 'search_id' ); ?>
				<?php $commissions_list->display(); ?>
			</form>
		</div>
	<?php
		return true;
	}

	/**
	 * After performing bulk updates, show a notification to the admin.
	 *
	 * @since  2.1.38
	 * @return void
	 */
	private function maybe_render_bulk_update_notifications() {
		if ( ! empty( $_REQUEST['processed'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$processed               = intval( $_REQUEST['processed'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$items_processed_message = sprintf( _n( '%d item processed.', '%d items processed', $processed, 'woocommerce-product-vendors' ), $processed );
			WC_Admin_Settings::add_message( $items_processed_message );

			if ( ! empty( $_REQUEST['pay'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$paid_status_message = esc_html__( 'Paid status will be updated in a few minutes.', 'woocommerce-product-vendors' );
				WC_Admin_Settings::add_message( $paid_status_message );
			}
			WC_Admin_Settings::show_messages();
		}
	}

	/**
	 * Adds extra vendor field to attachment so we know who the attachment belongs to
	 *
	 * @access public
	 * @since 2.0.0
	 * @version 2.0.0
	 * @param array $form_fields
	 * @param object $post
	 * @return array $form_fields
	 */
	public function add_attachments_field( $form_fields, $post ) {
		if ( ! WC_Product_Vendors_Utils::is_vendor() && current_user_can( 'manage_vendors' ) ) {
			$post_vendor = get_post_meta( $post->ID, '_wcpv_vendor', true );

			$form_fields['vendor'] = array(
				'label' => __( 'Vendor', 'woocommerce-product-vendors' ),
				'input' => 'text',
				'value' => $post_vendor,
			);
		}

		return $form_fields;
	}

	/**
	 * Saves attachment extra fields
	 *
	 * @access public
	 * @since 2.0.0
	 * @version 2.0.0
	 * @param object $post
	 * @param array $data
	 * @return array $post
	 */
	public function save_attachments_field( $post, $data ) {
		if ( ! WC_Product_Vendors_Utils::is_vendor() && current_user_can( 'manage_vendors' ) ) {
			if ( ! empty( $data['vendor'] ) ) {
				// save vendor id to attachment post meta
				update_post_meta( $post['ID'], '_wcpv_vendor', absint( $data['vendor'] ) );
			}
		}

		return $post;
	}

	/**
	 * Add vendor settings section to products tab
	 *
	 * @access public
	 * @since 2.0.0
	 * @version 2.0.0
	 * @param array $sections existing sections
	 * @return array $sections modified sections
	 */
	public function add_vendor_settings_section( $sections ) {
		$sections['wcpv_vendor_settings'] = __( 'Vendors', 'woocommerce-product-vendors' );

		return $sections;
	}

	/**
	 * Add vendor settings to vendor settings section
	 *
	 * @access public
	 * @since 2.0.0
	 * @version 2.0.0
	 * @param array $settings existing settings
	 * @param string $current_section current section name
	 * @return array $settings
	 */
	public function add_vendor_settings( $settings, $current_section ) {
		if ( 'wcpv_vendor_settings' === $current_section ) {
			$new_settings = array(
				array(
					'title'    => __( 'Payments', 'woocommerce-product-vendors' ),
					'id'       => 'wcpv_vendor_settings_payments',
					'type'     => 'title',
				),

				array(
					'title'    => __( 'Payout Schedule', 'woocommerce-product-vendors' ),
					'desc'     => __( 'Choose the frequency of commission payout for vendors.  Any commission that is unpaid will follow the schedule set here.  Note that by saving this option, a payout will initiate now and recur based on your settings from today\'s date.', 'woocommerce-product-vendors' ),
					'id'       => 'wcpv_vendor_settings_payout_schedule',
					'default'  => 'manual',
					'type'     => 'select',
					'options'  => array(
						'manual'    => __( 'Manual', 'woocommerce-product-vendors' ),
						'weekly'    => __( 'Weekly', 'woocommerce-product-vendors' ),
						'biweekly'  => __( 'Bi-Weekly', 'woocommerce-product-vendors' ),
						'monthly'   => __( 'Monthly', 'woocommerce-product-vendors' ),
					),
					'desc_tip' => true,
					'autoload' => false,
				),

				array(
					'title'         => __( 'PayPal Payouts Environment', 'woocommerce-product-vendors' ),
					'id'            => 'wcpv_vendor_settings_paypal_masspay_environment',
					'desc'          => __( 'PayPal Payouts sandbox mode can be used to test payouts.  You will need REST API app credentials for this.  Please refer to <a href="https://developer.paypal.com/developer/applications/" target="_blank" title="PayPal Documentation">PayPal Documentation</a>', 'woocommerce-product-vendors' ),
					'type'          => 'select',
					'default'       => 'sandbox',
					'options'       => array(
						'sandbox' => __( 'Sandbox', 'woocommerce-product-vendors' ),
						'live'    => __( 'Live', 'woocommerce-product-vendors' ),
					),
					'autoload'      => false,
				),

				array(
					'title'    => __( '(Sandbox) PayPal Payouts API Client ID', 'woocommerce-product-vendors' ),
					'desc'     => __( 'Enter the API Client ID.', 'woocommerce-product-vendors' ),
					'id'       => 'wcpv_vendor_settings_paypal_masspay_client_id_sandbox',
					'default'  => '',
					'type'     => 'text',
					'autoload' => false,
				),

				array(
					'title'    => __( '(Sandbox) PayPal Payouts API Secret', 'woocommerce-product-vendors' ),
					'desc'     => __( 'Enter the API Client Secret.', 'woocommerce-product-vendors' ),
					'id'       => 'wcpv_vendor_settings_paypal_masspay_client_secret_sandbox',
					'default'  => '',
					'type'     => 'text',
					'autoload' => false,
				),

				array(
					'title'    => __( 'PayPal Payouts API Client ID', 'woocommerce-product-vendors' ),
					'desc'     => __( 'Enter the API Client ID.', 'woocommerce-product-vendors' ),
					'id'       => 'wcpv_vendor_settings_paypal_masspay_client_id_live',
					'default'  => '',
					'type'     => 'text',
					'autoload' => false,
				),

				array(
					'title'    => __( 'PayPal Payouts API Client Secret', 'woocommerce-product-vendors' ),
					'desc'     => __( 'Enter the API Client Secret.', 'woocommerce-product-vendors' ),
					'id'       => 'wcpv_vendor_settings_paypal_masspay_client_secret_live',
					'default'  => '',
					'type'     => 'text',
					'autoload' => false,
				),

				array(
					'title'    => __( 'Default Commission', 'woocommerce-product-vendors' ),
					'desc'     => __( 'Enter a default commission that works globally for all vendors as a fallback if commission is not set per vendor level.  Enter a positive number.', 'woocommerce-product-vendors' ),
					'id'       => 'wcpv_vendor_settings_default_commission',
					'default'  => '0',
					'type'     => 'number',
					'desc_tip' => true,
					'autoload' => false,
					'custom_attributes' => array(
						'step' => 'any',
						'min'  => '0',
					),
				),

				array(
					'title'    => __( 'Commission Type', 'woocommerce-product-vendors' ),
					'desc'     => __( 'Choose whether the commission amount will be a fixed amount or a percentage of the cost.', 'woocommerce-product-vendors' ),
					'id'       => 'wcpv_vendor_settings_default_commission_type',
					'default'  => 'percentage',
					'type'     => 'select',
					'options'       => array(
						'percentage' => __( 'Percentage', 'woocommerce-product-vendors' ),
						'fixed'      => __( 'Fixed', 'woocommerce-product-vendors' ),
					),
					'autoload' => false,
				),

				array(
					'title'    => __( 'Logging', 'woocommerce-product-vendors' ),
					'desc'     => __( 'Enable this to log debug messages. Useful for troubleshooting.', 'woocommerce-product-vendors' ),
					'desc_tip' => __( 'Note: this may log personal information. We recommend using this for debugging purposes only and deleting the logs when finished.', 'woocommerce-shipping-stamps' ),
					'id'       => 'wcpv_vendor_settings_logging',
					'type'     => 'checkbox',
					'default'  => 'no',
				),

				array(
					'type' 	=> 'sectionend',
					'id' 	=> 'wcpv_vendor_settings_payments',
				),

				array(
					'title'    => __( 'Display', 'woocommerce-product-vendors' ),
					'id'       => 'wcpv_vendor_settings_display',
					'type'     => 'title',
				),

				array(
					'title'         => __( 'Show [Sold By]', 'woocommerce-product-vendors' ),
					'desc'          => __( 'Enable this to show [Sold By Vendor Name] for each product.', 'woocommerce-product-vendors' ),
					'id'            => 'wcpv_vendor_settings_display_show_by',
					'type'          => 'checkbox',
					'default'       => 'yes',
					'autoload'      => false,
				),

				array(
					'title'         => __( 'Show Vendor Review', 'woocommerce-product-vendors' ),
					'desc'          => __( 'Enable this to show vendor\'s overall review rating on vendor\'s page.', 'woocommerce-product-vendors' ),
					'id'            => 'wcpv_vendor_settings_vendor_review',
					'type'          => 'checkbox',
					'default'       => 'yes',
					'autoload'      => false,
				),

				array(
					'title'         => __( 'Show Vendor Logo', 'woocommerce-product-vendors' ),
					'desc'          => __( 'Enable this to show vendor\'s logo on vendor\'s page.', 'woocommerce-product-vendors' ),
					'id'            => 'wcpv_vendor_settings_vendor_display_logo',
					'type'          => 'checkbox',
					'default'       => 'yes',
					'autoload'      => false,
				),

				array(
					'title'         => __( 'Show Vendor Profile', 'woocommerce-product-vendors' ),
					'desc'          => __( 'Enable this to show vendor\'s profile on vendor\'s page.', 'woocommerce-product-vendors' ),
					'id'            => 'wcpv_vendor_settings_vendor_display_profile',
					'type'          => 'checkbox',
					'default'       => 'yes',
					'autoload'      => false,
				),

				array(
					'type' 	=> 'sectionend',
					'id' 	=> 'wcpv_vendor_settings_display',
				),
			);

			$settings = apply_filters( 'wcpv_vendor_settings', $new_settings );
		}

		return $settings;
	}

	/**
	 * Save vendor general/global settings
	 *
	 * @access public
	 * @since 2.0.0
	 * @version 2.0.0
	 * @return bool
	 */
	public function save_vendor_settings( $value, $option, $raw_value ) {
		global $current_section;

		if ( 'wcpv_vendor_settings' !== $current_section ) {
			return $value;
		}

		if ( 'wcpv_vendor_settings_default_commission' === $option['id'] ) {
			return WC_Product_Vendors_Utils::sanitize_commission( $value );
		}

		return $value;
	}

	/**
	 * Add sold by vendor to order item
	 *
	 * @access public
	 * @since 2.0.0
	 * @version 2.0.0
	 * @return bool
	 */
	public function add_sold_by_order_item_detail( $item_id, $item, $product ) {
		$sold_by = get_option( 'wcpv_vendor_settings_display_show_by', 'yes' );

		if ( 'yes' === $sold_by && ! empty( $item['product_id'] ) && WC_Product_Vendors_Utils::is_vendor_product( $item['product_id'] ) ) {

			$sold_by = WC_Product_Vendors_Utils::get_sold_by_link( $item['product_id'] );

			echo '<em class="wcpv-sold-by-order-details">' . apply_filters( 'wcpv_sold_by_text', __( 'Sold By:', 'woocommerce-product-vendors' ) ) . ' <a href="' . esc_url( $sold_by['link'] ) . '" title="' . esc_attr( $sold_by['name'] ) . '">' . $sold_by['name'] . '</a></em>';
		}
	}

	/**
	 * Add a commission field to the product general tab
	 *
	 * @access public
	 * @since 2.0.0
	 * @version 2.0.0
	 * @return bool
	 */
	public function add_product_commission_field_general() {
		if ( ! WC_Product_Vendors_Utils::is_vendor() && current_user_can( 'manage_vendors' ) ) {
			global $post;

			$vendor_id        = WC_Product_Vendors_Utils::get_vendor_id_from_product( $post->ID );
			$vendor_data      = WC_Product_Vendors_Utils::get_vendor_data_by_id( $vendor_id );
			$commission_data  = WC_Product_Vendors_Utils::get_product_commission( $post->ID, $vendor_data );

			$commission_placeholder = ! empty( $commission_data['commission'] ) ? $commission_data['commission'] : '';

			$commission_type = __( 'Fixed', 'woocommerce-product-vendors' );

			if ( 'percentage' === $commission_data['type'] ) {
				$commission_type = '%';
			}

			echo '<div class="options_group show_if_simple show_if_variable show_if_booking">';

			woocommerce_wp_text_input( array(
				'id'                => '_wcpv_product_commission',
				'label'             => sprintf( __( 'Commission %s:', 'woocommerce-product-vendors' ), '(' . $commission_type . ')' ),
				'desc_tip'          => 'true',
				'description'       => __( 'Enter a default commission for this product. Enter a positive number.', 'woocommerce-product-vendors' ),
				'placeholder'       => $commission_placeholder,
				'type'              => 'number',
				'custom_attributes' => array( 'step' => 'any', 'min' => '0' ),
			) );

			echo '</div>';
		}

		return true;
	}

	/**
	 * Save the commission field for the product general tab
	 *
	 * @access public
	 * @since 2.0.0
	 * @version 2.0.0
	 * @param int $post_id
	 * @return bool
	 */
	public function save_product_commission_field_general( $post_id ) {
		if ( ! WC_Product_Vendors_Utils::is_vendor() && current_user_can( 'manage_vendors' ) ) {
			if ( empty( $post_id ) ) {
				return;
			}

			$commission = WC_Product_Vendors_Utils::sanitize_commission( $_POST['_wcpv_product_commission'] );

			update_post_meta( $post_id, '_wcpv_product_commission', $commission );
		}

		return true;
	}

	/**
	 * Add a commission field to the product variation
	 *
	 * @access public
	 * @since 2.0.0
	 * @version 2.0.0
	 * @param int $loop
	 * @return bool
	 */
	public function add_product_commission_field_variation( $loop, $variation_data, $variation ) {
		if ( ! WC_Product_Vendors_Utils::is_vendor() && current_user_can( 'manage_vendors' ) ) {
			$commission = get_post_meta( $variation->ID, '_wcpv_product_commission', true );

			global $post;

			$vendor_id        = WC_Product_Vendors_Utils::get_vendor_id_from_product( $post->ID );
			$vendor_data      = WC_Product_Vendors_Utils::get_vendor_data_by_id( $vendor_id );
			$commission_data  = WC_Product_Vendors_Utils::get_product_commission( $post->ID, $vendor_data );

			$commission_placeholder = ! empty( $commission_data['commission'] ) ? $commission_data['commission'] : '';

			$commission_type = __( 'Fixed', 'woocommerce-product-vendors' );

			if ( 'percentage' === $commission_data['type'] ) {
				$commission_type = '%';
			}

			echo '<div class="options_group show_if_variable show_if_booking">';
			?>
			<p class="wcpv-commission form-row form-row-first">
				<label><?php echo esc_html__( 'Commission', 'woocommerce-product-vendors' ) . ' (' . $commission_type . ')'; ?>: <?php echo wc_help_tip( __( 'Enter a commission for this product variation.  Enter a positive number.', 'woocommerce-product-vendors' ) ); ?></label>

				<input type="number" name="_wcpv_product_variation_commission[<?php echo $loop; ?>]" value="<?php echo esc_attr( $commission ); ?>" placeholder="<?php echo esc_attr( $commission_placeholder ); ?>" step="any" min="0" />
			</p>
			<?php
			echo '</div>';
		}

		return true;
	}

	/**
	 * Save the commission field for the product variable
	 *
	 * @access public
	 * @since 2.0.0
	 * @version 2.0.0
	 * @param int $product_id
	 * @return bool
	 */
	public function save_product_commission_field_variable( $product_id ) {
		if ( ! WC_Product_Vendors_Utils::is_vendor() && current_user_can( 'manage_vendors' ) ) {
			if ( empty( $product_id ) ) {
				return;
			}

			$commission = WC_Product_Vendors_Utils::sanitize_commission( $_POST['_wcpv_product_commission'] );

			update_post_meta( $product_id, '_wcpv_product_commission', $commission );
		}

		return true;
	}

	/**
	 * Save the commission field for the product variation
	 *
	 * @access public
	 * @since 2.0.0
	 * @version 2.0.0
	 * @param int $variation_id
	 * @param int $i loop count
	 * @return bool
	 */
	public function save_product_commission_field_variation( $variation_id, $i ) {
		if ( ! WC_Product_Vendors_Utils::is_vendor() && current_user_can( 'manage_vendors' ) ) {
			if ( empty( $variation_id ) ) {
				return;
			}

			$commission = WC_Product_Vendors_Utils::sanitize_commission( $_POST['_wcpv_product_variation_commission'][ $i ] );

			update_post_meta( $variation_id, '_wcpv_product_commission', $commission );
		}

		return true;
	}

	/**
	 * Add a pass shipping field to the product general tab
	 *
	 * @access public
	 * @since 2.0.0
	 * @version 2.0.0
	 * @return bool
	 */
	public function add_product_pass_shipping_tax_field_general() {
		/**
		 * @var WC_Product $product_object
		 */
		global $product_object;

		$vendor_id = WC_Product_Vendors_Utils::get_vendor_id_from_product( $product_object->get_id() );

		if ( $vendor_id ) {
			$vendor_data = WC_Product_Vendors_Utils::get_vendor_data_by_id( $vendor_id );
		} else {
			$vendor_data = null;
		}
		$product_settings = WC_Product_Vendors_Utils::get_product_vendor_settings( $product_object, $vendor_data );

		if ( ! WC_Product_Vendors_Utils::is_vendor() && current_user_can( 'manage_vendors' ) ) {
			?>
			<div class="options_group show_if_simple show_if_variable show_if_booking">
				<h2>Product Vendors</h2>
				<p class="form-field wcpv_product_default_pass_shipping_tax_field">
					<label for="wcpv_product_pass_shipping">
						<?php esc_html_e( 'Pass shipping', 'woocommerce-product-vendors' ) ?>
					</label>
					<input type="checkbox" name="_wcpv_product_pass_shipping" id="wcpv_product_pass_shipping" value="yes" <?php checked( 'yes', $product_settings['pass_shipping'] ); ?> />
					<span class="description">
						<?php esc_html_e( 'Check box to pass the shipping charges for this product to the vendor.', 'woocommerce-product-vendors' ); ?>
					</span>
				</p>
				<h2>Tax Handling</h2>
				<p class="form-field wcpv_product_taxes_field">
					<label for="wcpv-keep-tax">
						<?php esc_html_e( 'Keep Taxes', 'woocommerce-product-vendors' ); ?>
					</label>
					<input type="radio" value="keep-tax" id="wcpv-keep-tax" name="_wcpv_product_taxes" <?php checked( 'keep-tax', $product_settings['taxes'] ); ?> />
					<span class="description">
						<?php esc_html_e( 'Calculate commission based on product price only.', 'woocommerce-product-vendors' ); ?>
					</span>
				</p>
				<p class="form-field wcpv_product_taxes_field">
					<label for="wcpv-pass-tax">
						<?php esc_html_e( 'Pass Taxes', 'woocommerce-product-vendors' ); ?>
					</label>
					<input type="radio" value="pass-tax" id="wcpv-pass-tax" name="_wcpv_product_taxes" <?php checked( 'pass-tax', $product_settings['taxes'] ); ?> />
					<span class="description">
						<?php esc_html_e( 'All tax charges will be included in the vendor\'s commission.', 'woocommerce-product-vendors' ); ?>
					</span>
				</p>
				<p class="form-field wcpv_product_taxes_field">
					<label for="wcpv-split-tax">
						<?php esc_html_e( 'Split Taxes', 'woocommerce-product-vendors' ); ?>
					</label>
					<input type="radio" value="split-tax" id="wcpv-split-tax" name="_wcpv_product_taxes" <?php checked( 'split-tax', $product_settings['taxes'] ); ?> />
					<span class="description">
						<?php esc_html_e( 'The full price including taxes will be used to calculate commission.', 'woocommerce-product-vendors' ); ?>
					</span>
				</p>
			</div>
			<?php
		}

		return true;
	}

	/**
	 * Save the pass shipping field for the product general tab
	 *
	 * @since 2.0.0
	 * @version 2.0.0
	 * @param int $post_id
	 */
	public function save_product_pass_shipping_tax_field_general( $post_id ) {
		if ( empty( $post_id ) ) {
			return;
		}

		if ( ! WC_Product_Vendors_Utils::is_vendor() && current_user_can( 'manage_vendors' ) ) {

			$product = wc_get_product( $post_id );
			$product->update_meta_data(
				'_wcpv_product_pass_shipping',
				! empty( $_POST['_wcpv_product_pass_shipping'] ) ? 'yes' : 'no'
			);
			$product->update_meta_data(
				'_wcpv_product_pass_tax',
				! empty( $_POST['_wcpv_product_pass_tax'] ) ? 'yes' : 'no'
			);
			$product->update_meta_data(
				'_wcpv_product_split_tax',
				! empty( $_POST['_wcpv_product_split_tax'] ) ? 'yes' : 'no'
			);
			$product->save();
		}
	}

	/**
	 * Adds bulk edit action for vendors
	 *
	 * @access public
	 * @since 2.0.0
	 * @version 2.0.0
	 * @return bool
	 */
	public function add_variation_vendor_bulk_edit() {
		if ( ! WC_Product_Vendors_Utils::is_vendor() && current_user_can( 'manage_vendors' ) ) {
	?>
			<optgroup label="<?php esc_attr_e( 'Vendor', 'woocommerce-product-vendors' ); ?>">
				<option value="variable_vendor_commission"><?php esc_html_e( 'Commission', 'woocommerce-product-vendors' ); ?></option>
			</optgroup>
	<?php
		}
	}

	/**
	 * Ajax search for vendors
	 *
	 * @access public
	 * @since 2.0.0
	 * @version 2.0.0
	 * @return json $found_vendors
	 */
	public function vendor_search_ajax() {
		$nonce = $_GET['security'];

		// bail if nonce don't check out
		if ( ! wp_verify_nonce( $nonce, '_wcpv_vendor_search_nonce' ) ) {
		     wp_die( __( 'Cheatin&#8217; huh?', 'woocommerce-product-vendors' ) );
		}

		if ( empty( $_GET['term'] ) ) {
			wp_die( __( 'Cheatin&#8217; huh?', 'woocommerce-product-vendors' ) );
		}

		if ( version_compare( WC_VERSION, '3.0.0', '>=' ) ) {
			$term = (string) wc_clean( stripslashes( $_GET['term']['term'] ) );
		} else {
			$term = (string) wc_clean( stripslashes( $_GET['term'] ) );
		}

		$args = array(
			'hide_empty' => false,
			'name__like' => $term,
		);

		$vendor_terms = get_terms( WC_PRODUCT_VENDORS_TAXONOMY, $args );

		$found_vendors = array();

		if ( ! empty( $vendor_terms ) ) {
			foreach ( $vendor_terms as $term ) {
				$found_vendors[ $term->term_id ] = $term->name;
			}
		}

		wp_send_json( $found_vendors );
	}

	/**
	 * Add pass shipping/tax setting to product bulk edit menu
	 *
	 * @access public
	 * @since 2.0.0
	 * @version 2.0.0
	 * @return bool
	 */
	public function add_product_bulk_edit_pass_shipping_tax() {
		?>
		<label>
			<span class="title"><?php esc_html_e( 'Pass Shipping to Vendor?', 'woocommerce-product-vendors' ); ?></span>
				<span class="input-text-wrap">
					<select class="pass-shipping-tax" name="_wcpv_product_pass_shipping">
					<?php
					$options = array(
						''    => __( '— No Change —', 'woocommerce-product-vendors' ),
						'yes' => __( 'Yes', 'woocommerce-product-vendors' ),
						'no'  => __( 'No', 'woocommerce-product-vendors' ),
					);

					foreach ( $options as $key => $value ) {
						echo '<option value="' . esc_attr( $key ) . '">' . esc_html( $value ) . '</option>';
					}
					?>
				</select>
			</span>
		</label>
		<label>
			<span class="title"><?php esc_html_e( 'Pass Tax to Vendor?', 'woocommerce-product-vendors' ); ?></span>
			<span class="input-text-wrap">
					<select class="pass-shipping-tax" name="_wcpv_product_pass_tax">
					<?php
					$options = array(
						''    => __( '— No Change —', 'woocommerce-product-vendors' ),
						'yes' => __( 'Yes', 'woocommerce-product-vendors' ),
						'no'  => __( 'No', 'woocommerce-product-vendors' ),
					);

					foreach ( $options as $key => $value ) {
						echo '<option value="' . esc_attr( $key ) . '">' . esc_html( $value ) . '</option>';
					}
					?>
				</select>
			</span>
		</label>
		<label>
			<span class="title"><?php esc_html_e( 'Inclined Tax in commission?', 'woocommerce-product-vendors' ); ?></span>
			<span class="input-text-wrap">
					<select class="pass-shipping-tax" name="_wcpv_product_split_tax">
					<?php
					$options = array(
						''    => __( '— No Change —', 'woocommerce-product-vendors' ),
						'yes' => __( 'Yes', 'woocommerce-product-vendors' ),
						'no'  => __( 'No', 'woocommerce-product-vendors' ),
					);

					foreach ( $options as $key => $value ) {
						echo '<option value="' . esc_attr( $key ) . '">' . esc_html( $value ) . '</option>';
					}
					?>
				</select>
			</span>
		</label>
		<?php
	}

	/**
	 * Filters the order item meta label without underscore.
	 *
	 * @since 2.1.8 User correct WC core filter.
	 * @param string $key
	 * @return bool
	 */
	public function filter_order_attribute_label( $key ) {
		if ( '_fulfillment_status' === $key ) {
			return __( 'Fulfillment Status', 'woocommerce-product-vendors' );
		}

		if ( '_commission_status' === $key ) {
			return __( 'Commission Status', 'woocommerce-product-vendors' );
		}

		return $key;
	}

	/**
	 * Filters the order item meta value.
	 *
	 * @since 2.1.8
	 * @param string $value
	 * @param object $meta
	 * @return bool
	 */
	public function filter_order_attribute_value( $value, $meta ) {
		if ( 'unfulfilled' === $value ) {
			return __( 'Unfulfilled', 'woocommerce-product-vendors' );
		}

		if ( 'fulfilled' === $value ) {
			return __( 'Fulfilled', 'woocommerce-product-vendors' );
		}

		if ( 'paid' === $value ) {
			return __( 'Paid', 'woocommerce-product-vendors' );
		}

		if ( 'unpaid' === $value ) {
			return __( 'Unpaid', 'woocommerce-product-vendors' );
		}

		return $value;
	}

	/**
	 * Save pass shipping/tax setting to product bulk edit
	 *
	 * @since 2.0.0
	 * @version 2.0.0
	 * @param object $product
	 */
	public function save_product_bulk_edit_pass_shipping_tax( $product ) {
		if ( ! WC_Product_Vendors_Utils::is_vendor() && current_user_can( 'manage_vendors' ) ) {
			if ( empty( $product ) ) {
				return;
			}

			if ( ! empty( $_REQUEST['_wcpv_product_pass_shipping'] ) ) {
				$product->update_meta_data(
					'_wcpv_product_pass_shipping',
					$_REQUEST['_wcpv_product_pass_shipping']
				);
			}
			if ( ! empty( $_REQUEST['_wcpv_product_pass_tax'] ) ) {
				$product->update_meta_data(
					'_wcpv_product_pass_tax',
					$_POST['_wcpv_product_pass_tax']
				);
			}
			if ( ! empty( $_REQUEST['_wcpv_product_split_tax'] ) ) {
				$product->update_meta_data(
					'_wcpv_product_split_tax',
					$_REQUEST['_wcpv_product_split_tax']
				);
			}
			$product->save();
		}

		return;
	}

	/**
	 * Handles saving of the vendor to product.
	 *
	 * @since 2.0.0
	 * @version 2.0.0
	 * @param int $post_id
	 * @return bool
	 */
	public function save_product_vendor( $post_id ) {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}

		// don't continue if it is bulk/quick edit
		if ( ! empty( $_REQUEST['woocommerce_quick_edit'] ) || ! empty( $_REQUEST['woocommerce_bulk_edit'] ) || ! isset( $_POST['wcpv_product_term'] ) ) {
			return;
		}

		// if not a product bail
		if ( 'product' !== get_post_type( $post_id ) ) {
			return;
		}

		$term = ! empty( $_POST['wcpv_product_term'] ) ? absint( $_POST['wcpv_product_term'] ) : '';

		wp_set_object_terms( $post_id, $term, WC_PRODUCT_VENDORS_TAXONOMY );

		return true;
	}

	/**
	 * Add vendor selection on quick and bulk edit
	 *
	 * @access public
	 * @since 2.0.0
	 * @version 2.0.0
	 * @param string $column_name the name of the column to add it to
	 * @param string $post_type
	 * @return bool
	 */
	public function quick_edit( $column_name, $post_type ) {
		if ( WC_Product_Vendors_Utils::is_vendor() || ! current_user_can( 'manage_vendors' ) ) {
			return;
		}

		if ( 'taxonomy-wcpv_product_vendors' !== $column_name || 'product' !== $post_type ) {
			return;
		}

		$args = array(
			'hide_empty'   => false,
			'hierarchical' => false,
		);

		/**
		 * TODO:WCY
		 * 因为供应商数据太多了，所以这里直接不查询了，否则这个sql能执行上10秒
		 */
		//$terms = get_terms( WC_PRODUCT_VENDORS_TAXONOMY, $args );
		$terms = [];

		if ( ! empty( $terms ) ) {
			$output = '<fieldset class="inline-edit-col-center"><div class="inline-edit-group"><label class="alignleft"><span class="title">' . esc_html__( 'Vendors', 'woocommerce-product-vendors' ) . '</span>';

			$output .= '<select class="wcpv-product-vendor-terms-dropdown" name="wcpv_qe_product_term">';

			$output .= '<option value="no">' . esc_html__( 'No Change', 'woocommerce-product-vendors' ) . '</option>';
			$output .= '<option value="novendor">' . esc_html__( 'No Vendor', 'woocommerce-product-vendors' ) . '</option>';

			foreach ( $terms as $term ) {
				$output .= '<option value="' . esc_attr( $term->term_id ) . '">' . esc_html( $term->name ) . '</option>';
			}

			$output .= '</select>';

			$output .= '</label></div></fieldset>';

			echo $output;
		}
	}

	/**
	 * Handles quick and bulk edit saves
	 *
	 * @access public
	 * @since 2.0.0
	 * @version 2.0.0
	 * @param int $post_id
	 * @param object $post
	 * @return int
	 */
	public function bulk_and_quick_edit_save_post( $post_id, $post ) {
		if ( WC_Product_Vendors_Utils::is_vendor() || ! current_user_can( 'manage_vendors' ) ) {
			return $post_id;
		}

		// If this is an autosave, our form has not been submitted, so we don't want to do anything.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}

		// Don't save revisions and autosaves
		if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
			return $post_id;
		}

		// Check post type is product
		if ( 'product' !== $post->post_type ) {
			return $post_id;
		}

		// Check user permission
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return $post_id;
		}

		if ( empty( $_REQUEST['wcpv_qe_product_term'] ) || 'no' === $_REQUEST['wcpv_qe_product_term'] ) {
			return $post_id;
		}

		$term = ! empty( $_REQUEST['wcpv_qe_product_term'] ) ? absint( $_REQUEST['wcpv_qe_product_term'] ) : '';

		if ( 'novendor' === $term ) {
			$term = '';
		}

		// check if it is a quick edit or bulk edit
		if ( ! empty( $_REQUEST['woocommerce_quick_edit'] ) ) {
			// update the product term
			wp_set_object_terms( $post_id, $term, WC_PRODUCT_VENDORS_TAXONOMY );

			// Clear transient
			wc_delete_product_transients( $post_id );

		} elseif ( ! empty( $_REQUEST['woocommerce_bulk_edit'] ) && ! empty( $_REQUEST['post'] ) ) {
			foreach ( $_REQUEST['post'] as $post ) {
				// update the product term
				wp_set_object_terms( absint( $post ), $term, WC_PRODUCT_VENDORS_TAXONOMY );

				// Clear transient
				wc_delete_product_transients( absint( $post ) );
			}
		}

		return $post_id;
	}

	/**
	 * Generates the CSV ( commissions ) download of current view
	 *
	 * @access public
	 * @since 2.0.0
	 * @version 2.0.0
	 * @return array $query
	 */
	public function export_commissions_ajax() {
		$order_id          = ! empty( $_POST['order_id'] ) ? absint( $_POST['order_id'] ) : '';
		$year              = ! empty( $_POST['year'] ) ? sanitize_text_field( $_POST['year'] ) : '';
		$month             = ! empty( $_POST['month'] ) ? sanitize_text_field( $_POST['month'] ) : '';
		$commission_status = ! empty( $_POST['commission_status'] ) ? sanitize_text_field( $_POST['commission_status'] ) : '';
		$vendor_id         = ! empty( $_POST['vendor'] ) ? absint( $_POST['vendor'] ) : '';
		$nonce             = ! empty( $_POST['nonce'] ) ? sanitize_text_field( $_POST['nonce'] ) : '';

		// bail if nonce don't check out
		if ( ! wp_verify_nonce( $nonce, '_wcpv_export_commissions_nonce' ) ) {
			wp_die( __( 'Cheatin&#8217; huh?', 'woocommerce-product-vendors' ) );
		}

		$commission = new WC_Product_Vendors_Commission( new WC_Product_Vendors_PayPal_MassPay );

		$query = $commission->csv_filtered_query( $order_id, $year, $month, $commission_status, $vendor_id );

		echo $query;
		exit;
	}

	/**
	 * Handles export of unpaid commissions
	 *
	 * @access public
	 * @since 2.0.6
	 * @version 2.0.6
	 * @return bool
	 */
	public function export_unpaid_commissions_ajax() {
		$nonce = ! empty( $_POST['nonce'] ) ? sanitize_text_field( $_POST['nonce'] ) : '';

		// bail if nonce don't check out
		if ( ! wp_verify_nonce( $nonce, '_wcpv_export_unpaid_commissions_nonce' ) ) {
			wp_die( __( 'Cheatin&#8217; huh?', 'woocommerce-product-vendors' ) );
		}

		$currency = get_woocommerce_currency();
		$commission = new WC_Product_Vendors_Commission( new WC_Product_Vendors_PayPal_MassPay );

		$unpaid_commissions = $commission->get_unpaid_commission_data();

		$commissions = array();

		foreach ( $unpaid_commissions as $commission ) {
			if ( ! isset( $commissions[ $commission->vendor_id ] ) ) {
				$commissions[ $commission->vendor_id ] = wc_format_decimal( 0, 2 );
			}

			$commissions[ $commission->vendor_id ] += wc_format_decimal( $commission->total_commission_amount, 2 );
		}

		$payout_note = apply_filters( 'wcpv_export_unpaid_commissions_note', sprintf( __( 'Total commissions earned from %1$s as of %2$s on %3$s', 'woocommerce-product-vendors' ), get_bloginfo( 'name', 'display' ), date( 'H:i:s' ), date( 'd-m-Y' ) ) );

		$commissions_data = array();

		foreach ( $commissions as $vendor_id => $total ) {
			$vendor = WC_Product_Vendors_Utils::get_vendor_data_by_id( $vendor_id );

			$recipient = $vendor['name'];

			if ( ! empty( $vendor['paypal'] ) ) {
				$recipient = $vendor['paypal'];
			}

			$commissions_data[] = array(
				$recipient,
				$total,
				$currency,
				$vendor_id,
				$payout_note,
			);
		}

		// prepare CSV
		$headers = array(
			'Recipient',
			'Payment',
			'Currency',
			'Customer ID',
			'Note',
		);

		array_unshift( $commissions_data, $headers );

		// convert the array to string recursively
		$commissions_data = implode( PHP_EOL, array_map( array( 'WC_Product_Vendors_Utils', 'convert2string' ), $commissions_data ) );

		echo $commissions_data;
		exit;
	}

	/**
	 * Add debug tool button.
	 *
	 * @since 2.0.0
	 * @version 2.0.35
	 * @return array $tools
	 */
	public function add_debug_tool( $tools ) {
		if ( ! empty( $_GET['action'] ) && 'wcpv_clear_transients' === $_GET['action'] && version_compare( WC_VERSION, '3.0', '<' ) ) {
			WC_Product_Vendors_Utils::clear_reports_transients();

			echo '<div class="updated"><p>' . esc_html__( 'Product Vendor Transients Cleared', 'woocommerce-product-vendors' ) . '</p></div>';
		}

		if ( ! empty( $_GET['action'] ) && 'wcpv_delete_webhook' === $_GET['action'] && version_compare( WC_VERSION, '3.0', '<' ) ) {
			WC_Product_Vendors_Utils::delete_paypal_webhook_id();

			echo '<div class="updated"><p>' . esc_html__( 'Product Vendor Webhook Deleted.', 'woocommerce-product-vendors' ) . '</p></div>';
		}

		$tools['wcpv_clear_transients'] = array(
			'name'    => __( 'Product Vendors Transients', 'woocommerce-product-vendors' ),
			'button'  => __( 'Clear all transients/cache', 'woocommerce-product-vendors' ),
			'desc'    => __( 'This will clear all Product Vendors related transients/caches such as reports.', 'woocommerce-product-vendors' ),
		);

		$tools['wcpv_delete_webhook'] = array(
			'name'    => __( 'Product Vendors Delete Webhook', 'woocommerce-product-vendors' ),
			'button'  => __( 'Delete Webhook', 'woocommerce-product-vendors' ),
			'desc'    => __( 'Troubleshoot PayPal Payouts. Delete current webhook and receive new webhook id.', 'woocommerce-product-vendors' ),
		);

		if ( version_compare( WC_VERSION, '3.0', '>=' ) ) {
			$tools['wcpv_clear_transients']['callback'] = 'WC_Product_Vendors_Utils::clear_reports_transients';
			$tools['wcpv_delete_webhook']['callback'] = 'WC_Product_Vendors_Utils::delete_paypal_webhook_id';
		}

		return $tools;
	}

	/**
	 * Clears all report transients
	 *
	 * @access public
	 * @since 2.0.0
	 * @version 2.0.0
	 * @return bool
	 */
	public function clear_reports_transients() {
		WC_Product_Vendors_Utils::clear_reports_transients();

		return true;
	}
}

WC_Product_Vendors_Store_Admin::init();
