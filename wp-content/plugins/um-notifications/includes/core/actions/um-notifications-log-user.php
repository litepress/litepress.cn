<?php if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Notification about changed user's role
 *
 * @param array $new_roles
 * @param array $old_roles
 * @param int $user_id
 */
function um_notification_log_role_change( $new_roles, $old_roles, $user_id ) {
	$diff = array_diff( $old_roles, $new_roles );

	if ( count( $old_roles ) != count( $new_roles ) || ! empty( $diff ) ) {
		$vars['photo'] = um_get_avatar_url( get_avatar( $user_id, 40 ) );
		$vars['notification_uri'] = um_user_profile_url();

		$vars['role_pre'] = array_map( function( $item ) {
			return UM()->roles()->get_role_name( $item );
		}, $old_roles );
		$vars['role_pre'] = implode( ', ', $vars['role_pre'] );

		$vars['role_post'] = array_map( function( $item ) {
			return UM()->roles()->get_role_name( $item );
		}, $new_roles );
		$vars['role_post'] = implode( ', ', $vars['role_post'] );

		UM()->Notifications_API()->api()->store_notification( $user_id, 'upgrade_role', $vars );
	}
}
add_action( 'um_after_member_role_upgrade', 'um_notification_log_role_change', 10, 3 );