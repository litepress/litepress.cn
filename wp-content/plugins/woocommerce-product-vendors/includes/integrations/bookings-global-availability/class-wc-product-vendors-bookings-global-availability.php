<?php
/**
 * Bookings Global Availability integration for bookings versions >= 1.13.
 *
 * @package WooCommerce Product Vendors/Bookings
 */

/**
 * Class WC_Product_Vendors_Bookings_Global_Availability
 *
 * @since 2.1.15
 */
class WC_Product_Vendors_Bookings_Global_Availability {
	/**
	 * Cache vendor data to prevent extra queries.
	 *
	 * @var array
	 */
	private $bookings_to_vendors_cache = array();

	/**
	 * Init Integration.
	 */
	public function init() {
		$this->register_hooks();
	}

	/**
	 * Register all hooks.
	 */
	protected function register_hooks() {

		add_action( 'woocommerce_before_booking_global_availability_object_save', array( $this, 'before_global_availability_save' ), 10, 2 );

		add_action( 'woocommerce_bookings_before_delete_global_availability', array( $this, 'before_global_availability_delete' ), 10, 2 );

		add_filter( 'woocommerce_bookings_get_all_global_availability', array( $this, 'filter_global_availability' ) );

		add_action( 'woocommerce_bookings_extra_global_availability_fields', array( $this, 'extra_global_availability_fields' ) );

		add_action( 'woocommerce_bookings_extra_global_availability_fields_header', array( $this, 'extra_global_availability_fields_header' ) );

		add_filter( 'woocommerce_booking_get_availability_rules', array( $this, 'filter_availability_rules' ), 10, 3 );

		if ( defined( 'WC_BOOKINGS_VERSION' ) && version_compare( WC_BOOKINGS_VERSION, '1.14.2', '>=' ) ) {
			// Run migration of vendor to meta data. Needs to happen after bookings's migration in 1.13.
			add_action( 'plugins_loaded', array( $this, 'maybe_migrate_old_global_availability_option' ), 20 );
		}
		WP_Query::class;
	}

	/**
	 * Migrate global availability rules from WP option to global availability table.
	 */
	public function maybe_migrate_old_global_availability_option() {
		global $wpdb;
		$migrated = get_option( 'woocommerce_product_vendors_bookings_global_availability_migrated', false );
		// Set option now to ensure this only happens once.
		add_option( 'woocommerce_product_vendors_bookings_global_availability_migrated', true );

		if ( $migrated ) {
			return;
		}

		$old_values = get_option( 'wc_global_booking_availability', array() );
		remove_filter( 'woocommerce_bookings_get_all_global_availability', array( $this, 'filter_global_availability' ) );
		/**
		 * All unfiltered global availability objects.
		 *
		 * @var WC_Global_Availability[] $global_availabilities
		 */
		$global_availabilities = WC_Data_Store::load( 'booking-global-availability' )->get_all();
		add_filter( 'woocommerce_bookings_get_all_global_availability', array( $this, 'filter_global_availability' ) );

		foreach ( $old_values as $ordering => $old_value ) {
			try {

				if ( empty( $old_value['vendor'] ) ) {
					continue;
				}

				if ( isset( $global_availabilities[ $ordering ] ) && // Check if the rule hasn't change position first.
					$global_availabilities[ $ordering ]->get_range_type() === $old_value['type'] &&
					$global_availabilities[ $ordering ]->get_priority() === $old_value['priority'] &&
					$global_availabilities[ $ordering ]->get_from_range() === $old_value['from'] &&
					$global_availabilities[ $ordering ]->get_to_range() === $old_value['to'] &&
					$global_availabilities[ $ordering ]->get_bookable() === $old_value['bookable'] &&
					! $global_availabilities[ $ordering ]->get_meta( 'vendor_id' )

				) {
					$global_availabilities[ $ordering ]->add_meta_data( 'vendor_id', $old_value['vendor'], true );
					$global_availabilities[ $ordering ]->save_meta_data();
				} else {
					foreach ( $global_availabilities as $availability ) { // Search for vendor rule in all rules.
						if ( $availability->get_range_type() === $old_value['type'] &&
							$availability->get_priority() === $old_value['priority'] &&
							$availability->get_from_range() === $old_value['from'] &&
							$availability->get_to_range() === $old_value['to'] &&
							$availability->get_bookable() === $old_value['bookable'] &&
							! $availability->get_meta( 'vendor_id' )
						) {
							$availability->add_meta_data( 'vendor_id', $old_value['vendor'], true );
							$availability->save_meta_data();
							break;
						}
					}
				}
			} catch ( Exception $e ) {
				WC_Product_Vendors_Logger::log( $e->getMessage() );
			}
		}
	}

	/**
	 * Output the vendor header for the table.
	 */
	public function extra_global_availability_fields_header() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		echo '<th>' . esc_html__( 'Vendor', 'woocommerce-product-vendors' ) . '</th>';
	}

	/**
	 * Output the vendor if set.
	 *
	 * @param WC_Global_Availability $availability Current availability object.
	 */
	public function extra_global_availability_fields( WC_Global_Availability $availability ) {

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		echo '<td>';
		$vendor_id = (int) $availability->get_meta( 'vendor_id' );
		if ( $vendor_id ) {
			$vendor_data = WC_Product_Vendors_Utils::get_vendor_data_by_id( $vendor_id );
			if ( ! empty( $vendor_data['name'] ) ) {
				echo esc_html( $vendor_data['name'] );
			}
		}
		echo '</td>';
	}

	/**
	 * Adds vendor_id to meta data on save.
	 *
	 * @param WC_Global_Availability $availability Object being saved.
	 * @param WC_Data_Store          $data_store Data Store.
	 */
	public function before_global_availability_save( WC_Global_Availability $availability, WC_Data_Store $data_store ) {
		if ( WC_Product_Vendors_Utils::is_vendor() ) {

			if ( $availability->get_id() && (int) $availability->get_meta( 'vendor_id' ) !== (int) WC_Product_Vendors_Utils::get_logged_in_vendor() ) {
				// Availability already exists but current user does not own it, so create instead of updating.
				$availability->set_id( 0 );
			}
			$availability->add_meta_data( 'vendor_id', (int) WC_Product_Vendors_Utils::get_logged_in_vendor(), true );
		}
	}

	/**
	 * Removes vendor_id from meta data on delete.
	 *
	 * @param WC_Global_Availability            $availability Object being deleted.
	 * @param WC_Global_Availability_Data_Store $data_store Data Store.
	 */
	public function before_global_availability_delete( WC_Global_Availability $availability, WC_Global_Availability_Data_Store $data_store ) {
		if ( WC_Product_Vendors_Utils::is_vendor() ) {

			if ( (int) $availability->get_meta( 'vendor_id' ) !== (int) WC_Product_Vendors_Utils::get_logged_in_vendor() ) {
				// Current user does not own it, so remove id so delete does nothing.
				$availability->set_id( 0 );
			}
		}
	}

	/**
	 * Filter global availability by vendor.
	 *
	 * @param WC_Global_Availability[] $availabilities All global availability objects.
	 *
	 * @return WC_Global_Availability[]
	 */
	public function filter_global_availability( array $availabilities ) {

		if ( WC_Product_Vendors_Utils::is_vendor() ) {
			$vendor_id = (int) WC_Product_Vendors_Utils::get_logged_in_vendor();
			foreach ( $availabilities as $key => $availability ) {
				if ( (int) $availability->get_meta( 'vendor_id' ) !== $vendor_id ) {
					unset( $availabilities[ $key ] );
				}
			}
		}
		return $availabilities;
	}

	/**
	 * Filters the global availability rules for specific vendor's products only
	 *
	 * @param array  $rules All rules.
	 * @param int    $for_resource Resource id rules are for.
	 * @param object $booking Current Bookable product.
	 *
	 * @return array $availability_rules
	 */
	public function filter_availability_rules( $rules, $for_resource, $booking ) {

		// to prevent duplicate queries from bookings, cache vendor data.
		if ( isset( $this->bookings_to_vendors_cache[ $booking->get_id() ] ) ) {
			$vendor_id = $this->bookings_to_vendors_cache[ $booking->get_id() ];
		} else {
			$vendor = WC_Product_Vendors_Utils::is_vendor_product( $booking->get_id() );
			if ( $vendor ) {
				$vendor_id = $vendor[0]->term_id;
				$this->bookings_to_vendors_cache[ $booking->get_id() ] = $vendor_id;
			} else {
				$vendor_id = false;
			}
		}

		/**
		 * All global availability objects.
		 *
		 * @var WC_Global_Availability[] $global_availabilities
		 */
		$global_availabilities = WC_Data_Store::load( 'booking-global-availability' )->get_all();

		if ( $vendor_id ) {
			// filter rules that belong to this vendor's product.
			$filtered_global_availabilities = array_filter(
				$global_availabilities,
				function ( WC_Global_Availability $availability ) use ( $vendor_id ) {
					return (int) $availability->get_meta( 'vendor_id' ) === (int) $vendor_id;
				}
			);
		} else {
			// filter rules that don't belong to any vendor.
			$filtered_global_availabilities = array_filter(
				$global_availabilities,
				function ( WC_Global_Availability $availability ) {
					return empty( $availability->get_meta( 'vendor_id' ) );
				}
			);
		}

		// Remove existing global rules.
		foreach ( $rules as $key => $rule ) {
			if ( 'global' === $rule['level'] ) {
				unset( $rules[ $key ] );
			}
		}

		$rules = array_merge( $rules, WC_Product_Booking_Rule_Manager::process_availability_rules( $filtered_global_availabilities, 'global' ) );

		usort( $rules, array( 'WC_Product_Booking_Rule_Manager', 'sort_rules_callback' ) );

		return $rules;
	}
}
