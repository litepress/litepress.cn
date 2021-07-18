<?php
/**
 * Bookings compatibility code for bookings < 1.13.
 *
 * @package WooCommerce Product Vendors/Bookings */

/**
 * Class WC_Product_Vendors_Bookings_Legacy
 */
class WC_Product_Vendors_Bookings_Legacy {
	/**
	 * Init integration.
	 */
	public function init() {
		$this->register_hooks();
	}

	/**
	 * Register all hooks.
	 *
	 * @since 2.1.15
	 */
	protected function register_hooks() {
		// remove resources for vendors.
		add_filter( 'woocommerce_register_post_type_bookable_resource', array( $this, 'remove_resource' ) );

		// remove wc booking post type access.
		add_filter( 'woocommerce_register_post_type_wc_booking', array( $this, 'maybe_remove_wc_booking_post_type' ) );

		// remove bookable person post type access.
		add_filter( 'woocommerce_register_post_type_bookable_person', array( $this, 'maybe_remove_bookable_person_post_type' ) );
	}


	/**
	 * Remove bookings resources page
	 *
	 * @since 2.0.0
	 * @version 2.0.0
	 * @return array $post_type_args
	 */
	public function remove_resource( $post_type_args ) {
		if ( WC_Product_Vendors_Utils::is_vendor() ) {
			$post_type_args['capability_type'] = 'manage_booking_resource';
		}

		return $post_type_args;
	}


	/**
	 * Removes post type if bookings is not enabled
	 *
	 * @since 2.0.0
	 * @version 2.0.0
	 * @param array $args
	 * @return array $args
	 */
	public function maybe_remove_wc_booking_post_type( $args ) {
		if ( WC_Product_Vendors_Utils::is_vendor() ) {
			if ( ! WC_Product_Vendors_Utils::is_bookings_enabled() ) {
				$args['capability_type'] = 'manage_bookings';
			}
		}

		return $args;
	}

	/**
	 * Removes post type if bookings is not enabled
	 *
	 * @since 2.0.0
	 * @version 2.0.0
	 * @param array $args
	 * @return array $args
	 */
	public function maybe_remove_bookable_person_post_type( $args ) {
		if ( WC_Product_Vendors_Utils::is_vendor() ) {
			if ( ! WC_Product_Vendors_Utils::is_bookings_enabled() ) {
				$args['capability_type'] = 'manage_bookable_person';
			}
		}

		return $args;
	}
}