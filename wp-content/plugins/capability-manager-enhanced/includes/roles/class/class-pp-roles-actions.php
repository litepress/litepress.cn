<?php

class Pp_Roles_Actions
{

    /**
     * @var string
     */
    protected $capability = 'manage_options';

    /**
     * @var Pp_Roles_Manager
     */
    protected $manager = null;

    /**
     * @var array
     */
    protected $actions = [
        'pp-roles-add-role',
        'pp-roles-edit-role',
        'pp-roles-delete-role',
        'pp-roles-hide-role',
        'pp-roles-unhide-role',
    ];

    /**
     * Pp_Roles_Actions constructor.
     */
    public function __construct()
    {
        $this->manager = pp_capabilities_roles()->manager;

        if (did_action('wp_ajax_pp-roles-add-role') || did_action('wp_ajax_pp-roles-delete-role')) {
            $this->handle();
        }
    }

    /**
     * Is ajax request
     *
     * @return bool
     */
    protected function is_ajax()
    {
        return (defined('DOING_AJAX') && DOING_AJAX);
    }

    /**
     * Handle post actions
     */
    public function handle()
    {
        $current_action = $this->current_action();

        if (in_array($current_action, $this->actions)) {
            $current_action = str_replace('pp-roles-', '', $current_action);
            $current_action = str_replace('-', '_', $current_action);
            $this->$current_action();
        }
    }

    /**
     * Get the current action selected from the bulk actions dropdown.
     *
     * @return string|false The action name or False if no action was selected
     */
    protected function current_action()
    {
        if (isset($_REQUEST['filter_action']) && !empty($_REQUEST['filter_action'])) {
            return false;
        }

        if (isset($_REQUEST['action']) && -1 != $_REQUEST['action']) {
            return sanitize_key($_REQUEST['action']);
        }

        if (isset($_REQUEST['action2']) && -1 != $_REQUEST['action2']) {
            return sanitize_key($_REQUEST['action2']);
        }

        return false;
    }

    protected function notify_success($message) {
        $this->notify($message, 'success', false);
    }

    protected function notify_info($message) {
        $this->notify($message, 'info', false);
    }

    protected function notify_error($message) {
        $this->notify($message, 'error', false);
    }

    /**
     * Notify the user with a message. Handles ajax and post requests
     *
     * @param string $message The message to show to the user
     * @param string $type The type of message to show [error|success|warning\info]
     * @param bool $redirect If we should redirect to referrer
     * @param bool|string $redirect_url url to redirect to if provided
     */
    protected function notify($message, $type = 'error', $redirect = true, $redirect_url = false)
    {
        if (!in_array($type, ['error', 'success', 'warning'])) {
            $type = 'error';
        }

        if ($this->is_ajax()) {
            $format = '<div class="notice notice-%s is-dismissible"><p>%s</p></div>';
            wp_send_json_error(sprintf($format, $type, $message));
            exit;
        } else {
            //enqueue message
            pp_capabilities_roles()->notify->add($type, $message);

            if (!empty($_REQUEST['page']) && ('pp-capabilities' == $_REQUEST['page'])) {
                $redirect = false;
            }

            if ($redirect) {
                if (!$redirect_url) {
                    $redirect_url = wp_get_referer();
                    $redirect_url = wp_get_raw_referer();
                
                    if (empty($redirect_url)) {
                        $params = [
                        'page' => 'pp-capabilities-roles',
                    ];
                        $redirect_url = esc_url_raw(add_query_arg($params, admin_url('admin.php')));
                    }
                }
                wp_safe_redirect($redirect_url);
                die();
            }
        }
    }

    /**
     * Check if the user is able to access this page
     */
    protected function check_permissions()
    {

        if (!current_user_can($this->capability)) {
            $this->notify(esc_html__('You do not have sufficient permissions to perform this action.', 'capsman-enhanced'));
        }
    }

    /**
     * Check nonce and notify if error
     *
     * @param string $action
     * @param string $query_arg
     */
    protected function check_nonce($action = '-1', $query_arg = '_wpnonce')
    {
        $checked = isset($_REQUEST[$query_arg]) && wp_verify_nonce(sanitize_key($_REQUEST[$query_arg]), $action);
        if (!$checked) {
            $this->notify(esc_html__('Your link has expired, refresh the page and try again.', 'capsman-enhanced'));
        }
    }

    /**
     * Handles add role action
     */
    public function add_role()
    {
        /**
         * Check capabilities
         */
        $this->check_permissions();

        /**
         * Check nonce
         */
        if (!isset($_REQUEST['_wpnonce']) || !wp_verify_nonce(sanitize_key($_REQUEST['_wpnonce']), 'add-role')) {
            $this->notify(esc_html__('Your link has expired, refresh the page and try again.', 'capsman-enhanced'));
        }

        if (empty($_REQUEST['role_name'])) {
            $this->notify(esc_html__('Missing parameters, refresh the page and try again.', 'capsman-enhanced'));
        }

        if (empty($_REQUEST['role_slug'])) {
            $role_slug = str_replace(
                [' ', '(', ')', '&', '#', '@', '+', ','], 
                '_', 
                strtolower(sanitize_text_field($_REQUEST['role_name']))
            );

            $role_slug = preg_replace('/[^0-9a-zA-Z\-\_]/', '', $role_slug);
        } else {
            $role_slug = sanitize_key($_REQUEST['role_slug']);
        }

        /**
         * Validate input data
         */
        require_once(dirname(CME_FILE).'/includes/handler.php');
        $capsman_handler = new CapsmanHandler();
        $role = $capsman_handler->createNewName(sanitize_key($role_slug));
        
        /**
         * Check for invalid name entry
         */
        if (!empty($role['error']) && ('invalid_name' == $role['error'])) {
            $out = sprintf(
                __('Invalid role name entry: %s', 'capsman-enhanced'), 
                esc_html($role['name'])
            );
            $this->notify($out);
        }

        /**
         * Check role doesn't exist
         */
        if (!empty($role['error']) && ('role_exists' == $role['error'])) {
            //this role already exist
            $out = sprintf(
                __('The role "%s" already exists. Please choose a different name.', 'capsman-enhanced'),
                esc_html($role['name'])
            );

            $this->notify($out);
        }

        /**
         * Add role
         */
        $role_capabilities = [];
        $copied_role       = false;
        
        //get copied role capabilites
        if (!empty($_REQUEST['role_action']) && $_REQUEST['role_action'] === 'copy'
            && !empty($_REQUEST['role'])
            && $role_data = pp_roles_get_role_data(sanitize_key($_REQUEST['role']))
        ) {
            $role_capabilities = $role_data['capabilities'];
            $copied_role       = sanitize_key($_REQUEST['role']);
        }

        if (isset($_REQUEST['role_level'])) {
            $role_capabilities = array_merge($role_capabilities, ak_level2caps(absint($_REQUEST['role_level'])));
        }
        $result = add_role($role['name'], sanitize_text_field($_REQUEST['role_name']), $role_capabilities);
        if (!$result instanceof WP_Role) {
            if ($this->notify(esc_html__('Something went wrong, the system wasn\'t able to create the role, refresh the page and try again.', 'capsman-enhanced'))) {
                return;
            }
        }


        //update role options
        $role_option    = [];
        $role_option['role_editor']         = (!empty($_REQUEST['role_editor']) && is_array(($_REQUEST['role_editor']))) ? array_map('sanitize_text_field', $_REQUEST['role_editor']) : [];
        $role_option['login_redirect']      = !empty($_REQUEST['login_redirect']) ? home_url(str_replace(home_url(), '', sanitize_text_field($_REQUEST['login_redirect']))) : '';
        $role_option['logout_redirect']     = !empty($_REQUEST['logout_redirect']) ? home_url(str_replace(home_url(), '', sanitize_text_field($_REQUEST['logout_redirect']))) : '';
        $role_option['referer_redirect']    = !empty($_REQUEST['referer_redirect']) ? (int) $_REQUEST['referer_redirect'] : 0;
        $role_option['custom_redirect']     = !empty($_REQUEST['custom_redirect']) ? (int) $_REQUEST['custom_redirect'] : 0;
        $role_option['disable_code_editor'] = !empty($_REQUEST['disable_code_editor']) ? (int) $_REQUEST['disable_code_editor'] : 0;
        $role_option['disable_role_user_login'] = !empty($_REQUEST['disable_role_user_login']) ? (int) $_REQUEST['disable_role_user_login'] : 0;
        if (defined('WC_PLUGIN_FILE')) {
            $role_option['disable_woocommerce_admin_restrictions'] = !empty($_REQUEST['disable_woocommerce_admin_restrictions']) ? (int) $_REQUEST['disable_woocommerce_admin_restrictions'] : 0;
        }
        update_option('pp_capabilities_' . $role['name'] . '_role_option', $role_option);

        /**
         * Copy all features to new role
         */
        if ($copied_role) {
            $role_slug = $role['name'];
            //Editor Features
            $classic_editor = pp_capabilities_is_classic_editor_available();
            $def_post_types = array_unique(apply_filters('pp_capabilities_feature_post_types', ['post', 'page']));
            foreach ($def_post_types as $post_type) {
                if ($classic_editor) {
                    $post_features_option = get_option("capsman_feature_restrict_classic_{$post_type}", []);
                    if (is_array($post_features_option) && array_key_exists($copied_role, $post_features_option)) {
						$post_features_option[$role_slug] = $post_features_option[$copied_role];
						update_option("capsman_feature_restrict_classic_{$post_type}", $post_features_option, false);
                    }
                }
                $post_features_option = get_option("capsman_feature_restrict_{$post_type}", []);
                if (is_array($post_features_option) && array_key_exists($copied_role, $post_features_option)) {
                    $post_features_option[$role_slug] = $post_features_option[$copied_role];
                    update_option("capsman_feature_restrict_{$post_type}", $post_features_option, false);
                }
            }

           //Admin Features
           $disabled_admin_items = !empty(get_option('capsman_disabled_admin_features')) ? (array)get_option('capsman_disabled_admin_features') : [];
           if (is_array($disabled_admin_items) && array_key_exists($copied_role, $disabled_admin_items)) {
               $disabled_admin_items[$role_slug] = $disabled_admin_items[$copied_role];
               update_option('capsman_disabled_admin_features', $disabled_admin_items, false);
           }

           /**
             * Allow other plugins to perform action after role is copied.
             *
             * @param string   $role_slug New role slug.
             * @param string   $copied_role  Original role name that was copied.
             *
             * @since 2.4.0
             */
            do_action('pp_capabilities_after_role_copied', $role_slug, $copied_role);
        }

        /**
         * Notify user and redirect
         */
        $out = sprintf(esc_html__('The new role %s was created successfully.', 'capsman-enhanced'),  sanitize_text_field($_REQUEST['role_name']));
            
        $redirect_url = esc_url_raw(
            add_query_arg( 
                [
                    'page' => 'pp-capabilities-roles', 
                    'add' => 'new_item', 
                    'role_action' => 'edit', 
                    'active_tab' =>  !empty($_REQUEST['active_tab']) ? sanitize_key($_REQUEST['active_tab']) : 'general',
                    'role' => esc_attr($role['name'])
                 ],
                admin_url('admin.php')
            )
        );
        
        $this->notify($out, 'success', true, $redirect_url);
    }

    /**
     * Handles edit role action
     */
    public function edit_role()
    {
        global $wp_roles;
        
        /**
         * Check capabilities
         */
        $this->check_permissions();

        /**
         * Check nonce
         */
        if (!isset($_REQUEST['_wpnonce']) || !wp_verify_nonce(sanitize_key($_REQUEST['_wpnonce']), 'edit-role')) {
            $this->notify(esc_html__('Your link has expired, refresh the page and try again.', 'capsman-enhanced'));
        }

        if (empty($_REQUEST['current_role']) || empty($_REQUEST['role_name'])) {
            $this->notify(esc_html__('Missing parameters, refresh the page and try again.', 'capsman-enhanced'));
        }

        /**
         * check if it's delete action and refer
         */
        if (!empty($_REQUEST['delete_role'])) {
            $this->delete_role(sanitize_key($_REQUEST['current_role']), ['nonce_check' => 'edit-role']);
            return;
        }

        /**
         * Update role
         */
        $current = get_role(sanitize_key($_REQUEST['current_role']));
		$new_title = sanitize_text_field($_REQUEST['role_name']);

        $old_title = $wp_roles->roles[$current->name]['name'];
		$wp_roles->roles[$current->name]['name'] = $new_title;

        if ($current && isset($wp_roles->roles[$current->name]) && $new_title) {
            $old_title = $wp_roles->roles[$current->name]['name'];
            $wp_roles->roles[$current->name]['name'] = $new_title;
            update_option($wp_roles->role_key, $wp_roles->roles);
        }

        $new_caps = pp_roles_remove_capabilities_role_level($current->capabilities);

        if (isset($_REQUEST['role_level'])) {
            $add_caps = array_merge($new_caps, ak_level2caps(absint($_REQUEST['role_level'])));
        }else{
            $add_caps =  $new_caps;
        }
        $del_caps = array_diff_key($current->capabilities, $new_caps);


		// Remove capabilities from role
		foreach ( $del_caps as $cap => $grant) {
			if ( current_user_can('administrator') || current_user_can($cap) )
				$current->remove_cap($cap);
		}

        //add new capabilities to the role
        foreach ( $add_caps as $cap => $grant ) {
			if ( current_user_can('administrator') || current_user_can($cap) )
				$current->add_cap( $cap, $grant );
		}

        //update role options
        $role_option    = [];
        $role_option['role_editor']         = (!empty($_REQUEST['role_editor']) && is_array(($_REQUEST['role_editor']))) ? array_map('sanitize_text_field', $_REQUEST['role_editor']) : [];
        $role_option['login_redirect']      = !empty($_REQUEST['login_redirect']) ? home_url(str_replace(home_url(), '', sanitize_text_field($_REQUEST['login_redirect']))) : '';
        $role_option['logout_redirect']     = !empty($_REQUEST['logout_redirect']) ? home_url(str_replace(home_url(), '', sanitize_text_field($_REQUEST['logout_redirect']))) : '';
        $role_option['referer_redirect']    = !empty($_REQUEST['referer_redirect']) ? (int) $_REQUEST['referer_redirect'] : 0;
        $role_option['custom_redirect']     = !empty($_REQUEST['custom_redirect']) ? (int) $_REQUEST['custom_redirect'] : 0;
        $role_option['disable_code_editor'] = !empty($_REQUEST['disable_code_editor']) ? (int) $_REQUEST['disable_code_editor'] : 0;
        $role_option['disable_role_user_login'] = !empty($_REQUEST['disable_role_user_login']) ? (int) $_REQUEST['disable_role_user_login'] : 0;
        if (defined('WC_PLUGIN_FILE')) {
            $role_option['disable_woocommerce_admin_restrictions'] = !empty($_REQUEST['disable_woocommerce_admin_restrictions']) ? (int) $_REQUEST['disable_woocommerce_admin_restrictions'] : 0;
        }
        update_option('pp_capabilities_' . sanitize_key($_REQUEST['current_role']) . '_role_option', $role_option);

        /**
         * Notify user and redirect
         */
        $out = sprintf( __('%s role updated successfully.', 'capsman-enhanced'),  $new_title);
            
        $redirect_url = esc_url_raw(
            add_query_arg( 
                [
                    'page' => 'pp-capabilities-roles', 
                    'add' => 'new_item', 
                    'role_action' => 'edit', 
                    'active_tab' =>  !empty($_REQUEST['active_tab']) ? sanitize_key($_REQUEST['active_tab']) : 'general', 
                    'role' => esc_attr(sanitize_key($_REQUEST['current_role']))
                 ],
                admin_url('admin.php')
            )
        );
        
        $this->notify($out, 'success', true, $redirect_url);
    }

    /**
     * Delete role action
     */
    public function delete_role($role = '', $args = [])
    {
        $defaults = ['allow_system_role_deletion' => false, 'nonce_check' => 'bulk-roles'];
        $args = array_merge($defaults, $args);
        foreach (array_keys($defaults) as $var) {
            $$var = $args[$var];
        }

        if (empty($role)) {
            $role = (isset($_REQUEST['role'])) ? array_map('sanitize_key', (array) ($_REQUEST['role'])) : '';
        }

        /**
         * Check capabilities
         */
        $this->check_permissions();

        /**
         * Check nonce
         */
        if (!isset($_REQUEST['_wpnonce']) || !wp_verify_nonce(sanitize_key($_REQUEST['_wpnonce']), $nonce_check)) {
            $this->notify(esc_html__('Your link has expired, refresh the page and try again.', 'capsman-enhanced'));
        }

        /**
         * Validate input data
         */
        $roles = [];
        if ($role) {
            if (is_string($role)) {
                $input = sanitize_key($role);
                $roles[] = $input;
            } else if (is_array($role)) {
                foreach ($role as $key => $id) {
                    $roles[] = sanitize_key($id);
                }
            }
        } else {
            return;
        }

        /**
         * If no roles provided return
         */
        if (empty($roles)) {
            $this->notify(esc_html__('Missing parameters, refresh the page and try again.', 'capsman-enhanced'));
        }

        $default = get_option('default_role');
        
		if ( $default == $role ) {
            $this->notify(
                sprintf(
                    esc_html__('Cannot delete default role. You <a href="%s">have to change it first</a>.', 'capsman-enhanced'), 
                    'options-general.php'
                )
            );
			return;
		}

        /**
         * Check if is a system role
         */
        if (!$allow_system_role_deletion) {
            foreach ($roles as $key => $role) {
	
                if ($this->manager->is_system_role($role)) {
                    unset($roles[$key]);
                }
            }

            if (empty($roles)) {
                $this->notify(esc_html__('Deleting a system role is not allowed.', 'capsman-enhanced'));
            }
        }

        /**
         * Delete roles
         */
        $deleted = 0;
        $user_count = 0;

        foreach ($roles as $role) {
            if (pp_capabilities_is_editable_role($role)) {
                $moved_users = $this->manager->delete_role($role);
                if (false !== $moved_users) {
                    $deleted++;
                    $user_count = $user_count + $moved_users;
                    //delete role option
                    delete_option("pp_capabilities_{$role}_role_option");
                }
            }
        }

        if ($deleted) {
            $default_name = (wp_roles()->is_role($default)) ? wp_roles()->role_names[$default] : $default;
            $users_message = ($user_count) ? sprintf(esc_html__('%1$d users moved to default role %2$s.', 'capsman-enhanced'), (int) $user_count, esc_html($default_name)) : '';
            
            $role_name = (wp_roles()->is_role($roles[0])) ? wp_roles()->role_names[$roles[0]] : $roles[0];

            $single = sprintf(
                esc_html__('The role %1$s was successfully deleted. %2$s', 'capsman-enhanced'), 
                esc_html($roles[0]),
                $users_message
            );
            
            $plural = sprintf(
                esc_html__('The selected %1$s roles were successfully deleted. %2$s', 'capsman-enhanced'), 
                $deleted,
                $users_message
            );
            
            $out = _n($single, $plural, $deleted, 'capsman-enhanced');

            if ($this->is_ajax()) {
                wp_send_json_success($out);
            } else {
                $redirect_url = esc_url_raw(
                    add_query_arg( 
                        [
                            'page' => 'pp-capabilities-roles'
                         ],
                        admin_url('admin.php')
                    )
                );
                
                $this->notify($out, 'success', true, $redirect_url);
            }
        } else {
            $this->notify(esc_html__('The role could not be deleted.', 'capsman-enhanced'));
        }
    }

    /**
     * Hide role action
     */
    public function hide_role($role = '', $args = [])
    {
        if (!defined('PRESSPERMIT_ACTIVE')) {
            return;
        }

        if (empty($role)) {
            $role = (isset($_REQUEST['role'])) ? sanitize_key($_REQUEST['role']) : '';
        }

        /**
         * Check capabilities
         */
        $this->check_permissions();

        /**
         * Validate input data
         */
        $roles = [];
        if ($role) {
            if (is_string($role)) {
                $input = sanitize_key($role);
                $roles[] = $input;
            } else if (is_array($role)) {
                foreach ($role as $key => $id) {
                    $roles[] = sanitize_key($id);
                }
            }
        } else {
            return;
        }

        /**
         * If no roles provided return
         */
        if (empty($roles)) {
            $out = __('Missing parameters, refresh the page and try again.', 'capsman-enhanced');
            $this->notify($out);
        }

        $pp_only = (array) pp_capabilities_get_permissions_option( 'supplemental_role_defs' );
        $pp_only = array_merge($pp_only, (array) $roles);
        pp_capabilities_update_permissions_option('supplemental_role_defs', $pp_only);

        $role_name = (wp_roles()->is_role($roles[0])) ? wp_roles()->role_names[$roles[0]] : $roles[0];

        $out = sprintf(
            __('The role %1$s was successfully hidden.', 'capsman-enhanced'), 
            $roles[0]
        );
        
        if ($this->is_ajax()) {
            wp_send_json_success($out);
        } else {
            $this->notify($out, 'success');
        }
    }

    /**
     * Unhide role action
     */
    public function unhide_role($role = '', $args = [])
    {
        if (!defined('PRESSPERMIT_ACTIVE')) {
            return;
        }

        if (empty($role)) {
            $role = (isset($_REQUEST['role'])) ? sanitize_key($_REQUEST['role']) : '';
        }

        /**
         * Check capabilities
         */
        $this->check_permissions();

        /**
         * Validate input data
         */
        $roles = [];
        if ($role) {
            if (is_string($role)) {
                $input = sanitize_key($role);
                $roles[] = $input;
            } else if (is_array($role)) {
                foreach ($role as $key => $id) {
                    $roles[] = sanitize_key($id);
                }
            }
        } else {
            return;
        }

        /**
         * If no roles provided return
         */
        if (empty($roles)) {
            $this->notify(esc_html__('Missing parameters, refresh the page and try again.', 'capsman-enhanced'));
        }

        $pp_only = (array) pp_capabilities_get_permissions_option('supplemental_role_defs');
        $pp_only = array_diff($pp_only, (array) $roles);
        pp_capabilities_update_permissions_option('supplemental_role_defs', $pp_only);

        $role_name = (wp_roles()->is_role($roles[0])) ? wp_roles()->role_names[$roles[0]] : $roles[0];

        $out = sprintf(
            __('The role %1$s was successfully unhidden.', 'capsman-enhanced'), 
            $roles[0]
        );
        
        if ($this->is_ajax()) {
            wp_send_json_success($out);
        } else {
            $this->notify($out, 'success');
        }
    }
}
