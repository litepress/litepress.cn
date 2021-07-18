<?php if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * bbPress user capabilities
 *
 * @param $caps
 * @param $cap
 * @param $user_id
 * @param $args
 *
 * @return array
 */
function um_bbpress_meta_caps_filter( $caps, $cap, $user_id, $args ) {

	if ( in_array( 'administrator', $caps ) ) {
		return $caps;
	}

	$current_user_roles = um_user( 'roles' );

	switch ( $cap ) {
		case 'publish_topics':

			$caps[] = 'do_not_allow';

			$_um_bbpress_can_topic = get_post_meta( get_the_ID(), '_um_bbpress_can_topic', true );
			if ( empty( $_um_bbpress_can_topic ) ) {
				if ( UM()->roles()->um_user_can( 'can_create_topics' ) ) {
					unset( $caps[ array_search( 'do_not_allow', $caps ) ] );
				}
			} else {
				if ( ! empty( $current_user_roles ) && count( array_intersect( $current_user_roles, $_um_bbpress_can_topic ) ) > 0 ) {
					unset( $caps[ array_search( 'do_not_allow', $caps ) ] );
				}
			}

			if ( ! UM()->bbPress_API()->can_do_topic() ) {
				$caps[] = 'do_not_allow';
			}

			break;

		case 'publish_replies':

			$caps[] = 'do_not_allow';

			if ( bbp_is_topic( get_the_ID() ) ) {
				$forum_id = bbp_get_topic_forum_id( get_the_ID() );
			} else {
				$forum_id = get_the_ID();
			}

			$_um_bbpress_can_reply = get_post_meta( $forum_id, '_um_bbpress_can_reply', true );
			if ( empty( $_um_bbpress_can_reply ) ) {
				if ( UM()->roles()->um_user_can( 'can_create_replies' ) ) {
					unset( $caps[ array_search( 'do_not_allow', $caps ) ] );
				}
			} else {
				if ( ! empty( $current_user_roles ) && count( array_intersect( $current_user_roles, $_um_bbpress_can_reply ) ) > 0 ) {
					unset( $caps[ array_search( 'do_not_allow', $caps ) ] );
				}
			}

			break;
	}

	return $caps;
}
add_filter( 'bbp_map_meta_caps', 'um_bbpress_meta_caps_filter', 10, 4 );