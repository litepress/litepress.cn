<?php // phpcs:ignore WordPress.Files.FileName

/**
 * This class is the main class that load all other classes.
 *
 * @package YITH WooCommerce Tab Manager\Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly


if ( ! class_exists( 'YITH_WC_Tab_Manager' ) ) {

	/**
	 * Implements features of YITH WooCommerce Tab Manager plugin
	 *
	 * @class   YITH_WC_Tab_Manager
	 * @package YITHEMES
	 * @since   1.0.0
	 * @author  YITH
	 */
	class YITH_WC_Tab_Manager {


		/**
		 * Single instance of the class
		 *
		 * @var \YITH_WCTM
		 * @since 1.0.0
		 */
		protected static $instance;

		/**
		 * Post type name
		 *
		 * @var string
		 * @since 1.0.0
		 */
		public $post_type_name = 'ywtm_tab';

		/**
		 * The Plugin Panel
		 *
		 * @var YIT_Plugin_Panel_WooCommerce
		 */
		protected $_panel;

		/**
		 * Premium tab template file name
		 *
		 * @var string
		 */
		protected $_premium = 'premium.php';

		/**
		 * Premium version landing link
		 *
		 * @var string
		 */
		protected $_premium_landing_url = '//yithemes.com/themes/plugins/yith-woocommerce-tab-manager/';

		/**
		 * Plugin official documentation
		 *
		 * @var string
		 */
		protected $_official_documentation = '//yithemes.com/docs-plugins/yith-woocommerce-tab-manager/';

		/**
		 * Plugin live demo url
		 *
		 * @var string
		 */
		protected $_premium_live_demo = '//plugins.yithemes.com/yith-woocommerce-tab-manager';

		/**
		 * Yith WooCommerce Tab manager panel page
		 *
		 * @var string
		 */
		protected $_panel_page = 'yith_wc_tab_manager_panel';

		/**
		 * Default type Tab
		 *
		 * @var string
		 */
		protected $_default_type = 'global';
		/**
		 * Default tab layout
		 *
		 * @var string
		 */
		protected $_default_layout = 'default';

		/**
		 * Priority Tab penalty
		 *
		 * @var int
		 */
		protected $priority = 30;


		/**
		 * Returns single instance of the class
		 *
		 * @return \YITH_WCTM
		 * @since 1.0.0
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Constructor
		 *
		 * Initialize plugin and registers actions and filters to be used
		 *
		 * @since  1.0
		 * @author YITH
		 */
		public function __construct() {

			if ( $this->is_admin() ) {
				YITH_Tab_Manager_Admin();
			} else {
				YITH_Tab_Manager_Frontend();
			}
			YITH_WCTM_Post_Type();
			// Load Plugin Framework.
			add_action( 'plugins_loaded', array( $this, 'plugin_fw_loader' ), 15 );
			add_filter(
				'yith_plugin_fw_icons_field_icons_' . YWTM_SLUG,
				array(
					$this,
					'yith_add_retina_to_icons',
				),
				10,
				2
			);
		}

		/**
		 * Check is the current action is admin
		 *
		 * @author YITH
		 * @since 2.0.0
		 * @return bool
		 */
		public function is_admin() {
			$is_ajax = ( defined( 'DOING_AJAX' ) && DOING_AJAX && isset( $_REQUEST['context'] ) && 'frontend' === $_REQUEST['context'] );// phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

			return is_admin() && ! $is_ajax;
		}

		/**
		 * Load the plugin framework
		 *
		 * @since  1.0
		 * @access public
		 * @return void
		 * @author Andrea Grillo <andrea.grillo@yithemes.com>
		 */
		public function plugin_fw_loader() {
			if ( ! defined( 'YIT_CORE_PLUGIN' ) ) {
				global $plugin_fw_data;
				if ( ! empty( $plugin_fw_data ) ) {
					$plugin_fw_file = array_shift( $plugin_fw_data );
					require_once $plugin_fw_file;
				}
			}
		}

		/**
		 * Add the retina icon
		 *
		 * @param array $yit_icons The default icons.
		 * @author YITH
		 * @since 1.0.0
		 * @return array
		 */
		public function yith_add_retina_to_icons( $yit_icons ) {
			$font_json                    = YWTM_ASSETS_PATH . '/fonts/retinaicon-font/config.json';
			$yit_icons['retinaicon-font'] = json_decode( file_get_contents( $font_json ), true );

			return $yit_icons;
		}

	}
}

/**
 * Return the unique access of the class.
 *
 * @author YITH
 * @since 1.0.0
 * @return YITH_WC_Tab_Manager
 */
function YITH_Tab_Manager() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName
	return YITH_WC_Tab_Manager::get_instance();
}
