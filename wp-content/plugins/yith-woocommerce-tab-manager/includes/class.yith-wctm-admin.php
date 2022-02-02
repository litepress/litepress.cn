<?php // phpcs:ignore WordPress.Files.FileName
/**
 * This class manage the feature in admina area
 *
 * @package YITH WooCommerce Tab Manager\Classes
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'YITH_WCTM_Admin' ) ) {
	/**
	 * The admin class
	 *
	 * @author YITH
	 */
	class YITH_WCTM_Admin {
		/**
		 * The instance of the class
		 *
		 * @var YITH_WCTM_Admin
		 */
		protected static $instance;

		/**
		 * The YITH Panel
		 *
		 * @var YIT_Plugin_Panel_WooCommerce
		 */
		protected $_panel;

		/**
		 * The string that store the premium landing template
		 *
		 * @var string
		 */
		protected $_premium = 'premium.php';

		/**
		 * The plugin panel name
		 *
		 * @var string
		 */
		protected $_panel_page = 'yith_wc_tab_manager_panel';

		/**
		 * The construct of the class
		 *
		 * @author YITH
		 * @since 1.0.0
		 */
		public function __construct() {

			// Add action links.
			add_filter(
				'plugin_action_links_' . plugin_basename( YWTM_DIR . '/' . basename( YWTM_FILE ) ),
				array(
					$this,
					'action_links',
				)
			);
			add_filter( 'yith_show_plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 5 );
			add_action( 'yith_tab_manager_premium', array( $this, 'premium_tab' ) );

			// Add action menu.
			add_action( 'admin_menu', array( $this, 'add_menu_page' ), 5 );

			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_style' ) );
			// Register metabox to tab manager.
			add_action( 'admin_init', array( $this, 'add_tab_metabox' ), 1 );

		}

		/**
		 * Add a panel under YITH Plugins tab
		 *
		 * @return   void
		 * @since    1.0
		 * @author   Andrea Grillo <andrea.grillo@yithemes.com>
		 * @use     /Yit_Plugin_Panel class
		 * @see      plugin-fw/lib/yit-plugin-panel.php
		 */
		public function add_menu_page() {
			if ( ! empty( $this->_panel ) ) {
				return;
			}

			$admin_tabs = array(
				'settings' => __( 'Tab Manager', 'yith-woocommerce-tab-manager' ),
			);

			if ( ! defined( 'YWTM_PREMIUM' ) ) {
				$admin_tabs['premium'] = __( 'Premium Version', 'yith-woocommerce-tab-manager' );
			}

			$args = array(
				'create_menu_page' => true,
				'parent_slug'      => '',
				'page_title'       =>  'YITH WooCommerce Tab Manager',
				'menu_title'       => 'Tab Manager',
				'capability'       => 'manage_options',
				'plugin_slug'      => YWTM_SLUG,
				'parent'           => '',
				'class'            => yith_set_wrapper_class(),
				'parent_page'      => 'yit_plugin_panel',
				'page'             => $this->_panel_page,
				'admin-tabs'       => $admin_tabs,
				'options-path'     => YWTM_DIR . '/plugin-options',
			);

			$this->_panel = new YIT_Plugin_Panel_WooCommerce( $args );
		}

		/**
		 * Add the action links to plugin admin page
		 *
		 * @param array  $new_row_meta_args The new plugin meta.
		 * @param array  $plugin_meta The plugin meta.
		 * @param string $plugin_file The filename of plugin.
		 * @param array  $plugin_data The plugin data.
		 * @param string $status The plugin status.
		 * @param string $init_file The filename of plugin.
		 *
		 * @return   array
		 * @since    1.0
		 * @author   Andrea Grillo <andrea.grillo@yithemes.com>
		 * @use plugin_row_meta
		 */
		public function plugin_row_meta( $new_row_meta_args, $plugin_meta, $plugin_file, $plugin_data, $status, $init_file = 'YWTM_FREE_INIT' ) {

			if ( defined( $init_file ) && constant( $init_file ) === $plugin_file ) {
				$new_row_meta_args['slug'] = YWTM_SLUG;

			}

			return $new_row_meta_args;

		}

		/**
		 * Premium Tab Template
		 *
		 * Load the premium tab template on admin page
		 *
		 * @since   1.0.0
		 * @author  Andrea Grillo <andrea.grillo@yithemes.com>
		 * @return  void
		 */
		public function premium_tab() {
			$premium_tab_template = YWTM_TEMPLATE_PATH . '/admin/' . $this->_premium;
			if ( file_exists( $premium_tab_template ) ) {
				include_once $premium_tab_template;
			}
		}

		/**
		 * Action Links, add the action links to plugin admin page
		 *
		 * @param array $links | links plugin array.
		 *
		 * @return   array
		 * @since    1.0
		 * @author   Andrea Grillo <andrea.grillo@yithemes.com>
		 * @use plugin_action_links_{$plugin_file_name}
		 */
		public function action_links( $links ) {
			$is_premium = defined( 'YWTM_INIT' );
			$links      = yith_add_action_links( $links, $this->_panel_page, $is_premium );

			return $links;
		}

		/**
		 * Include style and script files
		 *
		 * @author YITH
		 * @since 1.0.0
		 */
		public function enqueue_admin_style() {

			wp_register_style( 'yit-tab-style', YWTM_ASSETS_URL . 'css/yith-tab-manager-admin.css', array(), YWTM_VERSION );

			$current_screen = get_current_screen();
			$is_panel_page  = isset( $_GET['page'] ) && 'yith_wc_tab_manager_panel' === $_GET['page']; // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

			if ( ( $is_panel_page ) || ( isset( $current_screen->post_type ) && ( in_array( $current_screen->post_type, array( 'ywtm_tab', 'product' ), true ) ) ) ) {

				wp_enqueue_style( 'yit-tab-style' );

			}

		}


		/**
		 * Add_tab_metabox, register metabox for global tab
		 *
		 * @author YITH
		 * @since 1.0.0
		 */
		public function add_tab_metabox() {

			$args = include_once YWTM_INC . '/metabox/tab-metabox.php';

			if ( ! function_exists( 'YIT_Metabox' ) ) {
				require_once YWTM_DIR . 'plugin-fw/yit-plugin.php';
			}
			$metabox = YIT_Metabox( 'yit-tab-manager-setting' );
			$metabox->init( $args );
		}

		/**
		 * Returns single instance of the class
		 *
		 * @author YITH
		 * @return YITH_WCTM_Admin
		 * @since 2.0.0
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}


	}
}


/**
 * Return the unique instance of the admin class
 *
 * @author YITH
 * @return YITH_WCTM_Admin|YITH_WCTM_Admin_Premium
 */
function YITH_Tab_Manager_Admin() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName

	if ( defined( 'YWTM_PREMIUM' ) && class_exists( 'YITH_WCTM_Admin_Premium' ) ) {
		return YITH_WCTM_Admin_Premium::get_instance();
	} else {
		return YITH_WCTM_Admin::get_instance();
	}
}
