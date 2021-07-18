<?php
/*
Plugin Name: Ultimate Member - bbPress
Plugin URI: http://ultimatemember.com/extensions/bbpress
Description: Integrates Ultimate Member with bbPress beautifully.
Version: 2.0.9
Author: Ultimate Member
Author URI: http://ultimatemember.com/
Text Domain: um-bbpress
Domain Path: /languages
UM version: 2.1.0
*/

if ( ! defined( 'ABSPATH' ) ) exit;

require_once( ABSPATH.'wp-admin/includes/plugin.php' );

$plugin_data = get_plugin_data( __FILE__ );

define( 'um_bbpress_url', plugin_dir_url( __FILE__ ) );
define( 'um_bbpress_path', plugin_dir_path( __FILE__ ) );
define( 'um_bbpress_plugin', plugin_basename( __FILE__ ) );
define( 'um_bbpress_extension', $plugin_data['Name'] );
define( 'um_bbpress_version', $plugin_data['Version'] );
define( 'um_bbpress_textdomain', 'um-bbpress' );

define( 'um_bbpress_requires', '2.1.0' );

function um_bbpress_plugins_loaded() {
	$locale = ( get_locale() != '' ) ? get_locale() : 'en_US';
	load_textdomain( um_bbpress_textdomain, WP_LANG_DIR . '/plugins/' .um_bbpress_textdomain . '-' . $locale . '.mo');
	load_plugin_textdomain( um_bbpress_textdomain, false, dirname( plugin_basename(  __FILE__ ) ) . '/languages/' );

}
add_action( 'init', 'um_bbpress_plugins_loaded', 0 );


add_action( 'plugins_loaded', 'um_bbpress_check_dependencies', -20 );

if ( ! function_exists( 'um_bbpress_check_dependencies' ) ) {
	function um_bbpress_check_dependencies() {
		if ( ! defined( 'um_path' ) || ! file_exists( um_path  . 'includes/class-dependencies.php' ) ) {
			//UM is not installed
			function um_bbpress_dependencies() {
				echo '<div class="error"><p>' . sprintf( __( 'The <strong>%s</strong> extension requires the Ultimate Member plugin to be activated to work properly. You can download it <a href="https://wordpress.org/plugins/ultimate-member">here</a>', 'um-bbpress' ), um_bbpress_extension ) . '</p></div>';
			}

			add_action( 'admin_notices', 'um_bbpress_dependencies' );
		} else {

			if ( ! function_exists( 'UM' ) ) {
				require_once um_path . 'includes/class-dependencies.php';
				$is_um_active = um\is_um_active();
			} else {
				$is_um_active = UM()->dependencies()->ultimatemember_active_check();
			}

			if ( ! $is_um_active ) {
				//UM is not active
				function um_bbpress_dependencies() {
					echo '<div class="error"><p>' . sprintf( __( 'The <strong>%s</strong> extension requires the Ultimate Member plugin to be activated to work properly. You can download it <a href="https://wordpress.org/plugins/ultimate-member">here</a>', 'um-bbpress' ), um_bbpress_extension ) . '</p></div>';
				}

				add_action( 'admin_notices', 'um_bbpress_dependencies' );

			} elseif ( ! UM()->dependencies()->bbpress_active_check() ) {
				//UM is not active
				function um_bbpress_dependencies() {
					echo '<div class="error"><p>' . sprintf( __( 'Sorry. You must activate the <strong>bbPress</strong> plugin to use the %s.', 'um-bbpress' ), um_bbpress_extension ) . '</p></div>';
				}

				add_action( 'admin_notices', 'um_bbpress_dependencies' );
			} elseif ( true !== UM()->dependencies()->compare_versions( um_bbpress_requires, um_bbpress_version, 'bbpress', um_bbpress_extension ) ) {
				//UM old version is active
				function um_bbpress_dependencies() {
					echo '<div class="error"><p>' . UM()->dependencies()->compare_versions( um_bbpress_requires, um_bbpress_version, 'bbpress', um_bbpress_extension ) . '</p></div>';
				}

				add_action( 'admin_notices', 'um_bbpress_dependencies' );

			} else {

				require_once um_bbpress_path . 'includes/core/um-bbpress-init.php';
			}
		}
	}
}


if ( ! function_exists( 'um_bbpress_activation_hook' ) ) {
	function um_bbpress_activation_hook() {
		//first install
		$version = get_option( 'um_bbpress_version' );
		if ( ! $version ) {
			update_option( 'um_bbpress_last_version_upgrade', um_bbpress_version );
		}

		if ( $version != um_bbpress_version ) {
			update_option( 'um_bbpress_version', um_bbpress_version );
		}

		//run setup
		if ( ! class_exists( 'um_ext\um_bbpress\core\bbPress_Setup' ) ) {
			require_once um_bbpress_path . 'includes/core/class-bbpress-setup.php';
		}

		$bbpress_setup = new um_ext\um_bbpress\core\bbPress_Setup();
		$bbpress_setup->run_setup();

		//only first install
		if ( ! $version ) {
			$bbpress_setup->set_restriction_settings();
		}
	}
}
register_activation_hook( um_bbpress_plugin, 'um_bbpress_activation_hook' );