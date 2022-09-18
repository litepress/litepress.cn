<?php

namespace LitePress\WAMWR;

use LitePress\WAMWR\Inc\Model\Rsa;
use function LitePress\WAMWR\Inc\ras_sign;

require_once 'inc/functions.php';

require_once 'inc/setting.php';

require_once 'inc/model/class-rsa.php';

add_action( 'admin_enqueue_scripts', function ( $page ) {
	if ( 'toplevel_page_lprsa' !== $page ) {
		return;
	}

	wp_enqueue_style( 'wamwr', WAMWR_ROOT_URL . '/assets/wamwr.css' );
	wp_enqueue_script( 'wamwr', WAMWR_ROOT_URL . '/assets/wamwr.js', array( 'jquery' ) );
} );

add_filter( 'wc_api_manager_status_data', function ( $data ) {
	if ( isset( $_GET['public_key_id'] ) ) {
		$rsa_model              = new Rsa();
		$private_key            = $rsa_model->get_private_key( $_GET['public_key_id'] );
		$data['activated_sina'] = ras_sign( (string) $_GET['api_key'], (string) $private_key );
	}

	return $data;
} );

add_filter( 'wc_api_manager_activation_data', function ( $data ) {
	if ( isset( $_GET['public_key_id'] ) ) {
		$rsa_model              = new Rsa();
		$private_key            = $rsa_model->get_private_key( $_GET['public_key_id'] );
		$data['activated_sina'] = ras_sign( (string) $_GET['api_key'], (string) $private_key );
	}

	return $data;
} );
