<?php
/**
 * 这里定义了一些Ajax接口
 */

use function LitePress\Cravatar\Inc\handle_email_delete;

add_action( 'wp_ajax_delete_email', function () {
	$email   = sanitize_email( $_POST['email'] );
	$user_id = (int) $_POST['user_id'];

	check_ajax_referer( 'delete-email-' . $email );

	$r = handle_email_delete( $user_id, $email );
	if ( is_wp_error( $r ) ) {
		wp_send_json_error( $r );
	} else {
		wp_send_json_success( array( 'message' => '已成功删除' . $email ) );
	}

	wp_die();
} );
