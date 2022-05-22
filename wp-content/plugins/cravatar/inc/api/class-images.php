<?php

namespace LitePress\Cravatar\Inc\Api;

use LitePress\Cravatar\Inc\Service\Image;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * Class Images
 *
 * 图片管理相关 API 接口
 *
 * @package LitePress\User\Inc\Api
 */
class Images extends Base {

	private Image $avatar_service;

	public function __construct() {
		$user_id              = get_current_user_id();
		$this->avatar_service = new Image( $user_id );

		register_rest_route( 'cravatar', 'images', array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => array( $this, 'all' ),
			'permission_callback' => 'is_user_logged_in',
		) );

		register_rest_route( 'cravatar', 'images', array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => array( $this, 'add' ),
			'permission_callback' => 'is_user_logged_in',
		) );

		register_rest_route( 'cravatar', 'images/(?P<id>\d+)', array(
			'methods'             => WP_REST_Server::DELETABLE,
			'callback'            => array( $this, 'delete' ),
			'permission_callback' => 'is_user_logged_in',
		) );
	}

	/**
	 * 获取当前用户全部的图片数据
	 *
	 * @return \WP_REST_Response
	 */
	public function all(): WP_REST_Response {
		$avatars = $this->avatar_service->all();

		return $this->success( '数据获取成功', $avatars );
	}

	/**
	 * 添加一个新图片
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_REST_Response
	 */
	public function add( WP_REST_Request $request ): WP_REST_Response {
		$params = $this->prepare_add_params( $request->get_file_params() );
		if ( is_wp_error( $params ) ) {
			return $this->error( $params->get_error_message() );
		}

		$r = $this->avatar_service->add( $params['image'] );
		if ( is_wp_error( $r ) ) {
			return $this->error( $r->get_error_message() );
		}

		return $this->success( '添加成功' );
	}

	private function prepare_add_params( array $params ): array|WP_Error {
		$allowed = array(
			'image',
		);

		if ( empty( $params['image'] ) ) {
			return new WP_Error( 'required_field_is_empty', '未传入图片' );
		}

		return array_filter( $params, function ( string $param ) use ( $allowed ) {
			return in_array( $param, $allowed );
		}, ARRAY_FILTER_USE_KEY );
	}

	/**
	 * 删除图片
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_REST_Response
	 */
	public function delete( WP_REST_Request $request ): WP_REST_Response {
		$params = $this->prepare_delete_params( $request->get_params() );
		if ( is_wp_error( $params ) ) {
			return $this->error( $params->get_error_message() );
		}

		$r = $this->avatar_service->delete( $params['id'] );
		if ( is_wp_error( $r ) ) {
			return $this->error( $r->get_error_message() );
		}

		return $this->success( '删除成功' );
	}

	private function prepare_delete_params( array $params ): array|WP_Error {
		$allowed = array(
			'id',
		);

		foreach ( $params as $key => $param ) {
			$params[ $key ] = sanitize_text_field( $param );
		}

		if ( empty( $params['id'] ) ) {
			return new WP_Error( 'required_field_is_empty', '图片 ID 为空' );
		}

		return array_filter( $params, function ( string $param ) use ( $allowed ) {
			return in_array( $param, $allowed );
		}, ARRAY_FILTER_USE_KEY );
	}

}
