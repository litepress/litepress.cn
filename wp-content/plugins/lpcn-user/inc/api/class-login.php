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
 * @package LitePress\User\Inc\Api\Login
 */
class Login extends Base {

	public function __construct() {
		register_rest_route( 'lpcn/user', 'login', array(
			'methods'  => WP_REST_Server::CREATABLE,
			'callback' => array( $this, 'login' ),
		) );
	}

	public function login( WP_REST_Request $request ): WP_REST_Response {
		if ( is_user_logged_in() ) {
			return $this->error( '你已经处于登录状态，平台不允许重复登录，请刷新页面查看。' );
		}

		$params = $this->prepare_params( $request->get_params() );
		if ( is_wp_error( $params ) ) {
			return $this->error( $params->get_error_message() );
		}

		if ( ! tcaptcha_check( $params['tcaptcha-ticket'], $params['tcaptcha-randstr'] ) ) {
			return $this->error( '验证码错误' );
		}

		$login_data['user_login']    = $params['username'];
		$login_data['user_password'] = $params['password'];
		$login_data['remember']      = true;

		$user_verify = wp_signon( $login_data, false );
		if ( is_wp_error( $user_verify ) ) {
			return $this->error( '用户名或者密码错误！' );
		}

		// 如果传入了 Token 则添加第三方登录绑定信息
		if ( ! empty( $params['token'] ) ) {
			$token_data = get_transient( "lpcn_user_bind_{$params['token']}" );
			if ( empty( $token_data ) ) {
				return $this->error( 'Token 过期或不存在' );
			}

			global $wpdb;
			if ( 'qq' === $token_data['type'] ) {
				$exist = $wpdb->get_row(
					$wpdb->prepare( 'select * from wp_usermeta where meta_key=%s and user_id=%s', "qq_openid", $user_verify->ID )
				);
				if ( ! empty( $exist ) ) {
					return $this->error( '此账号已经绑定其他 QQ 号码' );
				}

				$r = add_user_meta( $user_verify->ID, 'qq_openid', $token_data['qq_openid'], true );
				if ( empty( $r ) ) {
					return $this->error( '用户创建失败：无法绑定 QQ 号码信息' );
				}
			} else if ( 'mobile' === $token_data['type'] ) {
				$exist = $wpdb->get_row(
					$wpdb->prepare( 'select * from wp_usermeta where meta_key=%s and user_id=%s', "mobile", $user_verify->ID )
				);
				if ( ! empty( $exist ) ) {
					return $this->error( '此账号已经绑定其他手机号码' );
				}

				$r = add_user_meta( $user_verify->ID, 'mobile', $token_data['mobile'], true );
				if ( empty( $r ) ) {
					return $this->error( '用户创建失败：无法绑定手机号码信息' );
				}
			}
		}

		// 需要在登录成功后设置此 Cookie 以绕过 ols 的缓存
		setcookie( '_lscache_vary', 'abc', time() + ( 365 * 24 * 60 * 60 ), '/' );

		return $this->success( '登录成功' );
	}

	private function prepare_params( array $params ): array|WP_Error {
		$allowed = array(
			'username',
			'password',
			'token', // 选填，填了此 Token 则代表本次登录后需要绑定用户信息
			'tcaptcha-ticket',
			'tcaptcha-randstr',
		);

		if ( empty( $params['username'] ) || empty( $params['password'] ) ) {
			return new WP_Error( 'required_field_is_empty', '账号密码不能为空' );
		}

		if ( empty( $params['tcaptcha-ticket'] ) || empty( $params['tcaptcha-randstr'] ) ) {
			return new WP_Error( 'required_field_is_empty', '必须完成滑块验证才可登录' );
		}

		foreach ( $params as $key => $param ) {
			$params[ $key ] = sanitize_text_field( $param );
		}

		return array_filter( $params, function ( string $param ) use ( $allowed ) {
			return in_array( $param, $allowed );
		}, ARRAY_FILTER_USE_KEY );
	}

}
