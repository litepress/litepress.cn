<?php
/**
 * YITH Plugin License Class.
 *
 * @class   YIT_Plugin_Licence
 * @package YITH\PluginFramework\Classes
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YIT_Plugin_Licence' ) ) {
	/**
	 * YIT_Plugin_Licence class.
	 * Set page to manage products.
	 *
	 * @author Andrea Grillo <andrea.grillo@yithemes.com>
	 */
	class YIT_Plugin_Licence {
		/**
		 * The single instance of the class.
		 *
		 * @var YIT_Plugin_Licence
		 */
		private static $instance;

		/**
		 * Singleton implementation.
		 *
		 * @return YIT_Plugin_Licence
		 */
		public static function instance() {
			return ! is_null( self::$instance ) ? self::$instance : self::$instance = new self();
		}

		/**
		 * YIT_Plugin_Licence constructor.
		 */
		private function __construct() {
			// Silence is golden.
		}

		/**
		 * Premium products registration
		 *
		 * @param string $init       The product identifier.
		 * @param string $secret_key The secret key.
		 * @param string $product_id The product id.
		 *
		 * @return void
		 */
		public function register( $init, $secret_key, $product_id ) {
			if ( ! function_exists( 'YITH_Plugin_Licence' ) ) {
				// Try to load YITH_Plugin_Licence class.
				yith_plugin_fw_load_update_and_licence_files();
			}

			if ( function_exists( 'YITH_Plugin_Licence' ) && is_callable( array( YITH_Plugin_Licence(), 'register' ) ) ) {
				YITH_Plugin_Licence()->register( $init, $secret_key, $product_id );
			}
		}

		/**
		 * Get license activation URL
		 *
		 * @param string $plugin_slug The plugin slug.
		 *
		 * @return string|false
		 * @since  3.0.17
		 */
		public static function get_license_activation_url( $plugin_slug = '' ) {
			return function_exists( 'YITH_Plugin_Licence' ) ? YITH_Plugin_Licence()->get_license_activation_url( $plugin_slug ) : false;
		}

		/**
		 * Retrieve the products
		 *
		 * @return array
		 */
		public function get_products() {
			return function_exists( 'YITH_Plugin_Licence' ) ? YITH_Plugin_Licence()->get_products() : array();
		}
	}
}

if ( ! function_exists( 'YIT_Plugin_Licence' ) ) {
	/**
	 * Single instance of YIT_Plugin_Licence
	 *
	 * @return YIT_Plugin_Licence
	 */
	function YIT_Plugin_Licence() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid
		return YIT_Plugin_Licence::instance();
	}
}
