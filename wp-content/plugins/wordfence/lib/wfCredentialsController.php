<?php

class wfCredentialsController {
	const UNCACHED = 'uncached';
	const NOT_LEAKED = 'not-leaked';
	const LEAKED = 'leaked';
	
	const ALLOW_LEGACY_2FA_OPTION = 'allowLegacy2FA';
	const DISABLE_LEGACY_2FA_OPTION = 'disableLegacy2FA';
	
	public static function allowLegacy2FA() {
		return wfConfig::get(self::ALLOW_LEGACY_2FA_OPTION, false);
	}
	
	public static function useLegacy2FA() {
		if (!self::allowLegacy2FA()) {
			return false;
		}
		return !wfConfig::get(self::DISABLE_LEGACY_2FA_OPTION, false);
	}
	
	public static function hasOld2FARecords() {
		$twoFactorUsers = wfConfig::get_ser('twoFactorUsers', array());
		if (is_array($twoFactorUsers) && !empty($twoFactorUsers)) {
			foreach ($twoFactorUsers as &$t) {
				if ($t[3] == 'activated') {
					$user = new WP_User($t[0]);
					if ($user instanceof WP_User && $user->exists()) {
						return true;
					}
				}
			}
		}
		return false;
	}
	
	public static function hasNew2FARecords() {
		if (version_compare(phpversion(), '5.3', '>=') && class_exists('\WordfenceLS\Controller_DB')) {
			global $wpdb;
			$table = WFLSPHP52Compatability::secrets_table();
			return !!intval($wpdb->get_var("SELECT COUNT(*) FROM `{$table}`"));
		}
		return false;
	}
	
	/**
	 * Queries the API and returns whether or not the password exists in the breach database.
	 * 
	 * @param string $login
	 * @param string $password
	 * @return bool
	 */
	public static function isLeakedPassword($login, $password) {
		$sha1 = strtoupper(hash('sha1', $password));
		$prefix = substr($sha1, 0, 5);
		
		$ssl_verify = (bool) wfConfig::get('ssl_verify');
		$args = array(
			'timeout'    => 5,
			'user-agent' => "Wordfence.com UA " . (defined('WORDFENCE_VERSION') ? WORDFENCE_VERSION : '[Unknown version]'),
			'sslverify'  => $ssl_verify,
			'headers'	 => array('Referer' => false),
		);
		
		if (!$ssl_verify) { // Some versions of cURL will complain that SSL verification is disabled but the CA bundle was supplied.
			$args['sslcertificates'] = false;
		}
		
		$response = wp_remote_get(sprintf(WORDFENCE_BREACH_URL_BASE_SEC . "%s.txt", $prefix), $args);
		
		if (!is_wp_error($response)) {
			$data = wp_remote_retrieve_body($response);
			$lines = explode("\n", $data);
			foreach ($lines as $l) {
				$components = explode(":", $l);
				$teshSHA1 = $prefix . strtoupper($components[0]);
				if (hash_equals($sha1, $teshSHA1)) {
					return true;
				}
			}
		}
		
		return false;
	}
	
	/**
	 * Returns the transient key for the given user.
	 * 
	 * @param WP_User $user
	 * @return string
	 */
	protected static function _cachedCredentialStatusKey($user) {
		$key = 'wfcredentialstatus_' . $user->ID;
		return $key;
	}
	
	/**
	 * Returns the cached credential status for the given user: self::UNCACHED, self::NOT_LEAKED, or self::LEAKED.
	 * 
	 * @param WP_User $user
	 * @return string
	 */
	public static function cachedCredentialStatus($user) {
		$key = self::_cachedCredentialStatusKey($user);
		$value = get_transient($key);
		if ($value === false) {
			return self::UNCACHED;
		}
		
		$status = substr($value, 0, 1);
		if (strlen($value) > 1) {
			if (!hash_equals(substr($value, 1), hash('sha256', $user->user_pass))) { //Different hash but our clear function wasn't called so treat it as uncached
				return self::UNCACHED;
			}
		}
		
		if ($status) {
			return self::LEAKED;
		}
		return self::NOT_LEAKED;
	}
	
	/**
	 * Stores a cached leak value for the given user.
	 * 
	 * @param WP_User $user
	 * @param bool $isLeaked
	 */
	public static function setCachedCredentialStatus($user, $isLeaked) {
		$key = self::_cachedCredentialStatusKey($user);
		set_transient($key, ($isLeaked ? '1' : '0') . hash('sha256', $user->user_pass), 3600);
	}
	
	/**
	 * Clears the cache for the given user.
	 * 
	 * @param WP_User $user
	 */
	public static function clearCachedCredentialStatus($user) {
		$key = self::_cachedCredentialStatusKey($user);
		delete_transient($key);
	}
	
	/**
	 * Returns whether or not we've seen a successful login from $ip for the given user.
	 * 
	 * @param WP_User $user
	 * @param string $ip
	 * @return bool
	 */
	public static function hasPreviousLoginFromIP($user, $ip) {
		global $wpdb;
		$table_wfLogins = wfDB::networkTable('wfLogins');
		
		$id = property_exists($user, 'ID') ? $user->ID : 0;
		if ($id == 0) {
			return false;
		}
		
		$result = $wpdb->get_row($wpdb->prepare("SELECT id FROM {$table_wfLogins} WHERE action = 'loginOK' AND userID = %d AND IP = %s LIMIT 0,1", $id, wfUtils::inet_pton($ip)), ARRAY_A);
		if (is_array($result)) {
			return true;
		}
		
		$lastAdminLogin = wfConfig::get_ser('lastAdminLogin');
		if (is_array($lastAdminLogin) && isset($lastAdminLogin['userID']) && isset($lastAdminLogin['IP'])) {
			if ($lastAdminLogin['userID'] == $id && wfUtils::inet_pton($lastAdminLogin['IP']) == wfUtils::inet_pton($ip)) {
				return true;
			}
			return false;
		}
		
		//Final check -- if the IP recorded at plugin activation matches, let it through. This is __only__ checked when we don't have any other record of an admin login.
		$activatingIP = wfConfig::get('activatingIP');
		if (wfUtils::isValidIP($activatingIP)) {
			if (wfUtils::inet_pton($activatingIP) == wfUtils::inet_pton($ip)) {
				return true;
			}
		}
		
		return false;
	}
}