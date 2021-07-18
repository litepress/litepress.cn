<?php
/**
 * Plugin Name: WordPress实人认证插件
 * Version: 1.0.0
 * Description: 支持调用阿里云实人认证接口及天眼查企业三要素接口对用户进行自动身份核验
 * Author: WP中国本土化社区
 * Author URI: https://wp-china.org
 * Requires at least: 5.4
 * Tested up to: 5.7.1
 * Requires PHP: 7.4
 */

namespace WCY\WC_Product_Vendor_Registration;

add_action( 'wp_loaded', function () {
	/** 装载插件 */
	require_once 'load.php';
} );
