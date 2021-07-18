<?php
/**
 * Admin View: Notice - Update
 */

defined( 'ABSPATH' ) || exit;

?>
<div id="message" class="updated woocommerce-api-manager-message wc-am-connect">
    <p><strong><?php esc_html_e( 'WooCommerce API Manager data update', 'woocommerce-api-manager' ); ?></strong>
        &#8211; <?php esc_html_e( 'We need to update your database to the latest version.', 'woocommerce-api-manager' ); ?></p>
    <p class="submit"><a
                href="<?php echo esc_url( add_query_arg( 'do_update_woocommerce_api_manager', 'true', admin_url( 'admin.php?page=wc-settings&tab=api_manager' ) ) ); ?>"
                class="woocommerce-api-manager-update-now button-primary"><?php esc_html_e( 'Run the updater', 'woocommerce-api-manager' ); ?></a></p>
</div>
<script type="text/javascript">
    "use strict";
    jQuery('.wc-am-update-now').click('click', function () {
        return window.confirm('<?php echo esc_js( __( 'It is strongly recommended that you backup your database before proceeding. Are you sure you wish to run the updater now?', 'woocommerce-api-manager' ) ); ?>'); // jshint ignore:line
    });
</script>