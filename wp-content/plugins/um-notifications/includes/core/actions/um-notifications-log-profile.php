<?php if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * @param $args
 */
function um_notification_log_view( $args ) {
	if ( ! um_is_core_page( 'user' ) ) {
		return;
	}

	global $post;

	if ( is_user_logged_in() && get_current_user_id() != um_profile_id() ) {

		um_fetch_user( get_current_user_id() );

		$vars['photo'] = um_get_avatar_url( get_avatar( get_current_user_id(), 40 ) );

		$vars['member'] = um_user( 'display_name' );

		$vars['notification_uri'] = um_user_profile_url();

		um_fetch_user( um_profile_id() );

		UM()->Notifications_API()->api()->store_notification( um_profile_id(), 'profile_view', $vars );

	}

	if ( ! is_user_logged_in() && isset( $post->ID ) ) {
		$restriction = UM()->access()->get_post_privacy_settings( $post );
		if ( $restriction && $restriction['_um_accessible'] == '2' ) {
			return;
		}

		$vars['photo'] = um_get_avatar_url( get_avatar( '123456789', 40 ) );

		um_fetch_user( um_profile_id() );

		UM()->Notifications_API()->api()->store_notification( um_profile_id(), 'profile_view_guest', $vars );
	}
}
add_action( 'wp_head', 'um_notification_log_view', 100 );