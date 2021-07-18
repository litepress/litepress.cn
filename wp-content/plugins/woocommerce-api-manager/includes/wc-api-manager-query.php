<?php

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * WooCommerce API Manager Query Class
 *
 * @since       1.4.4
 *
 * @author      Todd Lahman LLC
 * @copyright   Copyright (c) Todd Lahman LLC
 * @package     WooCommerce API Manager/query
 */
class WC_API_Manager_Query extends WC_Query {

	public $api_keys_endpoint  = 'api-keys';
	public $downloads_endpoint = 'api-downloads';

	public function __construct() {
		add_action( 'init', array( $this, 'add_endpoints' ) );
		add_filter( 'the_title', array( $this, 'change_endpoint_title' ), 11, 1 );

		if ( ! is_admin() ) {
			add_filter( 'query_vars', array( $this, 'add_query_vars' ), 0 );
			add_action( 'parse_request', array( $this, 'parse_request' ), 0 );
			add_filter( 'woocommerce_get_breadcrumb', array( $this, 'add_breadcrumb' ), 10 );
			add_action( 'pre_get_posts', array( $this, 'pre_get_posts' ), 11 );
			add_filter( 'woocommerce_get_query_vars', array( $this, 'add_wc_am_query_vars' ) );
			// Inserting new tab/page into the My Account page.
			add_filter( 'woocommerce_account_menu_items', array( $this, 'add_menu_items' ) );
			add_filter( 'woocommerce_get_endpoint_url', array( $this, 'get_endpoint_url' ), 10, 4 );
			add_action( 'woocommerce_account_' . $this->api_keys_endpoint . '_endpoint', array( $this, 'api_keys_endpoint_content' ) );
			add_action( 'woocommerce_account_' . $this->downloads_endpoint . '_endpoint', array( $this, 'downloads_endpoint_content' ) );
		}

		$this->init_query_vars();

		/**
		 * @since 2.0
		 */
		if ( WCAM()->is_woocommerce_pre( '3.4' ) ) {
			add_filter( 'woocommerce_account_settings', array( $this, 'add_endpoint_account_settings' ) );
		} else {
			add_filter( 'woocommerce_get_settings_advanced', array( $this, 'add_endpoint_account_settings' ) );
		}
	}

	public function add_endpoints() {
		add_rewrite_endpoint( $this->api_keys_endpoint, EP_ROOT | EP_PAGES );
		add_rewrite_endpoint( $this->downloads_endpoint, EP_ROOT | EP_PAGES );
	}

	/**
	 * Init query vars by loading options.
	 *
	 * @since 1.4.4
	 */
	public function init_query_vars() {
		$this->query_vars = array(
			$this->api_keys_endpoint  => get_option( 'woocommerce_myaccount_' . $this->api_keys_endpoint . '_endpoint', $this->api_keys_endpoint ),
			$this->downloads_endpoint => get_option( 'woocommerce_myaccount_' . $this->downloads_endpoint . '_endpoint', $this->downloads_endpoint ),
		);
	}

	/**
	 * Adds endpoint breadcrumb when viewing API Manager endpoints.
	 *
	 * @since 1.4.4
	 *
	 * @param array $bread_crumbs
	 *
	 * @return array
	 */
	public function add_breadcrumb( $bread_crumbs ) {
		foreach ( $this->query_vars as $key => $query_var ) {
			if ( $this->is_query( $query_var ) ) {
				$bread_crumbs[] = array( $this->get_endpoint_title( $key ) );
			}
		}

		return $bread_crumbs;
	}

	/**
	 * Changes page title on view API Manager page.
	 *
	 * @since 1.4.4
	 *
	 * @param string $title
	 *
	 * @return string
	 */
	public function change_endpoint_title( $title ) {
		if ( in_the_loop() ) {
			foreach ( $this->query_vars as $key => $query_var ) {
				if ( $this->is_query( $query_var ) ) {
					$title = $this->get_endpoint_title( $key );
				}
			}
		}

		return $title;
	}

	/**
	 * Set the API Manager page title when viewing.
	 *
	 * @since 1.4.4
	 * @since WC 4.6.0 Added $action parameter.
	 *
	 * @param string $endpoint
	 * @param string $action Since WC 4.6.0 Added $action parameter.
	 *
	 * @return string
	 */
	public function get_endpoint_title( $endpoint, $action = '' ) {
		switch ( $endpoint ) {
			case $this->api_keys_endpoint :
				$title = esc_html__( 'API Keys', 'woocommerce-api-manager' );
				break;
			case $this->downloads_endpoint :
				$title = esc_html__( 'API Downloads', 'woocommerce-api-manager' );
				break;
			default:
				$title = '';
				break;
		}

		return $title;
	}

	/**
	 * Insert the new endpoint into the My Account menu.
	 *
	 * @since 1.4.4
	 *
	 * @param array $menu_items
	 *
	 * @return array
	 */
	public function add_menu_items( $menu_items ) {
		// Add our menu item after the Orders tab if it exists, otherwise just add it to the end
		if ( array_key_exists( 'orders', $menu_items ) ) {
			$menu_items = WC_AM_ARRAY()->array_insert_after( 'orders', $menu_items, $this->api_keys_endpoint, esc_html__( 'API Keys', 'woocommerce-api-manager' ) );
			$menu_items = WC_AM_ARRAY()->array_insert_after( $this->api_keys_endpoint, $menu_items, $this->downloads_endpoint, esc_html__( 'API Downloads', 'woocommerce-api-manager' ) );
		} else {
			$menu_items[ $this->api_keys_endpoint ]  = esc_html__( 'API Keys', 'woocommerce-api-manager' );
			$menu_items[ $this->downloads_endpoint ] = esc_html__( 'API Downloads', 'woocommerce-api-manager' );
		}

		return $menu_items;
	}

	/**
	 * Endpoint HTML content.
	 *
	 * @since 1.4.4
	 */
	public function api_keys_endpoint_content() {
		wc_get_template( 'myaccount/api-keys.php', array( 'user_id' => get_current_user_id() ), '', WCAM()->plugin_path() . '/templates/' );
	}

	/**
	 * Endpoint HTML content.
	 *
	 * @since 1.4.4
	 */
	public function downloads_endpoint_content() {
		wc_get_template( 'myaccount/api-downloads.php', array( 'user_id' => get_current_user_id() ), '', WCAM()->plugin_path() . '/templates/' );
	}

	/**
	 * Check if the current query is for a type we want to override.
	 *
	 * @since 1.4.4
	 *
	 * @param string $query_var
	 *
	 * @return mixed
	 */
	protected function is_query( $query_var ) {
		global $wp;

		if ( is_main_query() && is_page() && isset( $wp->query_vars[ $query_var ] ) ) {
			$is_query = true;
		} else {
			$is_query = false;
		}

		return apply_filters( 'wc_api_manager_query_is_query', $is_query, $query_var );
	}

	/**
	 * Fix for endpoints on the homepage.
	 *
	 * Based on WC_Query->pre_get_posts(), but only applies the fix for endpoints on the homepage from it
	 * instead of duplicating all the code to handle the main product query.
	 *
	 * @since 1.4.4
	 *
	 * @param mixed $q
	 */
	public function pre_get_posts( $q ) {
		// We only want to affect the main query
		if ( ! $q->is_main_query() ) {
			return;
		}

		if ( $q->is_home() && 'page' === get_option( 'show_on_front' ) && absint( get_option( 'page_on_front' ) ) !== absint( $q->get( 'page_id' ) ) ) {
			$_query = wp_parse_args( $q->query );
			if ( ! empty( $_query ) && array_intersect( array_keys( $_query ), array_keys( $this->query_vars ) ) ) {
				$q->is_page     = true;
				$q->is_home     = false;
				$q->is_singular = true;
				$q->set( 'page_id', (int) get_option( 'page_on_front' ) );
				add_filter( 'redirect_canonical', '__return_false' );
			}
		}
	}

	/**
	 * Gets the URL for an endpoint, which varies depending on permalink settings.
	 *
	 * @since 2.0
	 *
	 * @param string $endpoint
	 * @param string $value
	 * @param string $permalink
	 *
	 * @return string $url
	 */
	public function get_endpoint_url( $url, $endpoint, $value = '', $permalink = '' ) {
		if ( ! empty( $this->query_vars[ $endpoint ] ) ) {
			remove_filter( 'woocommerce_get_endpoint_url', array( $this, 'get_endpoint_url' ) );

			$url = wc_get_endpoint_url( $this->query_vars[ $endpoint ], $value, $permalink );

			add_filter( 'woocommerce_get_endpoint_url', array( $this, 'get_endpoint_url' ), 10, 4 );
		}

		return $url;
	}

	/**
	 * Hooks into `woocommerce_get_query_vars` to make sure query vars defined in this class are also considered `WC_Query` query vars.
	 *
	 * @since 2.0
	 *
	 * @param array $query_vars
	 *
	 * @return array
	 */
	public function add_wc_am_query_vars( $query_vars ) {
		return array_merge( $query_vars, $this->query_vars );
	}

	/**
	 * Add UI option for changing API Manager endpoints in WC settings.
	 *
	 * @since 2.0
	 *
	 * @param mixed $settings
	 *
	 * @return mixed $account_settings
	 */
	public function add_endpoint_account_settings( $settings ) {
		$api_keys_endpoint_setting = array(
			'title'    => __( 'API Keys', 'woocommerce-api-manager' ),
			'desc'     => __( 'Endpoint for the My Account &rarr; API Keys page', 'woocommerce-api-manager' ),
			'id'       => get_option( 'woocommerce_myaccount_' . $this->api_keys_endpoint . '_endpoint', $this->api_keys_endpoint ),
			'type'     => 'text',
			'default'  => $this->api_keys_endpoint,
			'desc_tip' => true,
		);

		$api_downloads_endpoint_setting = array(
			'title'    => __( 'API Downloads', 'woocommerce-api-manager' ),
			'desc'     => __( 'Endpoint for the My Account &rarr; API Downloads page', 'woocommerce-api-manager' ),
			'id'       => get_option( 'woocommerce_myaccount_' . $this->downloads_endpoint . '_endpoint', $this->downloads_endpoint ),
			'type'     => 'text',
			'default'  => $this->downloads_endpoint,
			'desc_tip' => true,
		);

		$this->insert_setting_after( $settings, 'woocommerce_myaccount_view_order_endpoint', array(
			$api_keys_endpoint_setting,
			$api_downloads_endpoint_setting
		), 'multiple_settings' );

		return $settings;
	}

	/**
	 * Insert a setting or an array of settings after another specific setting by its ID.
	 *
	 * @since 2.0
	 *
	 * @param array  $settings                The original list of settings.
	 * @param string $insert_after_setting_id The setting id to insert the new setting after.
	 * @param array  $new_setting             The new setting to insert. Can be a single setting or an array of settings.
	 * @param string $insert_type             The type of insert to perform. Can be 'single_setting' or 'multiple_settings'. Optional. Defaults to a single setting insert.
	 */
	public function insert_setting_after( &$settings, $insert_after_setting_id, $new_setting, $insert_type = 'single_setting' ) {
		if ( is_array( $settings ) ) {
			$original_settings = $settings;
			$settings          = array();

			foreach ( $original_settings as $setting ) {
				$settings[] = $setting;

				if ( isset( $setting[ 'id' ] ) && $insert_after_setting_id === $setting[ 'id' ] ) {
					if ( 'single_setting' === $insert_type ) {
						$settings[] = $new_setting;
					} else {
						$settings = array_merge( $settings, $new_setting );
					}
				}
			}
		}
	}
}

new WC_API_Manager_Query();