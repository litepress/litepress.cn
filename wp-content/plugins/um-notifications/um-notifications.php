<?php
/*
Plugin Name: Ultimate Member - Real-time Notifications
Plugin URI: https://ultimatemember.com/extensions/real-time-notifications/
Description: Adds real-time activity notifications to community users.
Version: 2.2.0
Author: Ultimate Member
Author URI: https://ultimatemember.com/
Text Domain: um-notifications
Domain Path: /languages
UM version: 2.1.0
*/

if ( ! defined( 'ABSPATH' ) ) exit;

require_once( ABSPATH.'wp-admin/includes/plugin.php' );

$plugin_data = get_plugin_data( __FILE__ );

define( 'um_notifications_url', plugin_dir_url( __FILE__ ) );
define( 'um_notifications_path', plugin_dir_path( __FILE__ ));
define( 'um_notifications_plugin', plugin_basename( __FILE__ ) );
define( 'um_notifications_extension', $plugin_data['Name'] );
define( 'um_notifications_version', $plugin_data['Version'] );
define( 'um_notifications_textdomain', 'um-notifications' );

define( 'um_notifications_requires', '2.1.0' );

function um_notifications_plugins_loaded() {
	$locale = ( get_locale() != '' ) ? get_locale() : 'en_US';
	load_textdomain( um_notifications_textdomain, WP_LANG_DIR . '/plugins/' . um_notifications_textdomain . '-' . $locale . '.mo' );
	load_plugin_textdomain( um_notifications_textdomain, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}
add_action( 'plugins_loaded', 'um_notifications_plugins_loaded', 0 );

add_action( 'plugins_loaded', 'um_notifications_check_dependencies', -20 );

if ( ! function_exists( 'um_notifications_check_dependencies' ) ) {
	function um_notifications_check_dependencies() {
		if ( ! defined( 'um_path' ) || ! file_exists( um_path  . 'includes/class-dependencies.php' ) ) {
			//UM is not installed
			function um_notifications_dependencies() {
				echo '<div class="error"><p>' . sprintf( __( 'The <strong>%s</strong> extension requires the Ultimate Member plugin to be activated to work properly. You can download it <a href="https://wordpress.org/plugins/ultimate-member">here</a>', 'um-notifications' ), um_notifications_extension ) . '</p></div>';
			}

			add_action( 'admin_notices', 'um_notifications_dependencies' );
		} else {

			if ( ! function_exists( 'UM' ) ) {
				require_once um_path . 'includes/class-dependencies.php';
				$is_um_active = um\is_um_active();
			} else {
				$is_um_active = UM()->dependencies()->ultimatemember_active_check();
			}

			if ( ! $is_um_active ) {
				//UM is not active
				function um_notifications_dependencies() {
					echo '<div class="error"><p>' . sprintf( __( 'The <strong>%s</strong> extension requires the Ultimate Member plugin to be activated to work properly. You can download it <a href="https://wordpress.org/plugins/ultimate-member">here</a>', 'um-notifications' ), um_notifications_extension ) . '</p></div>';
				}

				add_action( 'admin_notices', 'um_notifications_dependencies' );

			} elseif ( true !== UM()->dependencies()->compare_versions( um_notifications_requires, um_notifications_version, 'notifications', um_notifications_extension ) ) {
				//UM old version is active
				function um_notifications_dependencies() {
					echo '<div class="error"><p>' . UM()->dependencies()->compare_versions( um_notifications_requires, um_notifications_version, 'notifications', um_notifications_extension ) . '</p></div>';
				}

				add_action( 'admin_notices', 'um_notifications_dependencies' );

			} else {
				require_once um_notifications_path . 'includes/core/um-notifications-init.php';
			}
		}
	}
}

if ( ! function_exists( 'um_notifications_activation_hook' ) ) {
	function um_notifications_activation_hook() {
		//run setup
		if ( ! class_exists( 'um_ext\um_notifications\core\Notifications_Setup' ) )
			require_once um_notifications_path . 'includes/core/class-notifications-setup.php';

		$notifications_setup = new um_ext\um_notifications\core\Notifications_Setup();
		$notifications_setup->run_setup();

		//first install
		$version = get_option( 'um_notifications_version' );
		if ( ! $version )
			update_option( 'um_notifications_last_version_upgrade', um_notifications_version );

		if ( $version != um_notifications_version )
			update_option( 'um_notifications_version', um_notifications_version );
	}
}
register_activation_hook( um_notifications_plugin, 'um_notifications_activation_hook' );