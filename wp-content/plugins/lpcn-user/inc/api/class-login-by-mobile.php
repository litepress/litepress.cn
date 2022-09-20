<?php

namespace LitePress\User\Inc\Api;

use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use function LitePress\Helper\check_sms_code;
use function LitePress\User\Inc\generate_login_token;

/**
 * Class Login_By_Mobile
 *
 * 该接口用于用户使用手机号登录，如果未注册则会自动注册
 *
 * @package LitePress\User\Inc\Api\Login_By_Mobile
 */
class Login_By_Mobile extends Base {

	public function __construct() {
		register_rest_route( 'lpcn/user', 'login-by-mobile', array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => array( $this, 'login_by_mobile' ),
			'permission_callback' => '__return_true',
		) );
	}

	public function login_by_mobile( WP_REST_Request $request ): WP_REST_Response {
		if ( is_user_logged_in() ) {
			return $this->error( '你已经处于登录状态，平台不允许重复登录，请刷新页面查看。' );
		}

		$params = $this->prepare_params( $request->get_params() );
		if ( is_wp_error( $params ) ) {
			return $this->error( $params->get_error_message() );
		}

		// 需要验证短信
		if ( ! check_sms_code( $params['mobile'], $params['sms_code'] ) ) {
			return $this->error( '短信验证码不匹配！' );
		}

		global $wpdb;
		$r = $wpdb->get_row(
			$wpdb->prepare( 'select user_id from wp_usermeta where meta_key=%s and meta_value=%s', "mobile", $params['mobile'] )
		);

		if ( empty( $r ) ) { // 未查询到记录说明此手机号未注册或未绑定账号，直接向前端抛出错误
			$token = md5( rand( 100, 999 ) + time() );
			set_transient( "lpcn_user_bind_$token", array(
				'type'   => 'mobile',
				'mobile' => $params['mobile'],
			), 300 );

			return $this->error( '此手机号未绑定平台账号', array(
				'token' => $token,
			), 2 );
		} else {
			$user_id = $r->user_id;
		}

		wp_set_current_user( $user_id );
		wp_set_auth_cookie( $user_id, true );

		return $this->success( '登录成功', array(
			'login_token' => generate_login_token( $user_id ),
		)  );
	}

	private function prepare_params( array $params ): array|WP_Error {
		$allowed = array(
			'mobile',
			'sms_code',
		);

		if ( empty( $params['mobile'] ) ) {
			return new WP_Error( 'required_field_is_empty', '手机号不能为空' );
		}

		if ( empty( $params['sms_code'] ) ) {
			return new WP_Error( 'required_field_is_empty', '验证码不能为空' );
		}


		foreach ( $params as $key => $param ) {
			$params[ $key ] = sanitize_text_field( $param );
		}

		return array_filter( $params, function ( string $param ) use ( $allowed ) {
			return in_array( $param, $allowed );
		}, ARRAY_FILTER_USE_KEY );
	}

}
