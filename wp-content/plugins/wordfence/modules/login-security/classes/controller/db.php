<?php

namespace WordfenceLS;

class Controller_DB {
	const TABLE_2FA_SECRETS = 'wfls_2fa_secrets';
	const TABLE_SETTINGS = 'wfls_settings';
	
	/**
	 * Returns the singleton Controller_DB.
	 *
	 * @return Controller_DB
	 */
	public static function shared() {
		static $_shared = null;
		if ($_shared === null) {
			$_shared = new Controller_DB();
		}
		return $_shared;
	}
	
	/**
	 * Returns the table prefix for the main site on multisites and the site itself on single site installations.
	 *
	 * @return string
	 */
	public static function network_prefix() {
		global $wpdb;
		return $wpdb->base_prefix;
	}
	
	/**
	 * Returns the table with the site (single site installations) or network (multisite) prefix added.
	 *
	 * @param string $table
	 * @return string
	 */
	public static function network_table($table) {
		return self::network_prefix() . $table;
	}
	
	public function __get($key) {
		switch ($key) {
			case 'secrets':
				return self::network_table(self::TABLE_2FA_SECRETS);
			case 'settings':
				return self::network_table(self::TABLE_SETTINGS);
		}
		
		throw new \OutOfBoundsException('Unknown key: ' . $key);
	}
	
	public function install() {
		$this->_create_schema();
		
		global $wpdb;
		$table = $this->secrets;
		$wpdb->query($wpdb->prepare("UPDATE `{$table}` SET `vtime` = LEAST(`vtime`, %d)", Controller_Time::time()));
	}
	
	public function uninstall() {
		$tables = array(self::TABLE_2FA_SECRETS, self::TABLE_SETTINGS);
		foreach ($tables as $table) {
			global $wpdb;
			$wpdb->query('DROP TABLE IF EXISTS `' . self::network_table($table) . '`');
		}
	}
	
	protected function _create_schema() {
		$tables = array(
			self::TABLE_2FA_SECRETS => '(
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `secret` tinyblob NOT NULL,
  `recovery` blob NOT NULL,
  `ctime` int(10) unsigned NOT NULL,
  `vtime` int(10) unsigned NOT NULL,
  `mode` enum(\'authenticator\') NOT NULL DEFAULT \'authenticator\',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;',
			self::TABLE_SETTINGS => '(
  `name` varchar(191) NOT NULL DEFAULT \'\',
  `value` longblob,
  `autoload` enum(\'no\',\'yes\') NOT NULL DEFAULT \'yes\',
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;',
		);
		
		foreach ($tables as $table => $def) {
			global $wpdb;
			$wpdb->query('CREATE TABLE IF NOT EXISTS `' . self::network_table($table) . '` ' . $def);
		}
	}
}