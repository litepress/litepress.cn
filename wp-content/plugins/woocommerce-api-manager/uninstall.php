<?php

// Make sure that we are uninstalling
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

function wc_am_uninstall() {
	global $wpdb;

	// Remove tables
	$wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->prefix . "wc_am_secure_hash" );

}

wc_am_uninstall();