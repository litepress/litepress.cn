<?php

if (!defined('ABSPATH')) {
	exit;
}

/**
* array filter for finding users who have subscribed to GlotPress projects
*/
class GPNotifyFilterUsers {

	protected $project_id;

	/**
	* filter the array, returning only users subscribed to specified project
	* @param array[stdClass] $users
	* @param int $project_id
	* @return array
	*/
	public function execute($users, $project_id) {
		$this->project_id = $project_id;
		return array_filter($users, array($this, '_filter'));
	}

	/**
	* array_filter() callback, return true if user has subscribed to project
	* @param stdClass $user
	* @return bool
	*/
	public function _filter($user) {
		return !empty($user->projects[$this->project_id]);
	}

}
