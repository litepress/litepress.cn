<?php
/**
 * Order email to vendor (plain text).
 *
 * @version 2.1.0
 * @since 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( version_compare( WC_VERSION, '3.0.0', '>=' ) ) {
	$order_date = $order->get_date_created();
	$billing_first_name = $order->get_billing_first_name();
	$billing_last_name = $order->get_billing_last_name();
} else {
	$order_date = $order->order_date;
	$billing_first_name = $order->billing_first_name;
	$billing_last_name = $order->billing_last_name;
}

echo "= " . $email_heading . " =\n\n";

echo sprintf( __( 'You have received an order from %s.', 'woocommerce-product-vendors' ), $billing_first_name . ' ' . $billing_last_name ) . "\n\n";

echo "=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

echo strtoupper( sprintf( __( 'Order number: %s', 'woocommerce-product-vendors' ), $order->get_order_number() ) ) . "\n";
echo date_i18n( __( 'jS F Y', 'woocommerce-product-vendors' ), strtotime( $order_date ) ) . "\n";

echo "\n";

$email->render_order_details_table( $order, $sent_to_admin, $plain_text, $email, $this_vendor );

do_action( 'woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text, $email );

do_action( 'wc_product_vendors_email_order_meta', $order, $sent_to_admin, $plain_text, $email );
echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

do_action( 'woocommerce_email_after_order_table', $order, $sent_to_admin, $plain_text, $email );

do_action( 'woocommerce_email_customer_details', $order, $sent_to_admin, $plain_text, $email );

echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

echo apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) );
