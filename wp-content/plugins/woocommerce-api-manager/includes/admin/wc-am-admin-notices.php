<?php
/**
 * Display notices in admin
 *
 * @package     WooCommerce API Manager/Admin Notices
 * @author      Todd Lahman LLC
 * @copyright   Copyright (c) Todd Lahman LLC
 * @since       2.0
 */
defined( 'ABSPATH' ) || exit;

class WC_AM_Admin_Notices {

	/**
	 * Stores notices.
	 *
	 * @var array
	 */
	private $notices = array();

	/**
	 * Array of notices - name => callback.
	 * //'api_products_updating' => 'api_products_updating_notice'
	 *
	 * @since 2.0
	 *
	 * @var array
	 */
	private $core_notices = array(
		'update' => 'update_notice'
	);

	/**
	 * @var null
	 */
	private static $_instance = null;

	/**
	 * @static
	 * @return null|\WC_AM_Admin_Notices
	 */
	public static function instance() {

		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	private function __construct() {
		$this->notices = get_option( 'wc_am_admin_notices', array() );

		add_action( 'wp_loaded', array( $this, 'hide_notices' ) );
		add_action( 'shutdown', array( $this, 'store_notices' ) );

		// Prevents Call to undefined function wp_get_current_user() error.
		include_once( ABSPATH . 'wp-includes/pluggable.php' );

		if ( current_user_can( 'manage_woocommerce' ) ) {
			add_action( 'admin_print_styles', array( $this, 'add_notices' ) );
		}
	}

	/**
	 * Store notices to DB.
	 *
	 * @since 2.0
	 */
	public function store_notices() {
		update_option( 'wc_am_admin_notices', $this->get_notices() );
	}

	/**
	 * Get notices
	 *
	 * @since 2.0
	 *
	 * @return array
	 */
	public function get_notices() {
		return $this->notices;
	}

	/**
	 * Remove all notices.
	 *
	 * @since 2.0
	 */
	public function remove_all_notices() {
		$this->notices = array();
	}

	/**
	 * Show a notice.
	 *
	 * @since 2.0
	 *
	 * @param string $name
	 */
	public function add_notice( $name ) {
		$this->notices = array_unique( array_merge( $this->get_notices(), array( $name ) ) );
	}

	/**
	 * Remove a notice from being displayed.
	 *
	 * @since 2.0
	 *
	 * @param  string $name
	 */
	public function remove_notice( $name ) {
		$this->notices = array_diff( $this->get_notices(), array( $name ) );
		delete_option( 'wc_am_admin_notice_' . $name );
	}

	/**
	 * See if a notice is being shown.
	 *
	 * @since 2.0
	 *
	 * @param  string $name
	 *
	 * @return boolean
	 */
	public function has_notice( $name ) {
		return in_array( $name, $this->get_notices() );
	}

	/**
	 * Hide a notice if the GET variable is set.
	 *
	 * @since 2.0
	 */
	public function hide_notices() {
		if ( isset( $_GET[ 'wc-am-hide-notice' ] ) && isset( $_GET[ '_wc_am_notice_nonce' ] ) ) { // WPCS: input var ok, CSRF ok.
			if ( ! wp_verify_nonce( sanitize_key( wp_unslash( $_GET[ '_wc_am_notice_nonce' ] ) ), 'wc_am_hide_notices_nonce' ) ) { // WPCS: input var ok, CSRF ok.
				wp_die( esc_html__( 'Action failed. Please refresh the page and retry.', 'woocommerce-api-manager' ) );
			}

			if ( ! current_user_can( 'manage_woocommerce' ) ) {
				wp_die( esc_html__( 'You don&#8217;t have permission to do this.', 'woocommerce-api-manager' ) );
			}

			$hide_notice = sanitize_text_field( wp_unslash( $_GET[ 'wc-am-hide-notice' ] ) ); // WPCS: input var ok, CSRF ok.

			$this->remove_notice( $hide_notice );

			update_user_meta( get_current_user_id(), 'dismissed_' . $hide_notice . '_notice', true );

			do_action( 'wc_am_hide_' . $hide_notice . '_notice' );
		}
	}

	/**
	 * Add notices + styles if needed.
	 *
	 * @since 2.0
	 */
	public function add_notices() {
		$notices = $this->get_notices();

		if ( empty( $notices ) ) {
			return;
		}

		$screen          = get_current_screen();
		$screen_id       = $screen ? $screen->id : '';
		$show_on_screens = array(
			'dashboard',
			'plugins',
		);

		// Notices should only show on WooCommerce screens, the main dashboard, and on the plugins screen.
		if ( ! in_array( $screen_id, wc_get_screen_ids(), true ) && ! in_array( $screen_id, $show_on_screens, true ) ) {
			return;
		}

		wp_enqueue_style( 'woocommerce-api-manager--activation', plugins_url( '/includes/assets/css/activation.css', WCAM()->get_plugin_file() ), array(), WC_AM_VERSION );

		// Add RTL support.
		wp_style_add_data( 'woocommerce-api-manager-activation', 'rtl', 'replace' );

		foreach ( $notices as $notice ) {
			if ( ! empty( $this->core_notices[ $notice ] ) && apply_filters( 'wc_api_manager_show_admin_notice', true, $notice ) ) {
				add_action( 'admin_notices', array( $this, $this->core_notices[ $notice ] ) );
			} else {
				add_action( 'admin_notices', array( $this, 'output_custom_notices' ) );
			}
		}
	}

	/**
	 * Add a custom notice.
	 *
	 * @since 2.0
	 *
	 * @param string $name
	 * @param string $notice_html
	 */
	public function add_custom_notice( $name, $notice_html ) {
		$this->add_notice( $name );
		update_option( 'wc_am_admin_notice_' . $name, wp_kses_post( $notice_html ) );
	}

	/**
	 * Output any stored custom notices.
	 */
	public function output_custom_notices() {
		$notices = $this->get_notices();

		if ( ! empty( $notices ) ) {
			foreach ( $notices as $notice ) {
				if ( empty( $this->core_notices[ $notice ] ) ) {
					$notice_html = get_option( 'wc_am_admin_notice_' . $notice );

					if ( $notice_html ) {
						include( 'views/html-notice-custom.php' );
					}
				}
			}
		}
	}

	/**
	 * If we need to update, include a message with the update button.
	 *
	 * @since 2.0
	 */
	public function update_notice() {
		if ( version_compare( get_option( 'wc_am_db_version' ), WC_AM_VERSION, '<' ) ) {
			$updater = new WCAM_Background_Updater();
			if ( $updater->is_updating() || ! empty( $_GET[ 'do_update_woocommerce_api_manager' ] ) ) {
				include( 'views/html-notice-updating.php' );
			} else {
				include( 'views/html-notice-update.php' );
			}
		} else {
			include( 'views/html-notice-updated.php' );
		}
	}

	/**
	 * Notice shown when API products updating background process is running.
	 *
	 * @since 2.0
	 */
	public function api_products_updating_notice() {
		include( 'views/html-notice-api-products-update.php' );
	}
}