<?php if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Admin metabox - keys that have to be reset
 *
 * @param $array
 *
 * @return array
 */
function um_bbpress_multi_choice_keys( $array ) {
	$array[] = '_um_bbpress_can_topic';
	$array[] = '_um_bbpress_can_reply';
	return $array;
}
add_filter( 'um_admin_multi_choice_keys', 'um_bbpress_multi_choice_keys', 10, 1 );