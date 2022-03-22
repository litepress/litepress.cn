<?php

namespace LitePress\User\Inc\Api;

use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use function LitePress\User\Inc\login_by_user_id;

/**
 * Class Register
 *
 * 该接口用于用户注册
 *
 * @package LitePress\User\Inc\Api\Register
 */
class Register extends Base {

	public function __construct() {
		register_rest_route( 'lpcn/user', 'register', array(
			'methods'  => WP_REST_Server::CREATABLE,
			'callback' => array( $this, 'register' ),
		) );
	}

	public function register( WP_REST_Request $request ): WP_REST_Response {
		if ( is_user_logged_in() ) {
			return $this->error( '你已经处于登录状态，请注销后再注册新用户。' );
		}

		$params = $this->prepare_params( $request->get_params() );
		if ( is_wp_error( $params ) ) {
			return $this->error( $params->get_error_message() );
		}

		$token_data = get_transient( "lpcn_user_bind_{$params['token']}" );
		if ( empty( $token_data ) ) {
			return $this->error( 'Token 过期或不存在' );
		}

		global $wpdb;
		// 临时的用户名，等用户插入成功后将用户名更新为用户 ID，要不然太长了。
		$username = md5( rand( 100, 999 ) + time() );
		$user_id  = wp_create_user( $username, '' );
		if ( empty( $user_id ) ) {
			return $this->error( '用户创建失败，请联系管理员处理' );
		}

		$wpdb->update( 'wp_users',
			array(
				'user_login'    => $user_id,
				'user_nicename' => $user_id,
				'display_name'  => $user_id,
			), array(
				'ID' => $user_id,
			)
		);

		if ( 'qq' === $token_data['type'] ) {
			$r = add_user_meta( $user_id, 'qq_openid', $token_data['qq_openid'], true );
			if ( empty( $r ) ) {
				return $this->error( '用户创建失败：无法绑定 QQ 号码信息' );
			}
		} else if ( 'mobile' === $token_data['type'] ) {
			$r = add_user_meta( $user_id, 'mobile', $token_data['mobile'], true );
			if ( empty( $r ) ) {
				return $this->error( '用户创建失败：无法绑定手机号码信息' );
			}
		}

		login_by_user_id( $user_id );

		return $this->success( '注册成功' );
	}

	private function prepare_params( array $params ): array|WP_Error {
		$allowed = array(
			'token',
		);

		if ( empty( $params['token'] ) ) {
			return new WP_Error( 'required_field_is_empty', 'Token 不能为空' );
		}

		foreach ( $params as $key => $param ) {
			$params[ $key ] = sanitize_text_field( $param );
		}

		return array_filter( $params, function ( string $param ) use ( $allowed ) {
			return in_array( $param, $allowed );
		}, ARRAY_FILTER_USE_KEY );
	}

}
