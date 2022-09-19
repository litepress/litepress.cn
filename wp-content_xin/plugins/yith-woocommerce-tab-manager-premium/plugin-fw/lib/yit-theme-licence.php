<?php
/**
 * This file belongs to the YIT Plugin Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

if ( !defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'YIT_Theme_Licence' ) ) {
	/**
	 * YIT Licence Panel
	 *
	 * Setting Page to Manage Products
	 *
	 * @class      YIT_Licence
	 * @package    YITH
	 * @since      1.0
	 * @author     Andrea Grillo      <andrea.grillo@yithemes.com>
	 */
	class YIT_Theme_Licence {
		/**
		 * @var object The single instance of the class
		 * @since 1.0
		 */
		protected static $_instance = null;

		/**
		 * Constructor
		 *
		 * @since    1.0
		 * @author   Andrea Grillo <andrea.grillo@yithemes.com>
		 */
		public function __construct() {
			//Silence is golden
		}

		/**
		 * Premium products registration
		 *
		 * @param $init         string | The products identifier
		 * @param $secret_key   string | The secret key
		 * @param $product_id   string | The product id
		 *
		 * @return void
		 *
		 * @since    1.0
		 * @author   Andrea Grillo <andrea.grillo@yithemes.com>
		 */
		public function register( $init, $secret_key, $product_id ){
			if( ! function_exists( 'YITH_Theme_Licence' ) ){
				//Try to load YITH_Theme_Licence class
				yith_plugin_fw_load_update_and_licence_files();
			}

            try {
                YITH_Theme_Licence()->register( $init, $secret_key, $product_id );
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

/**
 * Main instance
 *
 * @return object
 * @since  1.0
 * @author Andrea Grillo <andrea.grillo@yithemes.com>
 */
if ( !function_exists( 'YIT_Theme_Licence' ) ) {
	function YIT_Theme_Licence() {
		return YIT_Theme_Licence::instance();
	}
}