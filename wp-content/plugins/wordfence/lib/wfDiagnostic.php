<?php

class wfGrant
{
	public $select = false;
	public $update = false;
	public $insert = false;
	public $delete = false;
	public $alter = false;
	public $create = false;
	public $drop = false;

	public static function get()
	{
		static $instance;
		if ($instance === null) {
			$instance = new self;
		}
		return $instance;
	}
	
	private function __construct()
	{
		global $wpdb;
		$rows = $wpdb->get_results("SHOW GRANTS FOR current_user()", ARRAY_N);
		
		foreach ($rows as $row) {
			preg_match("/GRANT (.+) ON (.+) TO/", $row[0], $matches);
			foreach (explode(",", $matches[1]) as $permission) {
				$permission = str_replace(" ", "_", trim(strtolower($permission)));
				if ($permission === 'all_privileges') {
					foreach ($this as $key => $value) {
						$this->$key = true;
					}
					break 2;
				}
				$this->$permission = true;
			}
		}
	}
}

class wfDiagnostic
{
	protected $minVersion = array(
		'PHP' => '5.6.20',
		'cURL' => '1.0',
	);

	protected $description = false; //Defined in the constructor to allow for localization

	protected $results = array();

	public function __construct()
	{
		$this->description = array(
			'Wordfence Status' => array(
				'description' => __('General information about the Wordfence installation.', 'wordfence'),
				'tests' => array(
					'wfVersion' => __('Wordfence Version', 'wordfence'),
					'geoIPVersion' => __('GeoIP Version', 'wordfence'),
					'cronStatus' => __('Cron Status', 'wordfence'),
				),
			),
			'Filesystem' => array(
				'description' => __('Ability to read/write various files.', 'wordfence'),
				'tests' => array(
					'isPluginReadable' => __('Checking if web server can read from <code>~/plugins/wordfence</code>', 'wordfence'),
					'isPluginWritable' => __('Checking if web server can write to <code>~/plugins/wordfence</code>', 'wordfence'),
					'isWAFReadable' => __('Checking if web server can read from <code>~/wp-content/wflogs</code>', 'wordfence'),
					'isWAFWritable' => __('Checking if web server can write to <code>~/wp-content/wflogs</code>', 'wordfence'),
				),
			),
			'Wordfence Config' => array(
				'description' => __('Ability to save Wordfence settings to the database.', 'wordfence'),
				'tests' => array(
					'configWritableSet' => __('Checking basic config reading/writing', 'wordfence'),
					'configWritableSetSer' => __('Checking serialized config reading/writing', 'wordfence'),
				),
			),
			'Wordfence Firewall' => array(
				'description' => __('Current WAF configuration.', 'wordfence'),
				'tests' => array(
					'wafAutoPrepend' => __('WAF auto prepend active', 'wordfence'),
					'wafStorageEngine' => __('Configured WAF storage engine (WFWAF_STORAGE_ENGINE)', 'wordfence'),
					'wafActiveStorageEngine' => __('Active WAF storage engine', 'wordfence'),
					'wafLogPath' => __('WAF log path', 'wordfence'),
					'wafSubdirectoryInstall' => __('WAF subdirectory installation', 'wordfence'),
					'wafAutoPrependFilePath' => __('wordfence-waf.php path', 'wordfence'),
					'wafFilePermissions' => __('WAF File Permissions', 'wordfence'),
					'wafRecentlyRemoved' => __('Recently removed wflogs files', 'wordfence'),
				),
			),
			'MySQL' => array(
				'description' => __('Database version and privileges.', 'wordfence'),
				'tests' => array(
					'databaseVersion' => __('Database Version', 'wordfence'),
					'userCanDelete' => __('Checking if MySQL user has <code>DELETE</code> privilege', 'wordfence'),
					'userCanInsert' => __('Checking if MySQL user has <code>INSERT</code> privilege', 'wordfence'),
					'userCanUpdate' => __('Checking if MySQL user has <code>UPDATE</code> privilege', 'wordfence'),
					'userCanSelect' => __('Checking if MySQL user has <code>SELECT</code> privilege', 'wordfence'),
					'userCanCreate' => __('Checking if MySQL user has <code>CREATE TABLE</code> privilege', 'wordfence'),
					'userCanAlter'  => __('Checking if MySQL user has <code>ALTER TABLE</code> privilege', 'wordfence'),
					'userCanDrop'   => __('Checking if MySQL user has <code>DROP</code> privilege', 'wordfence'),
					'userCanTruncate'   => __('Checking if MySQL user has <code>TRUNCATE</code> privilege', 'wordfence'),
				)
			),
			'PHP Environment' => array(
				'description' => __('PHP version, important PHP extensions.', 'wordfence'),
				'tests' => array(
					'phpVersion' => array('raw' => true, 'value' => wp_kses(sprintf(/* translators: Support URL. */ __('PHP version >= PHP 5.6.20<br><em> (<a href="https://wordpress.org/about/requirements/" target="_blank" rel="noopener noreferrer">Minimum version required by WordPress</a>)</em> <a href="%s" target="_blank" rel="noopener noreferrer" class="wfhelp"><span class="screen-reader-text"> (opens in new tab)</span></a>', 'wordfence'), wfSupportController::esc_supportURL(wfSupportController::ITEM_VERSION_PHP)), array('a'=>array('href'=>array(), 'target'=>array(), 'rel'=>array(), 'class'=>array()), 'span'=>array('class'=>array())))),
					'processOwner' => __('Process Owner', 'wordfence'),
					'hasOpenSSL' => __('Checking for OpenSSL support', 'wordfence'),
					'openSSLVersion' => __('Checking OpenSSL version', 'wordfence'),
					'hasCurl'    => __('Checking for cURL support', 'wordfence'),
					'curlFeatures'    => __('cURL Features Code', 'wordfence'),
					'curlHost'    => __('cURL Host', 'wordfence'),
					'curlProtocols'    => __('cURL Support Protocols', 'wordfence'),
					'curlSSLVersion'    => __('cURL SSL Version', 'wordfence'),
					'curlLibZVersion'    => __('cURL libz Version', 'wordfence'),
					'displayErrors' => array('raw' => true, 'value' => wp_kses(__('Checking <code>display_errors</code><br><em> (<a href="http://php.net/manual/en/errorfunc.configuration.php#ini.display-errors" target="_blank" rel="noopener noreferrer">Should be disabled on production servers<span class="screen-reader-text"> (opens in new tab)</span></a>)</em>', 'wordfence'), array('a'=>array('href'=>array(), 'target'=>array(), 'rel'=>array()), 'span'=>array('class'=>array()), 'em'=>array(), 'code'=>array(), 'br'=>array()))),
				)
			),
			'Connectivity' => array(
				'description' => __('Ability to connect to the Wordfence servers and your own site.', 'wordfence'),
				'tests' => array(
					'connectToServer1' => __('Connecting to Wordfence servers (http)', 'wordfence'),
					'connectToServer2' => __('Connecting to Wordfence servers (https)', 'wordfence'),
					'connectToSelf' => __('Connecting back to this site', 'wordfence'),
					'serverIP' => __('IP(s) used by this server', 'wordfence'),
				)
			),
			'Time' => array(
				'description' => __('Server time accuracy and applied offsets.', 'wordfence'),
				'tests' => array(
					'wfTime' => __('Wordfence Network Time', 'wordfence'),
					'serverTime' => __('Server Time', 'wordfence'),
					'wfTimeOffset' => __('Wordfence Network Time Offset', 'wordfence'),
					'ntpTimeOffset' => __('NTP Time Offset', 'wordfence'),
					'ntpStatus' => __('NTP Status', 'wordfence'),
					'timeSourceInUse' => __('TOTP Time Source', 'wordfence'),
					'wpTimeZone' => __('WordPress Time Zone', 'wordfence'),
				),
			),
		);
		
		foreach ($this->description as $title => $tests) {
			$this->results[$title] = array(
				'description' => $tests['description'],
			);
			foreach ($tests['tests'] as $name => $description) {
				if (!method_exists($this, $name)) {
					continue;
				}
				
				$result = $this->$name();

				if (is_bool($result)) {
					$result = array(
						'test'    => $result,
						'message' => $result ? 'OK' : 'FAIL',
					);
				}

				$result['label'] = $description;
				$result['name'] = $name;

				$this->results[$title]['results'][] = $result;
			}
		}
	}

	public function getResults()
	{
		return $this->results;
	}
	
	public function wfVersion() {
		return array('test' => true, 'message' => WORDFENCE_VERSION . ' (' . WORDFENCE_BUILD_NUMBER . ')');
	}
	
	public function geoIPVersion() {
		return array('test' => true, 'infoOnly' => true, 'message' => wfUtils::geoIPVersion());
	}
	
	public function cronStatus() {
		$cron = _get_cron_array();
		$overdue = 0;
		foreach ($cron as $timestamp => $values) {
			if (is_array($values)) {
				foreach ($values as $cron_job => $v) {
					if (is_numeric($timestamp)) {
						if ((time() - 1800) > $timestamp) { $overdue++; }
					}
				}
			}
		}
		
		return array('test' => true, 'infoOnly' => true, 'message' => $overdue ? sprintf(/* translators: Number of jobs. */ _n('%d Job Overdue', '%d Jobs Overdue', $overdue, 'wordfence'), $overdue) : __('Normal', 'wordfence'));
	}
	
	public function geoIPError() {
		$error = wfUtils::last_error('geoip');
		return array('test' => true, 'infoOnly' => true, 'message' => $error ? $error : __('None', 'wordfence'));
	}

	public function isPluginReadable() {
		return is_readable(WORDFENCE_PATH);
	}

	public function isPluginWritable() {
		return is_writable(WORDFENCE_PATH);
	}
	
	public function isWAFReadable() {
		if (!is_readable(WFWAF_LOG_PATH)) {
			if (defined('WFWAF_STORAGE_ENGINE') && WFWAF_STORAGE_ENGINE == 'mysqli') {
				return array('test' => false, 'infoOnly' => true, 'message' => __('No files readable', 'wordfence'));
			}
			
			return array('test' => false, 'message' => __('No files readable', 'wordfence'));
		}
		
		$files = array(
			WFWAF_LOG_PATH . 'attack-data.php', 
			WFWAF_LOG_PATH . 'ips.php', 
			WFWAF_LOG_PATH . 'config.php',
			WFWAF_LOG_PATH . 'rules.php',
		);
		$unreadable = array();
		foreach ($files as $f) {
			if (!file_exists($f)) {
				$unreadable[] = sprintf(__('File "%s" does not exist', 'wordfence'), basename($f));
			}
			else if (!is_readable($f)) {
				$unreadable[] = sprintf(/* translators: File path. */ __('File "%s" is unreadable', 'wordfence'), basename($f));
			}
		}
		
		if (count($unreadable) > 0) {
			if (defined('WFWAF_STORAGE_ENGINE') && WFWAF_STORAGE_ENGINE == 'mysqli') {
				return array('test' => false, 'infoOnly' => true, 'message' => implode(', ', $unreadable));
			}
			
			return array('test' => false, 'message' => implode(', ', $unreadable));
		}
		
		return true;
	}
	
	public function isWAFWritable() {
		if (!is_writable(WFWAF_LOG_PATH)) {
			if (defined('WFWAF_STORAGE_ENGINE') && WFWAF_STORAGE_ENGINE == 'mysqli') {
				return array('test' => false, 'infoOnly' => true, 'message' => __('No files writable', 'wordfence'));
			}
			
			return array('test' => false, 'message' => __('No files writable', 'wordfence'));
		}
		
		$files = array(
			WFWAF_LOG_PATH . 'attack-data.php',
			WFWAF_LOG_PATH . 'ips.php',
			WFWAF_LOG_PATH . 'config.php',
			WFWAF_LOG_PATH . 'rules.php',
		);
		$unwritable = array();
		foreach ($files as $f) {
			if (!file_exists($f)) {
				$unwritable[] = sprintf(/* translators: File name. */__('File "%s" does not exist', 'wordfence'), basename($f));
			}
			else if (!is_writable($f)) {
				$unwritable[] = sprintf(/* translators: File name. */__('File "%s" is unwritable', 'wordfence'), basename($f));
			}
		}
		
		if (count($unwritable) > 0) {
			if (defined('WFWAF_STORAGE_ENGINE') && WFWAF_STORAGE_ENGINE == 'mysqli') {
				return array('test' => false, 'infoOnly' => true, 'message' => implode(', ', $unwritable));
			}
			
			return array('test' => false, 'message' => implode(', ', $unwritable));
		}
		
		return true;
	}
	
	public function databaseVersion() {
		global $wpdb;
		$version = $wpdb->get_var("SELECT VERSION()");
		return array('test' => true, 'message' => $version);
	}

	public function userCanInsert() {
		return wfGrant::get()->insert;
	}
	
	public function userCanUpdate() {
		return wfGrant::get()->update;
	}

	public function userCanDelete() {
		return wfGrant::get()->delete;
	}

	public function userCanSelect() {
		return wfGrant::get()->select;
	}

	public function userCanCreate() {
		return wfGrant::get()->create;
	}

	public function userCanDrop() {
		return wfGrant::get()->drop;
	}

	public function userCanTruncate() {
		return wfGrant::get()->drop && wfGrant::get()->delete;
	}

	public function userCanAlter() {
		return wfGrant::get()->alter;
	}

	public function phpVersion()
	{
		return array(
			'test' => version_compare(phpversion(), $this->minVersion['PHP'], '>='),
			'message'  => phpversion(),
		);
	}
	
	public function configWritableSet() {
		global $wpdb;
		$show = $wpdb->hide_errors();
		$val = md5(time());
		wfConfig::set('configWritingTest', $val, wfConfig::DONT_AUTOLOAD);
		$testVal = wfConfig::get('configWritingTest');
		$wpdb->show_errors($show);
		return array(
			'test' => ($val === $testVal),
			'message' => __('Basic config writing', 'wordfence')
		);
	}
	public function configWritableSetSer() {
		global $wpdb;
		$show = $wpdb->hide_errors();
		$val = md5(time());
		wfConfig::set_ser('configWritingTest_ser', array($val), false, wfConfig::DONT_AUTOLOAD);
		$testVal = @array_shift(wfConfig::get_ser('configWritingTest_ser', array(), false));
		$wpdb->show_errors($show);
		return array(
			'test' => ($val === $testVal),
			'message' => __('Serialized config writing', 'wordfence')
		);
	}

	public function wafAutoPrepend() {
		return array('test' => true, 'infoOnly' => true, 'message' => (defined('WFWAF_AUTO_PREPEND') && WFWAF_AUTO_PREPEND ? __('Yes', 'wordfence') : __('No', 'wordfence')));
	}
	public function wafStorageEngine() {
		return array('test' => true, 'infoOnly' => true, 'message' => (defined('WFWAF_STORAGE_ENGINE') ? WFWAF_STORAGE_ENGINE : __('(default)', 'wordfence')));
	}
	private static function getStorageEngineDescription($storageEngine) {
		if ($storageEngine === null) {
			return __('None', 'wordfence');
		}
		else if (method_exists($storageEngine, 'getDescription')) {
			return $storageEngine->getDescription();
		}
		else {
			return __('Unknown (mixed plugin version)', 'wordfence');
		}
	}
	public function wafActiveStorageEngine() {
		return array('test' => true, 'infoOnly' => true, 'message' => self::getStorageEngineDescription(wfWAF::getSharedStorageEngine()));
	}
	public function wafLogPath() {
		$logPath = __('(not set)', 'wordfence');
		if (defined('WFWAF_LOG_PATH')) {
			$logPath = WFWAF_LOG_PATH;
			if (strpos($logPath, ABSPATH) === 0) {
				$logPath = '~/' . substr($logPath, strlen(ABSPATH));
			}
		}
		
		return array('test' => true, 'infoOnly' => true, 'message' => $logPath);
	}
	
	public function wafSubdirectoryInstall() {
		return array('test' => true, 'infoOnly' => true, 'message' => (defined('WFWAF_SUBDIRECTORY_INSTALL') && WFWAF_SUBDIRECTORY_INSTALL ? __('Yes', 'wordfence') : __('No', 'wordfence')));
	}
	
	public function wafAutoPrependFilePath() {
		$path = wordfence::getWAFBootstrapPath();
		if (!file_exists($path)) {
			$path = '';
		}
		return array('test' => true, 'infoOnly' => true, 'message' => $path);
	}
	
	public function wafFilePermissions() {
		if (defined('WFWAF_LOG_FILE_MODE')) {
			return array('test' => true, 'infoOnly' => true, 'message' => sprintf(/* translators: Unix file permissions in octal (example 0777). */ __('%s - using constant', 'wordfence'), str_pad(decoct(WFWAF_LOG_FILE_MODE), 4, '0', STR_PAD_LEFT)));
		}
		
		if (defined('WFWAF_LOG_PATH')) {
			$template = rtrim(WFWAF_LOG_PATH, '/') . '/template.php';
			if (file_exists($template)) {
				$stat = @stat($template);
				if ($stat !== false) {
					$mode = $stat[2];
					$updatedMode = 0600;
					if (($mode & 0020) == 0020) {
						$updatedMode = $updatedMode | 0060;
					}
					return array('test' => true, 'infoOnly' => true, 'message' => sprintf(/* translators: Unix file permissions in octal (example 0777). */ __('%s - using template', 'wordfence'), str_pad(decoct($updatedMode), 4, '0', STR_PAD_LEFT)));
				}
			}
		}
		return array('test' => true, 'infoOnly' => true, 'message' => __('0660 - using default', 'wordfence'));
	}
	
	public function wafRecentlyRemoved() {
		$removalHistory = wfConfig::getJSON('diagnosticsWflogsRemovalHistory', array());
		if (empty($removalHistory)) {
			return array('test' => true, 'infoOnly' => true, 'message' => __('None', 'wordfence'));
		}
		
		$message = array();
		foreach ($removalHistory as $r) {
			$m = wfUtils::formatLocalTime('M j, Y', $r[0]) . ': (' . count($r[1]) . ')';
			$r[1] = array_filter($r[1], array($this, '_filterOutNestedEntries'));
			$m .= ' ' . implode(', ', array_slice($r[1], 0, 5));
			if (count($r[1]) > 5) {
				$m .= ', ...';
			}
			$message[] = $m;
		}
		
		return array('test' => true, 'infoOnly' => true, 'message' => implode("\n", $message));
	}
	
	private function _filterOutNestedEntries($a) {
		return !is_array($a);
	}

	public function processOwner() {
		$disabledFunctions = explode(',', ini_get('disable_functions'));

		if (is_callable('posix_geteuid')) {
			if (!is_callable('posix_getpwuid') || in_array('posix_getpwuid', $disabledFunctions)) {
				return array(
					'test' => false,
					'message' => __('Unavailable', 'wordfence'),
				);
			}

			$processOwner = posix_getpwuid(posix_geteuid());
			if ($processOwner !== null)
			{
				return array(
					'test' => true,
					'message' => $processOwner['name'],
				);
			}
		}

		$usernameOrUserEnv = getenv('USERNAME') ? getenv('USERNAME') : getenv('USER');
		if (!empty($usernameOrUserEnv)) { //Check some environmental variable possibilities
			return array(
				'test' => true,
				'message' => $usernameOrUserEnv,
			);
		}

		$currentUser = get_current_user();
		if (!empty($currentUser)) { //php.net comments indicate on Windows this returns the process owner rather than the file owner
			return array(
				'test' => true,
				'message' => $currentUser,
			);
		}

		if (!empty($_SERVER['LOGON_USER'])) { //Last resort for IIS since POSIX functions are unavailable, Source: https://msdn.microsoft.com/en-us/library/ms524602(v=vs.90).aspx
			return array(
				'test' => true,
				'message' => $_SERVER['LOGON_USER'],
			);
		}

		return array(
			'test' => false,
			'message' => __('Unknown', 'wordfence'),
		);
	}

	public function hasOpenSSL() {
		return is_callable('openssl_open');
	}
	
	public function openSSLVersion() {
		if (!function_exists('openssl_verify') || !defined('OPENSSL_VERSION_NUMBER') || !defined('OPENSSL_VERSION_TEXT')) {
			return false;
		}
		$compare = wfVersionCheckController::shared()->checkOpenSSLVersion();
		return array(
			'test' => $compare == wfVersionCheckController::VERSION_COMPATIBLE,
			'message'  => OPENSSL_VERSION_TEXT . ' (0x' . dechex(OPENSSL_VERSION_NUMBER) . ')',
		);
	}

	public function hasCurl() {
		if (!is_callable('curl_version')) {
			return false;
		}
		$version = curl_version();
		return array(
			'test' => version_compare($version['version'], $this->minVersion['cURL'], '>='),
			'message'  => $version['version'] . ' (0x' . dechex($version['version_number']) . ')',
		);
	}
	
	public function curlFeatures() {
		if (!is_callable('curl_version')) {
			return false;
		}
		$version = curl_version();
		return array(
			'test' => true,
			'message'  => '0x' . dechex($version['features']),
			'infoOnly' => true,
		);
	}
	
	public function curlHost() {
		if (!is_callable('curl_version')) {
			return false;
		}
		$version = curl_version();
		return array(
			'test' => true,
			'message'  => $version['host'],
			'infoOnly' => true,
		);
	}
	
	public function curlProtocols() {
		if (!is_callable('curl_version')) {
			return false;
		}
		$version = curl_version();
		return array(
			'test' => true,
			'message'  => implode(', ', $version['protocols']),
			'infoOnly' => true,
		);
	}
	
	public function curlSSLVersion() {
		if (!is_callable('curl_version')) {
			return false;
		}
		$version = curl_version();
		return array(
			'test' => true,
			'message'  => $version['ssl_version'],
			'infoOnly' => true,
		);
	}
	
	public function curlLibZVersion() {
		if (!is_callable('curl_version')) {
			return false;
		}
		$version = curl_version();
		return array(
			'test' => true,
			'message'  => $version['libz_version'],
			'infoOnly' => true,
		);
	}
	
	public function displayErrors() {
		if (!is_callable('ini_get')) {
			return false;
		}
		$value = ini_get('display_errors');
		$isOn = strtolower($value) == 'on' || $value == 1;
		return array(
			'test' => !$isOn,
			'message'  => $isOn ? __('On', 'wordfence') : __('Off', 'wordfence'),
			'infoOnly' => true,
		);
	}

	public function connectToServer1() {
		return $this->_connectToServer('http');
	}

	public function connectToServer2() {
		return $this->_connectToServer('https');
	}

	public function _connectToServer($protocol) {
		$cronURL = admin_url('admin-ajax.php');
		$cronURL = preg_replace('/^(https?:\/\/)/i', '://noc1.wordfence.com/scanptest/', $cronURL);
		$cronURL .= '?action=wordfence_doScan&isFork=0&cronKey=47e9d1fa6a675b5999999333';
		$cronURL = $protocol . $cronURL;
		$result = wp_remote_post($cronURL, array(
			'timeout' => 10, //Must be less than max execution time or more than 2 HTTP children will be occupied by scan
			'blocking' => true, //Non-blocking seems to block anyway, so we use blocking
			// This causes cURL to throw errors in some versions since WordPress uses its own certificate bundle ('CA certificate set, but certificate verification is disabled')
			// 'sslverify' => false,
			'headers' => array()
			));
		if( (! is_wp_error($result)) && $result['response']['code'] == 200 && strpos($result['body'], "scanptestok") !== false){
			return true;
		}

		$detail = '';
		if (is_wp_error($result)) {
			$message = __('wp_remote_post() test to noc1.wordfence.com failed! Response was: ', 'wordfence') . $result->get_error_message();
		}
		else {
			$message = __('wp_remote_post() test to noc1.wordfence.com failed! Response was: ', 'wordfence') . $result['response']['code'] . " " . $result['response']['message'] . "\n";
			$message .= __('This likely means that your hosting provider is blocking requests to noc1.wordfence.com or has set up a proxy that is not behaving itself.', 'wordfence') . "\n";
			if (isset($result['http_response']) && is_object($result['http_response']) && method_exists($result['http_response'], 'get_response_object') && is_object($result['http_response']->get_response_object()) && property_exists($result['http_response']->get_response_object(), 'raw')) {
				$detail = str_replace("\r\n", "\n", $result['http_response']->get_response_object()->raw);
			}
		}

		return array(
			'test' => false,
			'message' => $message,
			'detail' => $detail,
		);
	}
	
	public function connectToSelf() {
		$adminAJAX = admin_url('admin-ajax.php?action=wordfence_testAjax');
		$result = wp_remote_post($adminAJAX, array(
			'timeout' => 10, //Must be less than max execution time or more than 2 HTTP children will be occupied by scan
			'blocking' => true, //Non-blocking seems to block anyway, so we use blocking
			'headers' => array()
		));
		
		if ((!is_wp_error($result)) && $result['response']['code'] == 200 && strpos($result['body'], "WFSCANTESTOK") !== false) {
			$host = parse_url($adminAJAX, PHP_URL_HOST);
			if ($host !== null) {
				$ips = wfUtils::resolveDomainName($host);
				$ips = implode(', ', $ips);
				return array('test' => true, 'message' => sprintf('OK - %s', $ips));
			}
			return true;
		}
		
		$detail = '';
		if (is_wp_error($result)) {
			$message = __('wp_remote_post() test back to this server failed! Response was: ', 'wordfence') . $result->get_error_message();
		}
		else {
			$message = __('wp_remote_post() test back to this server failed! Response was: ', 'wordfence') . $result['response']['code'] . " " . $result['response']['message'] . "\n";
			$message .= __('This additional info may help you diagnose the issue. The response headers we received were:', 'wordfence') . "\n";
			if (isset($result['http_response']) && is_object($result['http_response']) && method_exists($result['http_response'], 'get_response_object') && is_object($result['http_response']->get_response_object()) && property_exists($result['http_response']->get_response_object(), 'raw')) {
				$detail = str_replace("\r\n", "\n", $result['http_response']->get_response_object()->raw);
			}
		}
		
		return array(
			'test' => false,
			'message' => $message,
			'detail' => $detail,
		);
	}
	
	public function serverIP() {
		$serverIPs = wfUtils::serverIPs();
		return array(
			'test' => true,
			'infoOnly' => true,
			'message' => implode(',', $serverIPs),
		);
	}

	public function howGetIPs()
	{
		$howGet = wfConfig::get('howGetIPs', false);
		if ($howGet) {
			if (empty($_SERVER[$howGet])) {
				return array(
					'test' => false,
					'message' => sprintf(/* translators: PHP super global key. */ __('We cannot read $_SERVER[%s]', 'wordfence'), $howGet),
				);
			}
			return array(
				'test' => true,
				'message' => $howGet,
			);
		}
		foreach (array('HTTP_CF_CONNECTING_IP', 'HTTP_X_REAL_IP', 'HTTP_X_FORWARDED_FOR') as $test) {
			if (!empty($_SERVER[$test])) {
				return array(
					'test' => false,
					'message' => __('Should be: ', 'wordfence') . $test
				);
			}
		}
		return array(
			'test' => true,
			'message' => 'REMOTE_ADDR',
		);
	}
	
	public function serverTime() {
		return array(
			'test' => true,
			'infoOnly' => true,
			'message' => date('Y-m-d H:i:s', time()) . ' UTC',
		);
	}
	
	public function wfTime() {
		try {
			$api = new wfAPI(wfConfig::get('apiKey'), wfUtils::getWPVersion());
			$response = $api->call('timestamp');
			if (!is_array($response) || !isset($response['timestamp'])) {
				throw new Exception('Unexpected payload returned');
			}
		}
		catch (Exception $e) {
			return array(
				'test' => true,
				'infoOnly' => true,
				'message' => '-',
			);
		}
		
		return array(
			'test' => true,
			'infoOnly' => true,
			'message' => date('Y-m-d H:i:s', $response['timestamp']) . ' UTC',
		);
	}
	
	public function wfTimeOffset() {
		$delta = wfUtils::normalizedTime() - time();
		return array(
			'test' => true,
			'infoOnly' => true,
			'message' => ($delta < 0 ? '-' : '+') . ' ' . wfUtils::makeDuration(abs($delta), true),
		);
	}
	
	public function ntpTimeOffset() {
		if (class_exists('WFLSPHP52Compatability')) {
			$time = WFLSPHP52Compatability::ntp_time();
			if ($time === false) {
				return array(
					'test' => true,
					'infoOnly' => true,
					'message' => __('Blocked', 'wordfence'),
				);
			}
			
			$delta = $time - time();
			return array(
				'test' => true,
				'infoOnly' => true,
				'message' => ($delta < 0 ? '-' : '+') . ' ' . wfUtils::makeDuration(abs($delta), true),
			);
		}
		
		return array(
			'test' => true,
			'infoOnly' => true,
			'message' => '-',
		);
	}

	public function ntpStatus() {
		$maxFailures = \WordfenceLS\Controller_Time::FAILURE_LIMIT;
		$cronDisabled = \WordfenceLS\Controller_Settings::shared()->is_ntp_cron_disabled($failureCount);
		if ($cronDisabled) {
			$constant = \WordfenceLS\Controller_Settings::shared()->is_ntp_disabled_via_constant();
			$status = __('Disabled ', 'wordfence');
			if ($constant) {
				$status .= __('(WORDFENCE_LS_DISABLE_NTP)', 'wordfence');
			}
			else if ($failureCount > 0) {
				$status .= __('(failures exceeded limit)', 'wordfence');
			}
			else {
				$status .= __('(settings)', 'wordfence');
			}
		}
		else {
			$status = __('Enabled', 'wordfence');
			if ($failureCount > 0) {
				$remainingAttempts = $maxFailures - $failureCount;
				$status .= sprintf(__(' (%d of %d attempts remaining)', 'wordfence'), $remainingAttempts, $maxFailures);
			}
		}
		return array(
			'test' => true,
			'infoOnly' => true,
			'message' => $status
		);
	}
	
	public function timeSourceInUse() {
		if (class_exists('WFLSPHP52Compatability')) {
			$time = WFLSPHP52Compatability::ntp_time();
			if (WFLSPHP52Compatability::using_ntp_time()) {
				return array(
					'test' => true,
					'infoOnly' => true,
					'message' => __('NTP', 'wordfence'),
				);
			}
			else if (WFLSPHP52Compatability::using_wf_time()) {
				return array(
					'test' => true,
					'infoOnly' => true,
					'message' => __('Wordfence Network', 'wordfence'),
				);
			}
			
			return array(
				'test' => true,
				'infoOnly' => true,
				'message' => __('Server Time', 'wordfence'),
			);
		}
		
		return array(
			'test' => true,
			'infoOnly' => true,
			'message' => '-',
		);
	}
	
	public function wpTimeZone() {
		$tz = get_option('timezone_string');
		if (empty($tz)) {
			$offset = get_option('gmt_offset');
			$tz = 'UTC' . ($offset >= 0 ? '+' . $offset : $offset);
		}
		
		return array(
			'test' => true,
			'infoOnly' => true,
			'message' => $tz,
		);
	}
}