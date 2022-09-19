<?php
/*
 * PublishPress Capabilities [Free]
 * 
 * Process update operations from the Capabilities screen
 * 
 */

class CapsmanHandler
{
	var $cm;

	function __construct($manager_obj = false) {
		if ($manager_obj) {
			$this->cm = $manager_obj;
		} else {
			global $capsman;
			$this->cm = $capsman;
		}

		require_once (dirname(CME_FILE) . '/includes/roles/roles-functions.php');
	}
	
	function processAdminGeneral() {
		global $wpdb, $wp_roles;
		
		check_admin_referer('capsman-general-manager');
		
		if ( empty ($_POST['caps']) ) {
		    $_POST['caps'] = array();
		}

		if (!empty($_REQUEST['page']) && ('pp-capabilities-settings' == $_REQUEST['page'])) {
			do_action('publishpress-caps_process_update');
			return;
		}

		// Create a new role.
		if ( ! empty($_POST['CreateRole']) ) {
			if (!empty($_POST['create-name'])) {
				$newrole = $this->createRole(sanitize_text_field($_POST['create-name']));
			}

			if (!empty($newrole)) {
				ak_admin_notify(__('New role created.', 'capsman-enhanced'));
				$this->cm->set_current_role($newrole);
			} else {
				if ( empty($_POST['create-name']) && in_array(get_locale(), ['en_EN', 'en_US']) )
					ak_admin_error('Error: No role name specified.');
				else
					ak_admin_error(__('Error: Failed creating the new role.', 'capsman-enhanced'));
			}

		// Save role changes. Already saved at start with self::saveRoleCapabilities()
		} elseif ( ! empty($_POST['SaveRole']) && !empty($_POST['current'])) {
			if ( MULTISITE ) {
				( method_exists( $wp_roles, 'for_site' ) ) ? $wp_roles->for_site() : $wp_roles->reinit();
			}
			
			if (!pp_capabilities_is_editable_role(sanitize_key($_POST['current']))) {
				ak_admin_error(__('The selected role is not editable.', 'capsman-enhanced'));
				return;
			}

			$level = (isset($_POST['level'])) ? (int) $_POST['level'] : 0;
			$this->saveRoleCapabilities(sanitize_key($_POST['current']), array_map('boolval', $_POST['caps']), $level);
			
			if (defined( 'PRESSPERMIT_ACTIVE' ) && !empty($_POST['role'])) {  // log customized role caps for subsequent restoration
				// for bbPress < 2.2, need to log customization of roles following bbPress activation
				$plugins = ( function_exists( 'bbp_get_version' ) && version_compare( bbp_get_version(), '2.2', '<' ) ) ? array( 'bbpress.php' ) : array();	// back compat

				if ( ! $customized_roles = get_option( 'pp_customized_roles' ) )
					$customized_roles = array();
				
				$_role = sanitize_key($_POST['role']);

				$customized_roles[$_role] = (object) array( 'caps' => array_map( 'boolval', $_POST['caps'] ), 'plugins' => $plugins );
				update_option( 'pp_customized_roles', $customized_roles );
				
				$wpdb->query( "UPDATE $wpdb->options SET autoload = 'no' WHERE option_name = 'pp_customized_roles'" );
			}
		// Create New Capability and adds it to current role.
		} elseif (!empty($_POST['AddCap']) && !empty($_POST['current']) && !empty($_POST['capability-name'])) {
			if ( MULTISITE ) {
				( method_exists( $wp_roles, 'for_site' ) ) ? $wp_roles->for_site() : $wp_roles->reinit();
			}

			if (empty($_POST['current']) || !pp_capabilities_is_editable_role(sanitize_key($_POST['current']))) {
				ak_admin_error(__('The selected role is not editable.', 'capsman-enhanced'));
				return;
			}

			$role = get_role(sanitize_key($_POST['current']));
			$role->name = sanitize_key($_POST['current']);		// bbPress workaround

			$newname = $this->createNewName(sanitize_text_field($_POST['capability-name']), ['allow_dashes' => true]);

			if (empty($newname['error'])) {
				$role->add_cap($newname['name']);

				// for bbPress < 2.2, need to log customization of roles following bbPress activation
				$plugins = ( function_exists( 'bbp_get_version' ) && version_compare( bbp_get_version(), '2.2', '<' ) ) ? array( 'bbpress.php' ) : array();	// back compat
				
				if ( ! $customized_roles = get_option( 'pp_customized_roles' ) )
					$customized_roles = array();

				$customized_roles[sanitize_key($_POST['role'])] = (object) array( 'caps' => array_merge( $role->capabilities, array( $newname['name'] => 1 ) ), 'plugins' => $plugins );
				update_option( 'pp_customized_roles', $customized_roles );
				
				$wpdb->query( "UPDATE $wpdb->options SET autoload = 'no' WHERE option_name = 'pp_customized_roles'" );

				$redirect_role = (!empty($_POST['role'])) ? sanitize_key($_POST['role']) : '';

				$url = admin_url('admin.php?page=pp-capabilities&role=' . esc_attr($redirect_role) . '&added=1');
				wp_redirect($url);
				exit;
			} else {
				add_action('all_admin_notices', function() {
					ak_admin_notify(__('Incorrect capability name.', 'capsman-enhanced'));
				});
			}
			
		} elseif ( ! empty($_POST['update_filtered_types']) || ! empty($_POST['update_filtered_taxonomies']) || ! empty($_POST['update_detailed_taxonomies']) ) {
				ak_admin_notify(__('Type / Taxonomy settings saved.', 'capsman-enhanced'));
		} else {
			if (!apply_filters('publishpress-caps_submission_ok', false)) {
				ak_admin_error(__('Bad form received.', 'capsman-enhanced'));
			}
		}

		if ( ! empty($newrole) && defined('PRESSPERMIT_ACTIVE') ) {
			if ( ( ! empty($_POST['CreateRole']) && ! empty( $_REQUEST['new_role_pp_only'] ) ) || ( ! empty($_POST['CopyRole']) && ! empty( $_REQUEST['copy_role_pp_only'] ) ) ) {
				$pp_only = (array) pp_capabilities_get_permissions_option( 'supplemental_role_defs' );
				$pp_only[]= $newrole;

				pp_capabilities_update_permissions_option('supplemental_role_defs', $pp_only);
				
				_cme_pp_default_pattern_role( $newrole );
				pp_refresh_options();
			}
		}
	}

	
	/**
	 * Creates a new role/capability name from user input name.
	 * Name rules are:
	 * 		- 2-40 charachers lenght.
	 * 		- Only letters, digits, spaces and underscores.
	 * 		- Must to start with a letter.
	 *
	 * @param string $name	Name from user input.
	 * @return array|false An array with the name and display_name, or false if not valid $name.
	 */
	public function createNewName( $name, $args=[] ) {
		// Allow max 40 characters, letters, digits and spaces
		$name = trim(substr($name, 0, 40));
		$pattern = (!empty($args['allow_dashes'])) ? '/^[a-zA-Z][a-zA-Z0-9 _\-]+$/' : '/^[a-zA-Z][a-zA-Z0-9 _]+$/';

		if ( preg_match($pattern, $name) ) {
			$roles = ak_get_roles();

			$name = str_replace(' ', '_', $name);
			if ( in_array($name, $roles) || array_key_exists($name, $this->cm->capabilities) ) {
				return ['error' => 'role_exists', 'name' => $name];		// Already a role or capability with this name.
			}

			$display = explode('_', $name);
			$name = strtolower($name);

			// Apply ucfirst proper caps unless capitalization already provided
			foreach($display as $i => $word) {
				if ($word === strtolower($word)) {
					$display[$i] = ucfirst($word);
				}
			}

			$display = implode(' ', $display);

			return compact('name', 'display');
		} else {
			return ['error' => 'invalid_name', 'name' => $name];
		}
	}

	/**
	 * Creates a new role.
	 *
	 * @param string $name	Role name to create.
	 * @param array $caps	Role capabilities.
	 * @return string|false	Returns the name of the new role created or false if failed.
	 */
	public function createRole( $name, $caps = [], $args = [] ) {
		if ( ! is_array($caps) )
			$caps = array();

		$role = $this->createNewName($name);
		if (!empty($role['error'])) {
			return false;
		}

		$new_role = add_role($role['name'], $role['display'], $caps);
		if ( is_object($new_role) ) {
			return $role['name'];
		} else {
			return false;
		}
	}

	 /**
	  * Saves capability changes to roles.
	  *
	  * @param string $role_name Role name to change its capabilities
	  * @param array $caps New capabilities for the role.
	  * @return void
	  */
	private function saveRoleCapabilities( $role_name, $caps, $level ) {
		$this->cm->generateNames();
		$role = get_role($role_name);

		// workaround to ensure db storage of customizations to bbp dynamic roles
		$role->name = $role_name;
		
		$stored_role_caps = ( ! empty($role->capabilities) && is_array($role->capabilities) ) ? array_intersect( $role->capabilities, array(true, 1) ) : array();
		$stored_negative_role_caps = ( ! empty($role->capabilities) && is_array($role->capabilities) ) ? array_intersect( $role->capabilities, array(false) ) : array();
		
		$old_caps = array_intersect_key( $stored_role_caps, $this->cm->capabilities);
		$new_caps = ( is_array($caps) ) ? array_map('boolval', $caps) : array();
		$new_caps = array_merge($new_caps, ak_level2caps($level));

		// Find caps to add and remove
		$add_caps = array_diff_key($new_caps, $old_caps);
		$del_caps = array_diff_key(array_merge($old_caps, $stored_negative_role_caps), $new_caps);

		$changed_caps = array();
		foreach( array_intersect_key( $new_caps, $old_caps ) as $cap_name => $cap_val ) {
			if ( $new_caps[$cap_name] != $old_caps[$cap_name] )
				$changed_caps[$cap_name] = $cap_val;
		}
		
		$add_caps = array_merge( $add_caps, $changed_caps );
		
		if ( ! $is_administrator = current_user_can('administrator') ) {
			unset($add_caps['manage_capabilities']);
			unset($del_caps['manage_capabilities']);
		}

		if ( 'administrator' == $role_name && isset($del_caps['manage_capabilities']) ) {
			unset($del_caps['manage_capabilities']);
			ak_admin_error(__('You cannot remove Manage Capabilities from Administrators', 'capsman-enhanced'));
		}
		
		// additional safeguard against removal of read capability
		if ( isset( $del_caps['read'] ) && _cme_is_read_removal_blocked( $role_name ) ) {
			unset( $del_caps['read'] );
		}
		
		// Add new capabilities to role
		foreach ( $add_caps as $cap => $grant ) {
			if ( $is_administrator || current_user_can($cap) )
				$role->add_cap( $cap, $grant );
		}

		// Remove capabilities from role
		foreach ( $del_caps as $cap => $grant) {
			if ( $is_administrator || current_user_can($cap) )
				$role->remove_cap($cap);
		}
		
		$this->cm->log_db_roles();
		
		if (is_multisite() && is_super_admin() && is_main_site()) {
			if ( ! $autocreate_roles = get_site_option( 'cme_autocreate_roles' ) )
				$autocreate_roles = array();
			
			$this_role_autocreate = ! empty($_REQUEST['cme_autocreate_role']);
			
			if ( $this_role_autocreate && ! in_array( $role_name, $autocreate_roles ) ) {
				$autocreate_roles []= $role_name;
				update_site_option( 'cme_autocreate_roles', $autocreate_roles );
			}
			
			if ( ! $this_role_autocreate && in_array( $role_name, $autocreate_roles ) ) {
				$autocreate_roles = array_diff( $autocreate_roles, array( $role_name ) );
				update_site_option( 'cme_autocreate_roles', $autocreate_roles );
			}
			
			$do_role_sync = !empty($_REQUEST['cme_net_sync_role']);
			$do_option_sync = !empty($_REQUEST['cme_net_sync_options']);

			if ($do_role_sync || $do_option_sync) {
				// loop through all sites on network, creating or updating role def
		
				global $wpdb, $wp_roles, $blog_id;
				$blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs ORDER BY blog_id" );
				$orig_blog_id = $blog_id;	
		
				if ($do_role_sync) {
					$role_caption = $wp_roles->role_names[$role_name];
					
					$new_caps = ( is_array($caps) ) ? array_map('boolval', $caps) : array();
					$new_caps = array_merge($new_caps, ak_level2caps($level) );
					
					$admin_role = $wp_roles->get_role('administrator');
					$main_admin_caps = array_merge( $admin_role->capabilities, ak_level2caps(10) );
				}

				$sync_options = [];

				if ($do_option_sync) {
					// capability-related options
					$pp_prefix = (defined('PPC_VERSION') && !defined('PRESSPERMIT_VERSION')) ? 'pp' : 'presspermit';

					foreach(['define_create_posts_cap', 'enabled_post_types', 'enabled_taxonomies'] as $option_name) {
						$sync_options["{$pp_prefix}_$option_name"] = get_option("{$pp_prefix}_$option_name");
					}

					$sync_options['cme_detailed_taxonomies'] = get_option('cme_detailed_taxonomies');
					$sync_options['cme_enabled_post_types'] = get_option('cme_enabled_post_types');
					$sync_options['presspermit_supplemental_role_defs'] = get_option('presspermit_supplemental_role_defs');
				}

				foreach ( $blog_ids as $id ) {				
					if ( is_main_site($id) )
						continue;
					
					switch_to_blog( $id );

					if ($do_role_sync) {
						( method_exists( $wp_roles, 'for_site' ) ) ? $wp_roles->for_site() : $wp_roles->reinit();
						
						if ( $blog_role = $wp_roles->get_role( $role_name ) ) {
							$stored_role_caps = ( ! empty($blog_role->capabilities) && is_array($blog_role->capabilities) ) ? array_intersect( $blog_role->capabilities, array(true, 1) ) : array();
							
							$old_caps = array_intersect_key( $stored_role_caps, $this->cm->capabilities);

							// Find caps to add and remove
							$add_caps = array_diff_key($new_caps, $old_caps);
							$del_caps = array_intersect_key( array_diff_key($old_caps, $new_caps), $main_admin_caps );	// don't mess with caps that are totally unused on main site
							
							// Add new capabilities to role
							foreach ( $add_caps as $cap => $grant ) {
								$wp_roles->roles[$role_name]['capabilities'][$cap] = $grant;

							}

							// Remove capabilities from role
							foreach ( $del_caps as $cap => $grant) {
								unset($wp_roles->roles[$role_name]['capabilities'][$cap]);
							}

							if ($wp_roles->use_db) {
								update_option($wp_roles->role_key, $wp_roles->roles);
							}
						} else {
							$wp_roles->add_role( $role_name, $role_caption, $new_caps );
						}
					}

					foreach($sync_options as $option_name => $option_val) {
						update_option($option_name, $option_val);
					}
					
					restore_current_blog();
				}
				
				( method_exists( $wp_roles, 'for_site' ) ) ? $wp_roles->for_site() : $wp_roles->reinit();
			}
		} // endif multisite installation with super admin editing a main site role

		pp_capabilities_autobackup();
	}
}

if ( ! function_exists('boolval') ) {
	function boolval( $val ) {
		return (bool) $val;
	}
}
