<?php

namespace LitePress\WCAPI\Inc\Api;

use LitePress\WCAPI\Inc\Api\Info\Info_Categories as Info_Categories;
use LitePress\WCAPI\Inc\Api\Info\Info_Tags as Info_Tags;
use LitePress\WCAPI\Inc\Api\Product\Product_List as Product_List;
use LitePress\WCAPI\Inc\Api\Product\Product_Single as Product_Single;
use LitePress\WCAPI\Inc\Api\User\User_Info as User_Info;
use LitePress\WCAPI\Inc\Api\Order\Order_Check as Order_Check;
use LitePress\WCAPI\Inc\Api\Order\Order_Payment as Order_Payment;

/**
 * Class Base
 *
 * 该类提供 WCAPI 功能的一些基本方法，同时负责初始化所有 WCAPI 端点
 *
 * @package LitePress\WCAPI\Inc\Api
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
		new Info_Categories();
		new Info_Tags();
		new Product_List();
		new Product_Single();
		new User_Info();
		new Order_Check();
		new Order_Payment();
	}

	/**
	 * 引入所有 API 字段
	 */
	public static function load_fields() {

	}

	protected function success() {

	}

	protected function error( array $data, int $status_code = 500 ) {
		wp_send_json_error( $data, $status_code, JSON_UNESCAPED_UNICODE );
	}

}
