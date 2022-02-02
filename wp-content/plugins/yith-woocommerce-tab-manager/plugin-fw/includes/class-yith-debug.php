<?php
/**
 * YITH Debug Class.
 *
 * @class   YITH_Debug
 * @package YITH\PluginFramework\Classes
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_Debug' ) ) {
	/**
	 * YITH_Debug class.
	 *
	 * @author     Leanza Francesco <leanzafrancesco@gmail.com>
	 * @deprecated 3.7.7
	 */
	class YITH_Debug {

		/**
		 * The single instance of the class.
		 *
		 * @var YITH_Debug
		 */
		private static $instance;

		/**
		 * Singleton implementation.
		 *
		 * @return YITH_Debug
		 */
		public static function instance() {
			return ! is_null( self::$instance ) ? self::$instance : self::$instance = new self();
		}

		/**
		 * Deprecated singleton implementation.
		 * Kept for backward compatibility.
		 *
		 * @return YITH_Debug
		 * @deprecated 3.5 | use YITH_Debug::get_instance() instead.
		 */
		public static function get_instance() {
			return self::instance();
		}

		/**
		 * YITH_Debug constructor.
		 */
		private function __construct() {

		}

		/**
		 * Init
		 */
		public function init() {

		}

		/**
		 * Add debug node in admin bar.
		 *
		 * @param WP_Admin_Bar $wp_admin_bar WP_Admin_Bar instance.
		 */
		public function add_debug_in_admin_bar( $wp_admin_bar ) {
			// Do nothing.
		}


		/**
		 * Return an array of debug information.
		 *
		 * @return array
		 */
		public function get_debug_information() {
			return array();
		}

		/**
		 * Return the current screen ID.
		 *
		 * @return string
		 */
		public function get_current_screen_info() {
			$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : false;

			return ! ! $screen ? $screen->id : 'null';
		}

		/**
		 * Return the current theme name and version.
		 *
		 * @return string
		 */
		public function get_theme_info() {
			$theme = function_exists( 'wp_get_theme' ) ? wp_get_theme() : false;

			return ! ! $theme ? $theme->get( 'Name' ) . ' (' . $theme->get( 'Version' ) . ')' : 'null';
		}

		/**
		 * Return the WooCommerce version if active.
		 *
		 * @return string
		 */
		public function get_woocommerce_version_info() {
			return function_exists( 'WC' ) ? WC()->version : 'not active';
		}

		/**
		 * Return plugin framework information (version and loaded_by).
		 *
		 * @return string
		 */
		public function get_plugin_framework_info() {
			$plugin_fw_version   = yith_plugin_fw_get_version();
			$plugin_fw_loaded_by = basename( dirname( YIT_CORE_PLUGIN_PATH ) );

			return "$plugin_fw_version (by $plugin_fw_loaded_by)";
		}

		/**
		 * Return premium plugins list with versions.
		 *
		 * @return array
		 */
		public function get_premium_plugins_info() {
			$plugins      = YIT_Plugin_Licence()->get_products();
			$plugins_info = array();

			if ( ! ! $plugins ) {
				foreach ( $plugins as $plugin ) {
					$plugins_info[ $plugin['product_id'] ] = array( 'title' => $plugin['Name'] . ' (' . $plugin['Version'] . ')' );
				}

				sort( $plugins_info );
			}

			return $plugins_info;
		}
	}
}
if ( ! function_exists( 'yith_debug' ) ) {
	/**
	 * Single instance of YITH_Debug
	 *
	 * @return YITH_Debug
	 * @deprecated 3.7.7
	 */
	function yith_debug() {
		return YITH_Debug::instance();
	}
}
