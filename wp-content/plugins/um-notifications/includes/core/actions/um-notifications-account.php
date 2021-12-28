<?php if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Hook in account update
 */
function um_notification_account_update() {
	if ( isset( $_POST['um-notifyme'] ) ) {

		foreach ( UM()->Notifications_API()->api()->get_log_types() as $key => $arr ) {
			if ( ! isset( $_POST['um-notifyme'][ $key ] ) ) {
				$_POST['um-notifyme'][ $key ] = 0;
			} else {
				$_POST['um-notifyme'][ $key ] = UM()->options()->get( 'log_' . $key );
			}
		}

		update_user_meta( get_current_user_id(), '_notifications_prefs', $_POST['um-notifyme'] );

	} else {
			
		//update_user_meta( get_current_user_id(), '_notifications_prefs', array('') );
			
	}

}
add_action( 'um_post_account_update', 'um_notification_account_update' );


/**
 * When user is removed all notifications should be erased
 *
 * @param $user_id
 */
function um_notification_delete_user_data( $user_id ) {
	global $wpdb;
	$wpdb->delete( $wpdb->prefix . "um_notifications" , array( 'user' => $user_id ) );
}
add_action( 'um_delete_user', 'um_notification_delete_user_data' );