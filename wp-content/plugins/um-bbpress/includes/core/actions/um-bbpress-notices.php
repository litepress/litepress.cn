<?php if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Frontend notice If user can not post
 */
function um_bbpress_cant_post_notice() {
	$user_id = get_current_user_id();

	if ( ! $user_id ) {
		return;
	}

	wp_enqueue_style( 'um_bbpress' );

	$check_doing = true;
	$_um_bbpress_can_topic = get_post_meta( get_the_ID(), '_um_bbpress_can_topic', true );
	if ( empty( $_um_bbpress_can_topic ) ) {
		if ( UM()->roles()->um_user_can( 'can_create_topics' ) ) {
			$check_doing = false;
		}
	} else {
		$current_user_roles = um_user( 'roles' );
		if ( ! empty( $current_user_roles ) && count( array_intersect( $current_user_roles, $_um_bbpress_can_topic ) ) > 0 ) {
			$check_doing = false;
		}
	}

	if ( ! UM()->bbPress_API()->can_do_topic() ) {
		$user_lock_notice = um_user( 'lock_notice' );
		if ( ! empty( $user_lock_notice ) ) { ?>
			<div class="um-clear"></div>
			<div class="um-bbpress-warning"><?php echo um_user( 'lock_notice' ); ?></div>
		<?php }
	} else {
		$user_lock_notice2 = um_user( 'lock_notice2' );
		if ( $check_doing && ! empty( $user_lock_notice2 ) ) { ?>
			<div class="um-clear"></div>
			<div class="um-bbpress-warning"><?php echo um_user( 'lock_notice2' ); ?></div>
		<?php }
	}
}

add_action( 'bbp_template_before_forums_loop', 'um_bbpress_cant_post_notice', 99 );
add_action( 'bbp_template_before_topics_loop', 'um_bbpress_cant_post_notice', 99 );