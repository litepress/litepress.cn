<?php

namespace LitePress\GlotPress\MT;

use GP;
use GP_Route;

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
		$t = new Translate();

		/**
		 * 机器翻译引擎对外 API 接口
		 *
		 * 该接口在网关处被改写为：https://api.litepress.cn/mt/translate
		 */
		GP::$router->add( '/api/mt/translate', array( $t, 'api' ), 'post' );

		GP::$router->add( "/gp-mt/(.+?)", array( Web::class, 'add_web_translate_job' ), 'get' );
		GP::$router->add( "/gp-mt/(.+?)", array( Web::class, 'add_web_translate_job' ), 'post' );


		add_action( 'lpcn_schedule_gp_mt', array( $t, 'web' ), 999, 2 );
	}


}
