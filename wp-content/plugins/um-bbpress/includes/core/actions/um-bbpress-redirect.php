<?php if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Global profile redirection
 */
function um_bbpress_profile_redirect() {
	$bbp_user_id = get_query_var( 'bbp_user_id' );
	if ( $bbp_user_id > 0 && bbp_is_single_user() ) {
		um_fetch_user( $bbp_user_id );
		$redirect = um_user_profile_url();
		exit( wp_redirect( $redirect ) );
	}
}
add_action( 'template_redirect', 'um_bbpress_profile_redirect' );


/**
 * Change bbpress profile URL
 * @param $url
 * @param $user_id
 * @param $nicename
 *
 * @return bool|string
 */
function um_bbpress_get_user_profile_url( $url, $user_id, $nicename ) {
	return um_user_profile_url( $user_id );
}
add_filter( 'bbp_get_user_profile_url', 'um_bbpress_get_user_profile_url', 10, 3 );