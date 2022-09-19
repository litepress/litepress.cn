<?php

if (!defined('ABSPATH')) {
    exit;
}

class GDATTAdmin {
    private $page_ids = array();
    private $admin_plugin = false;

    function __construct() {
        add_action('after_setup_theme', array($this, 'load'));
    }

    public static function instance() {
        static $instance = false;

        if ($instance === false) {
            $instance = new GDATTAdmin();
        }

        return $instance;
    }

    public function admin_init() {
        if (isset($_GET['page'])) {
            $this->admin_plugin = $_GET['page'] == 'gdbbpress_attachments';
        }

        if ($this->admin_plugin) {
            wp_enqueue_style('gd-bbpress-attachments', GDBBPRESSATTACHMENTS_URL."css/admin.css", array(), GDBBPRESSATTACHMENTS_VERSION);
        }
    }

    public function load() {
        add_action('admin_init', array($this, 'admin_init'));
        add_action('admin_menu', array($this, 'admin_menu'));

        add_filter('plugin_action_links', array($this, 'plugin_actions'), 10, 2);
        add_filter('plugin_row_meta', array($this, 'plugin_links'), 10, 2);
    }

    public function admin_menu() {
        $this->page_ids[] = add_submenu_page('edit.php?post_type=forum', 'GD bbPress Attachments', __("Attachments", "gd-bbpress-attachments"), GDBBPRESSATTACHMENTS_CAP, 'gdbbpress_attachments', array($this, 'menu_attachments'));

        $this->admin_load_hooks();
    }

    public function admin_load_hooks() {
        foreach ($this->page_ids as $id) {
            add_action('load-'.$id, array($this, 'load_admin_page'));
        }
    }

    public function plugin_actions($links, $file) {
        if ($file == 'gd-bbpress-attachments/gd-bbpress-attachments.php') {
            $settings_link = '<a href="edit.php?post_type=forum&page=gdbbpress_attachments">'.__("Settings", "gd-bbpress-attachments").'</a>';
            array_unshift($links, $settings_link);
        }

        return $links;
    }

    public function plugin_links($links, $file) {
        if ($file == 'gd-bbpress-attachments/gd-bbpress-attachments.php') {
            $links[] = '<a target="_blank" style="color: #cc0000; font-weight: bold;" href="https://plugins.dev4press.com/gd-bbpress-toolbox/">'.__("Upgrade to GD bbPress Toolbox Pro", "gd-bbpress-attachments").'</a>';
        }

        return $links;
    }

    public function load_admin_page() {
        $screen = get_current_screen();

        $screen->set_help_sidebar('
            <p><strong>Dev4Press:</strong></p>
            <p><a target="_blank" href="https://www.dev4press.com/">'.__("Website", "gd-bbpress-attachments").'</a></p>
            <p><a target="_blank" href="https://twitter.com/milangd">'.__("On Twitter", "gd-bbpress-attachments").'</a></p>
            <p><a target="_blank" href="https://facebook.com/dev4press">'.__("On Facebook", "gd-bbpress-attachments").'</a></p>');

        $screen->add_help_tab(array(
            "id" => "gdpt-screenhelp-help",
            "title" => __("Get Help", "gd-bbpress-attachments"),
            "content" => '<h5>'.__("General Plugin Information", "gd-bbpress-attachments").'</h5>
                <p><a href="https://plugins.dev4press.com/gd-bbpress-attachments/" target="_blank">'.__("Home Page on Dev4Press.com", "gd-bbpress-attachments").'</a> | 
                <a href="https://wordpress.org/plugins/gd-bbpress-attachments/" target="_blank">'.__("Home Page on WordPress.org", "gd-bbpress-attachments").'</a></p> 
                <h5>'.__("Getting Plugin Support", "gd-bbpress-attachments").'</h5>
                <p><a href="https://support.dev4press.com/forums/forum/plugins-free/gd-bbpress-attachments/" target="_blank">'.__("Support Forum on Dev4Press.com", "gd-bbpress-attachments").'</a> | 
                <a href="https://wordpress.org/support/plugin/gd-bbpress-attachments" target="_blank">'.__("Support Forum on WordPress.org", "gd-bbpress-attachments").'</a> </p>'));

        $screen->add_help_tab(array(
            "id" => "gdpt-screenhelp-website",
            "title" => "Dev4Press", "sfc",
            "content" => '<p>'.__("On Dev4Press website you can find many useful plugins, themes and tutorials, all for WordPress. Please, take a few minutes to browse some of these resources, you might find some of them very useful.", "gd-bbpress-attachments").'</p>
                <p><a href="https://plugins.dev4press.com/plugins/" target="_blank"><strong>'.__("Plugins", "gd-bbpress-attachments").'</strong></a> - '.__("We have more than 10 plugins available, some of them are commercial and some are available for free.", "gd-bbpress-attachments").'</p>
                <p><a href="https://support.dev4press.com/kb/" target="_blank"><strong>'.__("Knowledge Base", "gd-bbpress-attachments").'</strong></a> - '.__("Premium and free tutorials for our plugins themes, and many general and practical WordPress tutorials.", "gd-bbpress-attachments").'</p>
                <p><a href="https://support.dev4press.com/forums/" target="_blank"><strong>'.__("Support Forums", "gd-bbpress-attachments").'</strong></a> - '.__("Premium support forum for all with valid licenses to get help. Also, report bugs and leave suggestions.", "gd-bbpress-attachments").'</p>'));
    }

    public function menu_attachments() {
        $options = GDATTCore::instance()->o;
        $_user_roles = d4p_bbpress_get_user_roles();

        include(GDBBPRESSATTACHMENTS_PATH.'forms/panels.php');
    }
}
