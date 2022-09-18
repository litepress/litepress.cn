<?php

namespace LitePress\Cravatar\Inc;

use LitePress\Cravatar\Inc\Api\Base;

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
		// 载入 Rest API
		add_action( 'rest_api_init', array( Base::class, 'init' ) );

		// 载入头像内容审查功能
		add_action( 'lpcn_sensitive_content_recognition', array( Avatar_Audit::get_instance(), 'worker' ), 10, 3 );
	}

}
