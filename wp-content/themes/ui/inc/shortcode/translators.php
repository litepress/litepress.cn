<?php

add_shortcode( 'translators', 'wcy_get_translators' );

/**
 * 译者名单
 */
function wcy_get_translators() {
	$html = '<ul class="translator-list">';

	global $wpdb;

	/**
	 * 获取总榜数据
	 */
	$translators_all = wp_cache_get( 'translators-all', 'litepress-cn' );
	if ( empty( $translators_all ) ) {
		$sql = <<<SQL
select user_id, count(*) as count
from wp_4_gp_translations
where user_id != 517
  and user_id != 0
group by user_id
order by count desc
limit 10;
SQL;

		$translators_all = $wpdb->get_results( $sql );
		wp_cache_set( 'translators-all', $translators_all, 'litepress-cn', 86400 );
	}

	/**
	 * 获取周榜数据
	 */
	$translators_week = wp_cache_get( 'translators-week', 'litepress-cn' );
	if ( empty( $translators_week ) ) {
		$sql = <<<SQL
select user_id, count(*) as count
from wp_4_gp_translations
where user_id != 517
  and user_id != 0
and DATE_SUB(CURDATE(), INTERVAL 7 DAY) <= date(date_added)
group by user_id
order by count desc
limit 10;
SQL;

		$translators_week = $wpdb->get_results( $sql );
		wp_cache_set( 'translators-week', $translators_week, 'litepress-cn', 86400 );
	}

	/**
	 * 输出周榜
	 */
	$html .= '周榜';
	foreach ( $translators_week as $k => $v ) {
		$user_info = get_user_by( 'id', $v->user_id );
		if ( $user_info ) {
			$html .= sprintf( '<li><em>%d.</em> <div class="rank-list__name"><a href="/user/%s?profiletab=translate">%s%s</a></div><span class="rank-list__number">%d 条</span></li>', $k + 1, $user_info->data->user_login, get_avatar( $user_info->data->user_email, 32 ), $user_info->data->display_name, $v->count );
		}
	}

	/**
	 * 输出总榜
	 */
	$html .= '总榜';
	foreach ( $translators_all as $k => $v ) {
		$user_info = get_user_by( 'id', $v->user_id );
		if ( $user_info ) {
			$html .= sprintf( '<li><em>%d.</em> <div class="rank-list__name"><a href="/user/%s?profiletab=translate">%s%s</a></div><span class="rank-list__number">%d 条</span></li>', $k + 1, $user_info->data->user_login, get_avatar( $user_info->data->user_email, 32 ), $user_info->data->display_name, $v->count );
		}
	}

	return $html .= '</ul>';
}