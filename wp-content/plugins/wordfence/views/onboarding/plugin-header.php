<?php
if (!defined('WORDFENCE_VERSION')) { exit; }
/**
 * Presents the fresh install plugin header.
 */
?>
<div id="wf-onboarding-plugin-header">
	<div id="wf-onboarding-plugin-header-header">
		<div id="wf-onboarding-plugin-header-title"><?php esc_html_e('Please Complete Wordfence Installation', 'wordfence'); ?></div>
		<div id="wf-onboarding-plugin-header-accessory"><a href="#" id="wf-onboarding-plugin-header-dismiss">&times;</a></div>
	</div>
	<div id="wf-onboarding-plugin-header-content">
		<ul>
			<li id="wf-onboarding-plugin-header-stage">
				<ul>
					<li id="wf-onboarding-plugin-header-stage-label-1" class="wf-onboarding-plugin-header-stage-label<?php if (wfConfig::get('onboardingAttempt2') != wfOnboardingController::ONBOARDING_SECOND_EMAILS) { echo ' wf-active'; } else { echo ' wf-complete'; } ?>">
						<ul>
							<li><?php esc_html_e('Admin Contact Info', 'wordfence'); ?></li>
							<li>&#10003;</li>
						</ul>
					</li>
					<li id="wf-onboarding-plugin-header-stage-label-2" class="wf-onboarding-plugin-header-stage-label<?php if (wfConfig::get('onboardingAttempt2') == wfOnboardingController::ONBOARDING_SECOND_EMAILS) { echo ' wf-active'; } ?>">
						<ul>
							<li><?php esc_html_e('Activate Premium', 'wordfence'); ?></li>
							<li>&#10003;</li>
						</ul>
					</li>
				</ul>
			</li>
			<li id="wf-onboarding-plugin-header-stage-content">
				<div id="wf-onboarding-plugin-header-stage-content-1"<?php if (wfConfig::get('onboardingAttempt2') == wfOnboardingController::ONBOARDING_FIRST_EMAILS) { echo ' style="display: none;"'; } ?>>
					<h4><?php esc_html_e('Please tell us where Wordfence should send you security alerts for your website:', 'wordfence'); ?></h4>
					<input type="text" id="wf-onboarding2-alerts" placeholder="you@example.com" value="<?php echo esc_attr(implode(',', wfConfig::getAlertEmails())); ?>">
					<p id="wf-onboarding-alerts-disclaimer"><?php esc_html_e('We do not use this email address for any other purpose unless you opt-in to receive other mailings. You can turn off alerts in the options.', 'wordfence'); ?></p>
					<div id="wf-onboarding2-subscribe">
						<label for="wf-onboarding2-email-list"><?php esc_html_e('Would you also like to join our WordPress security mailing list to receive WordPress security alerts and Wordfence news?', 'wordfence'); ?></label>
						<div id="wf-onboarding2-subscribe-controls">
							<ul id="wf-onboarding2-email-list" class="wf-switch">
								<li data-option-value="1"><?php esc_html_e('Yes', 'wordfence'); ?></li>
								<li data-option-value="0"><?php esc_html_e('No', 'wordfence'); ?></li>
							</ul>
							<p><?php esc_html_e('(Choose One)', 'wordfence'); ?></p>
						</div>
					</div>
				</div>
				<div id="wf-onboarding-plugin-header-stage-content-2"<?php if (wfConfig::get('onboardingAttempt2') != wfOnboardingController::ONBOARDING_FIRST_EMAILS) { echo ' style="display: none;"'; } ?>>
					<h4><?php esc_html_e('Enter Premium License Key', 'wordfence'); ?></h4>
					<p><?php esc_html_e('Enter your premium license key to enable real-time protection for your website.', 'wordfence'); ?></p>
					<div id="wf-onboarding2-license"><input type="text" placeholder="<?php esc_html_e('Enter Premium Key', 'wordfence'); ?>"><a href="#" class="wf-onboarding-btn wf-onboarding-btn-primary" id="wf-onboarding2-license-install"><?php esc_html_e('Install', 'wordfence'); ?></a></div>
				</div>
			</li>
			<li id="wf-onboarding-plugin-header-stage-image"></li>
		</ul>
	</div>
	<div id="wf-onboarding-plugin-header-footer">
		<ul id="wf-onboarding-plugin-header-footer-1"<?php if (wfConfig::get('onboardingAttempt2') == wfOnboardingController::ONBOARDING_FIRST_EMAILS) { echo ' style="display: none;"'; } ?>>
			<li>
				<input type="checkbox" class="wf-option-checkbox wf-small" id="wf-onboarding2-agree"> <label for="wf-onboarding2-agree"><?php echo wp_kses(__('By checking this box, I agree to the Wordfence <a href="https://www.wordfence.com/terms-of-use/" target="_blank" rel="noopener noreferrer">terms</a> and <a href="https://www.wordfence.com/privacy-policy/" target="_blank" rel="noopener noreferrer">privacy policy</a>', 'wordfence'), array('a'=>array('href'=>array(), 'target'=>array(), 'rel'=>array()))); ?></label>
				<p class="wf-gdpr-dpa"><?php echo wp_kses(sprintf(__('If you qualify as a data controller under the GDPR and need a data processing agreement, <a href="%s" target="_blank" rel="noopener noreferrer">click here</a>.', 'wordfence'), wfSupportController::esc_supportURL(wfSupportController::ITEM_GDPR_DPA)), array('a'=>array('href'=>array(), 'target'=>array(), 'rel'=>array()))); ?></p>
			</li>
			<li><a href="#" class="wf-onboarding-btn wf-onboarding-btn-default wf-disabled" id="wf-onboarding2-continue"><?php esc_html_e('Continue', 'wordfence'); ?></a></li>
		</ul>
		<ul id="wf-onboarding-plugin-header-footer-2"<?php if (wfConfig::get('onboardingAttempt2') != wfOnboardingController::ONBOARDING_FIRST_EMAILS) { echo ' style="display: none;"'; } ?>>
			<li><?php esc_html_e('If you don\'t have one, you can purchase one now.', 'wordfence'); ?></li>
			<li><a href="https://www.wordfence.com/gnl1onboardingSecondChanceGet/wordfence-signup/#premium-order-form" class="wf-onboarding-btn wf-onboarding-btn-primary" id="wf-onboarding2-get" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Upgrade to Premium', 'wordfence'); ?></a></li>
			<li><a href="https://www.wordfence.com/gnl1onboardingSecondChanceLearn/wordfence-signup/" class="wf-onboarding-btn wf-onboarding-btn-default" id="wf-onboarding2-learn" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Learn More', 'wordfence'); ?></a></li>
			<li><a href="#" id="wf-onboarding2-no-thanks"><?php esc_html_e('No Thanks', 'wordfence'); ?></a></li>
		</ul>
	</div>
</div>
<script type="application/javascript">
	(function($) {
		$(function() {
			$('#wf-onboarding-plugin-header-dismiss').on('click', function(e) {
				e.preventDefault();
				e.stopPropagation();

				$(window).trigger('wfOnboardingDismiss2');
				$('#wf-onboarding-plugin-header').slideUp(400, function() {
					$('#wf-onboarding-plugin-overlay').remove();
				});

				if ($('#wf-onboarding-plugin-header-stage-content-1').is(':visible')) {
					wordfenceExt.setOption('onboardingAttempt2', '<?php echo esc_attr(wfOnboardingController::ONBOARDING_SECOND_SKIPPED); ?>');
				}
				else {
					wordfenceExt.setOption('onboardingAttempt2', '<?php echo esc_attr(wfOnboardingController::ONBOARDING_SECOND_LICENSE); ?>');
				}
			});

			setTimeout(function() {
				$('#wf-onboarding2-subscribe-controls > p').show();
			}, 30000);

			$('#wf-onboarding2-subscribe .wf-switch > li').each(function(index, element) {
				$(element).on('click', function(e) {
					e.preventDefault();
					e.stopPropagation();

					var control = $(this).closest('.wf-switch');
					control.find('li').removeClass('wf-active');
					$(this).addClass('wf-active');

					$('#wf-onboarding2-continue').toggleClass('wf-disabled', wordfenceExt.parseEmails($('#wf-onboarding2-alerts').val()).length == 0 || !($('#wf-onboarding2-agree').is(':checked')) || $('#wf-onboarding2-subscribe .wf-switch > li.wf-active').length == 0);
				});
			});

			$('#wf-onboarding2-agree').on('change', function() {
				$('#wf-onboarding2-continue').toggleClass('wf-disabled', wordfenceExt.parseEmails($('#wf-onboarding2-alerts').val()).length == 0 || !($('#wf-onboarding2-agree').is(':checked')) || $('#wf-onboarding2-subscribe .wf-switch > li.wf-active').length == 0);
			});

			$('#wf-onboarding2-alerts').on('change paste keyup', function() {
				setTimeout(function() {
					$('#wf-onboarding2-continue').toggleClass('wf-disabled', wordfenceExt.parseEmails($('#wf-onboarding2-alerts').val()).length == 0 || !($('#wf-onboarding2-agree').is(':checked')) || $('#wf-onboarding2-subscribe .wf-switch > li.wf-active').length == 0);
				}, 100);
			}).trigger('change');

			$('#wf-onboarding2-continue').on('click', function(e) {
				e.preventDefault();
				e.stopPropagation();

				var touppAgreed = !!$('#wf-onboarding2-agree').is(':checked');
				if (!touppAgreed) {
					return;
				}

				var emails = wordfenceExt.parseEmails($('#wf-onboarding2-alerts').val());
				if (emails.length > 0) {
					var subscribe = !!parseInt($('#wf-onboarding2-subscribe .wf-switch > li.wf-active').data('optionValue'));
					wordfenceExt.onboardingProcessEmails(emails, subscribe, touppAgreed);
					
					<?php if (wfConfig::get('isPaid')): ?>
					$('#wf-onboarding-plugin-header').slideUp();
					wordfenceExt.setOption('onboardingAttempt2', '<?php echo esc_attr(wfOnboardingController::ONBOARDING_SECOND_LICENSE); ?>');
					var html = '<div class="wf-modal wf-modal-success"><div class="wf-model-success-wrapper"><div class="wf-modal-header"><div class="wf-modal-header-content"><div class="wf-modal-title"><?php esc_html_e('Configuration Complete', 'wordfence'); ?></div></div></div><div class="wf-modal-content"><?php esc_html_e('Congratulations! Configuration is complete and Wordfence Premium is active on your website.', 'wordfence'); ?></div></div><div class="wf-modal-footer"><ul class="wf-onboarding-flex-horizontal wf-onboarding-flex-align-right wf-onboarding-full-width"><li><a href="<?php echo esc_url(network_admin_url('admin.php?page=Wordfence')); ?>" class="wf-onboarding-btn wf-onboarding-btn-primary"><?php esc_html_e('Go To Dashboard', 'wordfence'); ?></a></li><li class="wf-padding-add-left-small"><a href="#" class="wf-onboarding-btn wf-onboarding-btn-default" onclick="jQuery.wfcolorbox.close(); return false;"><?php esc_html_e('Close', 'wordfence'); ?></a></li></ul></div></div>';
					$.wfcolorbox({
						width: (wordfenceExt.isSmallScreen ? '300px' : '500px'),
						html: html,
						overlayClose: true,
						closeButton: false,
						className: 'wf-modal'
					});
					<?php else: ?>
					wordfenceExt.setOption('onboardingAttempt2', '<?php echo esc_attr(wfOnboardingController::ONBOARDING_SECOND_EMAILS); ?>');
					$('#wf-onboarding-plugin-header-stage-label-1').removeClass('wf-active');
					$('#wf-onboarding-plugin-header-stage-label-1').addClass('wf-complete');
					$('#wf-onboarding-plugin-header-stage-label-2').addClass('wf-active');
					$('#wf-onboarding-plugin-header-stage-content-1').fadeOut(400, function() {
						$('#wf-onboarding-plugin-header-stage-content-2, #wf-onboarding-plugin-header-footer-2').fadeIn();
					});
					$('#wf-onboarding-plugin-header-footer-1').fadeOut(400);
					<?php endif; ?>
				}
			});

			$('#wf-onboarding2-license-install').on('click', function(e) {
				e.preventDefault();
				e.stopPropagation();

				var license = $('#wf-onboarding2-license input').val();
				wordfenceExt.onboardingInstallLicense(license,
					function(res) { //Success
						if (res.isPaid) {
							$('#wf-onboarding-plugin-header').slideUp();
							wordfenceExt.setOption('onboardingAttempt2', '<?php echo esc_attr(wfOnboardingController::ONBOARDING_SECOND_LICENSE); ?>');

							var html = '<div class="wf-modal wf-modal-success"><div class="wf-model-success-wrapper"><div class="wf-modal-header"><div class="wf-modal-header-content"><div class="wf-modal-title"><?php esc_html_e('Premium License Installed', 'wordfence'); ?></div></div></div><div class="wf-modal-content"><?php esc_html_e('Congratulations! Wordfence Premium is now active on your website. Please note that some Premium features are not enabled by default.', 'wordfence'); ?></div></div><div class="wf-modal-footer"><ul class="wf-onboarding-flex-horizontal wf-onboarding-flex-align-right wf-onboarding-full-width"><li><a href="<?php echo esc_url(network_admin_url('admin.php?page=Wordfence')); ?>" class="wf-onboarding-btn wf-onboarding-btn-primary"><?php esc_html_e('Go To Dashboard', 'wordfence'); ?></a></li><li class="wf-padding-add-left-small"><a href="#" class="wf-onboarding-btn wf-onboarding-btn-default" onclick="jQuery.wfcolorbox.close(); return false;"><?php esc_html_e('Close', 'wordfence'); ?></a></li></ul></div></div>';
							$.wfcolorbox({
								width: (wordfenceExt.isSmallScreen ? '300px' : '500px'),
								html: html,
								overlayClose: true,
								closeButton: false,
								className: 'wf-modal'
							});
							<?php
							//Congratulations! Wordfence Premium is now active on your website. Please note that some Premium features are not enabled by default. Read this brief article to learn more about <a href="#todo" target="_blank" rel="noopener noreferrer">getting the most out of Wordfence Premium</a>.	
							?>
						}
						else { //Unlikely to happen but possible
							var html = '<div class="wf-modal"><div class="wf-modal-header"><div class="wf-modal-header-content"><div class="wf-modal-title"><strong><?php esc_html_e('Free License Installed', 'wordfence'); ?></strong></div></div></div><div class="wf-modal-content"><?php esc_html_e('Free License Installed', 'wordfence'); ?></div><div class="wf-modal-footer"><ul class="wf-onboarding-flex-horizontal wf-onboarding-flex-align-right wf-onboarding-full-width"><li><a href="<?php echo esc_url(network_admin_url('admin.php?page=Wordfence')); ?>" class="wf-onboarding-btn wf-onboarding-btn-primary"><?php esc_html_e('Go To Dashboard', 'wordfence'); ?></a></li><li class="wf-padding-add-left-small"><a href="#" class="wf-onboarding-btn wf-onboarding-btn-default" onclick="jQuery.wfcolorbox.close(); return false;"><?php esc_html_e('Close', 'wordfence'); ?></a></li></ul></div></div>';
							$.wfcolorbox({
								width: (wordfenceExt.isSmallScreen ? '300px' : '500px'),
								html: html,
								overlayClose: true,
								closeButton: false,
								className: 'wf-modal'
							});
						}
					},
					function(res) { //Error
						var html = '<div class="wf-modal"><div class="wf-modal-header"><div class="wf-modal-header-content"><div class="wf-modal-title"><strong>Error Installing License</strong></div></div></div><div class="wf-modal-content">' + res.error + '</div><div class="wf-modal-footer"><ul class="wf-onboarding-flex-horizontal wf-onboarding-flex-align-right wf-onboarding-full-width"><li><a href="#" class="wf-onboarding-btn wf-onboarding-btn-primary" onclick="jQuery.wfcolorbox.close(); return false;">Close</a></li></ul></div></div>';
						$.wfcolorbox({
							width: (wordfenceExt.isSmallScreen ? '300px' : '500px'),
							html: html,
							overlayClose: true,
							closeButton: false,
							className: 'wf-modal'
						});
					});
			});

			$('#wf-onboarding2-no-thanks').on('click', function(e) {
				e.preventDefault();
				e.stopPropagation();

				wordfenceExt.setOption('onboardingAttempt2', '<?php echo esc_attr(wfOnboardingController::ONBOARDING_SECOND_LICENSE); ?>');
				$('#wf-onboarding-plugin-header').slideUp();
			});
		});
	})(jQuery);
</script>