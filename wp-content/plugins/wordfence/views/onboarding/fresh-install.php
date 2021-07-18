<?php
if (!defined('WORDFENCE_VERSION')) { exit; }
/**
 * Presents the fresh install modal.
 */
?>
<div id="wf-onboarding-fresh-install" class="wf-onboarding-modal">
	<div id="wf-onboarding-fresh-install-1" class="wf-onboarding-modal-content"<?php if (wfConfig::get('onboardingAttempt1') == wfOnboardingController::ONBOARDING_FIRST_EMAILS) { echo ' style="display: none;"'; } ?>>
		<div class="wf-onboarding-logo"><img src="<?php echo esc_attr(wfUtils::getBaseURL() . 'images/wf-horizontal.svg'); ?>" alt="<?php esc_html_e('Wordfence - Securing your WordPress Website', 'wordfence'); ?>"></div>
		<h3><?php printf(/* translators: Wordfence version. */ esc_html__('You have successfully installed Wordfence %s', 'wordfence'), WORDFENCE_VERSION); ?></h3>
		<h4><?php esc_html_e('Please tell us where Wordfence should send you security alerts for your website:', 'wordfence'); ?></h4>
		<input type="text" id="wf-onboarding-alerts" placeholder="you@example.com" value="<?php echo esc_attr(implode(',', wfConfig::getAlertEmails())); ?>">
		<p id="wf-onboarding-alerts-disclaimer"><?php esc_html_e('We do not use this email address for any other purpose unless you opt-in to receive other mailings. You can turn off alerts in the options.', 'wordfence'); ?></p>
		<div id="wf-onboarding-subscribe">
			<label for="wf-onboarding-email-list"><?php esc_html_e('Would you also like to join our WordPress security mailing list to receive WordPress security alerts and Wordfence news?', 'wordfence'); ?></label>
			<div id="wf-onboarding-subscribe-controls">
				<ul id="wf-onboarding-email-list" class="wf-switch">
					<li data-option-value="1"><?php esc_html_e('Yes', 'wordfence'); ?></li>
					<li data-option-value="0"><?php esc_html_e('No', 'wordfence'); ?></li>
				</ul>
				<p><?php esc_html_e('(Choose One)', 'wordfence'); ?></p>
			</div>
		</div>
		<div id="wf-onboarding-footer">
			<ul>
				<li>
					<input type="checkbox" class="wf-option-checkbox wf-small" id="wf-onboarding-agree"> <label for="wf-onboarding-agree"><?php echo wp_kses(__('By checking this box, I agree to the Wordfence <a href="https://www.wordfence.com/terms-of-use/" target="_blank" rel="noopener noreferrer">terms</a> and <a href="https://www.wordfence.com/privacy-policy/" target="_blank" rel="noopener noreferrer">privacy policy</a>', 'wordfence'), array('a'=>array('href'=>array(), 'target'=>array(), 'rel'=>array()))); ?></label>
					<p class="wf-gdpr-dpa"><?php echo wp_kses(sprintf(/* translators: Support URL. */ __('If you qualify as a data controller under the GDPR and need a data processing agreement, <a href="%s" target="_blank" rel="noopener noreferrer">click here</a>.', 'wordfence'), wfSupportController::esc_supportURL(wfSupportController::ITEM_GDPR_DPA)), array('a'=>array('href'=>array(), 'target'=>array(), 'rel'=>array()))); ?></p>
				</li>
				<li><a href="#" class="wf-onboarding-btn wf-onboarding-btn-primary wf-disabled" id="wf-onboarding-continue"><?php esc_html_e('Continue', 'wordfence'); ?></a></li>
			</ul>
		</div>
	</div>
	<div id="wf-onboarding-fresh-install-2" class="wf-onboarding-modal-content"<?php if (wfConfig::get('onboardingAttempt1') != wfOnboardingController::ONBOARDING_FIRST_EMAILS) { echo ' style="display: none;"'; } ?>>
		<div class="wf-onboarding-logo"><img src="<?php echo esc_attr(wfUtils::getBaseURL() . 'images/wf-horizontal.svg'); ?>" alt="<?php esc_html_e('Wordfence - Securing your WordPress Website', 'wordfence'); ?>"></div>
		<h3><?php esc_html_e('Enter Premium License Key', 'wordfence'); ?></h3>
		<p><?php esc_html_e('Enter your premium license key to enable real-time protection for your website.', 'wordfence'); ?></p>
		<div id="wf-onboarding-license"><input type="text" placeholder="<?php esc_html_e('Enter Premium Key', 'wordfence'); ?>"><a href="#" class="wf-onboarding-btn wf-onboarding-btn-primary wf-disabled" id="wf-onboarding-license-install"><?php esc_html_e('Install', 'wordfence'); ?></a></div>
		<div id="wf-onboarding-or"><span><?php esc_html_e('or', 'wordfence') ?></span></div>
		<p><?php esc_html_e('If you don\'t have one, you can purchase one now.', 'wordfence'); ?></p>
		<div id="wf-onboarding-license-footer">
			<ul>
				<li><a href="https://www.wordfence.com/gnl1onboardingOverlayGet/wordfence-signup/#premium-order-form" class="wf-onboarding-btn wf-onboarding-btn-primary" id="wf-onboarding-get" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Upgrade to Premium', 'wordfence'); ?></a></li>
				<li><a href="https://www.wordfence.com/gnl1onboardingOverlayLearn/wordfence-signup/" class="wf-onboarding-btn wf-onboarding-btn-default" id="wf-onboarding-learn" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Learn More', 'wordfence'); ?></a></li>
				<li><a href="#" id="wf-onboarding-no-thanks"><?php esc_html_e('No Thanks', 'wordfence'); ?></a></li>
			</ul>
		</div>
	</div>
</div>
<script type="application/javascript">
	(function($) {
		$(function() {
			setTimeout(function() {
				$('#wf-onboarding-subscribe-controls > p').show();
			}, 30000);
			
			$('#wf-onboarding-subscribe .wf-switch > li').each(function(index, element) {
				$(element).on('click', function(e) {
					e.preventDefault();
					e.stopPropagation();

					var control = $(this).closest('.wf-switch');
					control.find('li').removeClass('wf-active');
					$(this).addClass('wf-active');

					$('#wf-onboarding-continue').toggleClass('wf-disabled', wordfenceExt.parseEmails($('#wf-onboarding-alerts').val()).length == 0 || !($('#wf-onboarding-agree').is(':checked')) || $('#wf-onboarding-subscribe .wf-switch > li.wf-active').length == 0);
				});
			});
			
			$('#wf-onboarding-agree').on('change', function() {
				$('#wf-onboarding-continue').toggleClass('wf-disabled', wordfenceExt.parseEmails($('#wf-onboarding-alerts').val()).length == 0 || !($('#wf-onboarding-agree').is(':checked')) || $('#wf-onboarding-subscribe .wf-switch > li.wf-active').length == 0);
			});
			
			$('#wf-onboarding-alerts').on('change paste keyup', function() {
				setTimeout(function() {
					$('#wf-onboarding-continue').toggleClass('wf-disabled', wordfenceExt.parseEmails($('#wf-onboarding-alerts').val()).length == 0 || !($('#wf-onboarding-agree').is(':checked')) || $('#wf-onboarding-subscribe .wf-switch > li.wf-active').length == 0);
				}, 100);
			}).trigger('change');
			
			$('#wf-onboarding-continue').on('click', function(e) {
				e.preventDefault();
				e.stopPropagation();
				
				var touppAgreed = !!$('#wf-onboarding-agree').is(':checked');
				if (!touppAgreed) {
					return;
				}
				
				var emails = wordfenceExt.parseEmails($('#wf-onboarding-alerts').val());
				if (emails.length > 0) {
					var subscribe = !!parseInt($('#wf-onboarding-subscribe .wf-switch > li.wf-active').data('optionValue'));
					wordfenceExt.onboardingProcessEmails(emails, subscribe, touppAgreed);
					
					<?php if (wfConfig::get('isPaid')): ?>
					$('#wf-onboarding-dismiss').trigger('click');
					wordfenceExt.setOption('onboardingAttempt1', '<?php echo esc_attr(wfOnboardingController::ONBOARDING_FIRST_LICENSE); ?>');
					$('#wf-onboarding-plugin-header').slideUp();
					
					var html = '<div class="wf-modal wf-modal-success"><div class="wf-model-success-wrapper"><div class="wf-modal-header"><div class="wf-modal-header-content"><div class="wf-modal-title"><?php esc_html_e('Configuration Complete', 'wordfence'); ?></div></div></div><div class="wf-modal-content"><?php esc_html_e('Congratulations! Configuration is complete and Wordfence Premium is active on your website.', 'wordfence'); ?></div></div><div class="wf-modal-footer"><ul class="wf-onboarding-flex-horizontal wf-onboarding-flex-align-right wf-onboarding-full-width"><li><a href="<?php echo esc_url(network_admin_url('admin.php?page=Wordfence')); ?>" class="wf-onboarding-btn wf-onboarding-btn-primary"><?php esc_html_e('Go To Dashboard', 'wordfence'); ?></a></li><li class="wf-padding-add-left-small"><a href="#" class="wf-onboarding-btn wf-onboarding-btn-default" onclick="jQuery.wfcolorbox.close(); return false;"><?php esc_html_e('Close', 'wordfence'); ?></a></li></ul></div></div>';
					$.wfcolorbox({
						width: (wordfenceExt.isSmallScreen ? '300px' : '500px'),
						html: html,
						overlayClose: true,
						closeButton: false,
						className: 'wf-modal'
					});
					<?php else: ?>
					wordfenceExt.setOption('onboardingAttempt1', '<?php echo esc_attr(wfOnboardingController::ONBOARDING_FIRST_EMAILS); ?>');
					$('#wf-onboarding-fresh-install-1').fadeOut(400, function() {
						$('#wf-onboarding-fresh-install-2').fadeIn();
					});
					<?php endif; ?>
				}
			});

			$('#wf-onboarding-license input').on('change paste keyup', function() {
				setTimeout(function() {
					$('#wf-onboarding-license-install').toggleClass('wf-disabled', $('#wf-onboarding-license input').val().length == 0);
				}, 100);
			}).trigger('change');
			
			$('#wf-onboarding-license-install').on('click', function(e) {
				e.preventDefault();
				e.stopPropagation();
				
				var license = $('#wf-onboarding-license input').val();
				wordfenceExt.onboardingInstallLicense(license, 
					function(res) { //Success
						if (res.isPaid) {
							$('#wf-onboarding-dismiss').trigger('click');
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
						var html = '<div class="wf-modal"><div class="wf-modal-header"><div class="wf-modal-header-content"><div class="wf-modal-title"><strong><?php esc_html_e('Error Installing License', 'wordfence'); ?></strong></div></div></div><div class="wf-modal-content">' + res.error + '</div><div class="wf-modal-footer"><ul class="wf-onboarding-flex-horizontal wf-onboarding-flex-align-right wf-onboarding-full-width"><li><a href="#" class="wf-onboarding-btn wf-onboarding-btn-primary" onclick="jQuery.wfcolorbox.close(); return false;"><?php esc_html_e('Close', 'wordfence'); ?></a></li></ul></div></div>';
						$.wfcolorbox({
							width: (wordfenceExt.isSmallScreen ? '300px' : '500px'),
							html: html,
							overlayClose: true, 
							closeButton: false, 
							className: 'wf-modal'
						});
					});
			});
			
			$('#wf-onboarding-no-thanks').on('click', function(e) {
				e.preventDefault();
				e.stopPropagation();

				$('#wf-onboarding-dismiss').trigger('click');
			});
			
			$('#wf-onboarding-fresh-install').on('click', function(e) {
				e.stopPropagation();
			});

			$(window).on('wfOnboardingDismiss', function() {
				if ($('#wf-onboarding-fresh-install-1').is(':visible')) {
					wordfenceExt.setOption('onboardingAttempt1', '<?php echo esc_attr(wfOnboardingController::ONBOARDING_FIRST_SKIPPED); ?>');
				}
				else {
					wordfenceExt.setOption('onboardingAttempt1', '<?php echo esc_attr(wfOnboardingController::ONBOARDING_FIRST_LICENSE); ?>');
					$('#wf-onboarding-plugin-header').slideUp();
				}
			});
		});
	})(jQuery);
</script>