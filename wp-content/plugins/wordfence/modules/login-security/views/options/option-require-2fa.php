<?php
if (!defined('WORDFENCE_LS_VERSION')) { exit; }

$requireOptionName = \WordfenceLS\Controller_Settings::OPTION_REQUIRE_2FA_ADMIN;
$currentRequireValue = \WordfenceLS\Controller_Settings::shared()->get_bool($requireOptionName); 

$gracePeriodEnabledOptionName = \WordfenceLS\Controller_Settings::OPTION_REQUIRE_2FA_GRACE_PERIOD_ENABLED;
$currentGracePeriodEnabledValue = \WordfenceLS\Controller_Settings::shared()->get_bool($gracePeriodEnabledOptionName);

$gracePeriodDateOptionName = \WordfenceLS\Controller_Settings::OPTION_REQUIRE_2FA_GRACE_PERIOD;
$currentGracePeriodDateValue = \WordfenceLS\Controller_Settings::shared()->get_int($gracePeriodDateOptionName, \WordfenceLS\Controller_Time::time() + 7 *84600);

if ($currentGracePeriodEnabledValue && $currentGracePeriodDateValue < \WordfenceLS\Controller_Time::time()) { //Clear the grace period settings if this is the first page view since it expired
	\WordfenceLS\Controller_Settings::shared()->set($gracePeriodEnabledOptionName, false);
	$currentGracePeriodEnabledValue = false;
	\WordfenceLS\Controller_Settings::shared()->remove($gracePeriodDateOptionName);
	$currentGracePeriodDateValue = \WordfenceLS\Controller_Settings::shared()->get_int($gracePeriodDateOptionName, \WordfenceLS\Controller_Time::time() + 7 *84600);
}

$timeZoneMinutes = 0;
$tz = get_option('timezone_string');
if (!empty($tz)) {
	$timezone = new DateTimeZone($tz);
	$dtStr = gmdate("c", (int) $currentGracePeriodDateValue); //Have to do it this way because of PHP 5.2
	$dt = new DateTime($dtStr, $timezone);
	$timeZoneMinutes = (int) ($timezone->getOffset($dt) / 60);
}
else {
	$gmt = get_option('gmt_offset');
	if (!empty($gmt)) {
		$timeZoneMinutes = (int) ($gmt * 60);
	}
}
?>
<ul id="wfls-option-require-2fa" data-option="<?php echo esc_attr($requireOptionName); ?>" data-enabled-value="1" data-disabled-value="0" data-original-value="<?php echo $currentRequireValue ? '1' : '0'; ?>">
	<li>
		<ul class="wfls-option wfls-padding-add-bottom-small">
			<li id="wfls-require-2fa-admin" class="wfls-option-checkbox<?php echo ($currentRequireValue ? ' wfls-checked' : ''); ?>" role="checkbox" aria-checked="<?php echo ($currentRequireValue ? 'true' : 'false'); ?>" tabindex="0"><i class="wfls-ion-ios-checkmark-empty" aria-hidden="true" aria-labelledby="wfls-require-2fa-admin-label"></i></li>
			<li class="wfls-option-title">
				<ul class="wfls-flex-vertical wfls-flex-align-left">
					<li>
						<strong id="wfls-require-2fa-admin-label"><?php esc_html_e('Require 2FA for all administrators', 'wordfence-2fa'); ?></strong>
					</li>
					<li class="wfls-option-subtitle"><?php esc_html_e('Note: This setting requires at least one administrator to have 2FA active. On multisite, this option applies only to super admins.', 'wordfence-2fa'); ?></li>
				</ul>
			</li>
		</ul>
	</li>
	<li>
		<ul class="wfls-option wfls-padding-no-top">
			<li class="wfls-option-spacer"></li>
			<li id="wfls-require-2fa-grace-period" class="wfls-flex-horizontal wfls-option-date">
				<div class="wfls-option-checkbox<?php echo $currentGracePeriodEnabledValue ? ' wfls-checked' : ''; ?><?php echo $currentRequireValue ? '' : ' wfls-disabled'; ?>" data-original-value="<?php echo $currentGracePeriodEnabledValue ? '1' : '0'; ?>"><i class="wfls-ion-ios-checkmark-empty" aria-hidden="true"></i></div>
				<span id="wfls-require-2fa-grace-period-label" class="wfls-padding-add-left wfls-padding-add-right"><?php esc_html_e('Grace period to require 2FA', 'wordfence'); ?> </span>
				<input type="text" name="require2FAGracePeriod" id="input-require2FAGracePeriod" class="wfls-datetime wfls-form-control" placeholder="Enabled on..." data-value="<?php echo $currentGracePeriodDateValue; ?>" data-original-value="<?php echo $currentGracePeriodDateValue; ?>"<?php echo $currentGracePeriodEnabledValue ? '' : ' disabled'; ?>>
			</li>
		</ul>
	</li>
	<li>
		<ul class="wfls-option wfls-padding-no-top">
			<li class="wfls-option-spacer"></li>
			<li class="wfls-option-spacer"></li>
			<li><a href="#" id="wfls-send-grace-period-notification" class="wfls-btn wfls-btn-sm wfls-btn-default<?php echo (\WordfenceLS\Controller_Settings::shared()->get_bool(\WordfenceLS\Controller_Settings::OPTION_REQUIRE_2FA_ADMIN) && \WordfenceLS\Controller_Settings::shared()->get_bool(\WordfenceLS\Controller_Settings::OPTION_REQUIRE_2FA_GRACE_PERIOD_ENABLED) && \WordfenceLS\Controller_Time::time() < \WordfenceLS\Controller_Settings::shared()->get_int(\WordfenceLS\Controller_Settings::OPTION_REQUIRE_2FA_GRACE_PERIOD)) ? '' : ' wfls-disabled'; ?>"><?php esc_html_e('Send Notification', 'wordfence-2fa'); ?></a></li>
		</ul>
	</li>
</ul>
<script type="application/javascript">
	(function($) {
		$(function() {
			$('#wfls-require-2fa-admin').on('keydown', function(e) {
				if (e.keyCode == 32) {
					e.preventDefault();
					e.stopPropagation();

					$(this).trigger('click');
				}
			});
			
			$('#wfls-require-2fa-admin').on('click', function(e) {
				e.preventDefault();
				e.stopPropagation();

				var optionElement = $('#wfls-option-require-2fa');
				if (optionElement.hasClass('wfls-disabled')) {
					return;
				}

				var option = optionElement.data('option');
				var value = false;
				var isActive = $(this).hasClass('wfls-checked');
				if (isActive) {
					$(this).removeClass('wfls-checked').attr('aria-checked', 'false');
					$('#wfls-require-2fa-grace-period .wfls-option-checkbox').addClass('wfls-disabled');
					$('#wfls-option-require-2fa .wfls-datetime').attr('disabled', true);
					value = optionElement.data('disabledValue');
				}
				else {
					$(this).addClass('wfls-checked').attr('aria-checked', 'true');
					$('#wfls-require-2fa-grace-period .wfls-option-checkbox').removeClass('wfls-disabled');
					if ($('#wfls-require-2fa-grace-period .wfls-option-checkbox').hasClass('wfls-checked')) {
						$('#wfls-option-require-2fa .wfls-datetime').attr('disabled', false);
					}
					value = optionElement.data('enabledValue');
				}

				var originalValue = optionElement.data('originalValue');
				if (originalValue == value) {
					delete WFLS.pendingChanges[option];
				}
				else {
					WFLS.pendingChanges[option] = value;
				}

				$(optionElement).trigger('change', [false]);
				WFLS.updatePendingChanges();
			});

			$('#wfls-require-2fa-admin-label, #wfls-require-2fa-grace-period-label').on('click', function(e) {
				var links = $(this).find('a');
				var buffer = 10;
				for (var i = 0; i < links.length; i++) {
					var t = $(links[i]).offset().top;
					var l = $(links[i]).offset().left;
					var b = t + $(links[i]).height();
					var r = l + $(links[i]).width();

					if (e.pageX > l - buffer && e.pageX < r + buffer && e.pageY > t - buffer && e.pageY < b + buffer) {
						return;
					}
				}
				$(this).closest('.wfls-option').find('.wfls-option-checkbox').trigger('click');
			}).css('cursor', 'pointer');
			
			$('#wfls-option-require-2fa .wfls-datetime').datetimepicker({
				dateFormat: 'yy-mm-dd',
				timezone: <?php echo $timeZoneMinutes; ?>,
				showTime: false,
				showTimepicker: false,
				showMonthAfterYear: true
			}).each(function() {
				var el = $(this);
				if (el.attr('data-value')) {
					el.datetimepicker('setDate', new Date(el.attr('data-value') * 1000));
				}
			}).on('change', function() {
				var value = Math.floor($(this).datetimepicker('getDate').getTime() / 1000);
				var originalValue = $('#input-require2FAGracePeriod').data('originalValue');
				if (originalValue == value) {
					delete WFLS.pendingChanges['<?php echo esc_js($gracePeriodDateOptionName); ?>'];
				}
				else {
					WFLS.pendingChanges['<?php echo esc_js($gracePeriodDateOptionName); ?>'] = $(this).val();
				}
				WFLS.updatePendingChanges();
			});

			$('#wfls-require-2fa-grace-period .wfls-option-checkbox').on('click', function(e) {
				e.preventDefault();
				e.stopPropagation();

				if ($(this).hasClass('wfls-disabled')) {
					return;
				}

				var originalValue = $(this).data('originalValue');
				var value = originalValue;
				var isActive = $(this).hasClass('wfls-checked');
				if (isActive) {
					$(this).removeClass('wfls-checked');
					$('#wfls-option-require-2fa .wfls-datetime').attr('disabled', true);
					value = 0;
				}
				else {
					$(this).addClass('wfls-checked');
					$('#wfls-option-require-2fa .wfls-datetime').attr('disabled', false);
					value = 1;

					if (!$('#input-require2FAGracePeriod').val()) {
						var date = new Date();
						date.setDate(date.getDate() + 7);
						$('#input-require2FAGracePeriod').datetimepicker('setDate', date);
					}
				}

				if (originalValue == value) {
					delete WFLS.pendingChanges['<?php echo esc_js($gracePeriodEnabledOptionName); ?>'];
				}
				else {
					WFLS.pendingChanges['<?php echo esc_js($gracePeriodEnabledOptionName); ?>'] = value;
				}

				$('#wfls-option-require-2fa .wfls-datetime').trigger('change');

				WFLS.updatePendingChanges();
			});

			$(window).on('wflsOptionsReset', function() {
				$('#wfls-option-require-2fa').each(function() {
					var enabledValue = $(this).data('enabledValue');
					var disabledValue = $(this).data('disabledValue');
					var originalValue = $(this).data('originalValue');
					if (enabledValue == originalValue) {
						$(this).find('#wfls-require-2fa-admin.wfls-option-checkbox').addClass('wfls-checked').attr('aria-checked', 'true');
					}
					else {
						$(this).find('#wfls-require-2fa-admin.wfls-option-checkbox').removeClass('wfls-checked').attr('aria-checked', 'false');
					}
					$(this).trigger('change', [true]);
				});
				$('#wfls-require-2fa-grace-period .wfls-option-checkbox').each(function() {
					var originalValue = $(this).data('originalValue');
					$(this).toggleClass('wfls-checked', !!originalValue);
					$('#wfls-option-require-2fa .wfls-datetime').attr('disabled', !originalValue);
				});
				$('.wfls-datetime').each(function() {
					var el = $(this);
					if (el.attr('data-value')) {
						el.datetimepicker('setDate', new Date(el.attr('data-value') * 1000));
					}
					else {
						el.val('');
					}
				});
			});

			$('#wfls-send-grace-period-notification').on('click', function(e) {
				e.preventDefault();
				e.stopPropagation();
				
				WFLS.ajax('wordfence_ls_send_grace_period_notification', [], 
					function(response) {
						if (response.error) {
							WFLS.panelModal((WFLS.screenSize(500) ? '300px' : '400px'), '<?php echo \WordfenceLS\Text\Model_JavaScript::esc_js(__('Error Sending Notification', 'wordfence-2fa')); ?>', response.error);
						}
						else {
							WFLS.panelModal((WFLS.screenSize(500) ? '300px' : '400px'), '<?php echo \WordfenceLS\Text\Model_JavaScript::esc_js(__('Notification Sent', 'wordfence-2fa')); ?>', response.confirmation);
						}
					},
					function (error) {
						WFLS.panelModal((WFLS.screenSize(500) ? '300px' : '400px'), '<?php echo \WordfenceLS\Text\Model_JavaScript::esc_js(__('Error Sending Notification', 'wordfence-2fa')); ?>', '<?php echo \WordfenceLS\Text\Model_JavaScript::esc_js(__('An error was encountered while trying to send the notification. Please try again.', 'wordfence-2fa')); ?>');
					});
			});
		});
	})(jQuery);
</script>