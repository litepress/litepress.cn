<?php

add_shortcode( 'translators', 'wcy_get_translators' );

/**
 * 译者名单
 */
function wcy_get_translators() {
	$html = '<ul class="translator-list">';

	global $wpdb;

	$translators = wp_cache_get( 'translators', 'litepress-cn' );
	if ( empty( $translators ) ) {
		$translators = $wpdb->get_results( 'select user_id, count(*) as count from wp_4_gp_translations where user_id!=517 group by user_id order by count desc;' );
		wp_cache_set( 'translators', $translators, 'litepress-cn', 86400 );
	}

	$i = 0;
	foreach ( $translators as $k => $v ) {
		if ( $i >= 10 ) {
			break;
		}
		$user_info = get_user_by( 'id', $v->user_id );
		if ( $user_info) {
			$html .= sprintf( '<li><em>%d.</em> <div class="rank-list__name"><a href="/user/%s?profiletab=translate">%s%s</a></div><span class="rank-list__number">%d 条</span></li>', $k + 1, $user_info->data->user_login, get_avatar( $user_info->data->user_email, 32 ), $user_info->data->display_name, $v->count );
		}

		$i ++;
	}

	return $html .= '</ul>';
}