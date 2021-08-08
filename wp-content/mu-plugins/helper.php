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

use WP_Error;
use WP_Http;

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

/**
 * 从ES中检索一个产品
 *
 * @param string $slug 产品Slug
 * @param string $type 产品类型
 * @param array $fields 要输出的字段
 */

function get_product_from_es( string $slug, string $type, array $fields = array() ) {
	$body = array(
		'query' => array(
			'bool' => array(
				'must' => array(
					array(
						'term' => array(
							'terms.product_cat.slug' => "{$type}s"
						),
					),
					array(
						'term' => array(
							'slug.keyword' => $slug
						),
					),
				),
			),
		),
		'size'  => 10,
	);
	$body = wp_json_encode( $body );

	$request = wp_remote_post(
		'http://localhost:9200/litepresscnstore-post-3/_search' . ( empty( $fields ) ? '' : ( '?_source_includes=' . join( ',', $fields ) ) ),
		[
			'timeout' => 10,
			'headers' => array(
				'Content-Type' => 'application/json',
			),
			'body'    => $body,
		]
	);

	if ( is_wp_error( $request ) ) {
		return $request;
	}

	if ( WP_Http::OK !== wp_remote_retrieve_response_code( $request ) ) {
		return new WP_Error( 'response_code_not_ok' );
	}

	$body   = wp_remote_retrieve_body( $request );
	$result = json_decode( $body, true );

	return $result;
}

if ( isset( $_GET['ddddd'] ) ) {
	echo json_encode(get_product_from_es('zippy', 'plugin', ));
	exit;
}
