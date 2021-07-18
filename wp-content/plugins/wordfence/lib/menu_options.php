<?php
if (!defined('WORDFENCE_VERSION')) { exit; }
$waf = wfWAF::getInstance();
$d = new wfDashboard(); unset($d->countriesNetwork);
$firewall = new wfFirewall();
$scanner = wfScanner::shared();
$config = $waf->getStorageEngine();
$wafURL = wfPage::pageURL(wfPage::PAGE_FIREWALL);
$wafConfigURL = network_admin_url('admin.php?page=WordfenceWAF&subpage=waf_options#configureAutoPrepend');
$wafRemoveURL = network_admin_url('admin.php?page=WordfenceWAF&subpage=waf_options#removeAutoPrepend');
/** @var array $wafData */

$backPage = new wfPage(wfPage::PAGE_FIREWALL);
if (isset($_GET['source']) && wfPage::isValidPage($_GET['source'])) {
	$backPage = new wfPage($_GET['source']);
}
?>
<script type="application/javascript">
	(function($) {
		WFAD.wafData = <?php echo json_encode($wafData); ?>;
		WFAD.restoreWAFData = JSON.parse(JSON.stringify(WFAD.wafData)); //Copied into wafData when canceling changes

		$(function() {
			document.title = "<?php esc_attr_e('All Options', 'wordfence'); ?>" + " \u2039 " + WFAD.basePageName;
			
			WFAD.wafConfigPageRender();

			//Hash-based option block linking
			if (window.location.hash) {
				var hashes = WFAD.parseHashes();
				var hash = hashes[hashes.length - 1];
				var block = $('.wf-block[data-persistence-key="' + hash + '"]');
				if (block.length) {
					if (!block.hasClass('wf-active')) {
						block.find('.wf-block-content').slideDown({
							always: function() {
								block.addClass('wf-active');
								$('html, body').animate({
									scrollTop: block.offset().top - 100
								}, 1000);
							}
						});

						WFAD.ajax('wordfence_saveDisclosureState', {name: block.data('persistenceKey'), state: true}, function() {}, function() {}, true);
					}
					else {
						$('html, body').animate({
							scrollTop: block.offset().top - 100
						}, 1000);
					}

					history.replaceState('', document.title, window.location.pathname + window.location.search);
				}
			}
		});

		$(window).on('wfOptionsReset', function() {
			WFAD.wafData = JSON.parse(JSON.stringify(WFAD.restoreWAFData));
			WFAD.wafConfigPageRender();
		});
	})(jQuery);
</script>
<div class="wf-options-controls">
	<div class="wf-row">
		<div class="wf-col-xs-12">
			<?php
			$indexOptions = array(
				'wf-option-apiKey' => __('License Key', 'wordfence'),
				'wf-option-displayTopLevelOptions' => __('Display All Options menu item', 'wordfence'),
				'wf-option-displayTopLevelBlocking' => __('Display Blocking menu item', 'wordfence'),
				'wf-option-displayTopLevelLiveTraffic' => __('Display Live Traffic menu item', 'wordfence'),
				'wf-option-autoUpdate' => __('Update Wordfence automatically when a new version is released?', 'wordfence'),
				'wf-option-alertEmails' => __('Where to email alerts', 'wordfence'),
				'wf-option-howGetIPs' => __('How does Wordfence get IPs', 'wordfence'),
				'wf-option-howGetIPs-trusted-proxies' => __('Trusted Proxies', 'wordfence'),
				'wf-option-other-hideWPVersion' => __('Hide WordPress version', 'wordfence'),
				'wf-option-disableCodeExecutionUploads' => __('Disable Code Execution for Uploads directory', 'wordfence'),
				'wf-option-liveActivityPauseEnabled' => __('Pause live updates when window loses focus', 'wordfence'),
				'wf-option-actUpdateInterval' => __('Update interval in seconds', 'wordfence'),
				'wf-option-other-bypassLitespeedNoabort' => __('Bypass the LiteSpeed "noabort" check', 'wordfence'),
				'wf-option-deleteTablesOnDeact' => __('Delete Wordfence tables and data on deactivation', 'wordfence'),
				'wf-option-notification-updatesNeeded' => __('Updates Needed (Plugin, Theme, or Core)', 'wordfence'),
				'wf-option-notification-securityAlerts' => __('Security Alerts', 'wordfence'),
				'wf-option-notification-promotions' => __('Promotions', 'wordfence'),
				'wf-option-notification-blogHighlights' => __('Blog Highlights', 'wordfence'),
				'wf-option-notification-productUpdates' => __('Product Updates', 'wordfence'),
				'wf-option-notification-scanStatus' => __('Scan Status', 'wordfence'),
				'wf-option-alertOn-update' => __('Email me when Wordfence is automatically updated', 'wordfence'),
				'wf-option-alertOn-wordfenceDeactivated' => __('Email me if Wordfence is deactivated', 'wordfence'),
				'wf-option-alertOn-wafDeactivated' => __('Email me if the Wordfence Web Application Firewall is turned off', 'wordfence'),
				'wf-option-alertOn-scanIssues' => __('Alert me with scan results of this severity level or greater', 'wordfence'),
				'wf-option-alertOn-block' => __('Alert when an IP address is blocked', 'wordfence'),
				'wf-option-alertOn-loginLockout' => __('Alert when someone is locked out from login', 'wordfence'),
				'wf-option-alertOn-lostPasswdForm' => __('Alert when the "lost password" form is used for a valid user', 'wordfence'),
				'wf-option-alertOn-adminLogin' => __('Alert me when someone with administrator access signs in', 'wordfence'),
				'wf-option-alertOn-firstAdminLoginOnly' => __('Only alert me when that administrator signs in from a new device', 'wordfence'),
				'wf-option-alertOn-nonAdminLogin' => __('Alert me when a non-admin user signs in', 'wordfence'),
				'wf-option-alertOn-firstNonAdminLoginOnly' => __('Only alert me when that user signs in from a new device', 'wordfence'),
				'wf-option-wafAlertOnAttacks' => __('Alert me when there\'s a large increase in attacks detected on my site', 'wordfence'),
				'wf-option-alert-maxHourly' => __('Maximum email alerts to send per hour', 'wordfence'),
				'wf-option-email-summary-enabled' => __('Enable email summary', 'wordfence'),
				'wf-option-email-summary-excluded-directories' => __('List of directories to exclude from recently modified file list', 'wordfence'),
				'wf-option-email-summary-dashboard-widget-enabled' => __('Enable activity report widget on the WordPress dashboard', 'wordfence'),
				'wf-option-wafStatus' => __('Web Application Firewall Status', 'wordfence'),
				'wf-option-protectionMode' => __('Web Application Firewall Protection Level', 'wordfence'),
				'wf-option-disableWAFBlacklistBlocking' => __('Real-Time IP Blocklist', 'wordfence'),
				'wf-option-disableWAFIPBlocking' => __('Delay IP and Country blocking until after WordPress and plugins have loaded (only process firewall rules early)', 'wordfence'),
				'wf-option-whitelisted' => __('Allowlisted IP addresses that bypass all rules', 'wordfence'),
				'wf-option-whitelistedServices' => __('Allowlisted services', 'wordfence'),
				'wf-option-bannedURLs' => __('Immediately block IPs that access these URLs', 'wordfence'),
				'wf-option-wafAlertWhitelist' => __('Ignored IP addresses for Wordfence Web Application Firewall alerting', 'wordfence'),
				'wf-option-wafRules' => __('Web Application Firewall Rules', 'wordfence'),
				'wf-option-loginSecurityEnabled' => __('Enable brute force protection', 'wordfence'),
				'wf-option-loginSec-maxFailures' => __('Lock out after how many login failures', 'wordfence'),
				'wf-option-loginSec-maxForgotPasswd' => __('Lock out after how many forgot password attempts', 'wordfence'),
				'wf-option-loginSec-countFailMins' => __('Count failures over what time period', 'wordfence'),
				'wf-option-loginSec-lockoutMins' => __('Amount of time a user is locked out', 'wordfence'),
				'wf-option-loginSec-lockInvalidUsers' => __('Immediately lock out invalid usernames', 'wordfence'),
				'wf-option-loginSec-userBlacklist' => __('Immediately block the IP of users who try to sign in as these usernames', 'wordfence'),
				'wf-option-loginSec-strongPasswds-enabled' => __('Enforce strong passwords', 'wordfence'),
				'wf-option-loginSec-breachPasswds-enabled' => __('Prevent the use of passwords leaked in data breaches', 'wordfence'),
				'wf-option-loginSec-maskLoginErrors' => __('Don\'t let WordPress reveal valid users in login errors', 'wordfence'),
				'wf-option-loginSec-blockAdminReg' => __('Prevent users registering "admin" username if it doesn\'t exist', 'wordfence'),
				'wf-option-loginSec-disableAuthorScan' => __('Prevent discovery of usernames through "/?author=N" scans, the oEmbed API, the WordPress REST API, and WordPress XML Sitemaps', 'wordfence'),
				'wf-option-loginSec-disableApplicationPasswords' => __('Disable WordPress application passwords', 'wordfence'),
				'wf-option-other-blockBadPOST' => __('Block IPs who send POST requests with blank User-Agent and Referer', 'wordfence'),
				'wf-option-blockCustomText' => __('Custom text shown on block pages', 'wordfence'),
				'wf-option-other-pwStrengthOnUpdate' => __('Check password strength on profile update', 'wordfence'),
				'wf-option-other-WFNet' => __('Participate in the Real-Time Wordfence Security Network', 'wordfence'),
				'wf-option-firewallEnabled' => __('Enable Rate Limiting and Advanced Blocking', 'wordfence'),
				'wf-option-neverBlockBG' => __('How should we treat Google\'s crawlers', 'wordfence'),
				'wf-option-maxGlobalRequests' => __('If anyone\'s requests exceed', 'wordfence'),
				'wf-option-maxRequestsCrawlers' => __('If a crawler\'s page views exceed', 'wordfence'),
				'wf-option-max404Crawlers' => __('If a crawler\'s pages not found (404s) exceed', 'wordfence'),
				'wf-option-maxRequestsHumans' => __('If a human\'s page views exceed', 'wordfence'),
				'wf-option-max404Humans' => __('If a human\'s pages not found (404s) exceed', 'wordfence'),
				'wf-option-blockedTime' => __('How long is an IP address blocked when it breaks a rule', 'wordfence'),
				'wf-option-allowed404s' => __('Allowlisted 404 URLs', 'wordfence'),
				'wf-option-wafWhitelist' => __('Web Application Firewall Allowlisted URLs', 'wordfence'),
				'wf-option-ajaxWatcherDisabled-front' => __('Monitor background requests from an administrator\'s web browser for false positives (Front-end Website)', 'wordfence'),
				'wf-option-ajaxWatcherDisabled-admin' => __('Monitor background requests from an administrator\'s web browser for false positives (Admin Panel)', 'wordfence'),
				'wf-option-cbl-action' => __('What to do when we block someone visiting from a blocked country', 'wordfence'),
				'wf-option-cbl-redirURL' => __('URL to redirect blocked countries to', 'wordfence'),
				'wf-option-cbl-loggedInBlocked' => __('Block countries even if they are logged in', 'wordfence'),
				'wf-option-cbl-bypassRedirURL' => __('If user from a blocked country hits the relative URL ____ then redirect that user to ____ and set a cookie that will bypass all country blocking', 'wordfence'),
				'wf-option-cbl-bypassViewURL' => __('If user who is allowed to access the site views the relative URL ____ then set a cookie that will bypass country blocking in future in case that user hits the site from a blocked country', 'wordfence'),
				'wf-option-scheduledScansEnabled' => __('Schedule Wordfence Scans', 'wordfence'),
				'wf-option-scanType' => __('Scan Type', 'wordfence'),
				'wf-option-scansEnabled-checkGSB' => __('Check if this website is on a domain blocklist', 'wordfence'),
				'wf-option-spamvertizeCheck' => __('Check if this website is being &quot;Spamvertised&quot;', 'wordfence'),
				'wf-option-checkSpamIP' => __('Check if this website IP is generating spam', 'wordfence'),
				'wf-option-scansEnabled-checkHowGetIPs' => __('Scan for misconfigured How does Wordfence get IPs', 'wordfence'),
				'wf-option-scansEnabled-checkReadableConfig' => __('Scan for publicly accessible configuration, backup, or log files', 'wordfence'),
				'wf-option-scansEnabled-suspectedFiles' => __('Scan for publicly accessible quarantined files', 'wordfence'),
				'wf-option-scansEnabled-core' => __('Scan core files against repository versions for changes', 'wordfence'),
				'wf-option-scansEnabled-themes' => __('Scan theme files against repository versions for changes', 'wordfence'),
				'wf-option-scansEnabled-plugins' => __('Scan plugin files against repository versions for changes', 'wordfence'),
				'wf-option-scansEnabled-coreUnknown' => __('Scan wp-admin and wp-includes for files not bundled with WordPress', 'wordfence'),
				'wf-option-scansEnabled-malware' => __('Scan for signatures of known malicious files', 'wordfence'),
				'wf-option-scansEnabled-fileContents' => __('Scan file contents for backdoors, trojans and suspicious code', 'wordfence'),
				'wf-option-scansEnabled-fileContentsGSB' => __('Scan file contents for malicious URLs', 'wordfence'),
				'wf-option-scansEnabled-posts' => __('Scan posts for known dangerous URLs and suspicious content', 'wordfence'),
				'wf-option-scansEnabled-comments' => __('Scan comments for known dangerous URLs and suspicious content', 'wordfence'),
				'wf-option-scansEnabled-suspiciousOptions' => __('Scan WordPress core, plugin, and theme options for known dangerous URLs and suspicious content', 'wordfence'),
				'wf-option-scansEnabled-oldVersions' => __('Scan for out of date, abandoned, and vulnerable plugins, themes, and WordPress versions', 'wordfence'),
				'wf-option-scansEnabled-suspiciousAdminUsers' => __('Scan for suspicious admin users created outside of WordPress', 'wordfence'),
				'wf-option-scansEnabled-passwds' => __('Check the strength of passwords', 'wordfence'),
				'wf-option-scansEnabled-diskSpace' => __('Monitor disk space', 'wordfence'),
				'wf-option-scansEnabled-wafStatus' => __('Monitor Web Application Firewall status', 'wordfence'),
				'wf-option-other-scanOutside' => __('Scan files outside your WordPress installation', 'wordfence'),
				'wf-option-scansEnabled-scanImages' => __('Scan images, binary, and other files as if they were executable', 'wordfence'),
				'wf-option-lowResourceScansEnabled' => __('Use low resource scanning (reduces server load by lengthening the scan duration)', 'wordfence'),
				'wf-option-scan-maxIssues' => __('Limit the number of issues sent in the scan results email', 'wordfence'),
				'wf-option-scan-maxDuration' => __('Time limit that a scan can run in seconds', 'wordfence'),
				'wf-option-maxMem' => __('How much memory should Wordfence request when scanning', 'wordfence'),
				'wf-option-maxExecutionTime' => __('Maximum execution time for each scan stage', 'wordfence'),
				'wf-option-scan-exclude' => __('Exclude files from scan that match these wildcard patterns', 'wordfence'),
				'wf-option-scan-include-extra' => __('Additional scan signatures', 'wordfence'),
				'wf-option-liveTrafficEnabled' => __('Traffic logging mode (Live Traffic)', 'wordfence'),
				'wf-option-liveTraf-ignorePublishers' => __('Don\'t log signed-in users with publishing access', 'wordfence'),
				'wf-option-liveTraf-ignoreUsers' => __('List of comma separated usernames to ignore', 'wordfence'),
				'wf-option-liveTraf-ignoreIPs' => __('List of comma separated IP addresses to ignore', 'wordfence'),
				'wf-option-liveTraf-ignoreUA' => __('Browser user-agent to ignore', 'wordfence'),
				'wf-option-liveTraf-maxRows' => __('Amount of Live Traffic data to store (number of rows)', 'wordfence'),
				'wf-option-liveTraf-maxAge' => __('Maximum days to keep Live Traffic data', 'wordfence'),
				'wf-option-exportOptions' => __('Export this site\'s Wordfence options for import on another site', 'wordfence'),
				'wf-option-importOptions' => __('Import Wordfence options from another site using a token', 'wordfence'),
			);
			
			if (wfCredentialsController::useLegacy2FA()) {
				$indexOptions['wf-option-loginSec-requireAdminTwoFactor'] = __('Require Cellphone Sign-in for all Administrators', 'wordfence');
				$indexOptions['wf-option-loginSec-enableSeparateTwoFactor'] = __('Enable Separate Prompt for Two Factor Code', 'wordfence');
			}
			
			$indexOptions = array_merge($indexOptions, wfModuleController::shared()->optionIndexes);
			
			echo wfView::create('options/block-all-options-controls', array(
				'showIcon' => false,
				'indexOptions' => $indexOptions,
				'restoreDefaultsSection' => wfConfig::OPTIONS_TYPE_ALL,
				'restoreDefaultsMessage' => __('Are you sure you want to restore the default settings? This will undo any custom changes you have made to the options on this page. If you have manually disabled any rules or added any custom allowlisted URLs, those changes will not be overwritten.', 'wordfence'),
			))->render();
			?>
		</div>
	</div>
</div>
<div class="wf-options-controls-spacer"></div>
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
<div class="wrap wordfence">
	<div class="wf-container-fluid">
		<?php
		if (function_exists('network_admin_url') && is_multisite()) {
			$firewallURL = network_admin_url('admin.php?page=WordfenceWAF#top#waf');
			$blockingURL = network_admin_url('admin.php?page=WordfenceWAF#top#blocking');
		}
		else {
			$firewallURL = admin_url('admin.php?page=WordfenceWAF#top#waf');
			$blockingURL = admin_url('admin.php?page=WordfenceWAF#top#blocking');
		}
		?>
		<div class="wf-row">
			<div class="wf-col-xs-12">
				<div class="wp-header-end"></div>
			</div>
		</div>
		<div class="wf-row">
			<div class="<?php echo wfStyle::contentClasses(); ?>">
				<div id="wf-all-options" class="wf-fixed-tab-content">
					<?php
					$stateKeys = array(
						'wf-unified-global-options-license',
						'wf-unified-global-options-view-customization',
						'wf-unified-global-options-general',
						'wf-unified-global-options-dashboard',
						'wf-unified-global-options-alert',
						'wf-unified-global-options-email-summary',
						'wf-unified-waf-options-basic',
						'wf-unified-waf-options-advanced',
						'wf-unified-waf-options-bruteforce',
						'wf-unified-waf-options-ratelimiting',
						'wf-unified-waf-options-whitelisted',
						'wf-unified-blocking-options-country',
						'wf-unified-scanner-options-schedule',
						'wf-unified-scanner-options-basic',
						'wf-unified-scanner-options-general',
						'wf-unified-scanner-options-performance',
						'wf-unified-scanner-options-custom',
						'wf-unified-2fa-options',
						'wf-unified-live-traffic-options',
					);
					
					echo wfView::create('options/options-title', array(
						'title' => __('All Options', 'wordfence'),
						'stateKeys' => $stateKeys,
						'showIcon' => true,
					))->render();
					?>
					
					<p><?php esc_html_e('These options are also available throughout the plugin pages, in the relevant sections. This page is provided for easier setup for experienced Wordfence users.', 'wordfence'); ?></p>
					
					<?php
					echo wfView::create('common/section-subtitle', array(
						'title' => __('Wordfence Global Options', 'wordfence'),
						'showIcon' => false,
					))->render();
					
					echo wfView::create('dashboard/options-group-license', array(
						'stateKey' => 'wf-unified-global-options-license',
					))->render();
					
					echo wfView::create('dashboard/options-group-view-customization', array(
						'stateKey' => 'wf-unified-global-options-view-customization',
					))->render();
					
					echo wfView::create('dashboard/options-group-general', array(
						'stateKey' => 'wf-unified-global-options-general',
					))->render();
					
					echo wfView::create('dashboard/options-group-dashboard', array(
						'stateKey' => 'wf-unified-global-options-dashboard',
					))->render();
					
					echo wfView::create('dashboard/options-group-alert', array(
						'stateKey' => 'wf-unified-global-options-alert',
					))->render();
					
					echo wfView::create('dashboard/options-group-email-summary', array(
						'stateKey' => 'wf-unified-global-options-email-summary',
					))->render();
					?>
					
					<?php
					echo wfView::create('common/section-subtitle', array(
						'title' => __('Firewall Options', 'wordfence'),
						'showIcon' => false,
					))->render();
					
					echo wfView::create('waf/options-group-basic-firewall', array(
						'firewall' => $firewall,
						'waf' => $waf,
						'stateKey' => 'wf-unified-waf-options-basic',
					))->render();
					
					echo wfView::create('waf/options-group-advanced-firewall', array(
						'firewall' => $firewall,
						'waf' => $waf,
						'stateKey' => 'wf-unified-waf-options-advanced',
					))->render();
					
					echo wfView::create('waf/options-group-brute-force', array(
						'firewall' => $firewall,
						'waf' => $waf,
						'stateKey' => 'wf-unified-waf-options-bruteforce',
					))->render();
					
					echo wfView::create('waf/options-group-rate-limiting', array(
						'firewall' => $firewall,
						'waf' => $waf,
						'stateKey' => 'wf-unified-waf-options-ratelimiting',
					))->render();
					
					echo wfView::create('waf/options-group-whitelisted', array(
						'firewall' => $firewall,
						'waf' => $waf,
						'stateKey' => 'wf-unified-waf-options-whitelisted',
					))->render();
					?>

					<?php
					echo wfView::create('common/section-subtitle', array(
						'title' => __('Blocking Options', 'wordfence'),
						'showIcon' => false,
					))->render();
					
					echo wfView::create('blocking/options-group-advanced-country', array(
						'stateKey' => 'wf-unified-blocking-options-country',
					))->render();
					?>
					
					<?php
					echo wfView::create('common/section-subtitle', array(
						'title' => __('Scan Options', 'wordfence'),
						'showIcon' => false,
					))->render();
					
					echo wfView::create('scanner/options-group-scan-schedule', array(
						'scanner' => $scanner,
						'stateKey' => 'wf-unified-scanner-options-schedule',
					))->render();
					
					echo wfView::create('scanner/options-group-basic', array(
						'scanner' => $scanner,
						'stateKey' => 'wf-unified-scanner-options-basic',
					))->render();
					
					echo wfView::create('scanner/options-group-general', array(
						'scanner' => $scanner,
						'stateKey' => 'wf-unified-scanner-options-general',
					))->render();
					
					echo wfView::create('scanner/options-group-performance', array(
						'scanner' => $scanner,
						'stateKey' => 'wf-unified-scanner-options-performance',
					))->render();
					
					echo wfView::create('scanner/options-group-advanced', array(
						'scanner' => $scanner,
						'stateKey' => 'wf-unified-scanner-options-custom',
					))->render();
					?>

					<?php
					echo wfView::create('common/section-subtitle', array(
						'title' => __('Tool Options', 'wordfence'),
						'showIcon' => false,
					))->render();
					
					if (wfCredentialsController::useLegacy2FA()) {
						echo wfView::create('tools/options-group-2fa', array(
							'stateKey' => 'wf-unified-2fa-options',
						))->render();
					}
					
					echo wfView::create('tools/options-group-live-traffic', array(
						'stateKey' => 'wf-unified-live-traffic-options',
						'hideShowMenuItem' => true,
					))->render();
					?>

					<div class="wf-row">
						<div class="wf-col-xs-12">
							<div class="wf-block wf-always-active" data-persistence-key="">
								<div class="wf-block-header">
									<div class="wf-block-header-content">
										<div class="wf-block-title">
											<strong><?php esc_html_e('Import/Export Options', 'wordfence'); ?></strong>
										</div>
									</div>
								</div>
								<div class="wf-block-content">
									<ul class="wf-block-list">
										<li>
											<ul class="wf-flex-horizontal wf-flex-vertical-xs wf-flex-full-width wf-add-top wf-add-bottom">
												<li><?php esc_html_e('Importing and exporting of options is available on the Tools page', 'wordfence'); ?></li>
												<li class="wf-right wf-left-xs wf-padding-add-top-xs-small">
													<a href="<?php echo esc_url(network_admin_url('admin.php?page=WordfenceTools&subpage=importexport')); ?>" class="wf-btn wf-btn-primary wf-btn-callout-subtle" id="wf-export-options"><?php esc_html_e('Import/Export Options', 'wordfence'); ?></a>
												</li>
											</ul>
											<input type="hidden" id="wf-option-exportOptions">
											<input type="hidden" id="wf-option-importOptions">
										</li>
									</ul>
								</div>
							</div>
						</div>
					</div> <!-- end import options -->
					<?php
					$moduleOptionBlocks = wfModuleController::shared()->optionBlocks;
					foreach ($moduleOptionBlocks as $b) {
						echo $b;
					}
					?>
				</div> <!-- end options block -->
			</div> <!-- end content block -->
		</div> <!-- end row -->
	</div> <!-- end container -->
</div>
