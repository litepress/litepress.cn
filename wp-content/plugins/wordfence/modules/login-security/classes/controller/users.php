<?php

namespace WordfenceLS;

use WordfenceLS\Crypto\Model_JWT;
use WordfenceLS\Crypto\Model_Symmetric;

class Controller_Users {
	const RECOVERY_CODE_COUNT = 5;
	const RECOVERY_CODE_SIZE = 8;
	const SECONDS_PER_DAY = 86400;
	const META_KEY_GRACE_PERIOD_RESET = 'wfls-grace-period-reset';
	const META_KEY_GRACE_PERIOD_OVERRIDE = 'wfls-grace-period-override';
	const META_KEY_ALLOW_GRACE_PERIOD = 'wfls-allow-grace-period';
	
	/**
	 * Returns the singleton Controller_Users.
	 *
	 * @return Controller_Users
	 */
	public static function shared() {
		static $_shared = null;
		if ($_shared === null) {
			$_shared = new Controller_Users();
		}
		return $_shared;
	}
	
	public function init() {
		$this->_init_actions();
	}
	
	/**
	 * Imports the array of 2FA secrets. Users that do not currently exist or are disallowed from enabling 2FA are not imported.
	 *
	 * @param array $secrets An array of secrets in the format array(<user id> => array('secret' => <secret in hex>, 'recovery' => <recovery keys in hex>, 'ctime' => <timestamp>, 'vtime' => <timestamp>, 'type' => <type>), ...)
	 * @return int The number imported.
	 */
	public function import_2fa($secrets) {
		global $wpdb;
		$table = Controller_DB::shared()->secrets;
		
		$count = 0;
		foreach ($secrets as $id => $parameters) {
			$user = new \WP_User($id);
			if (!$user->exists() || !$this->can_activate_2fa($user) || $parameters['type'] != 'authenticator' || $this->has_2fa_active($user)) { continue; }
			$secret = Model_Compat::hex2bin($parameters['secret']);
			$recovery = Model_Compat::hex2bin($parameters['recovery']);
			$ctime = (int) $parameters['ctime'];
			$vtime = min((int) $parameters['vtime'], Controller_Time::time());
			$type = $parameters['type'];
			$wpdb->query($wpdb->prepare("INSERT INTO `{$table}` (`user_id`, `secret`, `recovery`, `ctime`, `vtime`, `mode`) VALUES (%d, %s, %s, %d, %d, %s)", $user->ID, $secret, $recovery, $ctime, $vtime, $type));
			$count++;
		}
		return $count;
	}
	
	public function admin_users() {
		//We should eventually allow for any user to be granted the manage capability, but we won't account for that now
		if (is_multisite()) {
			$logins = get_super_admins();
			$users = array();
			foreach ($logins as $l) {
				$user = new \WP_User(null, $l);
				if ($user->ID > 0) {
					$users[] = $user;
				}
			}
			return $users;
		}
		
		$query = new \WP_User_Query(http_build_query(array('role' => 'administrator', 'number' => -1)));
		return $query->get_results();
	}

	public function get_users_by_role($role, $limit = -1) {
		if ($role === 'super-admin') {
			$superAdmins = array();
			foreach(get_super_admins() as $username) {
				$superAdmins[] = new \WP_User($username);
			}
			return $superAdmins;
		}
		else {
			$query = new \WP_User_Query(http_build_query(array('role' => $role, 'number' => is_int($limit) ? $limit : -1)));
			return $query->get_results();
		}
	}
	
	/**
	 * Returns whether or not the user has a valid remembered device.
	 * 
	 * @param \WP_User $user
	 * @return bool
	 */
	public function has_remembered_2fa($user) {
		static $_cache = array();
		if (isset($_cache[$user->ID])) {
			return $_cache[$user->ID];
		}
		
		if (!Controller_Settings::shared()->get_bool(Controller_Settings::OPTION_REMEMBER_DEVICE_ENABLED)) {
			return false;
		}
		
		$maxExpiration = \WordfenceLS\Controller_Time::time() + Controller_Settings::shared()->get_int(Controller_Settings::OPTION_REMEMBER_DEVICE_DURATION);
		
		$encrypted = Model_Symmetric::encrypt((string) $user->ID);
		if (!$encrypted) { //Can't generate cookie key due to host failure
			return false;
		}
		
		foreach ($_COOKIE as $name => $value) {
			if (!preg_match('/^wfls\-remembered\-(.+)$/', $name, $matches)) {
				continue;
			}
			
			$jwt = Model_JWT::decode_jwt($value);
			if (!$jwt || !isset($jwt->payload['iv'])) {
				continue;
			}
			
			if (\WordfenceLS\Controller_Time::time() > min($jwt->expiration, $maxExpiration)) { //Either JWT is expired or the remember period was shortened since generating it
				continue;
			}
			
			$data = Model_JWT::base64url_convert_from($matches[1]);
			$iv = $jwt->payload['iv'];
			$encrypted = array('data' => $data, 'iv' => $iv);
			$userID = (int) Model_Symmetric::decrypt($encrypted);
			if ($userID != 0 && $userID == $user->ID) {
				$_cache[$user->ID] = true;
				return true;
			}
		}
		
		$_cache[$user->ID] = false;
		return false;
	}
	
	/**
	 * Sets the cookie needed to remember the 2FA status.
	 * 
	 * @param \WP_User $user
	 */
	public function remember_2fa($user) {
		if (!Controller_Settings::shared()->get_bool(Controller_Settings::OPTION_REMEMBER_DEVICE_ENABLED)) {
			return;
		}
		
		if ($this->has_remembered_2fa($user)) {
			return;
		}
		
		$encrypted = Model_Symmetric::encrypt((string) $user->ID);
		if (!$encrypted) { //Can't generate cookie key due to host failure
			return;
		}
		
		//Remove old cookies
		foreach ($_COOKIE as $name => $value) {
			if (!preg_match('/^wfls\-remembered\-(.+)$/', $name, $matches)) {
				continue;
			}
			setcookie($name, '', \WordfenceLS\Controller_Time::time() - 86400);
		}
		
		//Set the new one
		$expiration = \WordfenceLS\Controller_Time::time() + Controller_Settings::shared()->get_int(Controller_Settings::OPTION_REMEMBER_DEVICE_DURATION);
		$jwt = new Model_JWT(array('iv' => $encrypted['iv']), $expiration);
		$cookieName = 'wfls-remembered-' . Model_JWT::base64url_convert_to($encrypted['data']);
		$cookieValue = (string) $jwt;
		setcookie($cookieName, $cookieValue, $expiration, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true);
	}
	
	/**
	 * Returns whether or not 2FA can be activated on the given user.
	 *
	 * @param \WP_User $user
	 * @return bool
	 */
	public function can_activate_2fa($user) {
		if (is_multisite() && !is_super_admin($user->ID)) {
			$blogs = get_blogs_of_user($user->ID);
			foreach ($blogs as $id => $info) {
				if ($this->_user_can_for_blog($user, $id, Controller_Permissions::CAP_ACTIVATE_2FA_SELF)) {
					return true;
				}
			}
			return false;
		}
		return user_can($user, Controller_Permissions::CAP_ACTIVATE_2FA_SELF);
	}
	
	/**
	 * Implementation of current_user_can_for_blog that works for an arbitrary user.
	 * 
	 * @param int $user_id
	 * @param int $blog_id
	 * @param string $capability
	 * @return bool
	 */
	private function _user_can_for_blog($user_id, $blog_id, $capability) {
		$switched = is_multisite() ? switch_to_blog($blog_id) : false;
		
		$user = new \WP_User($user_id);
	
		$args = array_slice(func_get_args(), 2);
		$args = array_merge(array($capability), $args);
		
		$can = call_user_func_array(array($user, 'has_cap'), $args);
		
		if ($switched) {
			restore_current_blog();
		}
		
		return $can;
	}
	
	/**
	 * Returns whether or not any user has 2FA activated.
	 *
	 * @return bool
	 */
	public function any_2fa_active() {
		global $wpdb;
		$table = Controller_DB::shared()->secrets;
		return !!intval($wpdb->get_var("SELECT COUNT(*) FROM `{$table}`"));
	}
	
	/**
	 * Returns whether or not the user has 2FA activated.
	 *
	 * @param \WP_User $user
	 * @return bool
	 */
	public function has_2fa_active($user) {
		global $wpdb;
		$table = Controller_DB::shared()->secrets;
		return $this->can_activate_2fa($user) && !!intval($wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM `{$table}` WHERE `user_id` = %d", $user->ID)));
	}
	
	/**
	 * Deactivates a user.
	 *
	 * @param \WP_User $user
	 */
	public function deactivate_2fa($user) {
		global $wpdb;
		$table = Controller_DB::shared()->secrets;
		$wpdb->query($wpdb->prepare("DELETE FROM `{$table}` WHERE `user_id` = %d", $user->ID));
	}

	private function has_admin_with_2fa_active() {
		static $cache = null;
		if ($cache === null) {
			$activeIDs = $this->_user_ids_with_2fa_active();
			foreach ($activeIDs as $id) {
				if (Controller_Permissions::shared()->can_manage_settings(new \WP_User($id))) {
					$cache = true;
					return $cache;
				}
			}
			$cache = false;
		}
		return $cache;
	}

	/**
	 * Returns whether or not 2FA is required for the user regardless of activation status. 2FA is considered required
	 * when the option to require it is enabled and there is at least one administrator with it active.
	 * 
	 * @param \WP_User $user
	 * @param bool &$gracePeriod
	 * @param int &$requiredAt
	 * @return bool
	 */
	public function requires_2fa($user, &$gracePeriod = false, &$requiredAt = null) {
		static $cache = array();
		if (array_key_exists($user->ID, $cache)) {
			list($required, $gracePeriod, $requiredAt) = $cache[$user->ID];
			return $required;
		}
		else {
			$gracePeriod = false;
			$requiredAt = null;
			$required = $this->does_user_role_require_2fa($user, $gracePeriod, $requiredAt);
			$cache[$user->ID] = array($required, $gracePeriod, $requiredAt);
			return $required;
		}
	}
	
	/**
	 * Returns the number of recovery codes remaining for the user or null if the user does not have 2FA active.
	 *
	 * @param \WP_User $user
	 * @return float|null
	 */
	public function recovery_code_count($user) {
		global $wpdb;
		$table = Controller_DB::shared()->secrets;
		$record = $wpdb->get_var($wpdb->prepare("SELECT `recovery` FROM `{$table}` WHERE `user_id` = %d", $user->ID));
		if (!$record) {
			return null;
		}
		
		return floor(Model_Crypto::strlen($record) / self::RECOVERY_CODE_SIZE);
	}
	
	/**
	 * Generates a new set of recovery codes and saves them to $user if provided.
	 *
	 * @param \WP_User|bool $user The user to save the codes to or false to just return codes.
	 * @param int $count
	 * @return array
	 */
	public function regenerate_recovery_codes($user = false, $count = self::RECOVERY_CODE_COUNT) {
		$codes = array();
		for ($i = 0; $i < $count; $i++) {
			$c = \WordfenceLS\Model_Crypto::random_bytes(self::RECOVERY_CODE_SIZE);
			$codes[] = $c;
		}
		
		if ($user && Controller_Users::shared()->has_2fa_active($user)) {
			global $wpdb;
			$table = Controller_DB::shared()->secrets;
			$wpdb->query($wpdb->prepare("UPDATE `{$table}` SET `recovery` = %s WHERE `user_id` = %d", implode('', $codes), $user->ID));
		}
		
		return $codes;
	}
	
	/**
	 * Records the reCAPTCHA score for later display.
	 * 
	 * This is not atomic, which means this can miscount on hits that overlap, but the overhead of being atomic is not 
	 * worth it for our use.
	 * 
	 * @param \WP_User $user|null
	 * @param float $score
	 */
	public function record_captcha_score($user, $score) {
		if (!Controller_CAPTCHA::shared()->enabled()) { return; }
		if ($this->has_2fa_active($user)) { return; } //2FA activated users do not retrieve a score
		
		if ($user) { update_user_meta($user->ID, 'wfls-last-captcha-score', $score); }
		$stats = Controller_Settings::shared()->get_array(Controller_Settings::OPTION_CAPTCHA_STATS);
		$int_score = min(max((int) ($score * 10), 0), 10);
		$count = array_sum($stats['counts']);
		$stats['counts'][$int_score]++;
		$stats['avg'] = ($stats['avg'] * $count + $int_score) / ($count + 1);
		Controller_Settings::shared()->set_array(Controller_Settings::OPTION_CAPTCHA_STATS, $stats);
	}
	
	/**
	 * Returns the active and inactive user counts.
	 * 
	 * @return array
	 */
	public function user_counts() {
		if (is_multisite() && function_exists('get_user_count')) {
			$total_users = get_user_count();
		}
		else {
			global $wpdb;
			$total_users = (int) $wpdb->get_var("SELECT COUNT(ID) as c FROM {$wpdb->users}");
		}
		$active_users = $this->active_count();
		return array('active_users' => $active_users, 'inactive_users' => max($total_users - $active_users, 0));
	}
	
	public function detailed_user_counts() {
		global $wpdb;
		
		//Base counts
		$counts = count_users();
		
		//Adaptation of the source of the above call to get enabled counts
		$site_id = get_current_blog_id();
		$blog_prefix = $wpdb->get_blog_prefix($site_id);
		$roles = new \WP_Roles();
		if (is_multisite() && get_current_blog_id() != $site_id) {
			switch_to_blog($site_id);
			$avail_roles = $roles->get_names();
			restore_current_blog();
		}
		else {
			$avail_roles = $roles->get_names();
		}
		
		// Build a CPU-intensive query that will return concise information.
		$select_count = array();
		foreach ($avail_roles as $this_role => $name) {
			if (!method_exists($wpdb, 'esc_like')) {
				$like = addcslashes('"' . $this_role . '"', '_%\\'); //for WP < 4.0
			}
			else {
				$like = $wpdb->esc_like('"' . $this_role . '"');
			}
			$select_count[] = $wpdb->prepare("COUNT(NULLIF(`meta_value` LIKE %s, false))", '%' . $like . '%');
		}
		$select_count[] = "COUNT(NULLIF(`meta_value` = 'a:0:{}', false))";
		$select_count = implode(', ', $select_count);
		
		// Add the meta_value index to the selection list, then run the query.
		$table = Controller_DB::shared()->secrets;
		$row = $wpdb->get_row("
			SELECT {$select_count}, COUNT(*)
			FROM {$wpdb->usermeta} um
			INNER JOIN {$table} tf ON um.user_id = tf.user_id
			WHERE meta_key = '{$blog_prefix}capabilities'
		", ARRAY_N);
		
		// Run the previous loop again to associate results with role names.
		$col = 0;
		$role_counts = array();
		foreach ($avail_roles as $this_role => $name) {
			$count = (int) $row[$col++];
			if ($count > 0) {
				$role_counts[$this_role] = $count;
			}
		}
		
		$role_counts['none'] = (int) $row[$col++];

		// Separately add super admins for multisite
		if (is_multisite()) {
			$superAdmins = 0;
			$activeSuperAdmins = 0;
			foreach(get_super_admins() as $username) {
				$superAdmins++;
				$user = new \WP_User($username);
				if ($this->has_2fa_active($user)) {
					$activeSuperAdmins++;
				}
			}
			$counts['avail_roles']['super-admin'] = $superAdmins;
			$role_counts['super-admin'] = $activeSuperAdmins;
		}
		
		// Get the meta_value index from the end of the result set.
		$total_users = (int) $row[$col];
		
		$counts['active_total_users'] = $total_users;
		$counts['active_avail_roles'] =& $role_counts;

		return $counts;
	}
	
	/**
	 * Returns the number of users with 2FA active.
	 * 
	 * @return int
	 */
	public function active_count() {
		global $wpdb;
		$table = Controller_DB::shared()->secrets;
		return intval($wpdb->get_var("SELECT COUNT(*) FROM `{$table}`"));
	}
	
	/**
	 * WP Filters/Actions
	 */
	
	protected function _init_actions() {
		add_action('deleted_user', array($this, '_deleted_user'));
		add_filter('manage_users_columns', array($this, '_manage_users_columns'));
		add_filter('manage_users_custom_column', array($this, '_manage_users_custom_column'), 10, 3);
		add_filter('manage_users_sortable_columns', array($this, '_manage_users_sortable_columns'), 10, 1);
		add_filter('users_list_table_query_args', array($this, '_users_list_table_query_args'));
		add_filter('user_row_actions', array($this, '_user_row_actions'), 10, 2);
		add_filter('views_users', array($this, '_views_users'));
		
		if (is_multisite()) {
			add_filter('manage_users-network_columns', array($this, '_manage_users_columns'));
			add_filter('manage_users-network_custom_column', array($this, '_manage_users_custom_column'), 10, 3);
			add_filter('manage_users-network_sortable_columns', array($this, '_manage_users_sortable_columns'), 10, 1);
			add_filter('ms_user_row_actions', array($this, '_user_row_actions'), 10, 2);
			add_filter('views_users-network', array($this, '_views_users'));
		}
	}
	
	public function _deleted_user($id) {
		$user = new \WP_User($id);
		if ($user instanceof \WP_User && !$user->exists()) {
			global $wpdb;
			$table = Controller_DB::shared()->secrets;
			$wpdb->query($wpdb->prepare("DELETE FROM `{$table}` WHERE `user_id` = %d", $id));
		}
	}
	
	public function _manage_users_columns($columns = array()) {
		if (user_can(wp_get_current_user(), Controller_Permissions::CAP_ACTIVATE_2FA_OTHERS)) {
			$columns['wfls_2fa_status'] = esc_html__('2FA Status', 'wordfence-2fa');
		}
		
		if (Controller_Permissions::shared()->can_manage_settings(wp_get_current_user())) {
			$columns['wfls_last_login'] = esc_html__('Last Login', 'wordfence-2fa');
			if (Controller_CAPTCHA::shared()->enabled()) {
				$columns['wfls_last_captcha'] = esc_html__('Last CAPTCHA', 'wordfence-2fa');
			}
		}
		return $columns;
	}
	
	public function _manage_users_custom_column($value = '', $column_name = '', $user_id = 0) {
		switch($column_name) {
			case 'wfls_2fa_status':
				$user = new \WP_User($user_id);
				$value = __('Not Allowed', 'wordfence-2fa');
				if (Controller_Users::shared()->can_activate_2fa($user)) {
					$has2fa = Controller_Users::shared()->has_2fa_active($user);
					$requires2fa = $this->requires_2fa($user, $inGracePeriod);
					if ($has2fa) {
						$value = esc_html__('Active', 'wordfence-2fa');
					}
					elseif ($inGracePeriod) {
						$value = wp_kses(__('Inactive<small class="wfls-sub-status">(Grace Period)</small>', 'wordfence-2fa'), array('small'=>array('class'=>array())));
					}
					elseif (($requires2fa && !$has2fa)) {
						$value = wp_kses($inGracePeriod === null ? __('Locked Out<small class="wfls-sub-status">(Grace Period Disabled)</small>') : __('Locked Out<small class="wfls-sub-status">(Grace Period Exceeded)</small>', 'wordfence-2fa'), array('small'=>array('class'=>array())));
					}
					else {
						$value = esc_html__('Inactive', 'wordfence-2fa');
					}
				}
				break;
			case 'wfls_last_login':
				$value = '-';
				if (($last = get_user_meta($user_id, 'wfls-last-login', true))) {
					$value = Controller_Time::format_local_time(get_option('date_format') . ' ' . get_option('time_format'), $last);
				}
				break;
			case 'wfls_last_captcha':
				$user = new \WP_User($user_id);
				if (Controller_Users::shared()->can_activate_2fa($user) && Controller_Users::shared()->has_2fa_active($user)) {
					$value = __('(not required)', 'wordfence-2fa');
				}
				else {
					$value = '-';
					if (($last = get_user_meta($user_id, 'wfls-last-captcha-score', true))) {
						$value = number_format($last, 1);
					}
				}
				break;
		}
		
		return $value;
	}
	
	public function _manage_users_sortable_columns($sortable_columns) {
		return array_merge($sortable_columns, array(
			'wfls_last_login' => 'wfls-lastlogin',
			'wfls_last_captcha' => 'wfls-lastcaptcha',
		));
	}
	
	protected function _user_ids_with_2fa_active() {
		global $wpdb;
		$table = Controller_DB::shared()->secrets;
		return $wpdb->get_col("SELECT DISTINCT `user_id` FROM {$table}");
	}
	
	public function _users_list_table_query_args($args) {
		if (isset($_REQUEST['wf2fa']) && preg_match('/^(?:in)?active$/i', $_REQUEST['wf2fa'])) {
			$mode = strtolower($_REQUEST['wf2fa']);
			if ($mode == 'active') {
				$args['include'] = $this->_user_ids_with_2fa_active();
			}
			else if ($mode == 'inactive') {
				unset($args['include']);
				$args['exclude'] = $this->_user_ids_with_2fa_active();
			}
		}
		
		if (isset($args['orderby'])) {
			if (is_string($args['orderby'])) {
				if ($args['orderby'] == 'wfls-lastlogin') {
					$args['meta_key'] = 'wfls-last-login';
					$args['orderby'] = 'meta_value';
				}
				else if ($args['orderby'] == 'wfls-lastcaptcha') {
					$args['meta_key'] = 'wfls-last-captcha-score';
					$args['orderby'] = 'meta_value';
				}
			}
			else {
				$has_one = false;
				if (array_key_exists('wfls-lastlogin', $args['orderby'])) {
					$args['meta_key'] = 'wfls-last-login';
					$args['orderby']['meta_value'] = $args['orderby']['wfls-lastlogin'];
					unset($args['orderby']['wfls-lastlogin']);
					$has_one = true;
				}
				
				if (array_key_exists('wfls-lastcaptcha', $args['orderby'])) {
					if (!$has_one) { //We have to discard one if both are set to sort by because $meta_key can only be a single value rather than an array
						$args['meta_key'] = 'wfls-last-captcha-score';
						$args['orderby']['meta_value'] = $args['orderby']['wfls-lastcaptcha'];
					}
					unset($args['orderby']['wfls-lastcaptcha']);
					$has_one = true;
				}
				
				if (in_array('wfls-lastlogin', $args['orderby'])) {
					if (!$has_one) { //We have to discard one if both are set to sort by because $meta_key can only be a single value rather than an array
						$args['meta_key'] = 'wfls-last-login';
						$args['orderby'][] = 'meta_value';
					}
					unset($args['orderby'][array_search('wfls-lastlogin', $args['orderby'])]);
					$has_one = true;
				}
				
				if (in_array('wfls-lastcaptcha', $args['orderby'])) {
					if (!$has_one) { //We have to discard one if both are set to sort by because $meta_key can only be a single value rather than an array
						$args['meta_key'] = 'wfls-last-captcha-score';
						$args['orderby'][] = 'meta_value';
					}
					unset($args['orderby'][array_search('wfls-lastcaptcha', $args['orderby'])]);
					$has_one = true;
				}
			}
		}
		return $args;
	}
	
	public function _user_row_actions($actions, $user) {
		//Format is 'view' => '<a href="https://wfpremium.dev1.ryanbritton.com/author/ryan/" aria-label="View posts by ryan">View</a>'
		if (user_can(wp_get_current_user(), Controller_Permissions::CAP_ACTIVATE_2FA_OTHERS) && (Controller_Users::shared()->can_activate_2fa($user) || Controller_Users::shared()->has_2fa_active($user))) {
			$url = (is_multisite() ? network_admin_url('admin.php?page=WFLS&user=' . $user->ID) : admin_url('admin.php?page=WFLS&user=' . $user->ID));
			$actions['wf2fa'] = '<a href="' . esc_url($url) . '" aria-label="' . esc_attr(sprintf(__('Edit two-factor authentication for %s', 'wordfence-2fa'), $user->user_login)) . '">' . esc_html__('2FA', 'wordfence-2fa') . '</a>';
		}
		return $actions;
	}
	
	public function _views_users($views) {
		//Format is 'subscriber' => '<a href=\\'users.php?role=subscriber\\'>Subscriber <span class="count">(40,002)</span></a>',
		include(ABSPATH . WPINC . '/version.php'); /** @var string $wp_version */
		if (user_can(wp_get_current_user(), Controller_Permissions::CAP_ACTIVATE_2FA_OTHERS) && version_compare($wp_version, '4.4.0', '>=')) {
			$counts = $this->user_counts();
			$views['all'] = str_replace(' class="current" aria-current="page"', '', $views['all']);
			$views['wfls-active'] = '<a href="' . esc_url(add_query_arg('wf2fa', 'active', 'users.php')) . '"' . (isset($_GET['wf2fa']) && $_GET['wf2fa'] == 'active' ? ' class="current" aria-current="page"' : '') . '>' . esc_html__('2FA Active', 'wordfence-2fa') . ' <span class="count">(' . number_format($counts['active_users']) . ')</span></a>';
			$views['wfls-inactive'] = '<a href="' . esc_url(add_query_arg('wf2fa', 'inactive', 'users.php')) . '"' . (isset($_GET['wf2fa']) && $_GET['wf2fa'] == 'inactive' ? ' class="current" aria-current="page"' : '') . '>' . esc_html__('2FA Inactive', 'wordfence-2fa') . ' <span class="count">(' . number_format($counts['inactive_users']) . ')</span></a>';
		}
		return $views;
	}

	private function get_grace_period_reset_time($user) {
		$time = get_user_option(self::META_KEY_GRACE_PERIOD_RESET, $user->ID);
		if (empty($time))
			return null;
		return (int) $time;
	}

	public function get_grace_period_override($user) {
		$override = get_user_option(self::META_KEY_GRACE_PERIOD_OVERRIDE, $user->ID);
		if ($override === false)
			return null;
		return (int) $override;
	}

	private function does_user_role_require_2fa($user, &$inGracePeriod = null, &$requiredAt = null) {
		$is2faAdmin = Controller_Permissions::shared()->can_manage_settings($user);
		$userDate = self::get_grace_period_reset_time($user);
		if ($userDate === null)
			$userDate = self::get_registration_date($user);
		if ($is2faAdmin && !$this->get_grace_period_allowed_flag($user->ID)) {
			$gracePeriod = 0;
			$inGracePeriod = null;
		}
		else {
			$gracePeriod = self::get_grace_period_override($user);
			if ($gracePeriod === null)
				$gracePeriod = Controller_Settings::shared()->get_user_2fa_grace_period();
			$gracePeriod *= self::SECONDS_PER_DAY;
			$inGracePeriod = false;
		}
		$now = time();
		foreach (Controller_Permissions::shared()->get_all_roles($user) as $role) {
			$roleDate = Controller_Settings::shared()->get_required_2fa_role_activation_time($role);
			if ($roleDate === false)
				continue;
			$effectiveDate = max($userDate, $roleDate) + $gracePeriod;
			if ($requiredAt === null || $effectiveDate < $requiredAt)
				$requiredAt = $effectiveDate;
			if ($effectiveDate <= $now && (!$is2faAdmin || $this->has_admin_with_2fa_active())) {
				if ($inGracePeriod)
					$inGracePeriod = false;
				return true;
			}
			else if ($inGracePeriod !== null) {
				$inGracePeriod = true;
			}
		}
		return false;
	}

	private static function get_registration_date($user) {
		return strtotime($user->user_registered);
	}

	public function reset_2fa_grace_period($user, $override = null) {
		if (!$this->can_activate_2fa($user) || $this->has_2fa_active($user))
			return false;
		update_user_option($user->ID, self::META_KEY_GRACE_PERIOD_RESET, time(), true);
		if ($override !== null)
			update_user_option($user->ID, self::META_KEY_GRACE_PERIOD_OVERRIDE, (int) $override, true);
		return true;
	}

	public function revoke_grace_period($user) {
		foreach(array(
			self::META_KEY_GRACE_PERIOD_RESET,
			self::META_KEY_GRACE_PERIOD_OVERRIDE,
			self::META_KEY_ALLOW_GRACE_PERIOD
			) as $option) {
			delete_user_option($user->ID, $option, true);
		}
	}

	public function allow_grace_period($userId) {
		update_user_option($userId, self::META_KEY_ALLOW_GRACE_PERIOD, true, true);
	}

	public function get_grace_period_allowed_flag($userId) {
		return (bool) get_user_option(self::META_KEY_ALLOW_GRACE_PERIOD, $userId);
	}

	public function has_revokable_grace_period($user) {
		return $this->get_grace_period_allowed_flag($user->ID) || $this->get_grace_period_reset_time($user) !== null;
	}

	private function get_inactive_2fa_super_admins($gracePeriod = false) {
		$inactive = array();
		foreach(get_super_admins() as $username) {
			$user = new \WP_User($username);
			if (!$this->has_2fa_active($user)) {
				$this->requires_2fa($user, $inGracePeriod, $requiredAt);
				if ($gracePeriod === null || $gracePeriod == $inGracePeriod) {
					$current = new \StdClass();
					$current->user_id = $user->ID;
					$current->user_login = $username;
					$current->required_at = $requiredAt;
					$inactive[] = $current;
				}
			}
		}
		return $inactive;
	}

	private function generate_inactive_2fa_user_query($roleKey, $gracePeriod = null, $page = null, $perPage = null) {
		global $wpdb;
		$secondsPerDay = (int) self::SECONDS_PER_DAY;
		$gracePeriodSeconds = (int) (Controller_Settings::shared()->get_user_2fa_grace_period() * self::SECONDS_PER_DAY);
		$roleTime = (int) (Controller_Settings::shared()->get_required_2fa_role_activation_time($roleKey));
		$siteId = get_current_blog_id();
		$blogPrefix = $wpdb->get_blog_prefix($siteId);
		$usermeta = $wpdb->usermeta;
		$users = $wpdb->users;
		$secrets = Controller_DB::shared()->secrets;
		$admin = Controller_Permissions::shared()->can_role_manage_settings($roleKey);
		$parameters = array(
			self::META_KEY_GRACE_PERIOD_RESET,
			self::META_KEY_GRACE_PERIOD_OVERRIDE
		);
		$gracePeriodClause = "IF(overrides.days IS NULL, $gracePeriodSeconds, overrides.days * $secondsPerDay)";
		$registeredTimestampClause = "UNIX_TIMESTAMP(CONVERT_TZ($users.user_registered, '+00:00', @@time_zone))";
		$now = time();
		if ($admin) {
			$allowancesJoin = <<<SQL
				LEFT JOIN (
					SELECT
						user_id,
						meta_value AS allowed
					FROM
						$usermeta
					WHERE
						meta_key = %s
				) allowances ON allowances.user_id = $usermeta.user_id
SQL;
			$parameters[] = self::META_KEY_ALLOW_GRACE_PERIOD;
			$allowedClause = 'IFNULL(allowances.allowed, 0)';
			$gracePeriodClause = "IF($allowedClause = 0, 0, $gracePeriodClause)";
		}
		else {
			$allowancesJoin = null;
			$allowedClause = null;
		}
		$timeClause = "GREATEST($roleTime, $registeredTimestampClause, IFNULL(resets.time, 0)) + $gracePeriodClause";
		$query = <<<SQL
			SELECT
				$usermeta.user_id,
				$users.user_login,
				$timeClause AS required_at
			FROM
				$usermeta
				JOIN $users ON $users.ID = $usermeta.user_id
				LEFT JOIN (
					SELECT
						user_id,
						meta_value AS time
					FROM
						$usermeta
					WHERE
						meta_key = %s
				) resets ON resets.user_id = $usermeta.user_id
				LEFT JOIN (
					SELECT
						user_id,
						meta_value AS days
					FROM
						$usermeta
					WHERE
						meta_key = %s
				) overrides ON overrides.user_id = $usermeta.user_id
				$allowancesJoin
			WHERE
				meta_key = '{$blogPrefix}capabilities'
				AND meta_value LIKE %s
				AND NOT $usermeta.user_id IN(SELECT user_id FROM {$secrets})
SQL;
		$conditions = array();
		$operator = 'AND';
		if ($gracePeriod !== null) {
			if ($gracePeriod) {
				$conditions[] = "$timeClause > $now";
			}
			else {
				$conditions[] = "$timeClause <= $now";
				$operator = 'OR';
			}
		}
		if ($admin) {
			$conditions[] = $allowedClause . ' = ' . ($gracePeriod ? 1 : 0);
		}
		if (!empty($conditions))
			$query .= ' AND (' . implode(" $operator ", $conditions). ')';
		if ($page !== null && $perPage !== null) {
			$offset = (int) (($page - 1) * $perPage);
			$limit = (int) ($perPage + 1);
			if ($offset >= 0 && $perPage > 0)
				$query .= " LIMIT $offset, $limit";
		}
		$serializedRoleKey = serialize($roleKey);
		$roleMatch = '%' . (method_exists($wpdb, 'esc_like') ? $wpdb->esc_like($serializedRoleKey) : addcslashes($serializedRoleKey, '_%\\')). '%';
		$parameters[] = $roleMatch;
		return $wpdb->prepare(
			$query.';',
			$parameters
		);
	}

	public function get_inactive_2fa_users($roleKey, $gracePeriod = null, $page = null, $perPage = null, &$lastPage = null) {
		global $wpdb;
		if (is_multisite() && $roleKey === 'super-admin') {
			$superAdmins = $this->get_inactive_2fa_super_admins($gracePeriod);
			if ($page !== null && $perPage !== null) {
				$start = ($page - 1) * $perPage;
				$end = $start + $perPage;
				$lastPage = $end >= count($superAdmins);
				$superAdmins = array_slice($superAdmins, $start, $perPage);
			}
			return $superAdmins;
		}
		else {
			$query = $this->generate_inactive_2fa_user_query($roleKey, $gracePeriod, $page, $perPage);
			$results = $wpdb->get_results($query);
			if (count($results) > $perPage) {
				$lastPage = false;
				array_pop($results);
			}
			else {
				$lastPage = true;
			}
			return $results;
		}
	}
}