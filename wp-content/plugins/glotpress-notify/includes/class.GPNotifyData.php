<?php

if (!defined('ABSPATH')) {
	exit;
}

/**
* GlotPress data access
*/
class GPNotifyData {

	// table prefix and full table names
	public $gp_prefix;
	public $api_keys;
	public $glossaries;
	public $glossary_entries;
	public $meta;
	public $originals;
	public $permissions;
	public $projects;
	public $translation_sets;
	public $translations;

	/**
	* static method for getting the instance of this singleton object
	* @param string $gp_prefix
	* @return self
	* @throws GPNotifyException
	*/
	public static function getInstance($gp_prefix) {
		static $instance = null;

// TODO: throw new exception type for no prefix, catch / ignore in caller

		if (is_null($instance)) {
			$instance = new self($gp_prefix);
		}

		return $instance;
	}

	/**
	* initialise for table prefix, throws an exception if it doesn't look like GlotPress lives there
	* @param string $gp_prefix
	* @throws GPNotifyException
	*/
	protected function __construct($gp_prefix) {
		global $wpdb;

		// attempt to describe projects table
		$table = $wpdb->get_col("show columns from {$gp_prefix}translation_sets", 0);
		if (is_array($table)) {
			$table = array_flip($table);
			if (isset($table['id']) && isset($table['name']) && isset($table['slug']) && isset($table['locale'])) {
				// smells like GlotPress, set up table names and continue
				$this->gp_prefix			= $gp_prefix;
				$this->api_keys				= $gp_prefix . 'api_keys';
				$this->glossaries			= $gp_prefix . 'glossaries';
				$this->glossary_entries		= $gp_prefix . 'glossary_entries';
				$this->meta					= $gp_prefix . 'meta';
				$this->originals			= $gp_prefix . 'originals';
				$this->permissions			= $gp_prefix . 'permissions';
				$this->projects				= $gp_prefix . 'projects';
				$this->translation_sets		= $gp_prefix . 'translation_sets';
				$this->translations			= $gp_prefix . 'translations';
				return;
			}
		}

		// if we made it here, $gp_prefix doesn't give us GlotPress tables
		throw new GPNotifyException(sprintf(__('Not a GlotPress table prefix: %s', 'glotpress-notify'), $gp_prefix));
	}

	/**
	* duplicate gp_sanitize_meta_key() from GlotPress meta.php, so we can maintain compatibility
	* @param string $key
	* @return string
	*/
	protected static function gp_sanitize_meta_key($key) {
		return preg_replace('|[^a-z0-9_]|i', '', $key);
	}

	/**
	* get a GlotPress option
	* @param string $option_name
	* @return mixed
	*/
	public function getOption($option_name) {
		global $wpdb;

		$option_name = self::gp_sanitize_meta_key($option_name);

		// see whether we've tried to fetch before and failed
		if (wp_cache_get($option_name, 'gp_option_not_set')) {
			// failed to fetch before, don't waste resources trying again
			$value = null;
		}
		else {
			// try to get from object cache
			$value = wp_cache_get($option_name, 'gp_option');
			if ($value === false) {
				// not in object cache, fetch from database
				$sql = "
					select meta_value
					from {$this->meta}
					where object_type = 'gp_option'
					and meta_key = %s
				";
				$row = $wpdb->get_row($wpdb->prepare($sql, $option_name));

				if (is_object($row)) {
					$value = maybe_unserialize($row->meta_value);
				}
				else {
					$value = null;
				}
			}
		}

		if ($value === null) {
			// remember that we failed to get the value
			wp_cache_set($option_name, true, 'gp_option_not_set');
		}
		else {
			// remember the value to fetch it faster next time
			wp_cache_set($option_name, $value, 'gp_option');
		}

		return $value;
	}

	/**
	* get URI for a GlotPress project
	* @param string $project_slug
	* @return string|false
	*/
	public function getProjectURI($project_slug) {
		$uri = false;

		$gp_uri = $this->getOption('uri');
		if ($gp_uri) {
			$uri = sprintf('%s/projects/%s', untrailingslashit($gp_uri), $project_slug);
		}

		return $uri;
	}

	/**
	* get URI for a GlotPress translation set
	* @param string $project_slug
	* @param string $locale
	* @param string $locale_slug
	* @return string|false
	*/
	public function getTranslationURI($project_slug, $locale, $locale_slug) {
		$uri = false;

		$gp_uri = $this->getOption('uri');
		if ($gp_uri) {
			$uri = sprintf('%s/projects/%s/%s/%s', untrailingslashit($gp_uri), $project_slug, $locale, $locale_slug);
		}

		return $uri;
	}

	/**
	* get list of projects in GlotPress
	*/
	public function listProjects() {
		global $wpdb;

		$rows = $wpdb->get_results("select `id`, `name`, `slug`, `path` from {$this->projects} order by `name`");

		$projects = array();
		foreach ($rows as $row) {
			$row->project_uri = $this->getProjectURI($row->slug);
			$projects[$row->id] = $row;
		}

		return $projects;
	}

	/**
	* get list of admins in GlotPress, keyed by user ID
	* @return array
	*/
	public function listAdmins() {
		global $wpdb;

		$sql = "
			select p.user_id, u.user_email, u.display_name
			from {$this->permissions} p
			join {$wpdb->users} u on u.id = p.user_id
			where p.action = 'admin'
		";

		$rows = $wpdb->get_results($sql);
		$users = array();
		foreach ($rows as $row) {
			$users[$row->user_id] = $row;
		}

		return $users;
	}

	/**
	* get list of WordPress / GlotPress users who want GlotPress notifications, keyed by user ID
	* @return array
	*/
	public function listSubscribers() {
		global $wpdb;

		// user options are specific to a blog, and ours are specific to a GlotPress table prefix
		$option_name = "{$wpdb->get_blog_prefix()}gpnotify_{$this->gp_prefix}projects";

		$sql = "
			select um.user_id, u.user_email, u.display_name, um.meta_value as projects
			from {$wpdb->usermeta} um
			join {$wpdb->users} u on u.id = um.user_id
			where um.meta_key = %s
		";

		$rows = $wpdb->get_results($wpdb->prepare($sql, $option_name));

		// collect list of users who want notifications on waiting translation strings
		$users = array();
		foreach ($rows as $row) {
			$projects = maybe_unserialize($row->projects);
			if (!empty($projects['waiting'])) {
				$users[$row->user_id] = $row;
			}
		}

		return $users;
	}

	/**
	* get counts of translations waiting to be validated, keyed by project ID
	* @return array
	*/
	public function listWaitingByProject() {
		global $wpdb;

		$sql = "
			select s.id, s.project_id, s.name as locale_name, s.locale, s.slug,
				sum(if(t.status = 'current', 1, 0)) as `current`,
				sum(if(t.status = 'waiting', 1, 0)) as `waiting`
			from {$this->translations} as t
			join {$this->translation_sets} as s on t.translation_set_id = s.id
			group by s.id, s.project_id, s.name, s.locale, s.slug
			having sum(if(t.status = 'waiting', 1, 0)) > 0
			order by s.project_id, s.locale
		";

		$rows = $wpdb->get_results($sql);

		$waiting = array();
		foreach ($rows as $row) {
			$waiting[$row->project_id][] = $row;
		}

		// populate the translation links
		$projects = $this->listProjects();
		foreach ($waiting as $project_id => $translations) {
			if (!empty($projects[$project_id])) {
				foreach ($translations as $key => $translation) {
					$translation_uri = $this->getTranslationURI($projects[$project_id]->slug, $translation->locale, $translation->slug);
					$waiting[$project_id][$key]->translation_uri = $translation_uri;
				}
			}
		}

		return $waiting;
	}

}
