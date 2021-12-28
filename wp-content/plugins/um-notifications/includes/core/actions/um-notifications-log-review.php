<?php
if ( ! defined( 'ABSPATH' ) ) exit;


	add_action('um_review_is_published', 'um_notification_log_review', 10, 6);
	function um_notification_log_review( $user_id, $reviewer_id, $reviewer, $reviews_url, $title, $review_id ) {
		$vars = array();

		$vars['review_excerpt'] = $title;
		$vars['notification_uri'] = $reviews_url . '#review-'. $review_id;

		// reviewer info
		um_fetch_user( $reviewer_id );
		$vars['photo'] = um_get_avatar_url( get_avatar( $reviewer_id, 40 ) );
		$vars['member'] = $reviewer;

        UM()->Notifications_API()->api()->store_notification( $user_id, 'user_review', $vars );

	}