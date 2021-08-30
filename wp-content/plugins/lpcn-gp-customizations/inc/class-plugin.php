<?php

namespace LitePress\GlotPress\Customizations\Inc;

use GP;
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
	}

	/**
	 * 自定义 GlotPress 的路由
	 */
	public function router() {
		GP::$router->prepend( "/", array( Index::class, 'index' ) );
		GP::$router->prepend( "/projects/(plugins|themes|docs|core|others)", array( Route_Project::class, 'single' ) );
		GP::$router->prepend( "/projects/-new", array( Route_Project::class, 'new_post' ), 'post' );
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

		// 未命中前方规则的权限检查转交给GlotPress继续处理
		return $none;
	}

	public function gp_url_profile( $url, $user_nicename ) {
		return "/user/$user_nicename?profiletab=translate";
	}

}
