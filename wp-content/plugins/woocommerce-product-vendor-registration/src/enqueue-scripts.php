<?php
/**
 * 脚本注册文件
 *
 * @package WP_REAL_PERSON_VERIFY
 */

add_action('wp_enqueue_scripts', function () {
	global $wp;

	if ( ! isset( $wp->query_vars['pagename'] ) ) {
		return;
	}

	$pages = explode( '/', $wp->query_vars['pagename'] );

	if ( 'real-person-verify' === $pages[0] ) {
		wp_enqueue_style( 'wcpvr', WPRPV_ROOT_URL . 'assets/css/wcpvr.css', array(), WPRPV_VERSION );

		wp_enqueue_script( 'wcpvr', WPRPV_ROOT_URL . 'assets/js/wcpvr.js', array(), WPRPV_VERSION, true );
	}
});
