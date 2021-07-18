<?php

class wfVersionCheckController {
	const VERSION_COMPATIBLE = 'compatible';
	const VERSION_DEPRECATED = 'deprecated';
	const VERSION_UNSUPPORTED = 'unsupported';
	
	const PHP_DEPRECATING = '5.5.0'; //When greater than PHP_MINIMUM, will issue a discontinuing warning the first time we check it and find a version less than this (also applies to the other similar constant pairs)
	const PHP_MINIMUM = '5.3.0'; //The currently supported minimum
	
	const OPENSSL_DEPRECATING = '1.0.1'; 
	const OPENSSL_MINIMUM = '1.0.1';
	
	const WORDPRESS_DEPRECATING = '3.9.0';
	const WORDPRESS_MINIMUM = '3.9.0';
	
	const OPENSSL_DEV = 0;
	//Betas are 1-14
	const OPENSSL_RELEASE = 15;
	
	public static function shared() {
		static $_shared = false;
		if ($_shared === false) {
			$_shared = new wfVersionCheckController();
		}
		return $_shared;
	}
	
	/**
	 * Returns whether or not all version checks are successful. If any check returns a value other than VERSION_COMPATIBLE, this returns false.
	 * 
	 * @return bool
	 */
	public function checkVersions() {
		return ($this->checkPHPVersion() == self::VERSION_COMPATIBLE) && ($this->checkOpenSSLVersion() == self::VERSION_COMPATIBLE) && ($this->checkWordPressVersion() == self::VERSION_COMPATIBLE);
	}
	
	/**
	 * Does the same thing as checkVersions but also triggers display of the corresponding warnings.
	 * 
	 * @return bool
	 */
	public function checkVersionsAndWarn() {
		//PHP
		$php = $this->checkPHPVersion();
		if ($php == self::VERSION_DEPRECATED) {
			$this->_alertEmail(
				'phpVersionCheckDeprecationEmail_' . self::PHP_DEPRECATING,
				__('PHP version too old', 'wordfence'),
				sprintf(
					/* translators: 1. PHP version. 2. PHP version. */
					__('Your site is using a PHP version (%1$s) that will no longer be supported by Wordfence in an upcoming release and needs to be updated. We recommend using the newest version of PHP available but will currently support PHP versions as old as %2$s. Version checks are run regularly, so if you have successfully updated, you can dismiss this notice or check that the update has taken effect later.', 'wordfence'),
					phpversion(),
					self::PHP_DEPRECATING
				)
				. ' ' .
				sprintf(__('Learn More: %s', 'wordfence'), wfSupportController::esc_supportURL(wfSupportController::ITEM_VERSION_PHP))
			);
			
			$this->_adminNotice(
				'phpVersionCheckDeprecationNotice_' . self::PHP_DEPRECATING,
				'phpVersionCheck',
				wp_kses(sprintf(
					/* translators: 1. PHP version. 2. PHP version. */
					__('<strong>WARNING: </strong> Your site is using a PHP version (%1$s) that will no longer be supported by Wordfence in an upcoming release and needs to be updated. We recommend using the newest version of PHP available but will currently support PHP versions as old as %2$s. Version checks are run regularly, so if you have successfully updated, you can dismiss this notice or check that the update has taken effect later.', 'wordfence'),
					phpversion(),
					self::PHP_DEPRECATING
				), array('a'=>array('href'=>array(), 'target'=>array(), 'rel'=>array()))) . ' <a href="' . wfSupportController::esc_supportURL(wfSupportController::ITEM_VERSION_PHP) . '" target="_blank" rel="noopener noreferrer">' . esc_html__('Learn More', 'wordfence') . '</a>'
			);
		}
		else if ($php == self::VERSION_UNSUPPORTED) {
			$this->_alertEmail(
				'phpVersionCheckUnsupportedEmail_' . self::PHP_MINIMUM,
				__('PHP version too old', 'wordfence'),
				sprintf(
					/* translators: 1. PHP version. 2. PHP version. */
					__('Your site is using a PHP version (%1$s) that is no longer supported by Wordfence and needs to be updated. We recommend using the newest version of PHP available but will currently support PHP versions as old as %2$s. Version checks are run regularly, so if you have successfully updated, you can dismiss this notice or check that the update has taken effect later.', 'wordfence'),
					phpversion(),
					self::PHP_DEPRECATING
				) . ' ' . sprintf(/* translators: Support URL. */ __('Learn More: %s', 'wordfence'), wfSupportController::esc_supportURL(wfSupportController::ITEM_VERSION_PHP))
			);
			
			$this->_adminNotice(
				'phpVersionCheckUnsupportedNotice_' . self::PHP_MINIMUM,
				'phpVersionCheck',
				wp_kses(sprintf(
					/* translators: 1. PHP version. 2. PHP version. */
					__('<strong>WARNING: </strong> Your site is using a PHP version (%1$s) that is no longer supported by Wordfence and needs to be updated. We recommend using the newest version of PHP available but will currently support PHP versions as old as %2$s. Version checks are run regularly, so if you have successfully updated, you can dismiss this notice or check that the update has taken effect later.', 'wordfence'),
					phpversion(),
					self::PHP_DEPRECATING
				), array('a'=>array('href'=>array(), 'target'=>array(), 'rel'=>array()))) . ' <a href="' . wfSupportController::esc_supportURL(wfSupportController::ITEM_VERSION_PHP) . '" target="_blank" rel="noopener noreferrer">' . esc_html__('Learn More', 'wordfence') . '</a>'
			);
		}
		else {
			wfAdminNoticeQueue::removeAdminNotice(false, 'phpVersionCheck');
		}
		
		if (wfAdminNoticeQueue::hasNotice('phpVersionCheck')) {
			return false;
		}
		
		//OpenSSL
		wfAdminNoticeQueue::removeAdminNotice(false, 'opensslVersionCheck');
		/*$openssl = $this->checkOpenSSLVersion();
		if ($openssl == self::VERSION_DEPRECATED) {
			$this->_alertEmail(
				'opensslVersionCheckDeprecationEmail_' . self::OPENSSL_DEPRECATING,
				__('OpenSSL version too old', 'wordfence'),
				sprintf(__('Your site is using an OpenSSL version (%s) that will no longer be supported by Wordfence in an upcoming release and needs to be updated. We recommend using the newest version of OpenSSL but will currently support OpenSSL versions as old as %s. Version checks are run regularly, so if you have successfully updated, you can dismiss this notice or check that the update has taken effect later.', 'wordfence'), self::openssl_make_text_version(), self::OPENSSL_DEPRECATING) . ' ' . sprintf(__('Learn More: %s', 'wordfence'), wfSupportController::esc_supportURL(wfSupportController::ITEM_VERSION_OPENSSL))
			);
			
			$this->_adminNotice(
				'opensslVersionCheckDeprecationNotice_' . self::OPENSSL_DEPRECATING,
				'opensslVersionCheck',
				sprintf(__('<strong>WARNING: </strong> Your site is using an OpenSSL version (%s) that will no longer be supported by Wordfence in an upcoming release and needs to be updated. We recommend using the newest version of OpenSSL but will currently support OpenSSL versions as old as %s. Version checks are run regularly, so if you have successfully updated, you can dismiss this notice or check that the update has taken effect later.', 'wordfence'), self::openssl_make_text_version(), self::OPENSSL_DEPRECATING) . ' <a href="' . wfSupportController::esc_supportURL(wfSupportController::ITEM_VERSION_OPENSSL) . '" target="_blank" rel="noopener noreferrer">' . __('Learn More', 'wordfence') . '</a>'
			);
			
			return false;
		}
		else if ($openssl == self::VERSION_UNSUPPORTED) {
			$this->_alertEmail(
				'opensslVersionCheckUnsupportedEmail_' . self::OPENSSL_MINIMUM,
				__('OpenSSL version too old', 'wordfence'),
				sprintf(__('Your site is using an OpenSSL version (%s) that is no longer supported by Wordfence and needs to be updated. We recommend using the newest version of OpenSSL but will currently support OpenSSL versions as old as %s. Version checks are run regularly, so if you have successfully updated, you can dismiss this notice or check that the update has taken effect later.', 'wordfence'), self::openssl_make_text_version(), self::OPENSSL_DEPRECATING) . ' ' . sprintf(__('Learn More: %s', 'wordfence'), wfSupportController::esc_supportURL(wfSupportController::ITEM_VERSION_OPENSSL))
			);
			
			$this->_adminNotice(
				'opensslVersionCheckUnsupportedNotice_' . self::OPENSSL_MINIMUM,
				'opensslVersionCheck',
				sprintf(__('<strong>WARNING: </strong> Your site is using an OpenSSL version (%s) that is no longer supported by Wordfence and needs to be updated. We recommend using the newest version of OpenSSL but will currently support OpenSSL versions as old as %s. Version checks are run regularly, so if you have successfully updated, you can dismiss this notice or check that the update has taken effect later.', 'wordfence'), self::openssl_make_text_version(), self::OPENSSL_DEPRECATING) . ' <a href="' . wfSupportController::esc_supportURL(wfSupportController::ITEM_VERSION_OPENSSL) . '" target="_blank" rel="noopener noreferrer">' . __('Learn More', 'wordfence') . '</a>'
			);
			
			return false;
		}
		else {
			wfAdminNoticeQueue::removeAdminNotice(false, 'opensslVersionCheck');
		}
		
		if (wfAdminNoticeQueue::hasNotice('opensslVersionCheck')) {
			return false;
		}*/
		
		//WordPress
		$wordpress = $this->checkWordPressVersion();
		if ($wordpress == self::VERSION_DEPRECATED) {
			require(ABSPATH . 'wp-includes/version.php'); /** @var string $wp_version */
			
			$this->_alertEmail(
				'wordpressVersionCheckDeprecationEmail_' . self::WORDPRESS_DEPRECATING,
				__('WordPress version too old', 'wordfence'),
				sprintf(
					/* translators: 1. WordPress version. 2. WordPress version. */
					__('Your site is using a WordPress version (%1$s) that will no longer be supported by Wordfence in an upcoming release and needs to be updated. We recommend using the newest version of WordPress but will currently support WordPress versions as old as %2$s. Version checks are run regularly, so if you have successfully updated, you can dismiss this notice or check that the update has taken effect later.', 'wordfence'),
					$wp_version,
					self::WORDPRESS_DEPRECATING
				) . ' ' . sprintf(__('Learn More: %s', 'wordfence'), wfSupportController::esc_supportURL(wfSupportController::ITEM_VERSION_WORDPRESS))
			);
			
			$this->_adminNotice(
				'wordpressVersionCheckDeprecationNotice_' . self::WORDPRESS_DEPRECATING,
				'wordpressVersionCheck',
				wp_kses(sprintf(
					/* translators: 1. WordPress version. 2. WordPress version. */
					__('<strong>WARNING: </strong> Your site is using a WordPress version (%1$s) that will no longer be supported by Wordfence in an upcoming release and needs to be updated. We recommend using the newest version of WordPress but will currently support WordPress versions as old as %2$s. Version checks are run regularly, so if you have successfully updated, you can dismiss this notice or check that the update has taken effect later.', 'wordfence'),
					$wp_version,
					self::WORDPRESS_DEPRECATING
				), array('a'=>array('href'=>array(), 'target'=>array(), 'rel'=>array()))) . ' <a href="' . wfSupportController::esc_supportURL(wfSupportController::ITEM_VERSION_WORDPRESS) . '" target="_blank" rel="noopener noreferrer">' . esc_html__('Learn More', 'wordfence') . '</a>'
			);
		}
		else if ($wordpress == self::VERSION_UNSUPPORTED) {
			require(ABSPATH . 'wp-includes/version.php'); /** @var string $wp_version */
			
			$this->_alertEmail(
				'wordpressVersionCheckUnsupportedEmail_' . self::WORDPRESS_MINIMUM,
				__('WordPress version too old', 'wordfence'),
				sprintf(
					/* translators: 1. WordPress version. 2. WordPress version. */
					__('Your site is using a WordPress version (%1$s) that is no longer supported by Wordfence and needs to be updated. We recommend using the newest version of WordPress but will currently support WordPress versions as old as %2$s. Version checks are run regularly, so if you have successfully updated, you can dismiss this notice or check that the update has taken effect later.', 'wordfence'), $wp_version, self::WORDPRESS_DEPRECATING) . ' ' . sprintf(__('Learn More: %s', 'wordfence'), wfSupportController::esc_supportURL(wfSupportController::ITEM_VERSION_WORDPRESS))
			);
			
			$this->_adminNotice(
				'wordpressVersionCheckUnsupportedNotice_' . self::WORDPRESS_MINIMUM,
				'wordpressVersionCheck',
				wp_kses(sprintf(
					/* translators: 1. WordPress version. 2. WordPress version. */
					__('<strong>WARNING: </strong> Your site is using a WordPress version (%1$s) that is no longer supported by Wordfence and needs to be updated. We recommend using the newest version of WordPress but will currently support WordPress versions as old as %2$s. Version checks are run regularly, so if you have successfully updated, you can dismiss this notice or check that the update has taken effect later.', 'wordfence'), $wp_version, self::WORDPRESS_DEPRECATING), array('a'=>array('href'=>array(), 'target'=>array(), 'rel'=>array()))) . ' <a href="' . wfSupportController::esc_supportURL(wfSupportController::ITEM_VERSION_WORDPRESS) . '" target="_blank" rel="noopener noreferrer">' . esc_html__('Learn More', 'wordfence') . '</a>'
			);
		}
		else {
			wfAdminNoticeQueue::removeAdminNotice(false, 'wordpressVersionCheck');
		}
		
		if (wfAdminNoticeQueue::hasNotice('wordpressVersionCheck')) {
			return false;
		}
		
		return true;
	}
	
	private function _alertEmail($checkKey, $title, $body) {
		if (!wfConfig::get($checkKey)) {
			wordfence::alert($title, $body, wfUtils::getIP());
			wfConfig::set($checkKey, true);
		}
	}
	
	private function _adminNotice($checkKey, $noticeKey, $message) {
		if (!wfConfig::get($checkKey)) {
			wfAdminNoticeQueue::addAdminNotice(wfAdminNotice::SEVERITY_CRITICAL, $message, $noticeKey);
			wfConfig::set($checkKey, true);
		}
	}
	
	/**
	 * Returns whether or not the PHP version meets our minimum requirement or is a version being deprecated.
	 * 
	 * @return string One of the VERSION_ constants.
	 */
	public function checkPHPVersion() {
		if (version_compare(phpversion(), self::PHP_DEPRECATING, '>=')) {
			return self::VERSION_COMPATIBLE;
		}
		
		if (self::PHP_DEPRECATING != self::PHP_MINIMUM && version_compare(phpversion(), self::PHP_MINIMUM, '>=')) {
			return self::VERSION_DEPRECATED;
		}
		
		return self::VERSION_UNSUPPORTED;
	}
	
	/**
	 * Returns whether or not the OpenSSL version meets our minimum requirement or is a version being deprecated.
	 *
	 * @return string One of the VERSION_ constants.
	 */
	public function checkOpenSSLVersion() {
		if (self::openssl_version_compare(self::OPENSSL_DEPRECATING) <= 0) {
			return self::VERSION_COMPATIBLE;
		}
		
		if (self::OPENSSL_DEPRECATING != self::OPENSSL_MINIMUM && self::openssl_version_compare(self::OPENSSL_MINIMUM) <= 0) {
			return self::VERSION_DEPRECATED;
		}
		
		return self::VERSION_UNSUPPORTED;
	}
	
	/**
	 * Returns whether or not the WordPress version meets our minimum requirement or is a version being deprecated.
	 *
	 * @return string One of the VERSION_ constants.
	 */
	public function checkWordPressVersion() {
		require(ABSPATH . 'wp-includes/version.php'); /** @var string $wp_version */
		
		if (version_compare($wp_version, self::WORDPRESS_DEPRECATING, '>=')) {
			return self::VERSION_COMPATIBLE;
		}
		
		if (self::WORDPRESS_DEPRECATING != self::WORDPRESS_MINIMUM && version_compare($wp_version, self::WORDPRESS_MINIMUM, '>=')) {
			return self::VERSION_DEPRECATED;
		}
		
		return self::VERSION_UNSUPPORTED;
	}
	
	/**
	 * Utility Functions
	 */
	
	/**
	 * Returns whether or not the OpenSSL version is before, after, or equal to the equivalent text version string.
	 *
	 * @param string $compareVersion
	 * @param int $openSSLVersion A version number in the format OpenSSL uses.
	 * @param bool $allowDevBeta If true, dev and beta versions of $compareVersion are treated as equivalent to release versions despite having a lower version number.
	 * @return bool|int Returns -1 if $compareVersion is earlier, 0 if equal, 1 if later, and false if not a valid version string.
	 */
	public static function openssl_version_compare($compareVersion, $openSSLVersion = OPENSSL_VERSION_NUMBER, $allowDevBeta = true) {
		if (preg_match('/^(\d+)\.(\d+)\.(\d+)([a-z]*)((?:-dev|-beta\d\d?)?)/i', $compareVersion, $matches)) {
			$primary = 0; $major = 0; $minor = 0; $fixLetterIndexes = 0; $patch = self::OPENSSL_RELEASE;
			if (isset($matches[1])) { $primary = (int) $matches[1]; }
			if (isset($matches[2])) { $major = (int) $matches[2]; }
			if (isset($matches[3])) { $minor = (int) $matches[3]; }
			if (isset($matches[4]) && !empty($matches[4])) {
				$letters = str_split($matches[4]);
				foreach ($letters as $l) {
					$fixLetterIndexes += strpos('abcdefghijklmnopqrstuvwxyz', strtolower($l)) + 1;
				}
			}
			if (isset($matches[5]) && !empty($matches[5])) {
				if (preg_match('/^-beta(\d+)$/i', $matches[5], $betaMatches)) {
					$patch = (int) $betaMatches[1];
				}
				else {
					$patch = self::OPENSSL_DEV;
				}
			}
			
			$compareOpenSSLVersion = self::openssl_make_number_version($primary, $major, $minor, $fixLetterIndexes, $patch);
			if ($allowDevBeta) {
				$compareOpenSSLVersion = $compareOpenSSLVersion >> 4;
				$openSSLVersion = $openSSLVersion >> 4;
			}
			
			if ($compareOpenSSLVersion < $openSSLVersion) { return -1; }
			else if ($compareOpenSSLVersion == $openSSLVersion) { return 0; }
			return 1;
		}
		
		return false;
	}
	
	/**
	 * Builds a number that can be compared to OPENSSL_VERSION_NUMBER from the parameters given. This is a modified
	 * version of the macro in the OpenSSL source.
	 *
	 * @param int $primary The '1' in 1.0.2g.
	 * @param int $major The '0' in 1.0.2g.
	 * @param int $minor The '2' in 1.0.2g.
	 * @param int $fixLetterIndexes The 'g' in 1.0.2g. This can potentially be multiple letters, in which case, all of the indexes are added.
	 * @param int $patch
	 * @return int
	 */
	public static function openssl_make_number_version($primary, $major, $minor, $fixLetterIndexes = 0, $patch = 0) {
		return ((($primary & 0xff) << 28) + (($major & 0xff) << 20) + (($minor & 0xff) << 12) + (($fixLetterIndexes & 0xff) << 4) + $patch);
	}
	
	/**
	 * Builds a text version of the OpenSSL version from a number-formatted one.
	 * 
	 * @param int $number
	 * @return string
	 */
	public static function openssl_make_text_version($number = OPENSSL_VERSION_NUMBER) {
		$primary = (($number >> 28) & 0xff);
		$major = (($number >> 20) & 0xff);
		$minor = (($number >> 12) & 0xff);
		$fix = (($number >> 4) & 0xff);
		$patch = ($number & 0xf); //0 is dev, 1-14 are betas, 15 is release
		
		$alphabet = str_split('abcdefghijklmnopqrstuvwxyz');
		$fixLetters = '';
		while ($fix > 26) {
			$fixLetters .= 'z';
			$fix -= 26;
		}
		if (array_key_exists($fix - 1, $alphabet)) {
			$fixLetters .= $alphabet[$fix - 1];
		}
		
		$version = "{$primary}.{$major}.{$minor}{$fixLetters}";
		
		if ($patch == self::OPENSSL_DEV) {
			$version .= '-dev';
		}
		else if ($patch == self::OPENSSL_RELEASE) {
			//Do nothing
		}
		else {
			$version .= '-beta' . $patch;
		}
		
		return $version;
	}
}