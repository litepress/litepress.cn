<?php if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Adds a main tab to display forum activity in profile
 *
 * @param $tabs
 *
 * @return mixed
 */
function um_bbpress_add_tab( $tabs ) {
	$tabs['forums'] = array(
		'name'  => __( 'Forums', 'um-bbpress' ),
		'icon'  => 'um-faicon-comments',
	);

	return $tabs;
}
add_filter( 'um_profile_tabs', 'um_bbpress_add_tab', 1000 );


/**
 * Add tabs based on user
 *
 * @param $tabs
 *
 * @return mixed
 */
function um_bbpress_user_add_tab( $tabs ) {
	if ( empty( $tabs['forums'] ) ) {
		return $tabs;
	}

	$user_id = um_user( 'ID' );
	if ( ! $user_id ) {
		return $tabs;
	}

	$topic_started = UM()->query()->make('post_status=closed,publish&post_type=topic&posts_per_page=-1&offset=0&author=' . $user_id );

	if ( isset( $topic_started->post_count ) ) {
		$topic_started = $topic_started->post_count;
	} else {
		$topic_started = 0;
	}

	$tabs['forums']['subnav'] = array(
		'topics'        => __( 'Topics Started', 'um-bbpress' ) . '<span>' . $topic_started . '</span>',
		'replies'       => __( 'Replies Created', 'um-bbpress' ) . '<span>' . bbp_get_user_reply_count_raw( $user_id ) . '</span>',
		'favorites'     => __( 'Favorites', 'um-bbpress' ) . '<span>' . count( bbp_get_user_favorites_topic_ids( $user_id ) ) . '</span>',
		'subscriptions' => __( 'Subscriptions', 'um-bbpress' ) . '<span>' . UM()->bbPress_API()->user_subscriptions_count( $user_id ) . '</span>',
	);
	$tabs['forums']['subnav_default'] = 'topics';

	if ( ! um_user( 'can_have_forums_tab' ) ) {
		unset( $tabs['forums'] );
	}

	if ( ! bbp_is_subscriptions_active() ) {
		unset( $tabs['forums']['subnav']['subscriptions'] );
	}

	if ( ! bbp_is_favorites_active() ) {
		unset( $tabs['forums']['subnav']['favorites'] );
	}

	if ( ! um_is_myprofile() && ! UM()->roles()->um_current_user_can( 'edit', $user_id ) ) {
		unset( $tabs['forums']['subnav']['subscriptions'] );
	}

	if ( ! um_user( 'can_create_topics' ) ) {
		unset( $tabs['forums']['subnav']['topics'] );
	}

	if ( ! um_user( 'can_create_replies' ) ) {
		unset( $tabs['forums']['subnav']['replies'] );
	}

	if ( isset( $tabs['forums'] ) && ! isset( $tabs['forums']['subnav'][ $tabs['forums']['subnav_default'] ] ) ) {
		$i = 0;
		if ( isset( $tabs['forums']['subnav'] ) ) {
			foreach ( $tabs['forums']['subnav'] as $id => $data ) {
				$i++;
				if ( $i == 1 ) {
					$tabs['forums']['subnav_default'] = $id;
				}
			}
		}
	}

	return $tabs;
}
add_filter( 'um_user_profile_tabs', 'um_bbpress_user_add_tab', 1000, 1 );