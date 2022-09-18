<?php
/**
 * Admin View: Notice - Updated
 */

defined( 'ABSPATH' ) || exit;

?>
<div id="message" class="updated woocommerce-api-manager-message wc-am-connect woocommerce-api-manager-message--success">
    <a class="woocommerce-api-manager-message-close notice-dismiss"
       href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'wc-am-hide-notice', 'update', remove_query_arg( 'do_update_woocommerce_api_manager' ) ), 'wc_am_hide_notices_nonce', '_wc_am_notice_nonce' ) ); ?>"><?php esc_html_e( 'Dismiss', 'woocommerce-api-manager' ); ?></a>

    <p><?php esc_html_e( 'WooCommerce API Manager data update complete. Thank you for updating to the latest version!', 'woocommerce-api-manager' ); ?></p>
</div>