<?php
/**
 * Admin View: Custom Notices
 */

defined( 'ABSPATH' ) || exit;

if ( ! empty( $notice ) && ! empty( $notice_html ) ) {
	?>
    <div id="message" class="updated woocommerce-api-manager-message">
        <a class="woocommerce-api-manager-message-close notice-dismiss"
           href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'wc-am-hide-notice', $notice ), 'wc_am_hide_notices_nonce', '_wc_am_notice_nonce' ) ); ?>"><?php esc_html_e( 'Dismiss', 'woocommerce-api-manager' ); ?></a>
		<?php echo wp_kses_post( wpautop( $notice_html ) ); ?>
    </div>
<?php } ?>