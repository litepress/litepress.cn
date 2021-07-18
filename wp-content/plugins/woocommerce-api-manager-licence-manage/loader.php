<?php

namespace LitePress\LM;

use LitePress\LM\Inc\Model\Licence;

require_once 'inc/functions.php';

require_once 'inc/model/class-licence.php';

require_once 'inc/setting.php';

add_action( 'admin_enqueue_scripts', function ( $page ) {
	if ( 'toplevel_page_lplm' !== $page ) {
		return;
	}

	wp_enqueue_style( 'remodal', LM_ROOT_URL . 'assets/remodal.min.css' );
	wp_enqueue_style( 'remodal-default-theme', LM_ROOT_URL . 'assets/remodal-default-theme.min.css' );
	wp_enqueue_style( 'lplm', LM_ROOT_URL . 'assets/lplm.css', array( 'remodal', 'remodal-default-theme' ) );

	wp_enqueue_script( 'remodal', LM_ROOT_URL . 'assets/remodal.min.js', array( 'jquery' ) );
	wp_enqueue_script( 'url', LM_ROOT_URL . 'assets/url.min.js', array( 'jquery' ) );
	wp_enqueue_script( 'lplm', LM_ROOT_URL . 'assets/lplm.js', array( 'url', 'jquery', 'remodal' ) );
} );

/**
 * API关闭函数写在主题里了，因为插件加载顺序的问题，导致钩子执行不了
 */

function api_is_disabled( $data ) {
	$is_disabled = Licence::is_disabled( $_GET['api_key'] );

	if ( false !== $is_disabled ) {
		return array(
			'code'    => '100',
			'error'   => '您的授权已被封禁，原因：' . $is_disabled,
			'success' => false,
			'data'    => array(
				'error_code' => '100',
				'error'      => '您的授权已被封禁，原因：' . $is_disabled,
			),
		);
	}

	return $data;
}

add_action( 'wc_api_success_response', 'LitePress\LM\api_is_disabled' );
add_action( 'wc_api_error_response', 'LitePress\LM\api_is_disabled' );
