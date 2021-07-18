<?php

class PP_Capabilities_Admin_UI {
    function __construct() {
        global $pagenow;

        /**
         * The class responsible for handling notifications
         */
        require_once (dirname(CME_FILE) . '/classes/pp-capabilities-notices.php');

        add_action('init', [$this, 'featureRestrictionsGutenberg']);

        if (is_admin()) {
            add_action('admin_init', [$this, 'featureRestrictionsClassic']);
        }

        add_action('admin_enqueue_scripts', [$this, 'adminScripts'], 100);
        add_action('admin_print_scripts', [$this, 'adminPrintScripts']);

        add_action('profile_update', [$this, 'action_profile_update'], 10, 2);

        if (is_multisite()) {
            add_action('add_user_to_blog', [$this, 'action_profile_update'], 9);
        } else {
            add_action('user_register', [$this, 'action_profile_update'], 9);
        }

        if (is_admin() && (isset($_REQUEST['page']) && (in_array($_REQUEST['page'], ['pp-capabilities', 'pp-capabilities-backup', 'pp-capabilities-roles', 'pp-capabilities-admin-menus', 'pp-capabilities-editor-features', 'pp-capabilities-nav-menus', 'pp-capabilities-settings']))
        || (!empty($_REQUEST['action']) && in_array($_REQUEST['action'], ['pp-roles-add-role', 'pp-roles-delete-role', 'pp-roles-hide-role', 'pp-roles-unhide-role']))
        || ( ! empty($_SERVER['SCRIPT_NAME']) && strpos( $_SERVER['SCRIPT_NAME'], 'p-admin/plugins.php' ) && ! empty($_REQUEST['action'] ) ) 
        || ( isset($_GET['action']) && 'reset-defaults' == $_GET['action'] )
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
    }

    private function applyFeatureRestrictions($editor = 'gutenberg') {
        global $pagenow;

        // Return if not a post editor request
        if (!in_array($pagenow, ['post.php', 'post-new.php'])) {
            return;
        }
    
        static $def_post_types; // avoid redundant filter application

        if (!isset($def_post_types)) {
            //$def_post_types = apply_filters('pp_capabilities_feature_post_types', get_post_types(['public' => true]));
            $def_post_types = apply_filters('pp_capabilities_feature_post_types', ['post', 'page']);
        }

        $post_type = pp_capabilities_get_post_type();

        // Return if not a supported post type
        if (!in_array($post_type, $def_post_types)) {
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

            if ('user-edit' === $screen->base || ('user' === $screen->base && 'add' === $screen->action && defined('PP_CAPABILITIES_ADD_USER_MULTI_ROLES'))) {
                // Check if we are on the user's profile page
                wp_enqueue_script(
                    'pp-capabilities-chosen-js',
                    plugin_dir_url(CME_FILE) . 'common/libs/chosen-v1.8.3/chosen.jquery.js',
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
                    plugin_dir_url(CME_FILE) . 'common/libs/chosen-v1.8.3/chosen.css',
                    false,
                    CAPSMAN_VERSION
                );
                wp_enqueue_style(
                    'pp-capabilities-roles-profile-css',
                    plugin_dir_url(CME_FILE) . 'common/css/profile.css',
                    ['pp-capabilities-chosen-css'],
                    CAPSMAN_VERSION
                );

                $roles = !empty($_GET['user_id']) ?$this->getUsersRoles($_GET['user_id']) : [];

                if (empty($roles)) {
                    $roles = (array) get_option('default_role');
                }

                wp_localize_script(
                    'pp-capabilities-roles-profile-js',
                    'ppCapabilitiesProfileData',
                    [
                        'selected_roles' => $roles
                    ]
                );
            }
        }
    }

    function adminPrintScripts() {
        // Counteract overzealous menu icon styling in PublishPress <= 3.2.0 :)
        if (defined('PUBLISHPRESS_VERSION') && version_compare(constant('PUBLISHPRESS_VERSION'), '3.2.0', '<=') && defined('PP_CAPABILITIES_FIX_ADMIN_ICON')):?>
        <style type="text/css">
        #toplevel_page_pp-capabilities .dashicons-before::before, #toplevel_page_pp-capabilities .wp-has-current-submenu .dashicons-before::before {
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
        if (isset($_POST['pp_roles']) && current_user_can('promote_users')) {
            // Remove the user's roles
            $user = get_user_by('ID', $userId);

            $newRoles     = $_POST['pp_roles'];
            $currentRoles = $user->roles;

            if (empty($newRoles) || !is_array($newRoles)) {
                return;
            }

            // Remove unselected roles
            foreach ($currentRoles as $role) {
                // Check if it is a bbPress rule. If so, don't remove it.
                $isBBPressRole = preg_match('/^bbp_/', $role);

                if (!in_array($role, $newRoles) && !$isBBPressRole) {
                    $user->remove_role($role);
                }
            }

            // Add new roles
            foreach ($newRoles as $role) {
                if (!in_array($role, $currentRoles)) {
                    $user->add_role($role);
                }
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
            'pp-capabilities',
            'cme_fakefunc',
            'dashicons-admin-network',
            $menu_order
        );

        add_submenu_page('pp-capabilities',  __('Roles', 'capsman-enhanced'), __('Roles', 'capsman-enhanced'), $cap_name, 'pp-capabilities-roles', 'cme_fakefunc');
        add_submenu_page('pp-capabilities',  __('Editor Features', 'capsman-enhanced'), __('Editor Features', 'capsman-enhanced'), $cap_name, 'pp-capabilities-editor-features', 'cme_fakefunc');
        add_submenu_page('pp-capabilities',  __('Admin Menus', 'capsman-enhanced'), __('Admin Menus', 'capsman-enhanced'), $cap_name, 'pp-capabilities-admin-menus', 'cme_fakefunc');
        add_submenu_page('pp-capabilities',  __('Nav Menus', 'capsman-enhanced'), __('Nav Menus', 'capsman-enhanced'), $cap_name, 'pp-capabilities-nav-menus', 'cme_fakefunc');
        add_submenu_page('pp-capabilities',  __('Backup', 'capsman-enhanced'), __('Backup', 'capsman-enhanced'), $cap_name, 'pp-capabilities-backup', 'cme_fakefunc');
        
        if (defined('PUBLISHPRESS_CAPS_PRO_VERSION')) {
        	add_submenu_page('pp-capabilities',  __('Settings', 'capsman-enhanced'), __('Settings', 'capsman-enhanced'), $cap_name, 'pp-capabilities-settings', 'cme_fakefunc');
        }

        if (!defined('PUBLISHPRESS_CAPS_PRO_VERSION')) {
            add_submenu_page(
                'pp-capabilities',
                __('Upgrade to Pro', 'capsman-enhanced'),
                __('Upgrade to Pro', 'capsman-enhanced'),
                'manage_capabilities',
                'capsman-enhanced',
                'cme_fakefunc'
            );
        }
    }

}

function cme_fakefunc() {
}

function pp_capabilities_get_post_id()
{
    global $post;

    if (defined('REST_REQUEST') && REST_REQUEST) {
        if ($_post_id = apply_filters('presspermit_rest_post_id', 0)) {
            return $_post_id;
        }
    }

    if (!empty($post) && is_object($post)) {
        if ('auto-draft' == $post->post_status) {
            return 0;
        } else {
            return $post->ID;
        }

    } elseif (isset($_REQUEST['post'])) {
        return (int)$_REQUEST['post'];

    } elseif (isset($_REQUEST['post_ID'])) {
        return (int)$_REQUEST['post_ID'];

    } elseif (isset($_REQUEST['post_id'])) {
        return (int)$_REQUEST['post_id'];

    } elseif (defined('WOOCOMMERCE_VERSION') && !empty($_REQUEST['product_id'])) {
        return (int)$_REQUEST['product_id'];
    }
}

/**
 * Based on Edit Flow's \Block_Editor_Compatible::should_apply_compat method.
 *
 * @return bool
 */
function _pp_capabilities_is_block_editor_active($post_type = '', $args = [])
{
    global $current_user, $wp_version;

    $defaults = ['suppress_filter' => false, 'force_refresh' => false];
    $args = array_merge($defaults, $args);
    $suppress_filter = $args['suppress_filter'];

    // Check if Revisionary lower than v1.3 is installed. It disables Gutenberg.
    if (defined('REVISIONARY_VERSION') && version_compare(REVISIONARY_VERSION, '1.3-beta', '<')) {
        return false;
    }

    static $buffer;
    if (!isset($buffer)) {
        $buffer = [];
    }

    if (!$post_type = pp_capabilities_get_post_type()) {
        return true;
    }

    if ($post_type_obj = get_post_type_object($post_type)) {
        if (!$post_type_obj->show_in_rest) {
            return false;
        }
    }

    if (isset($buffer[$post_type]) && empty($args['force_refresh']) && !$suppress_filter) {
        return $buffer[$post_type];
    }

    if (class_exists('Classic_Editor')) {
        if (isset($_REQUEST['classic-editor__forget']) && (isset($_REQUEST['classic']) || isset($_REQUEST['classic-editor']))) {
            return false;
        } elseif (isset($_REQUEST['classic-editor__forget']) && !isset($_REQUEST['classic']) && !isset($_REQUEST['classic-editor'])) {
            return true;
        } elseif (get_option('classic-editor-allow-users') === 'allow') {
            if ($post_id = pp_capabilities_get_post_id()) {
                $which = get_post_meta( $post_id, 'classic-editor-remember', true );

                if ('block-editor' == $which) {
                    return true;
                } elseif ('classic-editor' == $which) {
                    return false;
                }
            } else {
                $use_block = ('block' == get_user_meta($current_user->ID, 'wp_classic-editor-settings'));
                return $use_block && apply_filters('use_block_editor_for_post_type', $use_block, $post_type, PHP_INT_MAX);
            }
        }
    }

    $pluginsState = array(
        'classic-editor' => class_exists( 'Classic_Editor' ), // is_plugin_active('classic-editor/classic-editor.php'),
        'gutenberg'      => function_exists( 'the_gutenberg_project' ), //is_plugin_active('gutenberg/gutenberg.php'),
        'gutenberg-ramp' => class_exists('Gutenberg_Ramp'),
    );
    
    $conditions = [];

    if ($suppress_filter) remove_filter('use_block_editor_for_post_type', $suppress_filter, 10, 2);

    /**
     * 5.0:
     *
     * Classic editor either disabled or enabled (either via an option or with GET argument).
     * It's a hairy conditional :(
     */
    // phpcs:ignore WordPress.VIP.SuperGlobalInputUsage.AccessDetected, WordPress.Security.NonceVerification.NoNonceVerification
    $conditions[] = (version_compare($wp_version, '5.0', '>=') || $pluginsState['gutenberg'])
                    && ! $pluginsState['classic-editor']
                    && ! $pluginsState['gutenberg-ramp']
                    && apply_filters('use_block_editor_for_post_type', true, $post_type, PHP_INT_MAX);

    $conditions[] = version_compare($wp_version, '5.0', '>=')
                    && $pluginsState['classic-editor']
                    && (get_option('classic-editor-replace') === 'block'
                        && ! isset($_GET['classic-editor__forget']));

    $conditions[] = version_compare($wp_version, '5.0', '>=')
                    && $pluginsState['classic-editor']
                    && (get_option('classic-editor-replace') === 'classic'
                        && isset($_GET['classic-editor__forget']));

    $conditions[] = $pluginsState['gutenberg-ramp'] 
                    && apply_filters('use_block_editor_for_post', true, get_post(pp_capabilities_get_post_id()), PHP_INT_MAX);

    // Returns true if at least one condition is true.
    $result = count(
                array_filter($conditions,
                    function ($c) {
                        return (bool)$c;
                    }
                )
            ) > 0;
    
    if (!$suppress_filter) {
        $buffer[$post_type] = $result;
    }

    // Returns true if at least one condition is true.
    return $result;
}
