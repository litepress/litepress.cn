<?php
if (defined('WFWAF_VERSION') && !defined('WFWAF_RUN_COMPLETE')) {

interface wfWAFStorageInterface {
	const IP_BLOCKS_ALL = PHP_INT_MAX;
	const IP_BLOCKS_SINGLE = 1; //1 << 0
	const IP_BLOCKS_BLACKLIST = 2; //1 << 1
	
	public function hasPreviousAttackData($olderThan);

	public function hasNewerAttackData($newerThan);

	public function getAttackData();

	public function getAttackDataArray();

	public function getNewestAttackDataArray($newerThan);

	public function truncateAttackData();

	/**
	 * @param array $failedRules
	 * @param string $failedParamKey
	 * @param string $failedParamValue
	 * @param wfWAFRequestInterface $request
	 * @param mixed $_
	 * @return mixed
	 */
	public function logAttack($failedRules, $failedParamKey, $failedParamValue, $request, $_ = null);

	/**
	 * @param int $timestamp
	 * @param string $ip
	 * @param bool $ssl
	 * @param array $failedRuleIDs
	 * @param wfWAFRequestInterface|string $request
	 * @param mixed $_
	 * @return mixed
	 */
//	public function logAttack($timestamp, $ip, $ssl, $failedRuleIDs, $request, $_ = null);

	/**
	 * @param float $timestamp
	 * @param string $ip
	 * @return mixed
	 */
	public function blockIP($timestamp, $ip);

	public function isIPBlocked($ip);
	
	public function purgeIPBlocks($types = wfWAFStorageInterface::IP_BLOCKS_ALL);

	public function getConfig($key, $default = null, $category = '');

	public function setConfig($key, $value, $category = '');

	public function unsetConfig($key, $category = '');

	public function uninstall();
	
	//optional public function fileList();

	public function isInLearningMode();

	public function isDisabled();

	public function getRulesDSLCacheFile();

	public function isAttackDataFull();

	public function vacuum();

	public function getRules();

	public function setRules($rules);

	public function needsInitialRules();

	public function getDescription();
}
}
