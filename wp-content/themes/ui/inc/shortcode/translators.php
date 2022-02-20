<?php

add_shortcode( 'translators', 'wcy_get_translators' );

/**
 * 译者名单
 */
function wcy_get_translators() {
	$html = '
<ul class="nav nav-tabs" id="translator-list-Tab" role="tablist">
  <li class="nav-item" role="presentation">
    <button class="nav-link active" id="Weekly-list-tab" data-bs-toggle="tab" data-bs-target="#Weekly-list" type="button" role="tab" aria-controls="Weekly-list" aria-selected="true">周榜</button>
  </li>
  <li class="nav-item" role="presentation">
    <button class="nav-link" id="Overall-list-tab" data-bs-toggle="tab" data-bs-target="#Overall-list" type="button" role="tab" aria-controls="Overall-list" aria-selected="false">总榜</button>
  </li>
</ul>
<div class="tab-content">';

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
	$html .= '<div class="tab-pane translator-list active" id="Weekly-list" role="tabpanel" aria-labelledby="Weekly-list-tab">';
	foreach ( $translators_week as $k => $v ) {
		$user_info = get_user_by( 'id', $v->user_id );
		if ( $user_info ) {
			$html .= sprintf( '<li><em>%d.</em> <div class="rank-list__name"><a href="/user/%s?profiletab=translate">%s%s</a></div><span class="rank-list__number">%d 条</span></li>', $k + 1, $user_info->data->user_login, get_avatar( $user_info->data->user_email, 32 ), $user_info->data->display_name, $v->count );
		}
	}
    $html .= '</div>';
	/**
	 * 输出总榜
	 */
	$html .= '<div class="tab-pane translator-list" id="Overall-list" role="tabpanel" aria-labelledby="Overall-list-tab">';
	foreach ( $translators_all as $k => $v ) {
		$user_info = get_user_by( 'id', $v->user_id );
		if ( $user_info ) {
			$html .= sprintf( '<li><em>%d.</em> <div class="rank-list__name"><a href="/user/%s?profiletab=translate">%s%s</a></div><span class="rank-list__number">%d 条</span></li>', $k + 1, $user_info->data->user_login, get_avatar( $user_info->data->user_email, 32 ), $user_info->data->display_name, $v->count );
		}
	}
   $html .= '</div>';

	return $html .= '</ul></div>';
}