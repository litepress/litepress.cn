<?php

if (!defined('ABSPATH')) {
    exit;
}

class GDATTAdminMeta {
    function __construct() {
        add_action('after_setup_theme', array($this, 'load'), 10);
    }

    public function load() {
        add_action('admin_init', array($this, 'admin_init'));
        add_action('admin_menu', array($this, 'admin_meta'));
        add_action('admin_head', array($this, 'admin_head'));

        add_action('save_post', array($this, 'save_edit_forum'));

        add_action('manage_topic_posts_columns', array($this, 'admin_post_columns'), 1000);
        add_action('manage_reply_posts_columns', array($this, 'admin_post_columns'), 1000);

        add_action('manage_topic_posts_custom_column', array($this, 'admin_columns_data'), 1000, 2);
        add_action('manage_reply_posts_custom_column', array($this, 'admin_columns_data'), 1000, 2);
    }

    public static function instance() {
        static $instance = false;

        if ($instance === false) {
            $instance = new GDATTAdminMeta();
        }

        return $instance;
    }

    public function admin_init() {
        if (isset($_POST['gdbb-attach-submit'])) {

            check_admin_referer('gd-bbpress-attachments');

            GDATTCore::instance()->o['max_file_size'] = absint($_POST['max_file_size']);
            GDATTCore::instance()->o['max_to_upload'] = absint($_POST['max_to_upload']);
            GDATTCore::instance()->o['roles_to_upload'] = (array)$_POST['roles_to_upload'];
            GDATTCore::instance()->o['attachment_icon'] = isset($_POST['attachment_icon']) ? 1 : 0;
            GDATTCore::instance()->o['attachment_icons'] = isset($_POST['attachment_icons']) ? 1 : 0;
            GDATTCore::instance()->o['hide_from_visitors'] = isset($_POST['hide_from_visitors']) ? 1 : 0;
            GDATTCore::instance()->o['include_always'] = isset($_POST['include_always']) ? 1 : 0;
            GDATTCore::instance()->o['delete_attachments'] = d4p_sanitize_basic($_POST['delete_attachments']);

            update_option('gd-bbpress-attachments', GDATTCore::instance()->o);
            wp_redirect(add_query_arg('settings-updated', 'true'));
            exit();
        }

        if (isset($_POST['gdbb-att-advanced-submit'])) {
            check_admin_referer('gd-bbpress-attachments');

            GDATTCore::instance()->o['log_upload_errors'] = isset($_POST['log_upload_errors']) ? 1 : 0;
            GDATTCore::instance()->o['errors_visible_to_admins'] = isset($_POST['errors_visible_to_admins']) ? 1 : 0;
            GDATTCore::instance()->o['errors_visible_to_moderators'] = isset($_POST['errors_visible_to_moderators']) ? 1 : 0;
            GDATTCore::instance()->o['errors_visible_to_author'] = isset($_POST['errors_visible_to_author']) ? 1 : 0;
            GDATTCore::instance()->o['delete_visible_to_admins'] = d4p_sanitize_basic($_POST['delete_visible_to_admins']);
            GDATTCore::instance()->o['delete_visible_to_moderators'] = d4p_sanitize_basic($_POST['delete_visible_to_moderators']);
            GDATTCore::instance()->o['delete_visible_to_author'] = d4p_sanitize_basic($_POST['delete_visible_to_author']);

            update_option('gd-bbpress-attachments', GDATTCore::instance()->o);
            wp_redirect(add_query_arg('settings-updated', 'true'));
            exit();
        }

        if (isset($_POST['gdbb-att-images-submit'])) {
            check_admin_referer('gd-bbpress-attachments');

            GDATTCore::instance()->o['image_thumbnail_active'] = isset($_POST['image_thumbnail_active']) ? 1 : 0;
            GDATTCore::instance()->o['image_thumbnail_inline'] = isset($_POST['image_thumbnail_inline']) ? 1 : 0;
            GDATTCore::instance()->o['image_thumbnail_caption'] = isset($_POST['image_thumbnail_caption']) ? 1 : 0;
            GDATTCore::instance()->o['image_thumbnail_rel'] = d4p_sanitize_basic($_POST['image_thumbnail_rel']);
            GDATTCore::instance()->o['image_thumbnail_css'] = d4p_sanitize_basic($_POST['image_thumbnail_css']);
            GDATTCore::instance()->o['image_thumbnail_size_x'] = absint($_POST['image_thumbnail_size_x']);
            GDATTCore::instance()->o['image_thumbnail_size_y'] = absint($_POST['image_thumbnail_size_y']);

            update_option('gd-bbpress-attachments', GDATTCore::instance()->o);
            wp_redirect(add_query_arg('settings-updated', 'true'));
            exit();
        }
    }

    public function admin_head() { ?>
        <style type="text/css">
            /*<![CDATA[*/
            th.column-gdbbatt_count,
            td.column-gdbbatt_count {
                width: 3%;
                text-align: center;
            }
            /*]]>*/
        </style><?php
    }

    public function save_edit_forum($post_id) {
        if (isset($_POST['post_ID']) && $_POST['post_ID'] > 0) {
            $post_id = $_POST['post_ID'];
        }

        if (isset($_POST['gdbbatt_forum_meta']) && $_POST['gdbbatt_forum_meta'] == 'edit') {
            $data = (array)$_POST['gdbbatt'];
            $meta = array(
                'disable' => isset($data['disable']) ? 1 : 0,
                'to_override' => isset($data['to_override']) ? 1 : 0,
                'hide_from_visitors' => isset($data['hide_from_visitors']) ? 1 : 0,
                'max_file_size' => absint($data['max_file_size']),
                'max_to_upload' => absint($data['max_to_upload'])
            );

            update_post_meta($post_id, '_gdbbatt_settings', $meta);
        }
    }

    public function admin_post_columns($columns) {
        $columns['gdbbatt_count'] = '<img src="'.GDBBPRESSATTACHMENTS_URL.'css/gfx/attachment.png" width="16" height="12" alt="'.__("Attachments", "gd-bbpress-attachments").'" title="'.__("Attachments", "gd-bbpress-attachments").'" />';

        return $columns;
    }

    public function admin_columns_data($column, $id) {
        if ($column == 'gdbbatt_count') {
            $attachments = d4p_get_post_attachments($id);
            echo count($attachments);
        }
    }

    public function admin_meta() {
        if (current_user_can(GDBBPRESSATTACHMENTS_CAP)) {
            add_meta_box('gdbbattach-meta-forum', __("Attachments Settings", "gd-bbpress-attachments"), array($this, 'metabox_forum'), 'forum', 'side', 'high');
            add_meta_box('gdbbattach-meta-files', __("Attachments List", "gd-bbpress-attachments"), array($this, 'metabox_files'), 'topic', 'side', 'high');
            add_meta_box('gdbbattach-meta-files', __("Attachments List", "gd-bbpress-attachments"), array($this, 'metabox_files'), 'reply', 'side', 'high');
        }
    }

    public function metabox_forum() {
        global $post_ID;

        $meta = get_post_meta($post_ID, '_gdbbatt_settings', true);
        if (!is_array($meta)) {

            $meta = array(
                'disable' => 0,
                'to_override' => 0,
                'hide_from_visitors' => 1,
                'max_file_size' => GDATTCore::instance()->get_file_size(true),
                'max_to_upload' => GDATTCore::instance()->get_max_files(true)
            );
        }

        include(GDBBPRESSATTACHMENTS_PATH.'forms/attachments/meta_forum.php');
    }

    public function metabox_files() {
        global $post_ID, $user_ID;

        $post = get_post($post_ID);
        $author_id = $post->post_author;

        include(GDBBPRESSATTACHMENTS_PATH.'forms/attachments/meta_files.php');
    }
}
