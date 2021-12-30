<?php
if (!defined('WORDFENCE_LS_VERSION')) { exit; }
$maxFailures = (int) \WordfenceLS\Controller_Time::FAILURE_LIMIT;
$cronDisabled = \WordfenceLS\Controller_Settings::shared()->is_ntp_cron_disabled($failureCount);
$id = 'wfls-option-ntp';
?>
<ul id="<?php echo esc_attr($id); ?>" class="wfls-option wfls-flex-vertical wfls-flex-align-left">
	<li class="wfls-option-title"><strong><?php esc_html_e('NTP', 'wordfence-2fa') ?></strong></li>
	<li class="wfls-option-content">
		<p><?php esc_html_e('NTP is a protocol that allows for remote time synchronization. Wordfence Login Security uses this protocol to ensure that it has the most accurate time which is necessary for TOTP-based two-factor authentication.', 'wordfence-2fa') ?></p>
	<?php if (\WordfenceLS\Controller_Settings::shared()->is_ntp_disabled_via_constant()): ?>
		<p><?php esc_html_e('The constant WORDFENCE_LS_DISABLE_NTP is defined which disables NTP entirely. Remove this constant or set it to a falsy value to enable NTP.', 'wordfence-2fa') ?></p>
	<?php elseif ($cronDisabled): ?>
		<?php if ($failureCount > 0): ?>
			<p><strong><?php echo sprintf(esc_html__('NTP is currently disabled as %d subsequent attempts have failed.', 'wordfence-2fa'), $maxFailures) ?></strong></p>
		<?php else: ?>
			<p><?php esc_html_e('NTP was manually disabled.', 'wordfence-2fa') ?></p>
		<?php endif ?>
		<button id="wfls-reset-ntp-failure-count" class="wfls-btn wfls-btn-sm wfls-btn-default"><?php esc_html_e('Reset', 'wordfence-2fa') ?></button>
	<?php else: ?>
		<p><?php echo wp_kses(__('NTP is currently <strong>enabled</strong>.', 'wordfence-2fa'), array('strong'=>array())); ?></p>
		<?php if ($failureCount > 0): ?>
			<?php $remainingAttempts = $maxFailures - $failureCount; ?>
			<p>
				<strong><?php esc_html_e('NTP updates are currently failing.', 'wordfence-2fa') ?></strong> 
				<?php echo $remainingAttempts > 0 ? sprintf(esc_html__('NTP will be automatically disabled after %d more attempts.', 'wordfence-2fa'), $remainingAttempts) : esc_html__('NTP will be automatically disabled after 1 more attempt.', 'wordfence-2fa') ?>
			</p>
		<?php endif ?>
		<button id="wfls-disable-ntp" class="wfls-btn wfls-btn-sm wfls-btn-default"><?php esc_html_e('Disable', 'wordfence-2fa') ?></button>
	<?php endif ?>
	</li>
</ul>
<script>
	(function($) {
		$(function() {
			$('#wfls-reset-ntp-failure-count').on('click', function(e) {
				e.preventDefault();
				e.stopPropagation();

				function handleError(message) {
					WFLS.panelModal(
						(WFLS.screenSize(500) ? '300px' : '400px'),
						'<?php echo \WordfenceLS\Text\Model_JavaScript::esc_js(__('Error Resetting NTP', 'wordfence-2fa')); ?>',
						typeof message === 'undefined' ? '<?php echo \WordfenceLS\Text\Model_JavaScript::esc_js(__('An error was encountered while trying to reset the NTP state. Please try again.', 'wordfence-2fa')); ?>' : message
					);
				}

				WFLS.ajax('wordfence_ls_reset_ntp_failure_count', [],
					function(response) {
						if (response.error) {
							handleError(response.error);
						}
						else {
							window.location.reload();
						}
					},
					function (error) {
						handleError();
					});
			});
			$('#wfls-disable-ntp').on('click', function(e) {
				e.preventDefault();
				e.stopPropagation();

				function handleError(message) {
					WFLS.panelModal(
						(WFLS.screenSize(500) ? '300px' : '400px'),
						'<?php echo \WordfenceLS\Text\Model_JavaScript::esc_js(__('Error Disabling NTP', 'wordfence-2fa')); ?>',
						typeof message === 'undefined' ? '<?php echo \WordfenceLS\Text\Model_JavaScript::esc_js(__('An error was encountered while trying to disable NTP. Please try again.', 'wordfence-2fa')); ?>' : message
					);
				}

				WFLS.ajax('wordfence_ls_disable_ntp', [],
					function(response) {
						if (response.error) {
							handleError(response.error);
						}
						else {
							window.location.reload();
						}
					},
					function (error) {
						handleError();
					});
			});
		});
	})(jQuery);
</script>