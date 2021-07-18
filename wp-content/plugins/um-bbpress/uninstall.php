<?php
/**
* Uninstall UM bbPress
*
*/

// Exit if accessed directly.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) exit;


if ( ! defined( 'um_bbpress_path' ) ) {
	define( 'um_bbpress_path', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'um_bbpress_url' ) ) {
	define( 'um_bbpress_url', plugin_dir_url( __FILE__ ) );
}

if ( ! defined( 'um_bbpress_plugin' ) ) {
	define( 'um_bbpress_plugin', plugin_basename( __FILE__ ) );
}

$options = get_option( 'um_options', array() );

if ( ! empty( $options['uninstall_on_delete'] ) ) {
	if ( ! class_exists( 'um_ext\um_bbpress\core\bbPress_Setup' ) ) {
		require_once um_bbpress_path . 'includes/core/class-bbpress-setup.php';
	}

	$bbpress_setup = new um_ext\um_bbpress\core\bbPress_Setup();

	//remove settings
	foreach ( $bbpress_setup->settings_defaults as $k => $v ) {
		unset( $options[ $k ] );
	}

	unset( $options['um_bbpress_license_key'] );

	update_option( 'um_options', $options );

	global $wpdb;
	$wpdb->query(
		"DELETE 
		FROM {$wpdb->postmeta} 
		WHERE meta_key = '_um_bbpress_can_topic' OR 
		      meta_key = '_um_bbpress_can_reply'"
	);

	delete_option( 'um_bbpress_last_version_upgrade' );
	delete_option( 'um_bbpress_version' );
}