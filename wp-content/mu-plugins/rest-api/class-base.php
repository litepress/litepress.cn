<?php

namespace LitePress\RestApi;

use stdClass;
use WP_REST_Response;

// 引入 API 代码
require __DIR__ . '/class-tools.php';

/**
 * Class Base
 *
 * 该类提供 API 功能的一些基本方法，同时负责初始化所有 API 端点
 *
 * @package LitePress\RestApi
 */
class Base {

	/**
	 * 初始化 API
	 */
	public static function init(): void {
		self::load_routes();
	}

	/**
	 * 引入所有 API 端点
	 */
	public static function load_routes(): void {
		new Tools();
	}

	protected function success( string $message, array|stdClass $data = array() ): WP_REST_Response {
		return new WP_REST_Response( array( 'message' => $message, 'data' => $data, 'status' => 0 ) );
	}

	protected function error( string $message, array $data = array(), int $status_code = 1 ): WP_REST_Response {
		return new WP_REST_Response( array( 'message' => $message, 'data' => $data, 'status' => $status_code ), 500 );
	}

}
