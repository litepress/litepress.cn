<?php
if (!defined('WORDFENCE_LS_VERSION')) { exit; }

use \WordfenceLS\Controller_Settings;

$states = array(
	Controller_Settings::STATE_2FA_DISABLED => __('Disabled', 'wordfence-2fa'),
	Controller_Settings::STATE_2FA_OPTIONAL => __('Optional', 'wordfence-2fa'),
	Controller_Settings::STATE_2FA_REQUIRED => __('Required', 'wordfence-2fa')
);

$gracePeriod = Controller_Settings::shared()->get_int(Controller_Settings::OPTION_REQUIRE_2FA_USER_GRACE_PERIOD, Controller_Settings::DEFAULT_REQUIRE_2FA_USER_GRACE_PERIOD);

$requiredRoles = array();
foreach ($options as $option) {
	if ($option['state'] === Controller_Settings::STATE_2FA_REQUIRED) {
		$requiredRoles[$option['role']] = $option['title'];
	}
}

?>
<ul class="wfls-option wfls-option-2fa-roles">
	<li class="wfls-option-title">
		<label><?php esc_html_e('2FA Roles', 'wordfence-2fa') ?></label>
	</li>
	<li class="wfls-option-content">
		<ul>
		<?php foreach ($options as $option): ?>
		<?php $selectId = 'wfls-2fa-role-' . $option['name']; ?>
		<li>
			<label for="<?php echo esc_attr($selectId) ?>"><?php echo esc_html($option['title']) ?></label>
			<select id="<?php echo esc_attr($selectId) ?>" name="<?php echo esc_attr($option['name']) ?>" class="wfls-option-select">
				<?php foreach ($states as $key => $label): ?>
				<?php if (!$option['allow_disabling'] && $key === Controller_Settings::STATE_2FA_DISABLED) continue; ?>
				<option
					value="<?php echo esc_attr($key); ?>"
					<?php if($option['state'] === $key): ?> selected<?php endif ?>
					<?php if(!$option['editable']): ?> disabled<?php endif ?>
				>
					<?php echo esc_html($label) ?>
				</option>
				<?php endforeach ?>
			</select>
		</li>
		<?php endforeach ?>
		</ul>
		<?php if ($hasWoocommerce): ?>
			<p><?php esc_html_e('By default, the customer role provided by WooCommerce does not have access to admin pages and therefore users in this role cannot configure two-factor authentication at this time. A 2FA setup process will be available for the customer role in an upcoming release.', 'wordfence-2fa') ?></p>
		<?php endif ?>
	</li>
	<li class="wfls-2fa-grace-period-container">
		<label for="wfls-2fa-grace-period" class="wfls-primary-label"><?php esc_html_e('Grace Period', 'wordfence-2fa') ?></label>
		<input id="wfls-2fa-grace-period" type="text" pattern="[0-9]+" value="<?php echo (int)$gracePeriod; ?>" class="wfls-option-input wfls-option-input-required" name="<?php echo esc_html(Controller_Settings::OPTION_REQUIRE_2FA_USER_GRACE_PERIOD) ?>" maxlength="2">
		<label for="wfls-2fa-grace-period"><?php esc_html_e('days') ?></label>
		<div id="wfls-grace-period-zero-warning" style="display: none;">
			<strong><?php esc_html_e('Setting the grace period to 0 will prevent users in roles where 2FA is required, including newly created users, from logging in if they have not already enabled two-factor authentication.', 'wordfence-2fa') ?></strong>
			<a href="<?php echo esc_attr(\WordfenceLS\Controller_Support::esc_supportURL(\WordfenceLS\Controller_Support::ITEM_MODULE_LOGIN_SECURITY_ROLES)) ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Learn More', 'wordfence-2fa') ?></a>
		</div>
		<small><?php esc_html_e('For roles that require 2FA, users will have this many days to set up 2FA. Failure to set up 2FA during this period will result in the user losing account access. This grace period will apply to new users from the time of account creation. For existing users, this grace period will apply relative to the time at which the requirement is implemented. This grace period will not automatically apply to admins and must be manually enabled for each admin user.', 'wordfence-2fa') ?></small>
	</li>
	<?php if (!empty($requiredRoles)): ?>
	<li class="wfls-2fa-notification-action">
		<select id="wfls-grace-period-notification-role">
			<?php foreach ($requiredRoles as $role => $label): ?>
			<option value="<?php echo esc_attr($role) ?>"><?php echo esc_html($label) ?></option>
			<?php endforeach ?>
		</select>
		<button class="wfls-btn wfls-btn-default wfls-btn-sm" id="wfls-send-grace-period-notification"><?php esc_html_e('Notify') ?></button>
		<small><?php esc_html_e('Send an email to users with the selected role to notify them of the grace period for enabling 2FA.') ?></small>
	</li>
	<?php endif ?>
</ul>
<script>
	(function($) {
		function sendGracePeriodNotification(notifyAll) {
			var request = {
				role: $('#wfls-grace-period-notification-role').val()
			};
			if (typeof notifyAll !== "undefined" && notifyAll)
				request.notify_all = true;
			WFLS.ajax('wordfence_ls_send_grace_period_notification', request, 
				function(response) {
					if (response.error) {
						var settings = {
							additional_buttons: []
						};
						if (response.limit_exceeded) {
							settings.additional_buttons.push({
								label: '<?php echo \WordfenceLS\Text\Model_JavaScript::esc_js(__('Send Anyway', 'wordfence-2fa')); ?>',
								id: 'wfls-send-grace-period-notification-over-limit'
							});
						}
						WFLS.panelModal((WFLS.screenSize(500) ? '300px' : '400px'), '<?php echo \WordfenceLS\Text\Model_JavaScript::esc_js(__('Error Sending Notification', 'wordfence-2fa')); ?>', response.error, settings);
					}
					else {
						WFLS.panelModal((WFLS.screenSize(500) ? '300px' : '400px'), '<?php echo \WordfenceLS\Text\Model_JavaScript::esc_js(__('Notification Sent', 'wordfence-2fa')); ?>', response.confirmation);
					}
					if (request.notify_all) {
						WFLS.panelClose();
					}
				},
				function (error) {
					WFLS.panelModal((WFLS.screenSize(500) ? '300px' : '400px'), '<?php echo \WordfenceLS\Text\Model_JavaScript::esc_js(__('Error Sending Notification', 'wordfence-2fa')); ?>', '<?php echo \WordfenceLS\Text\Model_JavaScript::esc_js(__('An error was encountered while trying to send the notification. Please try again.', 'wordfence-2fa')); ?>');
					if (request.notify_all) {
						WFLS.panelClose();
					}
				});
		}
		$('#wfls-send-grace-period-notification').on('click', function(e) {
			e.preventDefault();
			e.stopPropagation();
			sendGracePeriodNotification();	
		});
		$(document).on('click', '#wfls-send-grace-period-notification-over-limit', function() {
			sendGracePeriodNotification(true);
			$(this).prop("disabled", true);
		});
		$('#wfls-2fa-grace-period').on('input', function(e) {
			var value = $(this).val();
			value = value.replace(/[^0-9]/g, '');
			value = parseInt(value);
			if (isNaN(value))
				value = '';
			if (value === 0) {
				$("#wfls-grace-period-zero-warning").show();
			}
			else {
				$("#wfls-grace-period-zero-warning").hide();
			}
			$(this).val(value);
		}).trigger('input');
	})(jQuery);
</script>