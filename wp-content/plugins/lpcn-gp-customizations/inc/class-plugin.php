<?php

namespace LitePress\GlotPress\Customizations\Inc;

use GP;
use GP_Route;
use GP_Route_Translation;
use GP_Translation;
use LitePress\Chinese_Format\Chinese_Format;
use LitePress\GlotPress\Customizations\Inc\Routes\Index;
use LitePress\GlotPress\Customizations\Inc\Routes\Route_Project;

class Plugin {

	/**
	 * @var Plugin|null The singleton instance.
	 */
	private static ?Plugin $instance = null;

	/**
	 * Instantiates a new Plugin object.
	 */
	private function __construct() {
		add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );
	}

	/**
	 * Returns always the same instance of this plugin.
	 *
	 * @return Plugin
	 */
	public static function get_instance(): Plugin {
		if ( ! ( self::$instance instanceof Plugin ) ) {
			self::$instance = new Plugin();
		}

		return self::$instance;
	}

	/**
	 * Initializes the plugin.
	 */
	public function plugins_loaded() {
		add_action( 'gp_translation_created', array( $this, 'translation_format' ), 1 );
		add_action( 'gp_translation_saved', array( $this, 'translation_format' ), 1 );

		add_filter( 'gp_pre_can_user', array( $this, 'can_user' ), 10, 2 );

		add_action( 'template_redirect', array( $this, 'router' ), 5 );

		add_filter( 'gp_url_profile', array( $this, 'gp_url_profile' ), 10, 2 );

		add_filter( 'gp_project_actions', array( $this, 'project_actions' ), 999, 2 );

		/**
		 * 导入翻译时跳过已存在“当前翻译”的条目
		 */
		add_filter( 'gp_translation_set_import_over_existing', '__return_false' );

		/**
		 * 自定义翻译条目列表的查询
		 */
		add_filter( 'gp_for_translation_where', array( $this, 'gp_for_translation_where' ), 10, 2 );
	}

	/**
	 * 自定义 GlotPress 的路由
	 */
	public function router() {
		GP::$router->prepend( "/", array( Index::class, 'index' ) );
		GP::$router->prepend( "/projects/(plugins|themes|docs|core|others)", array( Route_Project::class, 'single' ) );
		GP::$router->prepend( "/projects/-new", array( Route_Project::class, 'new_post' ), 'post' );
		GP::$router->prepend( "/projects/(.+?)/-edit", array( Route_Project::class, 'edit_post' ), 'post' );
		GP::$router->prepend( "/projects/(.+?)/import-originals", array(
			Route_Project::class,
			'import_originals_post'
		), 'post' );
	}

	public function translation_format( GP_Translation $translation ): GP_Translation {
		global $wpdb;

		$wpdb->update( 'wp_4_gp_translations',
			array(
				'translation_0' => Chinese_Format::get_instance()->convert( (string) $translation->translation_0 )
			),
			array(
				'id' => $translation->id
			)
		);

		return $translation;
	}

	public function can_user( $none, $args ) {
		// 任何用户均可导入状态为等待中的翻译
		if ( isset( $args['user_id'] ) && ! empty( $args['user_id'] ) && 'import-waiting' === $args['action'] ) {
			return true;
		}

		// 项目管理员对项目有写权限
		if ( isset( $args['user_id'] ) && ! empty( $args['user_id'] ) && 'write' === $args['action'] && 'project' === $args['object_type'] ) {
			$can = function ( int $project_id, int $user_id ) {
				$r = GP::$permission->find( array(
					'user_id'     => $user_id,
					'action'      => 'manage',
					'object_type' => 'project|locale|set-slug',
					'object_id'   => "$project_id|zh-cn|default",
				) );

				return ! empty( $r );
			};

			$project = GP::$project->find_one( array( 'id' => $args['object_id'] ) );
			// 如果项目不存在父项目则不允许编辑
			if ( empty( $project->parent_project_id ) && '/translate/projects/' !== $_SERVER['REQUEST_URI'] && '/translate/projects/-new/' !== $_SERVER['REQUEST_URI'] ) {
				return false;
			}

			$is_can = $can( (int) $args['object_id'], (int) $args['user_id'] );
			if ( ! $is_can ) {
				// 如果对父项目有权限，则也可以操作
				$is_can = $can( (int) ( $project->parent_project_id ?? 0 ), (int) $args['user_id'] );
			}

			if ( $is_can ) {
				return true;
			}
		}

		// 未命中前方规则的权限检查转交给GlotPress继续处理
		return $none;
	}

	public function gp_url_profile( $url, $user_nicename ): string {
		return "/user/$user_nicename?profiletab=translate";
	}

	public function project_actions( $actions, $project ): array {
		$data[] = $actions[0];

		return $data;
	}

	public function gp_for_translation_where( $where, $translation_set ): array {
		// 处理翻译替换，该功能只允许对项目拥有编辑和审核权限的用户使用
		$route = new GP_Route_Translation();
		if ( ! $route->can( 'approve', 'translation-set', $translation_set->id ) ) {
			return $where;
		}

		if ( ! isset( $_GET['filter'] ) || '应用替换' !== $_GET['filter'] ) {
			if ( isset( $_GET['filters']['term_by_replace'] ) && ! empty( $_GET['filters']['term_by_replace'] ) && '应用搜索' === $_GET['filter'] ) {
				$where[0] = "((t.translation_0 LIKE '%{$_GET['filters']['term_by_replace']}%') OR (t.translation_1 LIKE '%{$_GET['filters']['term_by_replace']}%'))";
			}

			return $where;
		}

		if ( ! isset( $_GET['filters']['term_by_replace'] ) || ! isset( $_GET['filters']['replace'] ) ) {
			return $where;
		}

		$term         = sanitize_text_field( $_GET['filters']['term_by_replace'] );
		$term_replace = sanitize_text_field( $_GET['filters']['replace'] );

		// 允许替换的术语为空，但是不允许搜索的术语为空
		if ( empty( $term ) ) {
			return $where;
		}

		global $wpdb;

		$sql = $wpdb->prepare( "update {$wpdb->prefix}gp_translations SET translation_0 = REPLACE( translation_0, %s, %s ) where translation_set_id=%d;", $term, $term_replace, $translation_set->id );
		$wpdb->query( $sql );

		return $where;
	}

}
