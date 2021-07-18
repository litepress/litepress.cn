<?php

class wfAdminNoticeQueue {
	protected static function _notices() {
		return wfConfig::get_ser('adminNoticeQueue', array());
	}
	
	protected static function _setNotices($notices) {
		wfConfig::set_ser('adminNoticeQueue', $notices);
	}
	
	/**
	 * Adds an admin notice to the display queue.
	 * 
	 * @param string $severity
	 * @param string $messageHTML
	 * @param bool|string $category If not false, notices with the same category will be removed prior to adding this one.
	 * @param bool|array $users If not false, an array of user IDs the notice should show for.
	 */
	public static function addAdminNotice($severity, $messageHTML, $category = false, $users = false) {
		$notices = self::_notices();
		foreach ($notices as $id => $n) {
			$usersMatches = false;
			if (isset($n['users'])) {
				$usersMatches = wfUtils::sets_equal($n['users'], $users);
			}
			else if ($users === false) {
				$usersMatches = true;
			}
			
			$categoryMatches = false;
			if ($category !== false && isset($n['category']) && $n['category'] == $category) {
				$categoryMatches = true;
			}
			
			if ($usersMatches && $categoryMatches) {
				unset($notices[$id]);
			}
		}
		
		$id = wfUtils::uuid();
		$notices[$id] = array(
			'severity' => $severity,
			'messageHTML' => $messageHTML,
		);
		
		if ($category !== false) {
			$notices[$id]['category'] = $category;
		}
		
		if ($users !== false) {
			$notices[$id]['users'] = $users;
		}
		
		self::_setNotices($notices);
	}
	
	/**
	 * Removes an admin notice using one of three possible search methods:
	 * 
	 * 	1. If $id matches. $category and $users are ignored
	 * 	2. If $category matches. $users must be false for this.
	 * 	3. If $category matches and the notice's user IDs matches $users.
	 * 
	 * @param bool|int $id
	 * @param bool|string $category
	 * @param bool|int[] $users
	 */
	public static function removeAdminNotice($id = false, $category = false, $users = false) {
		if ($id === false && $category === false && $users === false) {
			return;
		}
		else if ($id !== false) {
			$category = false;
			$users = false;
		}
		
		$notices = self::_notices();
		$found = false;
		foreach ($notices as $nid => $n) {
			if ($id == $nid) { //ID match
				unset($notices[$nid]);
				$found=true;
				break;
			}
			else if ($id !== false) {
				continue;
			}
			
			if ($category !== false && isset($n['category']) && $category == $n['category']) {
				if ($users !== false) {
					if (isset($n['users']) && wfUtils::sets_equal($users, $n['users'])) {
						unset($notices[$nid]);
						$found=true;
					}
				}
				else {
					unset($notices[$nid]);
					$found=true;
				}
			}
		}
		if($found)
			self::_setNotices($notices);
	}
	
	public static function hasNotice($category = false, $users = false) {
		$notices = self::_notices();
		foreach ($notices as $nid => $n) {
			$categoryMatches = false;
			if (($category === false && !isset($n['category'])) || ($category !== false && isset($n['category']) && $category == $n['category'])) {
				$categoryMatches = true;
			}
			
			$usersMatches = false;
			if (($users === false && !isset($n['users'])) || ($users !== false && isset($n['users']) && wfUtils::sets_equal($users, $n['users']))) {
				$usersMatches = true;
			}
			
			if ($categoryMatches && $usersMatches) {
				return true;
			}
		}
		return false;
	}
	
	public static function enqueueAdminNotices() {
		$user = wp_get_current_user();
		if ($user->ID == 0) {
			return false;
		}
		
		$networkAdmin = is_multisite() && is_network_admin();
		$notices = self::_notices();
		$added = false;
		foreach ($notices as $nid => $n) {
			if (isset($n['users']) && array_search($user->ID, $n['users']) === false) {
				continue;
			}
			
			$notice = new wfAdminNotice($nid, $n['severity'], $n['messageHTML']);
			if ($networkAdmin) {
				add_action('network_admin_notices', array($notice, 'displayNotice'));
			}
			else {
				add_action('admin_notices', array($notice, 'displayNotice'));
			}
			
			$added = true;
		}
		
		return $added;
	}
}

class wfAdminNotice {
	const SEVERITY_CRITICAL = 'critical';
	const SEVERITY_WARNING = 'warning';
	const SEVERITY_INFO = 'info';
	
	private $_id;
	private $_severity;
	private $_messageHTML;
	
	public function __construct($id, $severity, $messageHTML) {
		$this->_id = $id;
		$this->_severity = $severity;
		$this->_messageHTML = $messageHTML;
	}
	
	public function displayNotice() {
		$severityClass = 'notice-info';
		if ($this->_severity == self::SEVERITY_CRITICAL) {
			$severityClass = 'notice-error';
		}
		else if ($this->_severity == self::SEVERITY_WARNING) {
			$severityClass = 'notice-warning';
		}
		
		echo '<div class="wf-admin-notice notice ' . $severityClass . '" data-notice-id="' . esc_attr($this->_id) . '"><p>' . $this->_messageHTML . '</p><p><a class="wf-btn wf-btn-default wf-btn-sm wf-dismiss-link" href="#" onclick="wordfenceExt.dismissAdminNotice(\'' . esc_attr($this->_id) . '\'); return false;">' . esc_html__('Dismiss', 'wordfence') . '</a></p></div>';
	}
}