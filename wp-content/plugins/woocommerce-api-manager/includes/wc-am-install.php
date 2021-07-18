<?php

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * WooCommerce API Manager Install Class
 *
 * @since       1.3.4
 *
 * @author      Todd Lahman LLC
 * @copyright   Copyright (c) Todd Lahman LLC
 * @package     WooCommerce API Manager/Install
 */
class WC_AM_Install {

	/**
	 * DB updates and callbacks that need to be run per version.
	 *
	 * @var array
	 */
	private $db_updates = array(
		'2.0.0' => array(
			'wc_am_update_200_create_master_api_key',
			'wc_am_update_200_data_migrate_orders',
			'wc_am_update_200_data_migrate_activations',
			'wc_am_update_200_data_add_product_id_and_add_api_orders_processed_flag_to_api_products',
			'wc_am_update_200_data_merge_software_title',
			'wc_am_update_200_db_version',
		),
		'2.0.1' => array(
			'wc_am_update_201_data_migrate_access_granted_to_order_created_time',
			'wc_am_update_201_db_version',
		),
		'2.0.5' => array(
			'wc_am_update_205_check_if_api_resources_table_is_empty',
			'wc_am_update_205_db_version',
		),
		'2.2.6' => array(
			'wc_am_update_2_2_6_db_version',
		),
	);

	/**
	 * Background update class.
	 *
	 * @var object
	 */
	private $background_updater;

	/**
	 * @var null The single instance of the class
	 */
	private static $_instance = null;

	/**
	 * @static
	 * @return \WC_AM_Install
	 */
	public static function instance() {

		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	private function __construct() {
		// Prep for new updater.
		$wc_am_version = get_option( 'wc_am_version' );

		if ( $wc_am_version === false || version_compare( $wc_am_version, '2.0.0', '<' ) ) {
			update_option( 'wc_am_version', '1.0.0' );
		}

		// Prep for new updater.
		$wc_am_db_version = get_option( 'wc_am_db_version' );

		if ( $wc_am_db_version === false || version_compare( $wc_am_db_version, '2.0.0', '<' ) ) {
			update_option( 'wc_am_db_version', '1.0.0' );
		}

		// Installation
		register_activation_hook( WCAM()->get_plugin_file(), array( $this, 'install' ) );
		// Add the Settings | Documentation | Support links on the Plugins administration screen
		add_filter( 'plugin_action_links_' . WCAM()->get_file(), array( $this, 'action_links' ) );
		add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 2 );
		add_action( 'init', array( $this, 'init_background_updater' ), 5 );
		add_action( 'init', array( $this, 'check_version' ), 5 );
		add_action( 'admin_init', array( $this, 'install_actions' ) );

		if ( WCAM()->get_wc_version() >= '3.2' ) {
			add_action( 'wc_api_manager_installed', 'wc_delete_expired_transients' );
		}
	}

	/**
	 * Include Settings | Documentation | Support links links on the Plugins administration screen
	 * Added string keys to prevent duplicates
	 *
	 * @param string $links
	 *
	 * @return array
	 */
	public function action_links( $links ) {
		return array_merge( array(
			                    'Settings' => '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=api_manager' ) . '">' . esc_html__( 'Settings', 'woocommerce-api-manager' ) . '</a>',
		                    ), $links );
	}

	/**
	 * Show row meta on the plugin screen.
	 *
	 * @param mixed $links Plugin Row Meta
	 * @param mixed $file  Plugin Base file
	 *
	 * @return    array
	 */
	public static function plugin_row_meta( $links, $file ) {
		if ( WCAM()->get_file() == $file ) {
			$row_meta = array(
				'Docs'    => '<a href="' . esc_url( apply_filters( 'wc_api_manager_docs_url', 'http://docs.woocommerce.com/document/woocommerce-api-manager/', 'woocommerce-api-manager' ) ) . '">' . esc_html__( 'Docs', 'woocommerce-api-manager' ) . '</a>',
				'Support' => '<a href="' . esc_url( apply_filters( 'wc_api_manager_support_url', 'https://woocommerce.com/my-account/create-a-ticket?broken=primary&select=260110' ) ) . '">' . esc_html__( 'Support', 'woocommerce-api-manager' ) . '</a>',
			);

			return array_merge( $links, $row_meta );
		}

		return (array) $links;
	}

	/**
	 * Init background updates
	 */
	public function init_background_updater() {
		require_once( dirname( __FILE__ ) . '/wcam-background-updater.php' );

		$this->background_updater = new WCAM_Background_Updater();
	}

	/**
	 * check_version function.
	 */
	public function check_version() {
		if ( ! defined( 'IFRAME_REQUEST' ) && version_compare( get_option( 'wc_am_version' ), WC_AM_VERSION, '<' ) ) {
			$this->install();

			do_action( 'wc_api_manager_updated' );
		}
	}

	/**
	 * Install actions when a update button is clicked within the admin area.
	 * This function is hooked into admin_init to affect admin only.
	 *
	 * @since 2.0
	 */
	public function install_actions() {
		if ( ! empty( $_GET[ 'do_update_woocommerce_api_manager' ] ) ) {
			$this->update();
			WC_AM_ADMIN_NOTICES()->add_notice( 'update' );
		}
		if ( ! empty( $_GET[ 'force_update_woocommerce_api_manager' ] ) ) {
			do_action( 'wp_wc_am_updater_cron' );
			wp_safe_redirect( admin_url( 'admin.php?page=wc-settings&tab=api_manager' ) );
			exit;
		}
	}

	/**
	 * Handles tasks when plugin is activated
	 */
	public function install() {
		// Check if we are not already running this routine.
		if ( get_transient( 'wc_am_installing' ) !== 'yes' ) {
			// Set the transient now before anything starts to run.
			set_transient( 'wc_am_installing', 'yes', MINUTE_IN_SECONDS * 10 );
			WCAM()->maybe_define_constant( 'WC_AM_INSTALLING', true );
			$this->remove_admin_notices();
			$this->create_options();
			$this->create_tables();
			// $this->create_master_api_key();
			$this->maybe_enable_setup_wizard();
			$this->update_wc_am_version();
			$this->maybe_update_db_version();
			WC_AM_SMART_CACHE()->queue_delete_transient( 'wc_am_installing' );

			flush_rewrite_rules();
			do_action( 'wc_api_manager_flush_rewrite_rules' );
			do_action( 'wc_api_manager_installed' );
		}
	}

	/**
	 * Reset any notices added to admin.
	 *
	 * @since 2.0
	 */
	private function remove_admin_notices() {
		include_once( dirname( __FILE__ ) . '/admin/wc-am-admin-notices.php' );
		WC_AM_ADMIN_NOTICES()->remove_all_notices();
	}

	/**
	 * Is this a brand new WC install?
	 *
	 * @since 2.0
	 * @return boolean
	 */
	private function is_new_install() {
		return is_null( get_option( 'wc_am_version', null ) ) && is_null( get_option( 'wc_am_db_version', null ) );
	}

	/**
	 * Is a DB update needed?
	 *
	 * @since 2.0
	 * @return boolean
	 */
	private function needs_db_update() {
		$current_db_version = get_option( 'wc_am_db_version', null );

		return ! is_null( $current_db_version ) && version_compare( $current_db_version, max( array_keys( $this->db_updates ) ), '<' );
	}

	/**
	 * See if we need the wizard or not.
	 *
	 * @since 2.0
	 */
	private function maybe_enable_setup_wizard() {
		if ( apply_filters( 'wc_api_manager_enable_setup_wizard', $this->is_new_install() ) ) {
			WC_AM_ADMIN_NOTICES()->add_notice( 'install' );
			set_transient( '_wc_am_activation_redirect', 1, 30 );
		}
	}

	/**
	 * See if we need to show or run database updates during install.
	 *
	 * @since 2.0
	 */
	private function maybe_update_db_version() {
		if ( $this->needs_db_update() ) {
			if ( apply_filters( 'wc_api_manager_enable_auto_update_db', false ) ) {
				$this->init_background_updater();
				$this->update();
			} else {
				WC_AM_ADMIN_NOTICES()->add_notice( 'update' );
			}
		} else {
			$this->update_db_version();
		}
	}

	/**
	 * Push all needed DB updates to the queue for processing.
	 */
	private function update() {
		$current_db_version = get_option( 'wc_am_db_version' );
		$logger             = wc_get_logger();
		$update_queued      = false;

		foreach ( $this->db_updates as $version => $update_callbacks ) {
			if ( version_compare( $current_db_version, $version, '<' ) ) {
				foreach ( $update_callbacks as $update_callback ) {
					$logger->info( sprintf( 'Queuing %s - %s', $version, $update_callback ), array( 'source' => 'wc_am_db_updates' ) );
					$this->background_updater->push_to_queue( $update_callback );
					$update_queued = true;
				}
			}
		}

		if ( $update_queued ) {
			$this->background_updater->save()->dispatch();
		}
	}

	/**
	 * Update WC API Manager version to current.
	 */
	private function update_wc_am_version() {
		delete_option( 'wc_am_version' );
		add_option( 'wc_am_version', WC_AM_VERSION );
	}

	/**
	 * Update DB version to current.
	 *
	 * @param string $version
	 */
	public function update_db_version( $version = null ) {
		delete_option( 'wc_am_db_version' );
		add_option( 'wc_am_db_version', is_null( $version ) ? WC_AM_VERSION : $version );
	}

	/**
	 * Create Table schema.
	 *
	 * https://github.com/woocommerce/woocommerce/wiki/Database-Description/
	 *
	 * Indexes have a maximum size of 767 bytes. Historically, we haven't need to be concerned about that.
	 * As of WordPress 4.2, however, we moved to utf8mb4, which uses 4 bytes per character. This means that an index which
	 * used to have room for floor(767/3) = 255 characters, now only has room for floor(767/4) = 191 characters.
	 *
	 * Changing indexes may cause duplicate index notices in logs due to https://core.trac.wordpress.org/ticket/34870 but dropping
	 * indexes first causes too much load on some servers/larger DB.
	 */
	private function create_tables() {
		global $wpdb;

		// Drop and rebuild the wc_am_secure_hash table, so it can be upgraded for utf8mb4 as required in WP 4.2.
		if ( version_compare( get_option( 'wc_am_version' ), '1.6', '<' ) ) {
			$wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->prefix . "woocommerce_api_manager_secure_hash" );
		}

		$wpdb->hide_errors();

		$collate = '';

		if ( $wpdb->has_cap( 'collation' ) ) {
			$collate = $wpdb->get_charset_collate();
		}

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		/**
		 * Provides secure hash for authenticating secure URLs for downloads.
		 */
		if ( ! $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->prefix}wc_am_secure_hash';" ) ) {
			$hash_table = "
				CREATE TABLE {$wpdb->prefix}wc_am_secure_hash (
					hash_id BIGINT UNSIGNED NOT NULL auto_increment,
					hash_user_id BIGINT UNSIGNED NOT NULL,
					hash_name VARCHAR(12) NOT NULL,
					hash_value VARCHAR(190) NOT NULL,
					hash_time BIGINT UNSIGNED NOT NULL,
					PRIMARY KEY (hash_id),
					KEY hash_name (hash_name)
				) $collate;
			";
			dbDelta( $hash_table );
		}

		/**
		 * API resource table. Contains all data related to purchased resources.
		 *
		 * @since 2.0
		 */
		if ( ! $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->prefix}wc_am_api_resource';" ) ) {
			$api_resource_table = "
				CREATE TABLE {$wpdb->prefix}wc_am_api_resource (
					api_resource_id BIGINT UNSIGNED NOT NULL auto_increment,
					activation_ids longtext NOT NULL,
					activations_total BIGINT UNSIGNED NOT NULL DEFAULT 0,
					activations_purchased BIGINT UNSIGNED NOT NULL DEFAULT 0,
					activations_purchased_total BIGINT UNSIGNED NOT NULL DEFAULT 0,
					active tinyint(1) NOT NULL DEFAULT '1',
					access_expires BIGINT UNSIGNED NOT NULL DEFAULT 0,
					access_granted BIGINT UNSIGNED NOT NULL,
					associated_api_key_ids longtext NOT NULL,
					collaborators longtext NOT NULL,
					download_requests BIGINT UNSIGNED NOT NULL DEFAULT 0,
					item_qty BIGINT UNSIGNED NOT NULL,
					master_api_key VARCHAR(60) NOT NULL,
					order_id BIGINT UNSIGNED NOT NULL,
					order_item_id BIGINT UNSIGNED NOT NULL,
					order_key VARCHAR(190) NOT NULL,
					parent_id BIGINT UNSIGNED NOT NULL,
					product_id BIGINT UNSIGNED NOT NULL,
					product_order_api_key VARCHAR(190) NOT NULL,
					product_title VARCHAR(190) NOT NULL,
					refund_qty BIGINT UNSIGNED NOT NULL,
					sub_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
					sub_item_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
					sub_previous_order_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
					sub_order_key VARCHAR(190) NOT NULL DEFAULT '',
					sub_parent_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
					user_id BIGINT UNSIGNED NOT NULL,
					variation_id BIGINT UNSIGNED NOT NULL,
					PRIMARY KEY (api_resource_id),
					KEY master_api_key (master_api_key),
					KEY order_id (order_id),
					KEY order_key (order_key),
					KEY product_id (product_id),
					UNIQUE KEY product_order_api_key (product_order_api_key),
					KEY user_id (user_id)
				) $collate;
			";
			dbDelta( $api_resource_table );
		}

		/**
		 * API activation table. Contains data related to client resource activation, as software, or as a service.
		 *
		 * @since 2.0
		 */
		if ( ! $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->prefix}wc_am_api_activation';" ) ) {
			$activation_table = "
				CREATE TABLE {$wpdb->prefix}wc_am_api_activation (
					activation_id BIGINT UNSIGNED NOT NULL auto_increment,
					activation_time BIGINT UNSIGNED NOT NULL,
					api_key VARCHAR(190) NOT NULL,
					api_resource_id BIGINT UNSIGNED NOT NULL,
					assigned_product_id BIGINT UNSIGNED NOT NULL,
					associated_api_key_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
					instance VARCHAR(190) NOT NULL,
					ip_address VARCHAR(190) NOT NULL DEFAULT '',
					master_api_key VARCHAR(60) NOT NULL,
					object VARCHAR(190) NOT NULL DEFAULT '',
					order_id BIGINT UNSIGNED NOT NULL,
					order_item_id BIGINT UNSIGNED NOT NULL,
					product_id VARCHAR(190) NOT NULL DEFAULT '',
					product_order_api_key VARCHAR(190) NOT NULL,
					sub_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
					sub_item_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
					sub_parent_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
					version VARCHAR(20) NOT NULL DEFAULT '',
					update_requests BIGINT UNSIGNED NOT NULL DEFAULT 0,
					user_id BIGINT UNSIGNED NOT NULL,
					PRIMARY KEY (activation_id),
					KEY api_key (api_key),
					KEY instance (instance),
					KEY master_api_key (master_api_key),
					KEY user_id (user_id)
				) $collate;
			";
			dbDelta( $activation_table );
		}

		/**
		 * Associated API Key table. Allows custom API Keys, or other types of keys such as those used to activate software such as Windows,
		 * or services. Keys can be stored in this table, then used in place of the Product Order API Key when a resource/product is purchased.
		 * The custom key can then be removed from this table, or left in place, since it will remain associated with the purchased API
		 * resource, as long as the order exists.
		 *
		 * @since 2.0
		 */
		if ( ! $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->prefix}wc_am_associated_api_key';" ) ) {
			$activation_table = "
				CREATE TABLE {$wpdb->prefix}wc_am_associated_api_key (
					associated_api_key_id BIGINT UNSIGNED NOT NULL auto_increment,
					activation_ids longtext NOT NULL,
					associated_api_key VARCHAR(190) NOT NULL,
					api_resource_id BIGINT UNSIGNED NOT NULL,
					product_id BIGINT UNSIGNED NOT NULL,
					PRIMARY KEY (associated_api_key_id),
					UNIQUE KEY associated_api_key (associated_api_key),
					KEY api_resource_id (api_resource_id)
				) $collate;
			";
			dbDelta( $activation_table );
		}
		/**
		 * TODO
		 * Collaborator table. Allows collaborators to be granted permission to access API resources. When a collaborator's email, name, etc.,
		 * are added by the resource owner, a customer account is created, and an email sent to the collaborator to set their password, after
		 * a secure password has already been created for the new account.
		 */
	}

	private function create_options() {
		/**
		 * @since 1.5
		 */
		if ( get_option( 'woocommerce_api_manager_description' ) === false ) {
			update_option( 'woocommerce_api_manager_description', 'no' );
		}

		if ( get_option( 'woocommerce_api_manager_installation' ) === false ) {
			update_option( 'woocommerce_api_manager_installation', 'no' );
		}

		if ( get_option( 'woocommerce_api_manager_faq' ) === false ) {
			update_option( 'woocommerce_api_manager_faq', 'no' );
		}

		if ( get_option( 'woocommerce_api_manager_screenshots' ) === false ) {
			update_option( 'woocommerce_api_manager_screenshots', 'no' );
		}

		if ( get_option( 'woocommerce_api_manager_other_notes' ) === false ) {
			update_option( 'woocommerce_api_manager_other_notes', 'no' );
		}

		/**
		 * @since 2.0
		 */
		if ( get_option( 'woocommerce_api_manager_hide_product_order_api_keys' ) === false ) {
			update_option( 'woocommerce_api_manager_hide_product_order_api_keys', 'yes' );
		}

		if ( get_option( 'woocommerce_api_manager_api_response_data' ) === false ) {
			update_option( 'woocommerce_api_manager_api_response_data', 'no' );
		}

		if ( get_option( 'woocommerce_api_manager_api_debug_log' ) === false ) {
			update_option( 'woocommerce_api_manager_api_debug_log', 'no' );
		}

		if ( get_option( 'woocommerce_api_manager_api_error_log' ) === false ) {
			update_option( 'woocommerce_api_manager_api_error_log', 'no' );
		}

		if ( get_option( 'woocommerce_api_manager_api_response_log' ) === false ) {
			update_option( 'woocommerce_api_manager_api_response_log', 'no' );
		}

		/**
		 * Bypass trash and force deletion of the Lost API Key page.
		 *
		 * @since 2.0
		 */
		$lost_api_key_id = get_page_by_title( 'Lost API Key' );

		if ( ! is_null( $lost_api_key_id ) ) {
			wp_delete_post( $lost_api_key_id->ID, true );
			delete_option( 'woocommerce_lost_license_page_id' );
		} elseif ( get_option( 'woocommerce_lost_license_page_id' ) ) {
			$lost_api_key_id = get_option( 'woocommerce_lost_license_page_id' );

			wp_delete_post( $lost_api_key_id, true );
			delete_option( 'woocommerce_lost_license_page_id' );
		}

		/**
		 * @since 2.0
		 */
		if ( get_option( 'woocommerce_api_manager_user_api_data_built' ) !== false ) {
			delete_option( 'woocommerce_api_manager_user_api_data_built' );
		}

		if ( get_option( 'woocommerce_api_manager_activation_order_note' ) !== false ) {
			delete_option( 'woocommerce_api_manager_activation_order_note' );
		}

		/**
		 * @since 2.1
		 */
		if ( get_option( 'woocommerce_api_manager_aws_s3_region' ) === false ) {
			update_option( 'woocommerce_api_manager_aws_s3_region', 'us-east-1' );
		}

		// Convert minutes value to days for new AWS Signature Version 4 time format, or set value for new installs.
		if ( get_option( 'woocommerce_api_manager_url_expire' ) === false || get_option( 'woocommerce_api_manager_url_expire' ) > 7 ) {
			update_option( 'woocommerce_api_manager_url_expire', 7 );
		}

		/**
		 * @since 2.2.0
		 */
		if ( get_option( 'woocommerce_api_manager_db_cache' ) !== false ) {
			delete_option( 'woocommerce_api_manager_db_cache' );
		}

		if ( get_option( 'woocommerce_api_manager_db_cache_expire' ) !== false ) {
			delete_option( 'woocommerce_api_manager_db_cache_expire' );
		}
	}

	/**
	 * @since 2.0
	 */
	private function create_master_api_key() {
		global $wpdb;

		if ( get_option( 'wc_am_master_api_key_created' ) === false ) {
			$id_list = $wpdb->get_col( "
				SELECT ID
				FROM $wpdb->users
			" );

			if ( ! empty( $id_list ) ) {
				foreach ( $id_list as $key => $user_id ) {
					$user_master_api_key = WC_AM_USER()->get_master_api_key( $user_id );

					if ( empty( $user_master_api_key ) ) {
						WC_AM_USER()->set_registration_master_key_and_status( $user_id );
					}
				}

				update_option( 'wc_am_master_api_key_created', 'yes' );
			}
		}
	}

}