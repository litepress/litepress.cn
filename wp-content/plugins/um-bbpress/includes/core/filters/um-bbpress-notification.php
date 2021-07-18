<?php if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * @param $array
 *
 * @return mixed
 */
function um_bbpress_notifications_types( $logs ) {

	$logs['bbpress_user_reply'] = array(
		'title'         => __( 'User leaves a reply to bbpress topic', 'um-bbpress' ),
		'account_desc'  => __( 'When a member replies to one of my topics', 'um-bbpress' ),
	);

	$logs['bbpress_guest_reply'] = array(
		'title'         => __( 'Guest leaves a reply to bbpress topic', 'um-bbpress' ),
		'account_desc'  => __( 'When a guest replies to one of my topics', 'um-bbpress' ),
	);

	return $logs;
}
add_filter( 'um_notifications_core_log_types', 'um_bbpress_notifications_types', 9999 , 1 );