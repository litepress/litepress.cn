<?php
if (!defined('WORDFENCE_VERSION')) { exit; }

require_once(dirname(__FILE__) . '/../../vendor/autoload.php');

use GeoIp2\Database\Reader;

class wfGeoIP2 {
	const DB_WFLOGS = 'wflogs';
	const DB_BUNDLED = 'bundled';
	
	static $_shared = array();
	
	private $_reader;
	
	/**
	 * Returns the singleton wfGeoIP2, optionally forcing use of a specific database.
	 *
	 * @return wfGeoIP2|bool
	 */
	public static function shared($whichDB = false) {
		try {
			if (file_exists(WFWAF_LOG_PATH . '/GeoLite2-Country.mmdb') && ($whichDB === false || $whichDB == self::DB_WFLOGS)) {
				if (isset(self::$_shared[self::DB_WFLOGS])) {
					return self::$_shared[self::DB_WFLOGS];
				}
				
				$reader = new Reader(WFWAF_LOG_PATH . '/GeoLite2-Country.mmdb');
				self::$_shared[self::DB_WFLOGS] = new wfGeoIP2($reader);
				return self::$_shared[self::DB_WFLOGS];
			}
		}
		catch (Exception $e) {
			//Fall through to bundled copy
		}
		
		if ($whichDB == self::DB_WFLOGS) {
			return false;
		}
		
		if (isset(self::$_shared[self::DB_BUNDLED])) {
			return self::$_shared[self::DB_BUNDLED];
		}
		$reader = new Reader(__DIR__ . '/../../lib/GeoLite2-Country.mmdb'); //Can throw, but we don't catch it here
		self::$_shared[self::DB_BUNDLED] = new wfGeoIP2($reader);
		return self::$_shared[self::DB_BUNDLED];
	}
	
	/**
	 * Automatically uses the wflogs version of the DB if present, otherwise uses the bundled one.
	 * 
	 * @param \GeoIp2\Database\Reader $reader If provided, uses the reader passed instead.
	 */
	public function __construct($reader = false) {
		if ($reader !== false) {
			$this->_reader = $reader;
			return;
		}
		
		try {
			if (file_exists(WFWAF_LOG_PATH . '/GeoLite2-Country.mmdb')) {
				$this->_reader = new Reader(WFWAF_LOG_PATH . '/GeoLite2-Country.mmdb');
				return;
			}
		}
		catch (Exception $e) {
			//Fall through to bundled copy
		}
		
		$this->_reader = new Reader(__DIR__ . '/../../lib/GeoLite2-Country.mmdb'); //Can throw, but we don't catch it because it means the installation is likely corrupt and needs fixed anyway
	}
	
	/**
	 * Returns the database version in use. This is the timestamp of when it was packaged.
	 * 
	 * @return null|int
	 */
	public function version() {
		try {
			return $this->_reader->metadata()->buildEpoch;
		}
		catch (Exception $e) {
			//Fall through
		}
		return null;
	}
	
	/**
	 * Returns the country code for the IP if known.
	 * 
	 * @param string $ip
	 * @return null|string
	 */
	public function countryCode($ip) {
		try {
			$record = $this->_reader->country($ip);
			return $record->country->isoCode;
		}
		catch (Exception $e) {
			//Fall through
		}
		return null;
	}
}
