<?php

namespace LitePress\User\Inc\Api\User_Center;

use LitePress\User\Inc\Api\Base;
use stdClass;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * Class Info
 *
 * 该接口用于获取用户信息
 *
 * @package LitePress\User\Inc\Api\User_Cente\Info
 */
class Basic_Info extends Base {

	public function __construct() {
		register_rest_route( 'center', 'basic-info', array(
			'methods'             => WP_REST_Server::EDITABLE,
			'callback'            => array( $this, 'edit' ),
			'permission_callback' => 'is_user_logged_in',
		) );

		register_rest_route( 'center', 'basic-info', array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => array( $this, 'show' ),
			'permission_callback' => 'is_user_logged_in',
		) );
	}

	public function edit( WP_REST_Request $request ): WP_REST_Response {

		$params = $this->prepare_edit_params( $request->get_params() );
		if ( is_wp_error( $params ) ) {
			return $this->error( $params->get_error_message() );
		}

		$user_id = get_current_user_id();

		// 保存用户表数据
		wp_update_user( array(
			'ID'           => $user_id,
			'display_name' => $params['display_name'],
		) );

		// 保存用户 Meta 表数据
		update_user_meta( $user_id, 'nameplate_text', $params['nameplate_text'] );
		update_user_meta( $user_id, 'nameplate_url', $params['nameplate_url'] );
		update_user_meta( $user_id, 'gender', $params['gender'] );
		update_user_meta( $user_id, 'description', $params['description'] );

		return $this->success( '用户信息更新成功' );
	}

	public function show( WP_REST_Request $request ): WP_REST_Response {

		$params = $this->prepare_show_params( $request->get_params() );
		if ( is_wp_error( $params ) ) {
			return $this->error( $params->get_error_message() );
		}

		$user_id = get_current_user_id();

		// 尝试从用户表提取数据
		$user_data = get_userdata( $user_id );
		if ( empty( $user_data ) || empty( $user_data->data ) ) {
			return $this->error( '获取用户信息失败' );
		}

		/**
		 * @var  stdClass $user_data
		 */
		$user_data = $user_data->data;

		// 去掉敏感字段
		unset( $user_data->user_pass );
		unset( $user_data->user_activation_key );

		// 检查邮箱是否为系统分配的，如果是则说明未绑定邮箱
		if ( $user_data->user_email == $user_id . '@litepress.cn' ) {
			$user_data->bind_email = false;
			$user_data->user_email = '';
		}

		// 尝试提取用户 meta 数据
		$user_data->nameplate_text = get_user_meta( $user_id, 'nameplate_text', true );
		$user_data->nameplate_url  = get_user_meta( $user_id, 'nameplate_url', true );
		$user_data->gender         = get_user_meta( $user_id, 'gender', true );
		$user_data->description    = get_user_meta( $user_id, 'description', true );

		// 获取用户 QQ 和手机号绑定信息
		$user_data->qq_nickname = get_user_meta( $user_id, 'qq_nickname', true );
		$user_data->bind_qq     = (bool) get_user_meta( $user_id, 'qq_nickname' );
		$user_data->mobile      = get_user_meta( $user_id, 'mobile', true );
		$user_data->bind_mobile = (bool) get_user_meta( $user_id, 'mobile' );

		// 获取头像
		$user_data->avatar = get_avatar_url( $user_data->user_email, array(
			'size' => 248
		) );

		return $this->success( '获取用户信息成功', $user_data );
	}

	private function prepare_edit_params( array $params ): array|WP_Error {
		$allowed = array(
			'display_name',
			'nameplate_text',
			'nameplate_url',
			'gender',
			'description',
		);

		foreach ( $params as $key => $param ) {
			$params[ $key ] = sanitize_text_field( $param );
		}

		return array_filter( $params, function ( string $param ) use ( $allowed ) {
			return in_array( $param, $allowed );
		}, ARRAY_FILTER_USE_KEY );
	}

	private function prepare_show_params( array $params ): array|WP_Error {
		$allowed = array();

		foreach ( $params as $key => $param ) {
			$params[ $key ] = sanitize_text_field( $param );
		}

		return array_filter( $params, function ( string $param ) use ( $allowed ) {
			return in_array( $param, $allowed );
		}, ARRAY_FILTER_USE_KEY );
	}

}
