<?php if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Show Notifications Bell
 */
function um_notification_show_feed() {
	if ( ! is_user_logged_in() || is_admin() ) {
		return;
	}

	$notifications = UM()->Notifications_API()->api()->get_notifications( 10 );
	$template = $notifications ? 'notifications.php' : 'no-notifications.php';

	$unread = (int)UM()->Notifications_API()->api()->get_notifications( 0, 'unread', true );
	$unread_count = ( absint( $unread ) > 9 ) ? '+9' : $unread;

	$t_args = compact( 'notifications', 'template', 'unread', 'unread_count' );
	UM()->get_template( 'feed.php', um_notifications_plugin, $t_args, true );
}
add_action( 'wp_footer', 'um_notification_show_feed', 99999999999 );


/**
 *
 */
function um_enqueue_feed_scripts() {
	if ( ! is_user_logged_in() ) {
		return;
	}

	wp_enqueue_script( 'um_notifications' );
	wp_enqueue_style( 'um_notifications' );
}
add_action( 'wp_footer', 'um_enqueue_feed_scripts', -1 );