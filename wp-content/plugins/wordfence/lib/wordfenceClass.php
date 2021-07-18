<?php
require_once(dirname(__FILE__) . '/wordfenceConstants.php');
require_once(dirname(__FILE__) . '/wfScanEngine.php');
require_once(dirname(__FILE__) . '/wfScan.php');
require_once(dirname(__FILE__) . '/wfCrawl.php');
require_once(dirname(__FILE__) . '/Diff.php');
require_once(dirname(__FILE__) . '/Diff/Renderer/Html/SideBySide.php');
require_once(dirname(__FILE__) . '/wfAPI.php');
require_once(dirname(__FILE__) . '/wfIssues.php');
require_once(dirname(__FILE__) . '/wfDB.php');
require_once(dirname(__FILE__) . '/wfUtils.php');
require_once(dirname(__FILE__) . '/wfLog.php');
require_once(dirname(__FILE__) . '/wfConfig.php');
require_once(dirname(__FILE__) . '/wfSchema.php');
require_once(dirname(__FILE__) . '/wfCache.php');
require_once(dirname(__FILE__) . '/wfCrypt.php');
require_once(dirname(__FILE__) . '/wfMD5BloomFilter.php');
require_once(dirname(__FILE__) . '/wfView.php');
require_once(dirname(__FILE__) . '/wfHelperString.php');
require_once(dirname(__FILE__) . '/wfDirectoryIterator.php');
require_once(dirname(__FILE__) . '/wfUpdateCheck.php');
require_once(dirname(__FILE__) . '/wfActivityReport.php');
require_once(dirname(__FILE__) . '/wfHelperBin.php');
require_once(dirname(__FILE__) . '/wfDiagnostic.php');
require_once(dirname(__FILE__) . '/wfStyle.php');
require_once(dirname(__FILE__) . '/wfDashboard.php');
require_once(dirname(__FILE__) . '/wfNotification.php');

require_once(dirname(__FILE__) . '/../models/page/wfPage.php');
require_once(dirname(__FILE__) . '/../models/common/wfTab.php');
require_once(dirname(__FILE__) . '/../models/block/wfBlock.php');
require_once(dirname(__FILE__) . '/../models/block/wfRateLimit.php');
require_once(dirname(__FILE__) . '/../models/firewall/wfFirewall.php');
require_once(dirname(__FILE__) . '/../models/scanner/wfScanner.php');
require_once(dirname(__FILE__) . '/wfPersistenceController.php');
require_once(dirname(__FILE__) . '/wfImportExportController.php');
require_once(dirname(__FILE__) . '/wfOnboardingController.php');
require_once(dirname(__FILE__) . '/wfSupportController.php');
require_once(dirname(__FILE__) . '/wfCredentialsController.php');
require_once(dirname(__FILE__) . '/wfVersionCheckController.php');
require_once(dirname(__FILE__) . '/wfDateLocalization.php');
require_once(dirname(__FILE__) . '/wfAdminNoticeQueue.php');
require_once(dirname(__FILE__) . '/wfModuleController.php');
require_once(dirname(__FILE__) . '/wfAlerts.php');

if (version_compare(phpversion(), '5.3', '>=')) {
	require_once(dirname(__FILE__) . '/WFLSPHP52Compatability.php');
	define('WORDFENCE_USE_LEGACY_2FA', wfCredentialsController::useLegacy2FA());
	$wfCoreLoading = true;
	require(dirname(__FILE__) . '/../modules/login-security/wordfence-login-security.php');	
}

require_once(dirname(__FILE__) . '/wfJWT.php');
require_once(dirname(__FILE__) . '/wfCentralAPI.php');

if (class_exists('WP_REST_Users_Controller')) { //WP 4.7+
	require_once(dirname(__FILE__) . '/wfRESTAPI.php');
}
if (wfCentral::isSupported()) { //WP 4.4.0+
	require_once(dirname(__FILE__) . '/rest-api/wfRESTAuthenticationController.php');
	require_once(dirname(__FILE__) . '/rest-api/wfRESTConfigController.php');
	require_once(dirname(__FILE__) . '/rest-api/wfRESTScanController.php');
}

class wordfence {
	public static $printStatus = false;
	public static $wordfence_wp_version = false;
	/**
	 * @var WP_Error
	 */
	public static $authError;
	private static $passwordCodePattern = '/\s+wf([a-z0-9 ]+)$/i'; 
	protected static $lastURLError = false;
	protected static $curlContent = "";
	protected static $curlDataWritten = 0;
	protected static $hasher = '';
	protected static $itoa64 = './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
	protected static $ignoreList = false;
	private static $wfLog = false;
	private static $hitID = 0;
	private static $debugOn = null;
	private static $runInstallCalled = false;
	private static $userDat = false;

	const ATTACK_DATA_BODY_LIMIT=41943040; //40MB

	public static function installPlugin(){
		self::runInstall();
		
		if (get_current_user_id() > 0) {
			wfConfig::set('activatingIP', wfUtils::getIP());
		}
		
		//Used by MU code below
		update_option('wordfenceActivated', 1);
		
		if (defined('WORDFENCE_LS_FROM_CORE') && WORDFENCE_LS_FROM_CORE) {
			WFLSPHP52Compatability::install_plugin();
		}
	}
	public static function uninstallPlugin(){
		//Send admin alert
		$currentUser = wp_get_current_user();
		$username = $currentUser->user_login;
		$alertCallback = array(new wfWordfenceDeactivatedAlert($username, wfUtils::getIP()), 'send');
		do_action('wordfence_security_event', 'wordfenceDeactivated', array(
			'username' => $username,
			'ip' => wfUtils::getIP(),
		), $alertCallback);
		
		//Check if caching is enabled and if it is, disable it and fix the .htaccess file.
		wfCache::removeCaching();

		//Used by MU code below
		update_option('wordfenceActivated', 0);
		wp_clear_scheduled_hook('wordfence_daily_cron');
		wp_clear_scheduled_hook('wordfence_hourly_cron');
		wp_clear_scheduled_hook('wordfence_daily_autoUpdate');

		//Remove old legacy cron job if it exists
		wp_clear_scheduled_hook('wordfence_scheduled_scan');

		//Remove all scheduled scans.
		wfScanner::shared()->unscheduleAllScans();

		// Remove cron for email summary
		wfActivityReport::clearCronJobs();

		// Remove the admin user list so it can be regenerated if Wordfence is reactivated.
		wfConfig::set_ser('adminUserList', false);

		if (!WFWAF_SUBDIRECTORY_INSTALL) {
			wfWAFConfig::set('wafDisabled', true);
		}

		if(wfConfig::get('deleteTablesOnDeact')){
			if (wfCentral::isSupported() && wfCentral::isConnected()) {
				self::ajax_wfcentral_disconnect_callback();
			}

			wfConfig::updateTableExists(false);
			$schema = new wfSchema();
			$schema->dropAll();
			foreach(array('wordfence_version', 'wordfenceActivated', wfSchema::TABLE_CASE_OPTION) as $opt) {
				if (is_multisite() && function_exists('delete_network_option')) {
					delete_network_option(null, $opt);
				}
				delete_option($opt);
			}

			if (!WFWAF_SUBDIRECTORY_INSTALL) {
				try {
					if (WFWAF_AUTO_PREPEND) {
						$helper = new wfWAFAutoPrependHelper();
						if ($helper->uninstall()) {
							wfWAF::getInstance()->uninstall();
						}
					} else {
						wfWAF::getInstance()->uninstall();
					}
				} catch (wfWAFStorageFileException $e) {
					error_log($e->getMessage());
				} catch (wfWAFStorageEngineMySQLiException $e) {
					error_log($e->getMessage());
				}
			}
		}
		
		if (defined('WORDFENCE_LS_FROM_CORE') && WORDFENCE_LS_FROM_CORE) {
			WFLSPHP52Compatability::uninstall_plugin();
		}
	}
	public static function hourlyCron() {
		wfLog::trimHumanCache();
		
		wfRateLimit::trimData();
		
		wfVersionCheckController::shared()->checkVersionsAndWarn();
	}
	private static function keyAlert($msg){
		self::alert($msg, $msg . " " . __("To ensure uninterrupted Premium Wordfence protection on your site,\nplease renew your license by visiting http://www.wordfence.com/ Sign in, go to your dashboard,\nselect the license about to expire and click the button to renew that license.", 'wordfence'), false);
	}
	public static function dailyCron() {
		$lastDailyCron = (int) wfConfig::get('lastDailyCron', 0);
		if (($lastDailyCron + 43200) > time()) { //Run no more frequently than every 12 hours
			return;
		}
		
		wfConfig::set('lastDailyCron', time());
		
		global $wpdb;
		$version = $wpdb->get_var("SELECT VERSION()");
		wfConfig::set('dbVersion', $version);
		
		$api = new wfAPI(wfConfig::get('apiKey'), wfUtils::getWPVersion());
		try {
			$keyType = wfAPI::KEY_TYPE_FREE;
			$keyData = $api->call('ping_api_key', array(), array('supportHash' => wfConfig::get('supportHash', ''), 'whitelistHash' => wfConfig::get('whitelistHash', ''), 'tldlistHash' => wfConfig::get('tldlistHash', '')));
			if (isset($keyData['_isPaidKey'])) {
				$keyType = wfConfig::get('keyType');
			}
			
			if(isset($keyData['_isPaidKey']) && $keyData['_isPaidKey']){
				$keyExpDays = $keyData['_keyExpDays'];
				$keyIsExpired = $keyData['_expired'];
				if (!empty($keyData['_autoRenew'])) {
					if ($keyExpDays > 12) {
						wfConfig::set('keyAutoRenew10Sent', '');
					} else if ($keyExpDays <= 12 && $keyExpDays > 0 && !wfConfig::get('keyAutoRenew10Sent')) {
						wfConfig::set('keyAutoRenew10Sent', 1);
						$email = __("Your Premium Wordfence License is set to auto-renew in 10 days.", 'wordfence');
						self::alert($email, $email . " " . __("To update your license settings please visit http://www.wordfence.com/zz9/dashboard", 'wordfence'), false);
					}
				} else {
					if($keyExpDays > 15){
						wfConfig::set('keyExp15Sent', '');
						wfConfig::set('keyExp7Sent', '');
						wfConfig::set('keyExp2Sent', '');
						wfConfig::set('keyExp1Sent', '');
						wfConfig::set('keyExpFinalSent', '');
					} else if($keyExpDays <= 15 && $keyExpDays > 0){
						if($keyExpDays <= 15 && $keyExpDays >= 11 && (! wfConfig::get('keyExp15Sent'))){
							wfConfig::set('keyExp15Sent', 1);
							self::keyAlert(__("Your Premium Wordfence License expires in less than 2 weeks.", 'wordfence'));
						} else if($keyExpDays <= 7 && $keyExpDays >= 4 && (! wfConfig::get('keyExp7Sent'))){
							wfConfig::set('keyExp7Sent', 1);
							self::keyAlert(__("Your Premium Wordfence License expires in less than a week.", 'wordfence'));
						} else if($keyExpDays == 2 && (! wfConfig::get('keyExp2Sent'))){
							wfConfig::set('keyExp2Sent', 1);
							self::keyAlert(__("Your Premium Wordfence License expires in 2 days.", 'wordfence'));
						} else if($keyExpDays == 1 && (! wfConfig::get('keyExp1Sent'))){
							wfConfig::set('keyExp1Sent', 1);
							self::keyAlert(__("Your Premium Wordfence License expires in 1 day.", 'wordfence'));
						}
					} else if($keyIsExpired && (! wfConfig::get('keyExpFinalSent')) ){
						wfConfig::set('keyExpFinalSent', 1);
						self::keyAlert(__("Your Wordfence Premium License has Expired!", 'wordfence'));
					}
				}
			}
			if (isset($keyData['dashboard'])) {
				wfConfig::set('lastDashboardCheck', time());
				wfDashboard::processDashboardResponse($keyData['dashboard']);
			}
			if (isset($keyData['support']) && isset($keyData['supportHash'])) {
				wfConfig::set('supportContent', $keyData['support']);
				wfConfig::set('supportHash', $keyData['supportHash']);
			}
			if (isset($keyData['_whitelist']) && isset($keyData['_whitelistHash'])) {
				wfConfig::setJSON('whitelistPresets', $keyData['_whitelist']);
				wfConfig::set('whitelistHash', $keyData['_whitelistHash']);
			}
			if (isset($keyData['_tldlist']) && isset($keyData['_tldlistHash'])) {
				wfConfig::set('tldlist', $keyData['_tldlist']);
				wfConfig::set('tldlistHash', $keyData['_tldlistHash']);
			}
			if (isset($keyData['scanSchedule']) && is_array($keyData['scanSchedule'])) {
				wfConfig::set_ser('noc1ScanSchedule', $keyData['scanSchedule']);
				if (wfScanner::shared()->schedulingMode() == wfScanner::SCAN_SCHEDULING_MODE_AUTOMATIC) {
					wfScanner::shared()->scheduleScans();
				}
			}
			if (isset($keyData['showWfCentralUI'])) {
				wfConfig::set('showWfCentralUI', (int) $keyData['showWfCentralUI']);
			}

			if (isset($keyData['_keyNoLongerValid']) && $keyData['_keyNoLongerValid'] == 1) {
				self::alert(__("The Wordfence Premium License in use on this site has been removed from your account.", 'wordfence'), __("The license you were using has been removed from your account. Please reach out to billing@wordfence.com or create a Premium support case at https://support.wordfence.com/support/tickets for more information. Our staff is happy to help.", 'wordfence'), false);
			}

			wfConfig::set('keyType', $keyType);
		}
		catch(Exception $e){
			wordfence::status(4, 'error', sprintf(/* translators: Wordfence license key. */ __("Could not verify Wordfence License: %s", 'wordfence'), $e->getMessage()));
		}
		
		$allowMySQLi = wfConfig::testDB();
		wfConfig::set('allowMySQLi', $allowMySQLi);

		$wfdb = new wfDB();
		
		$table_wfLocs = wfDB::networkTable('wfLocs');
		$wfdb->queryWrite("delete from {$table_wfLocs} where ctime < unix_timestamp() - %d", WORDFENCE_MAX_IPLOC_AGE);
		
		wfBlock::vacuum();
		
		$table_wfCrawlers = wfDB::networkTable('wfCrawlers');
		$wfdb->queryWrite("delete from {$table_wfCrawlers} where lastUpdate < unix_timestamp() - (86400 * 7)");

		self::trimWfHits(true);

		$maxRows = absint(wfConfig::get('liveTraf_maxRows', 2000));; //affects stuff further down too
		
		$table_wfLogins = wfDB::networkTable('wfLogins');
		$count2 = $wfdb->querySingle("select count(*) as cnt from {$table_wfLogins}");
		if($count2 > 20000){
			$wfdb->truncate($table_wfLogins); //in case of Dos
		} else if($count2 > $maxRows){
			$wfdb->queryWrite("delete from {$table_wfLogins} order by ctime asc limit %d", ($count2 - $maxRows));
		}
		
		$table_wfReverseCache = wfDB::networkTable('wfReverseCache');
		$wfdb->queryWrite("delete from {$table_wfReverseCache} where unix_timestamp() - lastUpdate > 86400");
		
		$table_wfStatus = wfDB::networkTable('wfStatus');
		$count4 = $wfdb->querySingle("select count(*) as cnt from {$table_wfStatus}");
		if($count4 > 100000){
			$wfdb->truncate($table_wfStatus);
		} else if($count4 > 1000){ //max status events we keep. This determines how much gets emailed to us when users sends us a debug report.
			$wfdb->queryWrite("delete from {$table_wfStatus} where level != 10 order by ctime asc limit %d", ($count4 - 1000));
			$count5 = $wfdb->querySingle("select count(*) as cnt from {$table_wfStatus} where level=10");
			if($count5 > 100){
				$wfdb->queryWrite("delete from {$table_wfStatus} where level = 10 order by ctime asc limit %d", ($count5 - 100) );
			}
		}
		
		self::_refreshVulnerabilityCache();

		$report = new wfActivityReport();
		$report->rotateIPLog();
		self::_refreshUpdateNotification($report, true);
		
		$next = self::getNextScanStartTimestamp();
		if ($next - time() > 3600 && wfConfig::get('scheduledScansEnabled')) {
			wfScanEngine::startScan(false, wfScanner::SCAN_TYPE_QUICK);
		}

		wfUpdateCheck::syncAllVersionInfo();

		wfConfig::remove('lastPermissionsTemplateCheck');
	}
	public static function _scheduleRefreshUpdateNotification($upgrader = null, $options = null) {
		$defer = false;
		if (is_array($options) && isset($options['type']) && $options['type'] == 'core') {
			$defer = true;
			set_site_transient('wordfence_updating_notifications', true, 600);
		}
		
		if ($defer) {
			wp_schedule_single_event(time(), 'wordfence_refreshUpdateNotification');
		}
		else {
			self::_refreshUpdateNotification();
		}
	}
	public static function _refreshUpdateNotification($report = null, $useCachedValued = false) {
		if ($report === null) {
			$report = new wfActivityReport();
		}
		
		$updatesNeeded = $report->getUpdatesNeeded($useCachedValued);
		if ($updatesNeeded) {
			$items = array();
			$plural = false;
			if ($updatesNeeded['core']) {
				$items[] = sprintf(/* translators: WordPress version. */ __('WordPress (v%s)', 'wordfence'), esc_html($updatesNeeded['core']));
			}
			
			if ($updatesNeeded['plugins']) {
				$entry = sprintf(/* translators: Number of plugins. */ _n('%d plugin', '%d plugins', count($updatesNeeded['plugins']), 'wordfence'), count($updatesNeeded['plugins']));
				$items[] = $entry;
			}
			
			if ($updatesNeeded['themes']) {
				$entry = sprintf(/* translators: Number of themes. */ _n('%d theme', '%d themes', count($updatesNeeded['themes']), 'wordfence'), count($updatesNeeded['themes']));
				$items[] = $entry;
			}
			
			$message = _n('An update is available for ', 'Updates are available for ', count($items), 'wordfence');

			for ($i = 0; $i < count($items); $i++) {
				if ($i > 0 && count($items) > 2) { $message .= ', '; }
				else if ($i > 0) { $message .= ' '; }
				if ($i > 0 && $i == count($items) - 1) { $message .= __('and ', 'wordfence'); }
				$message .= $items[$i];
			}
			
			new wfNotification(null, wfNotification::PRIORITY_HIGH_WARNING, '<a href="' . wfUtils::wpAdminURL('update-core.php') . '">' . $message . '</a>', 'wfplugin_updates');
		}
		else {
			$n = wfNotification::getNotificationForCategory('wfplugin_updates');
			if ($n !== null) {
				$n->markAsRead();
			}
		}
		
		$i = new wfIssues();
		$i->reconcileUpgradeIssues($report, true);
		
		wp_schedule_single_event(time(), 'wordfence_completeCoreUpdateNotification');
	}
	public static function _completeCoreUpdateNotification() {
		//This approach is here because WP Core updates run in a different sequence than plugin/theme updates, so we have to defer the running of the notification update sequence by an extra page load
		delete_site_transient('wordfence_updating_notifications');
		
		wfVersionCheckController::shared()->checkVersionsAndWarn();
	}
	public static function runInstall(){
		if(self::$runInstallCalled){ return; }
		self::$runInstallCalled = true;
		if (function_exists('ignore_user_abort')) {
			@ignore_user_abort(true);
		}
		if (!defined('DONOTCACHEDB')) { define('DONOTCACHEDB', true); }
		$previous_version = ((is_multisite() && function_exists('get_network_option')) ? get_network_option(null, 'wordfence_version', '0.0.0') : get_option('wordfence_version', '0.0.0'));
		if (is_multisite() && function_exists('update_network_option')) {
			update_network_option(null, 'wordfence_version', WORDFENCE_VERSION); //In case we have a fatal error we don't want to keep running install.	
		}
		else {
			update_option('wordfence_version', WORDFENCE_VERSION); //In case we have a fatal error we don't want to keep running install.
		}
		
		wordfence::status(4, 'info', sprintf(/* translators: Wordfence version. */ __('`runInstall` called with previous version = %s', 'wordfence'), $previous_version));
		
		//EVERYTHING HERE MUST BE IDEMPOTENT

		//Remove old legacy cron job if exists
		wp_clear_scheduled_hook('wordfence_scheduled_scan');

		wfSchema::updateTableCase();
		$schema = new wfSchema();
		$schema->createAll(); //if not exists
		wfConfig::updateTableExists(true);
		
		/** @var wpdb $wpdb */
		global $wpdb;
		
		//6.1.15
		$configTable = wfDB::networkTable('wfConfig');
		$hasAutoload = $wpdb->get_col($wpdb->prepare(<<<SQL
SELECT * FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA=DATABASE()
AND COLUMN_NAME='autoload'
AND TABLE_NAME=%s
SQL
			, $configTable));
		if (!$hasAutoload) {
			$wpdb->query("ALTER TABLE {$configTable} ADD COLUMN autoload ENUM('no', 'yes') NOT NULL DEFAULT 'yes'");
			$wpdb->query("UPDATE {$configTable} SET autoload = 'no' WHERE name = 'wfsd_engine' OR name LIKE 'wordfence_chunked_%'");
		}

		$wpdb->query("DELETE FROM $configTable WHERE `name` = 'emailedIssuesList' AND LENGTH(`val`) > 2 * 1024 * 1024");
		wfConfig::setDefaults(); //If not set

		$restOfSite = wfConfig::get('cbl_restOfSiteBlocked', 'notset');
		if($restOfSite == 'notset'){
			wfConfig::set('cbl_restOfSiteBlocked', '1');
		}

		if(wfConfig::get('autoUpdate') == '1'){
			wfConfig::enableAutoUpdate(); //Sets up the cron
		}

		$freshAPIKey = false;
		if(! wfConfig::get('apiKey')){
			$api = new wfAPI('', wfUtils::getWPVersion());
			try {
				$keyData = $api->call('get_anon_api_key');
				if($keyData['ok'] && $keyData['apiKey']){
					wfConfig::set('apiKey', $keyData['apiKey']);
					wfConfig::set('keyType', wfAPI::KEY_TYPE_FREE);
					wfConfig::set('touppPromptNeeded', true);
					$freshAPIKey = true;
				} else {
					throw new Exception(__("Could not understand the response we received from the Wordfence servers when applying for a free license key.", 'wordfence'));
				}
			} catch(Exception $e){
				error_log("Could not fetch free license key from Wordfence: " . $e->getMessage());
				return;
			}
		}
		wp_clear_scheduled_hook('wordfence_daily_cron');
		wp_clear_scheduled_hook('wordfence_hourly_cron');
		if (is_main_site()) {
			wfConfig::remove('lastDailyCron');
			wp_schedule_event(time() + 15, 'daily', 'wordfence_daily_cron'); //'daily'
			wp_schedule_event(time() + 15, 'hourly', 'wordfence_hourly_cron');
		}

		$db = new wfDB();

		// IPv6 schema changes for 6.0.1
		$tables_with_ips = array(
			'wfCrawlers',
			'wfBadLeechers',
			'wfBlockedIPLog',
			'wfBlocks', //Removed in 7.0.1 but left in in case migrating from really old
			'wfHits',
			'wfLocs',
			'wfLogins',
			'wfReverseCache',
		);

		foreach ($tables_with_ips as $ip_table) {
			$ptable = wfDB::networkTable($ip_table);
			$tableExists = $wpdb->get_col($wpdb->prepare(<<<SQL
SELECT TABLE_NAME FROM information_schema.TABLES
WHERE TABLE_SCHEMA=DATABASE()
AND TABLE_NAME=%s
SQL
				, $ptable));
			if (!$tableExists) {
				continue;
			}
			
			$result = $wpdb->get_row("SHOW FIELDS FROM {$ptable} where field = 'IP'");
			if (!$result || strtolower($result->Type) == 'binary(16)') {
				continue;
			}

			$db->queryWriteIgnoreError("ALTER TABLE {$ptable} MODIFY IP BINARY(16)");

			// Just to be sure we don't corrupt the data if the alter fails.
			$result = $wpdb->get_row("SHOW FIELDS FROM {$ptable} where field = 'IP'");
			if (!$result || strtolower($result->Type) != 'binary(16)') {
				continue;
			}
			$db->queryWriteIgnoreError("UPDATE {$ptable} SET IP = CONCAT(LPAD(CHAR(0xff, 0xff), 12, CHAR(0)), LPAD(
	CHAR(
		CAST(IP as UNSIGNED) >> 24 & 0xFF,
		CAST(IP as UNSIGNED) >> 16 & 0xFF,
		CAST(IP as UNSIGNED) >> 8 & 0xFF,
		CAST(IP as UNSIGNED) & 0xFF
	),
	4,
	CHAR(0)
))");
		}

		//Country reassignment moved to the GeoIP file sync segment

		if (wfConfig::get('other_hideWPVersion')) {
			wfUtils::hideReadme();
		}

		$colsFor610 = array(
			'attackLogTime'     => '`attackLogTime` double(17,6) unsigned NOT NULL AFTER `id`',
			'statusCode'        => '`statusCode` int(11) NOT NULL DEFAULT 0 AFTER `jsRun`',
			'action'            => "`action` varchar(64) NOT NULL DEFAULT '' AFTER `UA`",
			'actionDescription' => '`actionDescription` text AFTER `action`',
			'actionData'        => '`actionData` text AFTER `actionDescription`',
		);

		$hitTable = wfDB::networkTable('wfHits');
		foreach ($colsFor610 as $col => $colDefintion) {
			$count = $wpdb->get_col($wpdb->prepare(<<<SQL
SELECT * FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA=DATABASE()
AND COLUMN_NAME=%s
AND TABLE_NAME=%s
SQL
				, $col, $hitTable));
			if (!$count) {
				$wpdb->query("ALTER TABLE $hitTable ADD COLUMN $colDefintion");
			}
		}

		$has404 = $wpdb->get_col($wpdb->prepare(<<<SQL
SELECT * FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA=DATABASE()
AND COLUMN_NAME='is404'
AND TABLE_NAME=%s
SQL
			, $hitTable));
		if ($has404) {
			$wpdb->query(<<<SQL
UPDATE $hitTable
SET statusCode= CASE
WHEN is404=1 THEN 404
ELSE 200
END
SQL
			);

			$wpdb->query("ALTER TABLE $hitTable DROP COLUMN `is404`");
		}

		$loginsTable = wfDB::networkTable('wfLogins');
		$hasHitID = $wpdb->get_col($wpdb->prepare(<<<SQL
SELECT * FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA=DATABASE()
AND COLUMN_NAME='hitID'
AND TABLE_NAME=%s
SQL
			, $loginsTable));
		if (!$hasHitID) {
			$wpdb->query("ALTER TABLE $loginsTable ADD COLUMN hitID int(11) DEFAULT NULL AFTER `id`, ADD INDEX(hitID)");
		}

		if (!WFWAF_SUBDIRECTORY_INSTALL) {
			wfWAFConfig::set('wafDisabled', false);
		}

		// Call this before creating the index in cases where the wp-cron isn't running.
		self::trimWfHits(true);
		$hitsTable = wfDB::networkTable('wfHits');
		$hasAttackLogTimeIndex = $wpdb->get_var($wpdb->prepare(<<<SQL
SELECT COLUMN_KEY FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
AND TABLE_NAME = %s
AND COLUMN_NAME = 'attackLogTime'
SQL
			, $hitsTable));

		if (!$hasAttackLogTimeIndex) {
			$wpdb->query("ALTER TABLE $hitsTable ADD INDEX `attackLogTime` (`attackLogTime`)");
		}
		
		//6.1.16
		$allowed404s = wfConfig::get('allowed404s', '');
		if (!wfConfig::get('allowed404s6116Migration', false)) {
			if (!preg_match('/(?:^|\b)browserconfig\.xml(?:\b|$)/i', $allowed404s)) {
				if (strlen($allowed404s) > 0) {
					$allowed404s .= "\n";
				}
				$allowed404s .= "/browserconfig.xml";
				wfConfig::set('allowed404s', $allowed404s);
			}
			
			wfConfig::set('allowed404s6116Migration', 1);
		}
		if (wfConfig::get('email_summary_interval') == 'biweekly') {
			wfConfig::set('email_summary_interval', 'weekly');
		}
		
		//6.2.0
		wfConfig::migrateCodeExecutionForUploadsPHP7();
		
		//6.2.3
		if (!WFWAF_SUBDIRECTORY_INSTALL && class_exists('wfWAFIPBlocksController')) {
			wfWAFIPBlocksController::setNeedsSynchronizeConfigSettings(); //changed slightly for 7.0.1
		}
		
		//6.2.8
		wfCache::removeCaching();
		
		//6.2.10
		$snipCacheTable = wfDB::networkTable('wfSNIPCache');
		$hasType = $wpdb->get_col($wpdb->prepare(<<<SQL
SELECT * FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA=DATABASE()
AND COLUMN_NAME='type'
AND TABLE_NAME=%s
SQL
			, $snipCacheTable));
		if (!$hasType) {
			$wpdb->query("ALTER TABLE `{$snipCacheTable}` ADD `type` INT  UNSIGNED  NOT NULL  DEFAULT '0'");
			$wpdb->query("ALTER TABLE `{$snipCacheTable}` ADD INDEX (`type`)");
		}
		
		//6.3.5
		$fileModsTable = wfDB::networkTable('wfFileMods');
		$hasStoppedOn = $wpdb->get_col($wpdb->prepare(<<<SQL
SELECT * FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA=DATABASE()
AND COLUMN_NAME='stoppedOnSignature'
AND TABLE_NAME=%s
SQL
			, $fileModsTable));
		if (!$hasStoppedOn) {
			$wpdb->query("ALTER TABLE {$fileModsTable} ADD COLUMN stoppedOnSignature VARCHAR(255) NOT NULL DEFAULT ''");
			$wpdb->query("ALTER TABLE {$fileModsTable} ADD COLUMN stoppedOnPosition INT UNSIGNED NOT NULL DEFAULT '0'");
		}
		
		$blockedIPLogTable = wfDB::networkTable('wfBlockedIPLog');
		$hasType = $wpdb->get_col($wpdb->prepare(<<<SQL
SELECT * FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA=DATABASE()
AND COLUMN_NAME='blockType'
AND TABLE_NAME=%s
SQL
			, $blockedIPLogTable));
		if (!$hasType) {
			$wpdb->query("ALTER TABLE {$blockedIPLogTable} ADD blockType VARCHAR(50) NOT NULL DEFAULT 'generic'");
			$wpdb->query("ALTER TABLE {$blockedIPLogTable} DROP PRIMARY KEY");
			$wpdb->query("ALTER TABLE {$blockedIPLogTable} ADD PRIMARY KEY (IP, unixday, blockType)");
		}
		
		//6.3.6
		if (!wfConfig::get('migration636_email_summary_excluded_directories')) {
			$excluded_directories = explode(',', (string) wfConfig::get('email_summary_excluded_directories'));
			$key = array_search('wp-content/plugins/wordfence/tmp', $excluded_directories); if ($key !== false) { unset($excluded_directories[$key]); }
			$key = array_search('wp-content/wflogs', $excluded_directories); if ($key === false) { $excluded_directories[] = 'wp-content/wflogs'; }
			wfConfig::set('email_summary_excluded_directories', implode(',', $excluded_directories));
			wfConfig::set('migration636_email_summary_excluded_directories', 1, wfConfig::DONT_AUTOLOAD);
		}
    
		$fileModsTable = wfDB::networkTable('wfFileMods');
		$hasSHAC = $wpdb->get_col($wpdb->prepare(<<<SQL
SELECT * FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA=DATABASE()
AND COLUMN_NAME='SHAC'
AND TABLE_NAME=%s
SQL
			, $fileModsTable));
		if (!$hasSHAC) {
			$wpdb->query("ALTER TABLE {$fileModsTable} ADD COLUMN `SHAC` BINARY(32) NOT NULL DEFAULT '' AFTER `newMD5`");
			$wpdb->query("ALTER TABLE {$fileModsTable} ADD COLUMN `isSafeFile` VARCHAR(1) NOT NULL  DEFAULT '?' AFTER `stoppedOnPosition`");
		}
		
		//6.3.7
		$hooverTable = wfDB::networkTable('wfHoover');
		$hostKeySize = $wpdb->get_var($wpdb->prepare(<<<SQL
SELECT CHARACTER_MAXIMUM_LENGTH FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA=DATABASE()
AND COLUMN_NAME='hostKey'
AND TABLE_NAME=%s
SQL
			, $hooverTable));
		if ($hostKeySize < 124) {
			$wpdb->query("ALTER TABLE {$hooverTable} CHANGE `hostKey` `hostKey` VARBINARY(124) NULL DEFAULT NULL");
		}
		
		//6.3.15
		$scanFileContents = wfConfig::get('scansEnabled_fileContents', false);
		if (!wfConfig::get('fileContentsGSB6315Migration', false)) {
			if (!$scanFileContents) {
				wfConfig::set('scansEnabled_fileContentsGSB', false);
			}
			wfConfig::set('fileContentsGSB6315Migration', 1);
		}
		
		//6.3.20
		$lastBlockAggregation = wfConfig::get('lastBlockAggregation', 0);
		if ($lastBlockAggregation == 0) {
			wfConfig::set('lastBlockAggregation', time());
		}
		
		//7.0.1
		//---- Config Migration
		if (!wfConfig::get('config701Migration', false)) {
			//loginSec_strongPasswds gains a toggle
			if (wfConfig::get('loginSec_strongPasswds') == '') {
				wfConfig::set('loginSec_strongPasswds', 'pubs');
				wfConfig::set('loginSec_strongPasswds_enabled', false);
			}
			
			$limitedOptions = wfScanner::limitedScanTypeOptions();
			$standardOptions = wfScanner::standardScanTypeOptions();
			$highSensitivityOptions = wfScanner::highSensitivityScanTypeOptions();
			$settings = wfScanner::customScanTypeOptions();
			if ($settings == $limitedOptions) { wfConfig::set('scanType', wfScanner::SCAN_TYPE_LIMITED); }
			else if ($settings == $standardOptions) { wfConfig::set('scanType', wfScanner::SCAN_TYPE_STANDARD); }
			else if ($settings == $highSensitivityOptions) { wfConfig::set('scanType', wfScanner::SCAN_TYPE_HIGH_SENSITIVITY); }
			else { wfConfig::set('scanType', wfScanner::SCAN_TYPE_CUSTOM); }
			
			if (wfConfig::get('isPaid')) {
				wfConfig::set('keyType', wfAPI::KEY_TYPE_PAID_CURRENT);
			}
			
			wfConfig::remove('premiumAutoRenew');
			wfConfig::remove('premiumNextRenew');
			wfConfig::remove('premiumPaymentExpiring');
			wfConfig::remove('premiumPaymentExpired');
			wfConfig::remove('premiumPaymentMissing');
			wfConfig::remove('premiumPaymentHold');
			
			wfConfig::set('config701Migration', 1);
		}
		
		//---- wfBlocks migration
		$oldBlocksTable = wfDB::networkTable('wfBlocks');
		$blocksTable = wfBlock::blocksTable();
		$oldBlocksExist = $wpdb->get_col($wpdb->prepare(<<<SQL
SELECT TABLE_NAME FROM information_schema.TABLES
WHERE TABLE_SCHEMA=DATABASE()
AND TABLE_NAME=%s
SQL
			, $oldBlocksTable));
		if ($oldBlocksExist && !wfConfig::get('blocks701Migration', false)) {
			//wfBlocks migration
			$query = $wpdb->prepare("INSERT INTO `{$blocksTable}` (`type`, `IP`, `blockedTime`, `reason`, `lastAttempt`, `blockedHits`, `expiration`) SELECT CASE 
WHEN wfsn = 1 AND permanent = 0 THEN %d
WHEN wfsn = 0 AND permanent = 0 THEN %d
WHEN wfsn = 0 AND permanent = 1 THEN %d
END AS `type`, `IP`, `blockedTime`, `reason`, `lastAttempt`, `blockedHits`, CASE 
WHEN wfsn = 1 AND permanent = 0 THEN (`blockedTime` + 600)
WHEN wfsn = 0 AND permanent = 0 THEN (`blockedTime` + %d)
WHEN wfsn = 0 AND permanent = 1 THEN 0
END AS `expiration` FROM `{$oldBlocksTable}`", wfBlock::TYPE_WFSN_TEMPORARY, wfBlock::TYPE_RATE_BLOCK, wfBlock::TYPE_IP_AUTOMATIC_PERMANENT, wfConfig::get('blockedTime'));
			$wpdb->query($query);
			
			//wfBlocksAdv migration
			$advancedBlocksTable = wfDB::networkTable('wfBlocksAdv');
			$advancedBlocks = $wpdb->get_results("SELECT * FROM {$advancedBlocksTable}", ARRAY_A);
			foreach ($advancedBlocks as $b) {
				$blockType = $b['blockType']; //unused
				$blockString = $b['blockString'];
				$ctime = (int) $b['ctime'];
				$reason = $b['reason'];
				$totalBlocked = (int) $b['totalBlocked'];
				$lastBlocked = (int) $b['lastBlocked'];
				
				list($ipRange, $uaRange, $referrer, $hostname) = explode('|', $blockString);
				
				wfBlock::createPattern($reason, $ipRange, $hostname, $uaRange, $referrer, wfBlock::DURATION_FOREVER, $ctime, $lastBlocked, $totalBlocked);
			}
			
			//throttle migration
			$throttleTable = wfDB::networkTable('wfThrottleLog');
			$throttles = $wpdb->get_results("SELECT * FROM {$throttleTable}", ARRAY_A);
			foreach ($throttles as $t) {
				$ip = wfUtils::inet_ntop($t['IP']);
				$startTime = (int) $t['startTime'];
				$endTime = (int) $t['endTime'];
				$timesThrottled = (int) $t['timesThrottled'];
				$reason = $t['lastReason'];
				
				wfBlock::createRateThrottle($reason, $ip, wfBlock::rateLimitThrottleDuration(), $startTime, $endTime, $timesThrottled);
			}
			
			//lockout migration
			$lockoutTable = wfDB::networkTable('wfLockedOut');
			$lockouts = $wpdb->get_results("SELECT * FROM {$lockoutTable}", ARRAY_A);
			foreach ($lockouts as $l) {
				$ip = wfUtils::inet_ntop($l['IP']);
				$blockedTime = (int) $l['blockedTime'];
				$reason = $l['reason'];
				$lastAttempt = (int) $l['lastAttempt'];
				$blockedHits = (int) $l['blockedHits'];
				
				wfBlock::createLockout($reason, $ip, wfBlock::lockoutDuration(), $blockedTime, $lastAttempt, $blockedHits);
			}
			
			//country blocking migration
			$countries = wfConfig::get('cbl_countries', false);
			if ($countries) {
				$countries = explode(',', $countries);
				wfBlock::createCountry(__('Automatically generated from previous country blocking settings', 'wordfence'), wfConfig::get('cbl_loginFormBlocked', false), wfConfig::get('cbl_restOfSiteBlocked', false), $countries);
			}
			
			wfConfig::set('blocks701Migration', 1);
		}
		
		//---- wfIssues/wfPendingIssues Schema Change
		$issuesTable = wfDB::networkTable('wfIssues');
		$pendingIssuesTable = wfDB::networkTable('wfPendingIssues');
		$hasLastUpdated = $wpdb->get_col($wpdb->prepare(<<<SQL
SELECT * FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA=DATABASE()
AND COLUMN_NAME='lastUpdated'
AND TABLE_NAME=%s
SQL
			, $issuesTable));
		if (!$hasLastUpdated) {
			$wpdb->query("ALTER TABLE `{$issuesTable}` ADD `lastUpdated` INT UNSIGNED NOT NULL AFTER `time`");
			$wpdb->query("ALTER TABLE `{$issuesTable}` ADD INDEX (`lastUpdated`)");
			$wpdb->query("ALTER TABLE `{$issuesTable}` ADD INDEX (`status`)");
			$wpdb->query("ALTER TABLE `{$issuesTable}` ADD INDEX (`ignoreP`)");
			$wpdb->query("ALTER TABLE `{$issuesTable}` ADD INDEX (`ignoreC`)");
			$wpdb->query("UPDATE `{$issuesTable}` SET `lastUpdated` = `time` WHERE `lastUpdated` = 0");
			
			$wpdb->query("ALTER TABLE `{$pendingIssuesTable}` ADD `lastUpdated` INT UNSIGNED NOT NULL AFTER `time`");
			$wpdb->query("ALTER TABLE `{$pendingIssuesTable}` ADD INDEX (`lastUpdated`)");
			$wpdb->query("ALTER TABLE `{$pendingIssuesTable}` ADD INDEX (`status`)");
			$wpdb->query("ALTER TABLE `{$pendingIssuesTable}` ADD INDEX (`ignoreP`)");
			$wpdb->query("ALTER TABLE `{$pendingIssuesTable}` ADD INDEX (`ignoreC`)");
		}
		
		//---- Scheduled scan start hour and manual type
		if (wfConfig::get('schedStartHour') < 0) {
			wfConfig::set('schedStartHour', wfWAFUtils::random_int(0, 23));
			
			if (wfConfig::get('schedMode') == 'manual') {
				$sched = wfConfig::get_ser('scanSched', array());
				if (is_array($sched) && is_array($sched[0])) { //Try to determine the closest matching value for manualScanType
					$hours = array_fill(0, 24, 0);
					$distinctHours = array();
					$days = array_fill(0, 7, 0);
					$distinctDays = array();
					foreach ($sched as $dayIndex => $day) {
						foreach ($day as $h => $enabled) {
							if ($enabled) {
								if (in_array($h, $distinctHours)) {
									$distinctHours[] = $h;
								}
								$hours[$h]++;
								if (in_array($dayIndex, $distinctDays)) {
									$distinctDays[] = $dayIndex;
								}
								$days[$dayIndex]++;
							}
						}
					}
					
					sort($distinctHours, SORT_NUMERIC);
					sort($distinctDays, SORT_NUMERIC);
					if (count($distinctDays) == 7) {
						if (count($distinctHours) == 1) {
							wfConfig::set('manualScanType', wfScanner::MANUAL_SCHEDULING_ONCE_DAILY);
							wfConfig::set('schedStartHour', $distinctHours[0]);
						}
						else if (count($distinctHours) == 2) {
							$matchesTwiceDaily = false;
							if ($distinctHours[0] + 12 == $distinctHours[1]) {
								$matchesTwiceDaily = true;
								foreach ($sched as $dayIndex => $day) {
									if (!$day[$distinctHours[0]] || !$day[$distinctHours[1]]) {
										$matchesTwiceDaily = false;
									}
								}
							}
							
							if ($matchesTwiceDaily) {
								wfConfig::set('manualScanType', wfScanner::MANUAL_SCHEDULING_TWICE_DAILY);
								wfConfig::set('schedStartHour', $distinctHours[0]);
							}
							else {
								wfConfig::set('manualScanType', wfScanner::MANUAL_SCHEDULING_CUSTOM);
							}
						}
						else {
							wfConfig::set('manualScanType', wfScanner::MANUAL_SCHEDULING_CUSTOM);
						}
					}
					else if (count($distinctDays) == 5 && count($distinctHours) == 1) {
						if ($days[2] == 0 && $days[4] == 0 && $hours[$distinctHours[0]] == 5) {
							wfConfig::set('manualScanType', wfScanner::MANUAL_SCHEDULING_ODD_DAYS_WEEKENDS);
							wfConfig::set('schedStartHour', $distinctHours[0]);
						}
						else if ($days[0] == 0 && $days[6] == 0 && $hours[$distinctHours[0]] == 5) {
							wfConfig::set('manualScanType', wfScanner::MANUAL_SCHEDULING_WEEKDAYS);
							wfConfig::set('schedStartHour', $distinctHours[0]);
						}
						else {
							wfConfig::set('manualScanType', wfScanner::MANUAL_SCHEDULING_CUSTOM);
						}
					}
					else if (count($distinctDays) == 2 && count($distinctHours) == 1) {
						if ($distinctDays[0] == 0 && $distinctDays[1] == 6 && $hours[$distinctHours[0]] == 2) {
							wfConfig::set('manualScanType', wfScanner::MANUAL_SCHEDULING_WEEKENDS);
							wfConfig::set('schedStartHour', $distinctHours[0]);
						}
						else {
							wfConfig::set('manualScanType', wfScanner::MANUAL_SCHEDULING_CUSTOM);
						}
					}
					else {
						wfConfig::set('manualScanType', wfScanner::MANUAL_SCHEDULING_CUSTOM);
					}
				}
				//manualScanType
			}
		}
		
		//---- Onboarding
		if (!$freshAPIKey) {
			wfOnboardingController::migrateOnboarding();
		}
		
		//7.0.2
		if (!wfConfig::get('blocks702Migration')) {
			$blocksTable = wfBlock::blocksTable();
			
			$query = "UPDATE `{$blocksTable}` SET `type` = %d WHERE `type` = %d AND `parameters` IS NOT NULL AND `parameters` LIKE '%\"ipRange\"%'";
			$wpdb->query($wpdb->prepare($query, wfBlock::TYPE_PATTERN, wfBlock::TYPE_IP_AUTOMATIC_PERMANENT));
			
			$countryBlock = wfBlock::countryBlocks();
			if (!count($countryBlock)) {
				$query = "UPDATE `{$blocksTable}` SET `type` = %d WHERE `type` = %d AND `parameters` IS NOT NULL AND `parameters` LIKE '%\"blockLogin\"%' LIMIT 1";
				$wpdb->query($wpdb->prepare($query, wfBlock::TYPE_COUNTRY, wfBlock::TYPE_IP_AUTOMATIC_PERMANENT));
			}
			
			$query = "DELETE FROM `{$blocksTable}` WHERE `type` = %d AND `parameters` IS NOT NULL AND `parameters` LIKE '%\"blockLogin\"%'";
			$wpdb->query($wpdb->prepare($query, wfBlock::TYPE_IP_AUTOMATIC_PERMANENT));
			
			wfConfig::set('blocks702Migration', 1);
		}
		
		//7.0.3
		/*if (!wfConfig::get('generateAllOptionsNotification')) {
			new wfNotification(null, wfNotification::PRIORITY_HIGH_WARNING, '<p>Developers: If you prefer to edit all Wordfence options on one page, you can enable the "All Options" page here:</p>
<p><a href="javascript:WFAD.enableAllOptionsPage();" class="wf-btn wf-btn-primary wf-btn-callout-subtle">Enable "All Options" Page</a></p>', 'wfplugin_devalloptions');
			wfConfig::set('generateAllOptionsNotification', 1);
		}*/
		
		//7.1.9
		if (wfConfig::get('loginSec_maxFailures') == 1) {
			wfConfig::set('loginSec_maxFailures', 2);
		}
		
		$blocksTable = wfBlock::blocksTable();
		$patternBlocks = wfBlock::patternBlocks();
		foreach ($patternBlocks as $b) {
			if (!empty($b->ipRange) && preg_match('/^\d+\-\d+$/', $b->ipRange)) { //Old-style range block using long2ip
				$ipRange = new wfUserIPRange($b->ipRange);
				$ipRange = $ipRange->getIPString();
				
				$parameters = $b->parameters;
				$parameters['ipRange'] = $ipRange;
				$wpdb->query($wpdb->prepare("UPDATE `{$blocksTable}` SET `parameters` = %s WHERE `id` = %d", json_encode($parameters), $b->id));
			}
		}
		
		wfConfig::set('needsGeoIPSync', true, wfConfig::DONT_AUTOLOAD);

		// Set the default scan options based on scan type.
		if (!wfConfig::get('config720Migration', false)) {
			// Replace critical/warning checkboxes with setting based on numeric severity value.
			if (wfConfig::hasCachedOption('alertOn_critical') && wfConfig::hasCachedOption('alertOn_warnings')) {
				$alertOnCritical = wfConfig::get('alertOn_critical');
				$alertOnWarnings = wfConfig::get('alertOn_warnings');
				wfConfig::set('alertOn_scanIssues', $alertOnCritical || $alertOnWarnings);
				if ($alertOnCritical && ! $alertOnWarnings) {
					wfConfig::set('alertOn_severityLevel', wfIssues::SEVERITY_HIGH);
				} else {
					wfConfig::set('alertOn_severityLevel', wfIssues::SEVERITY_LOW);
				}
			}

			// Update severity for existing issues where they are still using the old severity values.
			foreach (wfIssues::$issueSeverities as $issueType => $severity) {
				$wpdb->query($wpdb->prepare("UPDATE $issuesTable SET severity = %d 
				WHERE `type` = %s
				AND severity in (0,1,2)
				", $severity, $issueType));
			}

			$syncedOptions = array();
			switch (wfConfig::get('scanType')) {
				case wfScanner::SCAN_TYPE_LIMITED:
					$syncedOptions = wfScanner::limitedScanTypeOptions();
					break;
				case wfScanner::SCAN_TYPE_STANDARD:
					$syncedOptions = wfScanner::standardScanTypeOptions();
					break;
				case wfScanner::SCAN_TYPE_HIGH_SENSITIVITY:
					$syncedOptions = wfScanner::highSensitivityScanTypeOptions();
					break;
			}
			if ($syncedOptions) {
				foreach ($syncedOptions as $key => $value) {
					if (is_bool($value)) {
						wfConfig::set($key, $value ? 1 : 0);
					}
				}
			}

			wfConfig::set('config720Migration', true);
		}
		
		//7.2.3
		if (wfConfig::get('waf_status') === false) {
			$firewall = new wfFirewall();
			$firewall->syncStatus(true);
		}
		
		//7.3.1
		//---- drop long deprecated tables
		$tables = array('wfBadLeechers', 'wfBlockedCommentLog', 'wfBlocks', 'wfBlocksAdv', 'wfLeechers', 'wfLockedOut', 'wfNet404s', 'wfScanners', 'wfThrottleLog', 'wfVulnScanners');
		foreach ($tables as $t) {
			$schema->drop($t);
		}
		
		//---- enable legacy 2fa if applicable
		if (wfConfig::get('isPaid') && (wfCredentialsController::hasOld2FARecords() || version_compare(phpversion(), '5.3', '<'))) {
			wfConfig::set(wfCredentialsController::ALLOW_LEGACY_2FA_OPTION, true);
		}

		//Check the How does Wordfence get IPs setting
		wfUtils::requestDetectProxyCallback();
		
		//Install new schedule. If schedule config is blank it will install the default 'auto' schedule.
		wfScanner::shared()->scheduleScans();
		
		//Check our minimum versions and generate the necessary warnings
		if (!wp_next_scheduled('wordfence_version_check')) {
			wp_schedule_single_event(time(), 'wordfence_version_check');
		}

		//Must be the final line
	}
	public static function _refreshVulnerabilityCache($upgrader = null, $hook_extra = null) {
		if($hook_extra ===null || in_array($hook_extra['type'], array('plugin', 'theme'))){
			$update_check = new wfUpdateCheck();
			$update_check->checkAllVulnerabilities();
		}
	}
	private static function doEarlyAccessLogging(){
		$wfLog = self::getLog();
		if($wfLog->logHitOK()){
			$request = $wfLog->getCurrentRequest();

			if(is_404()){
				if ($request) {
					$request->statusCode = 404;
				}
				$wfLog->logLeechAndBlock('404');
			} else {
				$wfLog->logLeechAndBlock('hit');
			}
		}
	}
	public static function initProtection(){ //Basic protection during WAF learning period
		// Infinite WP Client - Authentication Bypass < 1.9.4.5
		// https://wpvulndb.com/vulnerabilities/10011
		$iwpRule = new wfWAFRule(wfWAF::getInstance(), 0x80000000, null, 'auth-bypass', 100, 'Infinite WP Client - Authentication Bypass < 1.9.4.5', 0, 'block', null);
		wfWAF::getInstance()->setRules(wfWAF::getInstance()->getRules() + array(0x80000000 => $iwpRule));

		if (strrpos(wfWAF::getInstance()->getRequest()->getRawBody(), '_IWP_JSON_PREFIX_') !== false) {
			$iwpRequestDataArray = explode('_IWP_JSON_PREFIX_', wfWAF::getInstance()->getRequest()->getRawBody());
			$iwpRequest = json_decode(trim(base64_decode($iwpRequestDataArray[1])), true);
			if (is_array($iwpRequest)) {
				if (array_key_exists('iwp_action', $iwpRequest) &&
					($iwpRequest['iwp_action'] === 'add_site' || $iwpRequest['iwp_action'] === 'readd_site')
				) {
					require_once ABSPATH . '/wp-admin/includes/plugin.php';
					if (is_plugin_active('iwp-client/init.php')) {
						$iwpPluginData = get_plugin_data(WP_PLUGIN_DIR . '/iwp-client/init.php');
						if (version_compare('1.9.4.5', $iwpPluginData['Version'], '>')) {
							remove_action('setup_theme', 'iwp_mmb_set_request');
						}
					}

					if ((is_multisite() ? get_site_option('iwp_client_action_message_id') : get_option('iwp_client_action_message_id')) &&
						(is_multisite() ? get_site_option('iwp_client_public_key') : get_option('iwp_client_public_key'))
					) {
						wfWAF::getInstance()->getStorageEngine()->logAttack(array($iwpRule), 'request.rawBody',
							wfWAF::getInstance()->getRequest()->getRawBody(),
							wfWAF::getInstance()->getRequest(),
							wfWAF::getInstance()->getRequest()->getMetadata()
						);
					}
				}
			}
		}
	}
	public static function install_actions(){
		register_activation_hook(WORDFENCE_FCPATH, 'wordfence::installPlugin');
		register_deactivation_hook(WORDFENCE_FCPATH, 'wordfence::uninstallPlugin');

		$versionInOptions = ((is_multisite() && function_exists('get_network_option')) ? get_network_option(null, 'wordfence_version', false) : get_option('wordfence_version', false));
		if( (! $versionInOptions) || version_compare(WORDFENCE_VERSION, $versionInOptions, '>')){
			//Either there is no version in options or the version in options is greater and we need to run the upgrade
			self::runInstall();
		}
		
		self::getLog()->initLogRequest();
		
		//Fix wp_mail bug when $_SERVER['SERVER_NAME'] is undefined
		add_filter('wp_mail_from', 'wordfence::fixWPMailFromAddress');

		//These access wfConfig::get('apiKey') and will fail if runInstall hasn't executed.
		if(defined('MULTISITE') && MULTISITE === true){
			global $blog_id;
			if($blog_id == 1 && get_option('wordfenceActivated') != 1){ return; } //Because the plugin is active once installed, even before it's network activated, for site 1 (WordPress team, why?!)
		}
		//User may be logged in or not, so register both handlers
		add_action('wp_ajax_nopriv_wordfence_lh', 'wordfence::ajax_lh_callback');
		add_action('wp_ajax_nopriv_wordfence_doScan', 'wordfence::ajax_doScan_callback');
		add_action('wp_ajax_nopriv_wordfence_testAjax', 'wordfence::ajax_testAjax_callback');
		if(wfUtils::hasLoginCookie()){ //may be logged in. Fast way to check. These aren't secure functions, this is just a perf optimization, along with every other use of hasLoginCookie()
			add_action('wp_ajax_wordfence_lh', 'wordfence::ajax_lh_callback');
			add_action('wp_ajax_wordfence_doScan', 'wordfence::ajax_doScan_callback');
			add_action('wp_ajax_wordfence_testAjax', 'wordfence::ajax_testAjax_callback');

			if (is_multisite()) {
				add_action('wp_network_dashboard_setup', 'wordfence::addDashboardWidget');
			} else {
				add_action('wp_dashboard_setup', 'wordfence::addDashboardWidget');
			}
		}
		
		add_action('wp_ajax_wordfence_wafStatus', 'wordfence::ajax_wafStatus_callback');
		add_action('wp_ajax_nopriv_wordfence_wafStatus', 'wordfence::ajax_wafStatus_callback');
		
		add_action('wp_ajax_nopriv_wordfence_remoteVerifySwitchTo2FANew', 'wordfence::ajax_remoteVerifySwitchTo2FANew_callback');

		add_action('wordfence_start_scheduled_scan', 'wordfence::wordfenceStartScheduledScan');
		add_action('wordfence_daily_cron', 'wordfence::dailyCron');
		add_action('wordfence_daily_autoUpdate', 'wfConfig::autoUpdate');
		add_action('wordfence_hourly_cron', 'wordfence::hourlyCron');
		add_action('wordfence_version_check', array(wfVersionCheckController::shared(), 'checkVersionsAndWarn'));
		add_action('plugins_loaded', 'wordfence::veryFirstAction');
		add_action('init', 'wordfence::initAction');
		//add_action('admin_bar_menu', 'wordfence::admin_bar_menu', 99);
		add_action('template_redirect', 'wordfence::templateRedir', 1001);
		add_action('shutdown', 'wordfence::shutdownAction');
		
		if (!wfConfig::get('ajaxWatcherDisabled_front')) {
			add_action('wp_enqueue_scripts', 'wordfence::enqueueAJAXWatcher');
		}
		if (!wfConfig::get('ajaxWatcherDisabled_admin')) {
			add_action('admin_enqueue_scripts', 'wordfence::enqueueAJAXWatcher');
		}
		
		//add_action('wp_enqueue_scripts', 'wordfence::enqueueDashboard');
		add_action('admin_enqueue_scripts', 'wordfence::enqueueDashboard');

		if(version_compare(PHP_VERSION, '5.4.0') >= 0){
			add_action('wp_authenticate','wordfence::authActionNew', 1, 2);
		} else {
			add_action('wp_authenticate','wordfence::authActionOld', 1, 2);
		}
		add_filter('authenticate', 'wordfence::authenticateFilter', 99, 3);
		
		$lockout = wfBlock::lockoutForIP(wfUtils::getIP());
		if ($lockout !== false) {
			add_filter('xmlrpc_enabled', '__return_false');
		}

		add_action('login_init','wordfence::loginInitAction');
		add_action('wp_login','wordfence::loginAction');
		add_action('wp_logout','wordfence::logoutAction');
		add_action('lostpassword_post', 'wordfence::lostPasswordPost', '1');
		
		$allowSeparatePrompt = ini_get('output_buffering') > 0;
		if (wfConfig::get('loginSec_enableSeparateTwoFactor') && $allowSeparatePrompt) {
			add_action('login_form', 'wordfence::showTwoFactorField');
		}
		
		if(wfUtils::hasLoginCookie()){
			add_action('user_profile_update_errors', 'wordfence::validateProfileUpdate', 0, 3 );
			add_action('profile_update', 'wordfence::profileUpdateAction', '99', 2);
		}
		
		add_action('validate_password_reset', 'wordfence::validatePassword', 10, 2);

		// Add actions for the email summary
		add_action('wordfence_email_activity_report', array('wfActivityReport', 'executeCronJob'));

		//For debugging
		//add_filter( 'cron_schedules', 'wordfence::cronAddSchedules' );

		add_filter('wp_redirect', 'wordfence::wpRedirectFilter', 99, 2);
		add_filter('wp_redirect_status', 'wordfence::wpRedirectStatusFilter', 99, 2);
		//html|xhtml|atom|rss2|rdf|comment|export
		if(wfConfig::get('other_hideWPVersion')){
			add_filter('style_loader_src', 'wordfence::replaceVersion');
			add_filter('script_loader_src', 'wordfence::replaceVersion');

			add_action('upgrader_process_complete', 'wordfence::hideReadme');
		}
		add_filter('get_the_generator_html', 'wordfence::genFilter', 99, 2);
		add_filter('get_the_generator_xhtml', 'wordfence::genFilter', 99, 2);
		add_filter('get_the_generator_atom', 'wordfence::genFilter', 99, 2);
		add_filter('get_the_generator_rss2', 'wordfence::genFilter', 99, 2);
		add_filter('get_the_generator_rdf', 'wordfence::genFilter', 99, 2);
		add_filter('get_the_generator_comment', 'wordfence::genFilter', 99, 2);
		add_filter('get_the_generator_export', 'wordfence::genFilter', 99, 2);
		add_filter('registration_errors', 'wordfence::registrationFilter', 99, 3);
		add_filter('woocommerce_new_customer_data', 'wordfence::wooRegistrationFilter', 99, 1);
		
		if (wfConfig::get('loginSec_disableAuthorScan')) {
			add_filter('oembed_response_data', 'wordfence::oembedAuthorFilter', 99, 4);
			add_filter('rest_request_before_callbacks', 'wordfence::jsonAPIAuthorFilter', 99, 3);
			add_filter('rest_post_dispatch', 'wordfence::jsonAPIAdjustHeaders', 99, 3);
			add_filter('wp_sitemaps_users_pre_url_list', '__return_false', 99, 0);
			add_filter('wp_sitemaps_add_provider', 'wordfence::wpSitemapUserProviderFilter', 99, 2);
		}
		
		if (wfConfig::get('loginSec_disableApplicationPasswords')) {
			add_filter('wp_is_application_passwords_available', '__return_false');

			// Override the wp_die handler to let the user know app passwords were disabled by the Wordfence option.
			if (!empty($_SERVER['SCRIPT_FILENAME']) && $_SERVER['SCRIPT_FILENAME'] === ABSPATH . 'wp-admin/authorize-application.php') {
				add_filter('wp_die_handler', function ($handler = null) {
					return function ($message, $title, $args) {
						if ($message === 'Application passwords are not available.') {
							$message = __('Application passwords have been disabled by Wordfence.', 'wordfence');
						}
						_default_wp_die_handler($message, $title, $args);
					};
				}, 10, 1);
			}
		}

		add_filter('rest_dispatch_request', 'wordfence::_filterCentralFromLiveTraffic', 99, 4);

		// Change GoDaddy's limit login mu-plugin since it can interfere with the two factor auth message.
		if (self::hasGDLimitLoginsMUPlugin()) {
			add_action('login_errors', array('wordfence', 'fixGDLimitLoginsErrors'), 11);
		}
		
		add_action('upgrader_process_complete', 'wordfence::_refreshVulnerabilityCache', 10, 2);
		add_action('upgrader_process_complete', 'wfUpdateCheck::syncAllVersionInfo');
		add_action('upgrader_process_complete', 'wordfence::_scheduleRefreshUpdateNotification', 99, 2);
		add_action('automatic_updates_complete', 'wordfence::_scheduleRefreshUpdateNotification', 99, 0);
		add_action('wordfence_refreshUpdateNotification', 'wordfence::_refreshUpdateNotification', 99, 0);
		add_action('wordfence_completeCoreUpdateNotification', 'wordfence::_completeCoreUpdateNotification', 99, 0);
		
		add_action('wfls_xml_rpc_blocked', 'wordfence::checkSecurityNetwork');
		add_action('wfls_registration_blocked', 'wordfence::checkSecurityNetwork');
		add_action('wfls_activation_page_header', 'wordfence::_outputLoginSecurityInstallation');
		add_action('wfls_activation_page_footer', 'wordfence::_outputLoginSecurityTour');
		add_action('wfls_settings_set', 'wordfence::queueCentralConfigurationSync');

		if(is_admin()){
			add_action('admin_init', 'wordfence::admin_init');
			add_action('admin_head', 'wordfence::_retargetWordfenceSubmenuCallout');
			if(is_multisite()){
				if(wfUtils::isAdminPageMU()){
					add_action('network_admin_menu', 'wordfence::admin_menus', 10);
					add_action('network_admin_menu', 'wordfence::admin_menus_20', 20);
					add_action('network_admin_menu', 'wordfence::admin_menus_30', 30);
					add_action('network_admin_menu', 'wordfence::admin_menus_40', 40);
					add_action('network_admin_menu', 'wordfence::admin_menus_50', 50);
					add_action('network_admin_menu', 'wordfence::admin_menus_60', 60);
					add_action('network_admin_menu', 'wordfence::admin_menus_70', 70);
					add_action('network_admin_menu', 'wordfence::admin_menus_80', 80);
					add_action('network_admin_menu', 'wordfence::admin_menus_90', 90);
				} //else don't show menu
			} else {
				add_action('admin_menu', 'wordfence::admin_menus', 10);
				add_action('admin_menu', 'wordfence::admin_menus_20', 20);
				add_action('admin_menu', 'wordfence::admin_menus_30', 30);
				add_action('admin_menu', 'wordfence::admin_menus_40', 40);
				add_action('admin_menu', 'wordfence::admin_menus_50', 50);
				add_action('admin_menu', 'wordfence::admin_menus_60', 60);
				add_action('admin_menu', 'wordfence::admin_menus_70', 70);
				add_action('admin_menu', 'wordfence::admin_menus_80', 80);
				add_action('admin_menu', 'wordfence::admin_menus_90', 90);
			}
			add_filter('plugin_action_links_' . plugin_basename(realpath(dirname(__FILE__) . '/../wordfence.php')), 'wordfence::_pluginPageActionLinks');
		}

		add_action('request', 'wordfence::preventAuthorNScans');
		add_action('password_reset', 'wordfence::actionPasswordReset');

		$adminUsers = new wfAdminUserMonitor();
		if ($adminUsers->isEnabled()) {
			add_action('set_user_role', array($adminUsers, 'updateToUserRole'), 10, 3);
			add_action('grant_super_admin', array($adminUsers, 'grantSuperAdmin'), 10, 1);
			add_action('revoke_super_admin', array($adminUsers, 'revokeSuperAdmin'), 10, 1);
		} else if (wfConfig::get_ser('adminUserList', false)) {
			// reset this in the event it's disabled or the network is too large
			wfConfig::set_ser('adminUserList', false);
		}

		if (wfConfig::liveTrafficEnabled()) {
			add_action('wp_head', 'wordfence::wfLogHumanHeader');
			add_action('login_head', 'wordfence::wfLogHumanHeader');
		}

		add_action('wordfence_processAttackData', 'wordfence::processAttackData');
		if (!empty($_GET['wordfence_syncAttackData']) && get_site_option('wordfence_syncingAttackData') <= time() - 60 && get_site_option('wordfence_lastSyncAttackData', 0) < time() - 8) {
			@ignore_user_abort(true);
			update_site_option('wordfence_syncingAttackData', time());
			header('Content-Type: text/javascript');
			define('WORDFENCE_SYNCING_ATTACK_DATA', true);
			add_action('init', 'wordfence::syncAttackData', 10, 0);
			add_filter('woocommerce_unforce_ssl_checkout', '__return_false');
		}
		
		add_action('wordfence_batchReportBlockedAttempts', 'wordfence::wfsnBatchReportBlockedAttempts');
		add_action('wordfence_batchReportFailedAttempts', 'wordfence::wfsnBatchReportFailedAttempts');

		if (wfConfig::get('other_hideWPVersion')) {
			add_filter('update_feedback', 'wordfence::restoreReadmeForUpgrade');
		}

		add_action('rest_api_init', 'wordfence::initRestAPI');

		if (wfCentral::isConnected()) {
			add_action('wordfence_security_event', 'wfCentral::sendSecurityEvent', 10, 3);
		} else {
			add_action('wordfence_security_event', 'wfCentral::sendAlertCallback', 10, 3);
		}

		if (!wfConfig::get('wordfenceI18n', true)) {
			add_filter('gettext', function ($translation, $text, $domain) {
				if ($domain === 'wordfence') {
					return $text;
				}
				return $translation;
			}, 10, 3);
		}
	}
	public static function _pluginPageActionLinks($links) {
		if (!wfConfig::get('isPaid')) {
			$links = array_merge(array('aWordfencePluginCallout' => '<a href="https://www.wordfence.com/zz12/wordfence-signup/" target="_blank" rel="noopener noreferrer"><strong style="color: #11967A; display: inline;">' . esc_html__('Upgrade To Premium', 'wordfence') . '</strong></a>'), $links);
		} 
		return $links;
	}
	
	public static function _outputLoginSecurityInstallation() {
		if (WORDFENCE_LS_FROM_CORE && wfOnboardingController::shouldShowAttempt3()) {
			echo wfView::create('onboarding/banner')->render();
		}
	}
	
	public static function _outputLoginSecurityTour() {
		if (WORDFENCE_LS_FROM_CORE) {
			echo wfView::create('tours/login-security', array())->render();
		}
	}
	
	public static function fixWPMailFromAddress($from_email) {
		if ($from_email == 'wordpress@') { //$_SERVER['SERVER_NAME'] is undefined so we get an incomplete email address
			wordfence::status(4, 'info', __("wp_mail from address is incomplete, attempting to fix", 'wordfence'));
			$urls = array(get_site_url(), get_home_url());
			foreach ($urls as $u) {
				if (!empty($u)) {
					$u = preg_replace('#^[^/]*//+([^/]+).*$#', '\1', $u);
					if (substr($u, 0, 4) == 'www.') {
						$u = substr($u, 4);
					}
					
					if (!empty($u)) {
						wordfence::status(4, 'info', sprintf(/* translators: Email address. */ __("Fixing wp_mail from address: %s", 'wordfence'), $from_email . $u));
						return $from_email . $u;
					}
				}
			}
			
			//Can't fix it, return it as it was
		}
		return $from_email;
	}
	public static function wpRedirectFilter($location, $status) {
		self::getLog()->initLogRequest();
		self::getLog()->getCurrentRequest()->statusCode = $status;
		return $location;
	}
	public static function wpRedirectStatusFilter($status, $location) {
		self::getLog()->initLogRequest();
		self::getLog()->getCurrentRequest()->statusCode = $status;
		self::getLog()->logHit();
		return $status;
	}
	public static function enqueueAJAXWatcher() {
		$wafDisabled = !WFWAF_ENABLED || (class_exists('wfWAFConfig') && wfWAFConfig::isDisabled());
		if (wfUtils::isAdmin() && !$wafDisabled) {
			wp_enqueue_style('wordfenceAJAXcss', wfUtils::getBaseURL() . wfUtils::versionedAsset('css/wordfenceBox.css'), '', WORDFENCE_VERSION);
			wp_enqueue_script('wfi18njs', wfUtils::getBaseURL() . wfUtils::versionedAsset('js/wfi18n.js'), array(), WORDFENCE_VERSION);
			wp_enqueue_script('wordfenceAJAXjs', wfUtils::getBaseURL() . wfUtils::versionedAsset('js/admin.ajaxWatcher.js'), array('jquery'), WORDFENCE_VERSION);
			wp_localize_script('wordfenceAJAXjs', 'WFAJAXWatcherVars', array(
				'nonce' => wp_create_nonce('wf-waf-error-page'),
			));
			self::setupI18nJSStrings();
		}
	}
	public static function enqueueDashboard() {
		if (wfUtils::isAdmin()) {
			wp_enqueue_style('wf-adminbar', wfUtils::getBaseURL() . wfUtils::versionedAsset('css/wf-adminbar.css'), '', WORDFENCE_VERSION);
			wp_enqueue_script('wordfenceDashboardjs', wfUtils::getBaseURL() . wfUtils::versionedAsset('js/wfdashboard.js'), array('jquery'), WORDFENCE_VERSION);
			if (wfConfig::get('showAdminBarMenu')) {
				wp_enqueue_script('wordfencePopoverjs', wfUtils::getBaseURL() . wfUtils::versionedAsset('js/wfpopover.js'), array('jquery'), WORDFENCE_VERSION);
				wp_localize_script('wordfenceDashboardjs', 'WFDashVars', array(
					'ajaxURL' => admin_url('admin-ajax.php'),
					'nonce' => wp_create_nonce('wp-ajax'),
				));
			}
		}
	}
	public static function ajax_testAjax_callback(){
		die("WFSCANTESTOK");
	}
	public static function ajax_doScan_callback(){
		@ignore_user_abort(true);
		self::$wordfence_wp_version = false;
		if (!defined('DONOTCACHEDB')) { define('DONOTCACHEDB', true); }
		//This is messy, but not sure of a better way to do this without guaranteeing we get $wp_version
		require(ABSPATH . 'wp-includes/version.php'); /** @var string $wp_version */
		self::$wordfence_wp_version = $wp_version;
		require_once(dirname(__FILE__) . '/wfScan.php');
		wfScan::wfScanMain();

	} //END doScan
	public static function ajax_lh_callback(){
		self::getLog()->canLogHit = false;
		$UA = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
		$isCrawler = empty($UA);
		if ($UA) {
			if (wfCrawl::isCrawler($UA) || wfCrawl::isGoogleCrawler()) {
				$isCrawler = true;
			}
		}

		@ob_end_clean();
		if(! headers_sent()){
			header('Content-type: text/javascript');
			header("Connection: close");
			header("Content-Length: 0");
			header("X-Robots-Tag: noindex");
			if (!$isCrawler) {
				wfLog::cacheHumanRequester(wfUtils::getIP(), $UA);
			}
		}
		flush();
		if(! $isCrawler){
			$hid = $_GET['hid'];
			$hid = wfUtils::decrypt($hid);
			if(! preg_match('/^\d+$/', $hid)){ exit(); }
			$db = new wfDB();
			$table_wfHits = wfDB::networkTable('wfHits');
			$db->queryWrite("update {$table_wfHits} set jsRun=1 where id=%d", $hid);
		}
		die("");
	}
	public static function ajaxReceiver(){
		if(! wfUtils::isAdmin()){
			wfUtils::send_json(array('errorMsg' => __("You appear to have logged out or you are not an admin. Please sign-out and sign-in again.", 'wordfence')));
		}
		$func = (isset($_POST['action']) && $_POST['action']) ? $_POST['action'] : $_GET['action'];
		$nonce = (isset($_POST['nonce']) && $_POST['nonce']) ? $_POST['nonce'] : $_GET['nonce'];
		if(! wp_verify_nonce($nonce, 'wp-ajax')){
			wfUtils::send_json(array('errorMsg' => __("Your browser sent an invalid security token to Wordfence. Please try reloading this page or signing out and in again.", 'wordfence'), 'tokenInvalid' => 1));
		}
		//func is e.g. wordfence_ticker so need to munge it
		$func = str_replace('wordfence_', '', $func);
		$returnArr = call_user_func('wordfence::ajax_' . $func . '_callback');
		if($returnArr === false){
			$returnArr = array('errorMsg' => __("Wordfence encountered an internal error executing that request.", 'wordfence'));
		}

		if(! is_array($returnArr)){
			error_log("Function " . wp_kses($func, array()) . " did not return an array and did not generate an error.");
			$returnArr = array();
		}
		if(isset($returnArr['nonce'])){
			error_log("Wordfence ajax function return an array with 'nonce' already set. This could be a bug.");
		}
		$returnArr['nonce'] = wp_create_nonce('wp-ajax');
		wfUtils::send_json($returnArr);
	}
	public static function ajax_remoteVerifySwitchTo2FANew_callback() {
		$payload = wfUtils::decodeJWT(wfConfig::get('new2FAMigrationNonce'));
		if (empty($payload)) {
			wfUtils::send_json(new stdClass()); //Ensures an object response
		}
		
		$package = wfCrypt::noc1_encrypt($payload);
		wfUtils::send_json($package);
	}
	public static function ajax_switchTo2FANew_callback() {
		$migrate = (isset($_POST['migrate']) && wfUtils::truthyToBoolean($_POST['migrate']));
		
		$twoFactorUsers = wfConfig::get_ser('twoFactorUsers', array());
		if ($migrate && is_array($twoFactorUsers) && !empty($twoFactorUsers)) {
			$smsActive = array();
			$authenticatorActive = array();
			foreach ($twoFactorUsers as &$t) {
				if ($t[3] == 'activated') {
					$user = new WP_User($t[0]);
					if ($user instanceof WP_User && $user->exists()) {
						if ((!isset($t[5]) || $t[5] != 'authenticator')) {
							$smsActive[] = $user->user_login;
						}
						else {
							$authenticatorActive[] = $t[6];
						}
					}
				}
			}
			
			if (!empty($smsActive)) {
				return array('ok' => 0, 'smsActive' => $smsActive);
			}
			
			$total = 0;
			$imported = 0;
			$nonce = bin2hex(wfWAFUtils::random_bytes(32));
			wfConfig::set('new2FAMigrationNonce', wfUtils::generateJWT(array('nonce' => $nonce), 90));
			$api = new wfAPI(wfConfig::get('apiKey'), wfUtils::getWPVersion());
			try {
				$response = $api->call('twoFactorTOTP_migrate', array(), array('migrateids' => json_encode($authenticatorActive), 'nonce' => $nonce, 'verifyurl' => add_query_arg(array('action' => 'wordfence_remoteVerifySwitchTo2FANew'), admin_url('admin-ajax.php'))));
				/*
				 * A successful response will be in the format 
				 * {
				 * 		"ok": 1,
				 * 		"records": {
				 * 			"skipped": {
				 * 				<id>: true, ... if applicable
				 * 			},
				 * 			"totp": {
				 * 					<id>: {
				 * 							"secret": <secret>,
				 * 							"recovery": <recovery keys>,
				 * 							"ctime": <timestamp created>,
				 * 							"vtime": <timestamp of last verified TOTP code>
				 * 					},
				 * 					...
				 * 			}
				 * 		}
				 * }
				 */
				
				if (!is_array($response) || !isset($response['records']) || !is_array($response['records'])) {
					return array('ok' => 0, 'fail' => 1);
				}
				
				$secrets = $response['records'];
				if (!isset($secrets['totp']) || !is_array($secrets['totp'])) {
					return array('ok' => 0, 'fail' => 2);
				}
				
				$import = array();
				foreach ($twoFactorUsers as &$t) {
					if ($t[3] == 'activated') {
						$user = new WP_User($t[0]);
						if ($user instanceof WP_User && $user->exists()) {
							if ((!isset($t[5]) || $t[5] != 'authenticator')) {
								//Do nothing
							}
							else {
								if (isset($secrets['totp'][$t[6]])) { 
									$import[$user->ID] = $secrets['totp'][$t[6]];
									$import[$user->ID]['type'] = 'authenticator';
									$total++;
								}
							}
						}
					}
				}
				
				$imported = WFLSPHP52Compatability::import_2fa($import);
			}
			catch (Exception $e) {
				wordfence::status(4, 'error', sprintf(/* translators: Error message. */ __('2FA Migration Error: %s', 'wordfence'), $e->getMessage()));
				return array('ok' => 0, 'fail' => 1);
			}
			
			wfConfig::remove('new2FAMigrationNonce');
			wfConfig::set(wfCredentialsController::DISABLE_LEGACY_2FA_OPTION, true);
			return array('ok' => 1, 'total' => $total, 'imported' => $imported);
		}
		
		//No legacy 2FA active, just set the option.
		wfConfig::set(wfCredentialsController::DISABLE_LEGACY_2FA_OPTION, true);
		return array('ok' => 1);
	}
	public static function ajax_switchTo2FAOld_callback() {
		wfConfig::set(wfCredentialsController::DISABLE_LEGACY_2FA_OPTION, false);
		return array('ok' => 1);
	}
	public static function validateProfileUpdate($errors, $update, $userData){
		wordfence::validatePassword($errors, $userData);
	}
	public static function validatePassword($errors, $userData) {
		$password = (isset($_POST['pass1']) && trim($_POST['pass1'])) ? $_POST['pass1'] : false;
		$user_id = isset($userData->ID) ? $userData->ID : false;
		$username = isset($_POST["user_login"]) ? $_POST["user_login"] : $userData->user_login;
		if ($password == false) { return $errors; }
		if ($errors->get_error_data("pass")) { return $errors; }
		
		$enforceStrongPasswds = false;
		if (wfConfig::get('loginSec_strongPasswds_enabled')) {
			if (wfConfig::get('loginSec_strongPasswds') == 'pubs') {
				if (user_can($user_id, 'publish_posts')) {
					$enforceStrongPasswds = true;
				}
			}
			else if (wfConfig::get('loginSec_strongPasswds') == 'all') {
				$enforceStrongPasswds = true;
			}
		}
		
		if ($enforceStrongPasswds && !wordfence::isStrongPasswd($password, $username)) {
			$errors->add('pass', __('Please choose a stronger password. Try including numbers, symbols, and a mix of upper and lowercase letters and remove common words.', 'wordfence'));
			return $errors;
		}
		
		$twoFactorUsers = wfConfig::get_ser('twoFactorUsers', array());
		if (preg_match(self::$passwordCodePattern, $password) && is_array($twoFactorUsers) && count($twoFactorUsers) > 0) {
			$errors->add('pass', __('Passwords containing a space followed by "wf" without quotes are not allowed.', 'wordfence'));
			return $errors;
		}
		
		$enforceBreachedPasswds = false;
		if (wfConfig::get('loginSec_breachPasswds_enabled')) {
			if ($user_id !== false && wfConfig::get('loginSec_breachPasswds') == 'admins' && wfUtils::isAdmin($user_id)) {
				$enforceBreachedPasswds = true;
			}
			else if ($user_id !== false && wfConfig::get('loginSec_breachPasswds') == 'pubs' && user_can($user_id, 'publish_posts')) {
				$enforceBreachedPasswds = true;
			}
		}
		
		if ($enforceBreachedPasswds && wfCredentialsController::isLeakedPassword($username, $password)) {
			$errors->add('pass', sprintf(/* translators: Support URL. */ __('Please choose a different password. The password you are using exists on lists of passwords leaked in data breaches. Attackers use such lists to break into sites and install malicious code. <a href="%s">Learn More</a>', 'wordfence'), wfSupportController::esc_supportURL(wfSupportController::ITEM_USING_BREACH_PASSWORD)));
			return $errors;
		}
		else if ($user_id !== false) {
			wfAdminNoticeQueue::removeAdminNotice(false, '2faBreachPassword', array($user_id));
			wfAdminNoticeQueue::removeAdminNotice(false, 'previousIPBreachPassword', array($user_id));
			wfCredentialsController::clearCachedCredentialStatus($userData);
		}
		
		return $errors;
	}
	public static function isStrongPasswd($passwd, $username ) {
		$strength = 0;
		if(strlen( trim( $passwd ) ) < 5)
			return false;
		if(strtolower( $passwd ) == strtolower( $username ) )
			return false;
		if(preg_match('/(?:password|passwd|mypass|wordpress)/i', $passwd)){
			return false;
		}
		if($num = preg_match_all( "/\d/", $passwd, $matches) ){
			$strength += ((int)$num * 10);
		}
		if ( preg_match( "/[a-z]/", $passwd ) )
			$strength += 26;
		if ( preg_match( "/[A-Z]/", $passwd ) )
			$strength += 26;
		if ($num = preg_match_all( "/[^a-zA-Z0-9]/", $passwd, $matches)){
			$strength += (31 * (int)$num);

		}
		if($strength > 60){
			return true;
		}
	}
	public static function lostPasswordPost() {
		$IP = wfUtils::getIP();
		if ($request = self::getLog()->getCurrentRequest()) {
			$request->action = 'lostPassword';
			$request->save();
		}
		if (wfBlock::isWhitelisted($IP)) {
			return;
		}
		
		$lockout = wfBlock::lockoutForIP(wfUtils::getIP());
		if ($lockout !== false) {
			$lockout->recordBlock();
			$customText = wpautop(wp_strip_all_tags(wfConfig::get('blockCustomText', '')));
			require(dirname(__FILE__) . '/wfLockedOut.php');
		}
		
		if (empty($_POST['user_login'])) { return; }
		$user_login = $_POST['user_login'];
		if (is_array($user_login)) { $user_login = wfUtils::array_first($user_login); }
		$user_login = trim($user_login);
		$user  = get_user_by('login', $user_login);
		if (!$user) {
			$user = get_user_by('email', $user_login);
		}

		if($user){
			$alertCallback = array(new wfLostPasswdFormAlert($user, wfUtils::getIP()), 'send');
			do_action('wordfence_security_event', 'lostPasswdForm', array(
				'email' => $user->user_email,
				'ip' => wfUtils::getIP(),
			), $alertCallback);

		}
		if(wfConfig::get('loginSecurityEnabled')){
			$tKey = self::getForgotPasswordFailureCountTransient($IP);
			$forgotAttempts = get_transient($tKey);
			if($forgotAttempts){
				$forgotAttempts++;
			} else {
				$forgotAttempts = 1;
			}
			if($forgotAttempts >= wfConfig::get('loginSec_maxForgotPasswd')){
				self::lockOutIP($IP, sprintf(
					/* translators: 1. Password reset limit (number). 2. WordPress username. */
					__('Exceeded the maximum number of tries to recover their password which is set at: %1$s. The last username or email they entered before getting locked out was: \'%2$s\''),
					wfConfig::get('loginSec_maxForgotPasswd'),
					$_POST['user_login']
				));
				$customText = wpautop(wp_strip_all_tags(wfConfig::get('blockCustomText', '')));
				require(dirname(__FILE__) . '/wfLockedOut.php');
			}
			set_transient($tKey, $forgotAttempts, wfConfig::get('loginSec_countFailMins') * 60);
		}
	}
	public static function lockOutIP($IP, $reason) {
		wfBlock::createLockout($reason, $IP, wfBlock::lockoutDuration(), time(), time(), 1);
		self::getLog()->tagRequestForLockout($reason);
		$alertCallback = array(new wfLoginLockoutAlert($IP, $reason), 'send');
		do_action('wordfence_security_event', 'loginLockout', array(
			'ip'       => $IP,
			'reason'   => $reason,
			'duration' => wfBlock::lockoutDuration(),
		), $alertCallback);

	}

	public static function getLoginFailureCountTransient($IP) {
		return 'wflginfl_' . bin2hex(wfUtils::inet_pton($IP));
	}

	public static function getForgotPasswordFailureCountTransient($IP) {
		return 'wffgt_' . bin2hex(wfUtils::inet_pton($IP));
	}

	public static function clearLockoutCounters($IP) {
		delete_transient(self::getLoginFailureCountTransient($IP));
		delete_transient(self::getForgotPasswordFailureCountTransient($IP));
	}

	public static function veryFirstAction() {
		/** @var wpdb $wpdb ; */
		global $wpdb;
		
		self::initProtection();

		$wfFunc = isset($_GET['_wfsf']) ? @$_GET['_wfsf'] : false;
		if($wfFunc == 'unlockEmail'){
			$nonceValid = wp_verify_nonce(@$_POST['nonce'], 'wf-form');
			if (!$nonceValid && method_exists(wfWAF::getInstance(), 'createNonce')) {
				$nonceValid = wfWAF::getInstance()->verifyNonce(@$_POST['nonce'], 'wf-form');
			}
			if(!$nonceValid){
				die(__("Sorry but your browser sent an invalid security token when trying to use this form.", 'wordfence'));
			}
			$numTries = get_transient('wordfenceUnlockTries');
			if($numTries > 10){
				printf("<html><body><h1>%s</h1><p>%s</p></body></html>",
					esc_html__('Please wait 3 minutes and try again', 'wordfence'),
					esc_html__('You have used this form too much. Please wait 3 minutes and try again.', 'wordfence')
				);
				exit();
			}
			if(! $numTries){ $numTries = 1; } else { $numTries = $numTries + 1; }
			set_transient('wordfenceUnlockTries', $numTries, 180);

			$email = trim(@$_POST['email']);
			global $wpdb;
			$ws = $wpdb->get_results($wpdb->prepare("SELECT ID, user_login FROM $wpdb->users WHERE user_email = %s", $email));
			$found = false;
			foreach($ws as $user){
				$userDat = get_userdata($user->ID);
				if(wfUtils::isAdmin($userDat)){
					if($email == $userDat->user_email){
						$found = true;
						break;
					}
				}
			}
			if(! $found){
				foreach(wfConfig::getAlertEmails() as $alertEmail){
					if($alertEmail == $email){
						$found = true;
						break;
					}
				}
			}
			if($found){
				$key = wfUtils::bigRandomHex();
				$IP = wfUtils::getIP();
				set_transient('wfunlock_' . $key, $IP, 1800);
				$content = wfUtils::tmpl('email_unlockRequest.php', array(
					'siteName' => get_bloginfo('name', 'raw'),
					'siteURL' => wfUtils::getSiteBaseURL(),
					'unlockHref' => wfUtils::getSiteBaseURL() . '?_wfsf=unlockAccess&key=' . $key,
					'key' => $key,
					'IP' => $IP
					));
				wp_mail($email, __("Unlock email requested", 'wordfence'), $content, "Content-Type: text/html");
			}
			echo "<html><body><h1>" . esc_html__('Your request was received', 'wordfence') . "</h1><p>" .
				esc_html(sprintf(/* translators: Email address. */ __("We received a request to email \"%s\" instructions to unlock their access. If that is the email address of a site administrator or someone on the Wordfence alert list, they have been emailed instructions on how to regain access to this system. The instructions we sent will expire 30 minutes from now.", 'wordfence'), wp_kses($email, array())))
				. "</p></body></html>";

			exit();
		} else if($wfFunc == 'unlockAccess'){
			if (!preg_match('/^(?:(?:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){7})|(?:(?!(?:.*[a-f0-9](?::|$)){7,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?)))|(?:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){5}:)|(?:(?!(?:.*[a-f0-9]:){5,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3}:)?))?(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))(?:\.(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))){3}))$/i', get_transient('wfunlock_' . $_GET['key']))) {
				_e("Invalid key provided for authentication.", 'wordfence');
				exit();
			}
			
			if($_GET['func'] == 'unlockMyIP'){
				wfBlock::unblockIP(wfUtils::getIP());
				if (class_exists('wfWAFIPBlocksController')) { wfWAFIPBlocksController::setNeedsSynchronizeConfigSettings(); }
				self::clearLockoutCounters(wfUtils::getIP());
				header('Location: ' . wp_login_url());
				exit();
			} else if($_GET['func'] == 'unlockAllIPs'){
				wordfence::status(1, 'info', __("Request received via unlock email link to unblock all IPs.", 'wordfence'));
				wfBlock::removeAllIPBlocks();
				if (class_exists('wfWAFIPBlocksController')) { wfWAFIPBlocksController::setNeedsSynchronizeConfigSettings(); }
				self::clearLockoutCounters(wfUtils::getIP());
				header('Location: ' . wp_login_url());
				exit();
			} else if($_GET['func'] == 'disableRules'){
				wfConfig::set('firewallEnabled', 0);
				wfConfig::set('loginSecurityEnabled', 0);
				wordfence::status(1, 'info', __("Request received via unlock email link to unblock all IPs via disabling firewall rules.", 'wordfence'));
				wfBlock::removeAllIPBlocks();
				wfBlock::removeAllCountryBlocks();
				if (class_exists('wfWAFIPBlocksController')) { wfWAFIPBlocksController::setNeedsSynchronizeConfigSettings(); }
				self::clearLockoutCounters(wfUtils::getIP());
				header('Location: ' . wp_login_url());
				exit();
			} else {
				_e("Invalid function specified. Please check the link we emailed you and make sure it was not cut-off by your email reader.", 'wordfence');
				exit();
			}
		}
		else if ($wfFunc == 'detectProxy') {
			wfUtils::doNotCache();
			if (wfUtils::processDetectProxyCallback()) {
				self::getLog()->getCurrentRequest()->action = 'scan:detectproxy'; //Exempt a valid callback from live traffic
				echo wfConfig::get('detectProxyRecommendation', '-');
			}
			else {
				echo '0';
			}
			exit();
		}
		else if ($wfFunc == 'removeAlertEmail') {
			wfUtils::doNotCache();
			
			$payloadStatus = false;
			$jwt = (isset($_GET['jwt']) && is_string($_GET['jwt'])) ? $_GET['jwt'] : '';
			if (!empty($jwt)) {
				$payload = wfUtils::decodeJWT($jwt);
				if ($payload && isset($payload['email'])) {
					$payloadStatus = true;
				}
			}
			
			if (isset($_POST['resend'])) {
				$email = trim(@$_POST['email']);
				$found = false;
				$alertEmails = wfConfig::getAlertEmails();
				foreach ($alertEmails as $e) {
					if ($e == $email) {
						$found = true;
						break;
					}
				}
					
				if ($found) {
					$content = wfUtils::tmpl('email_unsubscribeRequest.php', array(
						'siteName' => get_bloginfo('name', 'raw'),
						'siteURL' => wfUtils::getSiteBaseURL(),
						'IP' => wfUtils::getIP(),
						'jwt' => wfUtils::generateJWT(array('email' => $email)),
					));
					wp_mail($email, __("Unsubscribe Requested", 'wordfence'), $content, "Content-Type: text/html");
				}
				
				echo wfView::create('common/unsubscribe', array(
					'state' => 'resent',
				))->render();
				exit();
			}
			else if (!$payloadStatus) {
				echo wfView::create('common/unsubscribe', array(
					'state' => 'bad',
				))->render();
				exit();
			}
			else if (isset($_POST['confirm'])) {
				$confirm = wfUtils::truthyToBoolean($_POST['confirm']);
				if ($confirm) {
					$found = false;
					$alertEmails = wfConfig::getAlertEmails();
					$updatedAlertEmails = array();
					foreach ($alertEmails as $alertEmail) {
						if ($alertEmail == $payload['email']) {
							$found = true;
						}
						else {
							$updatedAlertEmails[] = $alertEmail;
						}
					}
					
					if ($found) {
						wfConfig::set('alertEmails', implode(',', $updatedAlertEmails));
					}
					
					echo wfView::create('common/unsubscribe', array(
						'jwt' => $_GET['jwt'],
						'email' => $payload['email'],
						'state' => 'unsubscribed',
					))->render();
					exit();
				}
			}
			
			echo wfView::create('common/unsubscribe', array(
				'jwt' => $_GET['jwt'],
				'email' => $payload['email'],
				'state' => 'prompt',
			))->render();
			exit();
		}
		else if ($wfFunc == 'installLicense') {
			if (wfUtils::isAdmin()) {
				wfUtils::doNotCache();
				
				if (isset($_POST['license'])) {
					$nonceValid = wp_verify_nonce(@$_POST['nonce'], 'wf-form');
					if (!$nonceValid) {
						die(__('Sorry but your browser sent an invalid security token when trying to use this form.', 'wordfence'));
					}
					
					$changes = array('apiKey' => $_POST['license']);
					$errors = wfConfig::validate($changes);
					if ($errors !== true) {
						$error = __('An error occurred while saving the license.', 'wordfence');
						if (count($errors) == 1) {
							$error = sprintf(/* translators: Error message. */ __('An error occurred while saving the license: %s', 'wordfence'), $errors[0]['error']);
						}
						
						echo wfView::create('common/license', array(
							'state' => 'bad',
							'error' => $error,
						))->render();
						exit();
					}
					
					try {
						wfConfig::save(wfConfig::clean($changes));
						echo wfView::create('common/license', array(
							'state' => 'installed',
						))->render();
						exit();
					}
					catch (Exception $e) {
						echo wfView::create('common/license', array(
							'state' => 'bad',
							'error' => sprintf(/* translators: Error message. */ __('An error occurred while saving the license: %s', 'wordfence'), $e->getMessage()),
						))->render();
						exit();
					}
				}
				
				echo wfView::create('common/license', array(
					'state' => 'prompt',
				))->render();
				exit();
			}
		}
		
		if (is_main_site() && wfUtils::isAdmin()) {
			if (wp_next_scheduled('wordfence_daily_cron') === false) {
				wp_schedule_event(time() + 600, 'daily', 'wordfence_daily_cron');
				wordfence::status(2, 'info', __("Rescheduled missing daily cron", 'wordfence'));
			}
			
			if (wp_next_scheduled('wordfence_hourly_cron') === false) {
				wp_schedule_event(time() + 600, 'hourly', 'wordfence_hourly_cron');
				wordfence::status(2, 'info', __("Rescheduled missing hourly cron", 'wordfence'));
			}
		}

		// Sync the WAF data with the database.
		$updateCountries = false;
		if (!WFWAF_SUBDIRECTORY_INSTALL && $waf = wfWAF::getInstance()) {
			$homeurl = wfUtils::wpHomeURL();
			$siteurl = wfUtils::wpSiteURL();
			
			//Sync the GeoIP database if needed
			$destination = WFWAF_LOG_PATH . '/GeoLite2-Country.mmdb';
			if (!file_exists($destination) || wfConfig::get('needsGeoIPSync')) {
				$allowSync = false;
				if (wfConfig::createLock('wfSyncGeoIP')) {
					$status = get_transient('wfSyncGeoIPActive');
					if (!$status) {
						$allowSync = true;
						set_transient('wfSyncGeoIPActive', true, 3600);
					}
					wfConfig::releaseLock('wfSyncGeoIP');
				}
				
				if ($allowSync) {
					if (version_compare(phpversion(), '5.4.0', '>=')) {
						if (!class_exists('wfGeoIP2')) {
							require_once(dirname(__FILE__) . '/../models/common/wfGeoIP2.php');
						}
						
						try {
							$wflogsGeoIP = @wfGeoIP2::shared(wfGeoIP2::DB_WFLOGS);
							$bundledGeoIP = @wfGeoIP2::shared(wfGeoIP2::DB_BUNDLED);
							
							if ($wflogsGeoIP === false || $wflogsGeoIP->version() != $bundledGeoIP->version()) {
								$source = dirname(__FILE__) . '/GeoLite2-Country.mmdb';
								if (copy($source, $destination)) {
									$shash = '';
									$dhash = '';
									
									$sp = @fopen($source, "rb");
									if ($sp) {
										$scontext = hash_init('sha256');
										while (!feof($sp)) {
											$data = fread($sp, 65536);
											if ($data === false) {
												$scontext = false;
												break;
											}
											hash_update($scontext, $data);
										}
										fclose($sp);
										if ($scontext !== false) {
											$shash = hash_final($scontext, false);
										}
									}
									
									$dp = @fopen($destination, "rb");
									if ($dp) {
										$dcontext = hash_init('sha256');
										while (!feof($dp)) {
											$data = fread($dp, 65536);
											if ($data === false) {
												$dcontext = false;
												break;
											}
											hash_update($dcontext, $data);
										}
										fclose($dp);
										if ($scontext !== false) {
											$dhash = hash_final($dcontext, false);
										}
									}
									
									if (hash_equals($shash, $dhash)) {
										$updateCountries = true;
										wfConfig::remove('needsGeoIPSync');
										delete_transient('wfSyncGeoIPActive');
									}
								}
							}
							else {
								wfConfig::remove('needsGeoIPSync');
								delete_transient('wfSyncGeoIPActive');
							}
						}
						catch (Exception $e) {
							//Ignore
						}
					}
				}
			}
			
			if (!$updateCountries && version_compare(phpversion(), '5.4.0', '>=')) {
				$previousVersionHash = wfConfig::get('geoIPVersionHash', '');
				$geoIPVersion = wfUtils::geoIPVersion();
				if (is_array($geoIPVersion)) {
					$geoIPVersion = implode(',', $geoIPVersion);
				}
				$geoIPVersionHash = hash('sha256', $geoIPVersion);
				$updateCountries = ($geoIPVersion !== null && $previousVersionHash != $geoIPVersionHash);
			}
			
			if ($updateCountries) { // Fix the data in the country column
				$intervalSQL = 'FLOOR(UNIX_TIMESTAMP(DATE_SUB(NOW(), interval 7 day)) / 86400)';
				switch (wfConfig::get('email_summary_interval', 'weekly')) {
					case 'daily':
						$intervalSQL = 'FLOOR(UNIX_TIMESTAMP(DATE_SUB(NOW(), interval 1 day)) / 86400)';
						break;
					case 'monthly':
						$intervalSQL = 'FLOOR(UNIX_TIMESTAMP(DATE_SUB(NOW(), interval 1 month)) / 86400)';
						break;
				}
				
				$table_wfBlockedIPLog = wfDB::networkTable('wfBlockedIPLog');
				$ip_results = $wpdb->get_results("SELECT DISTINCT countryCode, IP FROM `{$table_wfBlockedIPLog}` WHERE unixday >= {$intervalSQL} GROUP BY IP ORDER BY unixday DESC LIMIT 500");
				if ($ip_results) {
					foreach ($ip_results as $ip_row) {
						$country = wfUtils::IP2Country(wfUtils::inet_ntop($ip_row->IP));
						if ($country != $ip_row->countryCode) {
							$wpdb->query($wpdb->prepare("UPDATE `{$table_wfBlockedIPLog}` SET countryCode = %s WHERE IP = %s", $country, $ip_row->IP));
						}
					}
				}
				
				$geoIPVersion = wfUtils::geoIPVersion();
				if (is_array($geoIPVersion)) {
					$geoIPVersion = implode(',', $geoIPVersion);
				}
				$geoIPVersionHash = hash('sha256', $geoIPVersion);
				wfConfig::set('geoIPVersionHash', $geoIPVersionHash);
			}
			
			try {
				$sapi = @php_sapi_name();
				if ($sapi != "cli") {
					$lastPermissionsTemplateCheck = wfConfig::getInt('lastPermissionsTemplateCheck', 0);
					if (defined('WFWAF_LOG_PATH') && ($lastPermissionsTemplateCheck + 43200) < time()) { //Run no more frequently than every 12 hours
						$timestamp = preg_replace('/[^0-9]/', '', microtime(false)); //We avoid using tmpfile since it can potentially create one with different permissions than the defaults
						$tmpTemplate = rtrim(WFWAF_LOG_PATH, '/') . "/template.{$timestamp}.tmp";
						$template = rtrim(WFWAF_LOG_PATH, '/') . '/template.php';
						@unlink($tmpTemplate);
						@file_put_contents($tmpTemplate, "<?php exit('Access denied'); __halt_compiler(); ?>\n");
						$tmpStat = @stat($tmpTemplate);
						if ($tmpStat !== false) {
							$mode = $tmpStat[2] & 0777;
							$updatedMode = 0600;
							if (($mode & 0020) == 0020) { //Group writable
								$updatedMode = $updatedMode | 0060;
							}
							
							if (defined('WFWAF_LOG_FILE_MODE')) {
								$updatedMode = WFWAF_LOG_FILE_MODE;
							}
							
							$stat = @stat($template);
							if ($stat === false || ($stat[2] & 0777) != $updatedMode) {
								@chmod($tmpTemplate, $updatedMode);
								
								@unlink($template);
								@rename($tmpTemplate, $template);
							}
							@unlink($tmpTemplate);
						}
						else {
							@unlink($tmpTemplate);
						}
						
						wfConfig::set('lastPermissionsTemplateCheck', time());
					
						@chmod(WFWAF_LOG_PATH, (wfWAFWordPress::permissions() | 0755));
						wfWAFWordPress::writeHtaccess();
						
						$contents = self::_wflogsContents();
						if ($contents) {
							$validFiles = wfWAF::getInstance()->fileList();
							foreach ($validFiles as &$vf) {
								$vf = basename($vf);
							}
							$validFiles = array_filter($validFiles);
							
							$previousWflogsFileList = wfConfig::getJSON('previousWflogsFileList', array());
							
							$wflogs = realpath(WFWAF_LOG_PATH);
							$filesRemoved = array();
							foreach ($contents as $f) {
								if (!in_array($f, $validFiles) && in_array($f, $previousWflogsFileList)) {
									$fullPath = $f;
									$removed = self::_recursivelyRemoveWflogs($f);
									$filesRemoved = array_merge($filesRemoved, $removed);
								}
							}
							
							$contents = self::_wflogsContents();
							wfConfig::setJSON('previousWflogsFileList', $contents);
							
							if (!empty($filesRemoved)) {
								$removalHistory = wfConfig::getJSON('diagnosticsWflogsRemovalHistory', array());
								$removalHistory = array_slice($removalHistory, 0, 4);
								array_unshift($removalHistory, array(time(), $filesRemoved));
								wfConfig::setJSON('diagnosticsWflogsRemovalHistory', $removalHistory);
							}
						}
					}
				}
			}
			catch (Exception $e) { 
				//Ignore
			}
			
			try {
				$configDefaults = array(
					'apiKey'         => wfConfig::get('apiKey'),
					'isPaid'         => !!wfConfig::get('isPaid'),
					'siteURL'        => $siteurl,
					'homeURL'        => $homeurl,
					'whitelistedIPs' => (string) wfConfig::get('whitelisted'),
					'whitelistedServiceIPs' => @json_encode(wfUtils::whitelistedServiceIPs()),
					'howGetIPs'      => (string) wfConfig::get('howGetIPs'),
					'howGetIPs_trusted_proxies' => wfConfig::get('howGetIPs_trusted_proxies', ''),
					'detectProxyRecommendation' => (string) wfConfig::get('detectProxyRecommendation'),
					'other_WFNet'    => !!wfConfig::get('other_WFNet', true), 
					'pluginABSPATH'	 => ABSPATH,
					'serverIPs'		 => json_encode(wfUtils::serverIPs()),
					'blockCustomText' => wpautop(wp_strip_all_tags(wfConfig::get('blockCustomText', ''))),
					'betaThreatDefenseFeed' => !!wfConfig::get('betaThreatDefenseFeed'),
					'disableWAFIPBlocking' => wfConfig::get('disableWAFIPBlocking'),
					'wordpressVersion' => wfConfig::get('wordpressVersion'),
					'wordpressPluginVersions' => wfConfig::get_ser('wordpressPluginVersions'),
					'wordpressThemeVersions' => wfConfig::get_ser('wordpressThemeVersions'),
					'WPLANG' => get_site_option('WPLANG'),
				);
				if (wfUtils::isAdmin()) {
					$errorNonceKey = 'errorNonce_' . get_current_user_id();
					$configDefaults[$errorNonceKey] = wp_create_nonce('wf-waf-error-page'); //Used by the AJAX watcher script
				}
				foreach ($configDefaults as $key => $value) {
					$waf->getStorageEngine()->setConfig($key, $value, 'synced');
				}
				
				if (wfConfig::get('timeoffset_wf') !== false) {
					$waf->getStorageEngine()->setConfig('timeoffset_wf', wfConfig::get('timeoffset_wf'), 'synced');
				}
				else {
					$waf->getStorageEngine()->unsetConfig('timeoffset_wf', 'synced');
				}
				
				if (class_exists('wfWAFIPBlocksController')) {
					wfWAFIPBlocksController::setNeedsSynchronizeConfigSettings();
				}
				
				if (wfUtils::isAdmin()) {
					if ($waf->getStorageEngine()->getConfig('wafStatus', '') == 'learning-mode') {
						if ($waf->getStorageEngine()->getConfig('learningModeGracePeriodEnabled', false)) {
							if ($waf->getStorageEngine()->getConfig('learningModeGracePeriod', 0) <= time()) {
								// Reached the end of the grace period, activate the WAF.
								$waf->getStorageEngine()->setConfig('wafStatus', 'enabled');
								$waf->getStorageEngine()->setConfig('learningModeGracePeriodEnabled', 0);
								$waf->getStorageEngine()->unsetConfig('learningModeGracePeriod');
								
								$firewall = new wfFirewall();
								$firewall->syncStatus(true);
							}
						}
					}
				}

				if (empty($_GET['wordfence_syncAttackData'])) {
					$table_wfHits = wfDB::networkTable('wfHits');
					if ($waf->getStorageEngine() instanceof wfWAFStorageMySQL) {
						$lastAttackMicroseconds = floatval($waf->getStorageEngine()->getConfig('lastAttackDataTruncateTime'));
					} else {
						$lastAttackMicroseconds = $wpdb->get_var("SELECT MAX(attackLogTime) FROM {$table_wfHits}");
					}
					if (get_site_option('wordfence_lastSyncAttackData', 0) < time() - 8) {
						if ($waf->getStorageEngine()->hasNewerAttackData($lastAttackMicroseconds)) {
							if (get_site_option('wordfence_syncingAttackData') <= time() - 60) {
								// Could be the request to itself is not completing, add ajax to the head as a workaround
								$attempts = get_site_option('wordfence_syncAttackDataAttempts', 0);
								if ($attempts > 10) {
									add_action('wp_head', 'wordfence::addSyncAttackDataAjax');
									add_action('login_head', 'wordfence::addSyncAttackDataAjax');
									add_action('admin_head', 'wordfence::addSyncAttackDataAjax');
								} else {
									update_site_option('wordfence_syncAttackDataAttempts', ++$attempts);
									wp_remote_post(add_query_arg('wordfence_syncAttackData', microtime(true), home_url('/')), array(
										'timeout'   => 0.01,
										'blocking'  => false,
										'sslverify' => apply_filters('https_local_ssl_verify', false)
									));
								}
							}
						}
					}
				}

				if ($waf instanceof wfWAFWordPress && ($learningModeAttackException = $waf->getLearningModeAttackException())) {
					$log = self::getLog();
					$log->initLogRequest();
					$request = $log->getCurrentRequest();
					$request->action = 'learned:waf';
					$request->attackLogTime = microtime(true);

					$ruleIDs = array();
					/** @var wfWAFRule $failedRule */
					foreach ($learningModeAttackException->getFailedRules() as $failedRule) {
						$ruleIDs[] = $failedRule->getRuleID();
					}

					$actionData = array(
						'learningMode' => 1,
						'failedRules'  => $ruleIDs,
						'paramKey'     => $learningModeAttackException->getParamKey(),
						'paramValue'   => $learningModeAttackException->getParamValue(),
					);
					if ($ruleIDs && $ruleIDs[0]) {
						$rule = $waf->getRule($ruleIDs[0]);
						if ($rule) {
							$request->actionDescription = $rule->getDescription();
							$actionData['category'] = $rule->getCategory();
							$actionData['ssl'] = $waf->getRequest()->getProtocol() === 'https';
							$actionData['fullRequest'] = base64_encode($waf->getRequest());
						}
					}
					$request->actionData = wfRequestModel::serializeActionData($actionData);
					register_shutdown_function(array($request, 'save'));

					self::scheduleSendAttackData();
				}
			} catch (wfWAFStorageFileException $e) {
				// We don't have anywhere to write files in this scenario.
			} catch (wfWAFStorageEngineMySQLiException $e) {
				// Ignore and continue
			}
		}

		if(wfConfig::get('firewallEnabled')){
			$wfLog = self::getLog();
			$wfLog->firewallBadIPs();

			$IP = wfUtils::getIP();
			if (wfBlock::isWhitelisted($IP)) {
				return;
			}
			if (wfConfig::get('neverBlockBG') == 'neverBlockUA' && wfCrawl::isGoogleCrawler()) {
				return;
			}
			if (wfConfig::get('neverBlockBG') == 'neverBlockVerified' && wfCrawl::isVerifiedGoogleCrawler()) {
				return;
			}

			if (wfConfig::get('bannedURLs', false)) {
				$URLs = explode("\n", wfUtils::cleanupOneEntryPerLine(wfConfig::get('bannedURLs')));
				foreach ($URLs as $URL) {
					if (preg_match(wfUtils::patternToRegex($URL, ''), $_SERVER['REQUEST_URI'])) {
						$reason = __('Accessed a banned URL', 'wordfence');
						wfBlock::createIP($reason, $IP, wfBlock::blockDuration(), time(), time(), 1, wfBlock::TYPE_IP_AUTOMATIC_TEMPORARY);
						wfActivityReport::logBlockedIP($IP, null, 'bannedurl');
						$wfLog->tagRequestForBlock($reason);
						$wfLog->do503(3600, __("Accessed a banned URL", 'wordfence'));
						//exits
					}
				}
			}

			if (wfConfig::get('other_blockBadPOST') == '1' && $_SERVER['REQUEST_METHOD'] == 'POST' && empty($_SERVER['HTTP_USER_AGENT']) && empty($_SERVER['HTTP_REFERER'])) {
				$reason = __('POST received with blank user-agent and referer', 'wordfence');
				wfBlock::createIP($reason, $IP, wfBlock::blockDuration(), time(), time(), 1, wfBlock::TYPE_IP_AUTOMATIC_TEMPORARY);
				wfActivityReport::logBlockedIP($IP, null, 'badpost');
				$wfLog->tagRequestForBlock($reason);
				$wfLog->do503(3600, __("POST received with blank user-agent and referer", 'wordfence'));
				//exits
			}
		}
	}
	
	private static function _wflogsContents() {
		$dir = opendir(WFWAF_LOG_PATH);
		if ($dir) {
			$contents = array();
			while ($path = readdir($dir)) {
				if ($path == '.' || $path == '..') { continue; }
				$contents[] = $path;
			}
			closedir($dir);
			return $contents;
		}
		return false;
	}
	
	/**
	 * Removes a path within wflogs, recursing as necessary.
	 * 
	 * @param string $file
	 * @param array $processedDirs
	 * @return array The list of removed files/folders.
	 */
	private static function _recursivelyRemoveWflogs($file, $processedDirs = array()) {
		if (preg_match('~(?:^|/|\\\\)\.\.(?:/|\\\\|$)~', $file)) {
			return array();
		}
		
		if (stripos(WFWAF_LOG_PATH, 'wflogs') === false) { //Sanity check -- if not in a wflogs folder, user will have to do removal manually
			return array();
		}
		
		$path = rtrim(WFWAF_LOG_PATH, '/') . '/' . $file;
		if (is_link($path)) {
			if (@unlink($path)) {
				return array($file);
			}
			return array();
		}
		
		if (is_dir($path)) {
			$real = realpath($file);
			if (in_array($real, $processedDirs)) {
				return array();
			}
			$processedDirs[] = $real;
			
			$count = 0;
			$dir = opendir($path);
			if ($dir) {
				$contents = array();
				while ($sub = readdir($dir)) {
					if ($sub == '.' || $sub == '..') { continue; }
					$contents[] = $sub;
				}
				closedir($dir);
				
				$filesRemoved = array();
				foreach ($contents as $f) {
					$removed = self::_recursivelyRemoveWflogs($file . '/' . $f, $processedDirs);
					$filesRemoved = array($filesRemoved, $removed);
				}
			}
			
			if (@rmdir($path)) {
				$filesRemoved[] = $file;
			}
			return $filesRemoved;
		}
		
		if (@unlink($path)) {
			return array($file);
		}
		return array();
	}

	public static function loginAction($username){
		if(sizeof($_POST) < 1){ return; } //only execute if login form is posted
		if(! $username){ return; }
		wfConfig::inc('totalLogins');
		$user = get_user_by('login', $username);
		$userID = $user ? $user->ID : 0;
		self::getLog()->logLogin('loginOK', 0, $username);
		if(wfUtils::isAdmin($user)){
			wfConfig::set_ser('lastAdminLogin', array(
				'userID' => $userID,
				'username' => $username,
				'firstName' => $user->first_name,
				'lastName' => $user->last_name,
				'time' => wfUtils::localHumanDateShort(),
				'IP' => wfUtils::getIP()
				));
		}
		
		$salt = wp_salt('logged_in');
		//TODO: Drop support for legacy cookie after 1 year
		$legacyCookieName = 'wf_loginalerted_' . hash_hmac('sha256', wfUtils::getIP() . '|' . $user->ID, $salt);
		$cookieName = 'wf_loginalerted_' . hash_hmac('sha256', $user->ID, $salt);
		$cookieValue = hash_hmac('sha256', $user->user_login, $salt);
		$newDevice = !(isset($_COOKIE[$legacyCookieName]) && hash_equals($cookieValue, $_COOKIE[$legacyCookieName])); //Check legacy cookie
		if($newDevice){
			$newDevice = !(isset($_COOKIE[$cookieName]) && hash_equals($cookieValue, $_COOKIE[$cookieName]));
		}
		else{
			$_COOKIE[$cookieName]=$cookieValue;
		}
		if(wfUtils::isAdmin($userID)){
			$securityEvent = 'adminLogin';
			$alertCallback = array(new wfAdminLoginAlert($cookieName, $cookieValue, $username, wfUtils::getIP()), 'send');

		} else {
			$securityEvent = 'nonAdminLogin';
			$alertCallback = array(new wfNonAdminLoginAlert($cookieName, $cookieValue, $username, wfUtils::getIP()), 'send');
		}
		if($newDevice)
			$securityEvent.='NewLocation';
		do_action('wordfence_security_event', $securityEvent, array(
			'username' => $username,
			'ip' => wfUtils::getIP(),
		), $alertCallback);
		
		if (wfConfig::get(wfUtils::isAdmin($userID)?'alertOn_firstAdminLoginOnly':'alertOn_firstNonAdminLoginOnly')) {
			//Purge legacy cookie if still present
			if(array_key_exists($legacyCookieName, $_COOKIE))
				wfUtils::setcookie($legacyCookieName, '', 1, '/', null, wfUtils::isFullSSL(), true);
			wfUtils::setcookie($cookieName, $cookieValue, time() + (86400 * 365), '/', null, wfUtils::isFullSSL(), true);
		}
	}
	public static function registrationFilter($errors, $sanitizedLogin, $userEmail) {
		if (wfConfig::get('loginSec_blockAdminReg') && $sanitizedLogin == 'admin') {
			$errors->add('user_login_error', __('<strong>ERROR</strong>: You can\'t register using that username', 'wordfence'));
		}
		return $errors;
	}
	public static function wooRegistrationFilter($wooCustomerData) {
		/*
		   $wooCustomerData matches:
		   array(
				'user_login' => $username,
				'user_pass'  => $password,
				'user_email' => $email,
				'role'       => 'customer',
			)
		 */
		if (wfConfig::get('loginSec_blockAdminReg') && is_array($wooCustomerData) && isset($wooCustomerData['user_login']) && isset($wooCustomerData['user_email']) && preg_match('/^admin\d*$/i', $wooCustomerData['user_login'])) {
			//Converts a username of `admin` generated from something like `admin@example.com` to `adminexample`
			$emailComponents = explode('@', $wooCustomerData['user_email']);
			if (strpos(wfUtils::array_last($emailComponents), '.') === false) { //e.g., admin@localhost 
				$wooCustomerData['user_login'] .= wfUtils::array_last($emailComponents); 
			}
			else { //e.g., admin@example.com
				$hostComponents = explode('.', wfUtils::array_last($emailComponents));
				array_pop($hostComponents);
				$wooCustomerData['user_login'] .= wfUtils::array_last($hostComponents);
			}
			
			//If it's still `admin` at this point, it will fall through and get blocked by wordfence::blacklistedUsernames
		}
		return $wooCustomerData;
	}
	public static function oembedAuthorFilter($data, $post, $width, $height) {
		unset($data['author_name']);
		unset($data['author_url']);
		return $data;
	}
	public static function jsonAPIAuthorFilter($response, $handler, $request) {
		$route = $request->get_route();
		if (!current_user_can('edit_others_posts')) {
			$urlBase = wfWP_REST_Users_Controller::wfGetURLBase();
			if (preg_match('~' . preg_quote($urlBase, '~') . '/*$~i', $route)) {
				$error = new WP_Error('rest_user_cannot_view', __('Sorry, you are not allowed to list users.'), array('status' => rest_authorization_required_code()));
				$response = rest_ensure_response($error);
				if (!defined('WORDFENCE_REST_API_SUPPRESSED')) { define('WORDFENCE_REST_API_SUPPRESSED', true); }
			}
			else if (preg_match('~' . preg_quote($urlBase, '~') . '/+(\d+)/*$~i', $route, $matches)) {
				$id = (int) $matches[1];
				if (get_current_user_id() !== $id) {
					$error = new WP_Error('rest_user_invalid_id', __('Invalid user ID.'), array('status' => 404));
					$response = rest_ensure_response($error);
					if (!defined('WORDFENCE_REST_API_SUPPRESSED')) { define('WORDFENCE_REST_API_SUPPRESSED', true); }
				}
			}
		}
		return $response;
	}
	public static function jsonAPIAdjustHeaders($response, $server, $request) {
		if (defined('WORDFENCE_REST_API_SUPPRESSED')) {
			$response->header('Allow', 'GET');
		}
		
		return $response;
	}
	public static function wpSitemapUserProviderFilter($provider, $name) {
		if ($name === 'users') {
			return false;
		}
		return $provider;
	}
	public static function _filterCentralFromLiveTraffic($dispatch_result, $request, $route, $handler) {
		if (preg_match('~^/wordfence/v\d+/~i', $route)) {
			self::getLog()->canLogHit = false;
		}
		return $dispatch_result;
	}
	public static function showTwoFactorField() {
		$existingContents = ob_get_contents();
		if (!preg_match('/wftwofactornonce:([0-9]+)\/(.+?)\s/', $existingContents, $matches)) {
			return;
		}
		
		$userID = intval($matches[1]);
		$twoFactorNonce = preg_replace('/[^a-f0-9]/i', '', $matches[2]);
		if (!self::verifyTwoFactorIntermediateValues($userID, $twoFactorNonce)) {
			return;
		}
		
		//Strip out the username and password fields
		$formPosition = strrpos($existingContents, '<form');
		$formTagEnd = strpos($existingContents, '>', $formPosition);
		if ($formPosition === false || $formTagEnd === false) {
			return;
		}
		
		ob_end_clean();
		ob_start();
		echo substr($existingContents, 0, $formTagEnd + 1);
		
		//Add the 2FA field
		echo "<p>
        <label for=\"wfAuthenticationCode\">Authentication Code<br>
        <input type=\"text\" size=\"6\" class=\"input\" id=\"wordfence_authFactor\" name=\"wordfence_authFactor\" autofocus></label>
        <input type=\"hidden\" id=\"wordfence_twoFactorUser\" name=\"wordfence_twoFactorUser\" value=\"" . $userID . "\">
        <input type=\"hidden\" id=\"wordfence_twoFactorNonce\" name=\"wordfence_twoFactorNonce\" value=\"" . $twoFactorNonce . "\">
    </p>";
	}
	private static function verifyTwoFactorIntermediateValues($userID, $twoFactorNonce) {
		$user = get_user_by('ID', $userID);
		if (!$user || get_class($user) != 'WP_User') { return false; } //Check that the user exists
		
		$expectedNonce = get_user_meta($user->ID, '_wf_twoFactorNonce', true);
		$twoFactorNonceTime = get_user_meta($user->ID, '_wf_twoFactorNonceTime', true);
		if (empty($twoFactorNonce) || empty($twoFactorNonceTime)) { return false; } //Ensure the two factor nonce and time have been set
		if ($twoFactorNonce != $expectedNonce) { return false; } //Verify the nonce matches the expected
		
		$twoFactorUsers = wfConfig::get_ser('twoFactorUsers', array());
		if (!$twoFactorUsers || !is_array($twoFactorUsers)) { return false; } //Make sure there are two factor users configured
		foreach ($twoFactorUsers as &$t) { //Ensure the two factor nonce hasn't expired
			if ($t[0] == $user->ID && $t[3] == 'activated') {
				if (isset($t[5]) && $t[5] == 'authenticator') { $graceTime = WORDFENCE_TWO_FACTOR_GRACE_TIME_AUTHENTICATOR; }
				else { $graceTime = WORDFENCE_TWO_FACTOR_GRACE_TIME_PHONE; }
				return ((time() - $twoFactorNonceTime) < $graceTime);
			}
		}
		return false;
	}
	public static function authenticateFilter($authUser, $username, $passwd) {
		wfConfig::inc('totalLoginHits'); //The total hits to wp-login.php including logins, logouts and just hits.
		$IP = wfUtils::getIP();
		$secEnabled = wfConfig::get('loginSecurityEnabled');
		
		$twoFactorUsers = wfConfig::get_ser('twoFactorUsers', array());
		$userDat = self::$userDat;
		
		$checkBreachList = $secEnabled &&
			!wfBlock::isWhitelisted($IP) &&
			wfConfig::get('loginSec_breachPasswds_enabled') &&
			is_object($authUser) &&
			get_class($authUser) == 'WP_User' &&
			((wfConfig::get('loginSec_breachPasswds') == 'admins' && wfUtils::isAdmin($authUser)) || (wfConfig::get('loginSec_breachPasswds') == 'pubs' && user_can($authUser, 'publish_posts')));
		
		$usingBreachedPassword = false;
		if ($checkBreachList) {
			$cacheStatus = wfCredentialsController::cachedCredentialStatus($authUser);
			if ($cacheStatus != wfCredentialsController::UNCACHED) {
				$usingBreachedPassword = ($cacheStatus == wfCredentialsController::LEAKED);
			}
			else {
				if (wfCredentialsController::isLeakedPassword($authUser->username, $passwd)) {
					$usingBreachedPassword = true;
				}
				wfCredentialsController::setCachedCredentialStatus($authUser, $usingBreachedPassword);
			}
		}
		
		$checkTwoFactor = $secEnabled &&
			!wfBlock::isWhitelisted($IP) &&
			wfConfig::get('isPaid') &&
			isset($twoFactorUsers) &&
			is_array($twoFactorUsers) &&
			sizeof($twoFactorUsers) > 0 &&
			is_object($userDat) &&
			get_class($userDat) == 'WP_User' &&
			wfCredentialsController::useLegacy2FA();
		
		if ($checkTwoFactor) {
			$twoFactorRecord = false;
			$hasActivatedTwoFactorUser = false;
			foreach ($twoFactorUsers as &$t) {
				if ($t[3] == 'activated') {
					$userID = $t[0];
					$testUser = get_user_by('ID', $userID);
					if (is_object($testUser) && wfUtils::isAdmin($testUser)) {
						$hasActivatedTwoFactorUser = true;
					}
					
					if ($userID == $userDat->ID) {
						$twoFactorRecord = &$t;
					}
				}
			}
			
			if (isset($_POST['wordfence_authFactor']) && $_POST['wordfence_authFactor'] && $twoFactorRecord) { //User authenticated with name and password, 2FA code ready to check
				$userID = $userDat->ID;
				
				if (is_object($authUser) && get_class($authUser) == 'WP_User' && $authUser->ID == $userID) {
					//Do nothing. This is the code path the old method of including the code in the password field will take -- since we already have a valid $authUser, skip the nonce verification portion
				}
				else if (isset($_POST['wordfence_twoFactorNonce'])) {
					$twoFactorNonce = preg_replace('/[^a-f0-9]/i', '', $_POST['wordfence_twoFactorNonce']);
					if (!self::verifyTwoFactorIntermediateValues($userID, $twoFactorNonce)) {
						remove_action('login_errors', 'limit_login_fixup_error_messages'); //We're forced to do this because limit-login-attempts does not have any allowances for legitimate error messages
						self::$authError = new WP_Error('twofactor_required', __('<strong>VERIFICATION FAILED</strong>: Two-factor authentication verification failed. Please try again.'));
						return self::processBruteForceAttempt(self::$authError, $username, $passwd);
					}
				}
				else { //Code path for old method, invalid password the second time
					self::$authError = $authUser;
					if (is_wp_error(self::$authError) && (self::$authError->get_error_code() == 'invalid_username' || $authUser->get_error_code() == 'invalid_email' || self::$authError->get_error_code() == 'incorrect_password' || $authUser->get_error_code() == 'authentication_failed') && wfConfig::get('loginSec_maskLoginErrors')) {
						self::$authError = new WP_Error('incorrect_password', sprintf(/* translators: 1. WordPress username. 2. Password reset URL. */ __('<strong>ERROR</strong>: The username or password you entered is incorrect. <a href="%2$s" title="Password Lost and Found">Lost your password</a>?'), $username, wp_lostpassword_url()));
					}
					
					return self::processBruteForceAttempt(self::$authError, $username, $passwd);
				}
				
				if ($usingBreachedPassword) {
					wfAdminNoticeQueue::removeAdminNotice(false, 'previousIPBreachPassword', array($userID));
					wfAdminNoticeQueue::addAdminNotice(wfAdminNotice::SEVERITY_CRITICAL, sprintf(
						/* translators: 1. WordPress admin panel URL. 2. Support URL. */
						__('<strong>WARNING: </strong>The password you are using exists on lists of passwords leaked in data breaches. Attackers use such lists to break into sites and install malicious code. Please <a href="%1$s">change your password</a>. <a href="%2$s" target="_blank" rel="noopener noreferrer">Learn More</a>', 'wordfence'),
						self_admin_url('profile.php'),
						wfSupportController::esc_supportURL(wfSupportController::ITEM_USING_BREACH_PASSWORD)
					), '2faBreachPassword', array($authUser->ID));
				}
				
				if (isset($twoFactorRecord[5])) { //New method TOTP
					$mode = $twoFactorRecord[5];
					$code = preg_replace('/[^a-f0-9]/i', '', $_POST['wordfence_authFactor']);
					
					$api = new wfAPI(wfConfig::get('apiKey'), wfUtils::getWPVersion());
					try {
						$codeResult = $api->call('twoFactorTOTP_verify', array(), array('totpid' => $twoFactorRecord[6], 'code' => $code, 'mode' => $mode));
						
						if (isset($codeResult['notPaid']) && $codeResult['notPaid']) {
							//No longer a paid key, let them sign in without two factor
						}
						else if (isset($codeResult['ok']) && $codeResult['ok']) {
							//Everything's good, let the sign in continue
						} 
						else {
							if (is_object($authUser) && get_class($authUser) == 'WP_User' && $authUser->ID == $userID) { //Using the old method of appending the code to the password
								if ($mode == 'authenticator') {
									remove_action('login_errors', 'limit_login_fixup_error_messages'); //We're forced to do this because limit-login-attempts does not have any allowances for legitimate error messages
									self::$authError = new WP_Error('twofactor_invalid', __('<strong>INVALID CODE</strong>: Please sign in again and add a space, the letters <code>wf</code>, and the code from your authenticator app to the end of your password (e.g., <code>wf123456</code>).'));
								}
								else {
									remove_action('login_errors', 'limit_login_fixup_error_messages'); //We're forced to do this because limit-login-attempts does not have any allowances for legitimate error messages
									self::$authError = new WP_Error('twofactor_invalid', __('<strong>INVALID CODE</strong>: Please sign in again and add a space, the letters <code>wf</code>, and the code sent to your phone to the end of your password (e.g., <code>wf123456</code>).'));
								}
							}
							else {
								$loginNonce = wfWAFUtils::random_bytes(20);
								if ($loginNonce === false) { //Should never happen but is technically possible
									remove_action('login_errors', 'limit_login_fixup_error_messages'); //We're forced to do this because limit-login-attempts does not have any allowances for legitimate error messages
									self::$authError = new WP_Error('twofactor_required', __('<strong>AUTHENTICATION FAILURE</strong>: A temporary failure was encountered while trying to log in. Please try again.'));
									return self::$authError;
								}
								
								$loginNonce = bin2hex($loginNonce);
								update_user_meta($userDat->ID, '_wf_twoFactorNonce', $loginNonce);
								update_user_meta($userDat->ID, '_wf_twoFactorNonceTime', time());
								
								if ($mode == 'authenticator') {
									remove_action('login_errors', 'limit_login_fixup_error_messages'); //We're forced to do this because limit-login-attempts does not have any allowances for legitimate error messages
									self::$authError = new WP_Error('twofactor_invalid', __('<strong>INVALID CODE</strong>: You need to enter the code generated by your authenticator app. The code should be a six digit number (e.g., 123456).') . '<!-- wftwofactornonce:' . $userDat->ID . '/' . $loginNonce . ' -->');
								}
								else {
									remove_action('login_errors', 'limit_login_fixup_error_messages'); //We're forced to do this because limit-login-attempts does not have any allowances for legitimate error messages
									self::$authError = new WP_Error('twofactor_invalid', __('<strong>INVALID CODE</strong>: You need to enter the code generated sent to your phone. The code should be a six digit number (e.g., 123456).') . '<!-- wftwofactornonce:' . $userDat->ID . '/' . $loginNonce . ' -->');
								}
							}
							return self::processBruteForceAttempt(self::$authError, $username, $passwd);
						}
					}
					catch (Exception $e) {
						if (self::isDebugOn()) {
							error_log('TOTP validation error: ' . $e->getMessage());
						}
					} // Couldn't connect to noc1, let them sign in since the password was correct.
				}
				else { //Old method phone authentication
					$authFactor = $_POST['wordfence_authFactor'];
					if (strlen($authFactor) == 4) {
						$authFactor = 'wf' . $authFactor;
					}
					if ($authFactor == $twoFactorRecord[2] && $twoFactorRecord[4] > time()) { // Set this 2FA code to expire in 30 seconds (for other plugins hooking into the auth process)
						$twoFactorRecord[4] = time() + 30;
						wfConfig::set_ser('twoFactorUsers', $twoFactorUsers);
					}
					else if ($authFactor == $twoFactorRecord[2]) {
						$api = new wfAPI(wfConfig::get('apiKey'), wfUtils::getWPVersion());
						try {
							$codeResult = $api->call('twoFactor_verification', array(), array('phone' => $twoFactorRecord[1]));
							
							if (isset($codeResult['notPaid']) && $codeResult['notPaid']) {
								//No longer a paid key, let them sign in without two factor
							} 
							else if (isset($codeResult['ok']) && $codeResult['ok']) {
								$twoFactorRecord[2] = $codeResult['code'];
								$twoFactorRecord[4] = time() + 1800; //30 minutes until code expires
								wfConfig::set_ser('twoFactorUsers', $twoFactorUsers); //save the code the user needs to enter and return an error.
								
								$loginNonce = wfWAFUtils::random_bytes(20);
								if ($loginNonce === false) { //Should never happen but is technically possible
									remove_action('login_errors', 'limit_login_fixup_error_messages'); //We're forced to do this because limit-login-attempts does not have any allowances for legitimate error messages
									self::$authError = new WP_Error('twofactor_required', __('<strong>AUTHENTICATION FAILURE</strong>: A temporary failure was encountered while trying to log in. Please try again.'));
									return self::$authError;
								}
								
								$loginNonce = bin2hex($loginNonce);
								update_user_meta($userDat->ID, '_wf_twoFactorNonce', $loginNonce);
								update_user_meta($userDat->ID, '_wf_twoFactorNonceTime', time());
								
								remove_action('login_errors', 'limit_login_fixup_error_messages'); //We're forced to do this because limit-login-attempts does not have any allowances for legitimate error messages
								self::$authError = new WP_Error('twofactor_required', __('<strong>CODE EXPIRED. CHECK YOUR PHONE:</strong> The code you entered has expired. Codes are only valid for 30 minutes for security reasons. We have sent you a new code. Please sign in using your username, password, and the new code we sent you.') . '<!-- wftwofactornonce:' . $userDat->ID . '/' . $loginNonce . ' -->');
								return self::$authError;
							}
							
							//else: No new code was received. Let them sign in with the expired code.
						}
						catch (Exception $e) {
							// Couldn't connect to noc1, let them sign in since the password was correct.
						} 
					}
					else { //Bad code, so cancel the login and return an error to user.
						$loginNonce = wfWAFUtils::random_bytes(20);
						if ($loginNonce === false) { //Should never happen but is technically possible
							remove_action('login_errors', 'limit_login_fixup_error_messages'); //We're forced to do this because limit-login-attempts does not have any allowances for legitimate error messages
							self::$authError = new WP_Error('twofactor_required', __('<strong>AUTHENTICATION FAILURE</strong>: A temporary failure was encountered while trying to log in. Please try again.'));
							return self::$authError;
						}
						
						$loginNonce = bin2hex($loginNonce);
						update_user_meta($userDat->ID, '_wf_twoFactorNonce', $loginNonce);
						update_user_meta($userDat->ID, '_wf_twoFactorNonceTime', time());
						
						remove_action('login_errors', 'limit_login_fixup_error_messages'); //We're forced to do this because limit-login-attempts does not have any allowances for legitimate error messages
						self::$authError = new WP_Error('twofactor_invalid', __('<strong>INVALID CODE</strong>: You need to enter your password and the code we sent to your phone. The code should start with \'wf\' and should be four characters (e.g., wfAB12).') . '<!-- wftwofactornonce:' . $userDat->ID . '/' . $loginNonce . ' -->');
						return self::processBruteForceAttempt(self::$authError, $username, $passwd);
					}
				}
				delete_user_meta($userDat->ID, '_wf_twoFactorNonce');
				delete_user_meta($userDat->ID, '_wf_twoFactorNonceTime');
				$authUser = $userDat; //Log in as the user we saved in the wp_authenticate action
			}
			else if (is_object($authUser) && get_class($authUser) == 'WP_User') { //User authenticated with name and password, prompt for the 2FA code
				//Verify at least one administrator has 2FA enabled
				$requireAdminTwoFactor = $hasActivatedTwoFactorUser && wfConfig::get('loginSec_requireAdminTwoFactor');
				
				if ($twoFactorRecord) {
					if ($twoFactorRecord[0] == $userDat->ID && $twoFactorRecord[3] == 'activated') { //Yup, enabled, so require the code
						if ($usingBreachedPassword) {
							wfAdminNoticeQueue::removeAdminNotice(false, 'previousIPBreachPassword', array($authUser->ID));
							wfAdminNoticeQueue::addAdminNotice(wfAdminNotice::SEVERITY_CRITICAL, sprintf(
								/* translators: 1. WordPress admin panel URL. 2. Support URL. */
								__('<strong>WARNING: </strong>The password you are using exists on lists of passwords leaked in data breaches. Attackers use such lists to break into sites and install malicious code. Please <a href="%1$s">change your password</a>. <a href="%2$s" target="_blank" rel="noopener noreferrer">Learn More</a>', 'wordfence'), self_admin_url('profile.php'), wfSupportController::esc_supportURL(wfSupportController::ITEM_USING_BREACH_PASSWORD)), '2faBreachPassword', array($authUser->ID));
						}
						
						$loginNonce = wfWAFUtils::random_bytes(20);
						if ($loginNonce === false) { //Should never happen but is technically possible, allow login
							$requireAdminTwoFactor = false;
						}
						else {
							$loginNonce = bin2hex($loginNonce);
							update_user_meta($userDat->ID, '_wf_twoFactorNonce', $loginNonce);
							update_user_meta($userDat->ID, '_wf_twoFactorNonceTime', time());
							
							if (isset($twoFactorRecord[5])) { //New method TOTP authentication
								if ($twoFactorRecord[5] == 'authenticator') {
									if (self::hasGDLimitLoginsMUPlugin() && function_exists('limit_login_get_address')) {
										$retries = get_option('limit_login_retries', array());
										$ip = limit_login_get_address();
										
										if (!is_array($retries)) {
											$retries = array();
										}
										if (isset($retries[$ip]) && is_int($retries[$ip])) {
											$retries[$ip]--;
										}
										else {
											$retries[$ip] = 0;
										}
										update_option('limit_login_retries', $retries);
									}
									
									$allowSeparatePrompt = ini_get('output_buffering') > 0;
									if (wfConfig::get('loginSec_enableSeparateTwoFactor') && $allowSeparatePrompt) {
										remove_action('login_errors', 'limit_login_fixup_error_messages'); //We're forced to do this because limit-login-attempts does not have any allowances for legitimate error messages
										self::$authError = new WP_Error('twofactor_required', __('<strong>CODE REQUIRED</strong>: Please check your authenticator app for the current code. Enter it below to sign in.') . '<!-- wftwofactornonce:' . $userDat->ID . '/' . $loginNonce . ' -->');
										return self::$authError;
									}
									else {
										remove_action('login_errors', 'limit_login_fixup_error_messages'); //We're forced to do this because limit-login-attempts does not have any allowances for legitimate error messages
										self::$authError = new WP_Error('twofactor_required', __('<strong>CODE REQUIRED</strong>: Please check your authenticator app for the current code. Please sign in again and add a space, the letters <code>wf</code>, and the code to the end of your password (e.g., <code>wf123456</code>).'));
										return self::$authError;
									}
								}
								else {
									//Phone TOTP
									$api = new wfAPI(wfConfig::get('apiKey'), wfUtils::getWPVersion());
									try {
										$codeResult = $api->call('twoFactorTOTP_sms', array(), array('totpid' => $twoFactorRecord[6]));
										if (isset($codeResult['notPaid']) && $codeResult['notPaid']) {
											$requireAdminTwoFactor = false;
											//Let them sign in without two factor if their API key has expired or they're not paid and for some reason they have this set up.
										}
										else {
											if (isset($codeResult['ok']) && $codeResult['ok']) {
												if (self::hasGDLimitLoginsMUPlugin() && function_exists('limit_login_get_address')) {
													$retries = get_option('limit_login_retries', array());
													$ip = limit_login_get_address();
													
													if (!is_array($retries)) {
														$retries = array();
													}
													if (isset($retries[$ip]) && is_int($retries[$ip])) {
														$retries[$ip]--;
													}
													else {
														$retries[$ip] = 0;
													}
													update_option('limit_login_retries', $retries);
												}
												
												$allowSeparatePrompt = ini_get('output_buffering') > 0;
												if (wfConfig::get('loginSec_enableSeparateTwoFactor') && $allowSeparatePrompt) {
													remove_action('login_errors', 'limit_login_fixup_error_messages'); //We're forced to do this because limit-login-attempts does not have any allowances for legitimate error messages
													self::$authError = new WP_Error('twofactor_required', __('<strong>CHECK YOUR PHONE</strong>: A code has been sent to your phone and will arrive within 30 seconds. Enter it below to sign in.') . '<!-- wftwofactornonce:' . $userDat->ID . '/' . $loginNonce . ' -->');
													return self::$authError;
												}
												else {
													remove_action('login_errors', 'limit_login_fixup_error_messages'); //We're forced to do this because limit-login-attempts does not have any allowances for legitimate error messages
													self::$authError = new WP_Error('twofactor_required', __('<strong>CHECK YOUR PHONE</strong>: A code has been sent to your phone and will arrive within 30 seconds. Please sign in again and add a space, the letters <code>wf</code>, and the code to the end of your password (e.g., <code>wf123456</code>).'));
													return self::$authError;
												}
											}
											else { //oops, our API returned an error.
												$requireAdminTwoFactor = false;
												//Let them sign in without two factor because the API is broken and we don't want to lock users out of their own systems.
											}
										}
									}
									catch (Exception $e) {
										if (self::isDebugOn()) {
											error_log('TOTP SMS error: ' . $e->getMessage());
										}
										$requireAdminTwoFactor = false;
										// Couldn't connect to noc1, let them sign in since the password was correct.
									}
								}
							}
							else { //Old method phone authentication
								$api = new wfAPI(wfConfig::get('apiKey'), wfUtils::getWPVersion());
								try {
									$codeResult = $api->call('twoFactor_verification', array(), array('phone' => $twoFactorRecord[1]));
									if (isset($codeResult['notPaid']) && $codeResult['notPaid']) {
										$requireAdminTwoFactor = false;
										//Let them sign in without two factor if their API key has expired or they're not paid and for some reason they have this set up.
									}
									else {
										if (isset($codeResult['ok']) && $codeResult['ok']) {
											$twoFactorRecord[2] = $codeResult['code'];
											$twoFactorRecord[4] = time() + 1800; //30 minutes until code expires
											wfConfig::set_ser('twoFactorUsers', $twoFactorUsers); //save the code the user needs to enter and return an error.
											
											if (self::hasGDLimitLoginsMUPlugin() && function_exists('limit_login_get_address')) {
												$retries = get_option('limit_login_retries', array());
												$ip = limit_login_get_address();
												
												if (!is_array($retries)) {
													$retries = array();
												}
												if (isset($retries[$ip]) && is_int($retries[$ip])) {
													$retries[$ip]--;
												}
												else {
													$retries[$ip] = 0;
												}
												update_option('limit_login_retries', $retries);
											}
											
											$allowSeparatePrompt = ini_get('output_buffering') > 0;
											if (wfConfig::get('loginSec_enableSeparateTwoFactor') && $allowSeparatePrompt) {
												remove_action('login_errors', 'limit_login_fixup_error_messages'); //We're forced to do this because limit-login-attempts does not have any allowances for legitimate error messages
												self::$authError = new WP_Error('twofactor_required', __('<strong>CHECK YOUR PHONE</strong>: A code has been sent to your phone and will arrive within 30 seconds. Enter it below to sign in.') . '<!-- wftwofactornonce:' . $userDat->ID . '/' . $loginNonce . ' -->');
												return self::$authError;
											}
											else {
												remove_action('login_errors', 'limit_login_fixup_error_messages'); //We're forced to do this because limit-login-attempts does not have any allowances for legitimate error messages
												self::$authError = new WP_Error('twofactor_required', __('<strong>CHECK YOUR PHONE</strong>: A code has been sent to your phone and will arrive within 30 seconds. Please sign in again and add a space and the code to the end of your password (e.g., <code>wfABCD</code>).'));
												return self::$authError;
											}
										}
										else { //oops, our API returned an error.
											$requireAdminTwoFactor = false;
											//Let them sign in without two factor because the API is broken and we don't want to lock users out of their own systems.
										}
									}
								}
								catch (Exception $e) {
									$requireAdminTwoFactor = false;
									// Couldn't connect to noc1, let them sign in since the password was correct.
								}
							} //end: Old method phone authentication
						}
					}
				}
				else if ($usingBreachedPassword) {
					if (wfCredentialsController::hasPreviousLoginFromIP($authUser, wfUtils::getIP())) {
						wfAdminNoticeQueue::removeAdminNotice(false, '2faBreachPassword', array($authUser->ID));
						wfAdminNoticeQueue::addAdminNotice(wfAdminNotice::SEVERITY_CRITICAL, sprintf(__('<strong>WARNING: </strong>Your login has been allowed because you have previously logged in from the same IP, but you will be blocked if your IP changes. The password you are using exists on lists of passwords leaked in data breaches. Attackers use such lists to break into sites and install malicious code. Please <a href="%1$s">change your password</a>. <a href="%2$s" target="_blank" rel="noopener noreferrer">Learn More</a>', 'wordfence'), self_admin_url('profile.php'), wfSupportController::esc_supportURL(wfSupportController::ITEM_USING_BREACH_PASSWORD)), 'previousIPBreachPassword', array($authUser->ID));
					}
					else {
						$username = $authUser->user_login;
						self::getLog()->logLogin('loginFailValidUsername', 1, $username);
						$alertCallback = array(new wfBreachLoginAlert($username, wp_lostpassword_url(), wfSupportController::esc_supportURL(wfSupportController::ITEM_USING_BREACH_PASSWORD), wfUtils::getIP()), 'send');

						do_action('wordfence_security_event', 'breachLogin', array(
							'username' => $username,
							'resetPasswordURL' => wp_lostpassword_url(),
							'supportURL' => wfSupportController::esc_supportURL(wfSupportController::ITEM_USING_BREACH_PASSWORD),
							'ip' => wfUtils::getIP(),
						), $alertCallback);
						
						remove_action('login_errors', 'limit_login_fixup_error_messages'); //We're forced to do this because limit-login-attempts does not have any allowances for legitimate error messages
						self::$authError = new WP_Error('breached_password', sprintf(
							/* translators: 1. Reset password URL. 2. Support URL. */
							__('<strong>INSECURE PASSWORD:</strong> Your login attempt has been blocked because the password you are using exists on lists of passwords leaked in data breaches. Attackers use such lists to break into sites and install malicious code. Please <a href="%1$s">reset your password</a> to reactivate your account. <a href="%2$s" target="_blank" rel="noopener noreferrer">Learn More</a>'), wp_lostpassword_url(), wfSupportController::esc_supportURL(wfSupportController::ITEM_USING_BREACH_PASSWORD)));
						return self::$authError;
					}
				}
				
				if ($requireAdminTwoFactor && wfUtils::isAdmin($authUser)) {
					$username = $authUser->user_login;
					self::getLog()->logLogin('loginFailValidUsername', 1, $username);
					wordfence::alert(__("Admin Login Blocked"), sprintf(/* translators: WordPress username. */__("A user with username \"%s\" who has administrator access tried to sign in to your WordPress site. Access was denied because all administrator accounts are required to have Cellphone Sign-in enabled but this account does not.", 'wordfence'), $username), wfUtils::getIP());
					self::$authError = new WP_Error('twofactor_disabled_required', __('<strong>Cellphone Sign-in Required</strong>: Cellphone Sign-in is required for all administrator accounts. Please contact the site administrator to enable it for your account.'));
					return self::$authError;
				}
				
				//User is not configured for two factor. Sign in without two factor.
			}
		} //End: if ($checkTwoFactor)
		else if ($usingBreachedPassword) {
			if (wfCredentialsController::hasPreviousLoginFromIP($authUser, wfUtils::getIP())) {
				wfAdminNoticeQueue::removeAdminNotice(false, '2faBreachPassword', array($authUser->ID));
				wfAdminNoticeQueue::addAdminNotice(wfAdminNotice::SEVERITY_CRITICAL, sprintf(/* translators: 1. Reset password URL. 2. Support URL. */ __('<strong>WARNING: </strong>Your login has been allowed because you have previously logged in from the same IP, but you will be blocked if your IP changes. The password you are using exists on lists of passwords leaked in data breaches. Attackers use such lists to break into sites and install malicious code. Please <a href="%1$s">change your password</a>. <a href="%2$s" target="_blank" rel="noopener noreferrer">Learn More</a>', 'wordfence'), self_admin_url('profile.php'), wfSupportController::esc_supportURL(wfSupportController::ITEM_USING_BREACH_PASSWORD)), 'previousIPBreachPassword', array($authUser->ID));
			}
			else {
				$username = $authUser->user_login;
				self::getLog()->logLogin('loginFailValidUsername', 1, $username);
				$alertCallback = array(new wfBreachLoginAlert($username, wp_lostpassword_url(), wfSupportController::esc_supportURL(wfSupportController::ITEM_USING_BREACH_PASSWORD), wfUtils::getIP()), 'send');

				do_action('wordfence_security_event', 'breachLogin', array(
					'username' => $username,
					'resetPasswordURL' => wp_lostpassword_url(),
					'supportURL' => wfSupportController::esc_supportURL(wfSupportController::ITEM_USING_BREACH_PASSWORD),
					'ip' => wfUtils::getIP(),
				), $alertCallback);

				remove_action('login_errors', 'limit_login_fixup_error_messages'); //We're forced to do this because limit-login-attempts does not have any allowances for legitimate error messages
				self::$authError = new WP_Error('breached_password', sprintf(
					/* translators: 1. Reset password URL. 2. Support URL. */
					__('<strong>INSECURE PASSWORD:</strong> Your login attempt has been blocked because the password you are using exists on lists of passwords leaked in data breaches. Attackers use such lists to break into sites and install malicious code. Please <a href="%1$s">reset your password</a> to reactivate your account. <a href="%2$s" target="_blank" rel="noopener noreferrer">Learn More</a>'), wp_lostpassword_url(), wfSupportController::esc_supportURL(wfSupportController::ITEM_USING_BREACH_PASSWORD)));
				return self::$authError;
			}
		}
		
		return self::processBruteForceAttempt($authUser, $username, $passwd);
	}
	
	public static function checkSecurityNetwork($endpointType = null) {
		if (wfConfig::get('other_WFNet')) {
			$IP = wfUtils::getIP();
			if ($maxBlockTime = self::wfsnIsBlocked($IP, 'brute', $endpointType)) {
				$secsToGo = ($maxBlockTime ? $maxBlockTime : wfBlock::blockDuration());
				$reason = __('Blocked by Wordfence Security Network', 'wordfence');
				wfBlock::createWFSN($reason, $IP, $secsToGo, time(), time(), 1);
				wfActivityReport::logBlockedIP($IP, null, 'brute');
				self::getLog()->tagRequestForBlock($reason, true);
				self::getLog()->getCurrentRequest()->action = 'blocked:wfsn';
				self::getLog()->do503($secsToGo, $reason); //exits
			}
		}
	}
	
	public static function processBruteForceAttempt($authUser, $username, $passwd) {
		$IP = wfUtils::getIP();
		$secEnabled = wfConfig::get('loginSecurityEnabled');
		
		if (wfBlock::isWhitelisted($IP)) {
			return $authUser;
		}
		
		$failureErrorCodes = array('invalid_username', 'invalid_email', 'incorrect_password', 'twofactor_invalid', 'authentication_failed', 'wfls_twofactor_invalid', 'wfls_twofactor_failed', 'wfls_twofactor_blocked');
		if (is_wp_error($authUser) && in_array($authUser->get_error_code(), $failureErrorCodes)) {
			self::checkSecurityNetwork(); //May exit
		}
		
		if($secEnabled){
			if(is_wp_error($authUser) && ($authUser->get_error_code() == 'invalid_username' || $authUser->get_error_code() == 'invalid_email')){
				if($blacklist = wfConfig::get('loginSec_userBlacklist')){
					$users = explode("\n", wfUtils::cleanupOneEntryPerLine($blacklist));
					foreach($users as $user){
						if(strtolower($username) == strtolower($user)){
							$secsToGo = wfBlock::blockDuration();
							$reason = __('Blocked by login security setting', 'wordfence');
							wfBlock::createIP($reason, $IP, $secsToGo, time(), time(), 1, wfBlock::TYPE_IP_AUTOMATIC_TEMPORARY);
							wfActivityReport::logBlockedIP($IP, null, 'brute');
							self::getLog()->tagRequestForBlock($reason);
							self::getLog()->do503($secsToGo, $reason); //exits
						}
					}
				}
				if(wfConfig::get('loginSec_lockInvalidUsers')){
					if(strlen($username) > 0 && preg_match('/[^\r\s\n\t]+/', $username)){
						self::lockOutIP($IP, sprintf(/* translators: WordPress username. */ __("Used an invalid username '%s' to try to sign in", 'wordfence'), $username));
						self::getLog()->logLogin('loginFailInvalidUsername', true, $username);
					}
					$customText = wpautop(wp_strip_all_tags(wfConfig::get('blockCustomText', '')));
					require(dirname(__FILE__) . '/wfLockedOut.php');
				}
			}
			$tKey = self::getLoginFailureCountTransient($IP);
			if(is_wp_error($authUser) && in_array($authUser->get_error_code(), $failureErrorCodes)) {
				$tries = get_transient($tKey);
				if($tries){
					$tries++;
				} else {
					$tries = 1;
				}
				if($tries >= wfConfig::get('loginSec_maxFailures')){
					self::lockOutIP($IP,
						sprintf(
							/* translators: 1. Login attempt limit. 2. WordPress username. */
							__('Exceeded the maximum number of login failures which is: %1$s. The last username they tried to sign in with was: \'%2$s\'', 'wordfence'),
							wfConfig::get('loginSec_maxFailures'),
							$username
						)
					);
					$customText = wpautop(wp_strip_all_tags(wfConfig::get('blockCustomText', '')));
					require(dirname(__FILE__) . '/wfLockedOut.php');
				}
				set_transient($tKey, $tries, wfConfig::get('loginSec_countFailMins') * 60);
			} else if(is_object($authUser) && get_class($authUser) == 'WP_User'){
				delete_transient($tKey); //reset counter on success
			}
		}
		if(is_wp_error($authUser)){
			if($authUser->get_error_code() == 'invalid_username' || $authUser->get_error_code() == 'invalid_email'){
				self::getLog()->logLogin('loginFailInvalidUsername', 1, $username);
			} else {
				self::getLog()->logLogin('loginFailValidUsername', 1, $username);
			}
		}

		if(is_wp_error($authUser) && ($authUser->get_error_code() == 'invalid_username' || $authUser->get_error_code() == 'invalid_email' || $authUser->get_error_code() == 'incorrect_password') && wfConfig::get('loginSec_maskLoginErrors')){
			return new WP_Error( 'incorrect_password', sprintf(
				/* translators: 1. WordPress username. 2. Reset password URL. */
				__( '<strong>ERROR</strong>: The username or password you entered is incorrect. <a href="%2$s" title="Password Lost and Found">Lost your password</a>?' ), $username, wp_lostpassword_url() ) );
		}
		
		return $authUser;
	}
	public static function wfsnBatchReportBlockedAttempts() {
		if (!defined('DONOTCACHEDB')) { define('DONOTCACHEDB', true); }
		$threshold = wfConfig::get('lastBruteForceDataSendTime', 0);;
		
		$wfdb = new wfDB();
		global $wpdb;
		$table_wfHits = wfDB::networkTable('wfHits');
		$rawBlocks = $wfdb->querySelect("SELECT SQL_CALC_FOUND_ROWS IP, ctime, actionData FROM {$table_wfHits} WHERE ctime > %f AND action = 'blocked:wfsnrepeat' ORDER BY ctime ASC LIMIT 100", sprintf('%.6f', $threshold));
		$totalRows = $wpdb->get_var('SELECT FOUND_ROWS()');
		$ipCounts = array();
		$maxctime = 0;
		foreach ($rawBlocks as $record) {
			$maxctime = max($maxctime, $record['ctime']);
			$endpointType = 0;
			if (!empty($record['actionData'])) {
				$actionData = wfRequestModel::unserializeActionData($record['actionData']);
				if (isset($actionData['type'])) {
					$endpointType = $actionData['type'];
				}
			}
			if (isset($ipCounts[$record['IP']])) {
				$ipCounts[$record['IP']] = array();
			}
			
			if (isset($ipCounts[$record['IP']][$endpointType])) {
				$ipCounts[$record['IP']][$endpointType]++;
			}
			else {
				$ipCounts[$record['IP']][$endpointType] = 1;
			}
		}
		
		$toSend = array();
		foreach ($ipCounts as $IP => $endpoints) {
			foreach ($endpoints as $endpointType => $count) {
				$toSend[] = array('IP' => base64_encode($IP), 'count' => $count, 'blocked' => 1, 'type' => $endpointType);
			}
		}
		
		try {
			$response = wp_remote_post(WORDFENCE_HACKATTEMPT_URL_SEC . 'multipleHackAttempts/?k=' . rawurlencode(wfConfig::get('apiKey')) . '&t=brute', array(
				'timeout' => 1,
				'user-agent' => "Wordfence.com UA " . (defined('WORDFENCE_VERSION') ? WORDFENCE_VERSION : '[Unknown version]'),
				'body' => 'IPs=' . rawurlencode(json_encode($toSend)),
				'headers' => array('Referer' => false),
			));
			
			if (!is_wp_error($response)) {
				if ($totalRows > 100) {
					self::wfsnScheduleBatchReportBlockedAttempts();
				}
				
				wfConfig::set('lastBruteForceDataSendTime', $maxctime);
			}
			else {
				self::wfsnScheduleBatchReportBlockedAttempts();
			}
		} 
		catch (Exception $err) {
			//Do nothing
		}
	}
	private static function wfsnScheduleBatchReportBlockedAttempts($timeToSend = null) {
		if (!defined('DONOTCACHEDB')) { define('DONOTCACHEDB', true); }
		if ($timeToSend === null) {
			$timeToSend = time() + 30;
		}
		$notMainSite = is_multisite() && !is_main_site();
		if ($notMainSite) {
			global $current_site;
			switch_to_blog($current_site->blog_id);
		}
		if (!wp_next_scheduled('wordfence_batchReportBlockedAttempts')) {
			wp_schedule_single_event($timeToSend, 'wordfence_batchReportBlockedAttempts');
		}
		if ($notMainSite) {
			restore_current_blog();
		}
	}
	public static function wfsnReportBlockedAttempt($IP, $type){
		if (!defined('DONOTCACHEDB')) { define('DONOTCACHEDB', true); }
		self::wfsnScheduleBatchReportBlockedAttempts();
		$endpointType = self::wfsnEndpointType();
		self::getLog()->getCurrentRequest()->actionData = wfRequestModel::serializeActionData(array('type' => $endpointType));
	}
	public static function wfsnBatchReportFailedAttempts() {
		if (!defined('DONOTCACHEDB')) { define('DONOTCACHEDB', true); }
		$threshold = time();
		
		$wfdb = new wfDB();
		$table_wfSNIPCache = wfDB::networkTable('wfSNIPCache');
		$rawRecords = $wfdb->querySelect("SELECT id, IP, type, count, 1 AS failed FROM {$table_wfSNIPCache} WHERE count > 0 AND expiration < FROM_UNIXTIME(%d) LIMIT 100", $threshold);
		$toSend = array();
		$toDelete = array();
		if (count($rawRecords)) {
			foreach ($rawRecords as $record) {
				$toDelete[] = $record['id'];
				unset($record['id']);
				$record['IP'] = base64_encode(filter_var($record['IP'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) ? wfUtils::inet_aton($record['IP']) : wfUtils::inet_pton($record['IP']));
				
				$key = $record['IP'] . $record['type']; //Aggregate multiple records if for some reason there are multiple for an IP/type combination
				if (!isset($toSend[$key])) {
					$toSend[$key] = $record;
				}
				else {
					$toSend[$key]['count'] += $record['count'];
				}
			}
			
			$toSend = array_values($toSend);
			
			try {
				$response = wp_remote_post(WORDFENCE_HACKATTEMPT_URL_SEC . 'multipleHackAttempts/?k=' . rawurlencode(wfConfig::get('apiKey')) . '&t=brute', array(
					'timeout' => 1,
					'user-agent' => "Wordfence.com UA " . (defined('WORDFENCE_VERSION') ? WORDFENCE_VERSION : '[Unknown version]'),
					'body' => 'IPs=' . rawurlencode(json_encode($toSend)),
					'headers' => array('Referer' => false),
				));
				
				if (is_wp_error($response)) {
					self::wfsnScheduleBatchReportFailedAttempts();
					return;
				}
			} 
			catch (Exception $err) {
				//Do nothing
			}
		}
		array_unshift($toDelete, $threshold);
		$wfdb->queryWriteIgnoreError("DELETE FROM {$table_wfSNIPCache} WHERE (expiration < FROM_UNIXTIME(%d) AND count = 0)" . (count($toDelete) > 1 ? " OR id IN (" . rtrim(str_repeat('%d, ', count($toDelete) - 1), ', ') . ")" : ""), $toDelete);
		
		$remainingRows = $wfdb->querySingle("SELECT COUNT(*) FROM {$table_wfSNIPCache}");
		if ($remainingRows > 0) {
			self::wfsnScheduleBatchReportFailedAttempts();
		}
	}
	private static function wfsnScheduleBatchReportFailedAttempts($timeToSend = null) {
		if (!defined('DONOTCACHEDB')) { define('DONOTCACHEDB', true); }
		if ($timeToSend === null) {
			$timeToSend = time() + 30;
		}
		$notMainSite = is_multisite() && !is_main_site();
		if ($notMainSite) {
			global $current_site;
			switch_to_blog($current_site->blog_id);
		}
		if (!wp_next_scheduled('wordfence_batchReportFailedAttempts')) {
			wp_schedule_single_event($timeToSend, 'wordfence_batchReportFailedAttempts');
		}
		if ($notMainSite) {
			restore_current_blog();
		}
	}
	public static function wfsnIsBlocked($IP, $hitType, $endpointType = null) {
		if (!defined('DONOTCACHEDB')) { define('DONOTCACHEDB', true); }
		$wfdb = new wfDB();
		if ($endpointType === null) { $endpointType = self::wfsnEndpointType(); }
		$table_wfSNIPCache = wfDB::networkTable('wfSNIPCache');
		$cachedRecord = $wfdb->querySingleRec("SELECT id, body FROM {$table_wfSNIPCache} WHERE IP = '%s' AND type = %d AND expiration > NOW()", $IP, $endpointType);
		if (isset($cachedRecord)) {
			$wfdb->queryWriteIgnoreError("UPDATE {$table_wfSNIPCache} SET count = count + 1 WHERE id = %d", $cachedRecord['id']);
			if (preg_match('/BLOCKED:(\d+)/', $cachedRecord['body'], $matches) && (!wfBlock::isWhitelisted($IP))) {
				return $matches[1];
			}
			return false;
		}
		
		$backoff = get_transient('wfsn_backoff');
		if ($backoff) {
			return false;
		}
		
		try {
			$result = wp_remote_get(WORDFENCE_HACKATTEMPT_URL_SEC . 'hackAttempt/?k=' . rawurlencode(wfConfig::get('apiKey')) . 
																			'&IP=' . rawurlencode(filter_var($IP, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) ? wfUtils::inet_aton($IP) : wfUtils::inet_pton($IP)) . 
																			'&t=' . rawurlencode($hitType) .
																			'&type=' . $endpointType, 
				array(
					'timeout' => 3,
					'user-agent' => "Wordfence.com UA " . (defined('WORDFENCE_VERSION') ? WORDFENCE_VERSION : '[Unknown version]'),
					'headers' => array('Referer' => false),
				));
			if (is_wp_error($result)) {
				set_transient('wfsn_backoff', 1, WORDFENCE_NOC3_FAILED_BACKOFF_TIME);
				return false;
			}
			$wfdb->queryWriteIgnoreError("INSERT INTO {$table_wfSNIPCache} (IP, type, expiration, body) VALUES ('%s', %d, DATE_ADD(NOW(), INTERVAL %d SECOND), '%s')", $IP, $endpointType, 30, $result['body']);
			self::wfsnScheduleBatchReportFailedAttempts();
			if (preg_match('/BLOCKED:(\d+)/', $result['body'], $matches) && (!wfBlock::isWhitelisted($IP))) {
				return $matches[1];
			}
			return false;
		} catch (Exception $err) {
			set_transient('wfsn_backoff', 1, WORDFENCE_NOC3_FAILED_BACKOFF_TIME);
			return false;
		}
	}
	public static function wfsnEndpointType() {
		$type = 0; //Unknown
		if (defined('XMLRPC_REQUEST') && XMLRPC_REQUEST) {
			$type = 2;
		}
		else if (defined('DOING_AJAX') && DOING_AJAX) {
			$type = 3;
			if (isset($_REQUEST['action']) && ($_REQUEST['action'] == 'wordfence_ls_authenticate' || $_REQUEST['action'] == 'nopriv_wordfence_ls_authenticate')) {
				$type = 301;
			}
		}
		else if (strpos($_SERVER['REQUEST_URI'], '/wp-login.php') !== false) {
			$type = 1;
		}
		return $type;
	}
	public static function logoutAction(){
		$userID = self::getLog()->getCurrentRequest()->userID;
		$userDat = get_user_by('id', $userID);
		if(is_object($userDat)){
			self::getLog()->logLogin('logout', 0, $userDat->user_login);
		}
		// Unset the roadblock cookie
		if (!WFWAF_SUBDIRECTORY_INSTALL) {
			wfUtils::setcookie(wfWAF::getInstance()->getAuthCookieName(), ' ', time() - (86400 * 365), '/', null, wfUtils::isFullSSL(), true);
		}
	}
	public static function loginInitAction() {
		$lockout = wfBlock::lockoutForIP(wfUtils::getIP());
		if ($lockout !== false) {
			$lockout->recordBlock();
			$customText = wpautop(wp_strip_all_tags(wfConfig::get('blockCustomText', '')));
			require(dirname(__FILE__) . '/wfLockedOut.php');
		}
		
		self::doEarlyAccessLogging(); //Rate limiting
	}
	public static function authActionNew(&$username, &$passwd){ //As of php 5.4 we must denote passing by ref in the function definition, not the function call (as WordPress core does, which is a bug in WordPress).
		$lockout = wfBlock::lockoutForIP(wfUtils::getIP());
		if ($lockout !== false) {
			$lockout->recordBlock();
			$customText = wpautop(wp_strip_all_tags(wfConfig::get('blockCustomText', '')));
			require(dirname(__FILE__) . '/wfLockedOut.php');
		}
		
		if (isset($_POST['wordfence_twoFactorUser'])) { //Final stage of login -- get and verify 2fa code, make sure we load the appropriate user
			$userID = intval($_POST['wordfence_twoFactorUser']);
			$twoFactorNonce = preg_replace('/[^a-f0-9]/i', '', $_POST['wordfence_twoFactorNonce']);
			if (self::verifyTwoFactorIntermediateValues($userID, $twoFactorNonce)) {
				$user = get_user_by('ID', $userID);
				$username = $user->user_login;
				$passwd = $twoFactorNonce;
				self::$userDat = $user;
				return;
			}
		}
		
		if (is_array($username) || is_array($passwd)) { return; }
		
		//Intermediate stage of login
		if(! $username){ return; }
		$userDat = get_user_by('login', $username);
		if (!$userDat) {
			$userDat = get_user_by('email', $username);
		}
		
		self::$userDat = $userDat;
		if(preg_match(self::$passwordCodePattern, $passwd, $matches)){
			$_POST['wordfence_authFactor'] = $matches[1];
			$passwd = preg_replace('/^(.+)\s+wf([a-z0-9 ]+)$/i', '$1', $passwd);
			$_POST['pwd'] = $passwd;
		}
	}
	public static function authActionOld($username, $passwd){ //Code is identical to Newer function above except passing by ref ampersand. Some versions of PHP are throwing an error if we include the ampersand in PHP prior to 5.4.
		$lockout = wfBlock::lockoutForIP(wfUtils::getIP());
		if ($lockout !== false) {
			$lockout->recordBlock();
			$customText = wpautop(wp_strip_all_tags(wfConfig::get('blockCustomText', '')));
			require(dirname(__FILE__) . '/wfLockedOut.php');
		}
		
		if (isset($_POST['wordfence_twoFactorUser'])) { //Final stage of login -- get and verify 2fa code, make sure we load the appropriate user
			$userID = intval($_POST['wordfence_twoFactorUser']);
			$twoFactorNonce = preg_replace('/[^a-f0-9]/i', '', $_POST['wordfence_twoFactorNonce']);
			if (self::verifyTwoFactorIntermediateValues($userID, $twoFactorNonce)) {
				$user = get_user_by('ID', $userID);
				$username = $user->user_login;
				$passwd = $twoFactorNonce;
				self::$userDat = $user;
				return;
			}
		}
		
		if (is_array($username) || is_array($passwd)) { return; }
		
		//Intermediate stage of login
		if(! $username){ return; }
		$userDat = get_user_by('login', $username);
		if (!$userDat) {
			$userDat = get_user_by('email', $username);
		}
		
		self::$userDat = $userDat;
		if(preg_match(self::$passwordCodePattern, $passwd, $matches)){
			$_POST['wordfence_authFactor'] = $matches[1];
			$passwd = preg_replace('/^(.+)\s+wf([a-z0-9 ]+)$/i', '$1', $passwd);
			$_POST['pwd'] = $passwd;
		}
	}
	public static function getWPFileContent($file, $cType, $cName, $cVersion){
		if ($cType == 'plugin') {
			if (preg_match('#^/?wp-content/plugins/[^/]+/#', $file)) {
				$file = preg_replace('#^/?wp-content/plugins/[^/]+/#', '', $file);
			}
			else {
				//If user is using non-standard wp-content dir, then use /plugins/ in pattern to figure out what to strip off
				$file = preg_replace('#^.*[^/]+/plugins/[^/]+/#', '', $file);
			}
		}
		else if ($cType == 'theme') {
			if (preg_match('#/?wp-content/themes/[^/]+/#', $file)) {
				$file = preg_replace('#/?wp-content/themes/[^/]+/#', '', $file);
			}
			else {
				$file = preg_replace('#^.*[^/]+/themes/[^/]+/#', '', $file);
			}
		}
		else if ($cType == 'core') {
			//No special processing
		}
		else {
			return array('errorMsg' => __('An invalid type was specified to get file.', 'wordfence'));
		}
		
		$api = new wfAPI(wfConfig::get('apiKey'), wfUtils::getWPVersion());
		try {
			$contResult = $api->binCall('get_wp_file_content', array(
				'v' => wfUtils::getWPVersion(),
				'file' => $file,
				'cType' => $cType,
				'cName' => $cName,
				'cVersion' => $cVersion
				));
			if ($contResult['data']) {
				return array('fileContent' => $contResult['data']);
			}
			
			throw new Exception(__('We could not fetch a core WordPress file from the Wordfence API.', 'wordfence'));
		}
		catch (Exception $e) {
			return array('errorMsg' => wp_kses($e->getMessage(), array()));
		}
	}
	public static function ajax_sendDiagnostic_callback(){
		add_filter('gettext', 'wordfence::_diagnosticsTranslationDisabler', 0, 3);
		$inEmail = true;
		$body = "This email is the diagnostic from " . site_url() . ".\nThe IP address that requested this was: " . wfUtils::getIP() . "\nTicket Number/Forum Username: " . $_POST['ticket'];
		$sendingDiagnosticEmail = true;
		ob_start();
		require(dirname(__FILE__) . '/menu_tools_diagnostic.php');
		$body = nl2br($body) . ob_get_clean();
		$findReplace = array(
			'<div class="wf-block-header">' => '<div style="margin:20px 0px 0px;padding:6px 4px;background-color:#222;color:#fff;width:926px;">',
			'<th ' => '<th style="text-align:left;background-color:#222;color:#fff;"',
			'<th>' => '<th style="text-align:left;background-color:#222;color:#fff;">',
			' class="wf-result-success"' => ' style="font-weight:bold;color:#008c10;" class="wf-result-success"',
			' class="wf-result-error"' => ' style="font-weight:bold;color:#d0514c;" class="wf-result-error"',
			' class="wf-result-inactive"' => ' style="font-weight:bold;color:#666666;" class="wf-result-inactive"',
		);
		$body = str_replace(array_keys($findReplace), array_values($findReplace), $body);
		$result = wfUtils::htmlEmail($_POST['email'], '[Wordfence] Diagnostic results (' . $_POST['ticket'] . ')', $body);
		if (function_exists('remove_filter')) { remove_filter('gettext', 'wordfence::_diagnosticsTranslationDisabler', 0); } //Remove for consistency. It's okay if it doesn't pre-4.7.0 since the call exits anyway.
		return compact('result');
	}
	public static function ajax_exportDiagnostics_callback(){
		add_filter('gettext', 'wordfence::_diagnosticsTranslationDisabler', 0, 3);

		$url = site_url();
		$url = preg_replace('/^https?:\/\//i', '', $url);
		$url = preg_replace('/[^a-zA-Z0-9\.]+/', '_', $url);
		$url = preg_replace('/^_+/', '', $url);
		$url = preg_replace('/_+$/', '', $url);

		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename="diagnostics_for_' . $url . '.txt"');

		echo wfView::create('diagnostics/text', array(
			'diagnostic' => new wfDiagnostic,
			'plugins' => get_plugins(),
		));
		exit;
	}
	public static function _diagnosticsTranslationDisabler($translation, $text, $domain) {
		return $text;
	}
	public static function ajax_sendTestEmail_callback(){
		$rawEmails = explode(",", $_POST['email']);
		$emails = array();
		foreach ($rawEmails as $e) {
			$e = trim($e);
			if (wfUtils::isValidEmail($e)) {
				$emails[] = $e;
			}
		}
		$result = false;
		if (count($emails)) {
			$result = wp_mail(implode(', ', $emails), __('Wordfence Test Email', 'wordfence'), sprintf(/* translators: 1. Site URL. 2. IP address. */ __("This is a test email from %1\$s.\nThe IP address that requested this was: %2\$s", 'wordfence'), site_url(), wfUtils::getIP()));
		}
		$result = $result ? 'True' : 'False';
		return array('result' => $result);
	}
	public static function ajax_addTwoFactor_callback(){
		if(! wfConfig::get('isPaid')){
			return array('errorMsg' => __('Cellphone Sign-in is only available to paid members. <a href="https://www.wordfence.com/gnl1twoFac3/wordfence-signup/" target="_blank" rel="noopener noreferrer">Click here to upgrade now.</a>', 'wordfence'));
		}
		$username = sanitize_text_field($_POST['username']);
		$phone = sanitize_text_field($_POST['phone']);
		$mode = sanitize_text_field($_POST['mode']);
		$user = get_user_by('login', $username);
		if(! $user){
			return array('errorMsg' => __("The username you specified does not exist.", 'wordfence'));
		}
		
		$twoFactorUsers = wfConfig::get_ser('twoFactorUsers', array());
		if (!is_array($twoFactorUsers)) {
			$twoFactorUsers = array();
		}
		for ($i = 0; $i < sizeof($twoFactorUsers); $i++) {
			if ($twoFactorUsers[$i][0] == $user->ID) {
				return array('errorMsg' => __("The username you specified is already enabled.", 'wordfence'));
			}
		}
		
		if ($mode != 'phone' && $mode != 'authenticator') {
			return array('errorMsg' => __("Unknown authentication mode.", 'wordfence'));
		}
		
		if ($mode == 'phone') {
			if (!preg_match('/^\+\d[\d\-\(\)\s]+$/', $phone)) {
				return array('errorMsg' => __("The phone number you entered must start with a '+', then country code and then area code and number. For example, a number in the United States with country code '1' would look like this: +1-123-555-1234", 'wordfence'));
			}
			$api = new wfAPI(wfConfig::get('apiKey'), wfUtils::getWPVersion());
			try {
				$codeResult = $api->call('twoFactorTOTP_register', array(), array('phone' => $phone, 'mode' => $mode));
			}
			catch (Exception $e) {
				return array('errorMsg' => sprintf(__("Could not contact Wordfence servers to generate a verification code: %s", 'wordfence'), wp_kses($e->getMessage(), array())));
			}
			
			$recoveryCodes = preg_replace('/[^a-f0-9]/i', '', $codeResult['recoveryCodes']);
			
			if (isset($codeResult['ok']) && $codeResult['ok']) {
				$secretID = $codeResult['id'];
			}
			else if (isset($codeResult['errorMsg']) && $codeResult['errorMsg']) {
				return array('errorMsg' => wp_kses($codeResult['errorMsg'], array()));
			}
			else {
				wordfence::status(4, 'info', sprintf(__("Could not generate verification code: %s", 'wordfence'), var_export($codeResult, true)));
				return array('errorMsg' => __("We could not generate a verification code.", 'wordfence'));
			}
			self::twoFactorAdd($user->ID, $phone, '', 'phone', $secretID);
			return array(
				'ok' => 1,
				'userID' => $user->ID,
				'username' => $username,
				'homeurl' => preg_replace('#.*?//#', '', get_home_url()),
				'mode' => $mode,
				'phone' => $phone,
				'recoveryCodes' => $recoveryCodes,
			);
		}
		else if ($mode == 'authenticator') {
			$api = new wfAPI(wfConfig::get('apiKey'), wfUtils::getWPVersion());
			try {
				$codeResult = $api->call('twoFactorTOTP_register', array(), array('mode' => $mode));
			}
			catch (Exception $e) {
				return array('errorMsg' => sprintf(/* translators: Error message. */ __("Could not contact Wordfence servers to generate a verification code: %s", 'wordfence'), wp_kses($e->getMessage(), array())));
			}
			
			/* Expected Fields:
				'ok' => 1,
				'secret' => $secret,
				'base32Secret' => $base32Secret,
				'recoveryCodes' => $codes,
				'uriQueryString' => $uriQueryString,
				'id' => $recordID,
			*/
			
			$secret = preg_replace('/[^a-f0-9]/i', '', $codeResult['secret']);
			$base32Secret = preg_replace('/[^a-z2-7]/i', '', $codeResult['base32Secret']); //Encoded in base32
			$recoveryCodes = preg_replace('/[^a-f0-9]/i', '', $codeResult['recoveryCodes']);
			$uriQueryString = preg_replace('/[^a-z0-9=&]/i', '', $codeResult['uriQueryString']);
			
			if (isset($codeResult['ok']) && $codeResult['ok']) {
				$secretID = $codeResult['id'];
			}
			else if (isset($codeResult['errorMsg']) && $codeResult['errorMsg']) {
				return array('errorMsg' => wp_kses($codeResult['errorMsg'], array()));
			}
			else {
				wordfence::status(4, 'info', sprintf(/* translators: Error message. */ __("Could not generate verification code: %s", 'wordfence'), var_export($codeResult, true)));
				return array('errorMsg' => __("We could not generate a verification code.", 'wordfence'));
			}
			self::twoFactorAdd($user->ID, '', '', 'authenticator', $secretID);
			return array(
				'ok' => 1,
				'userID' => $user->ID,
				'username' => $username,
				'homeurl' => preg_replace('#.*?//#', '', get_home_url()),
				'mode' => $mode,
				'secret' => $secret,
				'base32Secret' => $base32Secret,
				'recoveryCodes' => $recoveryCodes,
				'uriQueryString' => $uriQueryString,
			);
		}
		
		return array('errorMsg' => __("Unknown two-factor authentication mode.", 'wordfence'));
	}
	public static function ajax_twoFacActivate_callback() {
		$userID = sanitize_text_field($_POST['userID']);
		$code = sanitize_text_field($_POST['code']);
		$twoFactorUsers = wfConfig::get_ser('twoFactorUsers', array());
		if (!is_array($twoFactorUsers)) {
			$twoFactorUsers = array();
		}
		$found = false;
		$user = false;
		for ($i = 0; $i < sizeof($twoFactorUsers); $i++) {
			if ($twoFactorUsers[$i][0] == $userID) {
				$mode = 'phone';
				if (isset($twoFactorUsers[$i][5]) && $twoFactorUsers[$i][5] == 'authenticator') {
					$mode = 'authenticator';
				}
				$api = new wfAPI(wfConfig::get('apiKey'), wfUtils::getWPVersion());
				try {
					$codeResult = $api->call('twoFactorTOTP_verify', array(), array('totpid' => $twoFactorUsers[$i][6], 'code' => $code, 'mode' => $mode));
				}
				catch (Exception $e) {
					return array('errorMsg' => sprintf(/* translators: Error message. */ __("Could not contact Wordfence servers to generate a verification code: %s", 'wordfence'), wp_kses($e->getMessage(), array())));
				}
				
				if (isset($codeResult['ok']) && $codeResult['ok']) {
					$twoFactorUsers[$i][3] = 'activated';
					$twoFactorUsers[$i][4] = 0;
					$found = true;
					$user = $twoFactorUsers[$i];
					break;
				}
				else {
					return array('errorMsg' => __("The code you entered is invalid. Cellphone sign-in will not be enabled for this user until you enter a valid code.", 'wordfence'));
				}
			}
		}
		if(! $found){
			return array('errorMsg' => __("We could not find the user you are trying to activate. They may have been removed from the list of Cellphone Sign-in users. Please reload this page.", 'wordfence'));
		}
		wfConfig::set_ser('twoFactorUsers', $twoFactorUsers);
		$WPuser = get_userdata($userID);
		if ($mode == 'authenticator') {
			return array(
				'ok' => 1,
				'userID' => $userID,
				'username' => $WPuser->user_login,
				'status' => 'activated',
				'mode' => 'authenticator'
			);
		}
		
		return array(
			'ok' => 1,
			'userID' => $userID,
			'username' => $WPuser->user_login,
			'phone' => $user[1],
			'status' => 'activated',
			'mode' => 'phone'
			);
	}
	private static function twoFactorAdd($ID, $phone, $code, $mode, $totpID){
		$twoFactorUsers = wfConfig::get_ser('twoFactorUsers', array());
		if(! is_array($twoFactorUsers)){
			$twoFactorUsers = array();
		}
		for($i = 0; $i < sizeof($twoFactorUsers); $i++){
			if($twoFactorUsers[$i][0] == $ID || (! $twoFactorUsers[$i][0]) ){
				array_splice($twoFactorUsers, $i, 1);
				$i--;
			}
		}
		$twoFactorUsers[] = array($ID, $phone, $code /* deprecated parameter */, 'notActivated', time() + (86400 * 30) /* deprecated parameter */, $mode, $totpID); //expiry of code is 30 days in future
		wfConfig::set_ser('twoFactorUsers', $twoFactorUsers);
	}
	public static function ajax_loadTwoFactor_callback() {
		$users = wfConfig::get_ser('twoFactorUsers', array());
		$ret = array();
		foreach ($users as $user) {
			$WPuser = get_userdata($user[0]);
			if ($user) {
				if (isset($user[5]) && $user[5] == 'authenticator') { 
					$ret[] = array(
						'userID' => $user[0],
						'username' => $WPuser->user_login,
						'status' => $user[3],
						'mode' => 'authenticator'
					);
				}
				else {
					$ret[] = array(
						'userID' => $user[0],
						'username' => $WPuser->user_login,
						'phone' => $user[1],
						'status' => $user[3],
						'mode' => 'phone'
					);
				}
			}
		}
		return array('ok' => 1, 'users' => $ret);
	}
	public static function ajax_twoFacDel_callback(){
		$ID = $_POST['userID'];
		$twoFactorUsers = wfConfig::get_ser('twoFactorUsers', array());
		if(! is_array($twoFactorUsers)){
			$twoFactorUsers = array();
		}
		$deleted = false;
		for($i = 0; $i < sizeof($twoFactorUsers); $i++){
			if($twoFactorUsers[$i][0] == $ID){
				array_splice($twoFactorUsers, $i, 1);
				$deleted = true;
				$i--;
			}
		}
		wfConfig::set_ser('twoFactorUsers', $twoFactorUsers);
		if($deleted){
			return array('ok' => 1, 'userID' => $ID);
		} else {
			return array('errorMsg' => __("That user has already been removed from the list.", 'wordfence'));
		}
	}
	public static function getNextScanStartTimestamp() {
		$nextTime = false;
		$cron = _get_cron_array();
		foreach($cron as $key => $val){
			if(isset($val['wordfence_start_scheduled_scan'])){
				$nextTime = $key;
				break;
			}
		}
		return $nextTime;
	}
	public static function getNextScanStartTime($nextTime = null) {
		if ($nextTime === null) {
			$nextTime = self::getNextScanStartTimestamp();
		}
		
		if (!$nextTime) {
			return __('No scan is scheduled', 'wordfence');
		}
		
		$difference = $nextTime - time();
		if ($difference < 1) {
			return __("Next scan is starting now", 'wordfence');
		}

		return sprintf(/* translators: 1. Time until. 2. Localized date. */ __('Next scan in %1$s (%2$s)', 'wordfence'), wfUtils::makeDuration($difference), date_i18n('M j, Y g:i:s A', $nextTime + (3600 * get_option('gmt_offset'))));
	}
	public static function wordfenceStartScheduledScan($scheduledStartTime) {

		//If scheduled scans are not enabled in the global config option, then don't run a scheduled scan.
		if(wfConfig::get('scheduledScansEnabled') != '1'){
			return;
		}

		$minimumFrequency = (wfScanner::shared()->schedulingMode() == wfScanner::SCAN_SCHEDULING_MODE_MANUAL ? 1800 : 43200);
		$lastScanStart = wfConfig::get('lastScheduledScanStart', 0);
		if($lastScanStart && (time() - $lastScanStart) < $minimumFrequency){
			//A scheduled scan was started in the last 30 mins (manual schedule) or 12 hours (automatic schedule), so skip this one.
			return;
		}
		wfConfig::set('originalScheduledScanStart', $scheduledStartTime);
		wfConfig::set('lastScheduledScanStart', time());
		wordfence::status(1, 'info', sprintf(/* translators: Localized date. */ __("Scheduled Wordfence scan starting at %s", 'wordfence'), date('l jS \of F Y h:i:s A', current_time('timestamp'))) );

		//We call this before the scan actually starts to advance the schedule for the next week.
		//This  ensures that if the scan crashes for some reason, the schedule will hold.
		wfScanner::shared()->scheduleScans();

		try {
			wfScanEngine::startScan();
		}
		catch (wfScanEngineTestCallbackFailedException $e) {
			wfConfig::set('lastScanCompleted', $e->getMessage());
			wfConfig::set('lastScanFailureType', wfIssues::SCAN_FAILED_CALLBACK_TEST_FAILED);
			wfUtils::clearScanLock();
		}
		catch (Exception $e) {
			if ($e->getCode() != wfScanEngine::SCAN_MANUALLY_KILLED) {
				wfConfig::set('lastScanCompleted', $e->getMessage());
				wfConfig::set('lastScanFailureType', wfIssues::SCAN_FAILED_GENERAL);
			}
		}
	}
	public static function ajax_saveCountryBlocking_callback(){
		if(! wfConfig::get('isPaid')){
			return array('errorMsg' => __("Sorry but this feature is only available for paid customers.", 'wordfence'));
		}
		wfConfig::set('cbl_action', $_POST['blockAction']);
		wfConfig::set('cbl_countries', $_POST['codes']);
		wfConfig::set('cbl_redirURL', $_POST['redirURL']);
		wfConfig::set('cbl_loggedInBlocked', $_POST['loggedInBlocked']);
		wfConfig::set('cbl_loginFormBlocked', $_POST['loginFormBlocked']);
		wfConfig::set('cbl_restOfSiteBlocked', $_POST['restOfSiteBlocked']);
		wfConfig::set('cbl_bypassRedirURL', $_POST['bypassRedirURL']);
		wfConfig::set('cbl_bypassRedirDest', $_POST['bypassRedirDest']);
		wfConfig::set('cbl_bypassViewURL', $_POST['bypassViewURL']);
		return array('ok' => 1);
	}
	public static function ajax_sendActivityLog_callback(){
		$content  = sprintf(/* translators: Site URL. */ __('SITE: %s', 'wordfence'), site_url()) . "\n";
		$content .= sprintf(/* translators: Plugin version. */ __('PLUGIN VERSION: %s', 'wordfence'), WORDFENCE_VERSION) . "\n";
		$content .= sprintf(/* translators: WordPress version. */ __('WORDPRESS VERSION: %s', 'wordfence'), wfUtils::getWPVersion()) . "\n";
		$content .= sprintf(/* translators: Wordfence license key. */ __('LICENSE KEY: %s', 'wordfence'), wfConfig::get('apiKey')) . "\n";
		$content .= sprintf(/* translators: Email address. */ __('ADMIN EMAIL: %s', 'wordfence'), get_option('admin_email')) . "\n";
		$content .= __('LOG:', 'wordfence') . "\n\n";

		$wfdb = new wfDB();
		$table_wfStatus = wfDB::networkTable('wfStatus');
		$q = $wfdb->querySelect("select ctime, level, type, msg from {$table_wfStatus} order by ctime desc limit 10000");
		$timeOffset = 3600 * get_option('gmt_offset');
		foreach($q as $r){
			if($r['type'] == 'error'){
				$content .= "\n";
			}
			$content .= date(DATE_RFC822, $r['ctime'] + $timeOffset) . '::' . sprintf('%.4f', $r['ctime']) . ':' . $r['level'] . ':' . $r['type'] . '::' . wp_kses_data( (string) $r['msg']) . "\n";
		}
		$content .= "\n\n";
		$content .= str_repeat('-', 80);
		$content .= "\n\n";
		
		$content .= __('# Scan Issues', 'wordfence') . "\n\n";
		$issues = wfIssues::shared()->getIssues(0, 50, 0, 50);
		$issueCounts = array_merge(array('new' => 0, 'ignoreP' => 0, 'ignoreC' => 0), wfIssues::shared()->getIssueCounts());
		$issueTypes = wfIssues::validIssueTypes();
		
		$content .= sprintf(/* translators: Number of scan results. */ __('## New Issues (%d total)', 'wordfence'), $issueCounts['new']) . "\n\n";
		if (isset($issues['new']) && count($issues['new'])) {
			foreach ($issues['new'] as $i) {
				if (!in_array($i['type'], $issueTypes)) {
					continue;
				}
				
				$viewContent = '';
				try {
					$viewContent = wfView::create('scanner/issue-' . $i['type'], array('textOutput' => $i))->render();
				}
				catch (wfViewNotFoundException $e) {
					//Ignore -- should never happen since we validate the type
				}
				
				if (!empty($viewContent)) {
					$content .= $viewContent . "\n\n";
				}
			}
		}
		else {
			$content .= __('No New Issues', 'wordfence') . "\n\n";
		}
		
		$content .= str_repeat('-', 10);
		$content .= "\n\n";
		
		$content .= sprintf(/* translators: Number of scan results. */ __('## Ignored Issues (%d total)', 'wordfence'), $issueCounts['ignoreP'] + $issueCounts['ignoreC']) . "\n\n";
		if (isset($issues['new']) && count($issues['new'])) {
			foreach ($issues['ignored'] as $i) {
				if (!in_array($i['type'], $issueTypes)) {
					continue;
				}
				
				$viewContent = '';
				try {
					$viewContent = wfView::create('scanner/issue-' . $i['type'], array('textOutput' => $i))->render();
				}
				catch (wfViewNotFoundException $e) {
					//Ignore -- should never happen since we validate the type
				}
				
				if (!empty($viewContent)) {
					$content .= $viewContent . "\n\n";
				}
			}
		}
		else {
			$content .= __('No Ignored Issues', 'wordfence') . "\n\n";
		}
		
		$content .= str_repeat('-', 80);
		$content .= "\n\n";

		ob_start();
		phpinfo();
		$phpinfo = ob_get_contents();
		ob_get_clean();

		$content .= $phpinfo;

		$rawEmails = explode(",", $_POST['email']);
		$emails = array();
		foreach ($rawEmails as $e) {
			$e = trim($e);
			if (wfUtils::isValidEmail($e)) {
				$emails[] = $e;
			}
		}
		if (count($emails)) {
			wp_mail(implode(', ', $emails), __('Wordfence Activity Log', 'wordfence'), $content);
		}
		return array('ok' => 1);
	}
	public static function ajax_downgradeLicense_callback(){
		$api = new wfAPI('', wfUtils::getWPVersion());
		try {
			$keyData = $api->call('get_anon_api_key', array(), array('previousLicense' => wfConfig::get('apiKey')));
			if($keyData['ok'] && $keyData['apiKey']){
				wfConfig::set('apiKey', $keyData['apiKey']);
				wfConfig::set('isPaid', 0);
				wfConfig::set('keyType', wfAPI::KEY_TYPE_FREE);
				//When downgrading we must disable all two factor authentication because it can lock an admin out if we don't.
				wfConfig::set_ser('twoFactorUsers', array());
				wfConfig::remove('premiumAutoRenew');
				wfConfig::remove('premiumNextRenew');
				wfConfig::remove('premiumPaymentExpiring');
				wfConfig::remove('premiumPaymentExpired');
				wfConfig::remove('premiumPaymentMissing');
				wfConfig::remove('premiumPaymentHold');
				self::licenseStatusChanged();
				if (method_exists(wfWAF::getInstance()->getStorageEngine(), 'purgeIPBlocks')) {
					wfWAF::getInstance()->getStorageEngine()->purgeIPBlocks(wfWAFStorageInterface::IP_BLOCKS_BLACKLIST);
				}
			} else {
				throw new Exception(__("Could not understand the response we received from the Wordfence servers when applying for a free license key.", 'wordfence'));
			}
		} catch(Exception $e){
			return array('errorMsg' => sprintf(/* translators: Error message. */ __("Could not fetch free license key from Wordfence: %s", 'wordfence'), wp_kses($e->getMessage(), array())));
		}
		return array('ok' => 1);
	}
	public static function ajax_tourClosed_callback() {
		$page = '';
		if (isset($_POST['page'])) {
			$page = $_POST['page'];
		}
		
		$keys = array(wfOnboardingController::TOUR_DASHBOARD, wfOnboardingController::TOUR_FIREWALL, wfOnboardingController::TOUR_SCAN, wfOnboardingController::TOUR_BLOCKING, wfOnboardingController::TOUR_LIVE_TRAFFIC, wfOnboardingController::TOUR_LOGIN_SECURITY);
		if (in_array($page, $keys)) {
			if (wfOnboardingController::shouldShowNewTour($page)) {
				wfConfig::set('needsNewTour_' . $page, 0);
			}
			else if (wfOnboardingController::shouldShowUpgradeTour($page)) {
				wfConfig::set('needsUpgradeTour_' . $page, 0);
			}
		}
		
		return array('ok' => 1);
	}
	public static function ajax_autoUpdateChoice_callback(){
		$choice = $_POST['choice'];
		wfConfig::set('autoUpdateChoice', '1');
		if($choice == 'yes'){
			wfConfig::set('autoUpdate', '1');
		} else {
			wfConfig::set('autoUpdate', '0');
		}
		return array('ok' => 1);
	}
	public static function ajax_misconfiguredHowGetIPsChoice_callback() {
		$choice = $_POST['choice'];
		if ($choice == 'yes') {
			wfConfig::set('howGetIPs', wfConfig::get('detectProxyRecommendation', ''));
			
			if (isset($_POST['issueID'])) {
				$issueID = intval($_POST['issueID']);
				$wfIssues = new wfIssues();
				$wfIssues->updateIssue($issueID, 'delete');
				wfScanEngine::refreshScanNotification($wfIssues);
			}
		}
		else {
			wfConfig::set('misconfiguredHowGetIPsChoice' . WORDFENCE_VERSION, '1');
		}
		return array('ok' => 1);
	}
	public static function ajax_switchLiveTrafficSecurityOnlyChoice_callback() {
		$choice = $_POST['choice'];
		if ($choice == 'yes') {
			wfConfig::set('liveTrafficEnabled', false);
		}
		else {
			wfConfig::set('switchLiveTrafficSecurityOnlyChoice', '1');
		}
		return array('ok' => 1);
	}
	public static function ajax_dismissAdminNotice_callback() {
		if (isset($_POST['id'])) {
			wfAdminNoticeQueue::removeAdminNotice($_POST['id']);
		}
		return array('ok' => 1);
	}
	public static function ajax_updateConfig_callback(){
		$key = $_POST['key'];
		$val = $_POST['val'];
		wfConfig::set($key, $val);
		
		if ($key == 'howGetIPs') {
			wfConfig::set('detectProxyNextCheck', false, wfConfig::DONT_AUTOLOAD);
			$ipAll = wfUtils::getIPPreview();
			$ip = wfUtils::getIP(true);
			return array('ok' => 1, 'ip' => $ip, 'ipAll' => $ipAll);
		}
		
		return array('ok' => 1);
	}
	public static function ajax_checkHtaccess_callback(){
		if(wfUtils::isNginx()){
			return array('nginx' => 1);
		}
		$file = wfCache::getHtaccessPath();
		if(! $file){
			return array('err' => __("We could not find your .htaccess file to modify it.", 'wordfence'));
		}
		$fh = @fopen($file, 'r+');
		if(! $fh){
			$err = error_get_last();
			return array('err' => sprintf(/* translators: Error message. */ __("We found your .htaccess file but could not open it for writing: %s", 'wordfence'), $err['message']));
		}
		return array('ok' => 1);
	}
	public static function ajax_downloadHtaccess_callback(){
		$url = site_url();
		$url = preg_replace('/^https?:\/\//i', '', $url);
		$url = preg_replace('/[^a-zA-Z0-9\.]+/', '_', $url);
		$url = preg_replace('/^_+/', '', $url);
		$url = preg_replace('/_+$/', '', $url);
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename="htaccess_Backup_for_' . $url . '.txt"');
		$file = wfCache::getHtaccessPath();
		readfile($file);
		die();
	}
	public static function ajax_downloadLogFile_callback() {
		if (!isset($_GET['logfile'])) {
			status_header(400);
			nocache_headers();
			exit;
		}
		
		wfErrorLogHandler::outputErrorLog(stripslashes($_GET['logfile'])); //exits
	}
	public static function _blocksAJAXReponse(&$hasCountryBlock = false, $offset = 0, $sortColumn = 'type', $sortDirection = 'ascending', $filter = '') {
		$includeAutomatic = wfConfig::get('displayAutomaticBlocks');
		$types = array(); //Empty array is all
		if (!$includeAutomatic) {
			$types = array(wfBlock::TYPE_IP_MANUAL, wfBlock::TYPE_IP_AUTOMATIC_PERMANENT, wfBlock::TYPE_COUNTRY, wfBlock::TYPE_PATTERN);
		}
		
		if (empty($filter)) {
			$blocks = wfBlock::allBlocks(true, $types, $offset, WORDFENCE_BLOCKED_IPS_PER_PAGE, $sortColumn, $sortDirection);
		}
		else {
			$blocks = wfBlock::filteredBlocks(true, $types, $offset, WORDFENCE_BLOCKED_IPS_PER_PAGE, $sortColumn, $sortDirection, $filter);
		}
		$dateFormat = get_option('date_format') . ' ' . get_option('time_format');
		$hasCountryBlock = wfUtils::array_first(wfBlock::countryBlocks(true));
		if ($hasCountryBlock !== null) {
			$hasCountryBlock = json_encode($hasCountryBlock->editValues());
		}
		else {
			$hasCountryBlock = '';
		}
		
		$response = array();
		foreach ($blocks as $b) {
			$skip = false;
			
			$entry = array();
			$entry['id'] = $b->id;
			$entry['typeSort'] = $b->type;
			$entry['typeDisplay'] = esc_html(wfBlock::nameForType($b->type));
			
			switch ($b->type) {
				case wfBlock::TYPE_IP_MANUAL:
					$entry['editType'] = 'ip-address';
				case wfBlock::TYPE_IP_AUTOMATIC_PERMANENT:
					$entry['detailSort'] = base64_encode(wfUtils::inet_pton($b->ip));
					$entry['detailDisplay'] = esc_html($b->ip);
					break;
				case wfBlock::TYPE_IP_AUTOMATIC_TEMPORARY:
				case wfBlock::TYPE_WFSN_TEMPORARY:
				case wfBlock::TYPE_RATE_BLOCK:
				case wfBlock::TYPE_RATE_THROTTLE:
				case wfBlock::TYPE_LOCKOUT:
					if (!$includeAutomatic) { $skip = true; }
					$entry['detailSort'] = base64_encode(wfUtils::inet_pton($b->ip));
					$entry['detailDisplay'] = esc_html($b->ip);
					break;
				case wfBlock::TYPE_COUNTRY:
					require(WORDFENCE_PATH . 'lib/wfBulkCountries.php'); /** @var array $wfBulkCountries */
					ksort($wfBulkCountries);
					$countries = $b->countries;
					sort($countries);
					$entry['editable'] = 1;
					$entry['editType'] = 'country';
					$entry['editValues'] = json_encode($b->editValues());
					$entry['detailSort'] = $b->blockLogin . '|' . $b->blockSite . '|' . implode('|', $countries);
					$entry['detailDisplay'] = '';
					if ($countries == array_keys($wfBulkCountries)) {
						$entry['detailDisplay'] = __('All Countries', 'wordfence');
					}
					else if (count($countries) == 1) {
						$entry['detailDisplay'] = __('1 Country', 'wordfence');
					}
					else {
						$entry['detailDisplay'] = sprintf(/* translators: Number of countries. */ __('%d Countries', 'wordfence'), count($countries));
					}
					
					if ($b->blockLogin && $b->blockSite) {
						$entry['detailDisplay'] .= ' (' . __('Entire Site', 'wordfence') . ')';
					}
					else if ($b->blockLogin) {
						$entry['detailDisplay'] .= ' (' . __('Login Only', 'wordfence') . ')';
					}
					else if ($b->blockSite) {
						$entry['detailDisplay'] .= ' (' . __('Site Except Login', 'wordfence') . ')';
					}
					
					break;
				case wfBlock::TYPE_PATTERN:
					$entry['editType'] = 'custom-pattern';
					$entry['detailSort'] = base64_encode($b->ipRange . '|' . $b->userAgent . '|' . $b->referrer . '|' . $b->hostname);
					$components = array();
					if (!empty($b->ipRange)) { $components[] = __('IP Range', 'wordfence') . ' - ' . $b->ipRange; }
					if (!empty($b->userAgent)) { $components[] = __('User Agent', 'wordfence') . ' - ' . $b->userAgent; }
					if (!empty($b->referrer)) { $components[] = __('Referrer', 'wordfence') . ' - ' . $b->referrer; }
					if (!empty($b->hostname)) { $components[] = __('Hostname', 'wordfence') . ' - ' . $b->hostname; }
					$entry['detailDisplay'] = esc_html(implode(', ', $components));
					break;
			}
			
			if ($skip) { continue; }
			
			$entry['ruleAdded'] = $b->blockedTime;
			$entry['ruleAddedSort'] = $b->blockedTime;
			$entry['ruleAddedDisplay'] = esc_html(wfUtils::formatLocalTime($dateFormat, $b->blockedTime));
			$entry['reasonSort'] = esc_attr($b->reason);
			$entry['reasonDisplay'] = esc_html($b->reason);
			$entry['expiration'] = $b->expiration;
			$entry['expirationSort'] = $b->expiration;
			$entry['expirationDisplay'] = ($b->expiration == wfBlock::DURATION_FOREVER ? __('Permanent', 'wordfence') : esc_html(wfUtils::formatLocalTime($dateFormat, $b->expiration)));
			$entry['blockCountSort'] = $b->blockedHits;
			$entry['blockCountDisplay'] = $b->blockedHits;
			$entry['lastAttemptSort'] = $b->lastAttempt;
			$entry['lastAttemptDisplay'] = ($b->lastAttempt == 0 ? __('Never', 'wordfence') : esc_html(wfUtils::formatLocalTime($dateFormat, $b->lastAttempt)));
			
			$response[] = $entry;
		}
		return $response;
	}
	public static function ajax_getBlocks_callback() {
		$offset = 0;
		if (isset($_POST['offset'])) {
			$offset = (int) $_POST['offset'];
		}
		
		$sortColumn = 'type';
		if (isset($_POST['sortColumn']) && in_array($_POST['sortColumn'], array('type', 'detail', 'ruleAdded', 'reason', 'expiration', 'blockCount', 'lastAttempt'))) {
			$sortColumn = $_POST['sortColumn'];
		}
		
		$sortDirection = 'ascending';
		if (isset($_POST['sortDirection']) && in_array($_POST['sortDirection'], array('ascending', 'descending'))) {
			$sortDirection = $_POST['sortDirection'];
		}
		
		$filter = '';
		if (isset($_POST['blocksFilter'])) {
			$filter = $_POST['blocksFilter'];
		}
		
		$hasCountryBlock = false;
		$blocks = self::_blocksAJAXReponse($hasCountryBlock, $offset, $sortColumn, $sortDirection, $filter);
		return array('blocks' => $blocks, 'hasCountryBlock' => $hasCountryBlock);
	}
	public static function ajax_createBlock_callback() {
		$offset = 0;
		if (isset($_POST['offset'])) {
			$offset = (int) $_POST['offset'];
		}
		
		$sortColumn = 'type';
		if (isset($_POST['sortColumn']) && in_array($_POST['sortColumn'], array('type', 'detail', 'ruleAdded', 'reason', 'expiration', 'blockCount', 'lastAttempt'))) {
			$sortColumn = $_POST['sortColumn'];
		}
		
		$sortDirection = 'ascending';
		if (isset($_POST['sortDirection']) && in_array($_POST['sortDirection'], array('ascending', 'descending'))) {
			$sortDirection = $_POST['sortDirection'];
		}
		
		$filter = '';
		if (isset($_POST['blocksFilter'])) {
			$filter = $_POST['blocksFilter'];
		}
		
		if (!empty($_POST['payload']) && ($payload = json_decode(stripslashes($_POST['payload']), true)) !== false) {
			try {
				$error = wfBlock::validate($payload);
				if ($error !== true) {
					return array(
						'error' => $error,
					);
				}
				
				wfBlock::create($payload);
				$hasCountryBlock = false;
				$blocks = self::_blocksAJAXReponse($hasCountryBlock, $offset, $sortColumn, $sortDirection, $filter);
				return array('success' => true, 'blocks' => $blocks, 'hasCountryBlock' => $hasCountryBlock);
			}
			catch (Exception $e) {
				return array(
					'error' => __('An error occurred while creating the block.', 'wordfence'),
				);
			}
		}
		
		return array(
			'error' => __('No block parameters were provided.', 'wordfence'),
		);
	}
	public static function ajax_deleteBlocks_callback() {
		$offset = 0;
		if (isset($_POST['offset'])) {
			$offset = (int) $_POST['offset'];
		}
		
		$sortColumn = 'type';
		if (isset($_POST['sortColumn']) && in_array($_POST['sortColumn'], array('type', 'detail', 'ruleAdded', 'reason', 'expiration', 'blockCount', 'lastAttempt'))) {
			$sortColumn = $_POST['sortColumn'];
		}
		
		$sortDirection = 'ascending';
		if (isset($_POST['sortDirection']) && in_array($_POST['sortDirection'], array('ascending', 'descending'))) {
			$sortDirection = $_POST['sortDirection'];
		}
		
		$filter = '';
		if (isset($_POST['blocksFilter'])) {
			$filter = $_POST['blocksFilter'];
		}
		
		if (!empty($_POST['blocks']) && ($blocks = json_decode(stripslashes($_POST['blocks']), true)) !== false && is_array($blocks)) {
			$removed = wfBlock::removeBlockIDs($blocks, true); //wfBlock::removeBlockIDs sanitizes the array
			if($removed!==false) {
				foreach($removed as $block) {
					self::clearLockoutCounters(wfUtils::inet_ntop($block->IP));
				}
			}
			$hasCountryBlock = false;
			$blocks = self::_blocksAJAXReponse($hasCountryBlock, $offset, $sortColumn, $sortDirection, $filter);
			return array('success' => true, 'blocks' => $blocks, 'hasCountryBlock' => $hasCountryBlock);
		}
		
		return array(
			'error' => __('No blocks were provided.', 'wordfence'),
		);
	}
	public static function ajax_makePermanentBlocks_callback() {
		$offset = 0;
		if (isset($_POST['offset'])) {
			$offset = (int) $_POST['offset'];
		}
		
		$sortColumn = 'type';
		if (isset($_POST['sortColumn']) && in_array($_POST['sortColumn'], array('type', 'detail', 'ruleAdded', 'reason', 'expiration', 'blockCount', 'lastAttempt'))) {
			$sortColumn = $_POST['sortColumn'];
		}
		
		$sortDirection = 'ascending';
		if (isset($_POST['sortDirection']) && in_array($_POST['sortDirection'], array('ascending', 'descending'))) {
			$sortDirection = $_POST['sortDirection'];
		}
		
		$filter = '';
		if (isset($_POST['blocksFilter'])) {
			$filter = $_POST['blocksFilter'];
		}
		
		if (!empty($_POST['updates']) && ($updates = json_decode(stripslashes($_POST['updates']), true)) !== false && is_array($updates)) {
			wfBlock::makePermanentBlockIDs($updates); //wfBlock::makePermanentBlockIDs sanitizes the array
			$hasCountryBlock = false;
			$blocks = self::_blocksAJAXReponse($hasCountryBlock, $offset, $sortColumn, $sortDirection, $filter);
			return array('success' => true, 'blocks' => $blocks, 'hasCountryBlock' => $hasCountryBlock);
		}
		
		return array(
			'error' => __('No blocks were provided.', 'wordfence'),
		);
	}
	public static function ajax_installLicense_callback() {
		if (!empty($_POST['license'])) {
			$license = strtolower(trim($_POST['license']));
			if (!preg_match('/^[a-fA-F0-9]+$/', $license)) {
				return array(
					'error' => __('The license key entered is not in a valid format. It must contain only numbers and the letters A-F.', 'wordfence'),
				);
			}
			
			$existingLicense = strtolower(wfConfig::get('apiKey', ''));
			if ($existingLicense != $license) { //Key changed, try activating
				$api = new wfAPI($license, wfUtils::getWPVersion());
				try {
					$res = $api->call('check_api_key', array(), array('previousLicense' => $existingLicense));
					if ($res['ok'] && isset($res['isPaid'])) {
						$isPaid = wfUtils::truthyToBoolean($res['isPaid']);
						wfConfig::set('apiKey', $license);
						wfConfig::set('isPaid', $isPaid); //res['isPaid'] is boolean coming back as JSON and turned back into PHP struct. Assuming JSON to PHP handles bools.
						wordfence::licenseStatusChanged();
						if (!$isPaid) {
							wfConfig::set('keyType', wfAPI::KEY_TYPE_FREE);
						}
						return array(
							'success' => 1,
							'isPaid' => wfConfig::get('isPaid') ? 1 : 0,
						);
					}
					else if (isset($res['_hasKeyConflict']) && $res['_hasKeyConflict']) {
						return array(
							'error' => __('The license provided is already in use on another site.', 'wordfence'),
						);
					}
					else {
						return array(
							'error' => __('The Wordfence activation server returned an unexpected response. Please try again.', 'wordfence'),
						);
					}
				}
				catch (Exception $e) {
					return array(
						'error' => __('We received an error while trying to activate the license with the Wordfence servers: ', 'wordfence') . wp_kses($e->getMessage(), array())
					);
				}
			}
			else {
				return array(
					'success' => 1,
					'isPaid' => wfConfig::get('isPaid') ? 1 : 0,
				);
			}
		}
		
		return array(
			'error' => __('No license was provided to install.', 'wordfence'),
		);
	}
	public static function ajax_recordTOUPP_callback() {
		$api = new wfAPI(wfConfig::get('apiKey'), wfUtils::getWPVersion());
		$result = $api->call('record_toupp', array(), array());
		wfConfig::set('touppBypassNextCheck', 1); //In case this call kicks off the cron that checks, this avoids the race condition of that setting the prompt as being needed at the same time we've just recorded it as accepted
		wfConfig::set('touppPromptNeeded', 0);
		return array(
			'success' => 1,
		);
	}
	public static function ajax_mailingSignup_callback() {
		if (isset($_POST['emails'])) {
			$emails = @json_decode(stripslashes($_POST['emails']), true);
			if (is_array($emails) && count($emails)) {
				$api = new wfAPI(wfConfig::get('apiKey'), wfUtils::getWPVersion());
				$result = $api->call('mailing_signup', array(), array('signup' => json_encode(array('emails' => $emails)), 'ip' => wfUtils::getIP()));
			}
		}
		
		return array(
			'success' => 1,
		);
	}
	public static function ajax_enableAllOptionsPage_callback() {
		wfConfig::set('displayTopLevelOptions', 1);
		$n = wfNotification::getNotificationForCategory('wfplugin_devalloptions');
		if ($n !== null) {
			$n->markAsRead();
		}
		
		$response = array('success' => true);
		if (function_exists('network_admin_url') && is_multisite()) {
			$response['redirect'] = network_admin_url('admin.php?page=WordfenceOptions');
		}
		else {
			$response['redirect'] = admin_url('admin.php?page=WordfenceOptions');
		}
		
		return $response;
	}
	public static function ajax_restoreDefaults_callback() {
		if (!empty($_POST['section'])) {
			if (wfConfig::restoreDefaults($_POST['section'])) {
				return array(
					'success' => true,
				);
			}
			else {
				return array(
					'error' => __('An unknown configuration section was provided.', 'wordfence'),
				);
			}
		}
		
		return array(
			'error' => __('No configuration section was provided.', 'wordfence'),
		);
	}
	public static function ajax_saveOptions_callback() {
		if (!empty($_POST['changes']) && ($changes = json_decode(stripslashes($_POST['changes']), true)) !== false) {
			try {
				$errors = wfConfig::validate($changes);
				if ($errors !== true) {
					if (count($errors) == 1) {
						return array(
							'error' => sprintf(/* translators: Error message. */ __('An error occurred while saving the configuration: %s', 'wordfence'), $errors[0]['error']),
						);
					}
					else if (count($errors) > 1) {
						$compoundMessage = array();
						foreach ($errors as $e) {
							$compoundMessage[] = $e['error'];
						}
						return array(
							'error' => sprintf(/* translators: Error message. */ __('Errors occurred while saving the configuration: %s', 'wordfence'), implode(', ', $compoundMessage)),
						);
					}
					
					return array(
						'error' => __('Errors occurred while saving the configuration.', 'wordfence'),
					);
				}
				
				wfConfig::save(wfConfig::clean($changes));
				
				$response = array('success' => true);
				if (!empty($_POST['page']) && preg_match('/^Wordfence/i', $_POST['page'])) {
					if ($_POST['page'] == 'WordfenceOptions' && isset($changes['displayTopLevelOptions']) && !wfUtils::truthyToBoolean($changes['displayTopLevelOptions'])) {
						if (function_exists('network_admin_url') && is_multisite()) {
							$response['redirect'] = network_admin_url('admin.php?page=Wordfence');
						}
						else {
							$response['redirect'] = admin_url('admin.php?page=Wordfence');
						}
					}
				}
				
				return $response;
			}
			catch (wfWAFStorageFileException $e) {
				return array(
					'error' => __('An error occurred while saving the configuration.', 'wordfence'),
				);
			}
			catch (wfWAFStorageEngineMySQLiException $e) {
				return array(
					'error' => __('An error occurred while saving the configuration.', 'wordfence'),
				);
			}
			catch (Exception $e) {
				return array(
					'error' => $e->getMessage(),
				);
			}
		}
		
		return array(
			'error' => __('No configuration changes were provided to save.', 'wordfence'),
		);
	}
	
	public static function ajax_updateIPPreview_callback() {
		$howGet = $_POST['howGetIPs'];
		
		$validIPs = array();
		$invalidIPs = array();
		$testIPs = preg_split('/[\r\n,]+/', $_POST['howGetIPs_trusted_proxies']);
		foreach ($testIPs as $val) {
			if (strlen($val) > 0) {
				if (wfUtils::isValidIP($val) || wfUtils::isValidCIDRRange($val)) {
					$validIPs[] = $val;
				}
				else {
					$invalidIPs[] = $val;
				}
			}
		}
		$trustedProxies = $validIPs;
		
		$ipAll = wfUtils::getIPPreview($howGet, $trustedProxies);
		$ip = wfUtils::getIPForField($howGet, $trustedProxies);
		return array('ok' => 1, 'ip' => $ip, 'ipAll' => $ipAll);
	}

	public static function ajax_hideFileHtaccess_callback(){
		$issues = new wfIssues();
		$issue  = $issues->getIssueByID((int) $_POST['issueID']);
		if (!$issue) {
			return array('errorMsg' => __("We could not find that issue in our database.", 'wordfence'));
		}
		
		if (!function_exists('get_home_path')) {
			include_once(ABSPATH . 'wp-admin/includes/file.php');
		}
		
		$homeURL = get_home_url();
		$components = parse_url($homeURL);
		if ($components === false) {
			return array('errorMsg' => __("An error occurred while trying to hide the file.", 'wordfence'));
		}
		
		$sitePath = '';
		if (isset($components['path'])) {
			$sitePath = trim($components['path'], '/');
		}
		
		$homePath = get_home_path();
		$file = $issue['data']['file'];
		$localFile = ABSPATH . '/' . $file; //The scanner uses ABSPATH as its base rather than get_home_path()
		$localFile = realpath($localFile);
		if (strpos($localFile, $homePath) !== 0) {
			return array('errorMsg' => __("An invalid file was requested for hiding.", 'wordfence'));
		}
		$localFile = substr($localFile, strlen($homePath));
		$absoluteURIPath = trim($sitePath . '/' . $localFile, '/');
		$regexLocalFile = preg_replace('#/#', '/+', preg_quote($absoluteURIPath));
		$filename = basename($localFile);
		
		$htaccessContent = <<<HTACCESS
<IfModule mod_rewrite.c>
        RewriteEngine On
        RewriteCond %{REQUEST_URI} ^/?{$regexLocalFile}$
        RewriteRule .* - [F,L,NC]
</IfModule>
<IfModule !mod_rewrite.c>
	<Files "{$filename}">
	<IfModule mod_authz_core.c>
		Require all denied
	</IfModule>
	<IfModule !mod_authz_core.c>
		Order deny,allow
		Deny from all
	</IfModule>
	</Files>
</IfModule>
HTACCESS;

		if (!wfUtils::htaccessPrepend($htaccessContent)) {
			return array('errorMsg' => __("You don't have permission to repair .htaccess. You need to either fix the file manually using FTP or change the file permissions and ownership so that your web server has write access to repair the file.", 'wordfence'));
		}
		$issues->updateIssue((int) $_POST['issueID'], 'delete');
		wfScanEngine::refreshScanNotification($issues);
		$counts = $issues->getIssueCounts();
		return array(
			'ok' => 1,
			'issueCounts' => $counts,
		);
	}
	public static function ajax_unlockOutIP_callback(){
		$IP = $_POST['IP'];
		wfBlock::unlockOutIP($IP);
		self::clearLockoutCounters($IP);
		return array('ok' => 1);
	}
	public static function ajax_unblockIP_callback(){
		$IP = $_POST['IP'];
		wfBlock::unblockIP($IP);
		self::clearLockoutCounters($IP);
		return array('ok' => 1);
	}
	public static function ajax_permBlockIP_callback(){
		$IP = $_POST['IP'];
		wfBlock::createIP(__('Manual permanent block by admin', 'wordfence'), $IP, wfBlock::DURATION_FOREVER, time(), false, 0, wfBlock::TYPE_IP_MANUAL);
		return array('ok' => 1);
	}
	public static function ajax_unblockRange_callback(){
		$id = trim($_POST['id']);
		wfBlock::removeBlockIDs(array($id));
		return array('ok' => 1);
	}
	
	public static function ajax_whois_callback(){
		$val = trim($_POST['val']);
		$val = preg_replace('/[^a-zA-Z0-9\.\-:]+/', '', $val);
		$api = new wfAPI(wfConfig::get('apiKey'), wfUtils::getWPVersion());
		$result = $api->call('whois', array(), array(
			'val' => $val,
			));
		return array('ok' => 1, 'result' => $result['result']);
	}
	public static function ajax_recentTraffic_callback(){
		$ip = trim($_POST['ip']);
		try {
			$response = self::IPTraf($ip);
			$reverseLookup = $response['reverseLookup'];
			$results = $response['results'];
			ob_start();
			require(dirname(__FILE__) . '/IPTrafList.php');
			$content = ob_get_clean();
			return array('ok' => 1, 'result' => $content);
		} catch (InvalidArgumentException $e) {
			return array('errorMsg' => $e->getMessage());
		}
	}
	public static function ajax_blockIP_callback() {
		$IP = trim($_POST['IP']);
		$perm = (isset($_POST['perm']) && $_POST['perm'] == '1') ? wfBlock::DURATION_FOREVER : wfConfig::getInt('blockedTime');
		if (!wfUtils::isValidIP($IP)) {
			return array('err' => 1, 'errorMsg' => __("Please enter a valid IP address to block.", 'wordfence'));
		}
		if ($IP == wfUtils::getIP()) {
			return array('err' => 1, 'errorMsg' => __("You can't block your own IP address.", 'wordfence'));
		}
		$forcedWhitelistEntry = false;
		if (wfBlock::isWhitelisted($IP, $forcedWhitelistEntry)) {
			$message = sprintf(/* translators: IP address. */ __("The IP address %s is allowlisted and can't be blocked. You can remove this IP from the allowlist on the Wordfence options page.", 'wordfence'), wp_kses($IP, array()));
			if ($forcedWhitelistEntry) {
				$message = sprintf(/* translators: IP address. */ __("The IP address %s is in a range of IP addresses that Wordfence does not block. The IP range may be internal or belong to a service safe to allow access for.", 'wordfence'), wp_kses($IP, array()));
			}
			return array('err' => 1, 'errorMsg' => $message);
		}
		if (wfConfig::get('neverBlockBG') != 'treatAsOtherCrawlers') { //Either neverBlockVerified or neverBlockUA is selected which means the user doesn't want to block google
			if (wfCrawl::isVerifiedGoogleCrawler($IP)) {
				return array('err' => 1, 'errorMsg' => __("The IP address you're trying to block belongs to Google. Your options are currently set to not block these crawlers. Change this in Wordfence options if you want to manually block Google.", 'wordfence'));
			}
		}
		wfBlock::createIP($_POST['reason'], $IP, $perm);
		wfActivityReport::logBlockedIP($IP, null, 'manual');
		return array('ok' => 1);
	}
	public static function ajax_avatarLookup_callback() {
		$ids = explode(',', $_POST['ids']);
		$res = array();
		foreach ($ids as $id) {
			$avatar = get_avatar($id, 16);
			if ($avatar) {
				$res[$id] = $avatar;
			}
		}
		return array('ok' => 1, 'avatars' => $res);
	}
	public static function ajax_reverseLookup_callback(){
		$ips = explode(',', $_POST['ips']);
		$res = array();
		foreach($ips as $ip){
			$res[$ip] = wfUtils::reverseLookup($ip);
		}
		return array('ok' => 1, 'ips' => $res);
	}
	public static function ajax_deleteIssue_callback(){
		$wfIssues = new wfIssues();
		$issueID = $_POST['id'];
		$wfIssues->deleteIssue($issueID);
		wfScanEngine::refreshScanNotification($wfIssues);
		return array('ok' => 1);
	}
	public static function ajax_updateAllIssues_callback(){
		$op = $_POST['op'];
		$i = new wfIssues();
		if($op == 'deleteIgnored'){
			$i->deleteIgnored();
		} else if($op == 'deleteNew'){
			$i->deleteNew();
		} else if($op == 'ignoreAllNew'){
			$i->ignoreAllNew();
		} else {
			return array('errorMsg' => __("An invalid operation was called.", 'wordfence'));
		}
		wfScanEngine::refreshScanNotification($i);
		return array('ok' => 1);
	}
	public static function ajax_updateIssueStatus_callback(){
		$wfIssues = new wfIssues();
		$status = $_POST['status'];
		$issueID = $_POST['id'];
		if(! preg_match('/^(?:new|delete|ignoreP|ignoreC)$/', $status)){
			return array('errorMsg' => __("An invalid status was specified when trying to update that issue.", 'wordfence'));
		}
		$wfIssues->updateIssue($issueID, $status);
		wfScanEngine::refreshScanNotification($wfIssues);
		
		$counts = $wfIssues->getIssueCounts();
		return array(
			'ok' => 1,
			'issueCounts' => $counts,
			);
	}
	public static function ajax_killScan_callback(){
		wordfence::status(1, 'info', __("Scan stop request received.", 'wordfence'));
		wordfence::status(10, 'info', 'SUM_KILLED:' . __("A request was received to stop the previous scan.", 'wordfence'));
		wfUtils::clearScanLock(); //Clear the lock now because there may not be a scan running to pick up the kill request and clear the lock
		wfScanEngine::requestKill();
		wfConfig::remove('scanStartAttempt');
		wfConfig::set('lastScanFailureType', false);
		return array(
			'ok' => 1,
			);
	}
	public static function ajax_loadIssues_callback(){
		$offset = isset($_POST['offset']) ? intval($_POST['offset']) : 0;
		$limit = isset($_POST['limit']) ? intval($_POST['limit']) : WORDFENCE_SCAN_ISSUES_PER_PAGE;
		$ignoredOffset = isset($_POST['ignoredOffset']) ? intval($_POST['ignoredOffset']) : 0;
		$ignoredLimit = isset($_POST['ignoredLimit']) ? intval($_POST['ignoredLimit']) : WORDFENCE_SCAN_ISSUES_PER_PAGE;
		
		$issues = wfIssues::shared()->getIssues($offset, $limit, $ignoredOffset, $ignoredLimit);
		$issueCounts = array_merge(array('new' => 0, 'ignoreP' => 0, 'ignoreC' => 0), wfIssues::shared()->getIssueCounts());
		
		return array(
			'issues' => $issues,
			'issueCounts' => $issueCounts,
			);
	}
	public static function ajax_ticker_callback() {
		$wfdb = new wfDB();
		$table_wfStatus = wfDB::networkTable('wfStatus');
		$serverTime = $wfdb->querySingle("select unix_timestamp()");
		$jsonData = array(
			'serverTime' => $serverTime,
			'serverMicrotime' => microtime(true),
			'msg' => wp_kses_data((string) $wfdb->querySingle("SELECT msg FROM {$table_wfStatus} WHERE level < 3 AND ctime > (UNIX_TIMESTAMP() - 3600) ORDER BY ctime DESC LIMIT 1")),
			);
		$events = array();
		if (get_site_option('wordfence_syncAttackDataAttempts') > 10) {
			self::syncAttackData(false);
		}
		$results = self::ajax_loadLiveTraffic_callback();
		$events = $results['data'];
		if (isset($results['sql'])) {
			$jsonData['sql'] = $results['sql'];
		}
		
		$jsonData['events'] = $events;
		return $jsonData;
	}
	public static function ajax_activityLogUpdate_callback() {
		global $wpdb;
		$statusTable = wfDB::networkTable('wfStatus');
		$row = $wpdb->get_row("SELECT ctime, msg FROM {$statusTable} WHERE level < 3 AND ctime > (UNIX_TIMESTAMP() - 3600) ORDER BY ctime DESC LIMIT 1", ARRAY_A);
		$lastMessage = __('Idle', 'wordfence');
		
		$lastScanCompleted = wfConfig::get('lastScanCompleted');
		if ($row) {
			$lastMessage = '[' . strtoupper(wfUtils::formatLocalTime('M d H:i:s', $row['ctime'])) . '] ' . wp_kses_data($row['msg']);
		}
		else if ($lastScanCompleted == 'ok') {
			$scanLastCompletion = (int) wfScanner::shared()->lastScanTime();
			if ($scanLastCompletion) {
				$lastMessage = sprintf(/* translators: Localized date. */ __('Scan completed on %s', 'wordfence'), wfUtils::formatLocalTime(get_option('date_format') . ' ' . get_option('time_format'), $scanLastCompletion));
			}
		}
		else if ($lastScanCompleted === false || empty($lastScanCompleted)) {
			//Do nothing
		}
		else {
			$lastMessage = __('Last scan failed', 'wordfence');
		}
		
		$issues = wfIssues::shared();
		$scanFailed = $issues->hasScanFailed();
		
		$scanner = wfScanner::shared();
		$stages = $scanner->stageStatus();
		foreach ($stages as $key => &$value) {
			switch ($value) {
				case wfScanner::STATUS_PENDING:
					$value = 'wf-scan-step';
					break;
				case wfScanner::STATUS_RUNNING:
				case wfScanner::STATUS_RUNNING_WARNING:
					if ($scanFailed) {
						$value = 'wf-scan-step';
						break;
					}
					$value = 'wf-scan-step wf-scan-step-running';
					break;
				case wfScanner::STATUS_COMPLETE_SUCCESS:
					$value = 'wf-scan-step wf-scan-step-complete-success';
					break;
				case wfScanner::STATUS_COMPLETE_WARNING:
					$value = 'wf-scan-step wf-scan-step-complete-warning';
					break;
				case wfScanner::STATUS_PREMIUM:
					$value = 'wf-scan-step wf-scan-step-premium';
					break;
				case wfScanner::STATUS_DISABLED:
					$value = 'wf-scan-step wf-scan-step-disabled';
					break;
			}
		}
		
		$stats = array(
			'wf-scan-results-stats-postscommentsfiles' => $scanner->getSummaryItem(wfScanner::SUMMARY_SCANNED_POSTS, 0) + $scanner->getSummaryItem(wfScanner::SUMMARY_SCANNED_COMMENTS, 0) + $scanner->getSummaryItem(wfScanner::SUMMARY_SCANNED_FILES, 0),
			'wf-scan-results-stats-themesplugins' => $scanner->getSummaryItem(wfScanner::SUMMARY_SCANNED_PLUGINS, 0) + $scanner->getSummaryItem(wfScanner::SUMMARY_SCANNED_THEMES, 0),
			'wf-scan-results-stats-users' => $scanner->getSummaryItem(wfScanner::SUMMARY_SCANNED_USERS, 0),
			'wf-scan-results-stats-urls' => $scanner->getSummaryItem(wfScanner::SUMMARY_SCANNED_URLS, 0),
			'wf-scan-results-stats-issues' => $issues->getIssueCount(),
		);
		
		$lastIssueUpdateTimestamp = wfIssues::shared()->getLastIssueUpdateTimestamp();
		$issues = 0;
		$issueCounts = array_merge(array('new' => 0, 'ignoreP' => 0, 'ignoreC' => 0), wfIssues::shared()->getIssueCounts());
		if ($lastIssueUpdateTimestamp > $_POST['lastissuetime']) {
			$issues = wfIssues::shared()->getIssues(0, WORDFENCE_SCAN_ISSUES_PER_PAGE, 0, WORDFENCE_SCAN_ISSUES_PER_PAGE);
		}
		
		$timeLimit = intval(wfConfig::get('scan_maxDuration'));
		if ($timeLimit < 1) {
			$timeLimit = WORDFENCE_DEFAULT_MAX_SCAN_TIME;
		}
		
		$scanFailedHTML = '';
		switch ($scanFailed) {
			case wfIssues::SCAN_FAILED_TIMEOUT:
				$scanFailedSeconds = time() - wfIssues::lastScanStatusUpdate();
				$scanFailedTiming = wfUtils::makeTimeAgo($scanFailedSeconds);
				
				if ($scanFailedSeconds > $timeLimit) {
					$scanFailedTiming = sprintf(/* translators: Time until. */ __('more than %s', 'wordfence'), wfUtils::makeTimeAgo($timeLimit));
				}
				
				$scanFailedHTML = wfView::create('scanner/scan-failed', array(
					'messageHTML' => sprintf(/* translators: Localized date. */ __('The current scan looks like it has failed. Its last status update was <span id="wf-scan-failed-time-ago">%s</span> ago. You may continue to wait in case it resumes or stop and restart the scan. Some sites may need adjustments to run scans reliably.', 'wordfence'), $scanFailedTiming) . ' <a href="' . wfSupportController::esc_supportURL(wfSupportController::ITEM_SCAN_FAILS) . '" target="_blank" rel="noopener noreferrer">' . __('Click here for steps you can try.', 'wordfence') . '</a>',
					'buttonTitle' => __('Cancel Scan', 'wordfence'), 
				))->render();
				
				break;
			case wfIssues::SCAN_FAILED_FORK_FAILED:
			case wfIssues::SCAN_FAILED_GENERAL:
				$scanFailedHTML = wfView::create('scanner/scan-failed', array(
					'messageHTML' => __('The previous scan has failed. Some sites may need adjustments to run scans reliably.', 'wordfence') . ' <a href="' . wfSupportController::esc_supportURL(wfSupportController::ITEM_SCAN_FAILS) . '" target="_blank" rel="noopener noreferrer">' . __('Click here for steps you can try.', 'wordfence') . '</a>',
					'buttonTitle' => __('Close', 'wordfence'),
				))->render();
				break;
			case wfIssues::SCAN_FAILED_DURATION_REACHED:
				$scanFailedHTML = wfView::create('scanner/scan-failed', array(
					'messageHTML' => sprintf(/* translators: Time limit (number). */ __('The previous scan has terminated because the time limit of %s was reached. This limit can be customized on the options page.', 'wordfence'), wfUtils::makeDuration($timeLimit)) . ' <a href="' . wfSupportController::esc_supportURL(wfSupportController::ITEM_SCAN_OPTION_OVERALL_TIME_LIMIT) . '" target="_blank" rel="noopener noreferrer" class="wf-inline-help"><i class="wf-fa wf-fa-question-circle-o" aria-hidden="true"></i></a>',
					'buttonTitle' => __('Close', 'wordfence'),
				))->render();
				break;
			case wfIssues::SCAN_FAILED_VERSION_CHANGE:
				$scanFailedHTML = wfView::create('scanner/scan-failed', array(
					'messageHTML' => esc_html__('The previous scan has terminated because we detected an update occurring during the scan.', 'wordfence'),
					'buttonTitle' => __('Close', 'wordfence'),
				))->render();
				break;
			case wfIssues::SCAN_FAILED_START_TIMEOUT:
			case wfIssues::SCAN_FAILED_CALLBACK_TEST_FAILED:
				$scanFailedHTML = wfView::create('scanner/scan-failed', array(
					'messageHTML' => __('The scan has failed to start. This is often because the site either cannot make outbound requests or is blocked from connecting to itself.', 'wordfence') . ' <a href="' . wfSupportController::esc_supportURL(wfSupportController::ITEM_SCAN_FAILED_START) . '" target="_blank" rel="noopener noreferrer">' . __('Click here for steps you can try.', 'wordfence') . '</a>',
					'buttonTitle' => __('Close', 'wordfence'),
				))->render();
				break;
			case wfIssues::SCAN_FAILED_API_SSL_UNAVAILABLE:
				$scanFailedHTML = wfView::create('scanner/scan-failed', array(
					'messageHTML' => esc_html__('Scans are not functional because SSL is unavailable.', 'wordfence'),
					'buttonTitle' => __('Close', 'wordfence'),
				))->render();
				break;
			case wfIssues::SCAN_FAILED_API_CALL_FAILED:
				$scanFailedHTML = wfView::create('scanner/scan-failed', array(
					'messageHTML' => __('The scan has failed because we were unable to contact the Wordfence servers. Some sites may need adjustments to run scans reliably.', 'wordfence') . ' <a href="' . wfSupportController::esc_supportURL(wfSupportController::ITEM_SCAN_FAILS) . '" target="_blank" rel="noopener noreferrer">' . __('Click here for steps you can try.', 'wordfence') . '</a>',
					'rawErrorHTML' => esc_html(wfConfig::get('lastScanCompleted', '')),
					'buttonTitle' => __('Close', 'wordfence'),
				))->render();
				break;
			case wfIssues::SCAN_FAILED_API_INVALID_RESPONSE:
			case wfIssues::SCAN_FAILED_API_ERROR_RESPONSE:
				$scanFailedHTML = wfView::create('scanner/scan-failed', array(
					'messageHTML' => __('The scan has failed because we received an unexpected response from the Wordfence servers. This may be a temporary error, though some sites may need adjustments to run scans reliably.', 'wordfence') . ' <a href="' . wfSupportController::esc_supportURL(wfSupportController::ITEM_SCAN_FAILS) . '" target="_blank" rel="noopener noreferrer">' . __('Click here for steps you can try.', 'wordfence') . '</a>',
					'rawErrorHTML' => esc_html(wfConfig::get('lastScanCompleted'), ''),
					'buttonTitle' => __('Close', 'wordfence'),
				))->render();
				break;
		}
		
		wfUtils::doNotCache();
		return array(
			'ok'                  => 1,
			'lastMessage'		  => $lastMessage,
			'items'               => self::getLog()->getStatusEvents($_POST['lastctime']),
			'currentScanID'       => wfScanner::shared()->lastScanTime(),
			'signatureUpdateTime' => wfConfig::get('signatureUpdateTime'),
			'scanFailedHTML' 	  => $scanFailedHTML,
			'scanStalled'		  => ($scanFailed == wfIssues::SCAN_FAILED_TIMEOUT || $scanFailed == wfIssues::SCAN_FAILED_START_TIMEOUT ? 1 : 0),
			'scanRunning'		  => wfScanner::shared()->isRunning() ? 1 : 0,
			'scanStages'		  => $stages,
			'scanStats'			  => $stats,
			'issues'			  => $issues,
			'issueCounts'		  => $issueCounts,
			'issueUpdateTimestamp'=> $lastIssueUpdateTimestamp,
		);
	}
	public static function ajax_updateAlertEmail_callback(){
		$email = trim($_POST['email']);
		if(! preg_match('/[^\@]+\@[^\.]+\.[^\.]+/', $email) || in_array(hash('sha256', $email), wfConfig::alertEmailBlacklist())){
			return array( 'err' => __("Invalid email address given.", 'wordfence'));
		}
		wfConfig::set('alertEmails', $email);
		return array('ok' => 1, 'email' => $email);
	}
	public static function ajax_bulkOperation_callback() {
		$op = sanitize_text_field($_POST['op']);
		if ($op == 'del' || $op == 'repair') {
			$idsRemoved = array();
			$filesWorkedOn = 0;
			$errors = array();
			$wfIssues = new wfIssues();
			$issueCount = $wfIssues->getIssueCount();
			for ($offset = floor($issueCount / 100) * 100; $offset >= 0; $offset -= 100) {
				$issues = $wfIssues->getIssues($offset, 100, 0, 0);
				foreach ($issues['new'] as $i) {
					if ($op == 'del' && @$i['data']['canDelete']) {
						$file = $i['data']['file'];
						$localFile = ABSPATH . '/' . $file;
						$localFile = realpath($localFile);
						if (strpos($localFile, ABSPATH) !== 0) {
							continue;
						}
						
						if ($localFile === ABSPATH . 'wp-config.php') {
							$errors[] = esc_html__('Deleting an infected wp-config.php file must be done outside of Wordfence. The wp-config.php file contains your database credentials, which you will need to restore normal site operations. Your site will NOT function once the wp-config.php file has been deleted.', 'wordfence');
						}
						else if (@unlink($localFile)) {
							$wfIssues->updateIssue($i['id'], 'delete');
							$idsRemoved[] = $i['id'];
							$filesWorkedOn++;
						}
						else {
							$err = error_get_last();
							$errors[] = esc_html(sprintf(/* translators: 1. File path. 2. Error message. */ __('Could not delete file %1$s. Error was: %2$s', 'wordfence'), wp_kses($file, array()), wp_kses(str_replace(ABSPATH, '{WordPress Root}/', $err['message']), array())));
						}
					}
					else if ($op == 'repair' && @$i['data']['canFix']) {
						$file = $i['data']['file'];
						$localFile = ABSPATH . '/' . $file;
						$localFile = realpath($localFile);
						if (strpos($localFile, ABSPATH) !== 0) {
							continue;
						}
						
						$result = array();
						if (isset($i['data']) && is_array($i['data']) && isset($i['data']['file']) && isset($i['data']['cType']) && ( //Basics
								$i['data']['cType'] == 'core' || //Core file
								($i['data']['cType'] == 'plugin' || $i['data']['cType'] == 'theme') && isset($i['data']['cName']) && isset($i['data']['cVersion']) //Plugin or Theme file
							)) {
							$result = self::getWPFileContent($i['data']['file'], $i['data']['cType'], isset($i['data']['cName']) ? $i['data']['cName'] : null, isset($i['data']['cVersion']) ? $i['data']['cVersion'] : null);
						}
						
						if (is_array($result) && isset($result['errorMsg'])) {
							$errors[] = esc_html($result['errorMsg']);
							continue;
						}
						else if (!is_array($result) || !isset($result['fileContent'])) {
							$errors[] = esc_html(sprintf(/* translators: File path. */ __('We could not retrieve the original file of %s to do a repair.', 'wordfence'), wp_kses($file, array())));
							continue;
						}
						
						if (preg_match('/\.\./', $file)) {
							$errors[] = sprintf(/* translators: File path. */ __('An invalid file %s was specified for repair.', 'wordfence'), wp_kses($file, array()));
							continue;
						}
						
						$fh = fopen($localFile, 'w');
						if (!$fh) {
							$err = error_get_last();
							if (preg_match('/Permission denied/i', $err['message'])) {
								$errMsg = esc_html(sprintf(/* translators: File path. */ __('You don\'t have permission to repair %s. You need to either fix the file manually using FTP or change the file permissions and ownership so that your web server has write access to repair the file.', 'wordfence'), wp_kses($file, array())));
							}
							else {
								$errMsg = esc_html(sprintf(/* translators: 1. File path. 2. Error message. */ __('We could not write to %1$s. The error was: %2$s', 'wordfence'), wp_kses($file, array()),  $err['message']));
							}
							$errors[] = $errMsg;
							continue;
						}
						
						flock($fh, LOCK_EX);
						$bytes = fwrite($fh, $result['fileContent']);
						flock($fh, LOCK_UN);
						fclose($fh);
						if ($bytes < 1) {
							$errors[] = esc_html(sprintf(/* translators: 1. File path. 2. Number of bytes. */ __('We could not write to %1$s. (%2$d bytes written) You may not have permission to modify files on your WordPress server.', 'wordfence'), wp_kses($file, array()), $bytes));
							continue;
						}
						
						$filesWorkedOn++;
						$wfIssues->updateIssue($i['id'], 'delete');
						$idsRemoved[] = $i['id'];
					}
				}
			}
			
			if ($filesWorkedOn > 0 && count($errors) > 0) {
				$headMsg = esc_html($op == 'del' ? __('Deleted some files with errors', 'wordfence') : __('Repaired some files with errors', 'wordfence'));
				$bodyMsg = sprintf(esc_html($op == 'del' ?
					/* translators: 1. Number of files. 2. Error message. */
					__('Deleted %1$d files but we encountered the following errors with other files: %2$s', 'wordfence') :
					/* translators: 1. Number of files. 2. Error message. */
					__('Repaired %1$d files but we encountered the following errors with other files: %2$s', 'wordfence')),
					$filesWorkedOn, implode('<br>', $errors));
			}
			else if ($filesWorkedOn > 0) {
				$headMsg = sprintf(esc_html($op == 'del' ? /* translators: Number of files. */ __('Deleted %d files successfully', 'wordfence') : /* translators: Number of files. */ __('Repaired %d files successfully', 'wordfence')), $filesWorkedOn);
				$bodyMsg = sprintf(esc_html($op == 'del' ? /* translators: Number of files. */ __('Deleted %d files successfully. No errors were encountered.', 'wordfence') : /* translators: Number of files. */ __('Repaired %d files successfully. No errors were encountered.', 'wordfence')), $filesWorkedOn);
			}
			else if (count($errors) > 0) {
				$headMsg = esc_html($op == 'del' ? __('Could not delete files', 'wordfence') : __('Could not repair files', 'wordfence'));
				$bodyMsg = sprintf(esc_html($op == 'del' ?
					/* translators: Error message. */
					__('We could not delete any of the files you selected. We encountered the following errors: %s', 'wordfence') :
					/* translators: Error message. */
					__('We could not repair any of the files you selected. We encountered the following errors: %s', 'wordfence')),  implode('<br>', $errors));
			}
			else {
				$headMsg = esc_html__('Nothing done', 'wordfence');
				$bodyMsg = esc_html($op == 'del' ? __('We didn\'t delete anything and no errors were found.', 'wordfence') : __('We didn\'t repair anything and no errors were found.', 'wordfence'));
			}
			
			wfScanEngine::refreshScanNotification($wfIssues);
			$counts = $wfIssues->getIssueCounts();
			return array('ok' => 1, 'bulkHeading' => $headMsg, 'bulkBody' => $bodyMsg, 'idsRemoved' => $idsRemoved, 'issueCounts' => $counts);
		}
		else {
			return array('errorMsg' => esc_html__('Invalid bulk operation selected', 'wordfence'));
		}
	}
	public static function ajax_deleteFile_callback($issueID = null){
		if ($issueID === null) {
			$issueID = intval($_POST['issueID']);
		}
		$wfIssues = new wfIssues();
		$issue = $wfIssues->getIssueByID($issueID);
		if(! $issue){
			return array('errorMsg' => __('Could not delete file because we could not find that issue.', 'wordfence'));
		}
		if(! $issue['data']['file']){
			return array('errorMsg' => __('Could not delete file because that issue does not appear to be a file related issue.', 'wordfence'));
		}
		$file = $issue['data']['file'];
		$localFile = ABSPATH . '/' . $file;
		$localFile = realpath($localFile);
		if(strpos($localFile, ABSPATH) !== 0){
			return array('errorMsg' => __('An invalid file was requested for deletion.', 'wordfence'));
		}
		if ($localFile === ABSPATH . 'wp-config.php') {
			return array(
				'errorMsg' => __('Deleting an infected wp-config.php file must be done outside of Wordfence. The wp-config.php file contains your database credentials, which you will need to restore normal site operations. Your site will NOT function once the wp-config.php file has been deleted.', 'wordfence')
			);
		}

		/** @var WP_Filesystem_Base $wp_filesystem */
		global $wp_filesystem;

		$adminURL = network_admin_url('admin.php?' . http_build_query(array(
				'page'               => 'WordfenceScan',
				'subpage'       	 => 'scan_credentials',
				'action'			 => 'deleteFile',
				'issueID'            => $issueID,
				'nonce'              => wp_create_nonce('wp-ajax'),
			)));

		if (!self::requestFilesystemCredentials($adminURL, null, true, false)) {
			return array(
				'ok'               => 1,
				'needsCredentials' => 1,
				'redirect'         => $adminURL,
			);
		}

		if ($wp_filesystem->delete($localFile)) {
			$wfIssues->updateIssue($issueID, 'delete');
			$counts = $wfIssues->getIssueCounts();
			wfScanEngine::refreshScanNotification($wfIssues);
			return array(
				'ok' => 1,
				'localFile' => $localFile,
				'file' => $file,
				'issueCounts' => $counts,
			);
		}
		
		$err = error_get_last();
		return array(
			'errorMsg' => sprintf(
				/* translators: 1. File path. 2. Error message. */
				__('Could not delete file %1$s. The error was: %2$s', 'wordfence'),
				wp_kses($file, array()),
				wp_kses(str_replace(ABSPATH, '{WordPress Root}/', $err['message']), array())
			)
		);
	}
	public static function ajax_deleteDatabaseOption_callback(){
		/** @var wpdb $wpdb */
		global $wpdb;
		$issueID = intval($_POST['issueID']);
		$wfIssues = new wfIssues();
		$issue = $wfIssues->getIssueByID($issueID);
		if (!$issue) {
			return array('errorMsg' => __("Could not remove the option because we could not find that issue.", 'wordfence'));
		}
		if (empty($issue['data']['option_name'])) {
			return array('errorMsg' => __("Could not remove the option because that issue does not appear to be a database related issue.", 'wordfence'));
		}
		$table_options = wfDB::blogTable('options', $issue['data']['site_id']);
		if ($wpdb->query($wpdb->prepare("DELETE FROM {$table_options} WHERE option_name = %s", $issue['data']['option_name']))) {
			$wfIssues->updateIssue($issueID, 'delete');
			wfScanEngine::refreshScanNotification($wfIssues);
			return array(
				'ok'          => 1,
				'option_name' => $issue['data']['option_name'],
			);
		} else {
			return array('errorMsg' => sprintf(
				/* translators: 1. WordPress option. 2. Error message. */
				__('Could not remove the option %1$s. The error was: %2$s', 'wordfence'),
				esc_html($issue['data']['option_name']),
				esc_html($wpdb->last_error)
			));
		}
	}
	public static function ajax_fixFPD_callback(){
		$issues = new wfIssues();
		$issue  = $issues->getIssueByID($_POST['issueID']);
		if (!$issue) {
			return array('cerrorMsg' => __("We could not find that issue in our database.", 'wordfence'));
		}

		$htaccess = ABSPATH . '/.htaccess';
		$change   = "<IfModule mod_php5.c>\n\tphp_value display_errors 0\n</IfModule>\n<IfModule mod_php7.c>\n\tphp_value display_errors 0\n</IfModule>\n<IfModule mod_php.c>\n\tphp_value display_errors 0\n</IfModule>";
		$content  = "";
		if (file_exists($htaccess)) {
			$content = file_get_contents($htaccess);
		}

		if (@file_put_contents($htaccess, trim($content . "\n" . $change), LOCK_EX) === false) {
			return array('cerrorMsg' => __("You don't have permission to repair .htaccess. You need to either fix the file manually using FTP or change the file permissions and ownership so that your web server has write access to repair the file.", 'wordfence'));
		}
		if (wfScanEngine::testForFullPathDisclosure()) {
			// Didn't fix it, so revert the changes and return an error
			file_put_contents($htaccess, $content, LOCK_EX);
			return array(
				'cerrorMsg' => __("Modifying the .htaccess file did not resolve the issue, so the original .htaccess file was restored. You can fix this manually by setting <code>display_errors</code> to <code>Off</code> in your php.ini if your site is on a VPS or dedicated server that you control.", 'wordfence'),
			);
		}
		$issues->updateIssue($_POST['issueID'], 'delete');
		wfScanEngine::refreshScanNotification($issues);
		return array('ok' => 1);
	}
	public static function ajax_restoreFile_callback($issueID = null){
		if ($issueID === null) {
			$issueID = intval($_POST['issueID']);
		}
		$wfIssues = new wfIssues();
		$issue = $wfIssues->getIssueByID($issueID);
		if(! $issue){
			return array('cerrorMsg' => __("We could not find that issue in our database.", 'wordfence'));
		}

		/** @var WP_Filesystem_Base $wp_filesystem */
		global $wp_filesystem;
		
		$adminURL = network_admin_url('admin.php?' . http_build_query(array(
				'page'               => 'WordfenceScan',
				'subpage'       	 => 'scan_credentials',
				'action'			 => 'restoreFile',
				'issueID'            => $issueID,
				'nonce'              => wp_create_nonce('wp-ajax'),
			)));

		if (!self::requestFilesystemCredentials($adminURL, null, true, false)) {
			return array(
				'ok'               => 1,
				'needsCredentials' => true,
				'redirect'         => $adminURL,
			);
		}

		$dat = $issue['data'];
		$result = self::getWPFileContent($dat['file'], $dat['cType'], (isset($dat['cName']) ? $dat['cName'] : ''), (isset($dat['cVersion']) ? $dat['cVersion'] : ''));
		$file = $dat['file'];
		if(isset($result['errorMsg']) && $result['errorMsg']){
			return $result;
		} else if(! $result['fileContent']){
			return array('errorMsg' => __("We could not get the original file to do a repair.", 'wordfence'));
		}

		if(preg_match('/\.\./', $file)){
			return array('errorMsg' => __("An invalid file was specified for repair.", 'wordfence'));
		}
		$localFile = rtrim(ABSPATH, '/') . '/' . preg_replace('/^[\.\/]+/', '', $file);
		if ($wp_filesystem->put_contents($localFile, $result['fileContent'])) {
			$wfIssues->updateIssue($issueID, 'delete');
			$counts = $wfIssues->getIssueCounts();
			wfScanEngine::refreshScanNotification($wfIssues);
			return array(
				'ok'   => 1,
				'localFile' => $localFile,
				'file' => $file,
				'issueCounts' => $counts,
			);
		}
		return array(
			'errorMsg' => __("We could not write to that file. You may not have permission to modify files on your WordPress server.", 'wordfence'),
		);
	}
	public static function ajax_scan_callback(){
		self::status(4, 'info', __("Ajax request received to start scan.", 'wordfence'));
		$err = wfScanEngine::startScan();
		if ($err) {
			return array('errorMsg' => wp_kses($err, array()));
		}
		else {
			$issueCounts = array_merge(array('new' => 0, 'ignoreP' => 0, 'ignoreC' => 0), wfIssues::shared()->getIssueCounts());
			return array("ok" => 1, 'issueCounts' => $issueCounts);
		}
	}
	public static function ajax_exportSettings_callback() {
		$result = wfImportExportController::shared()->export();
		return $result;
	}
	public static function ajax_importSettings_callback(){
		$token = $_POST['token'];
		return self::importSettings($token);
	}
	public static function importSettings($token) { //Documented call for external interfacing.
		return wfImportExportController::shared()->import($token);
	}
	public static function ajax_dismissNotification_callback() {
		$id = $_POST['id'];
		$n = wfNotification::getNotificationForID($id);
		if ($n !== null) {
			$n->markAsRead();
		}
		return array(
			'ok' => 1,
		);
	}
	public static function ajax_utilityScanForBlacklisted_callback() {
		if (wfScanner::shared()->isRunning()) {
			return array('wait' => 2); //Can't run while a scan is running since the URL hoover is currently implemented like a singleton
		}
		
		$pageURL = stripslashes($_POST['url']);
		$source = stripslashes($_POST['source']);
		$apiKey = wfConfig::get('apiKey');
		$wp_version = wfUtils::getWPVersion();
		$h = new wordfenceURLHoover($apiKey, $wp_version);
		$h->hoover(1, $source);
		$hooverResults = $h->getBaddies();
		if ($h->errorMsg) {
			$h->cleanup();
			return array('wait' => 3, 'errorMsg' => $h->errorMsg); //Unable to contact noc1 to verify
		} 
		$h->cleanup();
		if (sizeof($hooverResults) > 0 && isset($hooverResults[1])) {
			$hresults = $hooverResults[1];
			$count = count($hresults);
			if ($count > 0) {
				new wfNotification(
					null,
					wfNotification::PRIORITY_HIGH_WARNING,
					sprintf(/* translators: Number of URLs. */ _n("Page contains %d malware URL: ", "Page contains %d malware URLs: ", $count, 'wordfence') . esc_html($pageURL)),
					'wfplugin_malwareurl_' . md5($pageURL),
					null,
					array(array('link' => wfUtils::wpAdminURL('admin.php?page=WordfenceScan'), 'label' => __('Run a Scan', 'wordfence'))));
				return array('bad' => $count);
			}
		}
		return array('ok' => 1);
	}
	public static function ajax_dashboardShowMore_callback() {
		$grouping = $_POST['grouping'];
		$period = $_POST['period'];
		
		$dashboard = new wfDashboard();
		if ($grouping == 'ips') {
			$data = null;
			if ($period == '24h') { $data = $dashboard->ips24h; }
			else if ($period == '7d') { $data = $dashboard->ips7d; }
			else if ($period == '30d') { $data = $dashboard->ips30d; }
			
			if ($data !== null) {
				foreach ($data as &$d) {
					$d['IP'] = esc_html(wfUtils::inet_ntop($d['IP']));
					$d['blockCount'] = esc_html(number_format_i18n($d['blockCount']));
					$d['countryFlag'] = esc_attr('wf-flag-' . strtolower($d['countryCode']));
					$d['countryName'] = esc_html($d['countryName']);
				}
				return array('ok' => 1, 'data' => $data);
			}
		}
		else if ($grouping == 'logins') {
			$data = null;
			if ($period == 'success') { $data = $dashboard->loginsSuccess; }
			else if ($period == 'fail') { $data = $dashboard->loginsFail; }
			
			if ($data !== null) {
				$data = array_slice($data, 0, 100);
				foreach ($data as &$d) {
					$d['ip'] = esc_html($d['ip']);
					$d['name'] = esc_html($d['name']);
					if (time() - $d['t'] < 86400) {
						$d['t'] = esc_html(wfUtils::makeTimeAgo(time() - $d['t']) . ' ago');
					}
					else {
						$d['t'] = esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), (int) $d['t']));
					}
				}
				return array('ok' => 1, 'data' => $data);
			}
		}
		
		return array('error' => __('Unknown dashboard data set.', 'wordfence'));
	}
	public static function startScan(){
		wfScanEngine::startScan();
	}
	public static function templateRedir(){
		if (!empty($_GET['wordfence_lh'])) {
			self::ajax_lh_callback();
			exit;
		}
		if (!empty($_GET['wfcentral_admin_redirect'])) {
			wp_safe_redirect(remove_query_arg('wfcentral_admin_redirect', network_admin_url('admin.php?page=Wordfence' . rawurlencode(ucwords(preg_replace('/\W/', '', $_GET['wfcentral_admin_redirect']))) . '&' . $_SERVER['QUERY_STRING'])));
			exit;
		}

		$wfFunc = !empty($_GET['_wfsf']) && is_string($_GET['_wfsf']) ? $_GET['_wfsf'] : '';

		//Logging
		self::doEarlyAccessLogging();
		//End logging


		if(! ($wfFunc == 'diff' || $wfFunc == 'view' || $wfFunc == 'viewOption' || $wfFunc == 'sysinfo' || $wfFunc == 'IPTraf' || $wfFunc == 'viewActivityLog' || $wfFunc == 'testmem' || $wfFunc == 'testtime' || $wfFunc == 'download' || $wfFunc == 'blockedIPs' || ($wfFunc == 'debugWAF' && WFWAF_DEBUG))){
			return;
		}
		if(! wfUtils::isAdmin()){
			return;
		}

		$nonce = $_GET['nonce'];
		if(! wp_verify_nonce($nonce, 'wp-ajax')){
			_e("Bad security token. It may have been more than 12 hours since you reloaded the page you came from. Try reloading the page you came from. If that doesn't work, please sign out and sign-in again.", 'wordfence');
			exit(0);
		}
		if($wfFunc == 'diff'){
			self::wfFunc_diff();
		} else if($wfFunc == 'view'){
			self::wfFunc_view();
		} else if($wfFunc == 'viewOption'){
			self::wfFunc_viewOption();
		} else if($wfFunc == 'sysinfo') {
			require(dirname(__FILE__) . '/sysinfo.php' );
		} else if($wfFunc == 'IPTraf'){
			self::wfFunc_IPTraf();
		} else if($wfFunc == 'viewActivityLog'){
			self::wfFunc_viewActivityLog();
		} else if($wfFunc == 'testmem'){
			self::wfFunc_testmem();
		} else if($wfFunc == 'testtime'){
			self::wfFunc_testtime();
		} else if($wfFunc == 'download'){
			self::wfFunc_download();
		} else if($wfFunc == 'blockedIPs'){
			self::wfFunc_blockedIPs();
		} else if($wfFunc == 'debugWAF' && WFWAF_DEBUG){
			self::wfFunc_debugWAF();
		}
		exit(0);
	}
	public static function memtest_error_handler($errno, $errstr, $errfile, $errline){
		echo "Error received: $errstr\n";
	}
	private static function wfFunc_testtime(){
		header('Content-Type: text/plain');
		@error_reporting(E_ALL);
		wfUtils::iniSet('display_errors','On');
		set_error_handler('wordfence::memtest_error_handler', E_ALL);

		echo "Wordfence process duration benchmarking utility version " . WORDFENCE_VERSION . ".\n";
		echo "This utility tests how long your WordPress host allows a process to run.\n\n--Starting test--\n";
		echo "Starting timed test. This will take at least three minutes. Seconds elapsed are printed below.\nAn error after this line is not unusual. Read it and the elapsed seconds to determine max process running time on your host.\n";
		for($i = 1; $i <= 180; $i++){
			echo "\n$i:";
			for($j = 0; $j < 1000; $j++){
				echo '.';
			}
			flush();
			sleep(1);
		}
		echo "\n--Test complete.--\n\nCongratulations, your web host allows your PHP processes to run at least 3 minutes.\n";
		exit();
	}
	private static function wfFunc_testmem(){
		header('Content-Type: text/plain');
		@error_reporting(E_ALL);
		wfUtils::iniSet('display_errors','On');
		set_error_handler('wordfence::memtest_error_handler', E_ALL);
		
		$maxMemory = ini_get('memory_limit');
		$last = strtolower(substr($maxMemory, -1));
		$maxMemory = (int) $maxMemory;
		
		$configuredMax = wfConfig::get('maxMem', 0);
		if ($configuredMax <= 0) {
			if ($last == 'g') { $configuredMax = $maxMemory * 1024; }
			else if ($last == 'm') { $configuredMax = $maxMemory; }
			else if ($last == 'k') { $configuredMax = $maxMemory / 1024; }
			$configuredMax = floor($configuredMax);
		}
		
		$stepSize = 5242880; //5 MB

		echo "Wordfence Memory benchmarking utility version " . WORDFENCE_VERSION . ".\n";
		echo "This utility tests if your WordPress host respects the maximum memory configured\nin their php.ini file, or if they are using other methods to limit your access to memory.\n\n--Starting test--\n";
		echo "Current maximum memory configured in php.ini: " . ini_get('memory_limit') . "\n";
		echo "Current memory usage: " . sprintf('%.2f', memory_get_usage(true) / (1024 * 1024)) . "M\n";
		echo "Attempting to set max memory to {$configuredMax}M.\n";
		wfUtils::iniSet('memory_limit', ($configuredMax + 5) . 'M'); //Allow a little extra for testing overhead
		echo "Starting memory benchmark. Seeing an error after this line is not unusual. Read the error carefully\nto determine how much memory your host allows. We have requested {$configuredMax} megabytes.\n";
		
		if (memory_get_usage(true) < 1) {
			echo "Exiting test because memory_get_usage() returned a negative number\n";
			exit();
		}
		if (memory_get_usage(true) > (1024 * 1024 * 1024)) {
			echo "Exiting because current memory usage is greater than a gigabyte.\n";
			exit();
		}
		
		if (!defined('WP_SANDBOX_SCRAPING')) { define('WP_SANDBOX_SCRAPING', true); } //Disables the WP error handler in somewhat of a hacky way
		
		$accumulatedMemory = array_fill(0, ceil($configuredMax / $stepSize), '');
		$currentUsage = memory_get_usage(true);
		$tenMB = 10 * 1024 * 1024;
		$start = ceil($currentUsage / $tenMB) * $tenMB - $currentUsage; //Start at the closest 10 MB increment to the current usage
		$configuredMax = $configuredMax * 1048576; //Bytes
		$testLimit = $configuredMax - memory_get_usage(true);
		$finalUsage = '0';
		$previous = 0;
		$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ012345678900000000000000000000000000000000000000000000000000000000000000000000000000000000000000011111111111111111222222222222222222233333333333333334444444444444444444444444555555555555666666666666666666";
		$index = 0;
		while ($start <= $testLimit) {
			$accumulatedMemory[$index] = str_repeat($chars, ($start - $previous) / 256);
			
			$finalUsage = sprintf('%.2f', (memory_get_usage(true) / 1024 / 1024));
			echo "Tested up to " . $finalUsage . " megabytes.\n";
			if ($start == $testLimit) { break; }
			$previous = $start;
			$start = min($start + $stepSize, $testLimit);
			
			if (memory_get_usage(true) > $configuredMax) { break; }
			$index++;
		}
		echo "--Test complete.--\n\nYour web host allows you to use at least {$finalUsage} megabytes of memory for each PHP process hosting your WordPress site.\n";
		exit();
	}
	public static function wfLogHumanHeader(){
		//Final check in case this was added as an action before the request was fully initialized
		if (self::getLog()->getCurrentRequest()->jsRun || !wfConfig::liveTrafficEnabled()) {
			return;
		}
		
		self::$hitID = self::getLog()->logHit();
		if (self::$hitID) {
			$URL = home_url('/?wordfence_lh=1&hid=' . wfUtils::encrypt(self::$hitID));
			$URL = addslashes(preg_replace('/^https?:/i', '', $URL));
			#Load as external script async so we don't slow page down.
			echo <<<HTML
<script type="text/javascript">
(function(url){
	if(/(?:Chrome\/26\.0\.1410\.63 Safari\/537\.31|WordfenceTestMonBot)/.test(navigator.userAgent)){ return; }
	var addEvent = function(evt, handler) {
		if (window.addEventListener) {
			document.addEventListener(evt, handler, false);
		} else if (window.attachEvent) {
			document.attachEvent('on' + evt, handler);
		}
	};
	var removeEvent = function(evt, handler) {
		if (window.removeEventListener) {
			document.removeEventListener(evt, handler, false);
		} else if (window.detachEvent) {
			document.detachEvent('on' + evt, handler);
		}
	};
	var evts = 'contextmenu dblclick drag dragend dragenter dragleave dragover dragstart drop keydown keypress keyup mousedown mousemove mouseout mouseover mouseup mousewheel scroll'.split(' ');
	var logHuman = function() {
		if (window.wfLogHumanRan) { return; }
		window.wfLogHumanRan = true;
		var wfscr = document.createElement('script');
		wfscr.type = 'text/javascript';
		wfscr.async = true;
		wfscr.src = url + '&r=' + Math.random();
		(document.getElementsByTagName('head')[0]||document.getElementsByTagName('body')[0]).appendChild(wfscr);
		for (var i = 0; i < evts.length; i++) {
			removeEvent(evts[i], logHuman);
		}
	};
	for (var i = 0; i < evts.length; i++) {
		addEvent(evts[i], logHuman);
	}
})('$URL');
</script>
HTML;
		}
	}
	public static function shutdownAction(){
	}
	public static function wfFunc_viewActivityLog(){
		require(dirname(__FILE__) . '/viewFullActivityLog.php');
		exit(0);
	}
	public static function wfFunc_IPTraf(){
		$IP = $_GET['IP'];
		try {
			$response = self::IPTraf($IP);
			$reverseLookup = $response['reverseLookup'];
			$results = $response['results'];
			require(dirname(__FILE__) . '/IPTraf.php');
			exit(0);
		} catch (InvalidArgumentException $e) {
			echo $e->getMessage();
			exit;
		}
	}

	private static function IPTraf($ip) {
		if(!wfUtils::isValidIP($ip)){
			throw new InvalidArgumentException(__("An invalid IP address was specified.", 'wordfence'));
		}
		$reverseLookup = wfUtils::reverseLookup($ip);
		$wfLog = wfLog::shared();
		$results = array_merge(
			$wfLog->getHits('hits', '404', 0, 10000, $ip),
			$wfLog->getHits('hits', 'hit', 0, 10000, $ip)
		);
		usort($results, 'wordfence::iptrafsort');
		
		$ids = array();
		foreach ($results as $k => $r) {
			if (isset($ids[$r['id']])) {
				unset($results[$k]);
			}
			else {
				$ids[$r['id']] = 1;
			}
		}
		
		$results = array_values($results);
		
		for ($i = 0; $i < count($results); $i++){
			if(array_key_exists($i + 1, $results)){
				$results[$i]['timeSinceLastHit'] = sprintf('%.4f', $results[$i]['ctime'] - $results[$i + 1]['ctime']);
			} else {
				$results[$i]['timeSinceLastHit'] = '';
			}
		}
		return compact('reverseLookup', 'results');
	}

	public static function iptrafsort($b, $a){
		if($a['ctime'] == $b['ctime']){ return 0; }
		return ($a['ctime'] < $b['ctime']) ? -1 : 1;
	}

	public static function wfFunc_viewOption() {
		/** @var wpdb $wpdb */
		global $wpdb;
		$site_id = !empty($_GET['site_id']) ? absint($_GET['site_id']) : get_current_blog_id();
		$option_name = !empty($_GET['option']) ? $_GET['option'] : false;

		$table_options = wfDB::blogTable('options', $site_id);
		$option_value = $wpdb->get_var($wpdb->prepare("SELECT option_value FROM {$table_options} WHERE option_name = %s", $option_name));

		header('Content-type: text/plain');
		exit($option_value);
	}

	public static function wfFunc_view(){
		wfUtils::doNotCache();
		if (WORDFENCE_DISABLE_FILE_VIEWER) {
			_e("File access blocked. (WORDFENCE_DISABLE_FILE_VIEWER is true)", 'wordfence');
			exit();
		}
		$localFile = ABSPATH . preg_replace('/^(?:\.\.|[\/]+)/', '', sanitize_text_field($_GET['file']));
		if(strpos($localFile, '..') !== false){
			_e("Invalid file requested. (Relative paths not allowed)", 'wordfence');
			exit();
		}
		if(preg_match('/[\'\"<>\!\{\}\(\)\&\@\%\$\*\+\[\]\?]+/', $localFile)){
			_e("File contains illegal characters.", 'wordfence');
			exit();
		}
		$cont = @file_get_contents($localFile);
		$isEmpty = false;
		if(! $cont){
			if(file_exists($localFile) && filesize($localFile) === 0){ //There's a remote possibility that very large files on 32 bit systems will return 0 here, but it's about 1 in 2 billion
				$isEmpty = true;
			} else {
				$err = error_get_last();
				printf(/* translators: Error message. */ __("We could not open the requested file for reading. The error was: %s", 'wordfence'), $err['message']);
				exit(0);
			}
		}
		$fileMTime = @filemtime($localFile);
		$fileMTime = date('l jS \of F Y h:i:s A', $fileMTime);
		try {
			if(wfUtils::fileOver2Gigs($localFile)){
				$fileSize = __("Greater than 2 Gigs", 'wordfence');
			} else {
				$fileSize = @filesize($localFile); //Checked if over 2 gigs above
				$fileSize = number_format($fileSize, 0, '', ',') . ' bytes';
			}
		} catch(Exception $e){ $fileSize = __('Unknown file size.', 'wordfence'); }

		require(dirname(__FILE__) . '/wfViewResult.php');
		exit(0);
	}
	public static function wfFunc_diff(){
		wfUtils::doNotCache();
		if (WORDFENCE_DISABLE_FILE_VIEWER) {
			esc_html_e("File access blocked. (WORDFENCE_DISABLE_FILE_VIEWER is true)", 'wordfence');
			exit();
		}
		if(preg_match('/[\'\"<>\!\{\}\(\)\&\@\%\$\*\+\[\]\?]+/', $_GET['file'])){
			esc_html_e("File contains illegal characters.", 'wordfence');
			exit();
		}

		$result = self::getWPFileContent($_GET['file'], $_GET['cType'], $_GET['cName'], $_GET['cVersion']);
		if( isset( $result['errorMsg'] ) && $result['errorMsg']){
			echo wp_kses($result['errorMsg'], array());
			exit(0);
		} else if(! $result['fileContent']){
			esc_html_e("We could not get the contents of the original file to do a comparison.", 'wordfence');
			exit(0);
		}

		$localFile = realpath(ABSPATH . '/' . preg_replace('/^[\.\/]+/', '', $_GET['file']));
		$localContents = file_get_contents($localFile);
		if($localContents == $result['fileContent']){
			$diffResult = '';
		} else {
			$diff = new Diff(
				//Treat DOS and Unix files the same
				preg_split("/(?:\r\n|\n)/", $result['fileContent']),
				preg_split("/(?:\r\n|\n)/", $localContents),
				array()
				);
			$renderer = new Diff_Renderer_Html_SideBySide;
			$diffResult = $diff->Render($renderer);
		}
		require(dirname(__FILE__) . '/diffResult.php');
		exit(0);
	}

	public static function wfFunc_download() {
		wfUtils::doNotCache();
		if (WORDFENCE_DISABLE_FILE_VIEWER) {
			esc_html_e("File access blocked. (WORDFENCE_DISABLE_FILE_VIEWER is true)", 'wordfence');
			exit();
		}
		$localFile = ABSPATH . preg_replace('/^(?:\.\.|[\/]+)/', '', sanitize_text_field($_GET['file']));
		if (strpos($localFile, '..') !== false) {
			esc_html_e("Invalid file requested. (Relative paths not allowed)", 'wordfence');
			exit();
		}
		if (preg_match('/[\'\"<>\!\{\}\(\)\&\@\%\$\*\+\[\]\?]+/', $localFile)) {
			esc_html_e("File contains illegal characters.", 'wordfence');
			exit();
		}
		if (!file_exists($localFile)) {
			_e('File does not exist.', 'wordfence');
			exit();
		}

		$filename = basename($localFile);
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename="' . $filename . '"');
		header('Content-Length: ' . filesize($localFile));
		readfile($localFile);
		exit;
	}
	
	public static function wfFunc_blockedIPs() {
		$blocks = wfBlock::ipBlocks(true);
		
		$output = '';
		if (is_array($blocks)) {
			foreach ($blocks as $entry) {
				$output .= $entry->ip . "\n";
			}
		}		
				
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename="' . get_bloginfo('name', 'raw') . ' - Blocked IPs.txt"');
		header('Content-Length: ' . strlen($output));
		
		echo $output;
		exit;
	}

	/**
	 *
	 */
	public static function wfFunc_debugWAF() {
		$data = array();
		if (!empty($_GET['hitid'])) {
			$data['hit'] = new wfRequestModel($_GET['hitid']);
			if ($data['hit']->actionData) {
				$data['hitData'] = (object) wfRequestModel::unserializeActionData($data['hit']->actionData);
			}
			echo wfView::create('waf/debug', $data);
		}
	}

	public static function initAction(){
		load_plugin_textdomain('wordfence', false, basename(WORDFENCE_PATH) . '/languages');

		$firewall = new wfFirewall();
		define('WFWAF_OPERATIONAL', $firewall->testConfig());
		
		$currentUserID = get_current_user_id();
		$role = wordfence::getCurrentUserRole();
		if (!WFWAF_SUBDIRECTORY_INSTALL) {
			try {
				$authCookie = wfWAF::getInstance()->parseAuthCookie();
				$capabilities = wordfence::getCurrentUserCapabilities();
				if (is_user_logged_in() &&
					(
						!$authCookie ||
						(int) $currentUserID !== (int) $authCookie['userID'] ||
						$role !== $authCookie['role'] ||
						$authCookie['capabilities'] !== $capabilities //Capability ordering is fixed so a direct equality check is valid
					)
				) {
					wfUtils::setcookie(wfWAF::getInstance()->getAuthCookieName(),
						$currentUserID . '|' . $role . '|' . implode(',', $capabilities) . '|' .
						wfWAF::getInstance()->getAuthCookieValue($currentUserID, $role, $capabilities),
						time() + 43200, COOKIEPATH, COOKIE_DOMAIN, wfUtils::isFullSSL(), true);
				}
			} catch (wfWAFStorageFileException $e) {
				error_log($e->getMessage());
			} catch (wfWAFStorageEngineMySQLiException $e) {
				error_log($e->getMessage());
			}
		}

		if (wfConfig::get('other_hideWPVersion')) {

			global $wp_version;
			global $wp_styles;

			if (!($wp_styles instanceof WP_Styles)) {
				$wp_styles = new WP_Styles();
			}
			if ($wp_styles->default_version === $wp_version) {
				$wp_styles->default_version = wp_hash($wp_styles->default_version);
			}

			foreach ($wp_styles->registered as $key => $val) {
				if ($wp_styles->registered[$key]->ver === $wp_version) {
					$wp_styles->registered[$key]->ver = wp_hash($wp_styles->registered[$key]->ver);
				}
			}

			global $wp_scripts;
			if (!($wp_scripts instanceof WP_Scripts)) {
				$wp_scripts = new WP_Scripts();
			}
			if ($wp_scripts->default_version === $wp_version) {
				$wp_scripts->default_version = wp_hash($wp_scripts->default_version);
			}

			foreach ($wp_scripts->registered as $key => $val) {
				if ($wp_scripts->registered[$key]->ver === $wp_version) {
					$wp_scripts->registered[$key]->ver = wp_hash($wp_scripts->registered[$key]->ver);
				}
			}
		}
	}
	public static function admin_init(){
		if(! wfUtils::isAdmin()){ return; }
		
		if (is_admin() && isset($_GET['page'])) {
			switch ($_GET['page']) {
				case 'WordfenceBlocking':
					wp_redirect(network_admin_url('admin.php?page=WordfenceWAF#top#blocking'));
					die;

				case 'WordfenceLiveTraffic':
					wp_redirect(network_admin_url('admin.php?page=WordfenceTools&subpage=livetraffic'));
					die;
			}
		}
		
		wfOnboardingController::initialize();
		
		if (wfConfig::get('touppBypassNextCheck')) {
			wfConfig::set('touppBypassNextCheck', 0);
			wfConfig::set('touppPromptNeeded', 0);
		}
		
		foreach(array(
			'activate', 'scan', 'updateAlertEmail', 'sendActivityLog', 'restoreFile',
			'exportSettings', 'importSettings', 'bulkOperation', 'deleteFile', 'deleteDatabaseOption', 'removeExclusion',
			'activityLogUpdate', 'ticker', 'loadIssues', 'updateIssueStatus', 'deleteIssue', 'updateAllIssues',
			'avatarLookup', 'reverseLookup', 'unlockOutIP', 'unblockRange', 'whois', 'recentTraffic', 'unblockIP',
			'blockIP', 'permBlockIP', 'loadStaticPanel', 'updateIPPreview', 'downloadHtaccess', 'downloadLogFile', 'checkHtaccess',
			'updateConfig', 'autoUpdateChoice', 'misconfiguredHowGetIPsChoice', 'switchLiveTrafficSecurityOnlyChoice', 'dismissAdminNotice',
			'killScan', 'saveCountryBlocking', 'tourClosed',
			'downgradeLicense', 'addTwoFactor', 'twoFacActivate', 'twoFacDel',
			'loadTwoFactor', 'sendTestEmail',
			'email_summary_email_address_debug', 'unblockNetwork',
			'sendDiagnostic', 'saveDisclosureState', 'saveWAFConfig', 'updateWAFRules', 'loadLiveTraffic', 'whitelistWAFParamKey',
			'disableDirectoryListing', 'fixFPD', 'deleteAdminUser', 'revokeAdminUser',
			'hideFileHtaccess', 'saveDebuggingConfig',
			'whitelistBulkDelete', 'whitelistBulkEnable', 'whitelistBulkDisable',
			'dismissNotification', 'utilityScanForBlacklisted', 'dashboardShowMore',
			'saveOptions', 'restoreDefaults', 'enableAllOptionsPage', 'createBlock', 'deleteBlocks', 'makePermanentBlocks', 'getBlocks',
			'installAutoPrepend', 'uninstallAutoPrepend',
			'installLicense', 'recordTOUPP', 'mailingSignup',
			'switchTo2FANew', 'switchTo2FAOld',
			'wfcentral_step1', 'wfcentral_step2', 'wfcentral_step3', 'wfcentral_step4', 'wfcentral_step5', 'wfcentral_step6', 'wfcentral_disconnect',
			'exportDiagnostics',
		) as $func){
			add_action('wp_ajax_wordfence_' . $func, 'wordfence::ajaxReceiver');
		}
		
		wp_register_script('chart-js', wfUtils::getBaseURL() . wfUtils::versionedAsset('js/Chart.bundle.min.js'), array('jquery'), '2.4.0');
		wp_register_script('wordfence-select2-js', wfUtils::getBaseURL() . wfUtils::versionedAsset('js/wfselect2.min.js'), array('jquery', 'jquery-ui-tooltip'), WORDFENCE_VERSION);
		wp_register_style('wordfence-select2-css', wfUtils::getBaseURL() . wfUtils::versionedAsset('css/wfselect2.min.css'), array(), WORDFENCE_VERSION);
		wp_register_style('wordfence-font-awesome-style', wfUtils::getBaseURL() . wfUtils::versionedAsset('css/wf-font-awesome.css'), '', WORDFENCE_VERSION);

		if (isset($_GET['page']) && (preg_match('/^Wordfence/', @$_GET['page']) || ($_GET['page'] == 'WFLS' && wfOnboardingController::shouldShowNewTour(wfOnboardingController::TOUR_LOGIN_SECURITY)))) {
			wp_enqueue_style('wp-pointer');
			wp_enqueue_script('wp-pointer');
			wp_enqueue_style('wordfence-font', wfUtils::getBaseURL() . wfUtils::versionedAsset('css/wf-roboto-font.css'), '', WORDFENCE_VERSION);
			wp_enqueue_style('wordfence-font-awesome-style');
			wp_enqueue_style('wordfence-main-style', wfUtils::getBaseURL() . wfUtils::versionedAsset('css/main.css'), '', WORDFENCE_VERSION);
			wp_enqueue_style('wordfence-ionicons-style', wfUtils::getBaseURL() . wfUtils::versionedAsset('css/wf-ionicons.css'), '', WORDFENCE_VERSION);
			wp_enqueue_style('wordfence-colorbox-style', wfUtils::getBaseURL() . wfUtils::versionedAsset('css/wf-colorbox.css'), '', WORDFENCE_VERSION);

			wp_enqueue_script('json2');
			wp_enqueue_script('jquery-ui-core');
			wp_enqueue_script('jquery-ui-menu');
			wp_enqueue_script('jquery.wftmpl', wfUtils::getBaseURL() . wfUtils::versionedAsset('js/jquery.tmpl.min.js'), array('jquery'), WORDFENCE_VERSION);
			wp_enqueue_script('jquery.wfcolorbox', wfUtils::getBaseURL() . wfUtils::versionedAsset('js/jquery.colorbox-min.js'), array('jquery'), WORDFENCE_VERSION);
			wp_enqueue_script('jquery.wfdataTables', wfUtils::getBaseURL() . wfUtils::versionedAsset('js/jquery.dataTables.min.js'), array('jquery'), WORDFENCE_VERSION);
			wp_enqueue_script('jquery.qrcode', wfUtils::getBaseURL() . wfUtils::versionedAsset('js/jquery.qrcode.min.js'), array('jquery'), WORDFENCE_VERSION);
			//wp_enqueue_script('jquery.tools', wfUtils::getBaseURL() . wfUtils::versionedAsset('js/jquery.tools.min.js'), array('jquery'));
			wp_enqueue_script('wfi18njs', wfUtils::getBaseURL() . wfUtils::versionedAsset('js/wfi18n.js'), array(), WORDFENCE_VERSION);
			wp_enqueue_script('wordfenceAdminExtjs', wfUtils::getBaseURL() . wfUtils::versionedAsset('js/wfglobal.js'), array('jquery'), WORDFENCE_VERSION);
			wp_enqueue_script('wordfenceAdminjs', wfUtils::getBaseURL() . wfUtils::versionedAsset('js/admin.js'), array('jquery', 'jquery-ui-core', 'jquery-ui-menu'), WORDFENCE_VERSION);
			wp_enqueue_script('wordfenceDropdownjs', wfUtils::getBaseURL() . wfUtils::versionedAsset('js/wfdropdown.js'), array('jquery'), WORDFENCE_VERSION);
			self::setupAdminVars();
			
			if (wfConfig::get('touppPromptNeeded')) {
				add_filter('admin_body_class', 'wordfence::showTOUPPOverlay', 99, 1);
			}
		} else {
			wp_enqueue_style('wp-pointer');
			wp_enqueue_script('wp-pointer');
			wp_enqueue_script('wfi18njs', wfUtils::getBaseURL() . wfUtils::versionedAsset('js/wfi18n.js'), array(), WORDFENCE_VERSION);
			wp_enqueue_script('wordfenceAdminExtjs', wfUtils::getBaseURL() . wfUtils::versionedAsset('js/wfglobal.js'), array('jquery'), WORDFENCE_VERSION);
			wp_enqueue_style('wordfence-font-awesome-style');
			wp_enqueue_style('wordfence-global-style', wfUtils::getBaseURL() . wfUtils::versionedAsset('css/wf-global.css'), '', WORDFENCE_VERSION);
			self::setupAdminVars();
		}
		
		if (is_admin()) { //Back end only
			wfUtils::refreshCachedHomeURL();
			wfUtils::refreshCachedSiteURL();
		}
		
		//Early WAF configuration actions
		if ((!WFWAF_AUTO_PREPEND || WFWAF_SUBDIRECTORY_INSTALL) && empty($_GET['wafAction']) && !wfConfig::get('dismissAutoPrependNotice') && !wfOnboardingController::shouldShowAttempt3() && !wfConfig::get('touppPromptNeeded')) {
			if (is_multisite()) {
				add_action('network_admin_notices', 'wordfence::wafAutoPrependNotice');
			} else {
				add_action('admin_notices', 'wordfence::wafAutoPrependNotice');
			}
		}
		
		if (isset($_GET['page']) && $_GET['page'] == 'WordfenceWAF' && isset($_GET['subpage']) && $_GET['subpage'] == 'waf_options') {
			if (!WFWAF_AUTO_PREPEND || WFWAF_SUBDIRECTORY_INSTALL) { //Not yet installed
				if (isset($_GET['action']) && $_GET['action'] == 'configureAutoPrepend') {
					check_admin_referer('wfWAFAutoPrepend', 'wfnonce');
					if (isset($_GET['serverConfiguration']) && wfWAFAutoPrependHelper::isValidServerConfig($_GET['serverConfiguration'])) {
						$helper = new wfWAFAutoPrependHelper($_GET['serverConfiguration']);
						if (isset($_GET['downloadBackup'])) {
							$helper->downloadBackups(isset($_GET['backupIndex']) ? absint($_GET['backupIndex']) : 0);
						}
					}
				}
			}
			else { //Already installed
				if (isset($_GET['action']) && $_GET['action'] == 'removeAutoPrepend') {
					check_admin_referer('wfWAFRemoveAutoPrepend', 'wfnonce');
					if (isset($_GET['serverConfiguration']) && wfWAFAutoPrependHelper::isValidServerConfig($_GET['serverConfiguration'])) {
						$helper = new wfWAFAutoPrependHelper($_GET['serverConfiguration']);
						if (isset($_GET['downloadBackup'])) {
							$helper->downloadBackups(isset($_GET['backupIndex']) ? absint($_GET['backupIndex']) : 0);
						}
					}
				}
			}
		}
	}
	private static function setupAdminVars(){
		$updateInt = max(absint(wfConfig::getInt('actUpdateInterval', 2)), 2) * 1000; //ms

		wp_localize_script('wordfenceAdminExtjs', 'WordfenceAdminVars', array(
			'ajaxURL' => admin_url('admin-ajax.php'),
			'firstNonce' => wp_create_nonce('wp-ajax'),
			'siteBaseURL' => wfUtils::getSiteBaseURL(),
			'debugOn' => wfConfig::get('debugOn', 0),
			'actUpdateInterval' => $updateInt,
			'cacheType' => wfConfig::get('cacheType'),
			'liveTrafficEnabled' => wfConfig::liveTrafficEnabled(),
			'scanIssuesPerPage' => WORDFENCE_SCAN_ISSUES_PER_PAGE,
			'allowsPausing' => wfConfig::get('liveActivityPauseEnabled'),
			'scanRunning' => wfScanner::shared()->isRunning() ? '1' : '0',
			'modalTemplate' => wfView::create('common/modal-prompt', array('title' => '${title}', 'message' => '${message}', 'primaryButton' => array('id' => 'wf-generic-modal-close', 'label' => __('Close', 'wordfence'), 'link' => '#')))->render(),
			'tokenInvalidTemplate' => wfView::create('common/modal-prompt', array('title' => '${title}', 'message' => '${message}', 'primaryButton' => array('id' => 'wf-token-invalid-modal-reload', 'label' => __('Reload', 'wordfence'), 'link' => '#')))->render(),
			'modalHTMLTemplate' => wfView::create('common/modal-prompt', array('title' => '${title}', 'message' => '{{html message}}', 'primaryButton' => array('id' => 'wf-generic-modal-close', 'label' => __('Close', 'wordfence'), 'link' => '#')))->render(),
			'alertEmailBlacklist' => wfConfig::alertEmailBlacklist(),
			'supportURLs' => array(
				'scan-result-repair-modified-files' => esc_url_raw(wfSupportController::supportURL(wfSupportController::ITEM_SCAN_RESULT_REPAIR_MODIFIED_FILES)),
			),
		));
		self::setupI18nJSStrings();
	}

	private static function setupI18nJSStrings() {
		static $called;
		if ($called) {
			return;
		}
		$called = true;
		wp_localize_script('wfi18njs', 'WordfenceI18nStrings', array(
			'${totalIPs} addresses in this network' => __('${totalIPs} addresses in this network', 'wordfence'),
			'%s in POST body: %s' => /* translators: 1. Description of firewall action. 2. Description of input parameters. */ __('%s in POST body: %s', 'wordfence'),
			'%s in cookie: %s' => /* translators: 1. Description of firewall action. 2. Description of input parameters. */ __('%s in cookie: %s', 'wordfence'),
			'%s in file: %s' => /* translators: 1. Description of firewall action. 2. Description of input parameters. */ __('%s in file: %s', 'wordfence'),
			'%s in query string: %s' => /* translators: 1. Description of firewall action. 2. Description of input parameters. */ __('%s in query string: %s', 'wordfence'),
			'%s is not valid hostname' => /* translators: Domain name. */ __('%s is not valid hostname', 'wordfence'),
			'.htaccess Updated' => __('.htaccess Updated', 'wordfence'),
			'.htaccess change' => __('.htaccess change', 'wordfence'),
			'404 Not Found' => __('404 Not Found', 'wordfence'),
			'Activity Log Sent' => __('Activity Log Sent', 'wordfence'),
			'Add action to allowlist' => __('Add action to allowlist', 'wordfence'),
			'Add code to .htaccess' => __('Add code to .htaccess', 'wordfence'),
			'All Hits' => __('All Hits', 'wordfence'),
			'All capabilties of admin user %s were successfully revoked.' => /* translators: WordPress username. */ __('All capabilties of admin user %s were successfully revoked.', 'wordfence'),
			'An error occurred' => __('An error occurred', 'wordfence'),
			'An error occurred when adding the request to the allowlist.' => __('An error occurred when adding the request to the allowlist.', 'wordfence'),
			'Are you sure you want to allowlist this action?' => __('Are you sure you want to allowlist this action?', 'wordfence'),
			'Authentication Code' => __('Authentication Code', 'wordfence'),
			'Background Request Blocked' => __('Background Request Blocked', 'wordfence'),
			'Block This Network' => __('Block This Network', 'wordfence'),
			'Blocked' => __('Blocked', 'wordfence'),
			'Blocked By Firewall' => __('Blocked By Firewall', 'wordfence'),
			'Blocked WAF' => __('Blocked WAF', 'wordfence'),
			'Blocked by Wordfence' => __('Blocked by Wordfence', 'wordfence'),
			'Blocked by Wordfence plugin settings' => __('Blocked by Wordfence plugin settings', 'wordfence'),
			'Blocked by the Wordfence Application Firewall and plugin settings' => __('Blocked by the Wordfence Application Firewall and plugin settings', 'wordfence'),
			'Blocked by the Wordfence Security Network' => __('Blocked by the Wordfence Security Network', 'wordfence'),
			'Blocked by the Wordfence Web Application Firewall' => __('Blocked by the Wordfence Web Application Firewall', 'wordfence'),
			'Bot' => __('Bot', 'wordfence'),
			'Cancel Changes' => __('Cancel Changes', 'wordfence'),
			'Cellphone Sign-In Recovery Codes' => __('Cellphone Sign-In Recovery Codes', 'wordfence'),
			'Cellphone Sign-in activated for user.' => __('Cellphone Sign-in activated for user.', 'wordfence'),
			'Click here to download a backup copy of this file now' => __('Click here to download a backup copy of this file now', 'wordfence'),
			'Click here to download a backup copy of your .htaccess file now' => __('Click here to download a backup copy of your .htaccess file now', 'wordfence'),
			'Click to fix .htaccess' => __('Click to fix .htaccess', 'wordfence'),
			'Close' => __('Close', 'wordfence'),
			'Crawlers' => __('Crawlers', 'wordfence'),
			'Diagnostic report has been sent successfully.' => __('Diagnostic report has been sent successfully.', 'wordfence'),
			'Directory Listing Disabled' => __('Directory Listing Disabled', 'wordfence'),
			'Directory listing has been disabled on your server.' => __('Directory listing has been disabled on your server.', 'wordfence'),
			'Disabled' => __('Disabled', 'wordfence'),
			'Dismiss' => __('Dismiss', 'wordfence'),
			'Don\'t ask again' => __('Don\'t ask again', 'wordfence'),
			'Download' => __('Download', 'wordfence'),
			'Download Backup File' => __('Download Backup File', 'wordfence'),
			'Each line of 16 letters and numbers is a single recovery code, with optional spaces for readability. When typing your password, enter "wf" followed by the entire code like "mypassword wf1234 5678 90AB CDEF". If your site shows a separate prompt for entering a code after entering only your username and password, enter only the code like "1234 5678 90AB CDEF". Your recovery codes are:' => __('Each line of 16 letters and numbers is a single recovery code, with optional spaces for readability. When typing your password, enter "wf" followed by the entire code like "mypassword wf1234 5678 90AB CDEF". If your site shows a separate prompt for entering a code after entering only your username and password, enter only the code like "1234 5678 90AB CDEF". Your recovery codes are:', 'wordfence'),
			'Email Diagnostic Report' => __('Email Diagnostic Report', 'wordfence'),
			'Email Wordfence Activity Log' => __('Email Wordfence Activity Log', 'wordfence'),
			'Enter a valid IP or domain' => __('Enter a valid IP or domain', 'wordfence'),
			'Enter the email address you would like to send the Wordfence activity log to. Note that the activity log may contain thousands of lines of data. This log is usually only sent to a member of the Wordfence support team. It also contains your PHP configuration from the phpinfo() function for diagnostic data.' => __('Enter the email address you would like to send the Wordfence activity log to. Note that the activity log may contain thousands of lines of data. This log is usually only sent to a member of the Wordfence support team. It also contains your PHP configuration from the phpinfo() function for diagnostic data.', 'wordfence'),
			'Error' => __('Error', 'wordfence'),
			'Error Enabling All Options Page' => __('Error Enabling All Options Page', 'wordfence'),
			'Error Restoring Defaults' => __('Error Restoring Defaults', 'wordfence'),
			'Error Saving Option' => __('Error Saving Option', 'wordfence'),
			'Error Saving Options' => __('Error Saving Options', 'wordfence'),
			'Failed Login' => __('Failed Login', 'wordfence'),
			'Failed Login: Invalid Username' => __('Failed Login: Invalid Username', 'wordfence'),
			'Failed Login: Valid Username' => __('Failed Login: Valid Username', 'wordfence'),
			'File hidden successfully' => __('File hidden successfully', 'wordfence'),
			'File restored OK' => __('File restored OK', 'wordfence'),
			'Filter Traffic' => __('Filter Traffic', 'wordfence'),
			'Firewall Response' => __('Firewall Response', 'wordfence'),
			'Full Path Disclosure' => __('Full Path Disclosure', 'wordfence'),
			'Google Bot' => __('Google Bot', 'wordfence'),
			'Google Crawlers' => __('Google Crawlers', 'wordfence'),
			'HTTP Response Code' => __('HTTP Response Code', 'wordfence'),
			'Human' => __('Human', 'wordfence'),
			'Humans' => __('Humans', 'wordfence'),
			'IP' => __('IP', 'wordfence'),
			'Key:' => __('Key:', 'wordfence'),
			'Last Updated: %s' => /* translators: Localized date. */ __('Last Updated: %s', 'wordfence'),
			'Learn more about repairing modified files.' => __('Learn more about repairing modified files.', 'wordfence'),
			'Loading...' => __('Loading...', 'wordfence'),
			'Locked Out' => __('Locked Out', 'wordfence'),
			'Locked out from logging in' => __('Locked out from logging in', 'wordfence'),
			'Logged In' => __('Logged In', 'wordfence'),
			'Logins' => __('Logins', 'wordfence'),
			'Logins and Logouts' => __('Logins and Logouts', 'wordfence'),
			'Look up IP or Domain' => __('Look up IP or Domain', 'wordfence'),
			'Manual block by administrator' => __('Manual block by administrator', 'wordfence'),
			'Next Update Check: %s' => /* translators: Localized date. */ __('Next Update Check: %s', 'wordfence'),
			'No activity to report yet. Please complete your first scan.' => __('No activity to report yet. Please complete your first scan.', 'wordfence'),
			'No issues have been ignored.' => __('No issues have been ignored.', 'wordfence'),
			'No new issues have been found.' => __('No new issues have been found.', 'wordfence'),
			'No rules were updated. Please verify you have permissions to write to the /wp-content/wflogs directory.' => __('No rules were updated. Please verify you have permissions to write to the /wp-content/wflogs directory.', 'wordfence'),
			'No rules were updated. Please verify your website can reach the Wordfence servers.' => __('No rules were updated. Please verify your website can reach the Wordfence servers.', 'wordfence'),
			'No rules were updated. Your website has reached the maximum number of rule update requests. Please try again later.' => __('No rules were updated. Your website has reached the maximum number of rule update requests. Please try again later.', 'wordfence'),
			'Note: Status will update when changes are saved' => __('Note: Status will update when changes are saved', 'wordfence'),
			'OK' => __('OK', 'wordfence'),
			'Pages Not Found' => __('Pages Not Found', 'wordfence'),
			'Paid Members Only' => __('Paid Members Only', 'wordfence'),
			'Please enter a valid IP address or domain name for your whois lookup.' => __('Please enter a valid IP address or domain name for your whois lookup.', 'wordfence'),
			'Please enter a valid email address.' => __('Please enter a valid email address.', 'wordfence'),
			'Please include your support ticket number or forum username.' => __('Please include your support ticket number or forum username.', 'wordfence'),
			'Please make a backup of this file before proceeding. If you need to restore this backup file, you can copy it to the following path from your site\'s root:' => __('Please make a backup of this file before proceeding. If you need to restore this backup file, you can copy it to the following path from your site\'s root:', 'wordfence'),
			'Please specify a reason' => __('Please specify a reason', 'wordfence'),
			'Please specify a valid IP address range in the form of "1.2.3.4 - 1.2.3.5" without quotes. Make sure the dash between the IP addresses in a normal dash (a minus sign on your keyboard) and not another character that looks like a dash.' => __('Please specify a valid IP address range in the form of "1.2.3.4 - 1.2.3.5" without quotes. Make sure the dash between the IP addresses in a normal dash (a minus sign on your keyboard) and not another character that looks like a dash.', 'wordfence'),
			'Please specify either an IP address range, Hostname or a web browser pattern to match.' => __('Please specify either an IP address range, Hostname or a web browser pattern to match.', 'wordfence'),
			'Recent Activity' => __('Recent Activity', 'wordfence'),
			'Recovery Codes' => __('Recovery Codes', 'wordfence'),
			'Redirected' => __('Redirected', 'wordfence'),
			'Redirected by Country Blocking bypass URL' => __('Redirected by Country Blocking bypass URL', 'wordfence'),
			'Referer' => __('Referer', 'wordfence'),
			'Registered Users' => __('Registered Users', 'wordfence'),
			'Restore Defaults' => __('Restore Defaults', 'wordfence'),
			'Rule Update Failed' => __('Rule Update Failed', 'wordfence'),
			'Rules Updated' => __('Rules Updated', 'wordfence'),
			'Save Changes' => __('Save Changes', 'wordfence'),
			'Scan Complete.' => __('Scan Complete.', 'wordfence'),
			'Scan the code below with your authenticator app to add this account. Some authenticator apps also allow you to type in the text version instead.' => __('Scan the code below with your authenticator app to add this account. Some authenticator apps also allow you to type in the text version instead.', 'wordfence'),
			'Security Event' => __('Security Event', 'wordfence'),
			'Send' => __('Send', 'wordfence'),
			'Sorry, but no data for that IP or domain was found.' => __('Sorry, but no data for that IP or domain was found.', 'wordfence'),
			'Specify a valid IP range' => __('Specify a valid IP range', 'wordfence'),
			'Specify a valid hostname' => __('Specify a valid hostname', 'wordfence'),
			'Specify an IP range, Hostname or Browser pattern' => __('Specify an IP range, Hostname or Browser pattern', 'wordfence'),
			'Success deleting file' => __('Success deleting file', 'wordfence'),
			'Success removing option' => __('Success removing option', 'wordfence'),
			'Success restoring file' => __('Success restoring file', 'wordfence'),
			'Success updating option' => __('Success updating option', 'wordfence'),
			'Successfully deleted admin' => __('Successfully deleted admin', 'wordfence'),
			'Successfully revoked admin' => __('Successfully revoked admin', 'wordfence'),
			'Test Email Sent' => __('Test Email Sent', 'wordfence'),
			'The \'How does Wordfence get IPs\' option was successfully updated to the recommended value.' => __('The \'How does Wordfence get IPs\' option was successfully updated to the recommended value.', 'wordfence'),
			'The Full Path disclosure issue has been fixed' => __('The Full Path disclosure issue has been fixed', 'wordfence'),
			'The admin user %s was successfully deleted.' => /* translators: WordPress username. */ __('The admin user %s was successfully deleted.', 'wordfence'),
			'The file %s was successfully deleted.' => /* translators: File path. */ __('The file %s was successfully deleted.', 'wordfence'),
			'The file %s was successfully hidden from public view.' => /* translators: File path. */ __('The file %s was successfully hidden from public view.', 'wordfence'),
			'The file %s was successfully restored.' => /* translators: File path. */ __('The file %s was successfully restored.', 'wordfence'),
			'The option %s was successfully removed.' => /* translators: WordPress option. */ __('The option %s was successfully removed.', 'wordfence'),
			'The request has been allowlisted. Please try it again.' => __('The request has been allowlisted. Please try it again.', 'wordfence'),
			'There was an error while sending the email.' => __('There was an error while sending the email.', 'wordfence'),
			'This will be shown only once. Keep these codes somewhere safe.' => __('This will be shown only once. Keep these codes somewhere safe.', 'wordfence'),
			'Throttled' => __('Throttled', 'wordfence'),
			'Two Factor Status' => __('Two Factor Status', 'wordfence'),
			'Type' => __('Type', 'wordfence'),
			'Type: %s' => /* translators: HTTP client type. */ __('Type: %s', 'wordfence'),
			'URL' => __('URL', 'wordfence'),
			'Unable to automatically hide file' => __('Unable to automatically hide file', 'wordfence'),
			'Use one of these %s codes to log in if you are unable to access your phone. Codes are 16 characters long, plus optional spaces. Each one may be used only once.' => /* translators: 2FA backup codes. */ __('Use one of these %s codes to log in if you are unable to access your phone. Codes are 16 characters long, plus optional spaces. Each one may be used only once.', 'wordfence'),
			'Use one of these %s codes to log in if you lose access to your authenticator device. Codes are 16 characters long, plus optional spaces. Each one may be used only once.' => /* translators: 2FA backup codes. */ __('Use one of these %s codes to log in if you lose access to your authenticator device. Codes are 16 characters long, plus optional spaces. Each one may be used only once.', 'wordfence'),
			'User Agent' => __('User Agent', 'wordfence'),
			'User ID' => __('User ID', 'wordfence'),
			'Username' => __('Username', 'wordfence'),
			'WHOIS LOOKUP' => __('WHOIS LOOKUP', 'wordfence'),
			'We are about to change your <em>.htaccess</em> file. Please make a backup of this file before proceeding.' => __('We are about to change your <em>.htaccess</em> file. Please make a backup of this file before proceeding.', 'wordfence'),
			'We can\'t modify your .htaccess file for you because: %s' => /* translators: Error message. */ __('We can\'t modify your .htaccess file for you because: %s', 'wordfence'),
			'We encountered a problem' => __('We encountered a problem', 'wordfence'),
			'Wordfence Firewall blocked a background request to WordPress for the URL %s. If this occurred as a result of an intentional action, you may consider allowlisting the request to allow it in the future.' => /* translators: URL. */ __('Wordfence Firewall blocked a background request to WordPress for the URL %s. If this occurred as a result of an intentional action, you may consider allowlisting the request to allow it in the future.', 'wordfence'),
			'Wordfence is working...' => __('Wordfence is working...', 'wordfence'),
			'You are using Nginx as your web server. You\'ll need to disable autoindexing in your nginx.conf. See the <a target=\'_blank\'  rel=\'noopener noreferrer\' href=\'http://nginx.org/en/docs/http/ngx_http_autoindex_module.html\'>Nginx docs for more info</a> on how to do this.' => __('You are using Nginx as your web server. You\'ll need to disable autoindexing in your nginx.conf. See the <a target=\'_blank\'  rel=\'noopener noreferrer\' href=\'http://nginx.org/en/docs/http/ngx_http_autoindex_module.html\'>Nginx docs for more info</a> on how to do this.', 'wordfence'),
			'You are using an Nginx web server and using a FastCGI processor like PHP5-FPM. You will need to manually delete or hide those files.' => __('You are using an Nginx web server and using a FastCGI processor like PHP5-FPM. You will need to manually delete or hide those files.', 'wordfence'),
			'You are using an Nginx web server and using a FastCGI processor like PHP5-FPM. You will need to manually modify your php.ini to disable <em>display_error</em>' => __('You are using an Nginx web server and using a FastCGI processor like PHP5-FPM. You will need to manually modify your php.ini to disable <em>display_error</em>', 'wordfence'),
			'You forgot to include a reason you\'re blocking this IP range. We ask you to include this for your own record keeping.' => __('You forgot to include a reason you\'re blocking this IP range. We ask you to include this for your own record keeping.', 'wordfence'),
			'You have unsaved changes to your options. If you leave this page, those changes will be lost.' => __('You have unsaved changes to your options. If you leave this page, those changes will be lost.', 'wordfence'),
			'Your .htaccess has been updated successfully. Please verify your site is functioning normally.' => __('Your .htaccess has been updated successfully. Please verify your site is functioning normally.', 'wordfence'),
			'Your Wordfence activity log was sent to %s' => /* translators: Email address. */ __('Your Wordfence activity log was sent to %s', 'wordfence'),
			'Your rules have been updated successfully.' => __('Your rules have been updated successfully.', 'wordfence'),
			'Your rules have been updated successfully. You are currently using the free version of Wordfence. Upgrade to Wordfence premium to have your rules updated automatically as new threats emerge. <a href="https://www.wordfence.com/wafUpdateRules1/wordfence-signup/">Click here to purchase a premium license</a>. <em>Note: Your rules will still update every 30 days as a free user.</em>' => __('Your rules have been updated successfully. You are currently using the free version of Wordfence. Upgrade to Wordfence premium to have your rules updated automatically as new threats emerge. <a href="https://www.wordfence.com/wafUpdateRules1/wordfence-signup/">Click here to purchase a premium license</a>. <em>Note: Your rules will still update every 30 days as a free user.</em>', 'wordfence'),
			'Your test email was sent to the requested email address. The result we received from the WordPress wp_mail() function was: %s<br /><br />A \'True\' result means WordPress thinks the mail was sent without errors. A \'False\' result means that WordPress encountered an error sending your mail. Note that it\'s possible to get a \'True\' response with an error elsewhere in your mail system that may cause emails to not be delivered.' => /* translators: wp_mail() return value. */ __('Your test email was sent to the requested email address. The result we received from the WordPress wp_mail() function was: %s<br /><br />A \'True\' result means WordPress thinks the mail was sent without errors. A \'False\' result means that WordPress encountered an error sending your mail. Note that it\'s possible to get a \'True\' response with an error elsewhere in your mail system that may cause emails to not be delivered.', 'wordfence'),
			'blocked by firewall' => __('blocked by firewall', 'wordfence'),
			'blocked by firewall for %s' => /* translators: Reason for firewall action. */ __('blocked by firewall for %s', 'wordfence'),
			'blocked by real-time IP blocklist' => __('blocked by real-time IP blocklist', 'wordfence'),
			'blocked by the Wordfence Security Network' => __('blocked by the Wordfence Security Network', 'wordfence'),
			'blocked for %s' => /* translators: Reason for firewall action. */ __('blocked for %s', 'wordfence'),
			'locked out from logging in' => __('locked out from logging in', 'wordfence'),
		));
	}
	public static function showTOUPPOverlay($classList) {
		return trim($classList . ' wf-toupp-required');
	}
	public static function activation_warning(){
		$activationError = get_option('wf_plugin_act_error', '');
		if(strlen($activationError) > 400){
			$activationError = substr($activationError, 0, 400) . '...[output truncated]';
		}
		if($activationError){
			echo '<div id="wordfenceConfigWarning" class="updated fade"><p><strong>' .
				__('Wordfence generated an error on activation. The output we received during activation was:', 'wordfence')
				. '</strong> ' . wp_kses($activationError, array()) . '</p></div>';
		}
		delete_option('wf_plugin_act_error');
	}
	public static function noKeyError(){
		echo '<div id="wordfenceConfigWarning" class="fade error"><p>' .
			sprintf('<strong>%s</strong> ', __('Wordfence could not register with the Wordfence scanning servers when it activated.', 'wordfence')) .
			__('You can try to fix this by deactivating Wordfence and then activating it again, so Wordfence will retry registering for you. If you keep seeing this error, it usually means your WordPress server can\'t connect to our scanning servers, or your wfConfig database table cannot be created to save the key. You can try asking your host to allow your server to connect to noc1.wordfence.com or check the wfConfig database table and database privileges.', 'wordfence')
			. '</p></div>';
	}
	public static function wafConfigInaccessibleNotice() {
		if (function_exists('network_admin_url') && is_multisite()) {
			$wafMenuURL = network_admin_url('admin.php?page=WordfenceWAF&wafconfigrebuild=1');
		}
		else {
			$wafMenuURL = admin_url('admin.php?page=WordfenceWAF&wafconfigrebuild=1');
		}
		$wafMenuURL = add_query_arg(array(
			'waf-nonce' => wp_create_nonce('wafconfigrebuild'),
		), $wafMenuURL);
		
		echo '<div id="wafConfigInaccessibleNotice" class="fade error"><p><strong>' . __('The Wordfence Web Application Firewall cannot run.', 'wordfence') . '</strong> ' .
			sprintf(
				/* translators: 1. WordPress admin panel URL. 2. Support URL. */
				__('The configuration files are corrupt or inaccessible by the web server, which is preventing the WAF from functioning. Please verify the web server has permission to access the configuration files. You may also try to rebuild the configuration file by <a href="%1$s">clicking here</a>. It will automatically resume normal operation when it is fixed. <a class="wfhelp" target="_blank" rel="noopener noreferrer" href="%2$s"></a>', 'wordfence'),
				$wafMenuURL,
				wfSupportController::esc_supportURL(wfSupportController::ITEM_NOTICE_WAF_INACCESSIBLE_CONFIG)
			) . '</p></div>';
	}
	public static function wafStorageEngineFallbackNotice() {
		echo '<div class="notice notice-warning"><p>'.__('The WAF storage engine is currently set to mysqli, but Wordfence is unable to use the database. The WAF will fall back to using local file system storage instead.', 'wordfence').'</p></div>';
	}
	public static function wafConfigNeedsUpdate_mod_php() {
		if (function_exists('network_admin_url') && is_multisite()) {
			$wafMenuURL = network_admin_url('admin.php?page=WordfenceWAF&wafconfigfixmodphp=1');
		}
		else {
			$wafMenuURL = admin_url('admin.php?page=WordfenceWAF&wafconfigfixmodphp=1');
		}
		$wafMenuURL = add_query_arg(array(
			'waf-nonce' => wp_create_nonce('wafconfigfixmodphp'),
		), $wafMenuURL);
		
		echo '<div id="wafConfigNeedsUpdateNotice" class="fade error"><p><strong>' . __('The Wordfence Web Application Firewall needs a configuration update.', 'wordfence') . '</strong> ' .
			sprintf(
				/* translators: 1. WordPress admin panel URL. 2. Support URL. */
				__('It is currently configured to use an older version of PHP and may become deactivated if PHP is updated. You may perform the configuration update automatically by <a href="%1$s">clicking here</a>. <a class="wfhelp" target="_blank" rel="noopener noreferrer" href="%2$s"></a>', 'wordfence'),
				$wafMenuURL,
				wfSupportController::esc_supportURL(wfSupportController::ITEM_NOTICE_WAF_MOD_PHP_FIX)
			) . '</p></div>';
	}
	public static function wafConfigNeedsFixed_mod_php() {
		if (function_exists('network_admin_url') && is_multisite()) {
			$wafMenuURL = network_admin_url('admin.php?page=WordfenceWAF&wafconfigfixmodphp=1');
		}
		else {
			$wafMenuURL = admin_url('admin.php?page=WordfenceWAF&wafconfigfixmodphp=1');
		}
		$wafMenuURL = add_query_arg(array(
			'waf-nonce' => wp_create_nonce('wafconfigfixmodphp'),
		), $wafMenuURL);
		
		echo '<div id="wafConfigNeedsFixedNotice" class="fade error"><p><strong>' . __('The Wordfence Web Application Firewall needs a configuration update.', 'wordfence') . '</strong> ' .
			sprintf(
				/* translators: 1. WordPress admin panel URL. 2. Support URL. */
				__('It is not currently in extended protection mode but was configured to use an older version of PHP and may have become deactivated when PHP was updated. You may perform the configuration update automatically by <a href="%1$s">clicking here</a> or use the "Optimize the Wordfence Firewall" button on the Firewall Options page. <a class="wfhelp" target="_blank" rel="noopener noreferrer" href="%2$s"></a>', 'wordfence'),
				$wafMenuURL,
				wfSupportController::esc_supportURL(wfSupportController::ITEM_NOTICE_WAF_MOD_PHP_FIX)
			) . '</p></div>';
	}
	public static function wafReadOnlyNotice() {
		echo '<div id="wordfenceWAFReadOnlyNotice" class="fade error"><p><strong>' . __('The Wordfence Web Application Firewall is in read-only mode.', 'wordfence') . '</strong> ' . sprintf('PHP is currently running as a command line user and to avoid file permission issues, the WAF is running in read-only mode. It will automatically resume normal operation when run normally by a web server. <a class="wfhelp" target="_blank" rel="noopener noreferrer" href="%s"></a>', wfSupportController::esc_supportURL(wfSupportController::ITEM_NOTICE_WAF_READ_ONLY_WARNING)) . '</p></div>';
	}
	public static function misconfiguredHowGetIPsNotice() {
		$url = network_admin_url('admin.php?page=Wordfence&subpage=global_options');
		$existing = wfConfig::get('howGetIPs', '');
		$recommendation = wfConfig::get('detectProxyRecommendation', '');
		
		$existingMsg = '';
		if ($existing == 'REMOTE_ADDR') {
			$existingMsg = __('This site is currently using PHP\'s built in REMOTE_ADDR.', 'wordfence');
		}
		else if ($existing == 'HTTP_X_FORWARDED_FOR') {
			$existingMsg = __('This site is currently using the X-Forwarded-For HTTP header, which should only be used when the site is behind a front-end proxy that outputs this header.', 'wordfence');
		}
		else if ($existing == 'HTTP_X_REAL_IP') {
			$existingMsg = __('This site is currently using the X-Real-IP HTTP header, which should only be used when the site is behind a front-end proxy that outputs this header.', 'wordfence');
		}
		else if ($existing == 'HTTP_CF_CONNECTING_IP') {
			$existingMsg = __('This site is currently using the Cloudflare "CF-Connecting-IP" HTTP header, which should only be used when the site is behind Cloudflare.', 'wordfence');
		}
		
		$recommendationMsg = '';
		if ($recommendation == 'REMOTE_ADDR') {
			$recommendationMsg = __('For maximum security use PHP\'s built in REMOTE_ADDR.', 'wordfence');
		}
		else if ($recommendation == 'HTTP_X_FORWARDED_FOR') {
			$recommendationMsg = __('This site appears to be behind a front-end proxy, so using the X-Forwarded-For HTTP header will resolve to the correct IPs.', 'wordfence');
		}
		else if ($recommendation == 'HTTP_X_REAL_IP') {
			$recommendationMsg = __('This site appears to be behind a front-end proxy, so using the X-Real-IP HTTP header will resolve to the correct IPs.', 'wordfence');
		}
		else if ($recommendation == 'HTTP_CF_CONNECTING_IP') {
			$recommendationMsg = __('This site appears to be behind Cloudflare, so using the Cloudflare "CF-Connecting-IP" HTTP header will resolve to the correct IPs.', 'wordfence');
		}
		echo '<div id="wordfenceMisconfiguredHowGetIPsNotice" class="fade error"><p><strong>' .
			__('Your \'How does Wordfence get IPs\' setting is misconfigured.', 'wordfence')
			. '</strong> ' . $existingMsg . ' ' . $recommendationMsg . ' <a href="#" onclick="wordfenceExt.misconfiguredHowGetIPsChoice(\'yes\'); return false;">' .
			__('Click here to use the recommended setting', 'wordfence')
			. '</a> ' .
			__('or', 'wordfence')
			. ' <a href="' . $url . '">' .
			__('visit the options page', 'wordfence')
			. '</a> ' .
			__('to manually update it.', 'wordfence')
			. '</p><p>
		<a class="wf-btn wf-btn-default wf-btn-sm wf-dismiss-link" href="#" onclick="wordfenceExt.misconfiguredHowGetIPsChoice(\'no\'); return false;">' .
			__('Dismiss', 'wordfence')
			. '</a> <a class="wfhelp" target="_blank" rel="noopener noreferrer" href="' . wfSupportController::esc_supportURL(wfSupportController::ITEM_NOTICE_MISCONFIGURED_HOW_GET_IPS) . '"></a></p></div>';
	}
	public static function autoUpdateNotice(){
		echo '<div id="wordfenceAutoUpdateChoice" class="fade error"><p><strong>' .
			__('Do you want Wordfence to stay up-to-date automatically?', 'wordfence')
			. '</strong>&nbsp;&nbsp;&nbsp;<a href="#" onclick="wordfenceExt.autoUpdateChoice(\'yes\'); return false;">'.
			__('Yes, enable auto-update.', 'wordfence')
			. '</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="#" onclick="wordfenceExt.autoUpdateChoice(\'no\'); return false;">' .
			__('No thanks.', 'wordfence')
			. '</a></p></div>';
	}
	public static function admin_menus(){
		if(! wfUtils::isAdmin()){ return; }
		$warningAdded = false;
		if(get_option('wf_plugin_act_error', false)){
			if(wfUtils::isAdminPageMU()){
				add_action('network_admin_notices', 'wordfence::activation_warning');
			} else {
				add_action('admin_notices', 'wordfence::activation_warning');
			}
			$warningAdded = true;
		}
		if(! wfConfig::get('apiKey')){
			if(wfUtils::isAdminPageMU()){
				add_action('network_admin_notices', 'wordfence::noKeyError');
			} else {
				add_action('admin_notices', 'wordfence::noKeyError');
			}
			$warningAdded = true;
		}
		
		$firewall = new wfFirewall();
		if (!empty($_GET['page']) && preg_match('/^Wordfence/i', $_GET['page'])) {
			if (!$firewall->testConfig()) {
				$warningAdded = true;
				if (wfUtils::isAdminPageMU()) {
					add_action('network_admin_notices', 'wordfence::wafConfigInaccessibleNotice');
				}
				else {
					add_action('admin_notices', 'wordfence::wafConfigInaccessibleNotice');
				}
			}
			else if (!$warningAdded && method_exists('wfWAF', 'hasFallbackStorageEngine') && wfWAF::hasFallbackStorageEngine()) {
				$warningAdded = true;
				add_action(wfUtils::isAdminPageMU()?'network_admin_notices':'admin_notices', 'wordfence::wafStorageEngineFallbackNotice');
			}
		}
		
		if (!$warningAdded && !WFWAF_SUBDIRECTORY_INSTALL && !wfWAFAutoPrependHelper::verifyHtaccessMod_php()) {
			if (WFWAF_AUTO_PREPEND) { //Active, running PHP 5 only mod_php block
				$warningAdded = true;
				if (wfUtils::isAdminPageMU()) {
					add_action('network_admin_notices', 'wordfence::wafConfigNeedsUpdate_mod_php');
				}
				else {
					add_action('admin_notices', 'wordfence::wafConfigNeedsUpdate_mod_php');
				}
			}
			else if (PHP_MAJOR_VERSION > 5) { //Inactive, probably deactivated by updating from PHP 5 -> 7 due to no PHP 7 mod_php block
				$warningAdded = true;
				if (wfUtils::isAdminPageMU()) {
					add_action('network_admin_notices', 'wordfence::wafConfigNeedsFixed_mod_php');
				}
				else {
					add_action('admin_notices', 'wordfence::wafConfigNeedsFixed_mod_php');
				}
			}
		}
		
		if (wfOnboardingController::shouldShowAttempt3() || wfConfig::get('touppPromptNeeded')) { //Both top banners
			$warningAdded = true;
		}
		
		//Check WAF rules status
		$firewall = new wfFirewall();
		if ($firewall->firewallMode() != wfFirewall::FIREWALL_MODE_DISABLED) {
			try {
				$lastChecked = (int) wfWAF::getInstance()->getStorageEngine()->getConfig('lastRuleUpdateCheck', null, 'transient');
				$lastUpdated = (int) wfWAF::getInstance()->getStorageEngine()->getConfig('rulesLastUpdated', null, 'transient');
				$threshold = time() - (86400 * (wfConfig::get('isPaid') ? 2.5 : 9)); //Refresh rate + 2 days
				if ($lastChecked > 0 && $lastUpdated > 0 && $lastChecked < $threshold) {
					$nextUpdate = PHP_INT_MAX;
					$cron = (array) wfWAF::getInstance()->getStorageEngine()->getConfig('cron', null, 'livewaf');
					if (is_array($cron)) {
						/** @var wfWAFCronEvent $event */
						foreach ($cron as $index => $event) {
							if ($event instanceof wfWAFCronFetchRulesEvent) {
								$event->setWaf(wfWAF::getInstance());
								if (!$event->isInPast()) {
									$nextUpdate = min($nextUpdate, $event->getFireTime());
								}
							}
						}
					}
					
					$message = sprintf(
						/* translators: Localized date. */
						__('The last rules update for the Wordfence Web Application Firewall was unsuccessful. The last successful update check was %s, so this site may be missing new rules added since then.', 'wordfence'),
						wfUtils::formatLocalTime(get_option('date_format') . ' ' . get_option('time_format'), $lastChecked)
					);
					
					if (!$firewall->isSubDirectoryInstallation()) {
						if ($nextUpdate < PHP_INT_MAX) {
							$message .= ' ' . sprintf(
								/* translators: 1. Localized date. 2. WordPress admin panel URL. */
								__('You may wait for the next automatic attempt at %1$s or try to <a href="%2$s">Manually Update</a> by clicking the "Manually Refresh Rules" button below the Rules list.', 'wordfence'),
								wfUtils::formatLocalTime(get_option('date_format') . ' ' . get_option('time_format'), $nextUpdate),
								esc_url(network_admin_url('admin.php?page=WordfenceWAF&subpage=waf_options#wf-option-wafRules'))
								);
						}
						else {
							$message .= ' ' . sprintf(/* translators: WordPress admin panel URL. */ __('You may wait for the next automatic attempt or try to <a href="%s">Manually Update</a> by clicking the "Manually Refresh Rules" button below the Rules list.', 'wordfence'), esc_url(network_admin_url('admin.php?page=WordfenceWAF&subpage=waf_options#waf-rules-next-update')));
						}
					}
					else {
						if ($nextUpdate < PHP_INT_MAX) {
							$message .= ' ' . sprintf(/* translators: WordPress admin panel URL. */ __('You may wait for the next automatic attempt at %s or log into the parent site to manually update by clicking the "Manually Refresh Rules" button below the Rules list.', 'wordfence'), wfUtils::formatLocalTime(get_option('date_format') . ' ' . get_option('time_format'), $nextUpdate));
						}
						else {
							$message .= ' ' . __('You may wait for the next automatic attempt or log into the parent site to manually update by clicking the "Manually Refresh Rules" button below the Rules list.', 'wordfence');
						}
					}
					
					wfAdminNoticeQueue::addAdminNotice(wfAdminNotice::SEVERITY_CRITICAL, $message, 'waf-rules-failed');
				}
				else {
					wfAdminNoticeQueue::removeAdminNotice(false, 'waf-rules-failed');
				}
			}
			catch (wfWAFStorageFileException $e) {
				error_log($e->getMessage());
			}
		}
		else {
			wfAdminNoticeQueue::removeAdminNotice(false, 'waf-rules-failed');
		}
		
		if (wfAdminNoticeQueue::enqueueAdminNotices()) {
			$warningAdded = true;
		}
		
		$existing = wfConfig::get('howGetIPs', '');
		$recommendation = wfConfig::get('detectProxyRecommendation', '');
		$canDisplayMisconfiguredHowGetIPs = true;
		if (empty($existing) || empty($recommendation) || $recommendation == 'UNKNOWN' || $recommendation == 'DEFERRED' || $existing == $recommendation) {
			$canDisplayMisconfiguredHowGetIPs = false;
		}
		if (!$warningAdded && $canDisplayMisconfiguredHowGetIPs && !wfUtils::truthyToBoolean(wfConfig::get('misconfiguredHowGetIPsChoice' . WORDFENCE_VERSION)) && !(defined('WORDFENCE_DISABLE_MISCONFIGURED_HOWGETIPS') && WORDFENCE_DISABLE_MISCONFIGURED_HOWGETIPS)) {
			$warningAdded = true;
			if (wfUtils::isAdminPageMU()) {
				add_action('network_admin_notices', 'wordfence::misconfiguredHowGetIPsNotice');
			}
			else {
				add_action('admin_notices', 'wordfence::misconfiguredHowGetIPsNotice');
			}
		}
		if (!$warningAdded && method_exists(wfWAF::getInstance(), 'isReadOnly') && wfWAF::getInstance()->isReadOnly()) {
			$warningAdded = true;
			if (wfUtils::isAdminPageMU()) {
				add_action('network_admin_notices', 'wordfence::wafReadOnlyNotice');
			}
			else {
				add_action('admin_notices', 'wordfence::wafReadOnlyNotice');
			}
		}
		if(! $warningAdded){
			if (!wfConfig::get('autoUpdate') && !wfConfig::get('autoUpdateChoice')) {
				$warningAdded = true;
				if (wfUtils::isAdminPageMU()) {
					add_action('network_admin_notices', 'wordfence::autoUpdateNotice');
				} else {
					add_action('admin_notices', 'wordfence::autoUpdateNotice');
				}
			}
		}

		if (!empty($_GET['page']) && $_GET['page'] === 'WordfenceWAF' && !empty($_GET['wafconfigrebuild']) && !WFWAF_SUBDIRECTORY_INSTALL) {
			check_admin_referer('wafconfigrebuild', 'waf-nonce');
			
			wfWAF::getInstance()->uninstall();
			if (function_exists('network_admin_url') && is_multisite()) {
				$wafMenuURL = network_admin_url('admin.php?page=WordfenceWAF');
			} else {
				$wafMenuURL = admin_url('admin.php?page=WordfenceWAF');
			}
			wp_redirect($wafMenuURL);
			exit;
		}
		
		if (!empty($_GET['page']) && $_GET['page'] === 'WordfenceWAF' && !empty($_GET['wafconfigfixmodphp']) && !WFWAF_SUBDIRECTORY_INSTALL) {
			check_admin_referer('wafconfigfixmodphp', 'waf-nonce');
			
			wfWAFAutoPrependHelper::fixHtaccessMod_php();
			if (function_exists('network_admin_url') && is_multisite()) {
				$wafMenuURL = network_admin_url('admin.php?page=WordfenceWAF');
			} else {
				$wafMenuURL = admin_url('admin.php?page=WordfenceWAF');
			}
			wp_redirect($wafMenuURL);
			exit;
		}

		if (version_compare(PHP_VERSION, '8.0', '>=') && !get_user_option('wordfence_php8_nag')) {
			wfAdminNoticeQueue::addAdminNotice(wfAdminNotice::SEVERITY_INFO, wp_kses(__(<<<HTML
PHP 8 includes significant changes from PHP 7, which may cause unexpected bugs in plugins, themes, and WordPress itself. Wordfence is not yet officially supported on PHP 8, but will be supported in the near future. <a href="https://www.wordfence.com/blog/2020/11/php-8-what-wordpress-users-need-to-know/">Read More</a>
HTML
				, 'wordfence'), 'post')
, 'php8', array(get_current_user_id()));
			update_user_option(get_current_user_id(), 'wordfence_php8_nag', 1);
		}

		$notificationCount = count(wfNotification::notifications());
		$updatingNotifications = get_site_transient('wordfence_updating_notifications');
		$hidden = ($notificationCount == 0 || $updatingNotifications ? ' wf-hidden' : '');
		$formattedCount = number_format_i18n($notificationCount);
		$dashboardExtra = " <span class='update-plugins wf-menu-badge wf-notification-count-container{$hidden}' title='{$notificationCount}'><span class='update-count wf-notification-count-value'>{$formattedCount}</span></span>";

		add_menu_page('Wordfence', "Wordfence{$dashboardExtra}", 'activate_plugins', 'Wordfence', 'wordfence::menu_dashboard', wfUtils::getBaseURL() . 'images/wordfence-logo.svg');
	}
	
	//These are split to allow our module plugins to insert their menu item(s) at any point in the hierarchy
	public static function admin_menus_20() {
		add_submenu_page("Wordfence", __("Wordfence Dashboard", 'wordfence'), __("Dashboard", 'wordfence'), "activate_plugins", "Wordfence", 'wordfence::menu_dashboard');
	}
	
	public static function admin_menus_30() {
		add_submenu_page("Wordfence", __("Firewall", 'wordfence'), __("Firewall", 'wordfence'), "activate_plugins", "WordfenceWAF", 'wordfence::menu_firewall');
		if (wfConfig::get('displayTopLevelBlocking')) {
			add_submenu_page("Wordfence", __("Blocking", 'wordfence'), __("Blocking", 'wordfence'), "activate_plugins", "WordfenceBlocking", 'wordfence::menu_blocking');
		}
	}
	
	public static function admin_menus_40() {
		add_submenu_page("Wordfence", __("Scan", 'wordfence'), __("Scan", 'wordfence'), "activate_plugins", "WordfenceScan", 'wordfence::menu_scan');
	}
	
	public static function admin_menus_50() {
		add_submenu_page('Wordfence', __('Tools', 'wordfence'), __('Tools', 'wordfence'), 'activate_plugins', 'WordfenceTools', 'wordfence::menu_tools');
		if (wfConfig::get('displayTopLevelLiveTraffic')) {
			add_submenu_page("Wordfence", __("Live Traffic", 'wordfence'), __("Live Traffic", 'wordfence'), "activate_plugins", "WordfenceLiveTraffic", 'wordfence::menu_tools');
		}
	}
	
	public static function admin_menus_60() {
		if (wfConfig::get('displayTopLevelOptions')) {
			add_submenu_page("Wordfence", __("All Options", 'wordfence'), __("All Options", 'wordfence'), "activate_plugins", "WordfenceOptions", 'wordfence::menu_options');
		}
	}
	
	public static function admin_menus_70() {
		add_submenu_page('Wordfence', __('Help', 'wordfence'), __('Help', 'wordfence'), 'activate_plugins', 'WordfenceSupport', 'wordfence::menu_support');
	}
	
	public static function admin_menus_80() {
		if (wfCentral::isSupported()) {
			add_submenu_page(null, __('Wordfence Central', 'wordfence'), __('Wordfence Central', 'wordfence'), 'activate_plugins', 'WordfenceCentral', 'wordfence::menu_wordfence_central');
		}
	}
	
	public static function admin_menus_90() {
		if (wfConfig::get('isPaid')) {
			add_submenu_page("Wordfence", __("Protect More Sites", 'wordfence'), "<strong id=\"wfMenuCallout\" style=\"color: #FCB214;\">" . __("Protect More Sites", 'wordfence') . "</strong>", "activate_plugins", "WordfenceProtectMoreSites", 'wordfence::_menu_noop');
		}
		else {
			add_submenu_page("Wordfence", __("Upgrade To Premium", 'wordfence'), "<strong id=\"wfMenuCallout\" style=\"color: #FCB214;\">" . __("Upgrade To Premium", 'wordfence') . "</strong>", "activate_plugins", "WordfenceUpgradeToPremium", 'wordfence::_menu_noop');
		}
		add_filter('clean_url', 'wordfence::_patchWordfenceSubmenuCallout', 10, 3);
	}
	
	public static function _patchWordfenceSubmenuCallout($url, $original_url, $_context){
		if (preg_match('/(?:WordfenceUpgradeToPremium)$/i', $url)) {
			remove_filter('clean_url', 'wordfence::_patchWordfenceSubmenuCallout', 10);
			return 'https://www.wordfence.com/zz11/wordfence-signup/';
		}
		else if (preg_match('/(?:WordfenceProtectMoreSites)$/i', $url)) {
			remove_filter('clean_url', 'wordfence::_patchWordfenceSubmenuCallout', 10);
			return 'https://www.wordfence.com/zz10/sign-in/';
		}
		return $url;
	}
	public static function _menu_noop() {
		//Do nothing
	}
	public static function _retargetWordfenceSubmenuCallout() {
		echo <<<JQUERY
<script type="text/javascript">
jQuery(document).ready(function($) {
	$('#wfMenuCallout').closest('a').attr('target', '_blank').attr('rel', 'noopener noreferrer');
});
</script>
JQUERY;

	}
	public static function admin_bar_menu() {
		global $wp_admin_bar;
		
		if (wfUtils::isAdmin() && wfConfig::get('showAdminBarMenu')) {
			$title = '<div id="wf-adminbar-icon" class="ab-item"></div>';
			$count = count(wfNotification::notifications());
			$sinceCount = count(wfNotification::notifications((int) get_user_meta(get_current_user_id(), 'wordfence-notifications', true)));
			if ($sinceCount > 0) {
				$counter = '<span id="wf-notification-popover" data-toggle="popover" data-trigger="focus" data-content="' .
					esc_attr(/* translators: Number of notifications. */ _n('You have %d new Wordfence notification.', 'You have %d new Wordfence notifications.', $sinceCount, 'wordfence'))
					. '" data-container="body" data-placement="wf-bottom">&nbsp;</span>';
				update_user_meta(get_current_user_id(), 'wordfence-notifications', time());
			}
			else {
				$counter = ' ';
			}
			$badge = '<div class="wp-core-ui wp-ui-notification wf-notification-counter wf-notification-count-container' . ($count == 0 ? ' wf-hidden' : '') . '"><span class="wf-count wf-notification-count-value">' . $count . '</span></div>';
			$counter .= $badge;
			
			$wp_admin_bar->add_menu( array(
				'id'    => 'wordfence-menu',
				'title' => $title . $counter,
				'href'  => network_admin_url('admin.php?page=Wordfence'),
			));
			$wp_admin_bar->add_menu( array(
				'parent' => 'wordfence-menu',
				'id'     => 'wordfence-notifications',
				'title'  => '<div id="wordfence-notifications-display" class="wf-adminbar-submenu-title">' . __('Notifications', 'wordfence') . '</div>' . $badge,
				'href'   => network_admin_url('admin.php?page=Wordfence'),
			));
			$wp_admin_bar->add_menu( array(
				'parent' => 'wordfence-menu',
				'id'     => 'wordfence-javascripterror',
				'title'  => '<div id="wordfence-javascripterror-display" class="wf-adminbar-submenu-title">' . __('JavaScript Errors', 'wordfence') . '</div><div class="wf-adminbar-status wf-adminbar-status-good">&bullet;</div>',
				'href'   => 'javascript:void(0)',
			));
			$wp_admin_bar->add_menu( array(
				'parent' => 'wordfence-menu',
				'id'     => 'wordfence-malwareurl',
				'title'  => '<div id="wordfence-malwareurl-display' . (is_admin() ? '-skip' : '') . '" class="wf-adminbar-submenu-title">' . __('Malware URLs', 'wordfence') . '</div><div class="wf-adminbar-status wf-adminbar-status-neutral">&bullet;</div>',
				'href'   => network_admin_url('admin.php?page=WordfenceScan'),
			));
		}
	}
	public static function menu_tools() {
		wp_enqueue_style('wordfence-select2-css');
		wp_enqueue_script('wordfence-select2-js');

		$subpage = filter_input(INPUT_GET, 'subpage', FILTER_SANITIZE_STRING);
		switch ($subpage) {
			case 'livetraffic':
				$content = self::_menu_tools_livetraffic();
				break;

			case 'whois':
				$content = self::_menu_tools_whois();
				break;

			case 'diagnostics':
				$content = self::_menu_tools_diagnostics();
				break;

			case 'importexport':
				$content = self::_menu_tools_importexport();
				break;

			// case 'twofactor':
			default:
				if (wfCredentialsController::allowLegacy2FA()) {
					$subpage = 'twofactor';
					$content = self::_menu_tools_twofactor();
				}
				else {
					$subpage = 'livetraffic';
					$content = self::_menu_tools_livetraffic();
				}
		}
		require(dirname(__FILE__) . '/menu_tools.php');
	}
	
	private static function _menu_tools_livetraffic() {
		wp_enqueue_style('wordfence-jquery-ui-css', wfUtils::getBaseURL() . wfUtils::versionedAsset('css/jquery-ui.min.css'), array(), WORDFENCE_VERSION);
		wp_enqueue_style('wordfence-jquery-ui-structure-css', wfUtils::getBaseURL() . wfUtils::versionedAsset('css/jquery-ui.structure.min.css'), array(), WORDFENCE_VERSION);
		wp_enqueue_style('wordfence-jquery-ui-theme-css', wfUtils::getBaseURL() . wfUtils::versionedAsset('css/jquery-ui.theme.min.css'), array(), WORDFENCE_VERSION);
		wp_enqueue_style('wordfence-jquery-ui-timepicker-css', wfUtils::getBaseURL() . wfUtils::versionedAsset('css/jquery-ui-timepicker-addon.css'), array(), WORDFENCE_VERSION);
		
		wp_enqueue_script('wordfence-timepicker-js', wfUtils::getBaseURL() . wfUtils::versionedAsset('js/jquery-ui-timepicker-addon.js'), array('jquery', 'jquery-ui-datepicker', 'jquery-ui-slider'), WORDFENCE_VERSION);
		wp_enqueue_script('wordfence-knockout-js', wfUtils::getBaseURL() . wfUtils::versionedAsset('js/knockout-3.3.0.js'), array(), WORDFENCE_VERSION);
		wp_enqueue_script('wordfence-live-traffic-js', wfUtils::getBaseURL() . wfUtils::versionedAsset('js/admin.liveTraffic.js'), array('jquery', 'jquery-ui-tooltip'), WORDFENCE_VERSION);
		
		ob_start();
		require(dirname(__FILE__) . '/menu_tools_livetraffic.php');
		$content = ob_get_clean();
		return $content;
	}
	
	private static function _menu_tools_whois() {
		ob_start();
		require(dirname(__FILE__) . '/menu_tools_whois.php');
		$content = ob_get_clean();
		return $content;
	}
	
	private static function _menu_tools_diagnostics() {
		$emailForm = true;
		$inEmail = false;
		ob_start();
		require(dirname(__FILE__) . '/menu_tools_diagnostic.php');
		$content = ob_get_clean();
		return $content;
	}
	
	private static function _menu_tools_importexport() {
		ob_start();
		require(dirname(__FILE__) . '/menu_tools_importExport.php');
		$content = ob_get_clean();
		return $content;
	}
	
	private static function _menu_tools_twofactor() {
		ob_start();
		require(dirname(__FILE__) . '/menu_tools_twoFactor.php');
		$content = ob_get_clean();
		return $content;
	}
	
	public static function menu_options() {
		wp_enqueue_style('wordfence-jquery-ui-css', wfUtils::getBaseURL() . wfUtils::versionedAsset('css/jquery-ui.min.css'), array(), WORDFENCE_VERSION);
		wp_enqueue_style('wordfence-jquery-ui-structure-css', wfUtils::getBaseURL() . wfUtils::versionedAsset('css/jquery-ui.structure.min.css'), array(), WORDFENCE_VERSION);
		wp_enqueue_style('wordfence-jquery-ui-theme-css', wfUtils::getBaseURL() . wfUtils::versionedAsset('css/jquery-ui.theme.min.css'), array(), WORDFENCE_VERSION);
		wp_enqueue_style('wordfence-jquery-ui-timepicker-css', wfUtils::getBaseURL() . wfUtils::versionedAsset('css/jquery-ui-timepicker-addon.css'), array(), WORDFENCE_VERSION);
		wp_enqueue_style('wordfence-select2-css');
		
		wp_enqueue_script('wordfence-timepicker-js', wfUtils::getBaseURL() . wfUtils::versionedAsset('js/jquery-ui-timepicker-addon.js'), array('jquery', 'jquery-ui-datepicker', 'jquery-ui-slider'), WORDFENCE_VERSION);
		wp_enqueue_script('wordfence-select2-js');
		
		try {
			$wafData = self::_getWAFData();
		}
		catch (wfWAFStorageFileConfigException $e) {
			// We don't have anywhere to write files in this scenario. Let's notify the user to update the permissions.
			$wafData = array(
				'learningMode' => false,
				'rules' => array(),
				'whitelistedURLParams' => array(),
				'disabledRules' => array(),
				'isPaid' => (bool) wfConfig::get('isPaid', 0),
			);
			$logPath = str_replace(ABSPATH, '~/', WFWAF_LOG_PATH);
			if (function_exists('network_admin_url') && is_multisite()) {
				$wafMenuURL = network_admin_url('admin.php?page=WordfenceWAF&wafconfigrebuild=1');
			} else {
				$wafMenuURL = admin_url('admin.php?page=WordfenceWAF&wafconfigrebuild=1');
			}
			$wafMenuURL = add_query_arg(array(
				'waf-nonce' => wp_create_nonce('wafconfigrebuild'),
			), $wafMenuURL);
			$storageExceptionMessage = $e->getMessage() . ' ' . sprintf(__('<a href="%s">Click here</a> to rebuild the configuration file.', 'wordfence'), esc_url($wafMenuURL));
		} catch (wfWAFStorageFileException $e) {
			// We don't have anywhere to write files in this scenario. Let's notify the user to update the permissions.
			$wafData = array(
				'learningMode' => false,
				'rules' => array(),
				'whitelistedURLParams' => array(),
				'disabledRules' => array(),
				'isPaid' => (bool) wfConfig::get('isPaid', 0),
			);
			$logPath = str_replace(ABSPATH, '~/', WFWAF_LOG_PATH);
			$storageExceptionMessage = sprintf(/* translators: File path. */ __('We were unable to write to %s which the WAF uses for storage. Please update permissions on the parent directory so the web server can write to it.', 'wordfence'), $logPath);
		} catch (wfWAFStorageEngineMySQLiException $e) {
			$wafData = array(
				'learningMode' => false,
				'rules' => array(),
				'whitelistedURLParams' => array(),
				'disabledRules' => array(),
				'isPaid' => (bool) wfConfig::get('isPaid', 0),
			);
			$logPath = null;
			$storageExceptionMessage = __('An error occured when fetching the WAF configuration from the database.', 'wordfence') . ' <pre>' . esc_html($e->getMessage()) . '</pre>';
		}
		
		require(dirname(__FILE__) . '/menu_options.php');
	}
	
	public static function menu_blocking() {
		// Do nothing -- this action is forwarded in admin_init
	}

	public static function menu_firewall() {
		wp_enqueue_style('wordfence-jquery-ui-css', wfUtils::getBaseURL() . wfUtils::versionedAsset('css/jquery-ui.min.css'), array(), WORDFENCE_VERSION);
		wp_enqueue_style('wordfence-jquery-ui-structure-css', wfUtils::getBaseURL() . wfUtils::versionedAsset('css/jquery-ui.structure.min.css'), array(), WORDFENCE_VERSION);
		wp_enqueue_style('wordfence-jquery-ui-theme-css', wfUtils::getBaseURL() . wfUtils::versionedAsset('css/jquery-ui.theme.min.css'), array(), WORDFENCE_VERSION);
		wp_enqueue_style('wordfence-jquery-ui-timepicker-css', wfUtils::getBaseURL() . wfUtils::versionedAsset('css/jquery-ui-timepicker-addon.css'), array(), WORDFENCE_VERSION);
		wp_enqueue_style('wordfence-select2-css');

		wp_enqueue_script('wordfence-timepicker-js', wfUtils::getBaseURL() . wfUtils::versionedAsset('js/jquery-ui-timepicker-addon.js'), array('jquery', 'jquery-ui-datepicker', 'jquery-ui-slider'), WORDFENCE_VERSION);
		wp_enqueue_script('wordfence-select2-js');
		wp_enqueue_script('chart-js');

		try {
			$wafData = self::_getWAFData();
		} catch (wfWAFStorageFileConfigException $e) {
			// We don't have anywhere to write files in this scenario. Let's notify the user to update the permissions.
			$wafData = array(
				'learningMode' => false,
				'rules' => array(),
				'whitelistedURLParams' => array(),
				'disabledRules' => array(),
				'isPaid' => (bool) wfConfig::get('isPaid', 0),
			);
			
			$logPath = str_replace(ABSPATH, '~/', WFWAF_LOG_PATH);
			if (function_exists('network_admin_url') && is_multisite()) {
				$wafMenuURL = network_admin_url('admin.php?page=WordfenceWAF&wafconfigrebuild=1');
			} else {
				$wafMenuURL = admin_url('admin.php?page=WordfenceWAF&wafconfigrebuild=1');
			}
			$wafMenuURL = add_query_arg(array(
				'waf-nonce' => wp_create_nonce('wafconfigrebuild'),
			), $wafMenuURL);
			$storageExceptionMessage = $e->getMessage() . ' ' . sprintf(/* translators: WordPress admin panel URL. */ __('<a href="%s">Click here</a> to rebuild the configuration file.', 'wordfence'), esc_url($wafMenuURL));
		} catch (wfWAFStorageFileException $e) {
			// We don't have anywhere to write files in this scenario. Let's notify the user to update the permissions.
			$wafData = array(
				'learningMode' => false,
				'rules' => array(),
				'whitelistedURLParams' => array(),
				'disabledRules' => array(),
				'isPaid' => (bool) wfConfig::get('isPaid', 0),
			);
			$logPath = str_replace(ABSPATH, '~/', WFWAF_LOG_PATH);
			$storageExceptionMessage = sprintf(/* translators: File path. */ __('We were unable to write to %s which the WAF uses for storage. Please update permissions on the parent directory so the web server can write to it.', 'wordfence'), $logPath);
		} catch (wfWAFStorageEngineMySQLiException $e) {
			$wafData = array(
				'learningMode' => false,
				'rules' => array(),
				'whitelistedURLParams' => array(),
				'disabledRules' => array(),
				'isPaid' => (bool) wfConfig::get('isPaid', 0),
			);
			$logPath = null;
			$storageExceptionMessage = __('An error occured when fetching the WAF configuration from the database.', 'wordfence') . ' <pre>' . esc_html($e->getMessage()) . '</pre>';
		}
		
		if (isset($_GET['subpage']) && $_GET['subpage'] == 'waf_options') {
			require(dirname(__FILE__) . '/menu_firewall_waf_options.php');
		}
		else if (isset($_GET['subpage']) && $_GET['subpage'] == 'blocking_options') {
			require(dirname(__FILE__) . '/menu_firewall_blocking_options.php');
		}
		else {
			require(dirname(__FILE__) . '/menu_firewall.php');
		}
	}

	public static function liveTrafficW3TCWarning() {
		echo self::cachingWarning("W3 Total Cache");
	}
	public static function liveTrafficSuperCacheWarning(){
		echo self::cachingWarning("WP Super Cache");
	}
	public static function cachingWarning($plugin){
		return '<div id="wordfenceConfigWarning" class="error fade"><p><strong>' .
			sprintf(/* translators: Plugin name. */ __('The Wordfence Live Traffic feature has been disabled because you have %s active which is not compatible with Wordfence Live Traffic.', 'wordfence'), $plugin)
			. '</strong> ' .
			sprintf(/* translators: 1. Plugin name. */ __('If you want to reenable Wordfence Live Traffic, you need to deactivate %1$s and then go to the Wordfence options page and reenable Live Traffic there. Wordfence does work with %1$s, however Live Traffic will be disabled and the Wordfence firewall will also count less hits per visitor because of the %1$s caching function. All other functions should work correctly.', 'wordfence'), $plugin)
			. '</p></div>';
	}
	public static function menu_dashboard() {
		wp_enqueue_style('wordfence-select2-css');
		wp_enqueue_script('wordfence-select2-js');
		wp_enqueue_script('chart-js');
		
		if (wfConfig::get('keyType') == wfAPI::KEY_TYPE_PAID_EXPIRED || (wfConfig::get('keyType') == wfAPI::KEY_TYPE_PAID_CURRENT && wfConfig::get('keyExpDays') < 30)) {
			$api = new wfAPI(wfConfig::get('apiKey', ''), wfUtils::getWPVersion());
			try {
				$api->call('check_api_key', array(), array(), false, 2);
			}
			catch (Exception $e) {
				//Do nothing
			}
		}
		
		if (isset($_GET['subpage']) && $_GET['subpage'] == 'global_options') {
			require(dirname(__FILE__) . '/menu_dashboard_options.php');
			return;
		}
		
		require(dirname(__FILE__) . '/menu_dashboard.php');
	}
	public static function menu_scan() {
		wp_enqueue_style('wordfence-select2-css');
		wp_enqueue_script('wordfence-select2-js');
		
		if (isset($_GET['subpage']) && $_GET['subpage'] == 'scan_options') {
			require(dirname(__FILE__) . '/menu_scanner_options.php');
			return;
		}
		else if (isset($_GET['subpage']) && $_GET['subpage'] == 'scan_credentials') {
			require(dirname(__FILE__) . '/menu_scanner_credentials.php');
			return;
		}

		require(dirname(__FILE__) . '/menu_scanner.php');
	}
	
	public static function menu_support() {
		wp_enqueue_style('wordfence-select2-css');
		wp_enqueue_script('wordfence-select2-js');
		
		require(dirname(__FILE__) . '/menu_support.php');
	}

	public static function menu_wordfence_central() {
		wfConfig::set('showWfCentralUI', 1);

		wp_enqueue_style('wordfence-select2-css');
		wp_enqueue_script('wordfence-select2-js');

		require(dirname(__FILE__) . '/menu_wordfence_central.php');
	}

	public static function fsActionRestoreFileCallback() {
		$issueID = filter_input(INPUT_GET, 'issueID', FILTER_SANITIZE_NUMBER_INT);
		$response = self::ajax_restoreFile_callback($issueID);
		if (!empty($response['ok'])) {
			$result = sprintf('<p>' . /* translators: File path. */ __('The file <code>%s</code> was restored successfully.', 'wordfence') . '</p>',
				esc_html(strpos($response['file'], ABSPATH) === 0 ? substr($response['file'], strlen(ABSPATH) + 1) : $response['file']));
		} else if (!empty($response['cerrorMessage'])) {
			$result = sprintf('<div class="wfSummaryErr">%s</div>', esc_html($response['cerrorMessage']));
		} else {
			$result = '<div class="wfSummaryErr">' . __('There was an error restoring the file.', 'wordfence') . '</div>';
		}
		printf(<<<HTML
<br>
%s
<p><a href="%s">%s</a></p>
HTML
			,
			$result,
			esc_url(network_admin_url('admin.php?page=WordfenceScan')),
			__('Return to scan results', 'wordfence')
		);
		wfScanEngine::refreshScanNotification();
	}

	public static function fsActionDeleteFileCallback() {
		$issueID = filter_input(INPUT_GET, 'issueID', FILTER_SANITIZE_NUMBER_INT);
		$response = self::ajax_deleteFile_callback($issueID);
		if (!empty($response['ok'])) {
			$result = sprintf('<p>' . /* translators: File path. */ __('The file <code>%s</code> was deleted successfully.', 'wordfence') . '</p>', esc_html($response['file']));
		} else if (!empty($response['errorMessage'])) {
			$result = sprintf('<div class="wfSummaryErr">%s</div>', esc_html($response['errorMessage']));
		} else {
			$result = '<div class="wfSummaryErr">' . __('There was an error deleting the file.', 'wordfence') . '</div>';
		}
		printf(<<<HTML
<br>
%s
<p><a href="%s">%s</a></p>
HTML
			,
			$result,
			esc_url(network_admin_url('admin.php?page=WordfenceScan')),
			__('Return to scan results', 'wordfence')
		);
		wfScanEngine::refreshScanNotification();
	}

	public static function status($level /* 1 has highest visibility */, $type /* info|error */, $msg){
		if($level > 3 && $level < 10 && (! self::isDebugOn())){ //level 10 and higher is for summary messages
			return false;
		}
		if($type != 'info' && $type != 'error'){ error_log("Invalid status type: $type"); return; }
		if(self::$printStatus){
			echo "STATUS: $level : $type : ".esc_html($msg)."\n";
		} else {
			self::getLog()->addStatus($level, $type, $msg);
		}
	}
	public static function profileUpdateAction($userID, $newDat = false){
		if(! $newDat){ return; }
		if(wfConfig::get('other_pwStrengthOnUpdate')){
			$oldDat = get_userdata($userID);
			if($newDat->user_pass != $oldDat->user_pass){
				$wf = new wfScanEngine();
				$wf->scanUserPassword($userID);
				$wf->emailNewIssues();
			}
		}
	}

	public static function replaceVersion($url) {
		return preg_replace_callback("/([&;\?]ver)=(.+?)(&|$)/", "wordfence::replaceVersionCallback", $url);
	}

	public static function replaceVersionCallback($matches) {
		global $wp_version;
		return $matches[1] . '=' . ($wp_version === $matches[2] ? wp_hash($matches[2]) : $matches[2]) . $matches[3];
	}

	public static function genFilter($gen, $type){
		if(wfConfig::get('other_hideWPVersion')){
			return '';
		} else {
			return $gen;
		}
	}
	public static function getMyHomeURL(){
		return wfUtils::wpAdminURL('admin.php?page=Wordfence');
	}
	public static function getMyOptionsURL(){
		return wfUtils::wpAdminURL('admin.php?page=Wordfence&subpage=global_options');
	}

	public static function alert($subject, $alertMsg, $IP) {
		wfConfig::inc('totalAlertsSent');
		$emails = wfConfig::getAlertEmails();
		if (sizeof($emails) < 1) { return; }

		$IPMsg = "";
		if ($IP) {
			$IPMsg = sprintf(/* translators: IP address. */ __("User IP: %s\n", 'wordfence'), $IP);
			$reverse = wfUtils::reverseLookup($IP);
			if ($reverse) {
				$IPMsg .= sprintf(/* translators: Domain name. */ __("User hostname: %s\n", 'wordfence'), $reverse);
			}
			$userLoc = wfUtils::getIPGeo($IP);
			if ($userLoc) {
				$IPMsg .= __('User location: ', 'wordfence');
				if ($userLoc['city']) {
					$IPMsg .= $userLoc['city'] . ', ';
				}
				if ($userLoc['region'] && wfUtils::shouldDisplayRegion($userLoc['countryName'])) {
					$IPMsg .= $userLoc['region'] . ', ';
				}
				$IPMsg .= $userLoc['countryName'] . "\n";
			}
		}
		
		$content = wfUtils::tmpl('email_genericAlert.php', array(
			'isPaid' => wfConfig::get('isPaid'),
			'subject' => $subject,
			'blogName' => get_bloginfo('name', 'raw'),
			'adminURL' => get_admin_url(),
			'alertMsg' => $alertMsg,
			'IPMsg' => $IPMsg,
			'date' => wfUtils::localHumanDate(),
			'myHomeURL' => self::getMyHomeURL(),
			'myOptionsURL' => self::getMyOptionsURL()
			));
		$shortSiteURL = preg_replace('/^https?:\/\//i', '', site_url());
		$subject = "[Wordfence Alert] $shortSiteURL " . $subject;

		$sendMax = wfConfig::get('alert_maxHourly', 0);
		if($sendMax > 0){
			$sendArr = wfConfig::get_ser('alertFreqTrack', array());
			if(! is_array($sendArr)){
				$sendArr = array();
			}
			$minuteTime = floor(time() / 60);
			$totalSent = 0;
			for($i = $minuteTime; $i > $minuteTime - 60; $i--){
				$totalSent += isset($sendArr[$i]) ? $sendArr[$i] : 0;
			}
			if($totalSent >= $sendMax){
				return;
			}
			$sendArr[$minuteTime] = isset($sendArr[$minuteTime]) ? $sendArr[$minuteTime] + 1 : 1;
			wfConfig::set_ser('alertFreqTrack', $sendArr);
		}
		//Prevent duplicate emails within 1 hour:
		$hash = md5(implode(',', $emails) . ':' . $subject . ':' . $alertMsg . ':' . $IP); //Hex
		$lastHash = wfConfig::get('lastEmailHash', false);
		if($lastHash){
			$lastHashDat = explode(':', $lastHash); //[time, hash]
			if(time() - $lastHashDat[0] < 3600){
				if($lastHashDat[1] == $hash){
					return; //Don't send because this email is identical to the previous email which was sent within the last hour.
				}
			}
		}
		wfConfig::set('lastEmailHash', time() . ':' . $hash);
		foreach ($emails as $email) {
			$uniqueContent = $content . "\n\n" . sprintf(/* translators: WordPress admin panel URL. */ __('No longer an administrator for this site? Click here to stop receiving security alerts: %s', 'wordfence'), wfUtils::getSiteBaseURL() . '?_wfsf=removeAlertEmail&jwt=' . wfUtils::generateJWT(array('email' => $email)));
			wp_mail($email, $subject, $uniqueContent);
		}
	}
	public static function getLog(){
		if(! self::$wfLog){
			$wfLog = wfLog::shared();
			self::$wfLog = $wfLog;
		}
		return self::$wfLog;
	}
	public static function wfSchemaExists(){
		global $wpdb;
		$exists = $wpdb->get_col($wpdb->prepare(<<<SQL
SELECT TABLE_NAME FROM information_schema.TABLES
WHERE TABLE_SCHEMA=DATABASE()
AND TABLE_NAME=%s
SQL
			, wfDB::networkTable('wfConfig')));
		return $exists ? true : false;
	}
	public static function isDebugOn(){
		if(is_null(self::$debugOn)){
			if(wfConfig::get('debugOn')){
				self::$debugOn = true;
			} else {
				self::$debugOn = false;
			}
		}
		return self::$debugOn;
	}
	//PUBLIC API
	public static function doNotCache(){ //Call this to prevent Wordfence from caching the current page.
		wfCache::doNotCache();
		return true;
	}
	public static function whitelistIP($IP){ //IP as a string in dotted quad notation e.g. '10.11.12.13'
		$IP = trim($IP);
		$user_range = new wfUserIPRange($IP);
		if (!$user_range->isValidRange()) {
			throw new Exception(__("The IP you provided must be in dotted quad notation or use ranges with square brackets. e.g. 10.11.12.13 or 10.11.12.[1-50]", 'wordfence'));
		}
		$whites = wfConfig::get('whitelisted', '');
		$arr = explode(',', $whites);
		$arr2 = array();
		foreach($arr as $e){
			if($e == $IP){
				return false;
			}
			$arr2[] = trim($e);
		}
		$arr2[] = $IP;
		wfConfig::set('whitelisted', implode(',', $arr2));
		return true;
	}

	public static function ajax_email_summary_email_address_debug_callback() {
		$email = !empty($_REQUEST['email']) ? $_REQUEST['email'] : null;
		if (!wfUtils::isValidEmail($email)) {
			return array('result' => __('Invalid email address provided', 'wordfence'));
		}
		
		$report = new wfActivityReport();
		return $report->sendReportViaEmail($email) ?
			array('ok' => 1, 'result' => __('Test email sent successfully', 'wordfence')) :
			array('result' => __("Test email failed to send", 'wordfence'));
	}

	public static function addDashboardWidget() {
		if (wfUtils::isAdmin() && (is_network_admin() || !is_multisite()) && wfConfig::get('email_summary_dashboard_widget_enabled')) {
			wp_enqueue_style('wordfence-activity-report-widget', wfUtils::getBaseURL() . wfUtils::versionedAsset('css/activity-report-widget.css'), '', WORDFENCE_VERSION);
			$report_date_range = 'week';
			switch (wfConfig::get('email_summary_interval')) {
				case 'daily':
					$report_date_range = 'day';
					break;

				case 'monthly':
					$report_date_range = 'month';
					break;
			}
			wp_add_dashboard_widget(
				'wordfence_activity_report_widget',
				sprintf(/* translators: Localized date range. */ __('Wordfence activity in the past %s', 'wordfence'), $report_date_range),
				array('wfActivityReport', 'outputDashboardWidget')
			);
		}
	}

	/**
	 * @return bool
	 */
	public static function hasGDLimitLoginsMUPlugin() {
		return defined('GD_SYSTEM_PLUGIN_DIR') && file_exists(GD_SYSTEM_PLUGIN_DIR . 'limit-login-attempts/limit-login-attempts.php')
			&& defined('LIMIT_LOGIN_DIRECT_ADDR');
	}

	/**
	 * @param string $content
	 * @return string
	 */
	public static function fixGDLimitLoginsErrors($content) {
		if (self::$authError) {
			$content = str_replace(__('<strong>ERROR</strong>: Incorrect username or password.', 'limit-login-attempts') . "<br />\n", '', $content);
			$content .= '<br />' . self::$authError->get_error_message();
		}
		return $content;
	}

	/**
	 * @return array
	 */
	public static function ajax_deleteAdminUser_callback() {
		/** @var wpdb $wpdb */
		global $wpdb;
		$issueID = absint(!empty($_POST['issueID']) ? $_POST['issueID'] : 0);
		$wfIssues = new wfIssues();
		$issue = $wfIssues->getIssueByID($issueID);
		if (!$issue) {
			return array('errorMsg' => __("We could not find that issue in our database.", 'wordfence'));
		}
		$data = $issue['data'];
		if (empty($data['userID'])) {
			return array('errorMsg' => __("We could not find that user in the database.", 'wordfence'));
		}
		$user = new WP_User($data['userID']);
		if (!$user->exists()) {
			return array('errorMsg' => __("We could not find that user in the database.", 'wordfence'));
		}
		$userLogin = $user->user_login;
		if (is_multisite() && strcasecmp($user->user_email, get_site_option('admin_email')) === 0) {
			return array('errorMsg' => __("This user's email is the network admin email. It will need to be changed before deleting this user.", 'wordfence'));
		}
		if (is_multisite()) {
			revoke_super_admin($data['userID']);
		}
		wp_delete_user($data['userID']);
		if (is_multisite()) {
			$wpdb->delete($wpdb->users, array('ID' => $data['userID']));
		}
		$wfIssues->deleteIssue($issueID);
		wfScanEngine::refreshScanNotification($wfIssues);

		return array(
			'ok'         => 1,
			'user_login' => $userLogin,
		);
	}

	public static function ajax_revokeAdminUser_callback() {
		$issueID = absint(!empty($_POST['issueID']) ? $_POST['issueID'] : 0);
		$wfIssues = new wfIssues();
		$issue = $wfIssues->getIssueByID($issueID);
		if (!$issue) {
			return array('errorMsg' => __("We could not find that issue in our database.", 'wordfence'));
		}
		$data = $issue['data'];
		if (empty($data['userID'])) {
			return array('errorMsg' => __("We could not find that user in the database.", 'wordfence'));
		}
		$user = new WP_User($data['userID']);
		$userLogin = $user->user_login;
		wp_revoke_user($data['userID']);
		if (is_multisite()) {
			revoke_super_admin($data['userID']);
		}

		$wfIssues->deleteIssue($issueID);
		wfScanEngine::refreshScanNotification($wfIssues);

		return array(
			'ok'         => 1,
			'user_login' => $userLogin,
		);
	}

	/**
	 *
	 */
	public static function ajax_disableDirectoryListing_callback() {
		$issueID = absint($_POST['issueID']);
		$wfIssues = new wfIssues();
		$issue = $wfIssues->getIssueByID($issueID);
		if (!$issue) {
			return array(
				'err'      => 1,
				'errorMsg' => __("We could not find that issue in our database.", 'wordfence'),
			);
		}
		$wfIssues->deleteIssue($issueID);

		$htaccessPath = wfCache::getHtaccessPath();
		if (!$htaccessPath) {
			return array(
				'err'      => 1,
				'errorMsg' => __("Wordfence could not find your .htaccess file.", 'wordfence'),
			);
		}

		$fileContents = file_get_contents($htaccessPath);
		if (file_put_contents($htaccessPath, "# Added by Wordfence " . date('r') . "\nOptions -Indexes\n\n" . $fileContents, LOCK_EX)) {
			$uploadPaths = wp_upload_dir();
			if (!wfScanEngine::isDirectoryListingEnabled($uploadPaths['baseurl'])) {
				return array(
					'ok' => 1,
				);
			} else {
				// Revert any changes done to .htaccess
				file_put_contents($htaccessPath, $fileContents, LOCK_EX);
				return array(
					'err'      => 1,
					'errorMsg' => __("Updating the .htaccess did not fix the issue. You may need to add <code>Options -Indexes</code> to your httpd.conf if using Apache, or find documentation on how to disable directory listing for your web server.", 'wordfence'),
				);
			}
		}
		return array(
			'err'      => 1,
			'errorMsg' => __("There was an error writing to your .htaccess file.", 'wordfence'),
		);
	}

	/**
	 * Modify the query to prevent username enumeration.
	 *
	 * @param array $query_vars
	 * @return array
	 */
	public static function preventAuthorNScans($query_vars) {
		if (wfConfig::get('loginSec_disableAuthorScan') && !is_admin() &&
			!empty($query_vars['author']) && (is_array($query_vars['author']) || is_numeric(preg_replace('/[^0-9]/', '', $query_vars['author']))) &&
			(
				(isset($_GET['author']) && (is_array($_GET['author']) || is_numeric(preg_replace('/[^0-9]/', '', $_GET['author'])))) ||
				(isset($_POST['author']) && (is_array($_POST['author']) || is_numeric(preg_replace('/[^0-9]/', '', $_POST['author']))))
			)
		) {
			global $wp_query;
			$wp_query->set_404();
			status_header(404);
			nocache_headers();
			
			$template = get_404_template();
			if ($template && file_exists($template)) {
				include($template);
			}
			
			exit;
		}
		return $query_vars;
	}

	/**
	 * @param WP_Upgrader $updater
	 * @param array $hook_extra
	 */
	public static function hideReadme($updater, $hook_extra = null) {
		if (wfConfig::get('other_hideWPVersion')) {
			wfUtils::hideReadme();
		}
	}
	
	public static function ajax_saveDisclosureState_callback() {
		if (isset($_POST['name']) && isset($_POST['state'])) {
			$name = preg_replace('/[^a-zA-Z0-9_\-]/', '', $_POST['name']);
			$state = wfUtils::truthyToBoolean($_POST['state']);
			if (!empty($name)) {
				$disclosureStates = wfConfig::get_ser('disclosureStates', array());
				$disclosureStates[$name] = $state;
				wfConfig::set_ser('disclosureStates', $disclosureStates);
				return array('ok' => 1);
			}
		}
		else if (isset($_POST['names']) && isset($_POST['state'])) {
			$rawNames = $_POST['names'];
			if (is_array($rawNames)) {
				$filteredNames = array();
				foreach ($rawNames as $name) {
					$name = preg_replace('/[^a-zA-Z0-9_\-]/', '', $name);
					if (!empty($name)) {
						$filteredNames[] = $name;
					}
				}
				
				$state = wfUtils::truthyToBoolean($_POST['state']);
				if (!empty($filteredNames)) {
					$disclosureStates = wfConfig::get_ser('disclosureStates', array());
					foreach ($filteredNames as $name) {
						$disclosureStates[$name] = $state;
					}
					wfConfig::set_ser('disclosureStates', $disclosureStates);
					return array('ok' => 1);
				}
			}
		}
		
		return array(
			'err'      => 1,
			'errorMsg' => __("Required parameters not sent.", 'wordfence'),
		);
	}

	public static function ajax_saveWAFConfig_callback() {
		if (isset($_POST['wafConfigAction'])) {
			$waf = wfWAF::getInstance();
			if (method_exists($waf, 'isReadOnly') && $waf->isReadOnly()) {
				return array(
					'err'      => 1,
					'errorMsg' => __("The WAF is currently in read-only mode and will not save any configuration changes.", 'wordfence'),
				);
			}
			
			switch ($_POST['wafConfigAction']) {
				case 'config':
					if (!empty($_POST['wafStatus']) && in_array($_POST['wafStatus'], array(wfFirewall::FIREWALL_MODE_DISABLED, wfFirewall::FIREWALL_MODE_LEARNING, wfFirewall::FIREWALL_MODE_ENABLED))) {
						if ($_POST['wafStatus'] == 'learning-mode' && !empty($_POST['learningModeGracePeriodEnabled'])) {
							$gracePeriodEnd = strtotime(isset($_POST['learningModeGracePeriod']) ? $_POST['learningModeGracePeriod'] : '');
							if ($gracePeriodEnd > time()) {
								wfWAF::getInstance()->getStorageEngine()->setConfig('learningModeGracePeriodEnabled', 1);
								wfWAF::getInstance()->getStorageEngine()->setConfig('learningModeGracePeriod', $gracePeriodEnd);
							} else {
								return array(
									'err'      => 1,
									'errorMsg' => __("The grace period end time must be in the future.", 'wordfence'),
								);
							}
						} else {
							wfWAF::getInstance()->getStorageEngine()->setConfig('learningModeGracePeriodEnabled', 0);
							wfWAF::getInstance()->getStorageEngine()->unsetConfig('learningModeGracePeriod');
						}
						wfWAF::getInstance()->getStorageEngine()->setConfig('wafStatus', $_POST['wafStatus']);
						$firewall = new wfFirewall();
						$firewall->syncStatus(true);
					}

					break;

				case 'addWhitelist':
					if (isset($_POST['whitelistedPath']) && isset($_POST['whitelistedParam'])) {
						$path = stripslashes($_POST['whitelistedPath']);
						$paramKey = stripslashes($_POST['whitelistedParam']);
						if (!$path || !$paramKey) {
							break;
						}
						$data = array(
							'timestamp'   => time(),
							'description' => __('Allowlisted via Firewall Options page', 'wordfence'),
							'ip'          => wfUtils::getIP(),
							'disabled'    => empty($_POST['whitelistedEnabled']),
						);
						if (function_exists('get_current_user_id')) {
							$data['userID'] = get_current_user_id();
						}
						wfWAF::getInstance()->whitelistRuleForParam($path, $paramKey, 'all', $data);
					}
					break;

				case 'replaceWhitelist':
					if (
						!empty($_POST['oldWhitelistedPath']) && !empty($_POST['oldWhitelistedParam']) &&
						!empty($_POST['newWhitelistedPath']) && !empty($_POST['newWhitelistedParam'])
					) {
						$oldWhitelistedPath = stripslashes($_POST['oldWhitelistedPath']);
						$oldWhitelistedParam = stripslashes($_POST['oldWhitelistedParam']);

						$newWhitelistedPath = stripslashes($_POST['newWhitelistedPath']);
						$newWhitelistedParam = stripslashes($_POST['newWhitelistedParam']);

						$savedWhitelistedURLParams = (array) wfWAF::getInstance()->getStorageEngine()->getConfig('whitelistedURLParams', null, 'livewaf');
						// These are already base64'd
						$oldKey = $oldWhitelistedPath . '|' . $oldWhitelistedParam;
						$newKey = base64_encode($newWhitelistedPath) . '|' . base64_encode($newWhitelistedParam);
						try {
							$savedWhitelistedURLParams = wfUtils::arrayReplaceKey($savedWhitelistedURLParams, $oldKey, $newKey);
						} catch (Exception $e) {
							error_log("Caught exception from 'wfUtils::arrayReplaceKey' with message: " . $e->getMessage());
						}
						wfWAF::getInstance()->getStorageEngine()->setConfig('whitelistedURLParams', $savedWhitelistedURLParams, 'livewaf');
					}
					break;

				case 'deleteWhitelist':
					if (
						isset($_POST['deletedWhitelistedPath']) && is_string($_POST['deletedWhitelistedPath']) &&
						isset($_POST['deletedWhitelistedParam']) && is_string($_POST['deletedWhitelistedParam'])
					) {
						$deletedWhitelistedPath = stripslashes($_POST['deletedWhitelistedPath']);
						$deletedWhitelistedParam = stripslashes($_POST['deletedWhitelistedParam']);
						$savedWhitelistedURLParams = (array) wfWAF::getInstance()->getStorageEngine()->getConfig('whitelistedURLParams', null, 'livewaf');
						$key = $deletedWhitelistedPath . '|' . $deletedWhitelistedParam;
						unset($savedWhitelistedURLParams[$key]);
						wfWAF::getInstance()->getStorageEngine()->setConfig('whitelistedURLParams', $savedWhitelistedURLParams, 'livewaf');
					}
					break;

				case 'enableWhitelist':
					if (isset($_POST['whitelistedPath']) && isset($_POST['whitelistedParam'])) {
						$path = stripslashes($_POST['whitelistedPath']);
						$paramKey = stripslashes($_POST['whitelistedParam']);
						if (!$path || !$paramKey) {
							break;
						}
						$enabled = !empty($_POST['whitelistedEnabled']);

						$savedWhitelistedURLParams = (array) wfWAF::getInstance()->getStorageEngine()->getConfig('whitelistedURLParams', null, 'livewaf');
						$key = $path . '|' . $paramKey;
						if (array_key_exists($key, $savedWhitelistedURLParams) && is_array($savedWhitelistedURLParams[$key])) {
							foreach ($savedWhitelistedURLParams[$key] as $ruleID => $data) {
								$savedWhitelistedURLParams[$key][$ruleID]['disabled'] = !$enabled;
							}
						}
						wfWAF::getInstance()->getStorageEngine()->setConfig('whitelistedURLParams', $savedWhitelistedURLParams, 'livewaf');
					}
					break;

				case 'enableRule':
					$ruleEnabled = !empty($_POST['ruleEnabled']);
					$ruleID = !empty($_POST['ruleID']) ? (int) $_POST['ruleID'] : false;
					if ($ruleID) {
						$disabledRules = (array) wfWAF::getInstance()->getStorageEngine()->getConfig('disabledRules');
						if ($ruleEnabled) {
							unset($disabledRules[$ruleID]);
						} else {
							$disabledRules[$ruleID] = true;
						}
						wfWAF::getInstance()->getStorageEngine()->setConfig('disabledRules', $disabledRules);
					}
					break;
				case 'disableWAFBlacklistBlocking':
					if (isset($_POST['disableWAFBlacklistBlocking'])) {
						$disableWAFBlacklistBlocking = (int) $_POST['disableWAFBlacklistBlocking'];
						wfWAF::getInstance()->getStorageEngine()->setConfig('disableWAFBlacklistBlocking', $disableWAFBlacklistBlocking);
						if (method_exists(wfWAF::getInstance()->getStorageEngine(), 'purgeIPBlocks')) {
							wfWAF::getInstance()->getStorageEngine()->purgeIPBlocks(wfWAFStorageInterface::IP_BLOCKS_BLACKLIST);
						}
					}
					break;
			}
		}

		return array(
			'success' => true,
			'data'    => self::_getWAFData(),
		);
	}

	public static function ajax_updateWAFRules_callback() {
		try {
			$event = new wfWAFCronFetchRulesEvent(time() - 2, true);
			$event->setWaf(wfWAF::getInstance());
			$success = $event->fire();
			$failureReason = false;
			if (!$success && method_exists($event, 'getResponse')) {
				$response = $event->getResponse();
				if ($response === false) {
					$failureReason = wfFirewall::UPDATE_FAILURE_UNREACHABLE;
				}
				else {
					$jsonData = @json_decode($response->getBody(), true);
					if (isset($jsonData['errorMessage']) && strpos($jsonData['errorMessage'], 'rate limit') !== false) {
						$failureReason = wfFirewall::UPDATE_FAILURE_RATELIMIT;
					}
					else if (isset($jsonData['data']['signature'])) {
						$failureReason = wfFirewall::UPDATE_FAILURE_FILESYSTEM;
					}
				}
			}
			
			return self::_getWAFData($success, $failureReason);
		}
		catch (Exception $e) {
			$wafData = array(
				'learningMode' => false,
				'rules' => array(),
				'whitelistedURLParams' => array(),
				'disabledRules' => array(),
				'isPaid' => (bool) wfConfig::get('isPaid', 0),
			);
			
			return $wafData;
		}
	}

	public static function ajax_loadLiveTraffic_callback() {
		$return = array();

		$filters = new wfLiveTrafficQueryFilterCollection();
		$query = new wfLiveTrafficQuery(self::getLog());
		$query->setFilters($filters);
		if (array_key_exists('groupby', $_REQUEST)) {
			$param = $_REQUEST['groupby'];
			if ($param === 'type') {
				$param = 'jsRun';
			}
			$query->setGroupBy(new wfLiveTrafficQueryGroupBy($query, $param));
		}
		$query->setLimit(isset($_REQUEST['limit']) ? absint($_REQUEST['limit']) : 20);
		$query->setOffset(isset($_REQUEST['offset']) ? absint($_REQUEST['offset']) : 0);

		if (!empty($_REQUEST['since'])) {
			$query->setStartDate($_REQUEST['since']);
		} else if (!empty($_REQUEST['startDate'])) {
			$query->setStartDate(is_numeric($_REQUEST['startDate']) ? $_REQUEST['startDate'] : strtotime($_REQUEST['startDate']));
		}

		if (!empty($_REQUEST['endDate'])) {
			$query->setEndDate(is_numeric($_REQUEST['endDate']) ? $_REQUEST['endDate'] : strtotime($_REQUEST['endDate']));
		}

		if (
			array_key_exists('param', $_REQUEST) && is_array($_REQUEST['param']) &&
			array_key_exists('operator', $_REQUEST) && is_array($_REQUEST['operator']) &&
			array_key_exists('value', $_REQUEST) && is_array($_REQUEST['value'])
		) {
			for ($i = 0; $i < count($_REQUEST['param']); $i++) {
				if (
					array_key_exists($i, $_REQUEST['param']) &&
					array_key_exists($i, $_REQUEST['operator']) &&
					array_key_exists($i, $_REQUEST['value'])
				) {
					$param = $_REQUEST['param'][$i];
					$operator = $_REQUEST['operator'][$i];
					$value = $_REQUEST['value'][$i];

					switch (strtolower($param)) {
						case 'type':
							$param = 'jsRun';
							$value = strtolower($value) === 'human' ? 1 : 0;
							break;
						case 'ip':
							$ip = $value;
							
							if (strpos($ip, '*') !== false) { //If the IP contains a *, treat it as a wildcard for that segment and silently adjust the rule
								if (preg_match('/^(?:(?:\d{1,3}|\*)(?:\.|$)){2,4}/', $ip)) { //IPv4
									$value = array('00', '00', '00', '00', '00', '00', '00', '00', '00', '00', 'FF', 'FF');
									$octets = explode('.', $ip);
									foreach ($octets as $o)
									{
										if (strpos($o, '*') !== false) {
											$value[] = '..';
										}
										else {
											$value[] = strtoupper(str_pad(dechex($o), 2, '0', STR_PAD_LEFT));
										}
									}
									$value = '^' . implode('', array_pad($value, 16, '..')) . '$';
									$operator = ($operator == '!=' ? 'hnotregexp' : 'hregexp');
								}
								else if (!empty($ip) && preg_match('/^((?:[\da-f*]{1,4}(?::|)){0,8})(::)?((?:[\da-f*]{1,4}(?::|)){0,8})$/i', $ip)) { //IPv6
									if ($ip === '::') {
										$value = '^' . str_repeat('00', 16) . '$';
									}
									else {
										$colon_count = substr_count($ip, ':');
										$dbl_colon_pos = strpos($ip, '::');
										if ($dbl_colon_pos !== false) {
											$ip = str_replace('::', str_repeat(':0000', (($dbl_colon_pos === 0 || $dbl_colon_pos === strlen($ip) - 2) ? 9 : 8) - $colon_count) . ':', $ip);
											$ip = trim($ip, ':');
										}

										$ip_groups = explode(':', $ip);
										$value = array();
										foreach ($ip_groups as $ip_group) {
											if (strpos($ip_group, '*') !== false) {
												$value[] = '..';
												$value[] = '..';
											}
											else {
												$ip_group = strtoupper(str_pad($ip_group, 4, '0', STR_PAD_LEFT));
												$value[] = substr($ip_group, 0, 2);
												$value[] = substr($ip_group, -2);
											}
										}

										$value = '^' . implode('', array_pad($value, 16, '..')) . '$';
									}
									$operator = ($operator == '=' ? 'hregexp' : 'hnotregexp');
								}
								else if (preg_match('/^((?:0{1,4}(?::|)){0,5})(::)?ffff:((?:\d{1,3}(?:\.|$)){4})$/i', $ip, $matches)) { //IPv4 mapped IPv6
									$value = array('00', '00', '00', '00', '00', '00', '00', '00', '00', '00', 'FF', 'FF');
									$octets = explode('.', $matches[3]);
									foreach ($octets as $o)
									{
										if (strpos($o, '*') !== false) {
											$value[] = '..';
										}
										else {
											$value[] = strtoupper(str_pad(dechex($o), 2, '0', STR_PAD_LEFT));
										}
									}
									$value = '^' . implode('', array_pad($value, 16, '.')) . '$';
									$operator = ($operator == '=' ? 'hregexp' : 'hnotregexp');
								}
								else {
									$value = false;
								}
							}
							else {
								$value = wfUtils::inet_pton($ip);
							}
							break;
						case 'userid':
							$value = absint($value);
							break;
					}
					if ($operator === 'match' && $param !== 'ip') {
						$value = str_replace('*', '%', $value);
					}
					$filters->addFilter(new wfLiveTrafficQueryFilter($query, $param, $operator, $value));
				}
			}
		}

		try {
			$return['data'] = $query->execute();
			/*if (defined('WP_DEBUG') && WP_DEBUG) {
				$return['sql'] = $query->buildQuery();
			}*/
		} catch (wfLiveTrafficQueryException $e) {
			$return['data'] = array();
			$return['sql'] = $e->getMessage();
		}

		$return['success'] = true;

		return $return;
	}

	public static function ajax_whitelistWAFParamKey_callback() {
		if (class_exists('wfWAF') && $waf = wfWAF::getInstance()) {
			if (isset($_POST['path']) && isset($_POST['paramKey']) && isset($_POST['failedRules'])) {
				$data = array(
					'timestamp'   => time(),
					'description' => __('Allowlisted via Live Traffic', 'wordfence'),
					'source'	  => 'live-traffic',
					'ip'          => wfUtils::getIP(),
				);
				if (function_exists('get_current_user_id')) {
					$data['userID'] = get_current_user_id();
				}
				$waf->whitelistRuleForParam(base64_decode($_POST['path']), base64_decode($_POST['paramKey']),
					$_POST['failedRules'], $data);

				return array(
					'success' => true,
				);
			}
		}
		return false;
	}

	public static function ajax_whitelistBulkDelete_callback() {
		if (class_exists('wfWAF') && $waf = wfWAF::getInstance()) {
			if (!empty($_POST['items']) && ($items = json_decode(stripslashes($_POST['items']), true)) !== false) {
				$whitelist = (array) $waf->getStorageEngine()->getConfig('whitelistedURLParams', null, 'livewaf');
				if (!is_array($whitelist)) {
					$whitelist = array();
				}
				foreach ($items as $key) {
					list($path, $paramKey, ) = $key;
					$whitelistKey = $path . '|' . $paramKey;
					if (array_key_exists($whitelistKey, $whitelist)) {
						unset($whitelist[$whitelistKey]);
					}
				}
				$waf->getStorageEngine()->setConfig('whitelistedURLParams', $whitelist, 'livewaf');
				return array(
					'data'    => self::_getWAFData(),
					'success' => true,
				);
			}
		}
		return false;
	}

	public static function ajax_whitelistBulkEnable_callback() {
		if (class_exists('wfWAF') && $waf = wfWAF::getInstance()) {
			if (!empty($_POST['items']) && ($items = json_decode(stripslashes($_POST['items']), true)) !== false) {
				self::_whitelistBulkToggle($items, true);
				return array(
					'data'    => self::_getWAFData(),
					'success' => true,
				);
			}
		}
		return false;
	}

	public static function ajax_whitelistBulkDisable_callback() {
		if (class_exists('wfWAF') && $waf = wfWAF::getInstance()) {
			if (!empty($_POST['items']) && ($items = json_decode(stripslashes($_POST['items']), true)) !== false) {
				self::_whitelistBulkToggle($items, false);
				return array(
					'data'    => self::_getWAFData(),
					'success' => true,
				);
			}
		}
		return false;
	}

	private static function _whitelistBulkToggle($items, $enabled) {
		$waf = wfWAF::getInstance();
		$whitelist = (array) $waf->getStorageEngine()->getConfig('whitelistedURLParams', null, 'livewaf');
		if (!is_array($whitelist)) {
			$whitelist = array();
		}
		foreach ($items as $key) {
			list($path, $paramKey, ) = $key;
			$whitelistKey = $path . '|' . $paramKey;
			if (array_key_exists($whitelistKey, $whitelist) && is_array($whitelist[$whitelistKey])) {
				foreach ($whitelist[$whitelistKey] as $ruleID => $data) {
					$whitelist[$whitelistKey][$ruleID]['disabled'] = !$enabled;
				}
			}
		}
		$waf->getStorageEngine()->setConfig('whitelistedURLParams', $whitelist, 'livewaf');
	}

	private static function _getWAFData($updated = null, $failureReason = false) {
		$data['learningMode'] = wfWAF::getInstance()->isInLearningMode();
		$data['rules'] = wfWAF::getInstance()->getRules();
		/** @var wfWAFRule $rule */
		foreach ($data['rules'] as $ruleID => $rule) {
			$data['rules'][$ruleID] = $rule->toArray();
		}

		$whitelistedURLParams = (array) wfWAF::getInstance()->getStorageEngine()->getConfig('whitelistedURLParams', array(), 'livewaf');
		$data['whitelistedURLParams'] = array();
		if (is_array($whitelistedURLParams)) {
			foreach ($whitelistedURLParams as $urlParamKey => $rules) {
				list($path, $paramKey) = explode('|', $urlParamKey);
				$whitelistData = null;
				foreach ($rules as $ruleID => $whitelistedData) {
					if ($whitelistData === null) {
						$whitelistData = $whitelistedData;
						continue;
					}
					if ($ruleID === 'all') {
						$whitelistData = $whitelistedData;
						break;
					}
				}

				if (is_array($whitelistData) && array_key_exists('userID', $whitelistData) && function_exists('get_user_by')) {
					$user = get_user_by('id', $whitelistData['userID']);
					if ($user) {
						$whitelistData['username'] = $user->user_login;
					}
				}

				$data['whitelistedURLParams'][] = array(
					'path'     => $path,
					'paramKey' => $paramKey,
					'ruleID'   => array_keys($rules),
					'data'     => $whitelistData,
				);
			}
		}

		$data['disabledRules'] = (array) wfWAF::getInstance()->getStorageEngine()->getConfig('disabledRules');
		if ($lastUpdated = wfWAF::getInstance()->getStorageEngine()->getConfig('rulesLastUpdated', null, 'transient')) {
			$data['rulesLastUpdated'] = $lastUpdated;
		}
		$data['isPaid'] = (bool) wfConfig::get('isPaid', 0);
		if ($updated !== null) {
			$data['updated'] = (bool) $updated;
			if (!$updated) {
				$data['failure'] = $failureReason;
			}
		}
		return $data;
	}
	
	public static function ajax_wafStatus_callback() {
		if (!empty($_REQUEST['nonce']) && hash_equals($_REQUEST['nonce'], wfConfig::get('wafStatusCallbackNonce', ''))) {
			wfConfig::set('wafStatusCallbackNonce', '');
			wfUtils::send_json(array('active' => WFWAF_AUTO_PREPEND, 'subdirectory' => WFWAF_SUBDIRECTORY_INSTALL));
		}
		wfUtils::send_json(false);
	}
	
	public static function ajax_installAutoPrepend_callback() {
		global $wp_filesystem;
		
		$currentAutoPrependFile = ini_get('auto_prepend_file');
		$currentAutoPrepend = null;
		if (isset($_POST['currentAutoPrepend']) && !WF_IS_WP_ENGINE && !WF_IS_PRESSABLE) {
			$currentAutoPrepend = $_POST['currentAutoPrepend'];
		}
		
		$serverConfiguration = null;
		if (isset($_POST['serverConfiguration']) && wfWAFAutoPrependHelper::isValidServerConfig($_POST['serverConfiguration'])) {
			$serverConfiguration = $_POST['serverConfiguration'];
		}
		
		if ($serverConfiguration === null) {
			return array('errorMsg' => __('A valid server configuration was not provided.', 'wordfence'));
		}
		
		$helper = new wfWAFAutoPrependHelper($serverConfiguration, $currentAutoPrepend === 'override' ? null : $currentAutoPrependFile);
		
		ob_start();
		$ajaxURL = admin_url('admin-ajax.php');
		$allow_relaxed_file_ownership = true;
		if (false === ($credentials = request_filesystem_credentials($ajaxURL, '', false, ABSPATH, array('version', 'locale', 'action', 'serverConfiguration', 'currentAutoPrepend'), $allow_relaxed_file_ownership))) {
			$credentialsContent = ob_get_clean();
			$html = wfView::create('waf/waf-modal-wrapper', array(
				'title' => __('Filesystem Credentials Required', 'wordfence'),
				'html' => $credentialsContent,
				'helpHTML' => sprintf(/* translators: Support URL. */ __('If you cannot complete the setup process, <a target="_blank" rel="noopener noreferrer" href="%s">click here for help</a>', 'wordfence'), wfSupportController::esc_supportURL(wfSupportController::ITEM_FIREWALL_WAF_INSTALL_MANUALLY)),
				'footerHTML' => esc_html__('Once you have entered credentials, click Continue to complete the setup.', 'wordfence'),
			))->render();
			return array('needsCredentials' => 1, 'html' => $html);
		}
		ob_end_clean();
		
		if (!WP_Filesystem($credentials, ABSPATH, $allow_relaxed_file_ownership) && $wp_filesystem->errors->get_error_code()) {
			$credentialsError = '';
			foreach ($wp_filesystem->errors->get_error_messages() as $message) {
				if (is_wp_error($message)) {
					if ($message->get_error_data() && is_string($message->get_error_data())) {
						$message = $message->get_error_message() . ': ' . $message->get_error_data();
					}
					else {
						$message = $message->get_error_message();
					}
				}
				$credentialsError .= "<p>$message</p>\n";
			}
				
			$html = wfView::create('waf/waf-modal-wrapper', array(
				'title' => __('Filesystem Permission Error', 'wordfence'),
				'html' => $credentialsError,
				'helpHTML' => sprintf(/* translators: Support URL. */ __('If you cannot complete the setup process, <a target="_blank" rel="noopener noreferrer" href="%s">click here for help</a>', 'wordfence'), wfSupportController::esc_supportURL(wfSupportController::ITEM_FIREWALL_WAF_INSTALL_MANUALLY)),
				'footerButtonTitle' => __('Cancel', 'wordfence'),
			))->render();
			return array('credentialsFailed' => 1, 'html' => $html);
		}
		
		try {
			$helper->performInstallation($wp_filesystem);
			
			$nonce = bin2hex(wfWAFUtils::random_bytes(32));
			wfConfig::set('wafStatusCallbackNonce', $nonce);
			$verifyURL = add_query_arg(array('action' => 'wordfence_wafStatus', 'nonce' => $nonce), $ajaxURL);
			$response = wp_remote_get($verifyURL, array('headers' => array('Referer' => false/*, 'Cookie' => 'XDEBUG_SESSION=1'*/)));
			
			$active = false;
			if (!is_wp_error($response)) {
				$wafStatus = @json_decode(wp_remote_retrieve_body($response), true);
				if (isset($wafStatus['active']) && isset($wafStatus['subdirectory'])) {
					$active = $wafStatus['active'] && !$wafStatus['subdirectory'];
				}
			}
			
			if ($serverConfiguration == 'manual') {
				$html = wfView::create('waf/waf-modal-wrapper', array(
					'title' => __('Manual Installation Instructions', 'wordfence'),
					'html' => wfView::create('waf/waf-install-manual')->render(),
					'footerButtonTitle' => __('Close', 'wordfence'),
				))->render();
			}
			else {
				$html = wfView::create('waf/waf-modal-wrapper', array(
					'title' => __('Installation Successful', 'wordfence'),
					'html' => wfView::create('waf/waf-install-success', array('active' => $active))->render(),
					'footerButtonTitle' => __('Close', 'wordfence'),
				))->render();
			}
			
			return array('ok' => 1, 'html' => $html);
		}
		catch (wfWAFAutoPrependHelperException $e) {
			$installError = "<p>" . $e->getMessage() . "</p>";
			$html = wfView::create('waf/waf-modal-wrapper', array(
				'title' => __('Installation Failed', 'wordfence'),
				'html' => $installError,
				'helpHTML' => sprintf(/* translators: Support URL. */ __('If you cannot complete the setup process, <a target="_blank" rel="noopener noreferrer" href="%s">click here for help</a>', 'wordfence'), wfSupportController::esc_supportURL(wfSupportController::ITEM_FIREWALL_WAF_INSTALL_MANUALLY)),
				'footerButtonTitle' => __('Cancel', 'wordfence'),
			))->render();
			return array('installationFailed' => 1, 'html' => $html);
		}
	}
	
	public static function ajax_uninstallAutoPrepend_callback() {
		global $wp_filesystem;
		
		$serverConfiguration = null;
		if (isset($_POST['serverConfiguration']) && wfWAFAutoPrependHelper::isValidServerConfig($_POST['serverConfiguration'])) {
			$serverConfiguration = $_POST['serverConfiguration'];
		}
		
		if ($serverConfiguration === null) {
			return array('errorMsg' => __('A valid server configuration was not provided.', 'wordfence'));
		}
		
		$helper = new wfWAFAutoPrependHelper($serverConfiguration, null);
		
		if (isset($_POST['credentials']) && isset($_POST['credentialsSignature'])) {
			$salt = wp_salt('logged_in');
			$expectedSignature = hash_hmac('sha256', $_POST['credentials'], $salt);
			if (hash_equals($expectedSignature, $_POST['credentialsSignature'])) {
				$decrypted = wfUtils::decrypt($_POST['credentials']);
				$credentials = @json_decode($decrypted, true);
			}
		}
		
		$ajaxURL = admin_url('admin-ajax.php');
		if (!isset($credentials)) {
			$allow_relaxed_file_ownership = true;
			ob_start();
			if (false === ($credentials = request_filesystem_credentials($ajaxURL, '', false, ABSPATH, array('version', 'locale', 'action', 'serverConfiguration', 'iniModified'), $allow_relaxed_file_ownership))) {
				$credentialsContent = ob_get_clean();
				$html = wfView::create('waf/waf-modal-wrapper', array(
					'title' => __('Filesystem Credentials Required', 'wordfence'),
					'html' => $credentialsContent,
					'helpHTML' => sprintf(/* translators: Support URL. */ __('If you cannot complete the uninstall process, <a target="_blank" rel="noopener noreferrer" href="%s">click here for help</a>', 'wordfence'), wfSupportController::esc_supportURL(wfSupportController::ITEM_FIREWALL_WAF_REMOVE_MANUALLY)),
					'footerHTML' => esc_html__('Once you have entered credentials, click Continue to complete uninstallation.', 'wordfence'),
				))->render();
				return array('needsCredentials' => 1, 'html' => $html);
			}
			ob_end_clean();
		}
		
		if (!WP_Filesystem($credentials, ABSPATH, $allow_relaxed_file_ownership) && $wp_filesystem->errors->get_error_code()) {
			$credentialsError = '';
			foreach ($wp_filesystem->errors->get_error_messages() as $message) {
				if (is_wp_error($message)) {
					if ($message->get_error_data() && is_string($message->get_error_data())) {
						$message = $message->get_error_message() . ': ' . $message->get_error_data();
					}
					else {
						$message = $message->get_error_message();
					}
				}
				$credentialsError .= "<p>$message</p>\n";
			}
			
			$html = wfView::create('waf/waf-modal-wrapper', array(
				'title' => __('Filesystem Permission Error', 'wordfence'),
				'html' => $credentialsError,
				'helpHTML' => sprintf(/* translators: Support URL. */ __('If you cannot complete the uninstall process, <a target="_blank" rel="noopener noreferrer" href="%s">click here for help</a>', 'wordfence'), wfSupportController::esc_supportURL(wfSupportController::ITEM_FIREWALL_WAF_REMOVE_MANUALLY)),
				'footerButtonTitle' => __('Cancel', 'wordfence'),
			))->render();
			return array('credentialsFailed' => 1, 'html' => $html);
		}
		
		try {
			if ((!isset($_POST['iniModified']) || (isset($_POST['iniModified']) && !$_POST['iniModified'])) && !WF_IS_PRESSABLE) { //Uses .user.ini but not yet modified
				$hasPreviousAutoPrepend = $helper->performIniRemoval($wp_filesystem);
				
				$iniTTL = intval(ini_get('user_ini.cache_ttl'));
				if ($iniTTL == 0) {
					$iniTTL = 300; //The PHP default
				}
				if (!$helper->usesUserIni()) {
					$iniTTL = 0; //.htaccess
				}
				$timeout = max(30, $iniTTL);
				$timeoutString = wfUtils::makeDuration($timeout);
				
				$waitingResponse = '<p>' . __('The <code>auto_prepend_file</code> setting has been successfully removed from <code>.htaccess</code> and <code>.user.ini</code>. Once this change takes effect, Extended Protection Mode will be disabled.', 'wordfence') . '</p>';
				if ($hasPreviousAutoPrepend) {
					$waitingResponse .= '<p>' . __('Any previous value for <code>auto_prepend_file</code> will need to be re-enabled manually if still needed.', 'wordfence') . '</p>';
				}
				
				$spinner = wfView::create('common/indeterminate-progress', array('size' => 32))->render();
				$waitingResponse .= '<ul class="wf-flex-horizontal"><li>' . $spinner . '</li><li class="wf-padding-add-left">' . sprintf(/* translators: Time until. */ __('Waiting for it to take effect. This may take up to %s.', 'wordfence'), $timeoutString) . '</li></ul>';
				
				$html = wfView::create('waf/waf-modal-wrapper', array(
					'title' => __('Waiting for Changes', 'wordfence'),
					'html' => $waitingResponse,
					'helpHTML' => sprintf(/* translators: Support URL. */ __('If you cannot complete the uninstall process, <a target="_blank" rel="noopener noreferrer" href="%s">click here for help</a>', 'wordfence'), wfSupportController::esc_supportURL(wfSupportController::ITEM_FIREWALL_WAF_REMOVE_MANUALLY)),
					'footerButtonTitle' => __('Close', 'wordfence'),
					'noX' => true,
				))->render();
				
				$response = array('uninstallationWaiting' => 1, 'html' => $html, 'timeout' => $timeout, 'serverConfiguration' => $_POST['serverConfiguration']);
				if (isset($credentials) && is_array($credentials)) {
					$salt = wp_salt('logged_in');
					$json = json_encode($credentials);
					$encrypted = wfUtils::encrypt($json);
					$signature = hash_hmac('sha256', $encrypted, $salt);
					$response['credentials'] = $encrypted;
					$response['credentialsSignature'] = $signature;
				}
				return $response;
			}
			else { //.user.ini and .htaccess modified if applicable and waiting period elapsed or otherwise ready to advance to next step
				if (WFWAF_AUTO_PREPEND && !WFWAF_SUBDIRECTORY_INSTALL && !WF_IS_WP_ENGINE && !WF_IS_PRESSABLE) { //.user.ini modified, but the WAF is still enabled
					$retryAttempted = (isset($_POST['retryAttempted']) && $_POST['retryAttempted']);
					$userIniError = '<p class="wf-error">';
					$userIniError .= __('Extended Protection Mode has not been disabled. This may be because <code>auto_prepend_file</code> is configured somewhere else or the value is still cached by PHP.', 'wordfence');
					if ($retryAttempted) {
						$userIniError .= ' <strong>' . __('Retrying Failed.', 'wordfence') . '</strong>';
					}
					$userIniError .= ' <a href="#" class="wf-waf-uninstall-try-again">' . __('Try Again', 'wordfence') . '</a>';
					$userIniError .= '</p>';
					$html = wfView::create('waf/waf-modal-wrapper', array(
						'title' => __('Unable to Uninstall', 'wordfence'),
						'html' => $userIniError,
						'helpHTML' => sprintf(/* translators: Support URL. */ __('If you cannot complete the uninstall process, <a target="_blank" rel="noopener noreferrer" href="%s">click here for help</a>', 'wordfence'), wfSupportController::esc_supportURL(wfSupportController::ITEM_FIREWALL_WAF_REMOVE_MANUALLY)),
						'footerButtonTitle' => __('Cancel', 'wordfence'),
					))->render();
					
					$response = array('uninstallationFailed' => 1, 'html' => $html, 'serverConfiguration' => $_POST['serverConfiguration']);
					if (isset($credentials) && is_array($credentials)) {
						$salt = wp_salt('logged_in');
						$json = json_encode($credentials);
						$encrypted = wfUtils::encrypt($json);
						$signature = hash_hmac('sha256', $encrypted, $salt);
						$response['credentials'] = $encrypted;
						$response['credentialsSignature'] = $signature;
					}
					return $response;
				}
				
				$helper->performAutoPrependFileRemoval($wp_filesystem);
				
				$nonce = bin2hex(wfWAFUtils::random_bytes(32));
				wfConfig::set('wafStatusCallbackNonce', $nonce);
				$verifyURL = add_query_arg(array('action' => 'wordfence_wafStatus', 'nonce' => $nonce), $ajaxURL);
				$response = wp_remote_get($verifyURL, array('headers' => array('Referer' => false/*, 'Cookie' => 'XDEBUG_SESSION=1'*/)));
				
				$active = true;
				$subdirectory = WFWAF_SUBDIRECTORY_INSTALL;
				if (!is_wp_error($response)) {
					$wafStatus = @json_decode(wp_remote_retrieve_body($response), true);
					if (isset($wafStatus['active']) && isset($wafStatus['subdirectory'])) {
						$active = $wafStatus['active'] && !$wafStatus['subdirectory'];
						$subdirectory = $wafStatus['subdirectory'];
					}
				}
				
				$html = wfView::create('waf/waf-modal-wrapper', array(
					'title' => __('Uninstallation Complete', 'wordfence'),
					'html' => wfView::create('waf/waf-uninstall-success', array('active' => $active, 'subdirectory' => $subdirectory))->render(),
					'footerButtonTitle' => __('Close', 'wordfence'),
				))->render();
				return array('ok' => 1, 'html' => $html);
			}
		}
		catch (wfWAFAutoPrependHelperException $e) {
			$installError = "<p>" . $e->getMessage() . "</p>";
			$html = wfView::create('waf/waf-modal-wrapper', array(
				'title' => __('Uninstallation Failed', 'wordfence'),
				'html' => $installError,
				'helpHTML' => sprintf(/* translators: Support URL. */ __('If you cannot complete the uninstall process, <a target="_blank" rel="noopener noreferrer" href="%s">click here for help</a>', 'wordfence'), wfSupportController::esc_supportURL(wfSupportController::ITEM_FIREWALL_WAF_REMOVE_MANUALLY)),
				'footerButtonTitle' => __('Cancel', 'wordfence'),
			))->render();
			return array('uninstallationFailed' => 1, 'html' => $html);
		}
	}

	public static function actionUserRegistration($user_id) {
		if (wfUtils::isAdmin($user_id) && ($request = self::getLog()->getCurrentRequest())) {
			//self::getLog()->canLogHit = true;
			$request->action = 'user:adminCreate';
			$request->save();
		}
	}

	public static function actionPasswordReset($user = null, $new_pass = null) {
		if ($request = self::getLog()->getCurrentRequest()) {
			//self::getLog()->canLogHit = true;
			$request->action = 'user:passwordReset';
			$request->save();
		}
	}

	public static function trimWfHits($force = false) {
		if(!$force && self::isApiDelayed())
			return;
		$wfdb = new wfDB();
		$lastAggregation = wfConfig::get('lastBlockAggregation', 0);
		$table_wfHits = wfDB::networkTable('wfHits');
		$count = $wfdb->querySingle("select count(*) as cnt from {$table_wfHits}");
		$liveTrafficMaxRows = absint(wfConfig::get('liveTraf_maxRows', 2000));
		if ($count > $liveTrafficMaxRows * 10) {
			self::_aggregateBlockStats($lastAggregation);
			$wfdb->truncate($table_wfHits); //So we don't slow down sites that have very large wfHits tables
		}
		else if ($count > $liveTrafficMaxRows) {
			self::_aggregateBlockStats($lastAggregation);
			$wfdb->queryWrite("delete from {$table_wfHits} order by id asc limit %d", ($count - $liveTrafficMaxRows) + ($liveTrafficMaxRows * .2));
		}
		else if ($lastAggregation < (time() - 86400)) {
			self::_aggregateBlockStats($lastAggregation);
		}
		
		$maxAge = wfConfig::get('liveTraf_maxAge', 30);
		if ($maxAge <= 0 || $maxAge > 30) {
			$maxAge = 30;
		}
		$wfdb->queryWrite("DELETE FROM {$table_wfHits} WHERE ctime < %d", time() - ($maxAge * 86400));
	}
	
	private static function _aggregateBlockStats($since = false) {
		global $wpdb;
		
		if (!wfConfig::get('other_WFNet', true)) {
			return;
		}
		
		if ($since === false) {
			$since = wfConfig::get('lastBlockAggregation', 0);
		}
		
		$hitsTable = wfDB::networkTable('wfHits');
		$query = $wpdb->prepare("SELECT COUNT(*) AS cnt, CASE WHEN (jsRun = 1 OR userID > 0) THEN 1 ELSE 0 END AS isHuman, statusCode FROM {$hitsTable} WHERE ctime > %d GROUP BY isHuman, statusCode", $since);
		$rows = $wpdb->get_results($query, ARRAY_A);
		if (count($rows)) {
			try {
				$api = new wfAPI(wfConfig::get('apiKey'), wfUtils::getWPVersion());
				$api->call('aggregate_stats', array(), array('stats' => json_encode($rows)));
			}
			catch (Exception $e) {
				// Do nothing
			}
		}
		
		wfConfig::set('lastBlockAggregation', time());
	}

	private static function isApiDelayed() {
		return wfConfig::get('apiDelayedUntil', 0) > time();
	}

	private static function delaySendAttackData($until) {
		wfConfig::set('apiDelayedUntil', $until);
		self::scheduleSendAttackData($until);
	}

	private static function scheduleSendAttackData($timeToSend = null) {
		if ($timeToSend === null) {
			$timeToSend = time() + (60 * 5);
		}
		$notMainSite = is_multisite() && !is_main_site();
		if ($notMainSite) {
			global $current_site;
			switch_to_blog($current_site->blog_id);
		}
		if (!wp_next_scheduled('wordfence_processAttackData')) {
			wp_schedule_single_event($timeToSend, 'wordfence_processAttackData');
		}
		if ($notMainSite) {
			restore_current_blog();
		}
	}

	/**
	 *
	 */
	public static function processAttackData() {
		global $wpdb;
		$table_wfHits = wfDB::networkTable('wfHits');
		if (!defined('DONOTCACHEDB')) { define('DONOTCACHEDB', true); }
		
		$waf = wfWAF::getInstance();
		if ($waf->getStorageEngine()->getConfig('attackDataKey', false) === false) {
			$waf->getStorageEngine()->setConfig('attackDataKey', mt_rand(0, 0xfff));
		}
		
		//Send alert email if needed
		if (wfConfig::get('wafAlertOnAttacks')) {
			$alertInterval = wfConfig::get('wafAlertInterval', 0);
			$cutoffTime = max(time() - $alertInterval, wfConfig::get('wafAlertLastSendTime'));
			$wafAlertWhitelist = wfConfig::get('wafAlertWhitelist', '');
			$wafAlertWhitelist = preg_split("/[,\r\n]+/", $wafAlertWhitelist);
			foreach ($wafAlertWhitelist as $index => &$entry) {
				$entry = trim($entry);
				if (empty($entry) || (!preg_match('/^(?:\d{1,3}(?:\.|$)){4}/', $entry) && !preg_match('/^((?:[\da-f]{1,4}(?::|)){0,8})(::)?((?:[\da-f]{1,4}(?::|)){0,8})$/i', $entry))) {
					unset($wafAlertWhitelist[$index]);
					continue;
				}
				
				$packed = @wfUtils::inet_pton($entry);
				if ($packed === false) {
					unset($wafAlertWhitelist[$index]);
					continue;
				}
				$entry = bin2hex($packed);
			}
			$wafAlertWhitelist = array_filter($wafAlertWhitelist);
			$attackData = $wpdb->get_results($wpdb->prepare("SELECT SQL_CALC_FOUND_ROWS * FROM {$table_wfHits}
	WHERE action = 'blocked:waf' " .
	(count($wafAlertWhitelist) ? "AND HEX(IP) NOT IN (" . implode(", ", array_fill(0, count($wafAlertWhitelist), '%s')) . ")" : "") 
	. "AND attackLogTime > %f
	ORDER BY attackLogTime DESC
	LIMIT 10", array_merge($wafAlertWhitelist, array(sprintf('%.6f', $cutoffTime)))));
			$attackCount = $wpdb->get_var('SELECT FOUND_ROWS()');
			$threshold = (int) wfConfig::get('wafAlertThreshold');
			if ($threshold < 1) {
				$threshold = 100;
			}
			if ($attackCount >= $threshold) {
				$durationMessage = wfUtils::makeDuration($alertInterval);
				$message = sprintf(
					/* translators: 1. Number of attacks/blocks. 2. Time since. */
					__('The Wordfence Web Application Firewall has blocked %1$d attacks over the last %2$s. Below is a sample of these recent attacks:', 'wordfence'),
					$attackCount,
					$durationMessage
				);
				$attackTable = array();
				$dateMax = $ipMax = $countryMax = 0;
				foreach ($attackData as $row) {
					$actionData = json_decode($row->actionData, true);
					if (!is_array($actionData) || !isset($actionData['paramKey']) || !isset($actionData['paramValue'])) {
						continue;
					}
					
					if (isset($actionData['failedRules']) && $actionData['failedRules'] == 'blocked') {
						$row->longDescription = __("Blocked because the IP is blocklisted", 'wordfence');
					}
					else {
						$row->longDescription = sprintf(/* translators: Reason for firewall action. */ __("Blocked for %s", 'wordfence'), $row->actionDescription);
					}
					
					$paramKey = base64_decode($actionData['paramKey']);
					$paramValue = base64_decode($actionData['paramValue']);
					if (strlen($paramValue) > 100) {
						$paramValue = substr($paramValue, 0, 100) . '...';
					}
					
					if (preg_match('/([a-z0-9_]+\.[a-z0-9_]+)(?:\[(.+?)\](.*))?/i', $paramKey, $matches)) {
						switch ($matches[1]) {
							case 'request.queryString':
								$row->longDescription = sprintf(
									/* translators: 1. Reason for firewall action. 2. Input parameter. 2. Input parameter value. */
									__('Blocked for %1$s in query string: %2$s = %3$s', 'wordfence'), $row->actionDescription, $matches[2], $paramValue);
								break;
							case 'request.body':
								$row->longDescription = sprintf(
									/* translators: 1. Reason for firewall action. 2. Input parameter. 2. Input parameter value. */
									__('Blocked for %1$s in POST body: %2$s = %3$s', 'wordfence'), $row->actionDescription, $matches[2], $paramValue);
								break;
							case 'request.cookie':
								$row->longDescription = sprintf(
									/* translators: 1. Reason for firewall action. 2. Input parameter. 2. Input parameter value. */
									__('Blocked for %1$s in cookie: %2$s = %3$s', 'wordfence'), $row->actionDescription, $matches[2], $paramValue);
								break;
							case 'request.fileNames':
								$row->longDescription = sprintf(
									/* translators: 1. Reason for firewall action. 2. Input parameter. 2. Input parameter value. */
									__('Blocked for %1$s in file: %2$s = %3$s', 'wordfence'), $row->actionDescription, $matches[2], $paramValue);
								break;
						}
					}
					
					$date = date_i18n('F j, Y g:ia', floor($row->attackLogTime)); $dateMax = max(strlen($date), $dateMax);
					$ip = wfUtils::inet_ntop($row->IP); $ipMax = max(strlen($ip), $ipMax);
					$country = wfUtils::countryCode2Name(wfUtils::IP2Country($ip)); $country = (empty($country) ? 'Unknown' : $country); $countryMax = max(strlen($country), $countryMax); 
					$attackTable[] = array('date' => $date, 'IP' => $ip, 'country' => $country, 'message' => $row->longDescription);
				}
				
				foreach ($attackTable as $row) {
					$date = str_pad($row['date'], $dateMax + 2);
					$ip = str_pad($row['IP'] . " ({$row['country']})", $ipMax + $countryMax + 8);
					$attackMessage = $row['message'];
					$message .= $date . $ip . $attackMessage . "\n";
				}

				$alertCallback = array(new wfIncreasedAttackRateAlert($message), 'send');
				do_action('wordfence_security_event', 'increasedAttackRate', array(
					'attackCount' => $attackCount,
					'attackTable' => $attackTable,
					'duration' => $alertInterval,
					'ip' => wfUtils::getIP(),
				), $alertCallback);

				wfConfig::set('wafAlertLastSendTime', time());
			}
		}

		if (wfConfig::get('other_WFNet', true)) {
			$response = wp_remote_get(sprintf(WFWAF_API_URL_SEC . "waf-rules/%d.txt", $waf->getStorageEngine()->getConfig('attackDataKey')), array('headers' => array('Referer' => false)));
			if (!is_wp_error($response)) {
				$okToSendBody = wp_remote_retrieve_body($response);
				if ($okToSendBody === 'ok') {
					//Send attack data
					$limit = 500;
					$lastSendTime = wfConfig::get('lastAttackDataSendTime');
					$lastSendId = wfConfig::get('lastAttackDataSendId');
					if($lastSendId===false){
						$query=$wpdb->prepare("SELECT SQL_CALC_FOUND_ROWS * FROM {$table_wfHits}
						WHERE action in ('blocked:waf', 'learned:waf', 'logged:waf', 'blocked:waf-always')
						AND attackLogTime > %f
						LIMIT %d", sprintf('%.6f', $lastSendTime), $limit);
					}
					else{
						$query=$wpdb->prepare("SELECT SQL_CALC_FOUND_ROWS * FROM {$table_wfHits}
						WHERE action in ('blocked:waf', 'learned:waf', 'logged:waf', 'blocked:waf-always')
						AND id > %d
						ORDER BY id LIMIT %d", $lastSendId, $limit);
					}
					$params[]=$limit;
					$attackData = $wpdb->get_results($query);
					$totalRows = $wpdb->get_var('SELECT FOUND_ROWS()');
			
					if ($attackData) { // Build JSON to send
						$dataToSend = array();
						$attackDataToUpdate = array();
						foreach ($attackData as $attackDataRow) {
							$actionData = (array) wfRequestModel::unserializeActionData($attackDataRow->actionData);
							$dataToSend[] = array(
								$attackDataRow->attackLogTime,
								$attackDataRow->ctime,
								wfUtils::inet_ntop($attackDataRow->IP),
								(array_key_exists('learningMode', $actionData) ? $actionData['learningMode'] : 0),
								(array_key_exists('paramKey', $actionData) ? base64_encode($actionData['paramKey']) : false),
								(array_key_exists('paramValue', $actionData) ? base64_encode($actionData['paramValue']) : false),
								(array_key_exists('failedRules', $actionData) ? $actionData['failedRules'] : ''),
								strpos($attackDataRow->URL, 'https') === 0 ? 1 : 0,
								(array_key_exists('fullRequest', $actionData) ? $actionData['fullRequest'] : ''),
							);
							if (array_key_exists('fullRequest', $actionData)) {
								unset($actionData['fullRequest']);
								$attackDataToUpdate[$attackDataRow->id] = array(
									'actionData' => wfRequestModel::serializeActionData($actionData),
								);
							}
							if ($attackDataRow->attackLogTime > $lastSendTime) {
								$lastSendTime = $attackDataRow->attackLogTime;
							}
						}

						$bodyLimit=self::ATTACK_DATA_BODY_LIMIT;
						$response=null;
						do {
							$bodyData=null;
							do {
								if($bodyData!==null)
									array_splice($dataToSend, floor(count($dataToSend)/2));
								$bodyData=json_encode($dataToSend);
							} while(strlen($bodyData)>$bodyLimit&&count($dataToSend)>1);
							
							$homeurl = wfUtils::wpHomeURL();
							$siteurl = wfUtils::wpSiteURL();
							$installType = wfUtils::wafInstallationType();
							$response = wp_remote_post(WFWAF_API_URL_SEC . "?" . http_build_query(array(
									'action' => 'send_waf_attack_data',
									'k'      => $waf->getStorageEngine()->getConfig('apiKey', null, 'synced'),
									's'      => $siteurl,
									'h'		 => $homeurl,
									't'		 => microtime(true),
									'c'		 => $installType,
									'lang'   => get_site_option('WPLANG'),
								), null, '&'),
								array(
									'body'    => $bodyData,
									'headers' => array(
										'Content-Type' => 'application/json',
										'Referer' => false,
									),
									'timeout' => 30,
								));
								$bodyLimit/=2;
						} while(wp_remote_retrieve_response_code($response)===413&&count($dataToSend)>1);

						if (!is_wp_error($response) && ($body = wp_remote_retrieve_body($response))) {
							$jsonData = json_decode($body, true);
							if (is_array($jsonData) && array_key_exists('success', $jsonData)) {
								wfConfig::set('lastAttackDataSendTime', $lastSendTime);
								$lastSendIndex=count($dataToSend)-1;
								if($lastSendIndex>=0){
									$lastSendId = $attackData[$lastSendIndex]->id;
									wfConfig::set('lastAttackDataSendId', $lastSendId);
									// Successfully sent data, remove the full request from the table to reduce storage size
									foreach ($attackDataToUpdate as $hitID => $dataToUpdate) {
										if ($hitID <= $lastSendId) {
											$wpdb->update($table_wfHits, $dataToUpdate, array(
												'id' => $hitID,
											));
										}
									}
								}
								if (count($dataToSend) < $totalRows) {
									self::scheduleSendAttackData();
								}
								
								if (array_key_exists('data', $jsonData) && array_key_exists('watchedIPList', $jsonData['data'])) {
									$waf->getStorageEngine()->setConfig('watchedIPs', $jsonData['data']['watchedIPList'], 'transient');
								}
							}
						}
						else{
							//Delay interactions for 30 minutes if an error occurs
							self::delaySendAttackData(time() + 30*60);
						}
					}
					
					//Send false positives
					$lastSendTime = wfConfig::get('lastFalsePositiveSendTime');
					$whitelistedURLParams = (array) wfWAF::getInstance()->getStorageEngine()->getConfig('whitelistedURLParams', array(), 'livewaf');
					if (count($whitelistedURLParams)) {
						$data = array();
						$mostRecentWhitelisting = $lastSendTime;
						foreach ($whitelistedURLParams as $urlParamKey => $rules) {
							list($path, $paramKey) = explode('|', $urlParamKey);
							$ruleData = array();
							foreach ($rules as $ruleID => $whitelistedData) {
								if ($whitelistedData['timestamp'] > $lastSendTime && (!isset($whitelistedData['disabled']) || !$whitelistedData['disabled'])) {
									if (isset($whitelistedData['source'])) {
										$source = $whitelistedData['source'];
									}
									else if ($whitelistedData['description'] == 'Allowlisted via false positive dialog') {
										$source = 'false-positive';
									}
									else if ($whitelistedData['description'] == 'Allowlisted via Live Traffic') {
										$source = 'live-traffic';
									}
									else if ($whitelistedData['description'] == 'Allowlisted while in Learning Mode.') {
										$source = 'learning-mode';
									}
									else { //A user-entered description or Whitelisted via Firewall Options page
										$source = 'waf-options';
									}
									
									$ruleData[] = array(
										$ruleID,
										$whitelistedData['timestamp'],
										$source,
										$whitelistedData['description'],
										$whitelistedData['ip'],
										isset($whitelistedData['userID']) ? $whitelistedData['userID'] : 0,
									);
									
									if ($whitelistedData['timestamp'] > $mostRecentWhitelisting) {
										$mostRecentWhitelisting = $whitelistedData['timestamp'];
									}
								}
							}
							
							if (count($ruleData)) {
								$data[] = array(
									base64_decode($path),
									base64_decode($paramKey),
									$ruleData,
								);
							}
						}
						
						if (count($data)) {
							$homeurl = wfUtils::wpHomeURL();
							$siteurl = wfUtils::wpSiteURL();
							$installType = wfUtils::wafInstallationType();
							$response = wp_remote_post(WFWAF_API_URL_SEC . "?" . http_build_query(array(
									'action' => 'send_waf_false_positives',
									'k'      => $waf->getStorageEngine()->getConfig('apiKey', null, 'synced'),
									's'      => $siteurl,
									'h'		 => $homeurl,
									't'		 => microtime(true),
									'c'		 => $installType,
									'lang'   => get_site_option('WPLANG'),
								), null, '&'),
								array(
									'body'    => json_encode($data),
									'headers' => array(
										'Content-Type' => 'application/json',
										'Referer' => false,
									),
									'timeout' => 30,
								));
							
							if (!is_wp_error($response) && ($body = wp_remote_retrieve_body($response))) {
								$jsonData = json_decode($body, true);
								if (is_array($jsonData) && array_key_exists('success', $jsonData)) {
									wfConfig::set('lastFalsePositiveSendTime', $mostRecentWhitelisting);
								}
							}
						}
					}
				}
				else if (is_string($okToSendBody) && preg_match('/next check in: ([0-9]+)/', $okToSendBody, $matches)) {
					self::delaySendAttackData(time() + $matches[1]);
				}
			}
			else { // Could be that the server is down, so hold off on sending data for a little while
				self::delaySendAttackData(time() + 7200);
			}
		}
		else if (!wfConfig::get('other_WFNet', true)) {
			wfConfig::set('lastAttackDataSendTime', time());
			wfConfig::set('lastFalsePositiveSendTime', time());
		}

		self::trimWfHits();
	}

	public static function syncAttackData($exit = true) {
		global $wpdb;
		if (!defined('DONOTCACHEDB')) { define('DONOTCACHEDB', true); }
		$log = self::getLog();
		$waf = wfWAF::getInstance();
		$table_wfHits = wfDB::networkTable('wfHits');
		if ($waf->getStorageEngine() instanceof wfWAFStorageMySQL) {
			$lastAttackMicroseconds = floatval($waf->getStorageEngine()->getConfig('lastAttackDataTruncateTime'));
		} else {
			$lastAttackMicroseconds = $wpdb->get_var("SELECT MAX(attackLogTime) FROM {$table_wfHits}");
		}

		if ($waf->getStorageEngine()->hasNewerAttackData($lastAttackMicroseconds)) {
			$attackData = $waf->getStorageEngine()->getNewestAttackDataArray($lastAttackMicroseconds);
			if ($attackData) {
				foreach ($attackData as $request) {
					if (count($request) !== 9 && count($request) !== 10 /* with metadata */ && count($request) !== 11) {
						continue;
					}

					list($logTimeMicroseconds, $requestTime, $ip, $learningMode, $paramKey, $paramValue, $failedRules, $ssl, $requestString) = $request;
					$metadata = null;
					$recordID = null;
					if (array_key_exists(9, $request)) {
						$metadata = $request[9];
					}
					if (array_key_exists(10, $request)) {
						$recordID = $request[10];
					}

					// Skip old entries and hits in learning mode, since they'll get picked up anyways.
					if ($logTimeMicroseconds <= $lastAttackMicroseconds || $learningMode) {
						continue;
					}
					
					$statusCode = 403;

					$hit = new wfRequestModel();
					if (is_numeric($recordID)) {
						$hit->id = $recordID;
					}

					$hit->attackLogTime = $logTimeMicroseconds;
					$hit->ctime = $requestTime;
					$hit->IP = wfUtils::inet_pton($ip);

					if (preg_match('/user\-agent:(.*?)\n/i', $requestString, $matches)) {
						$hit->UA = trim($matches[1]);
						$hit->isGoogle = wfCrawl::isGoogleCrawler($hit->UA);
					}

					if (preg_match('/Referer:(.*?)\n/i', $requestString, $matches)) {
						$hit->referer = trim($matches[1]);
					}

					if (preg_match('/^[a-z]+\s+(.*?)\s+/i', $requestString, $uriMatches) && preg_match('/Host:(.*?)\n/i', $requestString, $hostMatches)) {
						$hit->URL = 'http' . ($ssl ? 's' : '') . '://' . trim($hostMatches[1]) . trim($uriMatches[1]);
					}
					
					$hit->jsRun = (int) wfLog::isHumanRequest($ip, $hit->UA);
					$isHuman = !!$hit->jsRun;
					
					if (preg_match('/cookie:(.*?)\n/i', $requestString, $matches)) {
						$authCookieName = $waf->getAuthCookieName();
						$hasLoginCookie = strpos($matches[1], $authCookieName) !== false;
						if ($hasLoginCookie && preg_match('/' . preg_quote($authCookieName) . '=(.*?);/', $matches[1], $cookieMatches)) {
							$authCookie = rawurldecode($cookieMatches[1]);
							$decodedAuthCookie = $waf->parseAuthCookie($authCookie);
							if ($decodedAuthCookie !== false) {
								$hit->userID = $decodedAuthCookie['userID'];
								$isHuman = true;
							}
						}
					}

					$path = '/';
					if (preg_match('/^[A-Z]+ (.*?) HTTP\\/1\\.1/', $requestString, $matches)) {
						if (($pos = strpos($matches[1], '?')) !== false) {
							$path = substr($matches[1], 0, $pos);
						} else {
							$path = $matches[1];
						}
					}
					
					$metadata = ($metadata != null ? (array) $metadata : array());
					if (isset($metadata['finalAction']) && $metadata['finalAction']) { // The request was blocked/redirected because of its IP based on the plugin's blocking settings. WAF blocks should be reported but not shown in live traffic with that as a reason.
						$action = $metadata['finalAction']['action'];
						$actionDescription = $action;
						if (class_exists('wfWAFIPBlocksController')) {
							if ($action == wfWAFIPBlocksController::WFWAF_BLOCK_UAREFIPRANGE) {
								wfActivityReport::logBlockedIP($ip, null, 'advanced');
							}
							else if ($action == wfWAFIPBlocksController::WFWAF_BLOCK_COUNTRY_BYPASS_REDIR) {
								/* Handled below */
							}
							else if ($action == wfWAFIPBlocksController::WFWAF_BLOCK_COUNTRY_REDIR) {
								$actionDescription .= ' (' . wfConfig::get('cbl_redirURL') . ')';
								wfConfig::inc('totalCountryBlocked');
								wfActivityReport::logBlockedIP($ip, null, 'country');
							}
							else if ($action == wfWAFIPBlocksController::WFWAF_BLOCK_COUNTRY) {
								wfConfig::inc('totalCountryBlocked');
								wfActivityReport::logBlockedIP($ip, null, 'country');
							}
							else if ($action == wfWAFIPBlocksController::WFWAF_BLOCK_WFSN) {
								wordfence::wfsnReportBlockedAttempt($ip, 'login');
								wfActivityReport::logBlockedIP($ip, null, 'brute');
							}
							else if (defined('wfWAFIPBlocksController::WFWAF_BLOCK_BADPOST') && $action == wfWAFIPBlocksController::WFWAF_BLOCK_BADPOST) {
								wfActivityReport::logBlockedIP($ip, null, 'badpost');
							}
							else if (defined('wfWAFIPBlocksController::WFWAF_BLOCK_BANNEDURL') && $action == wfWAFIPBlocksController::WFWAF_BLOCK_BANNEDURL) {
								wfActivityReport::logBlockedIP($ip, null, 'bannedurl');
							}
							else if (defined('wfWAFIPBlocksController::WFWAF_BLOCK_FAKEGOOGLE') && $action == wfWAFIPBlocksController::WFWAF_BLOCK_FAKEGOOGLE) {
								wfActivityReport::logBlockedIP($ip, null, 'fakegoogle');
							}
							else if ((defined('wfWAFIPBlocksController::WFWAF_BLOCK_LOGINSEC') && $action == wfWAFIPBlocksController::WFWAF_BLOCK_LOGINSEC) ||
									(defined('wfWAFIPBlocksController::WFWAF_BLOCK_LOGINSEC_FORGOTPASSWD') && strpos($action, wfWAFIPBlocksController::WFWAF_BLOCK_LOGINSEC_FORGOTPASSWD) === 0) ||
									(defined('wfWAFIPBlocksController::WFWAF_BLOCK_LOGINSEC_FAILURES') && strpos($action, wfWAFIPBlocksController::WFWAF_BLOCK_LOGINSEC_FAILURES) === 0)) {
								wfActivityReport::logBlockedIP($ip, null, 'brute');
							}
							else if ((defined('wfWAFIPBlocksController::WFWAF_BLOCK_THROTTLEGLOBAL') && $action == wfWAFIPBlocksController::WFWAF_BLOCK_THROTTLEGLOBAL) ||
									(defined('wfWAFIPBlocksController::WFWAF_BLOCK_THROTTLESCAN') && $action == wfWAFIPBlocksController::WFWAF_BLOCK_THROTTLESCAN) ||
									(defined('wfWAFIPBlocksController::WFWAF_BLOCK_THROTTLECRAWLER') && $action == wfWAFIPBlocksController::WFWAF_BLOCK_THROTTLECRAWLER) ||
									(defined('wfWAFIPBlocksController::WFWAF_BLOCK_THROTTLECRAWLERNOTFOUND') && $action == wfWAFIPBlocksController::WFWAF_BLOCK_THROTTLECRAWLERNOTFOUND) ||
									(defined('wfWAFIPBlocksController::WFWAF_BLOCK_THROTTLEHUMAN') && $action == wfWAFIPBlocksController::WFWAF_BLOCK_THROTTLEHUMAN) ||
									(defined('wfWAFIPBlocksController::WFWAF_BLOCK_THROTTLEHUMANNOTFOUND') && $action == wfWAFIPBlocksController::WFWAF_BLOCK_THROTTLEHUMANNOTFOUND)
							) {
								wfConfig::inc('totalIPsThrottled');
								wfActivityReport::logBlockedIP($ip, null, 'throttle');
							}
							else { //Manual block
								wfActivityReport::logBlockedIP($ip, null, 'manual');
							}
							
							if (isset($metadata['finalAction']['id']) && $action != wfWAFIPBlocksController::WFWAF_BLOCK_COUNTRY_BYPASS_REDIR) {
								$id = $metadata['finalAction']['id'];
								$block = new wfBlock($id);
								$block->recordBlock(1, (int) $requestTime);
							}
						}
						
						if (strlen($actionDescription) == 0) {
							$actionDescription = 'Blocked by Wordfence';
						}
						
						if (empty($failedRules)) { // Just a plugin block
							$statusCode = 503;
							$hit->action = 'blocked:wordfence';
							if (class_exists('wfWAFIPBlocksController')) {
								if ($action == wfWAFIPBlocksController::WFWAF_BLOCK_COUNTRY_BYPASS_REDIR) {
									$statusCode = 302;
									$hit->action = 'cbl:redirect';
								}
								else if ($action == wfWAFIPBlocksController::WFWAF_BLOCK_WFSN) {
									$hit->action = 'blocked:wfsnrepeat';
									wordfence::wfsnReportBlockedAttempt($ip, 'waf');
								}
								else if (isset($metadata['finalAction']['lockout'])) {
									$hit->action = 'lockedOut';
								}
								else if (isset($metadata['finalAction']['block'])) {
									//Do nothing
								}
							}
							$hit->actionDescription = $actionDescription;
						}
						else if (preg_match('/\blogged\b/i', $failedRules)) {
							$statusCode = 200;
							$hit->action = 'logged:waf';
						}
						else { // Blocked by the WAF but would've been blocked anyway by the plugin settings so that message takes priority
							$hit->action = 'blocked:waf-always';
							$hit->actionDescription = $actionDescription;
						}
					}
					else {
						if (preg_match('/\blogged\b/i', $failedRules)) {
							$statusCode = 200;
							$hit->action = 'logged:waf';
						}
						else {
							$hit->action = 'blocked:waf';
							
							$type = null;
							if ($failedRules == 'blocked') {
								$type = 'blacklist';
							}
							else if (is_numeric($failedRules)) {
								$type = 'waf';
							}
							wfActivityReport::logBlockedIP($hit->IP, null, $type);
						}
					}

					/** @var wfWAFRule $rule */
					$ruleIDs = explode('|', $failedRules);
					$actionData = array(
						'learningMode' => $learningMode,
						'failedRules'  => $failedRules,
						'paramKey'     => $paramKey,
						'paramValue'   => $paramValue,
						'path'         => $path,
					);
					if ($ruleIDs && $ruleIDs[0]) {
						$rule = $waf->getRule($ruleIDs[0]);
						if ($rule) {
							if ($hit->action == 'logged:waf' || $hit->action == 'blocked:waf') { $hit->actionDescription = $rule->getDescription(); }
							$actionData['category'] = $rule->getCategory();
							$actionData['ssl'] = $ssl;
							$actionData['fullRequest'] = base64_encode($requestString);
						}
						else if ($ruleIDs[0] == 'logged' && isset($ruleIDs[1]) && ($rule = $waf->getRule($ruleIDs[1]))) {
							if ($hit->action == 'logged:waf' || $hit->action == 'blocked:waf') { $hit->actionDescription = $rule->getDescription(); }
							$actionData['category'] = $rule->getCategory();
							$actionData['ssl'] = $ssl;
							$actionData['fullRequest'] = base64_encode($requestString);
						}
						else if ($ruleIDs[0] == 'logged') {
							if ($hit->action == 'logged:waf' || $hit->action == 'blocked:waf') { $hit->actionDescription = 'Watched IP Traffic: ' . $ip; } 
							$actionData['category'] = 'logged';
							$actionData['ssl'] = $ssl;
							$actionData['fullRequest'] = base64_encode($requestString);
						}
						else if ($ruleIDs[0] == 'blocked') {
							$actionData['category'] = 'blocked';
							$actionData['ssl'] = $ssl;
							$actionData['fullRequest'] = base64_encode($requestString);
						}
					}

					$hit->actionData = wfRequestModel::serializeActionData($actionData);
					$hit->statusCode = $statusCode;
					$hit->save();

					self::scheduleSendAttackData();
				}
			}
			$waf->getStorageEngine()->truncateAttackData();
		}
		update_site_option('wordfence_syncingAttackData', 0);
		update_site_option('wordfence_syncAttackDataAttempts', 0);
		update_site_option('wordfence_lastSyncAttackData', time());
		if ($exit) {
			exit;
		}
	}

	public static function addSyncAttackDataAjax() {
		$URL = home_url('/?wordfence_syncAttackData=' . microtime(true));
		$URL = esc_url(preg_replace('/^https?:/i', '', $URL));
		// Load as external script async so we don't slow page down.
		echo "<script type=\"text/javascript\" src=\"$URL\" async></script>";
	}

	/**
	 * This is the only hook I see to tie into WP's core update process.
	 * Since we hide the readme.html to prevent the WordPress version from being discovered, it breaks the upgrade
	 * process because it cannot copy the previous readme.html.
	 *
	 * @param string $string
	 * @return string
	 */
	public static function restoreReadmeForUpgrade($string) {
		static $didRun;
		if (!isset($didRun)) {
			$didRun = true;
			wfUtils::showReadme();
			register_shutdown_function('wfUtils::hideReadme');
		}

		return $string;
	}

	public static function wafAutoPrependNotice() {
		$url = network_admin_url('admin.php?page=WordfenceWAF&subpage=waf_options#configureAutoPrepend');
		echo '<div class="update-nag" id="wf-extended-protection-notice">' . __('To make your site as secure as possible, take a moment to optimize the Wordfence Web Application Firewall:', 'wordfence') . ' &nbsp;<a class="wf-btn wf-btn-default wf-btn-sm" href="' . esc_url($url) . '">' . __('Click here to configure', 'wordfence') . '</a>
		<a class="wf-btn wf-btn-default wf-btn-sm wf-dismiss-link" href="#"  onclick="wordfenceExt.setOption(\'dismissAutoPrependNotice\', 1); jQuery(\'#wf-extended-protection-notice\').fadeOut(); return false;">' . __('Dismiss', 'wordfence') . '</a>
		<br>
		<em style="font-size: 85%;">' . sprintf(/* translators: Support URL. */ __('If you cannot complete the setup process, <a target="_blank" rel="noopener noreferrer" href="%s">click here for help</a>.', 'wordfence'), wfSupportController::esc_supportURL(wfSupportController::ITEM_FIREWALL_WAF_INSTALL_MANUALLY)) . '</em>
		</div>';
	}

	public static function wafAutoPrependVerify() {
		if (WFWAF_AUTO_PREPEND && !WFWAF_SUBDIRECTORY_INSTALL) {
			echo '<div class="updated is-dismissible"><p>' . __('Nice work! The firewall is now optimized.', 'wordfence') . '</p></div>';
		} else {
			echo '<div class="notice notice-error"><p>' . __('The changes have not yet taken effect. If you are using LiteSpeed or IIS as your web server or CGI/FastCGI interface, you may need to wait a few minutes for the changes to take effect since the configuration files are sometimes cached. You also may need to select a different server configuration in order to complete this step, but wait for a few minutes before trying. You can try refreshing this page.', 'wordfence') . '</p></div>';
		}
	}
	
	public static function wafAutoPrependRemoved() {
		if (!WFWAF_AUTO_PREPEND) {
			echo '<div class="updated is-dismissible"><p>' . __('Uninstallation was successful!', 'wordfence') . '</p></div>';
		}
		else if (WFWAF_SUBDIRECTORY_INSTALL) {
			echo '<div class="notice notice-warning"><p>' . __('Uninstallation from this site was successful! The Wordfence Firewall is still active because it is installed in another WordPress installation.', 'wordfence') . '</p></div>';
		}
		else {
			echo '<div class="notice notice-error"><p>' . __('The changes have not yet taken effect. If you are using LiteSpeed or IIS as your web server or CGI/FastCGI interface, you may need to wait a few minutes for the changes to take effect since the configuration files are sometimes cached. You also may need to select a different server configuration in order to complete this step, but wait for a few minutes before trying. You can try refreshing this page.', 'wordfence') . '</p></div>';
		}
	}
	
	public static function wafUpdateSuccessful() {
		echo '<div class="updated is-dismissible"><p>' . __('The update was successful!', 'wordfence') . '</p></div>';
	}

	public static function getWAFBootstrapPath() {
		if (WF_IS_PRESSABLE) {
			return WP_CONTENT_DIR . '/wordfence-waf.php';
		}
		return ABSPATH . 'wordfence-waf.php';
	}

	public static function getWAFBootstrapContent($currentAutoPrependedFile = null) {
		$bootstrapPath = dirname(self::getWAFBootstrapPath());
		$currentAutoPrepend = '';
		if ($currentAutoPrependedFile && is_file($currentAutoPrependedFile) && !WFWAF_SUBDIRECTORY_INSTALL) {
			$currentAutoPrepend = sprintf('
// This file was the current value of auto_prepend_file during the Wordfence WAF installation (%2$s)
if (file_exists(%1$s)) {
	include_once %1$s;
}', var_export($currentAutoPrependedFile, true), date('r'));
		}
		return sprintf('<?php
// Before removing this file, please verify the PHP ini setting `auto_prepend_file` does not point to this.
%3$s
if (file_exists(__DIR__.%1$s)) {
	define("WFWAF_LOG_PATH", __DIR__.%2$s);
	include_once __DIR__.%1$s;
}',
			var_export(wfUtils::relativePath(WORDFENCE_PATH . 'waf/bootstrap.php', $bootstrapPath, true), true),
			var_export(wfUtils::relativePath((WFWAF_SUBDIRECTORY_INSTALL ? WP_CONTENT_DIR . '/wflogs/' : WFWAF_LOG_PATH), $bootstrapPath, true), true),
			$currentAutoPrepend);
	}

	/**
	 * @return bool|string
	 */
	private static function getCurrentUserRole() {
		if (current_user_can('administrator') || is_super_admin()) {
			return 'administrator';
		}
		$roles = array('editor', 'author', 'contributor', 'subscriber');
		foreach ($roles as $role) {
			if (current_user_can($role)) {
				return $role;
			}
		}
		return 'other';
	}

	private static function getCurrentUserCapabilities() {
		$capabilities = array(
			'manage_options',
			'unfiltered_html',
			'edit_others_posts',
			'upload_files',
			'publish_posts',
			'edit_posts',
			'read',
			'manage_network'
		);
		foreach ($capabilities as $index=>$capability) {
			if (!current_user_can($capability)) {
				unset($capabilities[$index]);
			}
		}
		return array_values($capabilities);
	}

	public static function licenseStatusChanged() {
		$event = new wfWAFCronFetchRulesEvent(time() - 2);
		$event->setWaf(wfWAF::getInstance());
		$event->fire();
		
		//Update the WAF cron
		$cron = (array) wfWAF::getInstance()->getStorageEngine()->getConfig('cron', null, 'livewaf');
		if (is_array($cron)) {
			/** @var wfWAFCronEvent $event */
			foreach ($cron as $index => $event) {
				$event->setWaf(wfWAF::getInstance());
				if (!$event->isInPast()) {
					$newEvent = $event->reschedule();
					if ($newEvent instanceof wfWAFCronEvent && $newEvent !== $event) {
						$cron[$index] = $newEvent;
					} else {
						unset($cron[$index]);
					}
				}
			}
		}
		wfWAF::getInstance()->getStorageEngine()->setConfig('cron', $cron, 'livewaf');
	}

	/**
	 * @param string $adminURL
	 * @param string $homePath
	 * @param bool $relaxedFileOwnership
	 * @param bool $output Whether or not to output the credentials collection form. If false, this function only returns the status.
	 * @return bool Returns true if the path is writable, otherwise false.
	 */
	public static function requestFilesystemCredentials($adminURL, $homePath = null, $relaxedFileOwnership = true, $output = true) {
		if ($homePath === null) {
			$homePath = get_home_path();
		}

		if (!$output) { ob_start(); }
		if (false === ($credentials = request_filesystem_credentials($adminURL, '', false, $homePath, array('version', 'locale'), $relaxedFileOwnership))) {
			if (!$output) { ob_end_clean(); }
			return false;
		}

		if (!WP_Filesystem($credentials, $homePath, $relaxedFileOwnership)) { // Failed to connect, Error and request again
			request_filesystem_credentials($adminURL, '', true, ABSPATH, array('version', 'locale'), $relaxedFileOwnership);
			if (!$output) { ob_end_clean(); }
			return false;
		}
		
		global $wp_filesystem;
		if ($wp_filesystem->errors->get_error_code()) {
			if (!$output) { ob_end_clean(); }
			return false;
		}
		
		if (!$output) { ob_end_clean(); }
		return true;
	}

	public static function initRestAPI() {
		if (wfCentral::isSupported()) {
			$auth = new wfRESTAuthenticationController();
			$auth->registerRoutes();

			$config = new wfRESTConfigController();
			$config->registerRoutes();

			$scan = new wfRESTScanController();
			$scan->registerRoutes();
		}
	}

	public static function ajax_wfcentral_step1_callback() {
		// Step 1: Makes GET request to `/central/api/site/access-token` endpoint authenticated with the auth grant supplied by the user.
		// - Receives site GUID, public key, short lived JWT.

		$authGrant = isset($_REQUEST['auth-grant']) ? $_REQUEST['auth-grant'] : null;
		if (!$authGrant) {
			return array(
				'err'      => 1,
				'errorMsg' => __("Auth grant is invalid.", 'wordfence'),
			);
		}

		$request = new wfCentralAPIRequest('/site/access-token', 'GET', $authGrant);
		$response = $request->execute();

		if ($response->isError()) {
			return $response->returnErrorArray();
		}

		$body = $response->getJSONBody();
		if (!is_array($body) || !isset($body['data']['attributes'])) {
			return array(
				'err'      => 1,
				'errorMsg' => sprintf(/* translators: Error message. */ __("Invalid response from Wordfence Central: %s"), $response->getBody()),
			);
		}
		if (!array_key_exists('id', $body['data'])) {
			return array(
				'err'      => 1,
				'errorMsg' => sprintf(/* translators: JSON property. */ __("Invalid response from Wordfence Central. Parameter %s not found in response."), 'id'),
			);
		}

		$data = $body['data']['attributes'];
		$expected = array(
			'public-key',
			'access-token',
		);
		foreach ($expected as $key) {
			if (!array_key_exists($key, $data)) {
				return array(
					'err'      => 1,
					'errorMsg' => sprintf(/* translators: JSON property. */ __("Invalid response from Wordfence Central. Parameter %s not found in response."), $key),
				);
			}
		}

		wfConfig::set('wordfenceCentralSiteID', $body['data']['id']);
		wfConfig::set('wordfenceCentralPK', pack("H*", $data['public-key']));
		wfConfig::set('wordfenceCentralAccessToken', $data['access-token']);
		wfConfig::set('wordfenceCentralCurrentStep', 2);

		wfConfig::set('wordfenceCentralDisconnected', false);
		wfConfig::set('wordfenceCentralDisconnectTime', null);
		wfConfig::set('wordfenceCentralDisconnectEmail', null);

		return array(
			'success' => 1,
		);
	}

	public static function ajax_wfcentral_step2_callback() {
		// Step 2: Makes POST request to `/central/api/wf/site/<guid>` endpoint passing in the new public key.
		// Uses JWT from auth grant endpoint as auth.

		require_once(WORDFENCE_PATH . '/crypto/vendor/paragonie/sodium_compat/autoload-fast.php');

		$accessToken = wfConfig::get('wordfenceCentralAccessToken');
		if (!$accessToken) {
			return array(
				'err'      => 1,
				'errorMsg' => __("Access token not found.", 'wordfence'),
			);
		}

		$keypair = ParagonIE_Sodium_Compat::crypto_sign_keypair();
		$publicKey = ParagonIE_Sodium_Compat::crypto_sign_publickey($keypair);
		$secretKey = ParagonIE_Sodium_Compat::crypto_sign_secretkey($keypair);
		wfConfig::set('wordfenceCentralSecretKey', $secretKey);

		$request = new wfCentralAPIRequest('/site/' . wfConfig::get('wordfenceCentralSiteID'), 'POST',
			$accessToken, array(
				'data' => array(
					'attributes' => array(
						'public-key' => ParagonIE_Sodium_Compat::bin2hex($publicKey),
					),
				),
			));
		$response = $request->execute();

		if ($response->isError()) {
			return $response->returnErrorArray();
		}

		wfConfig::set('wordfenceCentralCurrentStep', 3);

		return array(
			'success' => 1,
		);
	}

	public static function ajax_wfcentral_step3_callback() {
		// Step 3: Makes GET request to `/central/api/wf/site/<guid>` endpoint signed using Wordfence plugin private key.
		// - Expects 200 response with site data.

		try {
			$request = new wfCentralAuthenticatedAPIRequest('/site/' . wfConfig::get('wordfenceCentralSiteID'));
			$response = $request->execute();
			if ($response->isError()) {
				return $response->returnErrorArray();
			}

			$body = $response->getJSONBody();
			if (!is_array($body) || !isset($body['data']['attributes'])) {
				return array(
					'error' => 1,
					'errorMsg' => __('Invalid response from Wordfence Central.', 'wordfence'),
				);
			}
			wfConfig::set('wordfenceCentralSiteData', json_encode($body['data']['attributes']));
			wfConfig::set('wordfenceCentralCurrentStep', 4);

			return array(
				'success' => 1,
			);

		} catch (wfCentralAPIException $e) {
			return array(
				'error' => 1,
				'errorMsg' => $e->getMessage(),
			);
		}
	}

	public static function ajax_wfcentral_step4_callback() {
		// Step 4: Poll for PUT request at `/wp-json/wp/v2/wordfence-auth-grant/` endpoint signed using Wordfence Central private key with short lived JWT.
		// - Expects verifiable signature of incoming request from Wordfence Central.
		// - Stores auth grant JWT.

		$wfCentralAuthGrant = wfConfig::get('wordfenceCentralUserSiteAuthGrant');
		if ($wfCentralAuthGrant) {
			wfConfig::set('wordfenceCentralCurrentStep', 5);
			return array(
				'success' => 1,
			);
		}
		return array(
			'success' => 0,
		);
	}

	public static function ajax_wfcentral_step5_callback() {
		// Step 5: Makes POST request to `/central/api/site/<guid>/access-token` endpoint signed using Wordfence plugin private key with auth grant JWT.
		// - Expects 200 response with access token.

		$wfCentralAuthGrant = wfConfig::get('wordfenceCentralUserSiteAuthGrant');
		if (!$wfCentralAuthGrant) {
			return array(
				'error' => 1,
				'errorMsg' => __('Auth grant not found.', 'wordfence'),
			);
		}

		try {
			$request = new wfCentralAuthenticatedAPIRequest(
				sprintf('/site/%s/access-token', wfConfig::get('wordfenceCentralSiteID')),
				'POST',
				array(
					'data' => array(
						'attributes' => array(
							'auth-grant' => $wfCentralAuthGrant,
						),
					),
				));
			$response = $request->execute();
			if ($response->isError()) {
				return $response->returnErrorArray();
			}

			$body = $response->getJSONBody();
			if (!is_array($body) || !isset($body['access-token'])) {
				return array(
					'error' => 1,
					'errorMsg' => __('Invalid response from Wordfence Central.', 'wordfence'),
				);
			}
			wfConfig::set('wordfenceCentralUserSiteAccessToken', $body['access-token']);
			wfConfig::set('wordfenceCentralCurrentStep', 6);

			return array(
				'success' => 1,
				'access-token' => $body['access-token'],
				'redirect-url' => sprintf(WORDFENCE_CENTRAL_URL_SEC . '/sites/%s?access-token=%s',
					rawurlencode(wfConfig::get('wordfenceCentralSiteID')), rawurlencode($body['access-token'])),
			);

		} catch (wfCentralAPIException $e) {
			return array(
				'error' => 1,
				'errorMsg' => $e->getMessage(),
			);
		}
	}
	public static function ajax_wfcentral_step6_callback() {
		$wfCentralUserSiteAccessToken = wfConfig::get('wordfenceCentralUserSiteAccessToken');
		if (!$wfCentralUserSiteAccessToken) {
			return array(
				'error' => 1,
				'errorMsg' => __('Access token not found.', 'wordfence'),
			);
		}

		$status = wfConfig::get('scanStageStatuses');
		wfCentral::updateScanStatus($status);

		wfConfig::set('wordfenceCentralConnectTime', time());
		wfConfig::set('wordfenceCentralConnectEmail', wp_get_current_user()->user_email);

		return array(
			'success' => 1,
			'access-token' => $wfCentralUserSiteAccessToken,
			'redirect-url' => sprintf(WORDFENCE_CENTRAL_URL_SEC . '/sites/%s?access-token=%s',
				rawurlencode(wfConfig::get('wordfenceCentralSiteID')), rawurlencode($wfCentralUserSiteAccessToken)),
		);
	}

	public static function ajax_wfcentral_disconnect_callback() {
		try {
			$request = new wfCentralAuthenticatedAPIRequest(
				sprintf('/site/%s', wfConfig::get('wordfenceCentralSiteID')),
				'DELETE');
			$response = $request->execute();
		} catch (wfCentralAPIException $e) {

		}

		wfRESTConfigController::disconnectConfig();

		return array(
			'success' => 1,
		);
	}

	public static function queueCentralConfigurationSync() {
		static $hasRun;
		if ($hasRun) {
			return;
		}
		$hasRun = true;
		add_action('shutdown', 'wfCentral::requestConfigurationSync');
	}
}


class wfWAFAutoPrependHelper {

	private $serverConfig;
	/**
	 * @var string
	 */
	private $currentAutoPrependedFile;
	
	public static function helper($serverConfig = null, $currentAutoPrependedFile = null) {
		return new wfWAFAutoPrependHelper($serverConfig, $currentAutoPrependedFile);
	}
	
	public static function isValidServerConfig($serverConfig) {
		$validValues = array(
			"apache-mod_php",
			"apache-suphp",
			"cgi",
			"litespeed",
			"nginx",
			"iis",
			'manual',
		);
		return in_array($serverConfig, $validValues);
	}
	
	/**
	 * Verifies the .htaccess block for mod_php if present, returning true if no changes need to happen, false
	 * if something needs to update.
	 * 
	 * @return bool
	 */
	public static function verifyHtaccessMod_php() {
		if (WFWAF_AUTO_PREPEND && PHP_MAJOR_VERSION > 5) {
			return true;
		}
		
		$serverInfo = wfWebServerInfo::createFromEnvironment();
		if (!$serverInfo->isApacheModPHP()) {
			return true;
		}
		
		$htaccessPath = get_home_path() . '.htaccess';
		if (file_exists($htaccessPath)) {
			$htaccessContent = file_get_contents($htaccessPath);
			$regex = '/# Wordfence WAF.*?# END Wordfence WAF/is';
			if (preg_match($regex, $htaccessContent, $matches)) {
				$wafBlock = $matches[0];
				$hasPHP5 = preg_match('/<IfModule mod_php5\.c>\s*php_value auto_prepend_file \'.*?\'\s*<\/IfModule>/is', $wafBlock);
				$hasPHP7 = preg_match('/<IfModule mod_php7\.c>\s*php_value auto_prepend_file \'.*?\'\s*<\/IfModule>/is', $wafBlock);
				$hasPHP8 = preg_match('/<IfModule mod_php\.c>\s*php_value auto_prepend_file \'.*?\'\s*<\/IfModule>/is', $wafBlock);
				if ($hasPHP5 && (!$hasPHP7 || !$hasPHP8)) { //Check if PHP 5 is configured, but not 7 or 8.
					return false;
				}
			}
		}
		
		return true;
	}
	
	/**
	 * Updates the mod_php block of the .htaccess if needed to include PHP 7. Returns whether or not this was performed successfully.
	 * 
	 * @return bool
	 */
	public static function fixHtaccessMod_php() {
		$htaccessPath = get_home_path() . '.htaccess';
		if (file_exists($htaccessPath)) {
			$htaccessContent = file_get_contents($htaccessPath);
			$regex = '/# Wordfence WAF.*?# END Wordfence WAF/is';
			if (preg_match($regex, $htaccessContent, $matches, PREG_OFFSET_CAPTURE)) {
				$wafBlock = $matches[0][0];
				$hasPHP5 = preg_match('/<IfModule mod_php5\.c>\s*php_value auto_prepend_file \'(.*?)\'\s*<\/IfModule>/is', $wafBlock, $php5Matches, PREG_OFFSET_CAPTURE);
				$hasPHP7 = preg_match('/<IfModule mod_php7\.c>\s*php_value auto_prepend_file \'.*?\'\s*<\/IfModule>/is', $wafBlock, $php7Matches, PREG_OFFSET_CAPTURE);
				$hasPHP8 = preg_match('/<IfModule mod_php\.c>\s*php_value auto_prepend_file \'.*?\'\s*<\/IfModule>/is', $wafBlock);
				if ($hasPHP5 && !$hasPHP7) {
					$beforeWAFBlock = substr($htaccessContent, 0, $matches[0][1]);
					$afterWAFBlock = substr($htaccessContent, $matches[0][1] + strlen($wafBlock));
					$beforeMod_php = substr($wafBlock, 0, $php5Matches[0][1]);
					$afterMod_php = substr($wafBlock, $php5Matches[0][1] + strlen($php5Matches[0][0]));
					$updatedHtaccessContent = $beforeWAFBlock . $beforeMod_php . $php5Matches[0][0] . "\n" . sprintf("<IfModule mod_php7.c>\n\tphp_value auto_prepend_file '%1\$s'\n</IfModule>\n<IfModule mod_php.c>\n\tphp_value auto_prepend_file '%1\$s'\n</IfModule>", $php5Matches[1][0] /* already escaped */) . $afterMod_php . $afterWAFBlock;
					return file_put_contents($htaccessPath, $updatedHtaccessContent) !== false;
				}
				if ($hasPHP5 && $hasPHP7 && !$hasPHP8) {
					$beforeWAFBlock = substr($htaccessContent, 0, $matches[0][1]);
					$afterWAFBlock = substr($htaccessContent, $matches[0][1] + strlen($wafBlock));
					$beforeMod_php = substr($wafBlock, 0, $php5Matches[0][1]);
					$afterMod_php = substr($wafBlock, $php7Matches[0][1] + strlen($php7Matches[0][0]));
					$updatedHtaccessContent = $beforeWAFBlock . $beforeMod_php . $php5Matches[0][0] . "\n" . $php7Matches[0][0] . "\n" . sprintf("<IfModule mod_php.c>\n\tphp_value auto_prepend_file '%s'\n</IfModule>", $php5Matches[1][0] /* already escaped */) . $afterMod_php . $afterWAFBlock;
					return file_put_contents($htaccessPath, $updatedHtaccessContent) !== false;
				}
			}
		}
		return false;
	}

	/**
	 * @param string|null $serverConfig
	 * @param string|null $currentAutoPrependedFile
	 */
	public function __construct($serverConfig = null, $currentAutoPrependedFile = null) {
		$this->serverConfig = $serverConfig;
		$this->currentAutoPrependedFile = $currentAutoPrependedFile;
	}

	public function getFilesNeededForBackup() {
		$backups = array();
		$htaccess = $this->getHtaccessPath();
		switch ($this->getServerConfig()) {
			case 'apache-mod_php':
			case 'apache-suphp':
			case 'litespeed':
			case 'cgi':
				if (file_exists($htaccess)) {
					$backups[] = $htaccess;
				}
				break;
		}
		if ($userIni = ini_get('user_ini.filename')) {
			$userIniPath = $this->getUserIniPath();
			switch ($this->getServerConfig()) {
				case 'cgi':
				case 'apache-suphp':
				case 'nginx':
				case 'litespeed':
				case 'iis':
					if (file_exists($userIniPath)) {
						$backups[] = $userIniPath;
					}
					break;
			}
		}
		return $backups;
	}

	public function downloadBackups($index = 0) {
		$backups = $this->getFilesNeededForBackup();
		if ($backups && array_key_exists($index, $backups)) {
			$url = site_url();
			$url = preg_replace('/^https?:\/\//i', '', $url);
			$url = preg_replace('/[^a-zA-Z0-9\.]+/', '_', $url);
			$url = preg_replace('/^_+/', '', $url);
			$url = preg_replace('/_+$/', '', $url);
			header('Content-Type: application/octet-stream');
			$backupFileName = ltrim(basename($backups[$index]), '.');
			header('Content-Disposition: attachment; filename="' . $backupFileName . '_Backup_for_' . $url . '.txt"');
			readfile($backups[$index]);
			die();
		}
	}

	/**
	 * @return mixed
	 */
	public function getServerConfig() {
		return $this->serverConfig;
	}

	/**
	 * @param mixed $serverConfig
	 */
	public function setServerConfig($serverConfig) {
		$this->serverConfig = $serverConfig;
	}

	/**
	 * @param WP_Filesystem_Base $wp_filesystem
	 * @throws wfWAFAutoPrependHelperException
	 */
	public function performInstallation($wp_filesystem) {
		$bootstrapPath = wordfence::getWAFBootstrapPath();
		if (!$wp_filesystem->put_contents($bootstrapPath, wordfence::getWAFBootstrapContent($this->currentAutoPrependedFile))) {
			throw new wfWAFAutoPrependHelperException(__('We were unable to create the <code>wordfence-waf.php</code> file in the root of the WordPress installation. It\'s possible WordPress cannot write to the <code>wordfence-waf.php</code> file because of file permissions. Please verify the permissions are correct and retry the installation.', 'wordfence'));
		}

		$serverConfig = $this->getServerConfig();

		$htaccessPath = $this->getHtaccessPath();
		$homePath = dirname($htaccessPath);

		$userIniPath = $this->getUserIniPath();
		$userIni = ini_get('user_ini.filename');

		$userIniHtaccessDirectives = '';
		if ($userIni) {
			$userIniHtaccessDirectives = sprintf('<Files "%s">
<IfModule mod_authz_core.c>
	Require all denied
</IfModule>
<IfModule !mod_authz_core.c>
	Order deny,allow
	Deny from all
</IfModule>
</Files>
', addcslashes($userIni, '"'));
		}


		// .htaccess configuration
		switch ($serverConfig) {
			case 'apache-mod_php':
				$autoPrependDirective = sprintf("# Wordfence WAF
<IfModule mod_php5.c>
	php_value auto_prepend_file '%1\$s'
</IfModule>
<IfModule mod_php7.c>
	php_value auto_prepend_file '%1\$s'
</IfModule>
<IfModule mod_php.c>
	php_value auto_prepend_file '%1\$s'
</IfModule>
$userIniHtaccessDirectives
# END Wordfence WAF
", addcslashes($bootstrapPath, "'"));
				break;

			case 'litespeed':
				$escapedBootstrapPath = addcslashes($bootstrapPath, "'");
				$autoPrependDirective = sprintf("# Wordfence WAF
<IfModule LiteSpeed>
php_value auto_prepend_file '%s'
</IfModule>
<IfModule lsapi_module>
php_value auto_prepend_file '%s'
</IfModule>
$userIniHtaccessDirectives
# END Wordfence WAF
", $escapedBootstrapPath, $escapedBootstrapPath);
				break;

			case 'apache-suphp':
				$autoPrependDirective = sprintf("# Wordfence WAF
$userIniHtaccessDirectives
# END Wordfence WAF
", addcslashes($homePath, "'"));
				break;

			case 'cgi':
				if ($userIniHtaccessDirectives) {
					$autoPrependDirective = sprintf("# Wordfence WAF
$userIniHtaccessDirectives
# END Wordfence WAF
", addcslashes($homePath, "'"));
				}
				break;

		}

		if (!empty($autoPrependDirective)) {
			// Modify .htaccess
			$htaccessContent = $wp_filesystem->get_contents($htaccessPath);

			if ($htaccessContent) {
				$regex = '/# Wordfence WAF.*?# END Wordfence WAF/is';
				if (preg_match($regex, $htaccessContent, $matches)) {
					$htaccessContent = preg_replace($regex, $autoPrependDirective, $htaccessContent);
				} else {
					$htaccessContent .= "\n\n" . $autoPrependDirective;
				}
			} else {
				$htaccessContent = $autoPrependDirective;
			}

			if (!$wp_filesystem->put_contents($htaccessPath, $htaccessContent)) {
				throw new wfWAFAutoPrependHelperException(__('We were unable to make changes to the .htaccess file. It\'s possible WordPress cannot write to the .htaccess file because of file permissions, which may have been set by another security plugin, or you may have set them manually. Please verify the permissions allow the web server to write to the file, and retry the installation.', 'wordfence'));
			}
			if ($serverConfig == 'litespeed') {
				// sleep(2);
				$wp_filesystem->touch($htaccessPath);
			}

		}
		if ($userIni) {
			// .user.ini configuration
			switch ($serverConfig) {
				case 'cgi':
				case 'nginx':
				case 'apache-suphp':
				case 'litespeed':
				case 'iis':
					$autoPrependIni = sprintf("; Wordfence WAF
auto_prepend_file = '%s'
; END Wordfence WAF
", addcslashes($bootstrapPath, "'"));

					break;
			}

			if (!empty($autoPrependIni)) {

				// Modify .user.ini
				$userIniContent = $wp_filesystem->get_contents($userIniPath);
				if (is_string($userIniContent)) {
					$userIniContent = str_replace('auto_prepend_file', ';auto_prepend_file', $userIniContent);
					$regex = '/; Wordfence WAF.*?; END Wordfence WAF/is';
					if (preg_match($regex, $userIniContent, $matches)) {
						$userIniContent = preg_replace($regex, $autoPrependIni, $userIniContent);
					} else {
						$userIniContent .= "\n\n" . $autoPrependIni;
					}
				} else {
					$userIniContent = $autoPrependIni;
				}

				if (!$wp_filesystem->put_contents($userIniPath, $userIniContent)) {
					throw new wfWAFAutoPrependHelperException(sprintf(/* translators: File path. */ __('We were unable to make changes to the %1$s file. It\'s possible WordPress cannot write to the %1$s file because of file permissions. Please verify the permissions are correct and retry the installation.', 'wordfence'), basename($userIniPath)));
				}
			}
		}
	}
	
	/**
	 * @param WP_Filesystem_Base $wp_filesystem
	 * @throws wfWAFAutoPrependHelperException
	 * 
	 * @return bool Whether or not the .user.ini still has a commented-out auto_prepend_file setting
	 */
	public function performIniRemoval($wp_filesystem) {
		$serverConfig = $this->getServerConfig();
		
		$htaccessPath = $this->getHtaccessPath();
		
		$userIniPath = $this->getUserIniPath();
		$userIni = ini_get('user_ini.filename');
		
		// Modify .htaccess
		$htaccessContent = $wp_filesystem->get_contents($htaccessPath);
		
		if (is_string($htaccessContent)) {
			$htaccessContent = preg_replace('/# Wordfence WAF.*?# END Wordfence WAF/is', '', $htaccessContent);
		} else {
			$htaccessContent = '';
		}
		
		if (!$wp_filesystem->put_contents($htaccessPath, $htaccessContent)) {
			throw new wfWAFAutoPrependHelperException(__('We were unable to make changes to the .htaccess file. It\'s possible WordPress cannot write to the .htaccess file because of file permissions, which may have been set by another security plugin, or you may have set them manually. Please verify the permissions allow the web server to write to the file, and retry the installation.', 'wordfence'));
		}
		if ($serverConfig == 'litespeed') {
			// sleep(2);
			$wp_filesystem->touch($htaccessPath);
		}
	
		if ($userIni) {
			// Modify .user.ini
			$userIniContent = $wp_filesystem->get_contents($userIniPath);
			if (is_string($userIniContent)) {
				$userIniContent = preg_replace('/; Wordfence WAF.*?; END Wordfence WAF/is', '', $userIniContent);
				$userIniContent = str_replace('auto_prepend_file', ';auto_prepend_file', $userIniContent);
			} else {
				$userIniContent = '';
			}
			
			if (!$wp_filesystem->put_contents($userIniPath, $userIniContent)) {
				throw new wfWAFAutoPrependHelperException(sprintf(/* translators: File path. */ __('We were unable to make changes to the %1$s file. It\'s possible WordPress cannot write to the %1$s file because of file permissions. Please verify the permissions are correct and retry the installation.', 'wordfence'), basename($userIniPath)));
			}
			
			return strpos($userIniContent, 'auto_prepend_file') !== false;
		}
		
		return false;
	}
	
	/**
	 * @param WP_Filesystem_Base $wp_filesystem
	 * @throws wfWAFAutoPrependHelperException
	 */
	public function performAutoPrependFileRemoval($wp_filesystem) {
		$bootstrapPath = wordfence::getWAFBootstrapPath();
		if (!$wp_filesystem->delete($bootstrapPath)) {
			throw new wfWAFAutoPrependHelperException(__('We were unable to remove the <code>wordfence-waf.php</code> file in the root of the WordPress installation. It\'s possible WordPress cannot remove the <code>wordfence-waf.php</code> file because of file permissions. Please verify the permissions are correct and retry the removal.', 'wordfence'));
		}
	}

	public function getHtaccessPath() {
		return get_home_path() . '.htaccess';
	}

	public function getUserIniPath() {
		$userIni = ini_get('user_ini.filename');
		if ($userIni) {
			return get_home_path() . $userIni;
		}
		return false;
	}
	
	public function usesUserIni() {
		$userIni = ini_get('user_ini.filename');
		if (!$userIni) {
			return false;
		}
		switch ($this->getServerConfig()) {
			case 'cgi':
			case 'apache-suphp':
			case 'nginx':
			case 'litespeed':
			case 'iis':
				return true;
		}
		return false;
	}

	public function uninstall() {
		/** @var WP_Filesystem_Base $wp_filesystem */
		global $wp_filesystem;

		$htaccessPath = $this->getHtaccessPath();
		$userIniPath = $this->getUserIniPath();

		$adminURL = admin_url('/');
		$allow_relaxed_file_ownership = true;
		$homePath = dirname($htaccessPath);

		ob_start();
		if (false === ($credentials = request_filesystem_credentials($adminURL, '', false, $homePath,
				array('version', 'locale'), $allow_relaxed_file_ownership))
		) {
			ob_end_clean();
			return false;
		}

		if (!WP_Filesystem($credentials, $homePath, $allow_relaxed_file_ownership)) {
			// Failed to connect, Error and request again
			request_filesystem_credentials($adminURL, '', true, ABSPATH, array('version', 'locale'),
				$allow_relaxed_file_ownership);
			ob_end_clean();
			return false;
		}

		if ($wp_filesystem->errors->get_error_code()) {
			ob_end_clean();
			return false;
		}
		ob_end_clean();

		if ($wp_filesystem->is_file($htaccessPath)) {
			$htaccessContent = $wp_filesystem->get_contents($htaccessPath);
			$regex = '/# Wordfence WAF.*?# END Wordfence WAF/is';
			if (preg_match($regex, $htaccessContent, $matches)) {
				$htaccessContent = preg_replace($regex, '', $htaccessContent);
				if (!$wp_filesystem->put_contents($htaccessPath, $htaccessContent)) {
					return false;
				}
			}
		}

		if ($wp_filesystem->is_file($userIniPath)) {
			$userIniContent = $wp_filesystem->get_contents($userIniPath);
			$regex = '/; Wordfence WAF.*?; END Wordfence WAF/is';
			if (preg_match($regex, $userIniContent, $matches)) {
				$userIniContent = preg_replace($regex, '', $userIniContent);
				if (!$wp_filesystem->put_contents($userIniPath, $userIniContent)) {
					return false;
				}
			}
		}

		$bootstrapPath = wordfence::getWAFBootstrapPath();
		if ($wp_filesystem->is_file($bootstrapPath)) {
			$wp_filesystem->delete($bootstrapPath);
		}
		return true;
	}
}

class wfWAFAutoPrependHelperException extends Exception {
}