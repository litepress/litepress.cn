<?php
/**
 * 头像管理页面
 */

use const LitePress\Cravatar\PLUGIN_DIR;

add_filter( 'wp_title_parts', function ( $title ) {
	$title[0] = '头像管理';

	return $title;
} );

get_header();

readfile( PLUGIN_DIR . '/frontend/dist/index.html' );

get_footer();
