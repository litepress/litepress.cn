<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Authentication Class.
 *
 * Handles the user/vendor authentication.
 *
 * @category Authentication
 * @package  WooCommerce Product Vendors/Authentication
 * @version  2.0.0
 */
class WC_Product_Vendors_Authentication {
	public $logged_in_vendor;

	/**
	 * Constructor
	 *
	 * @access public
	 * @since 2.0.0
	 * @version 2.0.0
	 * @return bool
	 */
	public function __construct() {
		// allow non admins to access admin
		add_filter( 'woocommerce_prevent_admin_access', array( $this, 'allow_admin_access' ) );

		// redirect vendors to dashboard instead of profile
		add_filter( 'login_redirect', array( $this, 'login_redirect' ), 10, 3 );

		// authenticates the vendor on login
		add_filter( 'authenticate', array( $this, 'login_authentication' ), 30, 3 );

		return true;
	}

	/**
	 * Allow vendors to access backend URL
	 *
	 * @access public
	 * @since 2.0.0
	 * @version 2.0.0
	 * @return bool
	 */
	public function allow_admin_access( $return ) {
		if ( WC_Product_Vendors_Utils::is_vendor() ) {
			return false;
		}

		return $return;
	}

	/**
	 * Redirect vendors on login
	 *
	 * @access public
	 * @since 2.0.0
	 * @version 2.0.0
	 * @param string $redirect_to the URL to redirect to
	 * @param string $request the URL the request was from
	 * @param object $user the user object passed
	 * @return bool
	 */
	public function login_redirect( $redirect_to, $request, $user ) {
		// redirect only if it is a vendor and logging in from wp-admin
		if ( isset( $user->ID ) && WC_Product_Vendors_Utils::is_vendor( $user->ID ) && admin_url() === $request ) {

			WC_Product_Vendors_Utils::clear_reports_transients();

			$redirect_to = admin_url( 'index.php' );
		}

		return $redirect_to;
	}

	/**
	 * Login Authentication
	 *
	 * @access public
	 * @since 2.0.0
	 * @version 2.0.0
	 * @param object $user
	 * @param string $username
	 * @param string $password
	 * @return bool
	 */
	public function login_authentication( $user, $username, $password ) {
		// check valid user
		if ( ! is_wp_error( $user ) && WC_Product_Vendors_Utils::is_vendor( $user->ID ) ) {

			// get all vendor data for this logged in user
			$vendor_data = WC_Product_Vendors_Utils::get_all_vendor_data( $user->ID );

			// if a pending vendor don't allow login at all
			if ( WC_Product_Vendors_Utils::is_pending_vendor( $user->ID ) ) {
				$user = new WP_Error( 'error', __( 'Your application is being reviewed.  You will be notified once approved.', 'woocommerce-product-vendors' ) );

			} elseif ( empty( $vendor_data ) ) {
				$user = new WP_Error( 'error', __( 'Your account is not authorized to manage any vendors.  Please contact us for help.', 'woocommerce-product-vendors' ) );

			} else {
				// set the default vendor this user will manage
				$this->logged_in_vendor = key( $vendor_data );
			}
		}

		return $user;
	}
}

new WC_Product_Vendors_Authentication();
