<?php

namespace LitePress\GlotPress\GP_Import_From_WP_Org;

use WP_Error;
use function LitePress\WP_Http\wp_remote_get;

/**
 * 基类
 */
class Base {

	const PLUGIN = 'plugin';

	const THEME = 'theme';

	public static function get_web_page_contents( $url ) {
		$response = wp_remote_get( $url, array(
			'timeout'   => 60,
			'sslverify' => false
		) );
		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$status_code = wp_remote_retrieve_response_code( $response );
		if ( 200 !== $status_code ) {
			return new WP_Error( 'http_request_error', '抓取项目失败，返回状态码：' . $status_code );
		}

		return $response['body'] ?? false;
	}

	public static function create_project( string $name, string $slug, string $type, int $parent_project_id = 0, string $parent_project_slug = '' ): int {
		global $wpdb;

		$type_for_int        = 'plugin' === $type ? 1 : 2;
		$type_slug           = self::PLUGIN === $type ? 'plugins' : 'themes';
		$parent_project_slug = empty( $parent_project_slug ) ? '' : $parent_project_slug . '/';

		$res = $wpdb->insert( 'wp_4_gp_projects', array(
			'name'                => $name,
			'author'              => '',
			'slug'                => $slug,
			'path'                => sprintf( '%s/%s%s', $type_slug, $parent_project_slug, $slug ),
			'description'         => '',
			'source_url_template' => '',
			'parent_project_id'   => 0 === $parent_project_id ? $type_for_int : $parent_project_id,
			'active'              => 1
		) );

		$project_id = $wpdb->insert_id;

		if ( 0 !== (int) $res && 0 !== $parent_project_id ) {
			$wpdb->insert( 'wp_4_gp_translation_sets', array(
				'name'       => '简体中文',
				'slug'       => 'default',
				'project_id' => $project_id,
				'locale'     => 'zh-cn'
			) );
		}

		return $project_id;
	}

}