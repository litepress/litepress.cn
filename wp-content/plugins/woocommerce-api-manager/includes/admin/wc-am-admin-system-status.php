<?php

/**
 * WooCommerce API Manager Admin System Status Class
 *
 * @since       2.0.21
 *
 * @author      Todd Lahman LLC
 * @copyright   Copyright (c) Todd Lahman LLC
 * @package     WooCommerce API Manager/Admin/Admin System Status
 */

defined( 'ABSPATH' ) || exit;

class WC_AM_Admin_System_Status {

	private $aws_s3_configured = '';

	/**
	 * @var null
	 */
	private static $_instance = null;

	/**
	 * @static
	 * @return \WC_AM_Admin_System_Status
	 */
	public static function instance() {

		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	private function __construct() {
		$amazon_s3_constants         = defined( 'WC_AM_AWS3_ACCESS_KEY_ID' ) && defined( 'WC_AM_AWS3_SECRET_ACCESS_KEY' );
		$amazon_s3_access_key_id     = get_option( 'woocommerce_api_manager_amazon_s3_access_key_id' );
		$amazon_s3_secret_access_key = get_option( 'woocommerce_api_manager_amazon_s3_secret_access_key' );
		$configured                  = $amazon_s3_constants || ( ! empty( $amazon_s3_access_key_id ) && ! empty( $amazon_s3_secret_access_key ) );
		$this->aws_s3_configured     = ! empty( $configured );

		add_action( 'woocommerce_system_status_report', array( $this, 'render_system_status_items' ) );
	}

	/**
	 * Renders the WooCommerce API Manager information on the WooCommerce status page
	 *
	 * @since 2.1
	 */
	public function render_system_status_items() {
		$wc_api_manager_data = array();

		$this->set_api_manager_version( $wc_api_manager_data );
		$this->set_api_manager_database_version( $wc_api_manager_data );
		$this->set_api_manager_amazon_s3_configured( $wc_api_manager_data );

		if ( $this->aws_s3_configured ) {
			$this->set_api_manager_amazon_s3_region( $wc_api_manager_data );
		}

		$this->set_api_manager_api_key_activations( $wc_api_manager_data );
		$this->set_api_manager_products_count( $wc_api_manager_data );
		$this->set_api_manager_api_resources( $wc_api_manager_data );
		$this->set_api_manager_associated_api_keys( $wc_api_manager_data );
		$this->set_api_manager_cache( $wc_api_manager_data );

		if ( WCAM()->get_db_cache() ) {
			$this->set_api_manager_api_cache_expires( $wc_api_manager_data );
			$this->set_api_manager_database_cache_expires( $wc_api_manager_data );
		}

		$this->set_api_manager_download_url_expires( $wc_api_manager_data );
		$this->set_api_manager_hide_product_order_api_keys( $wc_api_manager_data );
		$this->set_api_manager_secure_hash_count( $wc_api_manager_data );
		$this->set_theme_overrides( $wc_api_manager_data );

		$system_status_sections = array(
			array(
				'title'   => esc_attr__( 'WooCommerce API Manager', 'woocommerce-api-manager' ),
				'tooltip' => esc_attr__( 'This section shows information about the WooCommerce API Manager.', 'woocommerce-api-manager' ),
				'data'    => apply_filters( 'wc_api_manager_system_status', $wc_api_manager_data ),
			),

		);

		foreach ( $system_status_sections as $section ) {
			$section_title   = $section[ 'title' ];
			$section_tooltip = $section[ 'tooltip' ];
			$debug_data      = $section[ 'data' ];

			include( WCAM()->plugin_path() . '/templates/admin/status.php' );
		}
	}

	/**
	 * WooCommerce API Manager Version.
	 *
	 * @since 2.1
	 *
	 * @param $debug_data
	 */
	private function set_api_manager_version( &$debug_data ) {
		$debug_data[ 'wc_api_manager_version' ] = array(
			'name'      => _x( 'WC API Manager Version', 'WC API Manager Version, Label on WooCommerce -> System Status page', 'woocommerce-api-manager' ),
			'label'     => 'WC API Manager Version',
			'note'      => esc_attr( WC_AM_VERSION ),
			'mark'      => '',
			'mark_icon' => '',
		);
	}

	/**
	 * WooCommerce API Manager Database Version.
	 *
	 * @since 2.1
	 *
	 * @param $debug_data
	 */
	private function set_api_manager_database_version( &$debug_data ) {
		$wc_am_db_version = get_option( 'wc_am_db_version' );

		$debug_data[ 'wc_api_manager_database_version' ] = array(
			'name'      => _x( 'WC API Manager Database Version', 'WC API Manager Database Version, Label on WooCommerce -> System Status page', 'woocommerce-api-manager' ),
			'label'     => 'WC API Manager Database Version',
			'note'      => ! empty( $wc_am_db_version ) ? esc_attr( $wc_am_db_version ) : 'Not yet upgraded.',
			'mark'      => '',
			'mark_icon' => '',
		);
	}

	/**
	 * Amazon S3 Download Configured.
	 *
	 * @since 2.1.3
	 *
	 * @param $debug_data
	 */
	private function set_api_manager_amazon_s3_configured( &$debug_data ) {
		$debug_data[ 'wc_api_manager_amazon_s3_configured' ] = array(
			'name'    => _x( 'Amazon S3 Configured', 'Amazon S3 Configured, Label on WooCommerce -> System Status page', 'woocommerce-api-manager' ),
			'label'   => 'Amazon S3 Configured',
			'note'    => $this->aws_s3_configured ? esc_attr__( 'Yes', 'woocommerce-api-manager' ) : esc_attr__( 'No', 'woocommerce-api-manager' ),
			'success' => $this->aws_s3_configured ? 1 : 0,
		);
	}

	/**
	 * WooCommerce API Manager Database Version.
	 *
	 * @since 2.1
	 *
	 * @param $debug_data
	 */
	private function set_api_manager_amazon_s3_region( &$debug_data ) {
		$aws_s3_region = get_option( 'woocommerce_api_manager_aws_s3_region' );

		$debug_data[ 'wc_api_manager_$aws_s3_region' ] = array(
			'name'      => _x( 'Amazon S3 Region', 'Amazon S3 Region, Label on WooCommerce -> System Status page', 'woocommerce-api-manager' ),
			'label'     => 'Amazon S3 Region',
			'note'      => ! empty( $aws_s3_region ) ? esc_attr( $aws_s3_region ) : sprintf( __( '%sPick a region.%s', 'woocommerce-api-manager' ), '<a href="' . esc_url( admin_url( 'admin.php?page=wc-settings&tab=api_manager' ) ) . '">', '</a>' ),
			'mark'      => '',
			'mark_icon' => '',
		);
	}

	/**
	 * API Key Activations.
	 *
	 * @since 2.1
	 *
	 * @param $debug_data
	 */
	private function set_api_manager_api_key_activations( &$debug_data ) {
		$debug_data[ 'wc_api_manager_api_key_activations' ] = array(
			'name'      => _x( 'API Key Activations', 'API Key Activations Count, Label on WooCommerce -> System Status page', 'woocommerce-api-manager' ),
			'label'     => 'API Key Activations',
			'note'      => esc_attr( WC_AM_API_ACTIVATION_DATA_STORE()->get_activation_count() ),
			'mark'      => '',
			'mark_icon' => '',
		);
	}

	/**
	 * API Products Count.
	 *
	 * @since 2.1
	 *
	 * @param $debug_data
	 */
	private function set_api_manager_products_count( &$debug_data ) {
		$debug_data[ 'wc_api_manager_products_count' ] = array(
			'name'      => _x( 'API Products', 'API Products Count, Label on WooCommerce -> System Status page', 'woocommerce-api-manager' ),
			'label'     => 'API Products',
			'note'      => esc_attr( WC_AM_PRODUCT_DATA_STORE()->get_api_products_count() ),
			'mark'      => '',
			'mark_icon' => '',
		);
	}

	/**
	 * API Resources.
	 *
	 * @since 2.1
	 *
	 * @param $debug_data
	 */
	private function set_api_manager_api_resources( &$debug_data ) {
		$debug_data[ 'wc_api_manager_api_resources' ] = array(
			'name'      => _x( 'API Resources', 'API Resources Count, Label on WooCommerce -> System Status page', 'woocommerce-api-manager' ),
			'label'     => 'API Resources',
			'note'      => esc_attr( WC_AM_API_RESOURCE_DATA_STORE()->get_api_resource_count() ),
			'mark'      => '',
			'mark_icon' => '',
		);
	}

	/**
	 * Associated API Keys.
	 *
	 * @since 2.1
	 *
	 * @param $debug_data
	 */
	private function set_api_manager_associated_api_keys( &$debug_data ) {
		$debug_data[ 'wc_api_manager_associated_api_keys' ] = array(
			'name'      => _x( 'Associated API Keys', 'Associated API Keys Count, Label on WooCommerce -> System Status page', 'woocommerce-api-manager' ),
			'label'     => 'Associated API Keys',
			'note'      => esc_attr( WC_AM_ASSOCIATED_API_KEY_DATA_STORE()->get_associated_api_key_count() ),
			'mark'      => '',
			'mark_icon' => '',
		);
	}

	/**
	 * Database Cache on or off.
	 *
	 * @since 2.1
	 *
	 * @param $debug_data
	 */
	private function set_api_manager_cache( &$debug_data ) {
		$debug_data[ 'wc_api_manager_cache' ] = array(
			'name'    => _x( 'Cache Enabled', 'Cache Enabled, Label on WooCommerce -> System Status page', 'woocommerce-api-manager' ),
			'label'   => 'Cache Enabled',
			'note'    => WCAM()->get_db_cache() ? esc_attr__( 'Yes', 'woocommerce-api-manager' ) : esc_attr__( 'No', 'woocommerce-api-manager' ),
			'success' => WCAM()->get_db_cache() ? 1 : 0,
		);
	}

	/**
	 * API Cache Expires.
	 *
	 * @since 2.2.1
	 *
	 * @param $debug_data
	 */
	private function set_api_manager_api_cache_expires( &$debug_data ) {
		$debug_data[ 'wc_api_manager_api_cache_expires' ] = array(
			'name'      => _x( 'API Cache Expires', 'API Cache Expires, Label on WooCommerce -> System Status page', 'woocommerce-api-manager' ),
			'label'     => 'API Cache Expires',
			'note'      => ( absint( WCAM()->get_api_cache_expires() ) / 60 ) . esc_html__( ' hour', 'woocommerce-api-manager' ),
			'mark'      => '',
			'mark_icon' => '',
		);
	}

	/**
	 * Database Cache Expires.
	 *
	 * @since 2.1
	 *
	 * @param $debug_data
	 */
	private function set_api_manager_database_cache_expires( &$debug_data ) {
		$debug_data[ 'wc_api_manager_database_cache_expires' ] = array(
			'name'      => _x( 'Database Cache Expires', 'Database Cache Expires, Label on WooCommerce -> System Status page', 'woocommerce-api-manager' ),
			'label'     => 'Database Cache Expires',
			'note'      => ( absint( WCAM()->get_db_cache_expires() ) / 60 ) . esc_html__( ' hours', 'woocommerce-api-manager' ),
			'mark'      => '',
			'mark_icon' => '',
		);
	}

	/**
	 * Download URLs Expire.
	 *
	 * @since 2.1
	 *
	 * @param $debug_data
	 */
	private function set_api_manager_download_url_expires( &$debug_data ) {
		$time         = get_option( 'woocommerce_api_manager_url_expire' );
		$expires_time = $time < 2 ? esc_attr( $time ) . esc_attr__( ' day', 'woocommerce-api-manager' ) : esc_attr( $time ) . esc_attr__( ' days', 'woocommerce-api-manager' );

		$debug_data[ 'wc_api_manager_download_url_expires' ] = array(
			'name'      => _x( 'Download URLs Expire', 'Download URLs Expire, Label on WooCommerce -> System Status page', 'woocommerce-api-manager' ),
			'label'     => 'Download URLs Expire',
			'note'      => $expires_time,
			'mark'      => '',
			'mark_icon' => '',
		);
	}

	/**
	 * Hide Product Order API Keys?
	 *
	 * @since 2.1
	 *
	 * @param $debug_data
	 */
	private function set_api_manager_hide_product_order_api_keys( &$debug_data ) {
		$hide_keys = get_option( 'woocommerce_api_manager_hide_product_order_api_keys' ) === 'yes';

		$debug_data[ 'wc_api_manager_hide_product_order_api_keys' ] = array(
			'name'    => _x( 'Hide Product Order API Keys?', 'Hide Product Order API Keys, Label on WooCommerce -> System Status page', 'woocommerce-api-manager' ),
			'label'   => 'Hide Product Order API Keys?',
			'note'    => $hide_keys ? esc_attr__( 'Yes', 'woocommerce-api-manager' ) : esc_attr__( 'No', 'woocommerce-api-manager' ),
			'success' => $hide_keys ? 1 : 0,
		);
	}

	/**
	 * Secure Download URL Hashes Count.
	 *
	 * @since 2.1
	 *
	 * @param $debug_data
	 */
	private function set_api_manager_secure_hash_count( &$debug_data ) {
		$debug_data[ 'wc_api_manager_secure_hash_count' ] = array(
			'name'      => _x( 'Secure Download URL Hashes', 'Secure Download URL Hashes Count, Label on WooCommerce -> System Status page', 'woocommerce-api-manager' ),
			'label'     => 'Secure Download URL Hashes',
			'note'      => esc_attr( WC_AM_PRODUCT_DATA_STORE()->get_secure_hash_count() ),
			'mark'      => '',
			'mark_icon' => '',
		);
	}

	/**
	 * List WooCommerce API Manager template files that have been overridden.
	 *
	 * @since 2.1
	 *
	 * @param $debug_data
	 */
	private function set_theme_overrides( &$debug_data ) {
		$theme_overrides = $this->get_theme_overrides();

		if ( ! empty( $theme_overrides[ 'overrides' ] ) ) {
			$debug_data[ 'wc_am_theme_overrides' ] = array(
				'name'  => _x( 'WooCommerce API Manager Template Theme Overrides', 'label for the system status page', 'woocommerce-api-manager' ),
				'label' => 'WooCommerce API Manager Template Theme Overrides',
				'data'  => $theme_overrides[ 'overrides' ],
			);

			// Include a note on how to update if the templates are out of date.
			if ( ! empty( $theme_overrides[ 'has_outdated_templates' ] ) && true === $theme_overrides[ 'has_outdated_templates' ] ) {
				$debug_data[ 'wc_am_theme_overrides' ] += array(
					'mark_icon' => 'warning',
					'note'      => sprintf( __( '%sLearn how to update%s', 'woocommerce-api-manager' ), '<a href="https://docs.woocommerce.com/document/fix-outdated-templates-woocommerce/" target="_blank">', '</a>' ),
				);
			}
		}
	}

	/**
	 * Determine WooCommerce API Manager template files that have been overridden.
	 *
	 * @since 2.1
	 *
	 * @return array
	 */
	private function get_theme_overrides() {
		$wc_am_template_dir = dirname( WCAM()->get_file() ) . '/templates/';
		$wc_template_path   = trailingslashit( wc()->template_path() );
		$theme_root         = trailingslashit( get_theme_root() );
		$overridden         = array();
		$outdated           = false;
		$templates          = WC_Admin_Status::scan_template_files( $wc_am_template_dir );

		foreach ( $templates as $file ) {
			$theme_file = $is_outdated = false;
			$locations  = array(
				get_stylesheet_directory() . "/{$file}",
				get_stylesheet_directory() . "/{$wc_template_path}{$file}",
				get_template_directory() . "/{$file}",
				get_template_directory() . "/{$wc_template_path}{$file}",
			);

			foreach ( $locations as $location ) {
				if ( is_readable( $location ) ) {
					$theme_file = $location;
					break;
				}
			}

			if ( ! empty( $theme_file ) ) {
				$core_version  = WC_Admin_Status::get_file_version( $wc_am_template_dir . $file );
				$theme_version = WC_Admin_Status::get_file_version( $theme_file );

				$overridden_template_output = sprintf( '<code>%s</code>', esc_html( str_replace( $theme_root, '', $theme_file ) ) );

				if ( $core_version && ( empty( $theme_version ) || version_compare( $theme_version, $core_version, '<' ) ) ) {
					$outdated                   = true;
					$overridden_template_output .= sprintf( /* translators: %1$s is the file version, %2$s is the core version */ esc_html__( 'version %1$s is out of date. The core version is %2$s', 'woocommerce-api-manager' ), '<strong style="color:red">' . esc_html( $theme_version ) . '</strong>', '<strong>' . esc_html( $core_version ) . '</strong>' );
				}

				$overridden[ 'overrides' ][] = $overridden_template_output;
			}
		}

		$overridden[ 'has_outdated_templates' ] = $outdated;

		return $overridden;
	}

} // end of class