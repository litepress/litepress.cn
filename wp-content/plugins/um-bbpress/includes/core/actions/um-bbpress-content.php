<?php if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Hook in replies
 */
function um_bbpress_theme_after_reply_author_details() {
	wp_enqueue_style( 'um_bbpress' );

	do_action( 'um_bbpress_theme_after_reply_author_details' );
}
add_action( 'bbp_theme_after_reply_author_details', 'um_bbpress_theme_after_reply_author_details' );


/**
 * Default tab
 *
 * @param $args
 */
function um_bbpress_default_tab_content( $args ) {
	wp_enqueue_style( 'um_bbpress' );

	//$tabs = UM()->user()->tabs;
	$tabs = UM()->profile()->tabs_active();

	$default_tab = $tabs['forums']['subnav_default'];

	do_action( "um_profile_content_forums_{$default_tab}", $args );
}
add_action( 'um_profile_content_forums_default', 'um_bbpress_default_tab_content', 10, 1 );


/**
 * Topics
 *
 * @param $args
 */
function um_bbpress_user_topics( $args ) {
	if ( ! um_user( 'can_create_topics' ) ) {
		return;
	}

	wp_enqueue_style( 'um_bbpress' );
	
	$loop = UM()->query()->make( 'post_status=closed,publish&post_type=topic&posts_per_page=10&offset=0&author=' . um_user( 'ID' ) );

	$t_args = compact( 'args', 'loop' );
	UM()->get_template( 'topics.php', um_bbpress_plugin, $t_args, true );
}
add_action( 'um_profile_content_forums_topics', 'um_bbpress_user_topics', 10, 1 );


/**
 * Replies
 *
 * @param $args
 */
function um_bbpress_user_replies( $args ) {
	if ( ! um_user('can_create_replies') ) {
		return;
	}

	wp_enqueue_style( 'um_bbpress' );
	
	$loop = UM()->query()->make( 'post_type=reply&posts_per_page=10&offset=0&author=' . um_user( 'ID' ) );

	$t_args = compact( 'args', 'loop' );
	UM()->get_template( 'replies.php', um_bbpress_plugin, $t_args, true );
}
add_action( 'um_profile_content_forums_replies', 'um_bbpress_user_replies', 10, 1 );


/**
 * Favorites
 *
 * @param $args
 */
function um_bbpress_user_favorites( $args ) {
	wp_enqueue_style( 'um_bbpress' );

	$topics = bbp_get_user_favorites_topic_ids( um_user( 'ID' ) );
	if( !$topics ) {
		$topics = array(
				'abc' );
	}

	$loop = UM()->query()->make( array(
			'post_type'		 => 'topic',
			'post__in'		 => $topics,
			'post_status'	 => array( 'publish', 'closed' ) ) );

	$t_args = compact( 'args', 'loop', 'topics' );
	UM()->get_template( 'favorites.php', um_bbpress_plugin, $t_args, true );
}
add_action( 'um_profile_content_forums_favorites', 'um_bbpress_user_favorites', 10, 1 );


/**
 * Subscriptions
 *
 * @param $args
 */
function um_bbpress_user_subscriptions( $args ) {
	if ( ! UM()->roles()->um_current_user_can( 'edit', um_user( 'ID' ) ) ) {
		return;
	}

	wp_enqueue_style('um_bbpress');

	$subscribed_topics = bbp_get_user_subscribed_topic_ids( um_user( 'ID' ) );
	$subscribed_forums = bbp_get_user_subscribed_forum_ids( um_user( 'ID' ) );

	$subscribed = array_merge( $subscribed_topics, $subscribed_forums );
	if( !$subscribed ) {
		$subscribed = array();
	}

	$loop = UM()->query()->make( array(
			'post_type'	 => array( 'forum', 'topic' ),
			'post__in'	 => $subscribed,
			'orderby'		 => 'date',
			'order'			 => 'DESC' ) );

	$t_args = compact( 'args', 'loop', 'subscribed', 'subscribed_forums', 'subscribed_topics' );
	UM()->get_template( 'subscriptions.php', um_bbpress_plugin, $t_args, true );
}
add_action( 'um_profile_content_forums_subscriptions', 'um_bbpress_user_subscriptions', 10, 1 );


/**
 * @param $reply_id
 * @param $topic_id
 * @param $forum_id
 * @param $anonymous_data
 * @param $reply_author_id
 */
function um_bbp_new_reply( $reply_id, $topic_id, $forum_id, $anonymous_data, $reply_author_id ) {
	wp_enqueue_style( 'um_bbpress' );

	do_action( 'um_bbpress_new_reply', $reply_id, $topic_id, $forum_id, $anonymous_data, $reply_author_id );
}
add_action( 'bbp_new_reply', 'um_bbp_new_reply', 1000, 5 );