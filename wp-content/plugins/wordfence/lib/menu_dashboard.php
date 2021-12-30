<?php
if (!defined('WORDFENCE_VERSION')) { exit; }
$firewall = new wfFirewall();
$scanner = wfScanner::shared();
$d = new wfDashboard();
?>
<?php
if (wfOnboardingController::shouldShowAttempt3()) {
	echo wfView::create('onboarding/disabled-overlay')->render();
	echo wfView::create('onboarding/banner')->render();
}
else if (wfConfig::get('touppPromptNeeded')) {
	echo wfView::create('gdpr/disabled-overlay')->render();
	echo wfView::create('gdpr/banner')->render();
}
?>
<div class="wrap wordfence" id="wf-dashboard">
	<div class="wf-container-fluid">
		<div class="wf-row">
			<div class="wf-col-xs-12">
				<div class="wp-header-end"></div>
				<?php
				echo wfView::create('common/section-title', array(
					'title' => __('Wordfence Dashboard', 'wordfence'),
					'helpLink' => wfSupportController::supportURL(wfSupportController::ITEM_DASHBOARD),
					'helpLabelHTML' => wp_kses(__('Learn more<span class="wf-hidden-xs"> about the Dashboard</span>', 'wordfence'), array('span'=>array('class'=>array()))),
					'showIcon' => true,
				))->render();
				?>
			</div>
		</div>
		<div class="wf-row">
			<div class="wf-col-xs-12">
				<div class="wf-block wf-active wf-add-bottom">
					<div class="wf-block-content">
						<ul class="wf-block-list">
							<li>
								<?php
								echo wfView::create('dashboard/global-status', array(
									'firewall' => $firewall,
									'scanner' => $scanner,
									'dashboard' => $d,
								))->render();
								?>
							</li>
							<li>
								<ul class="wf-block-list wf-block-list-horizontal wf-block-list-nowrap wf-waf-coverage">
									<li>
										<?php
										echo wfView::create('common/status-detail', array(
											'id' => 'waf-coverage',
											'percentage' => $firewall->overallStatus(),
											'activeColor' => ($firewall->firewallMode() == wfFirewall::FIREWALL_MODE_LEARNING ? '#ececec' : null /* automatic */),
											'title' => __('Firewall', 'wordfence'),
											'subtitle' => ($firewall->firewallMode() == wfFirewall::FIREWALL_MODE_LEARNING ? __('WAF Currently in Learning Mode', 'wordfence') : __('Protection from known and emerging threats', 'wordfence')),
											'link' => wfPage::pageURL(wfPage::PAGE_FIREWALL_OPTIONS, wfPage::PAGE_DASHBOARD),
											'linkLabel' => __('Manage Firewall', 'wordfence'),
											'statusTitle' => __('Firewall Status', 'wordfence'),
											'statusList' => $firewall->statusList(),
											'statusExtra' => ($firewall->firewallMode() == wfFirewall::FIREWALL_MODE_LEARNING ? wfView::create('waf/status-tooltip-learning-mode')->render() : ''),
											'helpLink' => __('https://www.wordfence.com/help/dashboard/#dashboard-status', 'wordfence'),
										))->render();
										?>
									</li>
									<li>
										<?php
										echo wfView::create('common/status-detail', array(
											'id' => 'wf-scanner-type',
											'percentage' => $scanner->scanTypeStatus(),
											'activeColor' => (!$scanner->isEnabled() ? '#ececec' : null /* automatic */),
											'title' => __('Scan', 'wordfence'),
											'subtitle' => __('Detection of security issues', 'wordfence'),
											'link' => wfPage::pageURL(wfPage::PAGE_SCAN_OPTIONS, wfPage::PAGE_DASHBOARD),
											'linkLabel' => __('Manage Scan', 'wordfence'),
											'statusTitle' => __('Scan Status', 'wordfence'),
											'statusList' => $scanner->scanTypeStatusList(),
											'helpLink' => __('https://www.wordfence.com/help/dashboard/#dashboard-status', 'wordfence'),
										))->render();
										?>
									</li>
									<li>
										<?php if (wfConfig::get('hasKeyConflict')): ?>
											<?php
											echo wfView::create('common/status-critical', array(
												'id' => 'wf-premium-alert',
												'title' => __('Premium License Conflict', 'wordfence'),
												'subtitle' => __('License already in use', 'wordfence'),
												'link' => 'https://www.wordfence.com/gnl1manageConflict/manage-wordfence-api-keys/',
												'linkLabel' => __('Reset License', 'wordfence'),
												'linkNewWindow' => true,
											))->render();
											?>
										<?php elseif (wfConfig::get('keyType') == wfAPI::KEY_TYPE_PAID_EXPIRED): ?>
											<?php
											echo wfView::create('common/status-critical', array(
												'id' => 'wf-premium-alert',
												'title' => __('Premium Protection Disabled', 'wordfence'),
												'subtitle' => __('License is expired', 'wordfence'),
												'link' => 'https://www.wordfence.com/gnl1renewExpired/manage-wordfence-api-keys/',
												'linkLabel' => __('Renew License', 'wordfence'),
												'linkNewWindow' => true,
											))->render();
											?>
										<?php elseif (wfConfig::get('keyType') == wfAPI::KEY_TYPE_PAID_DELETED): ?>
											<?php
											echo wfView::create('common/status-critical', array(
												'id' => 'wf-premium-alert',
												'title' => __('Premium Protection Disabled', 'wordfence'),
												'subtitleHtml' => wp_kses(__('The license you were using has been removed from your account. Please reach out to <a href="mailto:billing@wordfence.com">billing@wordfence.com</a> or create a Premium support case at <a href="https://support.wordfence.com/support/tickets" target="_blank">https://support.wordfence.com/support/tickets<span class="screen-reader-text"> (opens in new tab)</span></a> for more information. Our staff is happy to help.', 'wordfence'), array('a'=>array('href'=>array(), 'target'=>array()), 'span'=>array('class'=>array()))),
												'link' => null,
												'linkLabel' => null
											))->render();
											?>
										<?php elseif (wfConfig::get('keyType') == wfAPI::KEY_TYPE_FREE || wfConfig::get('keyType') === false): ?>
											<div>
												<p><h3><?php esc_html_e('Premium Protection Disabled', 'wordfence'); ?></h3></p>
												<p><?php esc_html_e('As a free Wordfence user, you are currently using the Community version of the Threat Defense Feed. Premium users are protected by additional firewall rules and malware signatures. Upgrade to Premium today to improve your protection.', 'wordfence'); ?></p>
												<p><a class="wf-btn wf-btn-primary wf-btn-callout-subtle" href="https://www.wordfence.com/gnl1dashboardUpgrade/wordfence-signup/#premium-order-form" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Upgrade to Premium', 'wordfence'); ?></a>&nbsp;&nbsp;<a class="wf-btn wf-btn-callout-subtle wf-btn-default" href="https://www.wordfence.com/gnl1dashboardLearn/wordfence-signup/" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Learn More', 'wordfence'); ?><span class="screen-reader-text"> (<?php esc_html_e('opens in new tab', 'wordfence') ?>)</span></a></p>
											</div>
										<?php elseif (wfConfig::get('keyExpDays') < 30 && (wfConfig::get('premiumAutoRenew', null) === '0' || wfConfig::get('premiumAutoRenew', null) === 0)): ?>
											<?php
											echo wfView::create('common/status-critical', array(
												'id' => 'wf-premium-alert',
												'title' => __('Premium License Expiring', 'wordfence'),
												'subtitle' => __('Auto-renew is disabled', 'wordfence'),
												'link' => 'https://www.wordfence.com/gnl1renewExpiring/manage-wordfence-api-keys/',
												'linkLabel' => __('Renew License', 'wordfence'),
												'linkNewWindow' => true,
											))->render();
											?>
										<?php elseif (wfConfig::get('keyExpDays') < 30): ?>
											<?php
											if (wfConfig::get('premiumPaymentExpiring')) {
												$title = __('Payment Method Expiring', 'wordfence');
											}
											else if (wfConfig::get('premiumPaymentExpired')) {
												$title = __('Payment Method Expired', 'wordfence');
											}
											else if (wfConfig::get('premiumPaymentMissing')) {
												$title = __('Payment Method Missing', 'wordfence');
											}
											else if (wfConfig::get('premiumPaymentHold')) {
												$title = __('Payment Method Invalid', 'wordfence');
											}
											
											if (isset($title)) {
												$days = floor(((int) wfConfig::get('premiumNextRenew') - time()) / 86400);
												if ($days <= 0) {
													$subtitle = __('License renews today', 'wordfence');
												}
												else if ($days == 1) {
													$subtitle = __('License renews tomorrow', 'wordfence');
												}
												else {
													$subtitle = sprintf(
															/* translators: Number of days */
															__('License renews in %d days', 'wordfence'), $days);
												}
												
												echo wfView::create('dashboard/status-payment-expiring', array(
													'id' => 'wf-premium-alert',
													'title' => $title,
													'subtitle' => $subtitle,
													'link' => 'https://www.wordfence.com/gnl1renewExpiring/manage-wordfence-api-keys/',
													'linkLabel' => __('Update Payment Method', 'wordfence'),
													'linkNewWindow' => true,
												))->render();
											}
											else {
												$days = floor(((int) wfConfig::get('premiumNextRenew') - time()) / 86400);
												if ($days == 0) {
													$subtitle = __('License renews today', 'wordfence');
												}
												else if ($days == 1) {
													$subtitle = __('License renews in 1 day', 'wordfence');
												}
												else {
													$subtitle = sprintf(__('License renews in %d days', 'wordfence'), $days);
												}
												
												echo wfView::create('dashboard/status-renewing', array(
													'id' => 'wf-premium-alert',
													'title' => __('Premium License Expiring', 'wordfence'), 
													'subtitle' => $subtitle,
													'link' => 'https://www.wordfence.com/gnl1reviewExpiring/manage-wordfence-api-keys/',
													'linkLabel' => __('Review Payment Method', 'wordfence'),
													'linkNewWindow' => true,
												))->render();
											}
											?>
										<?php elseif (wfConfig::get('keyType') == wfAPI::KEY_TYPE_PAID_CURRENT): ?>
											<div class="wf-block-labeled-value wf-protection-status wf-protection-status-<?php echo esc_attr($firewall->ruleMode()); ?>">
												<div class="wf-block-labeled-value-value"><i class="wf-fa wf-fa-check" aria-hidden="true"></i></div>
												<div class="wf-block-labeled-value-label"><?php esc_html_e('Wordfence Premium Enabled', 'wordfence'); ?></div>
											</div> 
										<?php endif; ?>
									</li>
								</ul>
							</li>
						</ul>
					</div>
				</div>
			</div>
		</div>
		<!-- begin notifications -->
		<?php include(dirname(__FILE__) . '/dashboard/widget_notifications.php'); ?>
		<!-- end notifications -->
		<div class="wf-row">
			<div class="wf-col-xs-12">
				<div class="wf-block wf-active wf-add-bottom">
					<div class="wf-block-content">
						<ul class="wf-block-list">
							<li>
								<ul class="wf-block-list wf-block-list-horizontal wf-dashboard-navigation">
									<li>
										<?php
										echo wfView::create('common/block-navigation-option', array(
											'id' => 'wf-dashboard-option-tools',
											'img' => 'tools.svg',
											'title' => __('Tools', 'wordfence'),
											'subtitle' => __('Live Traffic, Whois Lookup, Import/Export, and Diagnostics', 'wordfence'),
											'link' => network_admin_url('admin.php?page=WordfenceTools'),
										))->render();
										?>
									</li>
									<li>
										<?php
										echo wfView::create('common/block-navigation-option', array(
											'id' => 'wf-dashboard-option-support',
											'img' => 'support.svg',
											'title' => __('Help', 'wordfence'),
											'subtitle' => __('Find the documentation and help you need', 'wordfence'),
											'link' => network_admin_url('admin.php?page=WordfenceSupport'),
										))->render();
										?>
									</li>
									<li>
										<?php
										echo wfView::create('common/block-navigation-option', array(
											'id' => 'wf-dashboard-option-options',
											'img' => 'options.svg',
											'title' => __('Global Options', 'wordfence'),
											'subtitle' => __('Manage global options for Wordfence such as alerts, premium status, and more', 'wordfence'),
											'link' => network_admin_url('admin.php?page=Wordfence&subpage=global_options'),
										))->render();
										?>
									</li>
								</ul>
							</li>
						</ul>
					</div>
				</div>
			</div>
		</div>
		<div class="wf-row">
			<div class="wf-col-xs-12 wf-col-lg-6 wf-col-lg-half-padding-right">
				<!-- begin firewall summary site -->
				<?php include(dirname(__FILE__) . '/dashboard/widget_localattacks.php'); ?>
				<!-- end firewall summary site -->
			</div> <!-- end content block -->
			<div class="wf-col-xs-12 wf-col-lg-6 wf-col-lg-half-padding-left">
				<!-- begin total attacks blocked network -->
				<?php include(dirname(__FILE__) . '/dashboard/widget_networkattacks.php'); ?>
				<!-- end total attacks blocked network -->
			</div> <!-- end content block -->
		</div> <!-- end row -->
	</div> <!-- end container -->
</div>

<?php if (wfOnboardingController::willShowNewTour(wfOnboardingController::TOUR_DASHBOARD)): ?>
<script type="application/javascript">
	(function($) {
		$(function() {
			WFAD.tour1 = function() {
				WFAD.tour('wfNewTour1', 'wfStatusTourMarker', 'top', 'left', null, WFAD.tour2);
			};
			WFAD.tour2 = function() {
				WFAD.tour('wfNewTour2', 'waf-coverage', 'top', 'left', WFAD.tour1, WFAD.tour3);
			};
			WFAD.tour3 = function() {
				WFAD.tour('wfNewTour3', 'wf-dashboard-option-options', 'right', 'right', WFAD.tour2, WFAD.tourComplete);
			};
			WFAD.tourComplete = function() { WFAD.tourFinish('<?php echo esc_attr(wfOnboardingController::TOUR_DASHBOARD); ?>'); };
			
			<?php if (wfOnboardingController::shouldShowNewTour(wfOnboardingController::TOUR_DASHBOARD) && !isset($_GET['onboarding'])): ?>
			if (!WFAD.isSmallScreen) { WFAD.tour1(); }
			<?php endif; ?>
		});
	})(jQuery);
</script>

<script type="text/x-jquery-template" id="wfNewTour1">
	<div>
		<h3><?php esc_html_e('This is your Dashboard', 'wordfence'); ?></h3>
		<p><?php esc_html_e('The Wordfence Dashboard provides valuable insights into the current state of your site\'s security. You\'ll find useful data summarized here as well as important status updates and notifications.', 'wordfence'); ?></p>
		<div class="wf-pointer-footer">
			<ul class="wf-tour-pagination">
				<li class="wf-active">&bullet;</li>
				<li>&bullet;</li>
				<li>&bullet;</li>
			</ul>
			<div id="wf-tour-continue"><a href="#" class="wf-onboarding-btn wf-onboarding-btn-primary" role="button"><?php esc_html_e('Next', 'wordfence'); ?></a></div>
		</div>
		<div id="wf-tour-close"><a href="#" role="button"><i class="wf-fa wf-fa-times-circle" aria-hidden="true"></i></a></div>
	</div>
</script>
<script type="text/x-jquery-template" id="wfNewTour2">
	<div>
		<h3><?php esc_html_e('Easily Monitor Your Wordfence Protection', 'wordfence'); ?></h3>
		<p><?php esc_html_e('Each feature contains a status that reminds you what\'s enabled, disabled or needs attention. The Notifications section will highlight actions you need to take.', 'wordfence'); ?></p>
		<div class="wf-pointer-footer">
			<ul class="wf-tour-pagination">
				<li>&bullet;</li>
				<li class="wf-active">&bullet;</li>
				<li>&bullet;</li>
			</ul>
			<div id="wf-tour-previous"><a href="#" class="wf-onboarding-btn wf-onboarding-btn-default" role="button"><?php esc_html_e('Previous', 'wordfence'); ?></a></div>
			<div id="wf-tour-continue"><a href="#" class="wf-onboarding-btn wf-onboarding-btn-primary" role="button"><?php esc_html_e('Next', 'wordfence'); ?></a></div>
		</div>
		<div id="wf-tour-close"><a href="#" role="button"><i class="wf-fa wf-fa-times-circle" aria-hidden="true"></i></a></div>
	</div>
</script>
<script type="text/x-jquery-template" id="wfNewTour3">
	<div>
		<h3><?php esc_html_e('Global Wordfence Options', 'wordfence'); ?></h3>
		<p class="wf-center"><svg viewBox="0 0 100.11 100.11" class="wf-icon"><path d="M99.59,41.42a2.06,2.06,0,0,0-1.37-.82L86.3,38.78a39.34,39.34,0,0,0-2.67-6.39q1.17-1.63,3.52-4.6t3.32-4.33A2.52,2.52,0,0,0,91,22a2.1,2.1,0,0,0-.46-1.43Q88.18,17.2,79.78,9.45a2.52,2.52,0,0,0-1.63-.65,2.12,2.12,0,0,0-1.57.59l-9.25,7a40.09,40.09,0,0,0-5.87-2.41L59.64,2a1.92,1.92,0,0,0-.75-1.4A2.46,2.46,0,0,0,57.29,0H42.82a2.19,2.19,0,0,0-2.34,1.82,106,106,0,0,0-1.89,12.12,37.62,37.62,0,0,0-5.93,2.48l-9-7A2.78,2.78,0,0,0,22,8.8q-1.44,0-6.16,4.66a64.88,64.88,0,0,0-6.42,7A2.75,2.75,0,0,0,8.8,22a2.44,2.44,0,0,0,.65,1.56q4.37,5.28,7,9a32.38,32.38,0,0,0-2.54,6L1.76,40.34a2,2,0,0,0-1.24.85A2.5,2.5,0,0,0,0,42.69V57.16a2.44,2.44,0,0,0,.52,1.53,2,2,0,0,0,1.37.82l11.93,1.76a31.91,31.91,0,0,0,2.67,6.45Q15.31,69.35,13,72.31T9.65,76.65a2.54,2.54,0,0,0-.07,3q2.54,3.52,10.75,11a2.25,2.25,0,0,0,1.63.71,2.35,2.35,0,0,0,1.63-.59l9.19-7a40.54,40.54,0,0,0,5.87,2.41l1.82,12a1.92,1.92,0,0,0,.75,1.4,2.45,2.45,0,0,0,1.6.55H57.29a2.2,2.2,0,0,0,2.35-1.82,107.41,107.41,0,0,0,1.89-12.12,37.19,37.19,0,0,0,5.93-2.48l9,7a3.18,3.18,0,0,0,1.69.59q1.43,0,6.13-4.62a65.86,65.86,0,0,0,6.45-7,2.16,2.16,0,0,0,.59-1.5,2.51,2.51,0,0,0-.65-1.63q-4.69-5.74-7-9a41.57,41.57,0,0,0,2.54-5.93l12.06-1.82a2,2,0,0,0,1.3-.85,2.52,2.52,0,0,0,.52-1.5V43a2.46,2.46,0,0,0-.52-1.53ZM61.85,61.86a16.08,16.08,0,0,1-11.8,4.89A16.69,16.69,0,0,1,33.37,50.06,16.69,16.69,0,0,1,50.06,33.37,16.69,16.69,0,0,1,66.74,50.06a16.08,16.08,0,0,1-4.89,11.8Zm0,0"></path></svg></p>
		<p><?php echo wp_kses(__('You\'ll find this icon throughout the plugin. Clicking it will show you the options and features for each section of Wordfence. From the dashboard, you can find the <strong>Global Options</strong> for Wordfence such as alerts, automatic updates, and managing your site\'s Premium License.', 'wordfence'), array('strong'=>array())); ?></p>
		<div class="wf-pointer-footer">
			<ul class="wf-tour-pagination">
				<li>&bullet;</li>
				<li>&bullet;</li>
				<li class="wf-active">&bullet;</li>
			</ul>
			<div id="wf-tour-previous"><a href="#" class="wf-onboarding-btn wf-onboarding-btn-default" role="button"><?php esc_html_e('Previous', 'wordfence'); ?></a></div>
			<div id="wf-tour-continue"><a href="#" class="wf-onboarding-btn wf-onboarding-btn-primary" role="button"><?php esc_html_e('Got it', 'wordfence'); ?></a></div>
		</div>
		<div id="wf-tour-close"><a href="#" role="button"><i class="wf-fa wf-fa-times-circle" aria-hidden="true"></i></a></div>
	</div>
</script>
<?php endif; ?>

<?php if (wfOnboardingController::willShowUpgradeTour(wfOnboardingController::TOUR_DASHBOARD)): ?>
<script type="application/javascript">
	(function($) {
		$(function() {
			WFAD.tour1 = function() {
				WFAD.tour('wfUpgradeTour1', 'wfStatusTourMarker', 'top', 'left', null, WFAD.tour2);
			};
			WFAD.tour2 = function() {
				WFAD.tour('wfUpgradeTour2', 'waf-coverage', 'top', 'left', WFAD.tour1, WFAD.tour3);
			};
			WFAD.tour3 = function() {
				WFAD.tour('wfUpgradeTour3', 'wf-dashboard-option-options', 'right', 'right', WFAD.tour2, WFAD.tour4);
			};
			WFAD.tour4 = function() {
				WFAD.tour('wfUpgradeTour4', 'toplevel_page_Wordfence', 'left', 'left', WFAD.tour3, WFAD.tourComplete);
			};
			WFAD.tourComplete = function() { WFAD.tourFinish('<?php echo esc_attr(wfOnboardingController::TOUR_DASHBOARD); ?>'); };
			
			<?php if (wfOnboardingController::shouldShowUpgradeTour(wfOnboardingController::TOUR_DASHBOARD) && !isset($_GET['onboarding'])): ?>
			if (!WFAD.isSmallScreen) { WFAD.tour1(); }
			<?php endif; ?>
		});
	})(jQuery);
</script>

<script type="text/x-jquery-template" id="wfUpgradeTour1">
	<div>
		<h3><?php printf(
				/* translators: Wordfence version. */
				esc_html__('You have successfully updated to Wordfence %s', 'wordfence'), WORDFENCE_VERSION); ?></h3>
		<p><?php esc_html_e('This update includes a number of significant interface changes. We\'d like to walk you through  some of them, but you can bypass the tour for a section at any time by closing the dialogs.', 'wordfence'); ?></p>
		<p><?php echo wp_kses(__('We welcome your feedback and comments at <a href="mailto:feedback@wordfence.com">feedback@wordfence.com</a>. For a deeper dive on all of the changes, <a href="https://www.wordfence.com/blog/2018/01/introducing-wordfence-7/" target="_blank" rel="noopener noreferrer">click here<span class="screen-reader-text"> (opens in new tab)</span></a>.', 'wordfence'), array('a'=>array('href'=>array(), 'target'=>array()), 'span'=>array('class'=>array()))); ?></p>
		<div class="wf-pointer-footer">
			<ul class="wf-tour-pagination">
				<li class="wf-active">&bullet;</li>
				<li>&bullet;</li>
				<li>&bullet;</li>
				<li>&bullet;</li>
			</ul>
			<div id="wf-tour-continue"><a href="#" class="wf-onboarding-btn wf-onboarding-btn-primary" role="button"><?php esc_html_e('Next', 'wordfence'); ?></a></div>
		</div>
		<div id="wf-tour-close"><a href="#" role="button"><i class="wf-fa wf-fa-times-circle" aria-hidden="true"></i></a></div>
	</div>
</script>
<script type="text/x-jquery-template" id="wfUpgradeTour2">
	<div>
		<h3><?php esc_html_e('Monitor Your Wordfence Protection', 'wordfence'); ?></h3>
		<p><?php esc_html_e('Each feature contains a status percentage reminding you at a high level of what\'s enabled, disabled, or needing your attention. The Notifications section highlights actions you need to take.', 'wordfence'); ?></p>
		<div class="wf-pointer-footer">
			<ul class="wf-tour-pagination">
				<li>&bullet;</li>
				<li class="wf-active">&bullet;</li>
				<li>&bullet;</li>
				<li>&bullet;</li>
			</ul>
			<div id="wf-tour-previous"><a href="#" class="wf-onboarding-btn wf-onboarding-btn-default" role="button"><?php esc_html_e('Previous', 'wordfence'); ?></a></div>
			<div id="wf-tour-continue"><a href="#" class="wf-onboarding-btn wf-onboarding-btn-primary" role="button"><?php esc_html_e('Next', 'wordfence'); ?></a></div>
		</div>
		<div id="wf-tour-close"><a href="#" role="button"><i class="wf-fa wf-fa-times-circle" aria-hidden="true"></i></a></div>
	</div>
</script>
<script type="text/x-jquery-template" id="wfUpgradeTour3">
	<div>
		<h3><?php esc_html_e('Global Wordfence Options', 'wordfence'); ?></h3>
		<p class="wf-center"><svg viewBox="0 0 100.11 100.11" class="wf-icon"><path d="M99.59,41.42a2.06,2.06,0,0,0-1.37-.82L86.3,38.78a39.34,39.34,0,0,0-2.67-6.39q1.17-1.63,3.52-4.6t3.32-4.33A2.52,2.52,0,0,0,91,22a2.1,2.1,0,0,0-.46-1.43Q88.18,17.2,79.78,9.45a2.52,2.52,0,0,0-1.63-.65,2.12,2.12,0,0,0-1.57.59l-9.25,7a40.09,40.09,0,0,0-5.87-2.41L59.64,2a1.92,1.92,0,0,0-.75-1.4A2.46,2.46,0,0,0,57.29,0H42.82a2.19,2.19,0,0,0-2.34,1.82,106,106,0,0,0-1.89,12.12,37.62,37.62,0,0,0-5.93,2.48l-9-7A2.78,2.78,0,0,0,22,8.8q-1.44,0-6.16,4.66a64.88,64.88,0,0,0-6.42,7A2.75,2.75,0,0,0,8.8,22a2.44,2.44,0,0,0,.65,1.56q4.37,5.28,7,9a32.38,32.38,0,0,0-2.54,6L1.76,40.34a2,2,0,0,0-1.24.85A2.5,2.5,0,0,0,0,42.69V57.16a2.44,2.44,0,0,0,.52,1.53,2,2,0,0,0,1.37.82l11.93,1.76a31.91,31.91,0,0,0,2.67,6.45Q15.31,69.35,13,72.31T9.65,76.65a2.54,2.54,0,0,0-.07,3q2.54,3.52,10.75,11a2.25,2.25,0,0,0,1.63.71,2.35,2.35,0,0,0,1.63-.59l9.19-7a40.54,40.54,0,0,0,5.87,2.41l1.82,12a1.92,1.92,0,0,0,.75,1.4,2.45,2.45,0,0,0,1.6.55H57.29a2.2,2.2,0,0,0,2.35-1.82,107.41,107.41,0,0,0,1.89-12.12,37.19,37.19,0,0,0,5.93-2.48l9,7a3.18,3.18,0,0,0,1.69.59q1.43,0,6.13-4.62a65.86,65.86,0,0,0,6.45-7,2.16,2.16,0,0,0,.59-1.5,2.51,2.51,0,0,0-.65-1.63q-4.69-5.74-7-9a41.57,41.57,0,0,0,2.54-5.93l12.06-1.82a2,2,0,0,0,1.3-.85,2.52,2.52,0,0,0,.52-1.5V43a2.46,2.46,0,0,0-.52-1.53ZM61.85,61.86a16.08,16.08,0,0,1-11.8,4.89A16.69,16.69,0,0,1,33.37,50.06,16.69,16.69,0,0,1,50.06,33.37,16.69,16.69,0,0,1,66.74,50.06a16.08,16.08,0,0,1-4.89,11.8Zm0,0"></path></svg></p>
		<p><?php esc_html_e('Manage your Wordfence license, see alerts and automatic plugin updates, and import/export your settings.', 'wordfence'); ?></p>
		<div class="wf-pointer-footer">
			<ul class="wf-tour-pagination">
				<li>&bullet;</li>
				<li>&bullet;</li>
				<li class="wf-active">&bullet;</li>
				<li>&bullet;</li>
			</ul>
			<div id="wf-tour-previous"><a href="#" class="wf-onboarding-btn wf-onboarding-btn-default" role="button"><?php esc_html_e('Previous', 'wordfence'); ?></a></div>
			<div id="wf-tour-continue"><a href="#" class="wf-onboarding-btn wf-onboarding-btn-primary" role="button"><?php esc_html_e('Next', 'wordfence'); ?></a></div>
		</div>
		<div id="wf-tour-close"><a href="#" role="button"><i class="wf-fa wf-fa-times-circle" aria-hidden="true"></i></a></div>
	</div>
</script>
<script type="text/x-jquery-template" id="wfUpgradeTour4">
	<div>
		<h3><?php esc_html_e('Updated Navigation', 'wordfence'); ?></h3>
		<p><?php echo wp_kses(__('The main navigation no longer includes an <strong>Options</strong> link. Options are now accessed via the <strong>Options</strong> link on each feature\'s main page. Live Traffic is now located in the Tools section, and blocking is found under the Firewall. Shortcuts to add a <strong>Blocking</strong> link back to the main navigation are available under Blocking options.', 'wordfence'), array('strong'=>array())); ?></p>
		<div class="wf-pointer-footer">
			<ul class="wf-tour-pagination">
				<li>&bullet;</li>
				<li>&bullet;</li>
				<li>&bullet;</li>
				<li class="wf-active">&bullet;</li>
			</ul>
			<div id="wf-tour-previous"><a href="#" class="wf-onboarding-btn wf-onboarding-btn-default" role="button"><?php esc_html_e('Previous', 'wordfence'); ?></a></div>
			<div id="wf-tour-continue"><a href="#" class="wf-onboarding-btn wf-onboarding-btn-primary" role="button"><?php esc_html_e('Got it', 'wordfence'); ?></a></div>
		</div>
		<div id="wf-tour-close"><a href="#" role="button"><i class="wf-fa wf-fa-times-circle" aria-hidden="true"></i></a></div>
	</div>
</script>
<?php endif; ?>
<?php
$hostSetting = false;
$recordAll = wfConfig::liveTrafficEnabled($hostSetting);

if ($recordAll && !$hostSetting && !wfUtils::truthyToBoolean(wfConfig::get('switchLiveTrafficSecurityOnlyChoice'))):
?>
<script type="application/javascript">
	(function($) {
		$(function() {
			var prompt = $('#wfLiveTrafficMigration').tmpl();
			var promptHTML = $("<div />").append(prompt).html();
			WFAD.colorboxHTML((WFAD.isSmallScreen ? '300px' : '700px'), promptHTML, {overlayClose: false, closeButton: false, className: 'wf-modal', onComplete: function() {
				$('#wf-livetrafficmigrate-no').on('click', function(e) {
					e.preventDefault();
					e.stopPropagation();

					wordfenceExt.switchLiveTrafficSecurityOnlyChoice('no');
					WFAD.colorboxClose();
				});

				$('#wf-livetrafficmigrate-yes').on('click', function(e) {
					e.preventDefault();
					e.stopPropagation();

					wordfenceExt.switchLiveTrafficSecurityOnlyChoice('yes');
					WFAD.colorboxClose();
				});
			}});
		});
	})(jQuery);
</script>
<script type="text/x-jquery-template" id="wfLiveTrafficMigration">
	<?php
	echo wfView::create('common/modal-prompt', array(
		'title' => __('Recommended Settings Change', 'wordfence'),
		'messageHTML' => '<p>' . esc_html__('Greetings! The default configuration for Wordfence Live Traffic has changed. The new default saves only logins and blocked requests, while this site is currently recording all traffic. Would you like to change to the new default?', 'wordfence') . '</p>' . (!wfRateLimit::identicalHumanBotRateLimits() ? '<p>' . __('Rate limiting based on type of request (human vs crawler) may be less accurate because this prevents loading the extra JavaScript used for that identification.', 'wordfence') . '</p>' : ''),
		'primaryButton' => array('id' => 'wf-livetrafficmigrate-yes', 'label' => __('Yes Please', 'wordfence'), 'link' => '#', 'type' => 'wf-btn-primary'),
		'secondaryButtons' => array(
			array('id' => 'wf-livetrafficmigrate-no', 'label' => __('No Thanks', 'wordfence'), 'link' => '#', 'type' => 'wf-btn-default'),
			array('id' => 'wf-livetrafficmigrate-learn', 'label' => __('Learn More', 'wordfence'), 'link' => wfSupportController::supportURL(wfSupportController::ITEM_NOTICE_SWITCH_LIVE_TRAFFIC), 'type' => 'wf-btn-default', 'target' => '_blank', 'rel' => 'noopener noreferrer'),
		),
	))->render();
	?>
</script>
<?php endif; ?>