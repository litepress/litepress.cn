<?php
namespace LitePress\WAMWR\Inc;

use LitePress\WAMWR\Inc\Model\Rsa;

add_action( 'admin_menu', function () {
	add_menu_page(
		'证书管理',
		'证书管理',
		'wc_product_vendors_admin_vendor',
		'lprsa',
		'LitePress\WAMWR\Inc\html',
		'dashicons-index-card',
		52
	);
} );

function html() {
	$rsa_model = new Rsa();

	if ( isset( $_SERVER['REQUEST_METHOD'] ) && 'POST' === $_SERVER['REQUEST_METHOD'] ) {
		switch ( $_POST['method'] ) {
			case 'create':
				$key = generate_rsa();
				$rsa_model->insert( get_current_user_id(), $key['public_key'], $key['private_key'] );
				break;
			case 'delete':
				$rsa_model->delete( $_POST['id'] );
				break;
			default:
				break;
		}
	}

	$rsa_list = $rsa_model->get_all_public_key( get_current_user_id() );

	require_once WAMWR_ROOT_PATH . 'template/lprsa.php';
}
