<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

/**
 * Implements features of YITH WooCommerce Tab Manager plugin
 *
 * @class   YITH_WC_Tab_Manager
 * @package YITHEMES
 * @since   1.0.0
 * @author  Your Inspiration Themes
 *
 */

if ( ! class_exists( 'YITH_WC_Tab_Manager' ) ) {

	class YITH_WC_Tab_Manager {


		/**
		 * Single instance of the class
		 *
		 * @var \YITH_WC_Tab_Manager
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
		 * @var Panel
		 */
		protected $_panel;

		/**
		 * @var $_premium string Premium tab template file name
		 */
		protected $_premium = 'premium.php';

		/**
		 * @var string Premium version landing link
		 */
		protected $_premium_landing_url = '//yithemes.com/themes/plugins/yith-woocommerce-tab-manager/';

		/**
		 * @var string Plugin official documentation
		 */
		protected $_official_documentation = '//yithemes.com/docs-plugins/yith-woocommerce-tab-manager/';

		protected $_premium_live_demo = '//plugins.yithemes.com/yith-woocommerce-tab-manager';

		/**
		 * @var string Yith WooCommerce Tab manager panel page
		 */
		protected $_panel_page = 'yith_wc_tab_manager_panel';

		/**
		 * Default type Tab
		 * @var string
		 */
		protected $_default_type = 'global';
		/**
		 * Default tab layout
		 * @var string
		 */
		protected $_default_layout = 'default';

		/**
		 * Priority Tab penalty
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
		 * @author YITHEMES
		 */

		public function __construct() {

			if ( $this->is_admin() ) {
				YITH_Tab_Manager_Admin();
			} else {
				YITH_Tab_Manager_Frontend();
			}
			YITH_WCTM_Post_Type();
			// Load Plugin Framework
			add_action( 'plugins_loaded', array( $this, 'plugin_fw_loader' ), 15 );
			add_filter( 'yith_plugin_fw_icons_field_icons_' . YWTM_SLUG, array(
				$this,
				'yith_add_retina_to_icons'
			), 10, 2 );

			add_filter( 'woocommerce_email_classes', array( $this, 'add_woocommerce_emails' ) );


		}

		/**
		 * @author Salvatore Strano
		 * @since 2.0.0
		 * check if backend
		 * @return bool
		 */
		public function is_admin() {
			$is_ajax = ( defined( 'DOING_AJAX' ) && DOING_AJAX && isset( $_REQUEST['context'] ) && $_REQUEST['context'] == 'frontend' );
			$is_elementor = isset( $_REQUEST['action']  ) && 'elementor' == $_REQUEST['action'];
			return is_admin() && ! $is_ajax && !$is_elementor;
		}

		/**
		 * load the plugin framework
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
					require_once( $plugin_fw_file );
				}
			}
		}

		public function yith_add_retina_to_icons( $yit_icons ) {
			$font_json = YWTM_ASSETS_PATH . '/fonts/retinaicon-font/config.json';
			$yit_icons['retinaicon-font'] = json_decode( file_get_contents( $font_json ), true );

			return $yit_icons;
		}

		/**
		 * add new email class
		 * @author YITHEMES
		 * @since 1.0.0
		 *
		 * @param array $emails
		 *
		 * @return array
		 */
		public function add_woocommerce_emails( $emails ) {

			$emails['YITH_Tab_Manager_Admin_Email']        = include( YWTM_INC . 'email/class.yith-tab-manager-email.php' );

			return $emails;
		}

	}
}

function YITH_Tab_Manager() {
	return YITH_WC_Tab_Manager::get_instance();
}