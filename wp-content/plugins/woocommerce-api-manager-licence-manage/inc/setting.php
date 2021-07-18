<?php

namespace LitePress\LM\Inc;

use LitePress\LM\Inc\Model\Licence;
use WC_Product_Vendors_Utils;

add_action( 'admin_menu', function () {
	add_menu_page(
		'授权管理',
		'授权管理',
		'wc_product_vendors_admin_vendor',
		'lplm',
		'LitePress\LM\Inc\html',
		'dashicons-admin-network',
		50
	);
} );

function html() {
	if ( isset( $_SERVER['REQUEST_METHOD'] ) && 'POST' === $_SERVER['REQUEST_METHOD'] ) {
		if ( (int)WC_Product_Vendors_Utils::get_logged_in_vendor() === (int)Licence::get_vendor_id_by_order_id( $_POST['order_id'] ) ) {
			switch ( $_POST['method'] ) {
				case 'enable':
					Licence::enable_api( $_POST['order_id'] );
					break;
				case 'disable':
					Licence::disable_api( $_POST['order_id'], $_POST['comment'] );
					break;
				default:
					break;
			}
		}
	}

	require_once LM_ROOT_PATH . 'template/lplm.php';
}
