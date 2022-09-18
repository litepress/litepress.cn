<?php

/*
 * PublishPress Capabilities [Free]
 * 
 * Admin execution controller: menu registration and other filters and actions that need to be loaded for every wp-admin URL
 * 
 * This module should not include full functions related to our own plugin screens.  
 * Instead, use these filter and action handlers to load other classes when needed.
 * 
 */
class PP_Capabilities_Admin_UI {
    function __construct() {
        global $pagenow;

        /**
         * The class responsible for handling notifications
         */
        require_once (dirname(CME_FILE) . '/classes/pp-capabilities-notices.php');

        add_action('init', [$this, 'featureRestrictionsGutenberg'], PHP_INT_MAX - 1);

        if (is_admin()) {
            add_action('admin_init', [$this, 'featureRestrictionsClassic'], PHP_INT_MAX - 1);
        }

        add_action('admin_enqueue_scripts', [$this, 'adminScripts'], 100);
        add_action('admin_print_scripts', [$this, 'adminPrintScripts']);

        add_action('profile_update', [$this, 'action_profile_update'], 10, 2);

        if (is_multisite()) {
            add_action('add_user_to_blog', [$this, 'action_profile_update'], 9);
        } else {
            add_action('user_register', [$this, 'action_profile_update'], 9);
        }

        if (is_admin() && (isset($_REQUEST['page']) && (in_array($_REQUEST['page'], ['pp-capabilities', 'pp-capabilities-backup', 'pp-capabilities-roles', 'pp-capabilities-admin-menus', 'pp-capabilities-editor-features', 'pp-capabilities-nav-menus', 'pp-capabilities-settings', 'pp-capabilities-admin-features']))

        || (!empty($_REQUEST['action']) && in_array($_REQUEST['action'], ['pp-roles-add-role', 'pp-roles-delete-role', 'pp-roles-hide-role', 'pp-roles-unhide-role']))
        || ( ! empty($_SERVER['SCRIPT_NAME']) && strpos(sanitize_text_field($_SERVER['SCRIPT_NAME']), 'p-admin/plugins.php' ) && ! empty($_REQUEST['action'] ) ) 
        || ( isset($_GET['action']) && ('reset-defaults' == $_GET['action']) && isset($_REQUEST['_wpnonce']) && wp_verify_nonce(sanitize_key($_REQUEST['_wpnonce']), 'capsman-reset-defaults') )
        || in_array( $pagenow, array( 'users.php', 'user-edit.php', 'profile.php', 'user-new.php' ) )
        ) ) {
            global $capsman;
            
            // Run the plugin
            require_once ( dirname(CME_FILE) . '/framework/lib/formating.php' );
            require_once ( dirname(CME_FILE) . '/framework/lib/users.php' );
            
            require_once ( dirname(CME_FILE) . '/includes/manager.php' );
            $capsman = new CapabilityManager();
        } else {
            add_action( 'admin_menu', [$this, 'cmeSubmenus'], 20 );
        }

        add_action('init', function() { // late execution avoids clash with autoloaders in other plugins
            global $pagenow;

            if ((($pagenow == 'admin.php') && isset($_GET['page']) && in_array($_GET['page'], ['pp-capabilities', 'pp-capabilities-roles', 'pp-capabilities-backup'])) // @todo: CSS for button alignment in Editor Features, Admin Features
            || (defined('DOING_AJAX') && DOING_AJAX && !empty($_REQUEST['action']) && (false !== strpos(sanitize_key($_REQUEST['action']), 'capability-manager-enhanced')))
            ) {
                if (!class_exists('\PublishPress\WordPressReviews\ReviewsController')) {
                    include_once PUBLISHPRESS_CAPS_ABSPATH . '/vendor/publishpress/wordpress-reviews/ReviewsController.php';
                }
    
                if (class_exists('\PublishPress\WordPressReviews\ReviewsController')) {
                    $reviews = new \PublishPress\WordPressReviews\ReviewsController(
                        'capability-manager-enhanced',
                        'PublishPress Capabilities',
                        plugin_dir_url(CME_FILE) . 'common/img/capabilities-wp-logo.png'
                    );
        
                    add_filter('publishpress_wp_reviews_display_banner_capability-manager-enhanced', [$this, 'shouldDisplayBanner']);
        
                    $reviews->init();
                }
            }
        });


        add_filter('pp_capabilities_feature_post_types', [$this, 'fltEditorFeaturesPostTypes'], 5);
        add_filter('block_editor_settings_all', [$this, 'filterCodeEditingStatus'], 999);
        add_filter('classic_editor_enabled_editors_for_post_type', [$this, 'filterRolePostTypeEditor'], 10, 2);
        add_filter('classic_editor_plugin_settings', [$this, 'filterRoleEditorSettings']);

        //capabilities settings
        add_action('pp-capabilities-settings-ui', [$this, 'settingsUI']);
    }

    /**
     * Filters the editors that are enabled for the post type.
     *
     * @param array $editors    Associative array of the editors and whether they are enabled for the post type.
     * @param string $post_type The post type.
     */
    public function filterRolePostTypeEditor($editors, $post_type) {
      $user = wp_get_current_user();

      if (is_object($user) && isset($user->roles)) {
          $current_user_editors = [];
          foreach ($user->roles as $user_role) {
              //get role option
              $role_option = get_option("pp_capabilities_{$user_role}_role_option", []);
              if (is_array($role_option) && !empty($role_option) && !empty($role_option['role_editor'])) {
                  $current_user_editors = array_merge($current_user_editors, $role_option['role_editor']);
              }
          }

          if (!empty($current_user_editors)) {
              $current_user_editors = array_unique($current_user_editors);
              $editors = array(
                  'classic_editor' => in_array('classic_editor', $current_user_editors) ? true : false,
                  'block_editor'   => in_array('block_editor', $current_user_editors) ? true : false,
              );
          }
      }

      return $editors;
  }

  /**
   * Override the classic editor plugin's settings.
   *
   * @param bool $settings
   * @return mixed
   */
  public function filterRoleEditorSettings($settings) {
      $user = wp_get_current_user();

      if (is_object($user) && isset($user->roles)) {
          $current_user_editors = [];
          foreach ($user->roles as $user_role) {
              //get role option
              $role_option = get_option("pp_capabilities_{$user_role}_role_option", []);
              if (is_array($role_option) && !empty($role_option) && !empty($role_option['role_editor'])) {
                  $current_user_editors = array_merge($current_user_editors, $role_option['role_editor']);
              }
          }

          if (!empty($current_user_editors)) {
              $current_user_editors = array_unique($current_user_editors);
              $settings = [];
              $settings['editor'] = ($current_user_editors[0] === 'classic_editor') ? 'classic' : 'block';
              $settings['allow-users'] = count($current_user_editors) > 1 ? true : false;
          }
      }

      return $settings;
  }

    public function filterCodeEditingStatus($settings) {
        $user = wp_get_current_user();

        if (is_object($user) && isset($user->roles)) {
            foreach ($user->roles as $user_role) {
                //get role option
                $role_option = get_option("pp_capabilities_{$user_role}_role_option", []);
                if (is_array($role_option) && !empty($role_option) && !empty($role_option['disable_code_editor']) && (int)$role_option['disable_code_editor'] > 0) {
                    $settings['codeEditingEnabled'] = false;
                    break;
                }
            }
        }

        return $settings;
    }

    public function fltEditorFeaturesPostTypes($def_post_types) {
        if((int)get_option('cme_editor_features_private_post_type') > 0 || defined('PP_CAPABILITIES_PRIVATE_TYPES')){
            $private_cpt = get_post_types(['public' => true, 'show_ui' => true], 'names', 'or');
            $public_cpt  = get_post_types(['public' => true, 'show_ui' => true], 'names', 'or');
            $def_post_types =  array_unique(array_merge($def_post_types, $private_cpt, $public_cpt));
        }else{
            $def_post_types = array_merge($def_post_types, get_post_types(['public' => true], 'names'));
        }

        unset($def_post_types['attachment']);

        if ((count($def_post_types) > 14) && !defined('PP_CAPABILITIES_UNLIMITED_FEATURE_TYPES')) {
            $custom_types = array_diff($def_post_types, ['post', 'page']);
            $def_post_types = array_merge(['post', 'page'], array_slice($custom_types, 0, 12));
        }

        return $def_post_types;
    }

    public function shouldDisplayBanner() {
        global $pagenow;

        return ($pagenow == 'admin.php') && isset($_GET['page']) && in_array($_GET['page'], ['pp-capabilities', 'pp-capabilities-roles', 'pp-capabilities-backup']);
    }

    private function applyFeatureRestrictions($editor = 'gutenberg') {
        global $pagenow;

        if (is_multisite() && is_super_admin() && !defined('PP_CAPABILITIES_RESTRICT_SUPER_ADMIN')) {
            return;
        }

        // Return if not a post editor request
        if (!in_array($pagenow, ['post.php', 'post-new.php'])) {
            return;
        }
    
        static $def_post_types; // avoid redundant filter application

        if (!isset($def_post_types)) {
            $def_post_types = array_unique(apply_filters('pp_capabilities_feature_post_types', ['post', 'page']));
        }

        $post_type = pp_capabilities_get_post_type();

        // Return if not a supported post type
        if (in_array($post_type, apply_filters('pp_capabilities_unsupported_post_types', ['attachment']))) {
            return;
        }

        switch ($editor) {
            case 'gutenberg':
                if (_pp_capabilities_is_block_editor_active()) {
                    require_once ( dirname(CME_FILE) . '/includes/features/restrict-editor-features.php' );
                    PP_Capabilities_Post_Features::applyRestrictions($post_type);
                }
                
                break;

            case 'classic':
                if (!_pp_capabilities_is_block_editor_active()) {
                    require_once ( dirname(CME_FILE) . '/includes/features/restrict-editor-features.php' );
                    PP_Capabilities_Post_Features::adminInitClassic($post_type);
                }
        }
    }

    function featureRestrictionsGutenberg() {
        $this->applyFeatureRestrictions();
    }

    function featureRestrictionsClassic() {
        $this->applyFeatureRestrictions('classic');
    }

    function adminScripts() {
        global $publishpress;

        if (function_exists('get_current_screen') && (!defined('PUBLISHPRESS_VERSION') || empty($publishpress) || empty($publishpress->modules) || empty($publishpress->modules->roles))) {
            $screen = get_current_screen();

            if ('user-edit' === $screen->base || ('user' === $screen->base && 'add' === $screen->action && (defined('PP_CAPABILITIES_ADD_USER_MULTI_ROLES') || get_option('cme_capabilities_add_user_multi_roles')))) {
                // Check if we are on the user's profile page
                wp_enqueue_script(
                    'pp-capabilities-chosen-js',
                    plugin_dir_url(CME_FILE) . 'common/libs/chosen-v1.8.7/chosen.jquery.js',
                    ['jquery'],
                    CAPSMAN_VERSION
                );

                wp_enqueue_script(
                    'pp-capabilities-jquery-ui',
                    plugin_dir_url(CME_FILE) . 'common/libs/jquery/jquery-ui.js',
                    ['jquery'],
                    CAPSMAN_VERSION
                );

                wp_enqueue_script(
                    'pp-capabilities-roles-profile-js',
                    plugin_dir_url(CME_FILE) . 'common/js/profile.js',
                    ['jquery', 'pp-capabilities-chosen-js'],
                    CAPSMAN_VERSION
                );

                wp_enqueue_style(
                    'pp-capabilities-chosen-css',
                    plugin_dir_url(CME_FILE) . 'common/libs/chosen-v1.8.7/chosen.css',
                    false,
                    CAPSMAN_VERSION
                );
                wp_enqueue_style(
                    'pp-capabilities-roles-profile-css',
                    plugin_dir_url(CME_FILE) . 'common/css/profile.css',
                    ['pp-capabilities-chosen-css'],
                    CAPSMAN_VERSION
                );

                $roles = !empty($_GET['user_id']) ? $this->getUsersRoles((int) $_GET['user_id']) : [];

                if (empty($roles)) {
                    $roles = (array) get_option('default_role');
                }

                wp_localize_script(
                    'pp-capabilities-roles-profile-js',
                    'ppCapabilitiesProfileData',
                    [
                        'role_description' => esc_html__('Drag multiple roles selection to change order.', 'capsman-enhanced'),
                        'selected_roles'   => $roles
                    ]
                );
            }
        }
    }

    function adminPrintScripts() {


        /**
         * Update capabilities top level slug from roles to capabilities
         */
        $menu_inline_script = "
            jQuery(document).ready( function($) {
                if (jQuery('li#toplevel_page_pp-capabilities-roles a.toplevel_page_pp-capabilities-roles').length > 0) {
                    var toplevel_page = jQuery('li#toplevel_page_pp-capabilities-roles a.toplevel_page_pp-capabilities-roles');
                    var toplevel_page_link = toplevel_page.attr('href');
                    if (toplevel_page_link) {
                        toplevel_page.attr('href', toplevel_page_link.replace('pp-capabilities-roles', 'pp-capabilities'));
                    }
                }
            });";
        ppc_add_inline_script($menu_inline_script);

        // Counteract overzealous menu icon styling in PublishPress <= 3.2.0 :)
        if (defined('PUBLISHPRESS_VERSION') && version_compare(constant('PUBLISHPRESS_VERSION'), '3.2.0', '<=') && defined('PP_CAPABILITIES_FIX_ADMIN_ICON')):?>
        <style type="text/css">
        #toplevel_page_pp-capabilities-roles .dashicons-before::before, #toplevel_page_pp-capabilities-roles .wp-has-current-submenu .dashicons-before::before {
            background-image: inherit !important;
            content: "\f112" !important;
        }
        </style>
        <?php endif;
    }

    /**
     * Returns a list of roles with name and display name to populate a select field.
     *
     * @param int $userId
     *
     * @return array
     */
    protected function getUsersRoles($userId)
    {
        if (empty($userId)) {
            return [];
        }

        $user = get_user_by('id', $userId);

        if (empty($user)) {
            return [];
        }

        return $user->roles;
    }

    public function action_profile_update($userId, $oldUserData = [])
    {
        // Check if we need to update the user's roles, allowing to set multiple roles.
        if ((!empty($_REQUEST['_wpnonce']) && wp_verify_nonce(sanitize_key($_REQUEST['_wpnonce']), 'update-user_' . $userId) 
            || !empty($_REQUEST['_wpnonce_create-user']) && wp_verify_nonce(sanitize_key($_REQUEST['_wpnonce_create-user']), 'create-user'))
            && isset($_POST['pp_roles']) && current_user_can('promote_users')) {
            // Remove the user's roles
            $user = get_user_by('ID', $userId);

            $newRoles     = array_map('sanitize_key', $_POST['pp_roles']);
            $currentRoles = $user->roles;

            if (empty($newRoles) || !is_array($newRoles)) {
                return;
            }

            // Remove all roles
            foreach ($currentRoles as $role) {
                // Check if it is a bbPress rule. If so, don't remove it.
                $isBBPressRole = preg_match('/^bbp_/', $role);

                if (!$isBBPressRole) {
                    $user->remove_role($role);
                }
            }

            // Add new roles in order
            foreach ($newRoles as $role) {
                $user->add_role($role);
            }
        }
    }


    // perf enhancement: display submenu links without loading framework and plugin code
    function cmeSubmenus() {
        // First we check if user is administrator and can 'manage_capabilities'.
        if (current_user_can('administrator') && ! current_user_can('manage_capabilities')) {
            if ($admin = get_role('administrator')) {
                $admin->add_cap('manage_capabilities');
            }
        }

        $cap_name = (is_multisite() && is_super_admin()) ? 'read' : 'manage_capabilities';

        $permissions_title = __('Capabilities', 'capsman-enhanced');

        $menu_order = 72;

        if (defined('PUBLISHPRESS_PERMISSIONS_MENU_GROUPING')) {
            foreach ((array)get_option('active_plugins') as $plugin_file) {
                if ( false !== strpos($plugin_file, 'publishpress.php') ) {
                    $menu_order = 27;
                }
            }
        }

        add_menu_page(
            $permissions_title,
            $permissions_title,
            $cap_name,
            'pp-capabilities-roles',
            'cme_fakefunc',
            'dashicons-admin-network',
            $menu_order
        );

        add_submenu_page('pp-capabilities-roles',  __('Roles', 'capsman-enhanced'), __('Roles', 'capsman-enhanced'), $cap_name, 'pp-capabilities-roles', 'cme_fakefunc');
		add_submenu_page('pp-capabilities-roles',  $permissions_title, $permissions_title, $cap_name, 'pp-capabilities', 'cme_fakefunc');
        add_submenu_page('pp-capabilities-roles',  __('Editor Features', 'capsman-enhanced'), __('Editor Features', 'capsman-enhanced'), $cap_name, 'pp-capabilities-editor-features', 'cme_fakefunc');
        add_submenu_page('pp-capabilities-roles',  __('Admin Features', 'capsman-enhanced'), __('Admin Features', 'capsman-enhanced'), $cap_name, 'pp-capabilities-admin-features', 'cme_fakefunc');
        add_submenu_page('pp-capabilities-roles',  __('Admin Menus', 'capsman-enhanced'), __('Admin Menus', 'capsman-enhanced'), $cap_name, 'pp-capabilities-admin-menus', 'cme_fakefunc');
        add_submenu_page('pp-capabilities-roles',  __('Nav Menus', 'capsman-enhanced'), __('Nav Menus', 'capsman-enhanced'), $cap_name, 'pp-capabilities-nav-menus', 'cme_fakefunc');
        add_submenu_page('pp-capabilities-roles',  __('Backup', 'capsman-enhanced'), __('Backup', 'capsman-enhanced'), $cap_name, 'pp-capabilities-backup', 'cme_fakefunc');
        add_submenu_page('pp-capabilities-roles',  __('Settings', 'capsman-enhanced'), __('Settings', 'capsman-enhanced'), $cap_name, 'pp-capabilities-settings', 'cme_fakefunc');

        if (!defined('PUBLISHPRESS_CAPS_PRO_VERSION')) {
            add_submenu_page(
                'pp-capabilities-roles',
                __('Upgrade to Pro', 'capsman-enhanced'),
                __('Upgrade to Pro', 'capsman-enhanced'),
                'manage_capabilities',
                'capsman-enhanced',
                'cme_fakefunc'
            );
        }
    }


    public function settingsUI() {
        require_once(dirname(__FILE__).'/settings-ui.php');
        new Capabilities_Settings_UI();
    }
}
