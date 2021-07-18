<?php
/**
 * Plugin Name: 从WordPress.org导入翻译项目
 * Description: 该插件的主要功能由Cron触发，用以自动从WordPress.org拉取项目，若项目不存在则新建。
 * Version: 1.0
 * Author: LitePress社区团队
 * Author URI: http://litepress.cn
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace LitePress\GlotPress\GP_Import_From_WP_Org;

use GP;
use WP_Error;

class GP_Import_From_WP_Org {

	const PLUGIN = 'plugin';

	const THEME = 'theme';

	/**
	 * @param string $slug
	 * @param int $type 是插件 Or 主题？
	 *
	 * @return false
	 */
	public static function handle( string $slug, string $type = self::PLUGIN ): bool {
		// 如果项目不存在于translate.wordpress.org中则跳过执行
		$wporg_url = sprintf( 'http://translate.wordpress.org/locale/zh-cn/default/wp-%ss/%s/', $type, $slug );
		$data      = self::get_web_page_contents( $wporg_url );
		if ( is_wp_error( $data ) || false === $data ) {
			return false;
		}

		if ( self::PLUGIN === $type ) {
			$sub_projects = self::get_plugin_sub_project( $slug );
		} else {
			$sub_projects = self::get_theme_sub_project( $slug );
		}

		if ( is_wp_error( $sub_projects ) ) {
			self::error_log( $slug, '获取子项目详情失败：' . $sub_projects->get_error_message() );

			return false;
		}

		foreach ( $sub_projects as $sub_project ) {
			// 因为每个项目都只有一个中文翻译集，所以这里直接按项目ID搜索
			$translation_set = GP::$translation_set->find_one( array( 'project_id' => $sub_project->id ) );

			if ( self::PLUGIN === $type ) {
				if ( 'body' === $sub_project->slug ) {
					$wporg_project_slug = 'stable';
				} elseif ( 'readme' === $sub_project->slug ) {
					$wporg_project_slug = 'stable-readme';
				} else {
					self::error_log( $sub_project->id, '插件子项目slug错误（非body亦非readme）' );

					continue;
				}

				$wporg_url = sprintf( 'http://translate.wordpress.org/projects/wp-%ss/%s/%s/zh-cn/default/export-translations/', $type, $slug, $wporg_project_slug );

				$data = self::get_web_page_contents( $wporg_url );
				if ( is_wp_error( $data ) ) {
					// 插件第一次请求失败时尝试抓取trunk翻译
					$wporg_project_slug = str_replace( 'stable', 'dev', $wporg_project_slug );
					$wporg_url          = sprintf( 'http://translate.wordpress.org/projects/wp-%ss/%s/%s/zh-cn/default/export-translations/', $type, $slug, $wporg_project_slug );

					$data = self::get_web_page_contents( $wporg_url );
				}
			} else {
				$wporg_url = sprintf( 'http://translate.wordpress.org/projects/wp-%ss/%s/zh-cn/default/export-translations/', $type, $slug );

				$data = self::get_web_page_contents( $wporg_url );
			}
			if ( is_wp_error( $data ) ) {
				self::error_log( $sub_project->id, $data->get_error_message() );

				continue;
			}

			if ( false !== $data && ! empty( $data ) ) {
				$temp_file = tempnam( sys_get_temp_dir(), 'GPI' );

				if ( false !== file_put_contents( $temp_file, $data ) ) {
					$format = gp_get_import_file_format( 'po', '' );

					$originals = $format->read_originals_from_file( $temp_file, $sub_project );
					if ( ! $originals ) {
						self::error_log( $sub_project->id, '无法从文件加载原文' );

						continue;
					}
					GP::$original->import_for_project( $sub_project, $originals );

					$translations = $format->read_translations_from_file( $temp_file, $sub_project );
					if ( ! $translations ) {
						unlink( $temp_file );

						self::error_log( $sub_project->id, '无法从文件加载翻译' );

						continue;
					}

					// 将翻译创建者统一设置为 超级 AI (用户编号 517)
					wp_set_current_user( 517 );

					$translation_set->import( $translations );

					unlink( $temp_file );
				}
			} else {
				self::error_log( $sub_project->id, '翻译下载出错' );
			}
		}

		return true;
	}

	private static function get_web_page_contents( $url ) {
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

	private static function get_plugin_sub_project( $slug ): array|WP_Error {
		$type         = self::PLUGIN;
		$type_for_int = 1;

		$project = GP::$project->find_one( array(
			'slug'              => $slug,
			'parent_project_id' => $type_for_int,
		) );

		$project_info = self::get_project_info_by_store( $slug, $type );

		/**
		 * 如果项目不存在则创建之
		 */
		if ( empty( $project ) ) {
			if ( empty( $project_info ) ) {
				return new WP_Error( 'error', '从应用市场获取项目详情失败' );
			}

			$master_project = self::create_project(
				$project_info['name'],
				$slug,
				$type,
			);
			if ( empty( $master_project ) ) {
				return new WP_Error( 'error', '创建主项目失败' );
			}

			$body_id = self::create_project(
				'程序主体',
				'body',
				$type,
				$master_project,
				$slug,
			);
			if ( empty( $body_id ) ) {
				return new WP_Error( 'error', '创建body项目失败' );
			}

			$readme_id = self::create_project(
				'自述文件',
				'readme',
				$type,
				$master_project,
				$slug,
			);
			if ( empty( $readme_id ) ) {
				return new WP_Error( 'error', '创建readme项目失败' );
			}

			$project = GP::$project->find_one( array(
				'slug'              => $slug,
				'parent_project_id' => $type_for_int,
			) );
			if ( empty( $project ) ) {
				return new WP_Error( 'error', '执行完项目创建流程后依然无法获取项目详情' );
			}
		}

		/**
		 * 对于项目详情和版本号，这俩基本每次需要获取的时候都会变更，所以在这里直接更新
		 */
		$r = self::update_project( (int) $project->id, (string) $project_info['name'], (string) $project_info['description'], (string) $project_info['version'] );
		if ( false === $r ) {
			return new WP_Error( 'error', '无法更新项目版本号' );
		}

		$body = GP::$project->find_one( array(
			'slug'              => 'body',
			'parent_project_id' => $project->id,
		) );
		if ( empty( $body ) ) {
			return new WP_Error( 'error', '无法获取body项目详情' );
		}

		$readme = GP::$project->find_one( array(
			'slug'              => 'readme',
			'parent_project_id' => $project->id,
		) );
		if ( empty( $readme ) ) {
			return new WP_Error( 'error', '无法获取readme项目详情' );
		}

		return array(
			'body'   => $body,
			'readme' => $readme,
		);
	}

	private static function get_project_info_by_store( string $slug, string $type ): array {
		global $wpdb;

		$sql = $wpdb->prepare( "SELECT * FROM lp_api_projects WHERE slug=%s AND type=%s;", $slug, $type );
		$r   = $wpdb->get_row( $sql );
		if ( empty( $r ) ) {
			return array();
		}

		if ( self::PLUGIN === $type ) {
			$sql     = $wpdb->prepare( "SELECT post_excerpt FROM wp_3_posts WHERE ID=%d;", $r->product_id );
			$product = $wpdb->get_row( $sql );
		} else {
			$sql                   = $wpdb->prepare( "SELECT meta_value FROM wp_3_postmeta WHERE meta_key='51_default_editor' AND post_id=%d;", $r->product_id );
			$product               = $wpdb->get_row( $sql );
			$product->post_excerpt = $product->meta_value;
		}

		return array(
			'name'        => $r->name,
			'version'     => $r->version,
			'description' => $product->post_excerpt,
		);
	}

	private static function create_project( string $name, string $slug, string $type, int $parent_project_id = 0, string $parent_project_slug = '' ): int {
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

	private static function update_project( string $project_id, string $name, string $description, string $version ): bool {
		GP::$project->update( array(
			'name'        => $name,
			'description' => $description,
		), array(
			'id' => $project_id,
		) );

		return gp_update_meta( $project_id, 'version', $version, 'project' );
	}

	private static function get_theme_sub_project( $slug ): array|WP_Error {
		$type         = self::THEME;
		$type_for_int = 2;

		$project = GP::$project->find_one( array(
			'slug'              => $slug,
			'parent_project_id' => $type_for_int,
		) );

		$project_info = self::get_project_info_by_store( $slug, $type );

		/**
		 * 如果项目不存在则创建之
		 */
		if ( empty( $project ) ) {
			if ( empty( $project_info ) ) {
				return new WP_Error( 'error', '从应用市场获取项目详情失败' );
			}

			$master_project = self::create_project(
				$project_info['name'],
				$slug,
				$type,
			);
			if ( empty( $master_project ) ) {
				return new WP_Error( 'error', '创建主项目失败' );
			}

			$body_id = self::create_project(
				$project_info['name'],
				$slug,
				$type,
				$master_project,
				$slug,
			);
			if ( empty( $body_id ) ) {
				return new WP_Error( 'error', '创建主题子项目失败' );
			}

			$project = GP::$project->find_one( array(
				'slug'              => $slug,
				'parent_project_id' => $type_for_int,
			) );
			if ( empty( $project ) ) {
				return new WP_Error( 'error', '执行完项目创建流程后依然无法获取项目详情' );
			}
		}

		/**
		 * 对于项目详情和版本号，这俩基本每次需要获取的时候都会变更，所以在这里直接更新
		 */
		$r = self::update_project( (int) $project->id, (string) $project_info['name'], (string) $project_info['description'], (string) $project_info['version'] );
		if ( false === $r ) {
			return new WP_Error( 'error', '无法更新项目版本号' );
		}

		$body = GP::$project->find_one( array(
			'slug'              => $slug,
			'parent_project_id' => $project->id,
		) );
		if ( empty( $body ) ) {
			return new WP_Error( 'error', '无法获取主题子项目详情' );
		}

		return array(
			$slug => $body
		);
	}

	/**
	 * @param int|string $project 可以在项目ID或项目slug
	 * @param string $message
	 */
	private static function error_log( int|string $project, string $message ) {
		global $wpdb;

		if ( is_int( $project ) ) {
			$wpdb->insert( 'wp_4_gp_import_error_log', array(
				'project_id' => $project,
				'message'    => $message,
			) );
		} else {
			$wpdb->insert( 'wp_4_gp_import_error_log', array(
				'slug'    => $project,
				'message' => $message,
			) );
		}
	}

}

add_action( 'gp_import_from_wp_org', array( GP_Import_From_WP_Org::class, 'handle' ), 10, 2 );

if ( isset( $_GET['debug'] ) ) {
	/*
	add_action( 'wp_loaded', function () {
		//do_action( 'gp_import_from_wp_org', 'astra', 'theme' );
		do_action( 'gp_import_from_wp_org', 'unlimited-elements-for-elementor', 'plugin' );
		exit;
	} );
	*/
	/*
	add_action( 'wp_loaded', function () {
		//GP_Import_From_WP_Org::handle( 'woocommerce', GP_Import_From_WP_Org::PLUGIN );
		GP_Import_From_WP_Org::handle( 'wp-super-cache', GP_Import_From_WP_Org::PLUGIN );
		var_dump( 'ss' );
		exit;
	} );
*/
}