<?php if (wfOnboardingController::shouldShowNewTour(wfOnboardingController::TOUR_LOGIN_SECURITY)): ?>
	<script type="application/javascript">
		(function($) {
			$(function() {
				WFAD.tour1 = function() {
					$('#wfls-tab-manage a').trigger('click');
					WFAD.tour('wfNewTour1', 'wfls-tab-manage', 'top', 'left', null, WFAD.tour2);
				};
				WFAD.tour2 = function() {
					$('#wfls-tab-settings a').trigger('click');
					WFAD.tour('wfNewTour2', 'wfls-option-whitelisted', 'bottom', 'right', WFAD.tour1, WFAD.tour3);
				};
				WFAD.tour3 = function() {
					$('#wfls-tab-settings a').trigger('click');
					WFAD.tour('wfNewTour3', 'wfls-enable-auth-captcha', 'bottom', 'left', WFAD.tour2, WFAD.tourComplete);
				};
				WFAD.tourComplete = function() { WFAD.tourFinish('<?php echo esc_attr(wfOnboardingController::TOUR_LOGIN_SECURITY); ?>'); };
				
				<?php if (wfOnboardingController::shouldShowNewTour(wfOnboardingController::TOUR_LOGIN_SECURITY)): ?>
				if (!WFAD.isSmallScreen) { WFAD.tour1(); }
				<?php endif; ?>
			});
		})(jQuery);
	</script>
	
	<script type="text/x-jquery-template" id="wfNewTour1">
		<div>
			<h3><?php esc_html_e('Introducing the New Wordfence 2FA', 'wordfence'); ?></h3>
			<p><?php esc_html_e('We are excited to announce the release of a completely rebuilt two-factor authentication (2FA) feature within Wordfence. 2FA is an important layer of security that protects you from password guessing and credential stuffing attacks. Previously a Premium-only feature, it is now available for sites running the free version of Wordfence. You are now able to enable 2FA for any role, we’ve added a number of important security features, and we’ve significantly improved the admin interface.', 'wordfence'); ?></p>
			<p><a href="<?php echo wfSupportController::esc_supportURL(wfSupportController::ITEM_MODULE_LOGIN_SECURITY_2FA); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Learn More', 'wordfence'); ?></a></p>
			<div class="wf-pointer-footer">
				<ul class="wf-tour-pagination">
					<li class="wf-active">&bullet;</li>
					<li>&bullet;</li>
					<li>&bullet;</li>
				</ul>
				<div id="wf-tour-continue"><a href="#" class="wf-onboarding-btn wf-onboarding-btn-primary"><?php esc_html_e('Next', 'wordfence'); ?></a></div>
			</div>
			<div id="wf-tour-close"><a href="#"><i class="wf-fa wf-fa-times-circle" aria-hidden="true"></i></a></div>
		</div>
	</script>
	<script type="text/x-jquery-template" id="wfNewTour2">
		<div>
			<h3><?php esc_html_e('Individual Allowlisting', 'wordfence'); ?></h3>
			<p><?php esc_html_e('Two-factor authentication now has its own IP allowlist. If necessary, you can allow specific IP addresses or ranges to skip 2FA when logging in.', 'wordfence'); ?></p>
			<div class="wf-pointer-footer">
				<ul class="wf-tour-pagination">
					<li>&bullet;</li>
					<li class="wf-active">&bullet;</li>
					<li>&bullet;</li>
				</ul>
				<div id="wf-tour-previous"><a href="#" class="wf-onboarding-btn wf-onboarding-btn-default"><?php esc_html_e('Previous', 'wordfence'); ?></a></div>
				<div id="wf-tour-continue"><a href="#" class="wf-onboarding-btn wf-onboarding-btn-primary"><?php esc_html_e('Next', 'wordfence'); ?></a></div>
			</div>
			<div id="wf-tour-close"><a href="#"><i class="wf-fa wf-fa-times-circle" aria-hidden="true"></i></a></div>
		</div>
	</script>
	<script type="text/x-jquery-template" id="wfNewTour3">
		<div>
			<h3><?php esc_html_e('New Login Page Captcha Feature', 'wordfence'); ?></h3>
			<p><?php esc_html_e('Wordfence now includes the option to enable Google reCaptcha v3 on your WordPress login and registration pages. This adds a powerful new layer of protection against password guessing and credential stuffing attacks from bots without slowing down real users.', 'wordfence'); ?></p>
			<p><a href="<?php echo wfSupportController::esc_supportURL(wfSupportController::ITEM_MODULE_LOGIN_SECURITY_CAPTCHA); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Learn More', 'wordfence'); ?></a></p>
			<div class="wf-pointer-footer">
				<ul class="wf-tour-pagination">
					<li>&bullet;</li>
					<li>&bullet;</li>
					<li class="wf-active">&bullet;</li>
				</ul>
				<div id="wf-tour-previous"><a href="#" class="wf-onboarding-btn wf-onboarding-btn-default"><?php esc_html_e('Previous', 'wordfence'); ?></a></div>
				<div id="wf-tour-continue"><a href="#" class="wf-onboarding-btn wf-onboarding-btn-primary"><?php esc_html_e('Done', 'wordfence'); ?></a></div>
			</div>
			<div id="wf-tour-close"><a href="#"><i class="wf-fa wf-fa-times-circle" aria-hidden="true"></i></a></div>
		</div>
	</script>
<?php endif; ?>