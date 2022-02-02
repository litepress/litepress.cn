<?php
/**
 * YITH Upgrade Class
 * handle notifications and plugin updates.
 *
 * @class   YIT_Upgrade
 * @package YITH\PluginFramework\Classes
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YIT_Upgrade' ) ) {
	/**
	 * YIT_Upgrade class.
	 */
	class YIT_Upgrade {
		/**
		 * The single instance of the class.
		 *
		 * @var YIT_Upgrade
		 */
		private static $instance;

		/**
		 * Singleton implementation.
		 *
		 * @return YIT_Upgrade
		 */
		public static function instance() {
			return ! is_null( self::$instance ) ? self::$instance : self::$instance = new self();
		}

		/**
		 * YIT_Upgrade constructor.
		 */
		private function __construct() {
			// Silence is golden.
		}

		/**
		 * Premium products registration.
		 *
		 * @param string $plugin_slug The plugin slug.
		 * @param string $plugin_init The plugin init file.
		 */
		public function register( $plugin_slug, $plugin_init ) {
			if ( ! function_exists( 'YITH_Plugin_Upgrade' ) ) {
				// Try to load YITH_Plugin_Upgrade class.
				yith_plugin_fw_load_update_and_licence_files();
			}

			if ( function_exists( 'YITH_Plugin_Upgrade' ) && is_callable( array( YITH_Plugin_Upgrade(), 'register' ) ) ) {
				YITH_Plugin_Upgrade()->register( $plugin_slug, $plugin_init );
			}
		}
	}
}

if ( ! function_exists( 'YIT_Upgrade' ) ) {
	/**
	 * Single instance of YIT_Upgrade
	 *
	 * @return YIT_Upgrade
	 */
	function YIT_Upgrade() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid
		return YIT_Upgrade::instance();
	}
}

YIT_Upgrade();
