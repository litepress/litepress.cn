<?php

if (!defined('ABSPATH')) {
    exit;
}

class GDATTCore {
    private $wp_version;
    private $plugin_path;
    private $plugin_url;

    public $l;
    public $o;

    function __construct() {
        global $wp_version;

        $this->wp_version = substr(str_replace('.', '', $wp_version), 0, 2);

        $gdd = new GDATTDefaults();

        $this->o = get_option('gd-bbpress-attachments');
        if (!is_array($this->o)) {
            $this->o = $gdd->default_options;
            update_option('gd-bbpress-attachments', $this->o);
        }

        if (!isset($this->o['build']) || $this->o['build'] != $gdd->default_options['build']) {
            $this->o = $this->_upgrade($this->o, $gdd->default_options);

            $this->o['version'] = $gdd->default_options['version'];
            $this->o['date'] = $gdd->default_options['date'];
            $this->o['status'] = $gdd->default_options['status'];
            $this->o['build'] = $gdd->default_options['build'];
            $this->o['revision'] = $gdd->default_options['revision'];
            $this->o['edition'] = $gdd->default_options['edition'];

            update_option('gd-bbpress-attachments', $this->o);
        }

        define('GDBBPRESSATTACHMENTS_INSTALLED', $gdd->default_options['version'].' Free');
        define('GDBBPRESSATTACHMENTS_VERSION', $gdd->default_options['version'].'_b'.($gdd->default_options['build'].'_free'));

        $this->plugin_path = dirname(dirname(__FILE__)).'/';
        $this->plugin_url = plugins_url('/gd-bbpress-attachments/');

        define('GDBBPRESSATTACHMENTS_URL', $this->plugin_url);
        define('GDBBPRESSATTACHMENTS_PATH', $this->plugin_path);

        add_action('after_setup_theme', array($this, 'load'), 5);
    }

    public static function instance() {
        static $instance = false;

        if ($instance === false) {
            $instance = new GDATTCore();
        }

        return $instance;
    }

    private function _upgrade($old, $new) {
        foreach ($new as $key => $value) {
            if (!isset($old[$key])) {
                $old[$key] = $value;
            }
        }

        $unset = array();
        foreach ($old as $key => $value) {
            if (!isset($new[$key])) {
                $unset[] = $key;
            }
        }

        foreach ($unset as $key) {
            unset($old[$key]);
        }

        return $old;
    }

    public function load() {
        load_plugin_textdomain('gd-bbpress-attachments', false, 'gd-bbpress-attachments/languages');

        add_action('init', array($this, 'init_thumbnail_size'), 1);
        add_action('init', array($this, 'delete_attachments'));

        add_action('before_delete_post', array($this, 'delete_post'));

        if (is_admin()) {
            require_once(GDBBPRESSATTACHMENTS_PATH.'code/admin.php');
            require_once(GDBBPRESSATTACHMENTS_PATH.'code/meta.php');

            GDATTAdmin::instance();
            GDATTAdminMeta::instance();
        } else {
            require_once(GDBBPRESSATTACHMENTS_PATH.'code/front.php');

            GDATTFront::instance();
        }
    }

    public function init_thumbnail_size() {
        add_image_size('d4p-bbp-thumb', $this->o['image_thumbnail_size_x'], $this->o['image_thumbnail_size_y'], true);
    }

    public function delete_attachments() {
        if (isset($_GET['d4pbbaction'])) {
            $nonce = wp_verify_nonce($_GET['_wpnonce'], 'd4p-bbpress-attachments');

            if ($nonce) {
                global $user_ID;

                $action = $_GET['d4pbbaction'];
                $att_id = intval($_GET['att_id']);
                $bbp_id = intval($_GET['bbp_id']);

                $post = get_post($bbp_id);
                $author_ID = $post->post_author;

                $file = get_attached_file($att_id);
                $file = pathinfo($file, PATHINFO_BASENAME);

                $allow = 'no';
                if (d4p_is_user_admin()) {
                    $allow = d4p_bba_o('delete_visible_to_admins');
                } else if (d4p_is_user_moderator()) {
                    $allow = d4p_bba_o('delete_visible_to_moderators');
                } else if ($author_ID == $user_ID) {
                    $allow = d4p_bba_o('delete_visible_to_author');
                }

                if ($action == 'delete' && ($allow == 'delete' || $allow == 'both')) {
                    wp_delete_attachment($att_id);

                    add_post_meta($bbp_id, '_bbp_attachment_log', array(
                        'code' => 'delete_attachment',
                        'user' => $user_ID,
                        'file' => $file)
                    );
                }

                if ($action == 'detach' && ($allow == 'detach' || $allow == 'both')) {
                    global $wpdb;
                    $wpdb->update($wpdb->posts, array('post_parent' => 0), array('ID' => $att_id));

                    add_post_meta($bbp_id, '_bbp_attachment_log', array(
                        'code' => 'detach_attachment',
                        'user' => $user_ID,
                        'file' => $file)
                    );
                }
            }

            $url = remove_query_arg(array('_wpnonce', 'd4pbbaction', 'att_id', 'bbp_id'));
            wp_redirect($url);
            exit;
        }
    }

    public function delete_post($id) {
        if (d4p_has_bbpress()) {
            if (bbp_is_reply($id) || bbp_is_topic($id)) {
                if ($this->o['delete_attachments'] == 'delete') {
                    $files = d4p_get_post_attachments($id);

                    if (is_array($files) && !empty($files)) {
                        foreach ($files as $file) {
                            wp_delete_attachment($file->ID);
                        }
                    }
                } else if ($this->o['delete_attachments'] == 'detach') {
                    global $wpdb;

                    $wpdb->update($wpdb->posts, array('post_parent' => 0), array('post_parent' => $id, 'post_type' => 'attachment'));
                }
            }
        }
    }

    public function enabled_for_forum($id = 0) {
        $meta = get_post_meta(bbp_get_forum_id($id), '_gdbbatt_settings', true);
        return !isset($meta['disable']) || (isset($meta['disable']) && $meta['disable'] == 0);
    }

    public function get_file_size($global_only = false, $forum_id = 0) {
        $forum_id = $forum_id == 0 ? bbp_get_forum_id() : $forum_id;
        $value = $this->o['max_file_size'];

        if (!$global_only) {
            $meta = get_post_meta($forum_id, '_gdbbatt_settings', true);

            if (is_array($meta) && $meta['to_override'] == 1) {
                $value = $meta['max_file_size'];
            }
        }

        return $value;
    }

    public function get_max_files($global_only = false, $forum_id = 0) {
        $forum_id = $forum_id == 0 ? bbp_get_forum_id() : $forum_id;
        $value = $this->o['max_to_upload'];

        if (!$global_only) {
            $meta = get_post_meta($forum_id, '_gdbbatt_settings', true);

            if (is_array($meta) && $meta['to_override'] == 1) {
                $value = $meta['max_to_upload'];
            }
        }

        return $value;
    }

    public function is_right_size($file, $forum_id = 0) {
        $forum_id = $forum_id == 0 ? bbp_get_forum_id() : $forum_id;

        $file_size = apply_filters('d4p_bbpressattchment_max_file_size', $this->get_file_size(false, $forum_id), $forum_id);

        return $file["size"] < $file_size * 1024;
    }

    public function is_user_allowed() {
        $allowed = false;

        if (is_user_logged_in()) {
            if (!isset($this->o['roles_to_upload'])) {
                $allowed = true;
            } else {
                $value = $this->o['roles_to_upload'];
                if (!is_array($value)) {
                    $allowed = true;
                }

                global $current_user;
                if (is_array($current_user->roles)) {
                    $matched = array_intersect($current_user->roles, $value);
                    $allowed = !empty($matched);
                }
            }
        }

        return apply_filters('d4p_bbpressattchment_is_user_allowed', $allowed);
    }

    public function is_hidden_from_visitors($forum_id = 0) {
        $forum_id = $forum_id == 0 ? bbp_get_forum_id() : $forum_id;

        $value = $this->o['hide_from_visitors'];
        $meta = get_post_meta($forum_id, '_gdbbatt_settings', true);

        if (is_array($meta) && $meta['to_override'] == 1) {
            $value = $meta['hide_from_visitors'];
        }

        return apply_filters('d4p_bbpressattchment_is_hidden_from_visitors', $value == 1);
    }
}
