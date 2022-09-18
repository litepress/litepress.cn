<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Woo_Alipay {

	protected static $alipay_lib_paths;

	public function __construct( $alipay_lib_paths, $init_hooks = false ) {

		self::$alipay_lib_paths = $alipay_lib_paths;
		$plugin_base_name       = plugin_basename( WOO_ALIPAY_PLUGIN_PATH );

		if ( $init_hooks ) {
			// Add translation
			add_action( 'init', array( $this, 'load_textdomain' ), 0, 0 );
			// Add main scripts & styles
			add_action( 'wp_enqueue_scripts', array( $this, 'add_frontend_scripts' ), 10, 0 );
			// Add admin scripts
			add_action( 'admin_enqueue_scripts', array( $this, 'add_admin_scripts' ), 99, 1 );

			// Add alipay payment gateway
			add_filter( 'woocommerce_payment_gateways', array( $this, 'add_gateway' ), 10, 1 );
			// Add alipay payment gateway settings page
			add_filter( 'plugin_action_links_' . $plugin_base_name, array( $this, 'plugin_edit_link' ), 10, 1 );
			// Display alipay transction number on order page
			add_filter( 'woocommerce_get_order_item_totals', array( $this, 'display_order_meta_for_customer' ), 10, 2 );
			// Add Alipay orphan transactions email notification
			add_filter( 'woocommerce_email_classes', array( $this, 'add_orphan_transaction_woocommerce_email' ), 10, 1 );
		}
	}

	/*******************************************************************
	 * Public methods
	 *******************************************************************/

	public static function activate() {
		wp_cache_flush();

		if ( ! get_option( 'woo_alipay_plugin_version' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';

			$plugin_data = get_plugin_data( WOO_ALIPAY_PLUGIN_FILE );
			$version     = $plugin_data['Version'];

			update_option( 'woo_alipay_plugin_version', $version );
		}
	}

	public static function deactivate() {}

	public static function uninstall() {
		require_once WOO_ALIPAY_PLUGIN_PATH . 'uninstall.php';
	}

	public static function require_lib( $operation_type ) {

		foreach ( self::$alipay_lib_paths[ $operation_type ] as $class_name => $path ) {

			if ( ! class_exists( $class_name ) ) {
				require_once $path;
			}
		}
	}

	public static function locate_template( $template_name, $load = false, $require_once = true ) {
		$paths    = array(
			'plugins/woo-alipay/' . $template_name,
			'woo-alipay/' . $template_name,
			'woocommerce/woo-alipay/' . $template_name,
			$template_name,
		);
		$template = locate_template(
			$paths,
			$load,
			$require_once
		);

		if ( empty( $template ) ) {
			$template = WOO_ALIPAY_PLUGIN_PATH . 'inc/templates/' . $template_name;

			if ( $load && '' !== $template ) {
				load_template( $template, $require_once );
			}
		}

		return $template;
	}

	public function load_textdomain() {
		load_plugin_textdomain( 'woo-alipay', false, 'woo-alipay/languages' );
	}

	public function add_frontend_scripts() {
		$debug   = (bool) ( constant( 'WP_DEBUG' ) );
		$css_ext = ( $debug ) ? '.css' : '.min.css';
		$version = filemtime( WOO_ALIPAY_PLUGIN_PATH . 'css/main' . $css_ext );

		wp_enqueue_style( 'woo-alipay-main-style', WOO_ALIPAY_PLUGIN_URL . 'css/main.css', array(), $version );
	}

	public function add_admin_scripts( $hook ) {

		if ( 'woocommerce_page_wc-settings' === $hook ) {
			$debug       = (bool) ( constant( 'WP_DEBUG' ) );
			$css_ext     = ( $debug ) ? '.css' : '.min.css';
			$js_ext      = ( $debug ) ? '.js' : '.min.js';
			$version_css = filemtime( WOO_ALIPAY_PLUGIN_PATH . 'css/admin/main' . $css_ext );
			$version_js  = filemtime( WOO_ALIPAY_PLUGIN_PATH . 'js/admin/main' . $js_ext );

			wp_enqueue_style(
				'woo-alipay-main-style',
				WOO_ALIPAY_PLUGIN_URL . 'css/admin/main' . $css_ext,
				array(),
				$version_css
			);

			$parameters = array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'debug'    => $debug,
			);

			wp_enqueue_script(
				'woo-alipay-admin-script',
				WOO_ALIPAY_PLUGIN_URL . 'js/admin/main' . $js_ext,
				array( 'jquery' ),
				$version_js,
				true
			);
			wp_localize_script( 'woo-alipay-admin-script', 'WooAlipay', $parameters );
		}
	}

	public function add_gateway( $methods ) {
		$methods[] = 'WC_Alipay';

		return $methods;
	}

	public function plugin_edit_link( $links ) {
		$url = admin_url( 'admin.php?page=wc-settings&tab=checkout&section=woo_alipay' );

		return array_merge(
			array(
				'settings' => '<a href="' . $url . '">' . __( 'Settings', 'woo-alipay' ) . '</a>',
			),
			$links
		);
	}

	public function display_order_meta_for_customer( $total_rows, $order ) {
		$trade_no = $order->get_transaction_id();

		if ( ! empty( $trade_no ) && $order->get_payment_method() === 'alipay' ) {
			$new_row = array(
				'alipay_trade_no' => array(
					'label' => __( 'Transaction:', 'woo-alipay' ),
					'value' => $trade_no,
				),
			);

			$total_rows = array_merge( array_splice( $total_rows, 0, 2 ), $new_row, $total_rows );
		}

		return $total_rows;
	}

	public function pay_notification_endpoint( $endpoint ) {

		return 'wc-api/WC_Alipay/';
	}

	public function add_orphan_transaction_woocommerce_email( $email_classes ) {
		require_once WOO_ALIPAY_PLUGIN_PATH . 'inc/class-wc-email-alipay-orphan-transaction.php';

		$email_classes['WC_Email_Alipay_Orphan_Transaction'] = new WC_Email_Alipay_Orphan_Transaction();

		return $email_classes;
	}

}
