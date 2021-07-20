<?php

namespace LitePress\Cravatar\Inc;

add_action( 'admin_menu', function () {
	add_menu_page(
		'头像审核',
		'头像审核',
		'manage_options',
		'avatar-verify',
		'LitePress\Cravatar\Inc\avatar_verify',
		'dashicons-cart',
		80
	);
} );

function avatar_verify() {
	global $wpdb;

	$images = $wpdb->get_results( "SELECT md5,url FROM {$wpdb->prefix}avatar_verify WHERE status=0 LIMIT 100;" );

	echo '<h1>头像审核</h1>';

	foreach ($images as $image) {
		echo "<img src='{$image->url}' width='100' height='100'>";
	}
}
