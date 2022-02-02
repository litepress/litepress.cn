<?php
/**
 * YITH Theme License Class.
 *
 * @class   YIT_Theme_Licence
 * @package YITH\PluginFramework\Classes
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YIT_Theme_Licence' ) ) {
	/**
	 * YIT_Theme_Licence class.
	 *
	 * @author Andrea Grillo <andrea.grillo@yithemes.com>
	 */
	class YIT_Theme_Licence {
		/**
		 * The single instance of the class.
		 *
		 * @var YIT_Theme_Licence
		 */
		private static $instance;

		/**
		 * Singleton implementation.
		 *
		 * @return YIT_Theme_Licence
		 */
		public static function instance() {
			return ! is_null( self::$instance ) ? self::$instance : self::$instance = new self();
		}

		/**
		 * YIT_Theme_Licence constructor.
		 */
		private function __construct() {
			// Silence is golden.
		}

		/**
		 * Premium products registration.
		 *
		 * @param string $init       The product init identifier.
		 * @param string $secret_key The secret key.
		 * @param string $product_id The product ID.
		 *
		 * @return void
		 * @since    1.0
		 * @author   Andrea Grillo <andrea.grillo@yithemes.com>
		 */
		public function register( $init, $secret_key, $product_id ) {
			if ( ! function_exists( 'YITH_Theme_Licence' ) ) {
				// Try to load YITH_Theme_Licence class.
				yith_plugin_fw_load_update_and_licence_files();
			}

			if ( function_exists( 'YITH_Theme_Licence' ) && is_callable( array( YITH_Theme_Licence(), 'register' ) ) ) {
				YITH_Theme_Licence()->register( $init, $secret_key, $product_id );
			}
		}
	}
}

if ( ! function_exists( 'YIT_Theme_Licence' ) ) {
	/**
	 * Single instance of YIT_Theme_Licence
	 *
	 * @return YIT_Theme_Licence
	 */
	function YIT_Theme_Licence() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid
		return YIT_Theme_Licence::instance();
	}
}
