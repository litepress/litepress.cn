<?php
/**
 * Admin View: Notice - Updating
 */

defined( 'ABSPATH' ) || exit;

?>
<div id="message" class="updated woocommerce-api-manager-message wc-am-connect">
    <p><strong><?php esc_html_e( 'WooCommerce API Manager data update', 'woocommerce-api-manager' ); ?></strong>
        &#8211; <?php esc_html_e( 'Your database is being updated in the background.', 'woocommerce-api-manager' ); ?> <a
                href="<?php echo esc_url( add_query_arg( 'force_update_woocommerce_api_manager', 'true', admin_url( 'admin.php?page=wc-settings&tab=api_manager' ) ) ); ?>"><?php esc_html_e( 'Taking a while? Click here to run it now.', 'woocommerce-api-manager' ); ?></a>
    </p>
</div>