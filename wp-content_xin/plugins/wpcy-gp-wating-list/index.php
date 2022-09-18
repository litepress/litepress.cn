<?php
/**
 * Plugin Name: GP-Wating-List
 * Description: GlotPress的待审核项目列表
 * Author: WP-China
 * Version: 1.0.0
 * Author URI:https://wp-china.org
 * License: GPLv3 or later
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

add_shortcode( 'trans_waiting_list', 'get_trans_waiting_list' );

function get_trans_waiting_list() {
	global $wpdb;

	$waiting_list = $wpdb->get_results( 'select translation_set_id from wp_4_gp_translations as t right join wp_4_gp_originals as o on t.original_id=o.id where t.`status`="waiting" and o.`status`="+active" group by translation_set_id;' );

	/**
	 * 重新拼接出仅包含翻译集ID的数组
	 */
	$translation_set_ids = [];
	foreach ( $waiting_list as $v ) {
		$translation_set_ids = array_merge( $translation_set_ids, [ $v->translation_set_id ] );
	}

	/**
	 * 通过翻译集ID查询对应的项目路径ID
	 */
	$sql = 'select name,path from wp_4_gp_projects where ';
	foreach ( $translation_set_ids as $v ) {
		$translation_set = $wpdb->get_row( 'select project_id from wp_4_gp_translation_sets where `id`=' . $v . ';' );
		if ( $v != end( $translation_set_ids ) ) {
			$sql .= 'id=' . $translation_set->project_id . ' or ';
		} else {
			$sql .= 'id=' . $translation_set->project_id . ';';
		}
	}

	/**
	 * 查询翻译集对应的项目路径，并输出超链接列表到页面
	 */
	$projects = $wpdb->get_results( $sql );
	echo "<style>
.list-group-item {
    position: relative;
    display: block;
    padding: .75rem 1.2rem;
    background-color: #fff;
    border: 1px solid #eef2f7;
    color: var(--wp-theme-color);
}
</style>";
	echo "<section class='theme-boxshadow bg-white p-3'><ul>";

	printf( '总计：' . count( $projects ) . ' 个待审核项目' );
	echo "<ul class='list-group mt-2'>";

	foreach ( $projects as $v ) {
		printf( '<a class="list-group-item list-group-item-action" href="/translate/projects/%s/zh-cn/default/?filters[translated]=yes&filters[status]=waiting" target="_blank">%s</a>', $v->path, $v->path );
	}
	echo "</ul>";
	echo "</section>";
}

