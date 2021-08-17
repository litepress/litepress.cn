<?php

namespace LitePress\GlotPress\Customizations;

use GP;
use GP_Translation;
use LitePress\Chinese_Format\Chinese_Format;

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
		add_action( 'gp_translation_created', [ $this, 'translation_format' ], 1 );
		add_action( 'gp_translation_saved', [ $this, 'translation_format' ], 1 );

		add_filter( 'gp_pre_can_user', array( $this, 'can_user' ), 10, 2 );
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

}
