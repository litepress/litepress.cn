<?php
/**
 * Plugin Name: 让Woo无需登录即可下载免费产品
 * Description: 有很多同类插件都可实现此功能，但该插件优势在于轻量以及会记录下载量（通过记录消费量来实现），触发方式为在应用市场 URL 上附件参数：?woo-free-download=' . $product_id
 * Version: 1.0.0
 * Author: LitePress社区
 * Author URI: https://litepress.cn/
 * Requires WP: 5.5.4
 * Requires PHP: 7.4
 * License: GPLv3 or later
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

add_action( 'wp_loaded', function () {
	if ( isset( $_GET['woo-free-download'] ) && ! empty( $_GET['woo-free-download'] ) ) {
		$product_id = $_GET['woo-free-download'];
		$product    = wc_get_product( $product_id );

		/**
		 * 记录下载数(通过增加销量实现)
		 */
		$total_sales = $product->get_total_sales() + 1;
		update_post_meta( $product->get_id(), 'total_sales', $total_sales );

		global $wpdb;
		$wpdb->update(
			"{$wpdb->prefix}wc_product_meta_lookup",
			array(
				'total_sales'    => $total_sales,
			),
			array(
				'product_id' => $product->get_id(),
			),
		);

		/**
		 * 301到真实的下载地址
		 */
		foreach ( $product->get_downloads() as $download ) {
			wp_redirect( $download['file'], 301 );
			exit;
		}
	}
} );
