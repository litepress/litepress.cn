<?php
if ( ! defined( 'ABSPATH' ) ) exit;


	// Post comment
	add_action('comment_post', 'um_notification_log_comment', 10, 2);
	function um_notification_log_comment($comment_ID, $status) {
		$vars = array();
		if ( $status == 1 ) {
			
			$comment = get_comment( $comment_ID );
	
			$parent = $comment->comment_parent;
			
			if ( $parent ) {
				
				$parentc = get_comment( $parent );
				$author = $parentc->user_id;
				
				if ( $author == $comment->user_id ) return;
				
				if ( $comment->user_id == 0 || $author == 0 ) return;
				
				$vars['notification_uri'] = get_comment_link( $comment->comment_ID );
				um_fetch_user( $comment->user_id );
				$vars['photo'] = um_get_avatar_url( get_avatar( $comment->user_id, 40 ) );
				$vars['member'] = um_user('display_name');
				$vars['comment_excerpt'] = get_comment_excerpt( $comment->comment_ID );

                UM()->Notifications_API()->api()->store_notification( $author, 'comment_reply', $vars );
				
			} else {
			
				$post = get_post( $comment->comment_post_ID );
				$author = $post->post_author;
				
				$vars['comment_excerpt'] = get_comment_excerpt( $comment->comment_ID );
				
				$vars['notification_uri'] = get_comment_link( $comment->comment_ID );
					
				if ( $comment->user_id == $author && is_user_logged_in() ) return;
				
				if ( $comment->user_id > 0 ) {

					um_fetch_user( $comment->user_id );
					$vars['photo'] = um_get_avatar_url( get_avatar( $comment->user_id, 40 ) );
					$vars['member'] = um_user('display_name');

                    UM()->Notifications_API()->api()->store_notification( $author, 'user_comment', $vars );
				
				} else {

                    UM()->Notifications_API()->api()->store_notification( $author, 'guest_comment', $vars );
					
				}
			
			}

		}
	}
	
	// Approved comment
	add_action('transition_comment_status', 'um_notification_log_comment_edit', 10, 3);
	function um_notification_log_comment_edit($new_status, $old_status, $comment) {
		$vars = array();
		if($old_status != $new_status) {
			if($new_status == 'approved') {

				$parent = $comment->comment_parent;
				
				if ( $parent ) {
					
					$parentc = get_comment( $parent );
					$author = $parentc->user_id;
					
					if ( $author == $comment->user_id ) return;
					
					if ( $comment->user_id == 0 || $author == 0 ) return;
					
					$vars['notification_uri'] = get_comment_link( $comment->comment_ID );
					um_fetch_user( $comment->user_id );
					$vars['photo'] = um_get_avatar_url( get_avatar( $comment->user_id, 40 ) );
					$vars['member'] = um_user('display_name');
                    UM()->Notifications_API()->api()->store_notification( $author, 'comment_reply', $vars );
					
				} else {
					
					$post = get_post( $comment->comment_post_ID );
					$author = $post->post_author;
					
					$vars['comment_excerpt'] = get_comment_excerpt( $comment->comment_ID );
					
					$vars['notification_uri'] = get_comment_link( $comment->comment_ID );
					
					if ( $comment->user_id == $author && is_user_logged_in() ) return;
					
					if ( $comment->user_id > 0 ) {

						um_fetch_user( $comment->user_id );
						$vars['photo'] = um_get_avatar_url( get_avatar( $comment->user_id, 40 ) );
						$vars['member'] = um_user('display_name');

                        UM()->Notifications_API()->api()->store_notification( $author, 'user_comment', $vars );
					
					} else {

                        UM()->Notifications_API()->api()->store_notification( $author, 'guest_comment', $vars );
						
					}
				
				}
			
			}
		}
	}