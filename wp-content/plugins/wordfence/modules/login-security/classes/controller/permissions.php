<?php

namespace WordfenceLS;

class Controller_Permissions {
	const CAP_ACTIVATE_2FA_SELF = 'wf2fa_activate_2fa_self'; //Activate 2FA on its own user account
	const CAP_ACTIVATE_2FA_OTHERS = 'wf2fa_activate_2fa_others'; //Activate 2FA on user accounts other than its own
	const CAP_MANAGE_SETTINGS = 'wf2fa_manage_settings'; //Edit settings for the plugin

	const SITE_BATCH_SIZE = 50; //The maximum number of sites to process during a single request

	private $network_roles = array();
	
	/**
	 * Returns the singleton Controller_Permissions.
	 *
	 * @return Controller_Permissions
	 */
	public static function shared() {
		static $_shared = null;
		if ($_shared === null) {
			$_shared = new Controller_Permissions();
		}
		return $_shared;
	}

	private function on_role_change() {
		update_site_option('wfls_last_role_change', time());
		if(is_multisite())
			update_site_option('wfls_role_batch_position', 0);
	}
	
	public function install() {
		$this->on_role_change();
		if (is_multisite()) {
			//Super Admin automatically gets all capabilities, so we don't need to explicitly add them
			$this->_add_cap_multisite('administrator', self::CAP_ACTIVATE_2FA_SELF, $this->get_primary_sites());
		}
		else {
			$this->_add_cap('administrator', self::CAP_ACTIVATE_2FA_SELF);
			$this->_add_cap('administrator', self::CAP_ACTIVATE_2FA_OTHERS);
			$this->_add_cap('administrator', self::CAP_MANAGE_SETTINGS);
		}
	}

	public function init() {
		global $wp_version;
		if(is_multisite()){
			if(version_compare($wp_version, '5.1.0', '>=')){
				add_action('wp_initialize_site', array($this, '_wp_initialize_site'), 99);
			}
			else{
				add_action('wpmu_new_blog', array($this, '_wpmu_new_blog'), 10, 5);
			}
			add_action('init', array($this, 'check_role_sync'), 1);
		}
	}

	public function _wpmu_new_blog($site_id, $user_id, $domain, $path, $network_id) {
		$this->sync_roles($network_id, $site_id);
	}

	public function _wp_initialize_site($new_site) {
		$this->sync_roles($new_site->site_id, $new_site->blog_id);
	}

	public function check_role_sync() {
		//Trigger an initial update for existing installations
		$last_role_change=(int)get_site_option('wfls_last_role_change', 0);
		if($last_role_change===0)
			$this->on_role_change();
		//Process the current batch if necessary
		$position=(int)get_site_option('wfls_role_batch_position', 0);
		if($position===-1)
			return;
		$sites=$this->get_sites($position, self::SITE_BATCH_SIZE);
		if(empty($sites)){
			$position=-1;
			return;
		}
		else{
			$network_id=get_current_site()->id;
			foreach($sites as $site){
				$site=(int)$site;
				$this->sync_roles($network_id, $site);
			}
			$position=$site;
		}
		update_site_option('wfls_role_batch_position', $position);
		//Update the current site if not already up to date
		$site_id=get_current_blog_id();
		if($last_role_change>=get_option('wfls_last_role_sync', 0)&&$site_id>=$position){
			$this->sync_roles(get_current_site()->id, $site_id);
			update_option('wfls_last_role_sync', time());
		}
	}

	/**
	 * Get the primary site ID for a given network
	 */
	private function get_primary_site_id($network_id) {
		global $wpdb;
		if(function_exists('get_network')){
			$network=get_network($network_id); //TODO: Support multi-network throughout plugin
			return (int)$network->blog_id;
		}
		else{
			return (int)$wpdb->get_var($wpdb->prepare("SELECT blogs.blog_id FROM {$wpdb->site} sites JOIN {$wpdb->blogs} blogs ON blogs.site_id=sites.id AND blogs.path=sites.path WHERE sites.id=%d", $network_id));
		}
	}

	/**
	 * Get all primary sites in a multi-network setup
	 */
	private function get_primary_sites() {
		global $wpdb;
		if(function_exists('get_networks')){
			return array_map(function($network){ return $network->blog_id; }, get_networks());
		}
		else{
			return $wpdb->get_col("SELECT blogs.blog_id FROM {$wpdb->site} sites JOIN {$wpdb->blogs} blogs ON blogs.site_id=sites.id AND blogs.path=sites.path");
		}
	}

	private function get_sites($from, $count) {
		global $wpdb;
		return $wpdb->get_col($wpdb->prepare("SELECT `blog_id` FROM `{$wpdb->blogs}` WHERE `deleted` = 0 AND blog_id > %d ORDER BY blog_id LIMIT %d", $from, $count));
	}

	/**
	 * Sync role capabilities from the default site to a newly added site
	 * @param int $network_id the relevant network
	 * @param int $site_id the newly added site(blog)
	 */
	private function sync_roles($network_id, $site_id){
		if(array_key_exists($network_id, $this->network_roles)){
			$current_roles=$this->network_roles[$network_id];
		}
		else{
			$current_roles=$this->_wp_roles($this->get_primary_site_id($network_id));
			$this->network_roles[$network_id]=$current_roles;
		}
		$new_site_roles=$this->_wp_roles($site_id);
		$capabilities=array(
			self::CAP_ACTIVATE_2FA_SELF,
			self::CAP_ACTIVATE_2FA_OTHERS,
			self::CAP_MANAGE_SETTINGS
		);
		foreach($current_roles->get_names() as $role_name=>$role_label){
			if($new_site_roles->get_role($role_name)===null)
				$new_site_roles->add_role($role_name, $role_label);
			$role=$current_roles->get_role($role_name);
			foreach($capabilities as $cap){
				if($role->has_cap($cap)){
					$this->_add_cap_multisite($role_name, $cap, array($site_id));
				}
				else{
					$this->_remove_cap_multisite($role_name, $cap, array($site_id));
				}
			}
		}
	}
	
	public function allow_2fa_self($role_name) {
		$this->on_role_change();
		if (is_multisite()) {
			$this->_add_cap_multisite($role_name, self::CAP_ACTIVATE_2FA_SELF, $this->get_primary_sites());
		}
		else {
			$this->_add_cap($role_name, self::CAP_ACTIVATE_2FA_SELF);
		}
	}
	
	public function disallow_2fa_self($role_name) {
		$this->on_role_change();
		if (is_multisite()) {
			$this->_remove_cap_multisite($role_name, self::CAP_ACTIVATE_2FA_SELF, $this->get_primary_sites());
		}
		else {
			if ($role_name == 'administrator') {
				return;
			}
			$this->_remove_cap($role_name, self::CAP_ACTIVATE_2FA_SELF);
		}
	}
	
	public function can_manage_settings($user = false) {
		if ($user === false) {
			$user = wp_get_current_user();
		}
		
		if (!($user instanceof \WP_User)) {
			return false;
		}
		return $user->has_cap(self::CAP_MANAGE_SETTINGS);
	}
	
	private function _wp_roles($site_id = null) {
		require(ABSPATH . 'wp-includes/version.php'); /** @var string $wp_version */
		if (version_compare($wp_version, '4.9', '>=')) {
			return new \WP_Roles($site_id);
		}
		
		//\WP_Roles in WP < 4.9 initializes based on the current blog ID
		if (is_multisite()) {
			switch_to_blog($site_id);
		}
		$wp_roles = new \WP_Roles();
		if (is_multisite()) {
			restore_current_blog();
		}
		return $wp_roles;
	}
	
	private function _add_cap_multisite($role_name, $cap, $blog_ids=null) {
		global $wpdb;
		$blogs = $blog_ids===null?$wpdb->get_col("SELECT `blog_id` FROM `{$wpdb->blogs}` WHERE `deleted` = 0"):$blog_ids;
		foreach ($blogs as $id) {
			$wp_roles = $this->_wp_roles($id);
			switch_to_blog($id);
			$this->_add_cap($role_name, $cap, $wp_roles);
			restore_current_blog();
		}
	}
	
	private function _add_cap($role_name, $cap, $wp_roles = null) {
		if ($wp_roles === null) { $wp_roles = $this->_wp_roles(); }
		$role = $wp_roles->get_role($role_name);
		if ($role === null) {
			return false;
		}
		
		$wp_roles->add_cap($role_name, $cap);
		return true;
	}
	
	private function _remove_cap_multisite($role_name, $cap, $blog_ids=null) {
		global $wpdb;
		$blogs = $blog_ids===null?$wpdb->get_col("SELECT `blog_id` FROM `{$wpdb->blogs}` WHERE `deleted` = 0"):$blog_ids;
		foreach ($blogs as $id) {
			$wp_roles = $this->_wp_roles($id);
			switch_to_blog($id);
			$this->_remove_cap($role_name, $cap, $wp_roles);
			restore_current_blog();
		}
	}
	
	private function _remove_cap($role_name, $cap, $wp_roles = null) {
		if ($wp_roles === null) { $wp_roles = $this->_wp_roles(); }
		$role = $wp_roles->get_role($role_name);
		if ($role === null) {
			return false;
		}
		
		$wp_roles->remove_cap($role_name, $cap);
		return true;
	}
}