<?php
/**
 * 细化控制每个页面加载的插件
 *
 * @author 孙锡源 <sxy@ibadboy.net>
 * @version 1.0.0
 */

if ( isset( $_GET['is-wc-api'] ) ) {
	add_filter( 'allow_active_plugins', function ( $allows, $plugins ) {

		return array(
			'woocommerce',
			'wcy-woo-data-entry',
			'external-media-without-import',
			'woocommerce-product-vendors',
		);
	}, 10, 2 );

	add_filter('woocommerce_rest_check_permissions', function ($permission, $context, $object_id, $post_type) {
		return true;
	}, 10, 4);
}

add_filter( 'option_active_plugins', function ( $plugins ) {
	$new_plugins = array();
	$allows = apply_filters( 'allow_active_plugins', array(), $plugins );

	if ( empty( $allows ) ) {
		$new_plugins = $plugins;
	} else {
		foreach ( $plugins as $plugin ) {
			$plugin_dir = explode( '/', $plugin )[0];
			if ( in_array( $plugin_dir, $allows ) ) {
				$new_plugins[] = $plugin;
			}
		}
	}

	return $new_plugins;
} );

