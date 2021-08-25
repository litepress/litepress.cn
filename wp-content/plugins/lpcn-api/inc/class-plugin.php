<?php

namespace LitePress\API\Inc;

use JetBrains\PhpStorm\NoReturn;
use LitePress\API\Inc\Api\Base;
use LitePress\API\Inc\Api\Plugins\Update_Check;

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
	#[NoReturn] public function plugins_loaded() {
		add_action( 'rest_api_init', array( Base::class, 'init' ) );
	}

	/**
	 * 路由
	 *
	 * @param string $route
	 */
	#[NoReturn] private function loader() {
		add_action( 'rest_api_init', array( __NAMESPACE__ . '\API\Base', 'init' ) );
		new Update_Check();

		/*
		if ( '/' === $route ) {
			wp_redirect( 'https://litepress.cn', 301 );
			exit;
		}

		switch ( $route ) {
			case '/plugins/update-check/1.1/':
			default:
				$r = request_wporg();
				if ( is_wp_error( $r ) ) {
					$args = array(
						'request_url' => add_query_arg( array() ),
						'method'      => $_SERVER['REQUEST_METHOD'],
						'body'        => $_POST,
						'message'     => $r->get_error_message(),
					);
					Logger::error( 'API', '请求 WPOrg API 失败', $args );

					$args = array(
						'message' => $r->get_error_message(),
					);
					wp_send_json_error( $args, 500 );
				}


				$body = wp_remote_retrieve_body( $r );
				json_decode( $body, true );

				if ( WP_Http::OK !== wp_remote_retrieve_response_code( $r ) || JSON_ERROR_NONE !== json_last_error() ) {
					header( 'Content-type:text/html; charset=utf-8' );
				} else {
					header( 'Content-type:application/json; charset=utf-8' );
				}

				echo request_wporg()['body'];
		}

		exit;
		*/
	}

}
