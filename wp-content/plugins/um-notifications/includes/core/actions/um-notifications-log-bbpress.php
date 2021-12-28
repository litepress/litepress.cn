<?php
if ( ! defined( 'ABSPATH' ) ) exit;


	add_action( 'um_bbpress_new_reply', 'um_notification_log_bbpress_reply', 1000, 5);
	function um_notification_log_bbpress_reply( $reply_id, $topic_id, $forum_id, $anonymous_data, $reply_author_id ) {
		global $wpdb;

		$vars = array();
		$user_id = get_current_user_id();
		
		$voices = $wpdb->get_col( $wpdb->prepare(
			"SELECT DISTINCT post_author 
			FROM {$wpdb->posts} 
			WHERE ( post_parent = %d AND post_status = '%s' AND post_type = '%s' ) OR 
				  ( ID = %d AND post_type = '%s' );",
			$topic_id,
			bbp_get_public_status_id(),
			bbp_get_reply_post_type(),
			$topic_id,
			bbp_get_topic_post_type()
		) );


		if( empty( $voices ) )
		    return;

		foreach( $voices as $author_id  ){
			
			if ( $author_id == $user_id ) continue; // Notify himself? no.
			
			// Not a guest
			if ( $author_id ) {
				
				um_fetch_user( $user_id );
				$vars['photo'] = um_get_avatar_url( get_avatar( $user_id, 40 ) );
				$vars['member'] = um_user('display_name');
				
				$user = 'user';
				
			} else {	
				$user = 'guest';
			}
			
			$vars['notification_uri'] = bbp_get_reply_url( $reply_id );

            UM()->Notifications_API()->api()->store_notification( $author_id, "bbpress_{$user}_reply", $vars );
		}
		
	}
