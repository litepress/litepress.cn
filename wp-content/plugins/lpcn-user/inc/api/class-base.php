<?php

namespace LitePress\API\Inc\Api;

use LitePress\API\Inc\Api\Plugins\Update_Check as Plugins_Update_Check;
use LitePress\API\Inc\Api\Themes\Update_Check as Themes_Update_Check;
use LitePress\API\Inc\Api\Core\Update_Check as Core_Update_Check;
use WP_REST_Response;

/**
 * Class Base
 *
 * 该类提供 API 功能的一些基本方法，同时负责初始化所有 API 端点
 *
 * @package LitePress\API\Inc\Api
 */
class Base {

	/**
	 * 初始化 API
	 */
	public static function init() {
		self::load_routes();
		self::load_fields();
	}

	/**
	 * 引入所有 API 端点
	 */
	public static function load_routes() {
		new Plugins_Update_Check();
		new Themes_Update_Check();
		new Core_Update_Check();
	}

	/**
	 * 引入所有 API 字段
	 */
	public static function load_fields() {

	}

	protected function success() {

	}

	protected function error( string $message, array $data, int $status_code = 1 ) {
		return new WP_REST_Response( array( 'message' => $message, 'data' => $data, $status_code ), 500 );
	}

}
