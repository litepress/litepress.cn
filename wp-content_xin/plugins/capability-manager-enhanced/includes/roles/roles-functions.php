<?php

/**
 * Helper method to get the main role instance
 *
 * @return PP_Capabilities_Roles
 * @access   global
 */
function pp_capabilities_roles()
{
    if (!class_exists('PP_Capabilities_Roles')) {
        require_once (dirname(__FILE__) . '/class/class-pp-roles.php');
        $roles = pp_capabilities_roles()->run();
    }

    return PP_Capabilities_Roles::instance();
}

/**
 * Roles page load
 */
function admin_roles_page_load()
{
    $plugin_name = 'capsman';
    //enqueue styles
    wp_enqueue_style($plugin_name, plugin_dir_url(CME_FILE) . 'includes/roles/css/pp-roles-admin.css', [], PUBLISHPRESS_CAPS_VERSION, 'all');

    //enqueue scripts
    wp_enqueue_script($plugin_name . '_table_edit', plugin_dir_url(CME_FILE) . 'includes/roles/js/pp-roles-admin.js', ['jquery'], PUBLISHPRESS_CAPS_VERSION, false);
    wp_enqueue_script('pp-capabilities-chosen-js', plugin_dir_url(CME_FILE) . 'common/libs/chosen-v1.8.7/chosen.jquery.js', ['jquery'], PUBLISHPRESS_CAPS_VERSION);

    //Localize
    wp_localize_script($plugin_name . '_table_edit', 'pp_roles_i18n', ['confirm_delete' => __('Are you sure you want to delete this role?', 'capsman-enhanced')]);
    wp_enqueue_style('pp-capabilities-chosen-css', plugin_dir_url(CME_FILE) . 'common/libs/chosen-v1.8.7/chosen.css', false, PUBLISHPRESS_CAPS_VERSION);

    //initialize table here to be able to register default WP_List_Table screen options
    pp_capabilities_roles()->admin->get_roles_list_table();

    //Handle actions
    pp_capabilities_roles()->admin->handle_actions();

    //Add screen options
    add_screen_option('per_page', ['default' => 999]);
}


/**
 * Conditional tag to check whether the currently logged-in user has a specific role.
 *
 * @access public
 * @param string|array $roles
 * @return bool
 */
function pp_roles_current_user_has_role($roles)
{

    return is_user_logged_in() ? pp_roles_user_has_role(get_current_user_id(), $roles) : false;
}


/**
 * Conditional tag to check whether a user has a specific role.
 *
 * @access public
 * @param int $user_id
 * @param string|array $roles
 * @return bool
 */
function pp_roles_user_has_role($user_id, $roles)
{

    $user = new WP_User($user_id);

    foreach ((array)$roles as $role) {

        if (in_array($role, (array)$user->roles))
            return true;
    }

    return false;
}

/**
 * Check if role exist and return it data
 *
 * @param string $role_name
 * @return bool|WP_Role
 */
function pp_roles_get_role_data($role_name)
{

    $role = false;
    $all_roles      = pp_capabilities_roles()->manager->get_roles_for_list_table('all', true);

    foreach ($all_roles as $role_data) {
        if ($role_name === $role_data['role']) {
            $role    = $role_data;
            break;
        }
    }

    return $role;
}


/**
 * Remove capabilities role levels
 *
 * @param array $capabilities
 * @return array
 */
function pp_roles_remove_capabilities_role_level($capabilities)
{

    for($i = 0; $i<=10; $i++) {
		if (array_key_exists("level_{$i}", $capabilities)) {
			unset($capabilities["level_{$i}"]);
		}
    }

	return $capabilities;
}

/**
 * Get editor features restriction for a role
 *
 * @param string $role role to check
 * @param boolean $check whether to check database or not
 * @return integer
 */
function pp_capabilities_roles_editor_features($role, $check = false)
{
    if ($role && $check) {
        $def_post_types = array_unique(apply_filters('pp_capabilities_feature_post_types', ['post', 'page']));
        $disabled_items = [];
        foreach ($def_post_types as $type_name) {
            $classic_disabled = get_option("capsman_feature_restrict_classic_{$type_name}", []);
            $gutenberg_disabled = get_option("capsman_feature_restrict_{$type_name}", []);
            if (!empty($classic_disabled[$role])) {
                $disabled_items = array_merge($disabled_items, (array) $classic_disabled[$role]);
            }
            if (!empty($gutenberg_disabled[$role])) {
                $disabled_items = array_merge($disabled_items, (array) $gutenberg_disabled[$role]);
            }
        }
        $disabled_items = array_filter($disabled_items);

        return count($disabled_items);
    } else {
        return 0;
    }
}

/**
 * Get admin features restriction for a role
 *
 * @param string $role role to check
 * @param boolean $check whether to check database or not
 * @return integer
 */
function pp_capabilities_roles_admin_features($role, $check = false)
{
    if ($role && $check) {
        $disabled_items = !empty(get_option('capsman_disabled_admin_features')) ? (array)get_option('capsman_disabled_admin_features') : [];
        $disabled_items = array_key_exists($role, $disabled_items) ? (array)$disabled_items[$role] : [];
        $disabled_items = array_filter($disabled_items);
        return count($disabled_items);
    } else {
        return 0;
    }
}

/**
 * Get admin menus restriction for a role
 *
 * @param string $role role to check
 * @param boolean $check whether to check database or not
 * @return integer
 */
function pp_capabilities_roles_admin_menus($role, $check = false)
{
    if ($role && $check) {
        $nav_menu_item_option = !empty(get_option('capsman_nav_item_menus')) ? get_option('capsman_nav_item_menus') : [];
        $nav_menu_item_option = array_key_exists($role, $nav_menu_item_option) ? (array)$nav_menu_item_option[$role] : [];
    
        $disabled_items = array_filter($nav_menu_item_option);
    
        return count($disabled_items);
    } else {
        return 0;
    }
}

/**
 * Get nav menus restriction for a role
 *
 * @param string $role role to check
 * @param boolean $check whether to check database or not
 * @return integer
 */
function pp_capabilities_roles_nav_menus($role, $check = false)
{
    if ($role && $check) {
        $nav_menu_item_option = !empty(get_option('capsman_nav_item_menus')) ? get_option('capsman_nav_item_menus') : [];
        $nav_menu_item_option = array_key_exists($role, $nav_menu_item_option) ? (array)$nav_menu_item_option[$role] : [];

        $disabled_items = array_filter($nav_menu_item_option);

        return count($disabled_items);
    } else {
        return 0;
    }
}


