<?php

namespace LitePress\WCAPI\Inc\Api\Info;

use LitePress\WCAPI\Inc\Api\Base;
use LitePress\WCAPI\Inc\Service\Info_Service;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * Class Info_Categories
 *
 * 该接口用于返回市场的全部分类列表
 *
 * @package LitePress\WCAPI\Inc\Api\Info
 */
class Info_Categories extends Base {

	public function __construct() {
		register_rest_route( 'store/v1', 'info_categories', array(
			'methods'  => WP_REST_Server::CREATABLE,
			'callback' => array( $this, 'info_categories' ),
		) );
	}

	public function info_categories( WP_REST_Request $request ): WP_REST_Response {

		$info_service = new Info_Service();
		$categories   = $info_service->get_categories();

		$args = array(
			'code' => 0,
			'msg'  => 'success',
			'data' => array(
				'categories' => $categories,
			),
		);

		return new WP_REST_Response( $args );
	}

}
