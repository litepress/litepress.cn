<?php
namespace LitePress\WAMAL\Inc;

add_action( 'admin_menu', function () {
	add_menu_page(
		'API日志',
		'API日志',
		'wc_product_vendors_admin_vendor',
		'lpapilog',
		'LitePress\WAMAL\Inc\html',
		'dashicons-hammer',
		51
	);
} );

function html() {
	require_once WAMAL_ROOT_PATH . 'template/lpapilog.php';
}
