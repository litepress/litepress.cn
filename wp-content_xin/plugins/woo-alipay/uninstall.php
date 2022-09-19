<?php

if ( ! defined( 'ABSPATH' ) || ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit; // Exit if accessed directly
}

global $wpdb;

$option_prefix      = $wpdb->esc_like( 'woo_alipay_' );
$wc_option_settings = 'woocommerce_alipay_settings';
$sql                = "DELETE FROM $wpdb->options WHERE `option_name` = '%s'";

$wpdb->query( $wpdb->prepare( $sql, $wc_option_settings ) ); // @codingStandardsIgnoreLine
