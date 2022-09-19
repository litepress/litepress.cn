<?php
/*
 * This file belongs to the YIT Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'YIT_Upgrade' ) ) {
	/**
	 * YIT Upgrade
	 *
	 * Notify and Update plugin
	 *
	 * @class       YIT_Upgrade
	 * @package     YITH
	 * @since       1.0
	 * @author      Your Inspiration Themes
	 * @see         WP_Updater Class
	 */
	class YIT_Upgrade {
		/**
		 * @var YIT_Upgrade The main instance
		 */
		protected static $_instance;

		/**
		 * Construct
		 *
		 * @author Andrea Grillo <andrea.grillo@yithemes.com>
		 * @since  1.0
		 */
		public function __construct() {
			//Silence is golden...
		}

		/**
		 * Main plugin Instance
		 *
		 * @param $plugin_slug | string The plugin slug
		 * @param $plugin_init | string The plugin init file
		 *
		 * @return void
		 *
		 * @since  1.0
		 * @author Andrea Grillo <andrea.grillo@yithemes.com>
		 */
		public function register( $plugin_slug, $plugin_init ) {
			if( ! function_exists( 'YITH_Plugin_Upgrade' ) ){
				//Try to load YITH_Plugin_Upgrade class
				yith_plugin_fw_load_update_and_licence_files();
			}

            try {
                YITH_Plugin_Upgrade()->register( $plugin_slug, $plugin_init );
            } catch( Error $e ){
            }
		}

		/**
		 * Main plugin Instance
		 *
		 * @static
		 * @return object Main instance
		 *
		 * @since  1.0
		 * @author Andrea Grillo <andrea.grillo@yithemes.com>
		 */
		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}

			return self::$_instance;
		}
	}
}

if ( ! function_exists( 'YIT_Upgrade' ) ) {
	/**
	 * Main instance of plugin
	 *
	 * @return YIT_Upgrade
	 * @since  1.0
	 * @author Andrea Grillo <andrea.grillo@yithemes.com>
	 */
	function YIT_Upgrade() {
		return YIT_Upgrade::instance();
	}
}

/**
 * Instance a YIT_Upgrade object
 */
YIT_Upgrade();
