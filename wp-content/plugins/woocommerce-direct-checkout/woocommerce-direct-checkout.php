<?php

/**
 * Plugin Name: Direct Checkout for WooCommerce
 * Plugin URI:  https://quadlayers.com/documentation/woocommerce-direct-checkout/
 * Description: Simplifies the checkout process to improve your sales rate.
 * Version:     2.6.7
 * Author:      QuadLayers
 * Author URI:  https://quadlayers.com
 * License: GPLv3
 * Text Domain: woocommerce-direct-checkout
 * WC requires at least: 3.1.0
 * WC tested up to: 6.8
 */
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

define( 'QLWCDC_PLUGIN_NAME', 'Direct Checkout for WooCommerce' );
define( 'QLWCDC_PLUGIN_VERSION', '2.6.7' );
define( 'QLWCDC_PLUGIN_FILE', __FILE__ );
define( 'QLWCDC_PLUGIN_DIR', __DIR__ . DIRECTORY_SEPARATOR );
define( 'QLWCDC_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'QLWCDC_PREFIX', 'qlwcdc' );
define( 'QLWCDC_DOMAIN', QLWCDC_PREFIX );
define( 'QLWCDC_WORDPRESS_URL', 'https://wordpress.org/plugins/woocommerce-direct-checkout/' );
define( 'QLWCDC_REVIEW_URL', 'https://wordpress.org/support/plugin/woocommerce-direct-checkout/reviews/?filter=5#new-post' );
define( 'QLWCDC_DEMO_URL', 'https://quadlayers.com/woocommerce-direct?utm_source=qlwcdc_admin' );
define( 'QLWCDC_DOCUMENTATION_URL', 'https://quadlayers.com/documentation/woocommerce-direct-checkout/?utm_source=qlwcdc_admin' );
define( 'QLWCDC_PURCHASE_URL', 'https://quadlayers.com/portfolio/woocommerce-direct-checkout/?utm_source=qlwcdc_admin' );
define( 'QLWCDC_SUPPORT_URL', 'https://quadlayers.com/account/support/?utm_source=qlwcdc_admin' );
define( 'QLWCDC_GROUP_URL', 'https://www.facebook.com/groups/quadlayers' );

define( 'QLWCDC_PREMIUM_SELL_SLUG', 'woocommerce-direct-checkout-pro' );
define( 'QLWCDC_PREMIUM_SELL_NAME', 'WooCommerce Direct Checkout' );
define( 'QLWCDC_PREMIUM_SELL_URL', 'https://quadlayers.com/portfolio/woocommerce-direct-checkout/?utm_source=qlwcdc_admin' );

define( 'QLWCDC_CROSS_INSTALL_SLUG', 'woocommerce-checkout-manager' );
define( 'QLWCDC_CROSS_INSTALL_NAME', 'Checkout Manager' );
define( 'QLWCDC_CROSS_INSTALL_DESCRIPTION', esc_html__( 'Checkout Field Manager( Checkout Manager ) for WooCommerce allows you to add custom fields to the checkout page, related to billing, Shipping or Additional fields sections.', 'woocommerce-direct-checkout' ) );
define( 'QLWCDC_CROSS_INSTALL_URL', 'https://quadlayers.com/portfolio/woocommerce-checkout-manager/?utm_source=qlwcdc_admin' );

if ( ! class_exists( 'QLWCDC' ) ) {
	include_once QLWCDC_PLUGIN_DIR . 'includes/qlwcdc.php';
}

require_once QLWCDC_PLUGIN_DIR . 'includes/quadlayers/widget.php';
require_once QLWCDC_PLUGIN_DIR . 'includes/quadlayers/notices.php';
require_once QLWCDC_PLUGIN_DIR . 'includes/quadlayers/links.php';
