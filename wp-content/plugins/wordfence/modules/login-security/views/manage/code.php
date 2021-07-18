<?php
if (!defined('WORDFENCE_LS_VERSION')) { exit; }
/**
 * @var string $secret The TOTP secret in binary. Required.
 * @var string $base32Secret The base32-encoded TOTP secret. Required.
 * @var string $totpURL The TOTP URL for the QR code content. Required.
 */
?>
<div class="wfls-block wfls-always-active wfls-flex-item-full-width">
	<div class="wfls-block-header wfls-block-header-border-bottom">
		<div class="wfls-block-header-content">
			<div class="wfls-block-title">
				<strong><?php esc_html_e('1. Scan Code or Enter Key', 'wordfence'); ?></strong>
			</div>
		</div>
	</div>
	<div class="wfls-block-content wfls-padding-add-bottom">
		<p>Scan the code below with your authenticator app to add this account. Some authenticator apps also allow you to type in the text version instead.</p>
		<div id="wfls-qr-code"></div>
		<p class="wfls-center wfls-no-bottom"><input id="wfls-qr-code-text" class="wfls-center" type="text" value="<?php echo esc_attr($base32Secret); ?>" onclick="this.select();" readonly></p>
	</div>
</div>
<script type="application/javascript">
	(function($) {
		$(function() {
			$('#wfls-qr-code').qrcode({text: '<?php echo \WordfenceLS\Text\Model_JavaScript::esc_js($totpURL); ?>', width: (WFLS.screenSize(500) ? 175 : 256), height: (WFLS.screenSize(500) ? 175 : 256)});
			if (!WFLS.screenSize(500)) {
				$('#wfls-qr-code-text').attr('size', 35).css('font-family', 'monospace');
			}
		});
	})(jQuery);
</script> 