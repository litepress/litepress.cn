<?php

namespace LitePress\WC_ES;

use LitePress\I18n\i18n;
use function LitePress\Helper\get_product_type_by_categories;

class Plugin {

	/**
	 * @var Plugin|null The singleton instance.
	 */
	private static ?Plugin $instance = null;

	/**
	 * Instantiates a new Plugin object.
	 */
	private function __construct() {
		add_action( 'plugins_loaded', [ $this, 'plugins_loaded' ] );
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
		add_filter( 'ep_post_sync_args', array( $this, 'prepare_post' ), 99, 2 );
		add_filter( 'ep_prepare_meta_allowed_protected_keys', array(
			$this,
			'prepare_meta_allowed_protected_keys'
		), 9999 );
	}

	public function prepare_meta_allowed_protected_keys( $allowed ) {
		$allowed[] = '51_default_editor';
		$allowed[] = '47_default_editor';
		$allowed[] = '46_custom_list_faqs';
		$allowed[] = '365_default_editor';
		$allowed[] = '_api_new_version';

		return $allowed;
	}

	public function prepare_post( $post_args, $post_id ) {
		if ( ! isset( $post_args['terms']['product_cat'] ) || empty( $post_args['terms']['product_cat'] ) ) {
			return $post_args;
		}

		$type = get_product_type_by_categories( $post_args['terms']['product_cat'] );

		switch ( $type ) {
			case 'plugin':
				$gp_project_path = sprintf( 'plugins/%s/readme', $post_args['post_name'] );
				break;
			case 'theme':
				$gp_project_path = sprintf( 'themes/%1$s/%1$s', $post_args['post_name'] );
				break;
			default:
				return $post_args;
		}

		// 翻译标题
		$post_args['post_title_en'] = $post_args['post_title'];
		$cache_key                  = sprintf( '%s_%s_title', $type, $post_args['post_name'] );
		$post_args['post_title']    = i18n::get_instance()->translate( $cache_key, $post_args['post_title'] ?? '', $gp_project_path );

		// 记录slug（产品的post_name参数可能因重复而多了-1、-2这些后缀）
		$urls = get_option( 'permalink-manager-uris' );

		if ( isset( $urls[ $post_args['ID'] ] ) ) {
			$url = $urls[ $post_args['ID'] ];

			$items = explode( '/', $url );

			$post_args['slug'] = $items[ count( $items ) - 1 ];
		} else {
			$post_args['slug'] = $post_args['post_name'];
		}

		// 翻译简介
		$post_args['post_excerpt_en'] = $post_args['post_excerpt'];
		$cache_key                    = sprintf( '%s_%s_short_description', $type, $post_args['post_name'] );
		$post_args['post_excerpt']    = i18n::get_instance()->translate( $cache_key, $post_args['post_excerpt'] ?? '', $gp_project_path );

		// 填充并翻译内容（产品内容默认是保存在Meta中的）
		$content                      = $post_args['meta']['51_default_editor'][0]['value'] ?? '';
		$post_args['post_content_en'] = $content;
		$cache_key                    = sprintf( '%s_%s', $type, $post_args['post_name'] );
		$post_args['post_content']    = i18n::get_instance()->translate( $cache_key, $content, $gp_project_path );

		return $post_args;
	}

}
