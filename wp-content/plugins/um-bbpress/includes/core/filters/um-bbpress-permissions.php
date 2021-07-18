<?php if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Filter user permissions in bbPress
 *
 * @param $meta
 * @param $user_id
 *
 * @return mixed
 */
function um_bbpress_user_permissions_filter( $meta, $user_id ) {
	if ( ! isset( $meta['can_have_forums_tab'] ) ) {
		$meta['can_have_forums_tab'] = 1;
	}
		
	if ( ! isset( $meta['can_create_topics'] ) ) {
		$meta['can_create_topics'] = 1;
	}
		
	if ( ! isset( $meta['can_create_replies'] ) ) {
		$meta['can_create_replies'] = 1;
	}
		
	if ( ! isset( $meta['lock_days'] ) ) {
		$meta['lock_days'] = 0;
	}
		
	if ( ! isset( $meta['lock_notice'] ) ) {
		$meta['lock_notice'] = 0;
	}

	if ( ! isset( $meta['lock_notice2'] ) ) {
		$meta['lock_notice2'] = 0;
	}

	return $meta;
}
add_filter( 'um_user_permissions_filter', 'um_bbpress_user_permissions_filter', 10, 4 );