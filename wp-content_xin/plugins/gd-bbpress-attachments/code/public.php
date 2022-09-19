<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get the list of attachments for a post.
 *
 * @param int $post_id topic or reply ID to get attachments for
 *
 * @return array list of attachments objects
 */
function d4p_get_post_attachments($post_id) {
    $args = array(
        'post_type' => 'attachment',
        'numberposts' => -1,
        'post_status' => null,
        'post_parent' => $post_id,
        'orderby' => 'ID',
        'order' => 'ASC'
    );

    return get_posts($args);
}

/**
 * Count attachments for the forum topic. It can include topic replies in the count.
 *
 * @param int  $topic_id        id of the topic to count attachments for
 * @param bool $include_replies true, to include reply attachments
 *
 * @return int number of attachments
 */
function d4p_topic_attachments_count($topic_id, $include_replies = false) {
    global $wpdb;

    $sql = "select ID from ".$wpdb->posts." where post_parent = ".$topic_id." and post_type = 'attachment'";

    if ($include_replies) {
        $sql = "(".$sql.") union (select ID from ".$wpdb->posts." where post_parent in (select ID from ".$wpdb->posts." where post_parent = ".$topic_id." and post_type = 'reply') and post_type = 'attachment')";
    }

    $attachments = $wpdb->get_results($sql);
    return count($attachments);
}

/**
 * Handle upload file error.
 *
 * @param string $file    file with error
 * @param string $message error message
 *
 * @return WP_Error error message
 */
function d4p_bbattachment_handle_upload_error(&$file, $message) {
    return new WP_Error("wp_upload_error", $message);
}

/**
 * Get current page forum ID. Handles the edge cases with the edit forms.
 *
 * @return int
 */
function d4p_get_forum_id() {
    $forum_id = bbp_get_forum_id();

    if ($forum_id == 0) {
        if (bbp_is_topic_edit()) {
            $topic_id = bbp_get_topic_id();
            $forum_id = bbp_get_topic_forum_id($topic_id);
        } else if (bbp_is_reply_edit()) {
            $reply_id = bbp_get_reply_id();
            $forum_id = bbp_get_reply_forum_id($reply_id);
        }
    }

    return $forum_id;
}

function d4p_bba_o($name) {
    return GDATTCore::instance()->o[$name];
}
