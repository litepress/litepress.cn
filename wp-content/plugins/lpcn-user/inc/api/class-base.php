<?php

namespace LitePress\User\Inc\Api;

use WP_REST_Response;

/**
 * Class Base
 *
 * 该类提供 API 功能的一些基本方法，同时负责初始化所有 API 端点
 *
 * @package LitePress\User\Inc\Api
 */
class Base {

	/**
	 * 初始化 API
	 */
	public static function init() {
		self::load_routes();
	}

	/**
	 * 引入所有 API 端点
	 */
	public static function load_routes() {
		new Login();
		new Register();
		new Common();
		new Login_By_Mobile();
	}

	protected function success( string $message, array $data = array() ): WP_REST_Response {
		return new WP_REST_Response( array( 'message' => $message, 'data' => $data, 'status' => 0 ) );
	}

	protected function error( string $message, array $data = array(), int $status_code = 1 ): WP_REST_Response {
		return new WP_REST_Response( array( 'message' => $message, 'data' => $data, 'status' => $status_code ), 500 );
	}

}
