<?php

namespace LitePress\WCAPI\Inc\Api\Info;

use LitePress\WCAPI\Inc\Api\Base;
use LitePress\WCAPI\Inc\Service\Info_Service;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * Class Info_Tags
 *
 * 该接口用于返回某个分类下属的标签列表
 *
 * @package LitePress\WCAPI\Inc\Api\Info
 */
class Info_Tags extends Base {

	public function __construct() {
		register_rest_route( 'store/v1', 'info_tags', array(
			'methods'  => WP_REST_Server::CREATABLE,
			'callback' => array( $this, 'info_tags' ),
		) );
	}

	public function info_tags( WP_REST_Request $request ): WP_REST_Response {
		$params = $this->prepare_params( $request->get_params() );

		$categories = $params['categories'];
		if ( JSON_ERROR_NONE !== json_last_error() ) {
			$args = array(
				'code' => 1,
				'msg'  => 'categories 参数格式错误，无法解析为 Json',
			);
			$this->error( $args );
		}
		$categories = $params['categories'] ?? array();

		$info_service = new Info_Service();
		$tags         = $info_service->get_tags(
			$categories,
		);

		$args = array(
			'code' => 0,
			'msg'  => 'success',
			'data' => array(
				'tags' => $tags,
			),
		);

		return new WP_REST_Response( $args );
	}

	private function prepare_params( array $params ): array {
		$allowed = array(
			'categories',
		);

		return array_filter( $params, function ( string $param ) use ( $allowed ) {
			return in_array( $param, $allowed );
		}, ARRAY_FILTER_USE_KEY );
	}

}
