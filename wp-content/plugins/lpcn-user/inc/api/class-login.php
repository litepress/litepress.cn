<?php

namespace LitePress\User\Inc\Api;

use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use function LitePress\User\Inc\tcaptcha_check;

/**
 * Class Login
 *
 * 该接口用于用户登录
 *
 * @package LitePress\User\Inc\Api\Plugins
 */
class Login extends Base {

	public function __construct() {
		register_rest_route( 'lpcn/user', 'login', array(
			'methods'  => WP_REST_Server::CREATABLE,
			'callback' => array( $this, 'login' ),
		) );
	}

	public function login( WP_REST_Request $request ): WP_REST_Response {
		$params = $this->prepare_params( $request->get_params() );
		if ( is_wp_error( $params ) ) {
			return $this->error( $params->get_error_message() );
		}

		if ( ! tcaptcha_check( $params['tcaptcha-ticket'], $params['tcaptcha-randstr'] ) ) {
			return $this->error( '验证码错误' );
		}

		$login_data['user_login']    = $params['username'];
		$login_data['user_password'] = $params['password'];
		$login_data['remember']      = $params['remember'];

		$user_verify = wp_signon( $login_data, false );
		if ( is_wp_error( $user_verify ) ) {
			return $this->error( '用户名或者密码错误！' );
		}

		// 需要在登录成功后设置此 Cookie 以绕过 ols 的缓存
		setcookie( '_lscache_vary', 'abc', time() + ( 365 * 24 * 60 * 60 ), '/' );

		return $this->success( '登录成功' );
	}

	private function prepare_params( array $params ): array|WP_Error {
		$allowed = array(
			'username',
			'password',
			'remember',
			'tcaptcha-ticket',
			'tcaptcha-randstr',
		);

		if ( empty( $params['username'] ) || empty( $params['password'] ) ) {
			return new WP_Error( 'required_field_is_empty', '账号密码不能为空' );
		}

		if ( empty( $params['tcaptcha-ticket'] ) || empty( $params['tcaptcha-randstr'] ) ) {
			return new WP_Error( 'required_field_is_empty', '必须完成滑块验证才可登录' );
		}

		return array_filter( $params, function ( string $param ) use ( $allowed ) {
			return in_array( $param, $allowed );
		}, ARRAY_FILTER_USE_KEY );
	}

}
