<?php

namespace LitePress\WAMAL;

use LitePress\WAMAL\Inc\Model\Api_Log;

require_once 'inc/functions.php';

require_once 'inc/setting.php';

require_once 'inc/model/class-api-log.php';

add_action( 'admin_enqueue_scripts', function ( $page ) {
	if ( 'toplevel_page_lpapilog' !== $page ) {
		return;
	}

	wp_enqueue_style( 'wamal', WAMAL_ROOT_URL . '/assets/wamal.css' );

	wp_enqueue_script( 'url', WAMAL_ROOT_URL . 'assets/url.min.js', array( 'jquery' ) );
	wp_enqueue_script( 'wamal', WAMAL_ROOT_URL . 'assets/wamal.js', array( 'url' ) );
} );

function log( $data ) {
	$api_log = new Api_Log();

	$api_log->insert( $_GET, $data );

	return $data;
}

add_filter( 'wc_api_success_response', 'LitePress\WAMAL\log', 9999 );
add_filter( 'wc_api_error_response', 'LitePress\WAMAL\log', 9999 );
