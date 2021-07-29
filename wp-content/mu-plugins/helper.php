<?php
/**
 * Plugin Name: LitePress.cn的帮助函数
 * Description: 一些有用的函数
 * Version: 1.0
 * Author: LitePress社区团队
 * Author URI: http://litepress.cn
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace LitePress\Helper;

/**
 * 通过解析一组category_ids来分析当前产品的类别
 *
 * 类型包括：插件、主题、小程序、块模板
 *
 * @param array $category_ids
 *
 * @return string
 */
function get_product_type_by_category_ids( array $category_ids ): string {
	$type = '';
	foreach ( $category_ids as $category_id ) {
		if ( 15 === (int) $category_id ) {
			$type = 'plugin';
		}
		if ( 17 === (int) $category_id ) {
			$type = 'theme';
		}
	}

	return $type;
}

/**
 * 通过解析一组categories来分析当前产品的类别
 *
 * 类型包括：插件、主题、小程序、块模板
 *
 * @param array $categories
 *
 * @return string
 */
function get_product_type_by_categories( array $categories ): string {
	$category_ids = array();

	foreach ( $categories as $category ) {
		if ( is_array( $category ) ) {
			$category_ids[] = $category['term_id'];
		} else {
			$category_ids[] = $category->term_id;
		}
	}

	return get_product_type_by_category_ids( $category_ids );
}

/**
 * 检查是否存在某个GlotPress项目
 *
 * @param string $slug 项目Slug
 * @param string $type 项目类型：plugin或theme
 *
 * @return bool 存在返回true，否则返回false
 */
function exist_gp_project( string $slug, string $type ): bool {
	global $wpdb;

	$parent_project_id = match ( $type ) {
		'plugin' => 1,
		'theme' => 2,
		default => 0,
	};

	if ( 0 === $parent_project_id ) {
		return false;
	}

	$sql = $wpdb->prepare( 'SELECT id FROM wp_4_gp_projects WHERE slug = %s AND parent_project_id = %s;', $slug, $parent_project_id );

	return ! empty( $wpdb->get_row( $sql ) );
}
