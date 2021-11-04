<?php
/*
Plugin Name: Ultimate Member - reCAPTCHA
Plugin URI: https://ultimatemember.com/extensions/google-recaptcha/
Description: Protect your website from spam and integrate Google reCAPTCHA into your Ultimate Member forms
Version: 2.2.0
Author: Ultimate Member
Author URI: http://ultimatemember.com/
Text Domain: um-recaptcha
Domain Path: /languages
UM version: 2.1.0
*/

if ( ! defined( 'ABSPATH' ) ) exit;

require_once( ABSPATH.'wp-admin/includes/plugin.php' );

$plugin_data = get_plugin_data( __FILE__ );

define( 'um_recaptcha_url', plugin_dir_url( __FILE__ ) );
define( 'um_recaptcha_path', plugin_dir_path( __FILE__ ) );
define( 'um_recaptcha_plugin', plugin_basename( __FILE__ ) );
define( 'um_recaptcha_extension', $plugin_data['Name'] );
define( 'um_recaptcha_version', $plugin_data['Version'] );
define( 'um_recaptcha_textdomain', 'um-recaptcha' );

define( 'um_recaptcha_requires', '2.1.0' );

function um_recaptcha_plugins_loaded() {
	$locale = ( get_locale() != '' ) ? get_locale() : 'en_US';
	load_textdomain( um_recaptcha_textdomain, WP_LANG_DIR . '/plugins/' . um_recaptcha_textdomain . '-' . $locale . '.mo' );
	load_plugin_textdomain( um_recaptcha_textdomain, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}
add_action( 'plugins_loaded', 'um_recaptcha_plugins_loaded', 0 );

add_action( 'plugins_loaded', 'um_recaptcha_check_dependencies', -20 );

if ( ! function_exists( 'um_recaptcha_check_dependencies' ) ) {
	function um_recaptcha_check_dependencies() {
		if ( ! defined( 'um_path' ) || ! file_exists( um_path  . 'includes/class-dependencies.php' ) ) {
			//UM is not installed
			function um_recaptcha_dependencies() {
				echo '<div class="error"><p>' . sprintf( __( 'The <strong>%s</strong> extension requires the Ultimate Member plugin to be activated to work properly. You can download it <a href="https://wordpress.org/plugins/ultimate-member">here</a>', 'um-recaptcha' ), um_recaptcha_extension ) . '</p></div>';
			}

			add_action( 'admin_notices', 'um_recaptcha_dependencies' );
		} else {

			if ( ! function_exists( 'UM' ) ) {
				require_once um_path . 'includes/class-dependencies.php';
				$is_um_active = um\is_um_active();
			} else {
				$is_um_active = UM()->dependencies()->ultimatemember_active_check();
			}

			if ( ! $is_um_active ) {
				//UM is not active
				function um_recaptcha_dependencies() {
					echo '<div class="error"><p>' . sprintf( __( 'The <strong>%s</strong> extension requires the Ultimate Member plugin to be activated to work properly. You can download it <a href="https://wordpress.org/plugins/ultimate-member">here</a>', 'um-recaptcha' ), um_recaptcha_extension ) . '</p></div>';
				}

				add_action( 'admin_notices', 'um_recaptcha_dependencies' );

			} elseif ( true !== UM()->dependencies()->compare_versions( um_recaptcha_requires, um_recaptcha_version, 'recaptcha', um_recaptcha_extension ) ) {
				//UM old version is active
				function um_recaptcha_dependencies() {
					echo '<div class="error"><p>' . UM()->dependencies()->compare_versions( um_recaptcha_requires, um_recaptcha_version, 'recaptcha', um_recaptcha_extension ) . '</p></div>';
				}

				add_action( 'admin_notices', 'um_recaptcha_dependencies' );

			} else {
				require_once um_recaptcha_path . 'includes/core/um-recaptcha-init.php';
			}
		}
	}
}


register_activation_hook( um_recaptcha_plugin, 'um_recaptcha_activation_hook' );
function um_recaptcha_activation_hook() {
	//first install
	$version = get_option( 'um_recaptcha_version' );
	if ( ! $version ) {
		update_option( 'um_recaptcha_last_version_upgrade', um_recaptcha_version );
	}

	if ( $version != um_recaptcha_version ) {
		update_option( 'um_recaptcha_version', um_recaptcha_version );
	}

	//run setup
	if ( ! class_exists( 'um_ext\um_recaptcha\core\Recaptcha_Setup' ) ) {
		require_once um_recaptcha_path . 'includes/core/class-recaptcha-setup.php';
	}

	$recaptcha_setup = new um_ext\um_recaptcha\core\Recaptcha_Setup();
	$recaptcha_setup->run_setup();
}
