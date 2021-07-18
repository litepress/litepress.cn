<?php

/**
 * Plugin Name: WooCommerce API Manager
 * Plugin URI: https://woocommerce.com/products/woocommerce-api-manager/
 * Description: An API resource manager.
 * Version: 2.3.10
 * Author: Todd Lahman LLC
 * Author URI: https://www.toddlahman.com
 * Developer: Todd Lahman LLC
 * Developer URI: https://www.toddlahman.com
 * Text Domain: woocommerce-api-manager
 * Domain Path: /i18n/languages/
 * WC requires at least: 3.4
 * WC tested up to: 5.2.2
 * Woo: 260110:f7cdcfb7de76afa0889f07bcb92bf12e
 * Requires WP: 4.7
 * Requires PHP: 7.0
 *
 * Intellectual Property rights, and copyright, reserved by Todd Lahman, LLC as allowed by law include,
 * but are not limited to, the working concept, function, and behavior of this plugin,
 * the logical code structure and expression as written.
 *
 * @since       1.0
 * @author      Todd Lahman LLC
 * @category    Plugin
 * @copyright   Copyright (c) Todd Lahman LLC (support@toddlahman.com)
 * @package     WooCommerce API Manager
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Constants
 */
define( 'WC_AM_VERSION', '2.3.10' );
define( 'WC_AM_WC_MIN_REQUIRED_VERSION', '3.4' );
define( 'WC_AM_REQUIRED_PHP_VERSION', '7.0' );
define( 'WC_AM_WC_SUBS_MIN_REQUIRED_VERSION', '2.3' );

/**
 * Required functions.
 */
if ( ! function_exists( 'woothemes_queue_update' ) || ! function_exists( 'is_woocommerce_active' ) ) {
	require_once( 'woo-includes/woo-functions.php' );
}

woothemes_queue_update( plugin_basename( __FILE__ ), 'f7cdcfb7de76afa0889f07bcb92bf12e', '260110' );

if ( ! is_woocommerce_active() ) {
	add_action( 'admin_notices', 'WooCommerce_API_Manager::woocommerce_inactive_notice' );

	return;
}

// Required PHP version notice.
if ( version_compare( PHP_VERSION, WC_AM_REQUIRED_PHP_VERSION, '<' ) ) {
	add_action( 'admin_notices', 'WooCommerce_API_Manager::wam_php_requirement' );

	return;
}

// Disable the WooCommerce API Manager until WooCommerce has been upgraded to the required minimum version.
$wam_wc_active_version = get_option( 'woocommerce_version' );

if ( ! empty( $wam_wc_active_version ) && version_compare( $wam_wc_active_version, WC_AM_WC_MIN_REQUIRED_VERSION, '<' ) ) {
	add_action( 'admin_notices', 'WooCommerce_API_Manager::upgrade_wc_am_warning' );

	return;
}

/**
 * Disable the WooCommerce API Manager until WooCommerce Subscriptions has been upgraded to the required minimum version,
 * if WooCommerce Subscriptions is installed and active.
 *
 * @since 2.0.15
 */
if ( WooCommerce_API_Manager::is_wc_subscriptions_active_static() ) {
	$wam_wc_subs_active_version = get_option( 'woocommerce_subscriptions_active_version' );

	if ( ! empty( $wam_wc_subs_active_version ) && version_compare( $wam_wc_subs_active_version, WC_AM_WC_SUBS_MIN_REQUIRED_VERSION, '<' ) ) {
		add_action( 'admin_notices', 'WooCommerce_API_Manager::upgrade_wc_sub_am_warning' );

		return;
	}
}

final class WooCommerce_API_Manager {

	/**
	 * @var string
	 */
	private $db_cache                   = false;
	private $db_cache_expires           = 5;
	private $api_cache_expires          = 5;
	private $wc_subs_exist              = false;
	private $file;
	private $plugin_file;
	private $grant_access_after_payment = false;
	private $unlimited_activation_limit = 0;

	/**
	 * @var null The single instance of the class
	 */
	private static $_instance = null;

	/**
	 * Singular class instance safeguard.
	 * Ensures only one instance of a class can be instantiated.
	 * Follows a singleton design pattern.
	 *
	 * @static
	 *
	 * @return WooCommerce_API_Manager - Main instance
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Cloning is forbidden.
	 *
	 * @since 2.0
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Cloning is forbidden.', 'woocommerce-api-manager' ), '2.0' );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 2.0
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Unserializing instances of this class is forbidden.', 'woocommerce-api-manager' ), '2.0' );
	}

	private function __construct() {
		$this->db_cache                   = true;
		$this->db_cache_expires           = 1440; // 24 hours.
		$this->api_cache_expires          = 60; // 1 hour.
		$this->wc_subs_exist              = $this->is_wc_subscriptions_active();
		$this->file                       = plugin_basename( __FILE__ );
		$this->plugin_file                = __FILE__;
		$this->grant_access_after_payment = get_option( 'woocommerce_downloads_grant_access_after_payment' ) === 'yes' ? true : false;
		$this->unlimited_activation_limit = apply_filters( 'wc_api_manager_unlimited_activation_limit', 100000 ); // since 2.2

		// Include required files
		$this->includes();
	}

	/**
	 * @since 2.3.1
	 *
	 * @return bool
	 */
	public function get_db_cache() {
		return $this->db_cache;
	}

	/**
	 * @since 2.3.1
	 *
	 * @return int
	 */
	public function get_db_cache_expires() {
		return $this->db_cache_expires;
	}

	/**
	 * @since 2.3.1
	 *
	 * @return int
	 */
	public function get_api_cache_expires() {
		return $this->api_cache_expires;
	}

	/**
	 * Return the WooCommerce version.
	 *
	 * @since 2.0
	 *
	 * @return string|bool
	 */
	public function get_wc_version() {
		if ( defined( 'WC_VERSION' ) && WC_VERSION ) {
			return WC_VERSION;
		} elseif ( defined( 'WOOCOMMERCE_VERSION' ) && WOOCOMMERCE_VERSION ) {
			return WOOCOMMERCE_VERSION;
		} elseif ( ! is_null( get_option( 'woocommerce_version', null ) ) ) {
			return get_option( 'woocommerce_version' );
		}

		return false;
	}

	/**
	 * @since 2.3.1
	 *
	 * @return bool
	 */
	public function get_wc_subs_exist() {
		return $this->wc_subs_exist;
	}

	/**
	 * @since 2.3.1
	 *
	 * @return string
	 */
	public function get_file() {
		return $this->file;
	}

	/**
	 * @since 2.3.1
	 *
	 * @return string
	 */
	public function get_plugin_file() {
		return $this->plugin_file;
	}

	/**
	 * @since 2.3.1
	 *
	 * @return bool
	 */
	public function get_grant_access_after_payment() {
		return $this->grant_access_after_payment;
	}

	/**
	 * @since 2.3.1
	 *
	 * @return int
	 */
	public function get_unlimited_activation_limit() {
		return (int) $this->unlimited_activation_limit;
	}

	/**
	 * Returns the WC_AM_API_Requests class object.
	 *
	 * @since 2.0
	 *
	 * @return \WC_AM_API_Requests
	 */
	function api_requests() {
		return WC_AM_API_REQUESTS( $_REQUEST );
	}

	/**
	 * Define a constant if it is not already defined.
	 *
	 * @since 2.0
	 *
	 * @param string $name  Constant name.
	 * @param string $value Value.
	 */
	public function maybe_define_constant( $name, $value ) {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	}

	/**
	 * Get the plugin's url.
	 *
	 * @return string
	 */
	public function plugin_url() {
		return plugins_url( '/', __FILE__ );
	}

	/**
	 * Get the plugin directory url.
	 *
	 * @return string
	 */
	public function plugins_dir_url() {
		return plugin_dir_url( __FILE__ );
	}

	/**
	 * Get the plugin path.
	 *
	 * @return string
	 */
	public function plugin_path() {
		return untrailingslashit( plugin_dir_path( __FILE__ ) );
	}

	/**
	 * Get the plugin basename.
	 *
	 * @return string
	 */
	public function plugins_basename() {
		return untrailingslashit( $this->file );
	}

	/**
	 * Get the directory name of the plugin basename.
	 *
	 * @since 2.0.10
	 *
	 * @return string
	 */
	public function plugin_dirname_of_plugin_basename() {
		return dirname( untrailingslashit( $this->file ) );
	}

	/**
	 * Get Ajax URL.
	 *
	 * @return string
	 */
	public function ajax_url() {
		return admin_url( 'admin-ajax.php' );
	}

	/**
	 * admin_scripts function.
	 */
	public function admin_scripts() {
		wp_enqueue_style( 'woocommerce_api_manager_admin_styles', $this->plugin_url() . 'includes/assets/css/admin-min.css', array(), WC_AM_VERSION );
	}

	/**
	 * Get styles for the frontend
	 *
	 * @param array
	 *
	 * @return array
	 */
	public function enqueue_styles( $styles ) {
		if ( is_account_page() ) {
			$styles[ 'woocommerce-api-manager' ] = array(
				'src'     => $this->plugin_url() . 'includes/assets/css/woocommerce-api-manager-min.css?' . filemtime( $this->plugin_path() . '/includes/assets/css/woocommerce-api-manager-min.css' ),
				'deps'    => 'woocommerce-smallscreen',
				'version' => WC_AM_VERSION,
				'media'   => 'all'
			);

			if ( wp_get_theme() == 'Storefront' ) {
				$styles[ 'wc-am-storefront-icons' ] = array(
					'src'     => $this->plugin_url() . 'includes/assets/css/wc-am-storefront-icons-min.css?' . filemtime( $this->plugin_path() . '/includes/assets/css/wc-am-storefront-icons-min.css' ),
					'deps'    => 'woocommerce-smallscreen',
					'version' => WC_AM_VERSION,
					'media'   => 'all'
				);
			}
		}

		return $styles;
	}

	/**
	 * Output queued JavaScript code in the footer inline.
	 *
	 * @since 1.3
	 *
	 * @param string $wc_queued_js JavaScript
	 */
	public function wc_print_js( $wc_queued_js ) {
		if ( ! empty( $wc_queued_js ) ) {
			// Sanitize
			$wc_queued_js = wp_check_invalid_utf8( $wc_queued_js );
			$wc_queued_js = preg_replace( '/&#(x)?0*(?(1)27|39);?/i', "'", $wc_queued_js );
			$wc_queued_js = str_replace( "\r", '', $wc_queued_js );

			echo "<!-- WooCommerce API Manager JavaScript -->\n<script type=\"text/javascript\">\njQuery(function($) ";
			echo "{";
			echo $wc_queued_js . "});\n</script>\n";

			unset( $wc_queued_js );
		}
	}

	/**
	 * Include required core files used in admin and on the frontend.
	 */
	public function includes() {
		add_action( 'init', array( $this, 'maybe_activate_woocommerce_api_manager' ) );
		register_deactivation_hook( $this->plugin_file, array( $this, 'deactivate_woocommerce_api_manager' ) );

		require_once( 'includes/wc-am-autoloader.php' );
		require_once( 'includes/wc-am-core-functions.php' );

		// Load dependents of other plugins
		add_action( 'plugins_loaded', array( $this, 'load_dependents' ) );

		/**
		 * API requests handler.
		 *
		 * @since 2.0
		 */
		add_action( 'woocommerce_api_wc-am-api', array( $this, 'api_requests' ) );

		/**
		 * @deprecated @since 2.0
		 */
		add_action( 'woocommerce_api_upgrade-api', array( $this, 'api_requests' ) );
		add_action( 'woocommerce_api_am-software-api', array( $this, 'api_requests' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
		//add_action( 'wp_enqueue_scripts', array( $this, 'frontend_scripts' ) );

		/**
		 * Run after Storefront because it sets the styles to be empty.
		 */
		add_filter( 'woocommerce_enqueue_styles', array( $this, 'enqueue_styles' ), 100, 1 );

		if ( is_admin() ) {
			add_action( 'admin_footer', array( $this, 'wc_print_js' ), 25 );
		}

		add_action( 'in_plugin_update_message-' . plugin_basename( __FILE__ ), array( $this, 'in_plugin_update_message' ), 10, 2 );
	}

	/**
	 * Checks on each admin page load if WooCommerce API Manager is activated.
	 *
	 * @since 2.0
	 */
	public function maybe_activate_woocommerce_api_manager() {
		$is_active = get_option( 'woocommerce_api_manager_active', false );

		if ( $is_active == false ) {
			add_option( 'woocommerce_api_manager_active', true );
			flush_rewrite_rules();
		}

		do_action( 'wc_api_manager_activated' );
	}

	/**
	 * Called when the WooCommerce API Manager is deactivated.
	 *
	 * @since 2.0
	 */
	public function deactivate_woocommerce_api_manager() {
		delete_option( 'woocommerce_api_manager_active' );
		flush_rewrite_rules();

		do_action( 'wc_api_manager_deactivated' );
	}

	/**
	 * Load dependents of other plugins
	 *
	 * @since 1.4.6.1
	 */
	public function load_dependents() {
		// Set up localisation
		$this->load_plugin_textdomain();

		/**
		 * @since 2.0.16
		 */
		if ( class_exists( 'WC_Subscriptions' ) && self::is_wc_subscriptions_active_static() ) {
			if ( version_compare( WC_Subscriptions::$version, WC_AM_WC_SUBS_MIN_REQUIRED_VERSION, '<' ) ) {
				add_action( 'admin_notices', __CLASS__ . '::upgrade_wc_sub_am_warning' );

				return;
			}
		}

		$this->remove_my_account_email_download_links();

		require_once( 'includes/wc-api-manager-query.php' );
	}

	/**
	 * Removes all download links from email, My Account, and Order Details in the My Account dashboard.
	 * The API Downloads table contains download URLs for each product.
	 *
	 * @since 1.3.4
	 */
	public function remove_my_account_email_download_links() {
		// Remove API downlads from My Account downloads.
		add_filter( 'woocommerce_customer_get_downloadable_products', array( WC_AM_PRODUCT_DATA_STORE(), 'filter_get_downloadable_products' ) );
		// Remove all download links from emails and My Account view-order Order Details
		add_filter( 'woocommerce_get_item_downloads', array( WC_AM_PRODUCT_DATA_STORE(), 'filter_get_item_downloads' ), 10, 2 );
	}

	/**
	 * Load Localization files.
	 *
	 * Note: the first-loaded translation file overrides any following ones if the same translation is present
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain( 'woocommerce-api-manager', false, dirname( $this->plugins_basename() ) . '/i18n/languages/' );
	}

	/**
	 * Displays an inactive notice when WooCommerce is inactive.
	 *
	 * @since 1.0
	 */
	public static function woocommerce_inactive_notice() { ?>
        <div class="notice notice-info is-dismissible">
            <p><?php printf( __( 'The %sWooCommerce API Manager is inactive.%s The %sWooCommerce%s plugin must be active for the WooCommerce API Manager to work. Please activate WooCommerce on the %splugin page%s once it is installed.', 'woocommerce-api-manager' ), '<strong>', '</strong>', '<a href="http://wordpress.org/extend/plugins/woocommerce/" target="_blank">', '</a>', '<a href="' . esc_url( admin_url( 'plugins.php' ) ) . '">', '</a>' ); ?></p>
        </div>
		<?php
	}

	/**
	 * Required version of PHP.
	 *
	 * Types of notices:
	 * notice-error – error message displayed with a red border
	 * notice-warning – warning message displayed with a yellow border
	 * notice-success – success message displayed with a green border
	 * notice-info – info message displayed with a blue border
	 */
	public static function wam_php_requirement() { ?>
        <div class="error notice-warning">
            <!--            <p>-->
			<?php //printf( __( 'Warning: The next release of the %sWooCommerce API Manager%s may not run if PHP version 7 or above is not installed. Upgrade now if you have not already.', 'woocommerce-api-manager' ), '<strong>', '</strong>', WC_AM_REQUIRED_PHP_VERSION, PHP_VERSION ); ?><!--</p>-->
            <p><?php printf( __( 'The %sWooCommerce API Manager%s is inactive because it requires PHP version %s or greater, but your server has %s installed. Ask your web host to upgrade your version of PHP.', 'woocommerce-api-manager' ), '<strong>', '</strong>', WC_AM_REQUIRED_PHP_VERSION, PHP_VERSION ); ?></p>
        </div>
		<?php
	}

	/**
	 * Returns the required checks based on the request type
	 *
	 * @since 1.0
	 *
	 * @param string $type
	 *
	 * @return bool
	 */
	public function is_request( $type ) {
		switch ( $type ) {
			case 'admin' :
				return is_admin();
			case 'ajax' :
				return defined( 'DOING_AJAX' );
			case 'cron' :
				return defined( 'DOING_CRON' );
			case 'frontend' :
				return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' );
		}

		return false;
	}

	/**
	 * Displays an error message indicating the WooCommerce API Manager will remain disabled until WooCommerce
	 * has been upgraded to the required minimum version.
	 *
	 * @since 2.0
	 */
	public static function upgrade_wc_am_warning() { ?>
        <div class="notice notice-error">
            <p><?php printf( __( 'The %sWooCommerce API Manager%s requires WooCommerce version %s or greater, but your server has WooCommerce version %s installed. The WooCommerce API Manager will remain disabled until WooCommerce has been upgraded to version %s or greater.', 'woocommerce-api-manager' ), '<strong>', '</strong>', WC_AM_WC_MIN_REQUIRED_VERSION, get_option( 'woocommerce_version' ), WC_AM_WC_MIN_REQUIRED_VERSION ); ?></p>
        </div>
		<?php
	}

	/**
	 * Displays an error message indicating the WooCommerce API Manager will remain disabled until WooCommerce Subscriptions
	 * has been upgraded to the required minimum version.
	 *
	 * @since 2.0.13
	 */
	public static function upgrade_wc_sub_am_warning() {
		$wam_wc_subs_active_version = class_exists( 'WC_Subscriptions' ) ? WC_Subscriptions::$version : get_option( 'woocommerce_subscriptions_active_version' );

		?>
        <div class="notice notice-error">
            <p><?php printf( __( 'The %sWooCommerce API Manager%s requires WooCommerce Subscriptions version %s or greater, but your server has WooCommerce Subscriptions version %s installed. Please upgrade WooCommerce Subscriptions to version %s or greater.', 'woocommerce-api-manager' ), '<strong>', '</strong>', WC_AM_WC_SUBS_MIN_REQUIRED_VERSION, $wam_wc_subs_active_version, WC_AM_WC_SUBS_MIN_REQUIRED_VERSION ); ?></p>
        </div>
		<?php
	}

	/**
	 * Fires at the end of the update message container in each
	 * row of the plugins list table.
	 *
	 * The dynamic portion of the hook name, `$file`, refers to the path
	 * of the plugin's primary file relative to the plugins directory.
	 *
	 * @see   /wp-admin/includes/update.php
	 *
	 * @since 2.0
	 *
	 * @param array $plugin_data {
	 *                           An array of plugin metadata.
	 *                           Information about the plugin.
	 *
	 * @type string $name        The human-readable name of the plugin.
	 * @type string $plugin_uri  Plugin URI.
	 * @type string $version     Plugin version.
	 * @type string $description Plugin description.
	 * @type string $author      Plugin author.
	 * @type string $author_uri  Plugin author URI.
	 * @type string $text_domain Plugin text domain.
	 * @type string $domain_path Relative path to the plugin's .mo file(s).
	 * @type bool   $network     Whether the plugin can only be activated network wide.
	 * @type string $title       The human-readable title of the plugin.
	 * @type string $author_name Plugin author's name.
	 * @type bool   $update      Whether there's an available update. Default null.
	 * }
	 *
	 * @param array $response    {
	 *                           An array of metadata about the available plugin update.
	 *                           Response from the server about the new version.
	 *
	 * @type int    $id          Plugin ID.
	 * @type string $slug        Plugin slug.
	 * @type string $new_version New plugin version.
	 * @type string $url         Plugin URL.
	 * @type string $package     Plugin update package URL.
	 * }
	 *
	 */
	public function in_plugin_update_message( $plugin_data, $response ) {

		// Bail if the update notice is not relevant, i.e. new version is not yet 2.0, or we're already on 2.0.
		if ( version_compare( '2.0.0', $plugin_data[ 'new_version' ], '>' ) || version_compare( '2.0.0', $plugin_data[ 'Version' ], '<=' ) ) {
			return;
		}

		$update_notice = '<div class="wc_plugin_upgrade_notice">';
		// translators: placeholders are opening and closing tags. Leads to docs on version 2
		$update_notice .= sprintf( __( 'Warning! Version 2.0 is a major update to the WooCommerce API Manager extension. Before updating, please create a backup, update all WooCommerce extensions, and test all plugins and custom code with version 2.0 on a staging site. %sLearn more about the changes in version 2.0 &raquo;%s', 'woocommerce-api-manager' ), '<a href="https://docs.woocommerce.com/document/woocommerce-api-manager/">', '</a>' );
		$update_notice .= '</div> ';

		echo wp_kses_post( $update_notice );
	}

	/**
	 * Checks if a plugin is activated
	 *
	 * @since 1.1
	 *
	 * @param string $slug
	 *
	 * @return bool
	 */
	public function is_plugin_active( $slug ) {
		$active_plugins = (array) get_option( 'active_plugins', array() );

		if ( is_multisite() ) {
			$active_plugins = array_merge( $active_plugins, get_site_option( 'active_sitewide_plugins', array() ) );
		}

		return in_array( $slug, $active_plugins ) || array_key_exists( $slug, $active_plugins );
	}

	/**
	 * Return true if pre argument version is older than the current version.
	 *
	 * @since 1.4.4
	 *
	 * @param string $version
	 *
	 * @return bool
	 */
	public function is_woocommerce_pre( $version ) {
		return ! empty( $this->get_wc_version() ) && version_compare( $this->get_wc_version(), $version, '<' ) ? true : false;
	}

	/**
	 * Is WooCommerce Subscriptions plugin active?
	 *
	 * @since 2.0
	 *
	 * @return bool
	 */
	public function is_wc_subscriptions_active() {
		/**
		 * A plugin can be removed without using the Plugins screen, so it remains listed as active, but the root plugin class will not exist.
		 */
		return $this->is_plugin_active( 'woocommerce-subscriptions/woocommerce-subscriptions.php' ) ? true : false;
	}

	/**
	 * Is WooCommerce Subscriptions plugin active?
	 *
	 * @since  2.0.15
	 * @access static
	 *
	 * @return bool
	 */
	public static function is_wc_subscriptions_active_static() {
		$slug           = 'woocommerce-subscriptions/woocommerce-subscriptions.php';
		$active_plugins = (array) get_option( 'active_plugins', array() );

		if ( is_multisite() ) {
			$active_plugins = array_merge( $active_plugins, get_site_option( 'active_sitewide_plugins', array() ) );
		}

		return in_array( $slug, $active_plugins ) || array_key_exists( $slug, $active_plugins );
	}

} // End class

/**
 * Returns the main instance of WooCommerce_API_Manager to prevent the need to use globals.
 *
 * @since  1.3
 * @return WooCommerce_API_Manager
 */
function WCAM() {
	return WooCommerce_API_Manager::instance();
}

// Initialize the class instance only once
WCAM();