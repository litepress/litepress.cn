<?php if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Creates UM Permissions metabox for Forum CPT
 *
 * @param $action
 */
function um_bbpress_add_access_metabox( $action ) {
	add_meta_box(
		"um-admin-custom-access/bbpress{" . um_bbpress_path . "}",
		__( 'UM Permissions', 'um-bbpress' ),
		array( UM()->metabox(), 'load_metabox_custom' ),
		'forum',
		'side',
		'low'
	);
}
add_action( 'add_meta_boxes', 'um_bbpress_add_access_metabox' );


/**
 * Save postmeta on Forum CPT
 *
 * @param bool $post_id
 * @param bool|WP_Post $post
 */
function um_bbpress_save_access_metabox( $post_id = false, $post = false ) {
	if ( empty( $post->post_type ) || $post->post_type != 'forum' ) {
		return;
	}

	$um_bbpress_can_topic = ! empty( $_POST['_um_bbpress_can_topic'] ) ? UM()->clean_array( $_POST['_um_bbpress_can_topic'] ) : array();
	$um_bbpress_can_reply = ! empty( $_POST['_um_bbpress_can_reply'] ) ? UM()->clean_array( $_POST['_um_bbpress_can_reply'] ) : array();

	update_post_meta( $post_id, '_um_bbpress_can_topic', $um_bbpress_can_topic );
	update_post_meta( $post_id, '_um_bbpress_can_reply', $um_bbpress_can_reply );
}
add_action( 'um_admin_custom_restrict_content_metaboxes', 'um_bbpress_save_access_metabox', 10, 2 );


/**
 * creates options in Role page
 *
 * @param array $roles_metaboxes
 *
 * @return array
 */
function um_bbpress_add_role_metabox( $roles_metaboxes ) {

	$roles_metaboxes[] = array(
		'id'        => "um-admin-form-bbpress{" . um_bbpress_path . "}",
		'title'     => __( 'bbPress', 'um-bbpress' ),
		'callback'  => array( UM()->metabox(), 'load_metabox_role' ),
		'screen'    => 'um_role_meta',
		'context'   => 'normal',
		'priority'  => 'default'
	);

	return $roles_metaboxes;
}
add_filter( 'um_admin_role_metaboxes', 'um_bbpress_add_role_metabox', 10, 1 );