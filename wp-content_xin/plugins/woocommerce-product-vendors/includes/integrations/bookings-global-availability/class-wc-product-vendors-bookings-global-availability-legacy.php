<?php
/**
 * Legacy class for pre 1.13 bookings.
 *
 * @package WooCommerce Product Vendors/Bookings
 */

/**
 * Class WC_Product_Vendors_Bookings_Global_Availability_Legacy
 */
class WC_Product_Vendors_Bookings_Global_Availability_Legacy {

	/**
	 * Init integration.
	 */
	public function init() {
		$this->register_hooks();
	}

	/**
	 * Register all hooks.
	 */
	protected function register_hooks() {
		// remove global availability page and roll our own.
		add_action( 'admin_menu', array( $this, 'remove_bookings_global_availability_menu' ), 99 );

		// add our own version of the global availability page.
		add_action( 'admin_menu', array( $this, 'add_bookings_global_availability_menu' ) );

		// filter bookings global availability.
		add_filter(
			'pre_update_option_wc_global_booking_availability',
			array(
				$this,
				'before_update_global_availability',
			),
			10,
			2
		);

		add_filter(
			'pre_option_wc_global_booking_availability',
			array(
				$this,
				'before_display_global_availability',
			),
			10,
			2
		);

		add_filter( 'woocommerce_booking_get_availability_rules', array( $this, 'filter_availability_rules' ), 10, 3 );
	}

	/**
	 * Remove bookings global availability menu
	 *
	 * @since 2.0.2
	 * @version 2.0.2
	 * @return bool
	 */
	public function remove_bookings_global_availability_menu() {
		// remove create bookings menu page.
		remove_submenu_page( 'edit.php?post_type=wc_booking', 'wc_bookings_global_availability' );

		return true;
	}

	/**
	 * Adds the submenu page
	 *
	 * @since 2.1.0
	 * @version 2.1.0
	 * @return bool
	 */
	public function add_bookings_global_availability_menu() {
		if ( version_compare( WC_BOOKINGS_VERSION, '1.13.0', '<' ) || ( version_compare( WC_BOOKINGS_VERSION, '1.13.0', '>=' ) && WC_Product_Vendors_Utils::is_vendor() ) ) {
			add_submenu_page(
				'edit.php?post_type=wc_booking',
				__( 'Global Availability', 'woocommerce-product-vendors' ),
				__( 'Global Availability', 'woocommerce-product-vendors' ),
				'manage_bookings',
				'wcpv_bookings_global_availability',
				array(
					$this,
					'global_availability_page',
				)
			);
		}

		return true;
	}

	/**
	 * Renders the global availability page
	 *
	 * @since 2.1.0
	 * @version 2.1.0
	 * @return bool
	 */
	public function global_availability_page() {
		global $wpdb, $wp_scripts;

		wp_enqueue_script( 'jquery-ui-sortable' );
		wp_enqueue_script( 'wc_bookings_writepanel_js' );
		wp_enqueue_script( 'wc_bookings_settings_js' );

		$jquery_version = isset( $wp_scripts->registered['jquery-ui-core']->ver ) ? $wp_scripts->registered['jquery-ui-core']->ver : '1.9.2';

		wp_enqueue_style( 'jquery-ui-style', '//ajax.googleapis.com/ajax/libs/jqueryui/' . $jquery_version . '/themes/smoothness/jquery-ui.css' );

		include 'views/html-bookings-global-availability-settings.php';

		return true;
	}


	/**
	 * When saving global availability rules, save it in vendor meta as well
	 *
	 * @since 2.1.0
	 * @version 2.1.0
	 *
	 * @param array $new_values
	 * @param array $old_values
	 *
	 * @return array $new_values
	 */
	public function before_update_global_availability( $new_values, $old_values ) {
		remove_filter(
			'pre_option_wc_global_booking_availability',
			array(
				$this,
				'before_display_global_availability',
			)
		);
		$old_values = get_option( 'wc_global_booking_availability', array() );
		add_filter(
			'pre_option_wc_global_booking_availability',
			array(
				$this,
				'before_display_global_availability',
			),
			10,
			2
		);

		$availability = array();

		if ( ! empty( $_POST['bookings_availability_submitted'] ) ) {
			$row_size = isset( $_POST['wc_booking_availability_type'] ) ? sizeof( $_POST['wc_booking_availability_type'] ) : 0;
			for ( $i = 0; $i < $row_size; $i ++ ) {
				$availability[ $i ]['type']     = wc_clean( $_POST['wc_booking_availability_type'][ $i ] );
				$availability[ $i ]['bookable'] = wc_clean( $_POST['wc_booking_availability_bookable'][ $i ] );
				$availability[ $i ]['priority'] = intval( $_POST['wc_booking_availability_priority'][ $i ] );

				switch ( $availability[ $i ]['type'] ) {
					case 'custom':
						$availability[ $i ]['from'] = wc_clean( $_POST['wc_booking_availability_from_date'][ $i ] );
						$availability[ $i ]['to']   = wc_clean( $_POST['wc_booking_availability_to_date'][ $i ] );
						break;
					case 'months':
						$availability[ $i ]['from'] = wc_clean( $_POST['wc_booking_availability_from_month'][ $i ] );
						$availability[ $i ]['to']   = wc_clean( $_POST['wc_booking_availability_to_month'][ $i ] );
						break;
					case 'weeks':
						$availability[ $i ]['from'] = wc_clean( $_POST['wc_booking_availability_from_week'][ $i ] );
						$availability[ $i ]['to']   = wc_clean( $_POST['wc_booking_availability_to_week'][ $i ] );
						break;
					case 'days':
						$availability[ $i ]['from'] = wc_clean( $_POST['wc_booking_availability_from_day_of_week'][ $i ] );
						$availability[ $i ]['to']   = wc_clean( $_POST['wc_booking_availability_to_day_of_week'][ $i ] );
						break;
					case 'time':
					case 'time:1':
					case 'time:2':
					case 'time:3':
					case 'time:4':
					case 'time:5':
					case 'time:6':
					case 'time:7':
						$availability[ $i ]['from'] = wc_booking_sanitize_time( $_POST['wc_booking_availability_from_time'][ $i ] );
						$availability[ $i ]['to']   = wc_booking_sanitize_time( $_POST['wc_booking_availability_to_time'][ $i ] );
						break;
					case 'time:range':
						$availability[ $i ]['from'] = wc_booking_sanitize_time( $_POST['wc_booking_availability_from_time'][ $i ] );
						$availability[ $i ]['to']   = wc_booking_sanitize_time( $_POST['wc_booking_availability_to_time'][ $i ] );

						$availability[ $i ]['from_date'] = wc_clean( $_POST['wc_booking_availability_from_date'][ $i ] );
						$availability[ $i ]['to_date']   = wc_clean( $_POST['wc_booking_availability_to_date'][ $i ] );
						break;
				}

				if ( isset( $_POST['wc_booking_availability_vendor'][ $i ] ) ) {
					$availability[ $i ]['vendor'] = absint( $_POST['wc_booking_availability_vendor'][ $i ] );
				} elseif ( WC_Product_Vendors_Utils::is_vendor() ) {
					$availability[ $i ]['vendor'] = absint( WC_Product_Vendors_Utils::get_logged_in_vendor() );
				}
			}
		}

		$modified_values = $availability;

		if ( WC_Product_Vendors_Utils::is_vendor() ) {
			if ( is_array( $modified_values ) ) {
				$modified_old_values = array();

				// add the rest of the rules back in.
				foreach ( $old_values as $old_value ) {
					// skip the ones that belongs to current vendor.
					if ( ! empty( $old_value['vendor'] ) && (int) WC_Product_Vendors_Utils::get_logged_in_vendor() === $old_value['vendor'] ) {
						continue;
					}

					$modified_old_values[] = $old_value;
				}
			}

			$modified_values = array_merge( $modified_values, $modified_old_values );
		}

		return $modified_values;
	}

	/**
	 * Filter out only rules for the vendor
	 *
	 * @since 2.1.0
	 * @version 2.1.0
	 *
	 * @param array $option
	 *
	 * @return array|false $option
	 */
	public function before_display_global_availability( $option = false ) {
		if ( WC_Product_Vendors_Utils::is_vendor() && is_admin() ) {
			remove_filter(
				'pre_option_wc_global_booking_availability',
				array(
					$this,
					'before_display_global_availability',
				)
			);
			$options = get_option( 'wc_global_booking_availability', array() );
			add_filter(
				'pre_option_wc_global_booking_availability',
				array(
					$this,
					'before_display_global_availability',
				),
				10,
				2
			);

			if ( empty( $options ) ) {
				return $option;
			}

			$filtered_options = array();

			foreach ( $options as $option ) {
				// only add the ones that belong to current vendor.
				if ( ! empty( $option['vendor'] ) && (int) WC_Product_Vendors_Utils::get_logged_in_vendor() === $option['vendor'] ) {
					$filtered_options[] = $option;
				}
			}

			return $filtered_options;
		}

		return false;
	}


	/**
	 * Filters the global availability rules for specific vendor's products only
	 *
	 * @since 2.1.0
	 * @version 2.1.0
	 *
	 * @param array  $rules
	 * @param int    $for_resource
	 * @param object $booking
	 *
	 * @return array $availability_rules
	 */
	public function filter_availability_rules( $rules, $for_resource, $booking ) {
		// Rule types.
		$resource_rules        = array();
		$filtered_global_rules = array();
		$product_rules         = get_post_meta( $booking->get_id(), '_wc_booking_availability', true );
		remove_filter(
			'pre_option_wc_global_booking_availability',
			array(
				$this,
				'before_display_global_availability',
			)
		);
		$global_rules = get_option( 'wc_global_booking_availability', array() );
		add_filter(
			'pre_option_wc_global_booking_availability',
			array(
				$this,
				'before_display_global_availability',
			),
			10,
			2
		);

		// to prevent duplicate queries from bookings, cache vendor data into
		// super global.
		if ( ! isset( $GLOBALS[ 'wcpv_is_vendor_booking_product_' . $booking->get_id() ] ) ) {
			$GLOBALS[ 'wcpv_is_vendor_booking_product_' . $booking->get_id() ] = false;

			if ( $vendor = WC_Product_Vendors_Utils::is_vendor_product( $booking->get_id() ) ) {
				$GLOBALS[ 'wcpv_is_vendor_booking_product_' . $booking->get_id() ] = $vendor;
			}
		}

		if ( $vendor = $GLOBALS[ 'wcpv_is_vendor_booking_product_' . $booking->get_id() ] ) {
			// filter rules that belong to this vendor's product.
			if ( ! empty( $global_rules ) ) {
				foreach ( $global_rules as $rule ) {
					if ( ! empty( $rule['vendor'] ) && $vendor[0]->term_id === $rule['vendor'] ) {
						$filtered_global_rules[] = $rule;
					}
				}
			}
		} else {
			// filter rules that don't belong to this vendor's product.
			if ( ! empty( $global_rules ) ) {
				foreach ( $global_rules as $rule ) {
					if ( empty( $rule['vendor'] ) ) {
						$filtered_global_rules[] = $rule;
					}
				}
			}
		}

		// Get availability of each resource - no resource has been chosen yet.
		if ( $booking->has_resources() && ! $for_resource ) {
			$resources      = $booking->get_resources();
			$resource_rules = array();

			if ( $booking->get_default_availability() ) {
				// If all blocks are available by default, we should not hide days if we don't know which resource is going to be used.
			} else {
				foreach ( $resources as $resource ) {
					$resource_rule = (array) get_post_meta( $resource->ID, '_wc_booking_availability', true );
					foreach ( $resource_rule as $index => $rule ) {
						$resource_rule[ $index ]['resource_id'] = $resource->ID;
					}
					$resource_rules = array_merge( $resource_rules, $resource_rule );

				}
			}
			// Standard handling.
		} elseif ( $for_resource ) {
			$resource_rules = (array) get_post_meta( $for_resource, '_wc_booking_availability', true );
			foreach ( $resource_rules as $index => $rule ) {
				$resource_rules[ $index ]['resource_id'] = $for_resource;
			}
		}

		$availability_rules = array_filter( array_reverse( array_merge( WC_Product_Booking_Rule_Manager::process_availability_rules( $resource_rules, 'resource' ), WC_Product_Booking_Rule_Manager::process_availability_rules( $product_rules, 'product' ), WC_Product_Booking_Rule_Manager::process_availability_rules( $filtered_global_rules, 'global' ) ) ) );

		if ( defined( 'WC_BOOKINGS_VERSION' ) && version_compare( WC_BOOKINGS_VERSION, '1.9.13', '>' ) ) {
			usort( $availability_rules, array( $booking, 'rule_override_power_sort' ) );
		} else {
			usort( $availability_rules, array( $booking, 'priority_sort' ) );
		}

		return $availability_rules;
	}
}
