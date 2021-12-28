<?php
if ( ! defined( 'ABSPATH' ) ) exit;


add_action( 'um_followers_after_user_follow', 'um_notifications_followers', 10, 2 );
function um_notifications_followers( $user_id1, $user_id2 ) {
	um_fetch_user( $user_id2 );

	$vars['photo'] = um_get_avatar_url( get_avatar( $user_id2, 40 ) );
	$vars['member'] = um_user('display_name');
	$vars['notification_uri'] = um_user_profile_url();

	um_fetch_user( $user_id1 );
	UM()->Notifications_API()->api()->store_notification( $user_id1, 'new_follow', $vars );
}


add_action( 'um_followers_new_mention', 'um_notifications_followers_new_mention', 10, 3 );
add_action( 'um_following_new_mention', 'um_notifications_followers_new_mention', 10, 3 );
function um_notifications_followers_new_mention( $user_id1, $user_id2, $post_id ) {
	um_fetch_user( $user_id1 );

	$vars['photo'] = um_get_avatar_url( get_avatar( $user_id1, 80 ) );
	$vars['member'] = um_user('display_name');
	$vars['notification_uri'] = UM()->Activity_API()->api()->get_permalink( $post_id );

	UM()->Notifications_API()->api()->store_notification( $user_id2, 'new_mention', $vars );
}


/**
 * @param int $user_id1
 * @param int $user_id2
 * @param int $post_id
 */
function um_notifications_friends_new_mention( $user_id1, $user_id2, $post_id ) {
	um_fetch_user( $user_id1 );

	$vars['photo'] = um_get_avatar_url( get_avatar( $user_id1, 80 ) );
	$vars['member'] = um_user('display_name');
	$vars['notification_uri'] = UM()->Activity_API()->api()->get_permalink( $post_id );

	UM()->Notifications_API()->api()->store_notification( $user_id2, 'new_mention', $vars );
}
add_action( 'um_friends_new_mention', 'um_notifications_friends_new_mention', 10, 3 );


add_action( 'um_friends_after_user_friend_request', 'um_notification_friends_request', 10, 2 );
function um_notification_friends_request( $user_id1, $user_id2 ) {
	um_fetch_user( $user_id2 );

	$vars['photo'] = um_get_avatar_url( get_avatar( $user_id2, 40 ) );
	$vars['member'] = um_user('display_name');
	$vars['notification_uri'] = um_user_profile_url();

	um_fetch_user( $user_id1 );
	UM()->Notifications_API()->api()->store_notification( $user_id1, 'new_friend_request', $vars );
}


add_action( 'um_friends_after_user_friend', 'um_notification_friends_approve', 10, 2 );
function um_notification_friends_approve( $user_id1, $user_id2 ) {
	um_fetch_user( $user_id2 );

	$vars['photo'] = um_get_avatar_url( get_avatar( $user_id2, 40 ) );
	$vars['member'] = um_user('display_name');
	$vars['notification_uri'] = um_user_profile_url();

	um_fetch_user( $user_id1 );
	UM()->Notifications_API()->api()->store_notification( $user_id1, 'new_friend', $vars );
}


/***
 ***	@Send a web notification
 ***/
add_action( 'um_after_new_message','um_notification_messaging', 50, 4 );
function um_notification_messaging( $to, $from, $conversation_id, $message_data = array() ) {
	um_fetch_user( $from );

	$vars['photo'] = um_get_avatar_url( get_avatar( $from, 40 ) );
	$vars['member'] = um_user('display_name');

	um_fetch_user( $to );

	$notification_uri = add_query_arg( 'profiletab', 'messages', um_user_profile_url() );
	$notification_uri = add_query_arg( 'conversation_id', $conversation_id, $notification_uri );

	$vars['notification_uri'] = $notification_uri;
	UM()->Notifications_API()->api()->store_notification( $to, 'new_pm', $vars );
}


add_filter( 'um_mycred_add_func','um_notification_mycred_func', 10, 1 );
add_filter( 'um_mycred_deduct_func','um_notification_mycred_func', 10, 1 );
function um_notification_mycred_func( $default ) {
	return false;
}


/***
 ***	@send a web notification after new post comment
 ***/
add_action('um_activity_after_wall_comment_published','um_notification_activity_comment', 90, 4 );
function um_notification_activity_comment( $comment_id, $comment_parent, $post_id, $user_id ) {
	if ( $comment_parent > 0 ) return false;

	$author = UM()->Activity_API()->api()->get_author( $post_id );
	if ( $author == $user_id ) return false;

	um_fetch_user( $user_id );

	$vars['photo'] = um_get_avatar_url( get_avatar( $user_id, 80 ) );
	$vars['member'] = um_user('display_name');

	um_fetch_user( $author );

	$url = UM()->Activity_API()->api()->get_permalink( $post_id );

	$vars['notification_uri'] = $url;

	UM()->Notifications_API()->api()->store_notification( $author, 'new_wall_comment', $vars );
}

/***
 ***	@send a web notification after new comment reply
 ***/
add_action('um_activity_after_wall_comment_reply_published','um_notification_activity_after_wall_comment_reply_published', 90, 4 );
function um_notification_activity_after_wall_comment_reply_published( $comment_id, $comment_parent, $post_id, $user_id ) {
	if ( $comment_parent <= 0 ) return false;

	$comment_parent_author = get_comment( $comment_parent );
	$parent_author = get_user_by('email', $comment_parent_author->comment_author_email );

	if( ! $parent_author  ) return false;

	um_fetch_user( $user_id );

	$vars['photo'] = um_get_avatar_url( get_avatar( $user_id, 80 ) );
	$vars['member'] = um_user('display_name');

	$comments = get_comments("parent={$comment_parent}");

	$arr_authors = array();
	foreach( $comments as $comment ){

		$author = get_user_by('email', $comment->comment_author_email );

		if( ! $author ) continue;
		if( in_array( $author->ID, $arr_authors ) ) continue;
		if( $author->ID == $user_id ) continue;

		$arr_authors[ ] = $author->ID;
		um_fetch_user( $author->ID  );

		$url = UM()->Activity_API()->api()->get_permalink( $post_id );

		$vars['notification_uri'] = $url;

		UM()->Notifications_API()->api()->store_notification( $author->ID , 'comment_reply', $vars );
	}

	if( !in_array( $parent_author->ID, $arr_authors ) && $parent_author->ID != $user_id  ){

		um_fetch_user( $parent_author->ID  );

		$url = UM()->Activity_API()->api()->get_permalink( $post_id );

		$vars['notification_uri'] = $url;

		UM()->Notifications_API()->api()->store_notification( $parent_author->ID , 'comment_reply', $vars );
	}

}

/***
 ***	@send a web notification after new post like
 ***/
add_action('um_activity_after_wall_post_liked','um_notification_activity_likepost', 90, 2 );
function um_notification_activity_likepost( $post_id, $user_id ) {

	$author = UM()->Activity_API()->api()->get_author( $post_id );
	if ( $author == $user_id ) return false;

	um_fetch_user( $user_id );

	$vars['photo'] = um_get_avatar_url( get_avatar( $user_id, 80 ) );
	$vars['member'] = um_user('display_name');

	um_fetch_user( $author );

	$url = UM()->Activity_API()->api()->get_permalink( $post_id );

	$vars['notification_uri'] = $url;

	UM()->Notifications_API()->api()->store_notification( $author, 'new_post_like', $vars );
}

/***
 ***	@send a web notification after new post
 ***/
add_action('um_activity_after_wall_post_published','um_notification_activity_post_published', 90, 3 );
function um_notification_activity_post_published( $post_id, $writer, $wall ) {
	if ( $writer == $wall ) return false;

	um_fetch_user( $writer );

	$vars['photo'] = um_get_avatar_url( get_avatar( $writer, 80 ) );
	$vars['member'] = um_user('display_name');

	um_fetch_user( $wall );

	$url = UM()->Activity_API()->api()->get_permalink( $post_id );

	$vars['notification_uri'] = $url;

	UM()->Notifications_API()->api()->store_notification( $wall, 'new_wall_post', $vars );
}


/**
 * Send a web notification after user account is verified
 *
 * @param $user_id
 */
function um_notification_after_user_is_verified( $user_id ) {
	um_fetch_user( $user_id );

	$vars['photo'] = um_get_avatar_url( get_avatar( $user_id, 40 ) );
	$vars['member'] = um_user( 'display_name' );
	$url = um_user_profile_url();
	$vars['notification_uri'] = $url;

	UM()->Notifications_API()->api()->store_notification( $user_id, 'account_verified', $vars );
}
add_action( 'um_after_user_is_verified', 'um_notification_after_user_is_verified' );


/**
 * Send a web notification after user's job is approved
 *
 * @param int $job_id
 * @param \WP_Post $job
 */
function um_notification_after_job_is_approved( $job_id, $job ) {
	$user_id = $job->post_author;
	um_fetch_user( $user_id );

	$vars['photo'] = um_get_avatar_url( get_avatar( $user_id, 40 ) );
	$vars['member'] = um_user( 'display_name' );
	$url = um_user_profile_url();
	$vars['notification_uri'] = $url;
	$vars['job_uri'] = get_permalink( $job );

	UM()->Notifications_API()->api()->store_notification( $user_id, 'jb_job_approved', $vars );
}
add_action( 'jb_job_is_approved', 'um_notification_after_job_is_approved', 10, 2 );


/**
 * Send a web notification after user's job is expired
 *
 * @param $job_id
 */
function um_notification_after_job_is_expired( $job_id ) {
	$job = get_post( $job_id );

	if ( ! empty( $job ) && ! is_wp_error( $job ) ) {
		$user_id = $job->post_author;
		um_fetch_user( $user_id );

		$vars['photo'] = um_get_avatar_url( get_avatar( $user_id, 40 ) );
		$vars['member'] = um_user( 'display_name' );
		$url = um_user_profile_url();
		$vars['notification_uri'] = $url;
		$vars['job_uri'] = get_permalink( $job );

		UM()->Notifications_API()->api()->store_notification( $user_id, 'jb_job_expired', $vars );
	}
}
add_action( 'jb_job_is_expired', 'um_notification_after_job_is_expired', 10, 1 );