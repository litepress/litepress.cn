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
use GP_Route;
use Translation_Entry;
use function LitePress\Helper\get_product_from_es;
use function LitePress\WP_Http\wp_remote_get;
use WP_Error;

require __DIR__ . '/class-base.php';

class GP_Import_From_WP_Org extends Base {

	/**
	 * 只有崭新的项目才会从w.org导入翻译，否则就只导入原文
	 *
	 * @var bool
	 */
	private static bool $is_new = false;

	public function __construct() {
		add_action( 'gp_import_from_wp_org', array( GP_Import_From_WP_Org::class, 'gp_wp_import' ), 10, 2 );

		add_filter( 'gp_translations_footer_links', array( $this, 'gp_translations_footer_links' ), 10, 4 );

		GP::$router->add( "/gp-wp-import/(.+?)/(.+?)", array( $this, 'gp_wp_import' ), 'get' );
		GP::$router->add( "/gp-wp-import/(.+?)/(.+?)", array( $this, 'gp_wp_import' ), 'post' );
	}

	/**
	 * @param string $slug
	 * @param string $type 是插件 Or 主题？
	 *
	 * @return false
	 */
	public static function gp_wp_import( string $slug, string $type = self::PLUGIN ): bool {
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

		$route = new GP_Route;

		if ( is_wp_error( $sub_projects ) ) {
			self::error_log( $slug, '获取子项目详情失败：' . $sub_projects->get_error_message() );

			if ( 'cli' !== PHP_SAPI ) {
				$route->redirect_with_error( '获取子项目详情失败' );
			}

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

				$wporg_url = sprintf( 'http://translate.wordpress.org/projects/wp-%ss/%s/%s/zh-cn/default/export-translations/?filters[term]&filters[term_scope]=scope_any&filters[status]=current_or_waiting_or_fuzzy_or_untranslated&filters[user_login]&format=po', $type, $slug, $wporg_project_slug );

				$data = self::get_web_page_contents( $wporg_url );
				if ( is_wp_error( $data ) ) {
					// 插件第一次请求失败时尝试抓取trunk翻译
					$wporg_project_slug = str_replace( 'stable', 'dev', $wporg_project_slug );
					$wporg_url          = sprintf( 'http://translate.wordpress.org/projects/wp-%ss/%s/%s/zh-cn/default/export-translations/?filters[term]&filters[term_scope]=scope_any&filters[status]=current_or_waiting_or_fuzzy_or_untranslated&filters[user_login]&format=po', $type, $slug, $wporg_project_slug );

					$data = self::get_web_page_contents( $wporg_url );
				}
			} else {
				$wporg_url = sprintf( 'http://translate.wordpress.org/projects/wp-%ss/%s/zh-cn/default/export-translations/?filters[term]&filters[term_scope]=scope_any&filters[status]=current_or_waiting_or_fuzzy_or_untranslated&filters[user_login]&format=po', $type, $slug );

				$data = self::get_web_page_contents( $wporg_url );
			}
			if ( is_wp_error( $data ) ) {
				self::error_log( $sub_project->id, $data->get_error_message() );

				continue;
			}

			if ( ! empty( $data ) ) {
				$temp_file = tempnam( sys_get_temp_dir(), 'GPI' );

				if ( false !== file_put_contents( $temp_file, $data ) ) {
					$format = gp_get_import_file_format( 'po', '' );

					$originals = $format->read_originals_from_file( $temp_file, $sub_project );
					if ( ! $originals ) {
						self::error_log( $sub_project->id, '无法从文件加载原文' );

						continue;
					}
					GP::$original->import_for_project( $sub_project, $originals );

					//if ( self::$is_new ) {
					$translations = $format->read_translations_from_file( $temp_file, $sub_project );
					if ( ! $translations ) {
						unlink( $temp_file );

						self::error_log( $sub_project->id, '无法从文件加载翻译' );

						continue;
					}

					// 将翻译创建者统一设置为 超级 AI (用户编号 517)
					wp_set_current_user( 517 );

					/**
					 * 禁止导入翻译字符串为空或仅仅包含一个换行符的条目，这些问题的翻译文件是又 GlotPress 的 BUG 在翻译导出时生成的
					 *
					 * @var $entry Translation_Entry
					 */
					foreach ( $translations->entries as &$entry ) {
						if ( empty( $entry->translations ) ) {
							continue;
						}

						if ( $entry->translations[0] === "\n" ) {
							$entry->translations = array();
						}
					}
					unset( $entry );

					$translation_set->import( $translations );
					//}
					unlink( $temp_file );
				}
			} else {
				self::error_log( $sub_project->id, '翻译下载出错' );

				return false;
			}
		}

		if ( 'cli' !== PHP_SAPI ) {
			$referer = gp_url_project( "{$type}s/$slug" );
			if ( isset( $_SERVER['HTTP_REFERER'] ) ) {
				$referer = $_SERVER['HTTP_REFERER'];
			}

			$route->notices[] = '已成功导入';
			$route->redirect( $referer );
		}

		return true;
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
			self::$is_new = true;

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
		} else {
			self::$is_new = false;
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

		/**
		 * 为程序主体更新代码模板URL
		 */
		self::update_source_url_template( (int) $body->id, (string) $body->path, $type, (string) $project_info['version'] );

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
		if ( self::PLUGIN === $type ) {
			$r = get_product_from_es( $slug, $type, array(
				'post_title_en',
				'meta._api_new_version.value',
				'post_excerpt_en'
			) );

			$description = $r['hits']['hits'][0]['_source']['post_excerpt_en'] ?? '';
		} else {
			$r = get_product_from_es( $slug, $type, array(
				'post_title_en',
				'meta._api_new_version.value',
				'post_content_en'
			) );

			$description = $r['hits']['hits'][0]['_source']['post_content_en'] ?? '';
		}

		if ( ! isset( $r['hits']['hits'][0] ) ) {
			return array();
		}

		return array(
			'name'        => $r['hits']['hits'][0]['_source']['post_title_en'] ?? '',
			'version'     => $r['hits']['hits'][0]['_source']['meta']['_api_new_version'][0]['value'] ?? '',
			'description' => $description,
		);
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

	/**
	 * 为项目更新上source_url_template字段
	 *
	 * 这个函数通常给只为承载程序主体翻译的项目调用
	 *
	 * @param int $project_id
	 * @param string $path
	 * @param string $type
	 * @param string $version
	 *
	 * @return bool
	 */
	private static function update_source_url_template( int $project_id, string $path, string $type, string $version = '' ): bool {
		if ( self::PLUGIN === $type ) {
			$source_url_template = str_replace( '/body', '/trunk/%file%#L%line%', $path );
		} elseif ( self::THEME === $type ) {
			$items = explode( '/', $path );
			$slug  = $items[ count( $items ) - 1 ];

			$source_url_template = str_replace( "/$slug/$slug", "/$slug/$version/%file%#L%line%", $path );
		} else {
			$source_url_template = '';
		}

		GP::$project->update( array(
			'source_url_template' => '/svn/' . $source_url_template,
		), array(
			'id' => $project_id,
		) );

		return true;
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

		/**
		 * 为程序主体更新代码模板URL
		 */
		self::update_source_url_template( (int) $body->id, (string) $body->path, $type, (string) $project_info['version'] );

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

	public function after_request() {
	}

	public function before_request() {
	}

	public function gp_translations_footer_links( $footer_links, $project, $locale, $translation_set ) {
		$type = str_starts_with( $project->path, 'plugins/' ) ? 'plugin' : false;
		$type = str_starts_with( $project->path, 'themes/' ) && false === $type ? 'theme' : $type;
		preg_match( "/{$type}s\/(.+)\//", $project->path, $match );

		if ( is_user_logged_in() && $type && isset( $match[1] ) && ! empty( $match[1] ) ) {
			$footer_links[] = gp_link_get( gp_url( "/gp-wp-import/{$match[1]}/$type" ), '从 wordpress.org 导入原文和翻译' );
		}

		return $footer_links;
	}

}

new GP_Import_From_WP_Org();


// 加载命令行
if ( class_exists( 'WP_CLI' ) ) {
	require __DIR__ . '/import-release.php';
}


if ( isset( $_GET['debug-import'] ) ) {
	/*
	add_action( 'wp_loaded', function () {
		//do_action( 'gp_import_from_wp_org', 'astra', 'theme' );
		do_action( 'gp_import_from_wp_org', 'unlimited-elements-for-elementor', 'plugin' );
		exit;
	} );
	*/

	add_action( 'wp_loaded', function () {
		//GP_Import_From_WP_Org::handle( 'woocommerce', GP_Import_From_WP_Org::PLUGIN );
		GP_Import_From_WP_Org::gp_wp_import( 'yoco-payment-gateway', GP_Import_From_WP_Org::PLUGIN );
		var_dump( 'ss' );
		exit;
	} );

	/*
		$body = array(
			"query" => "select * from translate_memory where target='\n\n'",
		);
		$body = wp_json_encode( $body );

		$request = wp_remote_post(
			'http://10.88.0.1:9200/_sql?format=json',
			[
				'timeout' => 10,
				'headers' => array(
					'Content-Type' => 'application/json',
				),
				'body'    => $body,
			]
		);

		var_dump($request['body']);
		exit;
	*/
}
