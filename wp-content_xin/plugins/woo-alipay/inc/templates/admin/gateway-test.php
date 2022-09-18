<?php if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

?>
<tr valign="top">
	<th scope="row" class="titledesc">
		<label for="woo_alipay_test_connection"><?php esc_html_e( 'Test Connection', 'woo-alipay' ); ?></label>
	</th>
	<td class="forminp">
		<?php wp_nonce_field( '_woo_alipay_test_nonce', 'woo_alipay_nonce' ); ?>
		<a id="woo_alipay_test_connection" href="#" class="button"><?php esc_html_e( 'Test Now', 'woo-alipay' ); ?></a>
		<span class="test-status">
			<span class="success dashicons dashicons-yes-alt"></span>
			<span class="failure dashicons dashicons-no"></span>
			<span class="error dashicons dashicons-no"></span>
		</span>
		<span class="spinner"></span>
		<p class="description help is-active">
			<?php esc_html_e( 'Send a message to Alipay to check if the gateway is properly set up.', 'woo-alipay' ); ?>
		</p>
		<p class="description test-status-message success">
			<?php esc_html_e( 'Connexion successful', 'woo-alipay' ); ?>
		</p>
		<p class="description test-status-message failure">
			<?php esc_html_e( 'Connexion failed: please make sure to double check the configuration. If necessary, try "Enable logging" and check the log file for more information.', 'woo-alipay' ); ?>
		</p>
		<p class="description test-status-message error">
			<?php esc_html_e( 'Unexpected error - please reload the page and try again.', 'woo-alipay' ); ?>
		</p>
	</td>
</tr>
