<?php
/**
 * Admin View: Notice - API Products updating.
 */

defined( 'ABSPATH' ) || exit;

?>
<div id="message" class="updated woocommerce-api-manager-message">
<!--    <a class="woocommerce-api-manager-message-close notice-dismiss"-->
<!--       href="--><?php //echo esc_url( wp_nonce_url( add_query_arg( 'wc-am-hide-notice', 'api_products_updating' ), 'wc_am_hide_notices_nonce', '_wc_am_notice_nonce' ) ); ?><!--">--><?php //esc_html_e( 'Cancel API Product processing', 'woocommerce-api-manager' ); ?><!--</a>-->

    <p><?php esc_html_e( 'An API Product is being added to the API Resources database table in the background. Depending on the amount of orders that include the API Product in your store, this may take a while.', 'woocommerce-api-manager' ); ?></p>
</div>
