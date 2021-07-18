<?php
if (!defined('WORDFENCE_VERSION')) { exit; }
if (wfOnboardingController::shouldShowAttempt3()) {
	echo wfView::create('onboarding/banner')->render();
}
else if (wfConfig::get('touppPromptNeeded')) {
	echo wfView::create('gdpr/banner')->render();
}

$support = @json_decode(wfConfig::get('supportContent'), true);
?>
	<div class="wrap wordfence">
		<div class="wf-container-fluid">
			<div class="wf-row">
				<div class="wf-col-xs-12">
					<div class="wp-header-end"></div>
					<?php
					echo wfView::create('common/section-title', array(
						'title' => __('Help', 'wordfence'),
						'showIcon' => true,
					))->render();
					?>
				</div>
				<div class="wf-col-xs-12">
					<div class="wf-block wf-active">
						<div class="wf-block-content">
							<ul class="wf-block-list">
								<li>
									<ul class="wf-block-list wf-block-list-horizontal">
										<li class="wf-flex-vertical">
											<h3><?php esc_html_e('Free Support', 'wordfence'); ?></h3>
											<p class="wf-center"><?php echo wp_kses(__('Support for free customers is available via our forums page on wordpress.org. The majority of requests <strong>receive an answer within a few days.</strong>', 'wordfence'), array('strong'=>array())); ?></p>
											<p><a href="<?php echo wfSupportController::esc_supportURL(wfSupportController::ITEM_FREE); ?>" target="_blank" rel="noopener noreferrer" class="wf-btn wf-btn-default wf-btn-callout-subtle"><?php esc_html_e('Go to Support Forums', 'wordfence'); ?></a></p>
										</li>
										<li class="wf-flex-vertical">
										<?php if (wfConfig::get('isPaid')): ?>
											<h3><?php esc_html_e('Premium Support', 'wordfence'); ?></h3>
											<p class="wf-center"><?php echo wp_kses(__('Our senior support engineers <strong>respond to Premium tickets within a few hours</strong> on average and have a direct line to our QA and development teams.', 'wordfence'), array('strong'=>array())); ?></p>
											<p><a href="<?php echo wfSupportController::esc_supportURL(wfSupportController::ITEM_PREMIUM); ?>" target="_blank" rel="noopener noreferrer" class="wf-btn wf-btn-primary wf-btn-callout-subtle"><?php esc_html_e('Go to Premium Support', 'wordfence'); ?></a></p>
										<?php else: ?>
											<h3><?php esc_html_e('Upgrade Now to Access Premium Support', 'wordfence'); ?></h3>
											<p class="wf-center"><?php echo wp_kses(__('Our senior support engineers <strong>respond to Premium tickets within a few hours</strong> on average and have a direct line to our QA and development teams.', 'wordfence'), array('strong'=>array())); ?></p>
											<p><a href="https://www.wordfence.com/gnl1supportUpgrade/wordfence-signup/" target="_blank" rel="noopener noreferrer" class="wf-btn wf-btn-primary wf-btn-callout-subtle"><?php esc_html_e('Upgrade to Premium', 'wordfence'); ?></a></p>
										<?php endif; ?>
										</li>
									</ul>
								</li>
							</ul>
						</div>
					</div>
				</div>
			</div>
			<div class="wf-row">
				<div class="wf-col-xs-12">
					<div class="wf-block<?php echo (wfPersistenceController::shared()->isActive('support-gdpr') ? ' wf-active' : ''); ?>" data-persistence-key="support-gdpr">
						<div class="wf-block-header">
							<div class="wf-block-header-content">
								<div class="wf-block-title">
									<strong><?php esc_html_e('GDPR Information', 'wordfence'); ?></strong>
								</div>
								<div class="wf-block-header-action"><div class="wf-block-header-action-disclosure" role="checkbox" aria-checked="<?php echo (wfPersistenceController::shared()->isActive('support-gdpr') ? 'true' : 'false'); ?>" tabindex="0"></div></div>
							</div>
						</div>
						<div class="wf-block-content">
							<ul class="wf-block-list">
								<li>
									<ul class="wf-option wf-option-static">
										<li class="wf-option-title">
											<ul class="wf-flex-vertical wf-flex-align-left">
												<li><?php esc_html_e('General Data Protection Regulation', 'wordfence'); ?> <a href="<?php echo wfSupportController::esc_supportURL(wfSupportController::ITEM_GDPR); ?>" target="_blank" rel="noopener noreferrer" class="wf-inline-help"><i class="wf-fa wf-fa-question-circle-o" aria-hidden="true"></i></a></li>
												<li class="wf-option-subtitle"><?php esc_html_e('The GDPR is a set of rules that provides more control over EU personal data. Defiant has updated its terms of use, privacy policies, and software, as well as made available a data processing agreement to meet GDPR compliance.', 'wordfence'); ?></li>
											</ul>
										</li>
									</ul>
								</li>
								<li>
									<ul class="wf-option wf-option-static">
										<li class="wf-option-title">
											<ul class="wf-flex-vertical wf-flex-align-left">
												<li><?php esc_html_e('Data Processing Agreement', 'wordfence'); ?> <a href="<?php echo wfSupportController::esc_supportURL(wfSupportController::ITEM_GDPR_DPA); ?>" target="_blank" rel="noopener noreferrer" class="wf-inline-help"><i class="wf-fa wf-fa-question-circle-o" aria-hidden="true"></i></a></li>
												<li class="wf-option-subtitle"><?php echo wp_kses(sprintf(
													/* translators: URL to support page. */
														__('If you qualify as a data controller under the GDPR and need a data processing agreement, it can be <a href="%s" target="_blank" rel="noopener noreferrer">found here</a>.', 'wordfence'), wfSupportController::esc_supportURL(wfSupportController::ITEM_GDPR_DPA)), array('a'=>array('href'=>array(), 'target'=>array(), 'rel'=>array()))); ?></li>
											</ul>
										</li>
									</ul>
								</li>
								<li>
									<ul class="wf-option wf-option-static">
										<li class="wf-option-title">
											<ul class="wf-flex-vertical wf-flex-align-left">
												<li><?php esc_html_e('Agreement to New Terms and Privacy Policies', 'wordfence'); ?></li>
												<li class="wf-option-subtitle"><?php esc_html_e('To continue using Defiant products and services including the Wordfence plugin, all customers must review and agree to the updated terms and privacy policies. These changes reflect our commitment to follow data protection best practices and regulations. The Wordfence interface will remain disabled until these terms are agreed to.', 'wordfence'); ?></li>
											</ul>
										</li>
									</ul>
								</li>
							</ul>
						</div>
					</div>
				</div>
			</div> <!-- end GDPR -->
			<?php if (isset($support['all'])): ?>
			<div class="wf-row">
				<div class="wf-col-xs-12 wf-col-sm-9 wf-col-sm-half-padding-right wf-add-top">
					<h3 class="wf-no-top"><?php esc_html_e('All Documentation', 'wordfence'); ?></h3>
				</div>
			</div>
			<div class="wf-row">
				<div class="wf-col-xs-12 wf-col-sm-3 wf-col-sm-push-9 wf-col-sm-half-padding-left"> 
					<div class="wf-block wf-active">
						<div class="wf-block-content">
							<div class="wf-support-top-block">
								<h4><?php esc_html_e('Top Topics and Questions', 'wordfence'); ?></h4> 
								<ol>
								<?php
								if (isset($support['top'])):
									foreach ($support['top'] as $entry):
								?>
									<li><a href="<?php echo esc_url($entry['permalink']); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html($entry['title']); ?></a></li>
								<?php
									endforeach;
								endif;
								?>
								</ol>
							</div>
						</div>
					</div>
				</div>
				<div class="wf-col-xs-12 wf-col-sm-9 wf-col-sm-pull-3 wf-col-sm-half-padding-right">
				<?php
				if (isset($support['all'])):
					foreach ($support['all'] as $entry):
				?>
					<div class="wf-block wf-active wf-add-bottom">
						<div class="wf-block-content">
							<div class="wf-support-block">
								<h4><a href="<?php echo esc_url($entry['permalink']); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html($entry['title']); ?></a></h4>
								<p><?php echo esc_html($entry['excerpt']); ?></p>
								<?php if (isset($entry['children'])): ?>
								<ul>
								<?php foreach ($entry['children'] as $child): ?>
									<li><a href="<?php echo esc_url($child['permalink']); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html($child['title']); ?></a></li>
								<?php endforeach; ?>
								</ul>
								<?php endif; ?>
							</div>
						</div>
					</div>
				<?php
					endforeach;
				endif;
				?>
				</div>
			</div>
			<?php else: ?>
			<div class="wf-row">
				<div class="wf-col-xs-12">
					<div class="wf-block wf-active">
						<div class="wf-block-content">
							<div class="wf-support-missing-block">
								<h4><?php esc_html_e('Documentation', 'wordfence'); ?></h4>
								<p><?php echo wp_kses(__('Documentation about Wordfence may be found on our website by clicking the button below or by clicking the <i class="wf-fa wf-fa-question-circle-o" aria-hidden="true"></i> links on any of the plugin\'s pages.', 'wordfence'), array('i'=>array('class'=>array(), 'aria-hidden'=>array()))); ?></p>
								<p class="wf-no-bottom"><a href="<?php echo wfSupportController::esc_supportURL(wfSupportController::ITEM_INDEX); ?>" target="_blank" rel="noopener noreferrer" class="wf-btn wf-btn-default wf-btn-callout-subtle"><?php esc_html_e('View Documentation', 'wordfence'); ?></a></p>
							</div>
						</div>
					</div>
				</div>
			</div>
			<?php endif; ?>
		</div> <!-- end container -->
	</div>
<?php if (wfOnboardingController::shouldShowAttempt3() && (isset($_GET['onboarding']) || wfOnboardingController::shouldShowAttempt3Automatically())): ?>
	<?php wfConfig::set('onboardingAttempt3Initial', true); ?>
	<script type="text/x-jquery-template" id="wfTmpl_onboardingFinal">
		<?php echo wfView::create('onboarding/modal-final-attempt')->render(); ?>
	</script>
	<script type="application/javascript">
		(function($) {
			$(function() {
				var prompt = $('#wfTmpl_onboardingFinal').tmpl();
				var promptHTML = $("<div />").append(prompt).html();
				WFAD.colorboxHTML('800px', promptHTML, {overlayClose: false, closeButton: false, className: 'wf-modal', onComplete: function() {
					setTimeout(function() {
						$('#wf-onboarding-subscribe-controls > p').show();
						$.wfcolorbox.resize();
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
							wordfenceExt.setOption('onboardingAttempt3', '<?php echo esc_attr(wfOnboardingController::ONBOARDING_THIRD_LICENSE); ?>');
							$('#wf-onboarding-banner').slideUp();
							WFAD.colorboxClose();
							if (WFAD.tour1) { setTimeout(function() { WFAD.tour1(); }, 500); }
							<?php else: ?>
							wordfenceExt.setOption('onboardingAttempt3', '<?php echo esc_attr(wfOnboardingController::ONBOARDING_THIRD_EMAILS); ?>');

							$('#wf-onboarding-final-attempt-1, .wf-modal-footer').fadeOut(400, function() {
								$('#wf-onboarding-final-attempt-2').fadeIn();
								$.wfcolorbox.resize();
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

						$('#wf-onboarding-license-status').fadeOut();

						var license = $('#wf-onboarding-license input').val();
						wordfenceExt.onboardingInstallLicense(license,
							function(res) { //Success
								if (res.isPaid) {
									wordfenceExt.setOption('onboardingAttempt3', '<?php echo esc_attr(wfOnboardingController::ONBOARDING_THIRD_LICENSE); ?>');
									//$('#wf-onboarding-license-status').addClass('wf-green-dark').removeClass('wf-yellow-dark wf-red-dark').text('You have successfully installed a premium license.').fadeIn();
									//$('#wf-onboarding-license-install').text('Installed').addClass('wf-disabled');
									//$('#wf-onboarding-license input').attr('disabled', true);
									$('#wf-onboarding-banner').slideUp();
									$('#wf-onboarding-final-attempt .wf-modal-header-action-close').off('click');
									/*$('#wf-onboarding-premium-cta, #wf-onboarding-license-footer, #wf-onboarding-or').fadeOut(400, function() {
									 $('#wf-onboarding-license-finished').fadeIn();
									 $.wfcolorbox.resize();
									 });*/

									var html = '<div class="wf-modal wf-modal-success"><div class="wf-model-success-wrapper"><div class="wf-modal-header"><div class="wf-modal-header-content"><div class="wf-modal-title"><?php esc_html_e('Premium License Installed', 'wordfence'); ?></div></div></div><div class="wf-modal-content"><?php esc_html_e('Congratulations! Wordfence Premium is now active on your website. Please note that some Premium features are not enabled by default.', 'wordfence'); ?></div></div><div class="wf-modal-footer"><ul class="wf-onboarding-flex-horizontal wf-onboarding-flex-align-right wf-onboarding-full-width"><li><a href="<?php echo esc_url(network_admin_url('admin.php?page=Wordfence')); ?>" class="wf-onboarding-btn wf-onboarding-btn-primary"><?php esc_html_e('Continue', 'wordfence'); ?></a></li></ul></div></div>';
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
									$('#wf-onboarding-license-status').addClass('wf-yellow-dark').removeClass('wf-green-dark wf-red-dark').text('You have successfully installed a free license.').fadeIn();
									$.wfcolorbox.resize();
								}
							},
							function(res) { //Error
								$('#wf-onboarding-license-status').addClass('wf-red-dark').removeClass('wf-green-dark wf-yellow-dark').text(res.error).fadeIn();
								$.wfcolorbox.resize();
							});
					});

					$('#wf-onboarding-no-thanks, #wf-onboarding-final-attempt .wf-modal-header-action-close').on('click', function(e) {
						e.preventDefault();
						e.stopPropagation();

						if ($('#wf-onboarding-final-attempt-2').is(':visible')) {
							wordfenceExt.setOption('onboardingAttempt3', '<?php echo esc_attr(wfOnboardingController::ONBOARDING_THIRD_LICENSE); ?>');
							$('#wf-onboarding-banner').slideUp();
						}

						WFAD.colorboxClose();
						if (WFAD.tour1) { setTimeout(function() { WFAD.tour1(); }, 500); }
					});
				}});
			});
		})(jQuery);
	</script>
<?php endif; ?>