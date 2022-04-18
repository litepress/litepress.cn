<?php

namespace LitePress\User\Inc\Api\User_Center;

use LitePress\User\Inc\Api\Base;
use PasswordHash;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use function LitePress\Helper\check_tncode;
use function LitePress\User\Inc\check_sms_code;
use function LitePress\User\Inc\check_email_code;

/**
 * Class Security
 *
 * 用户中心的 “安全” Tab 中的设置项目及绑定项目
 * QQ 绑定功能不在此处。
 *
 * @package LitePress\User\Inc\Api\User_Cente\Security
 */
class Security extends Base {

	public function __construct() {
		register_rest_route( 'center', 'security/bind-mobile', array(
			'methods'  => WP_REST_Server::EDITABLE,
			'callback' => array( $this, 'bind_mobile' ),
		) );

		register_rest_route( 'center', 'security/bind-email', array(
			'methods'  => WP_REST_Server::EDITABLE,
			'callback' => array( $this, 'bind_email' ),
		) );

		register_rest_route( 'center', 'security/reset-passwd', array(
			'methods'  => WP_REST_Server::EDITABLE,
			'callback' => array( $this, 'reset_passwd' ),
		) );
	}

	public function bind_email( WP_REST_Request $request ): WP_REST_Response {
		if ( ! is_user_logged_in() ) {
			return $this->error( '你必须先登录。' );
		}

		$params = $this->prepare_bind_email_params( $request->get_params() );
		if ( is_wp_error( $params ) ) {
			return $this->error( $params->get_error_message() );
		}

		$user_id = get_current_user_id();

		if ( ! check_tncode() ) {
			return $this->error( '滑块验证码错误' );
		}

		// 需要验证短信
		if ( ! check_email_code( $params['email'], $params['email_code'] ) ) {
			return $this->error( '邮箱验证码不匹配！' );
		}

		// 保存用户 Meta 表数据
		update_user_meta( $user_id, 'user_email', $params['email'] );

		return $this->success( '邮箱绑定成功' );
	}

	private function prepare_bind_email_params( array $params ): array|WP_Error {
		$allowed = array(
			'email',
			'email_code',
		);

		foreach ( $params as $key => $param ) {
			$params[ $key ] = sanitize_text_field( $param );
		}

		return array_filter( $params, function ( string $param ) use ( $allowed ) {
			return in_array( $param, $allowed );
		}, ARRAY_FILTER_USE_KEY );
	}

	public function bind_mobile( WP_REST_Request $request ): WP_REST_Response {
		if ( ! is_user_logged_in() ) {
			return $this->error( '你必须先登录。' );
		}

		$params = $this->prepare_bind_mobile_params( $request->get_params() );
		if ( is_wp_error( $params ) ) {
			return $this->error( $params->get_error_message() );
		}

		$user_id = get_current_user_id();

		if ( ! check_tncode() ) {
			return $this->error( '滑块验证码错误' );
		}

		// 需要验证短信
		if ( ! check_sms_code( $params['mobile'], $params['sms_code'] ) ) {
			return $this->error( '短信验证码不匹配！' );
		}

		// 保存用户 Meta 表数据
		update_user_meta( $user_id, 'mobile', $params['mobile'] );

		return $this->success( '手机号绑定成功' );
	}

	private function prepare_bind_mobile_params( array $params ): array|WP_Error {
		$allowed = array(
			'mobile',
			'sms_code',
		);

		foreach ( $params as $key => $param ) {
			$params[ $key ] = sanitize_text_field( $param );
		}

		return array_filter( $params, function ( string $param ) use ( $allowed ) {
			return in_array( $param, $allowed );
		}, ARRAY_FILTER_USE_KEY );
	}

	public function reset_passwd( WP_REST_Request $request ): WP_REST_Response {
		if ( ! is_user_logged_in() ) {
			return $this->error( '你必须先登录。' );
		}

		$params = $this->prepare_reset_passwd_params( $request->get_params() );
		if ( is_wp_error( $params ) ) {
			return $this->error( $params->get_error_message() );
		}

		$current_user = wp_get_current_user();

		$wp_hasher       = new PasswordHash( 8, true );
		$password_hashed = $current_user->user_pass;
		$plain_password  = $params['old_passwd'];
		if ( ! $wp_hasher->CheckPassword( $plain_password, $password_hashed ) ) {
			return $this->error( '旧密码不匹配，如果你未设置旧密码请留空。' );
		}

		// 更新用户密码
		wp_update_user( array( 'ID' => $current_user->ID, 'user_pass' => $params['new_passwd'] ) );

		return $this->success( '密码重置成功' );
	}

	private function prepare_reset_passwd_params( array $params ): array|WP_Error {
		$allowed = array(
			'old_passwd',
			'new_passwd',
		);

		foreach ( $params as $key => $param ) {
			$params[ $key ] = sanitize_text_field( $param );
		}

		return array_filter( $params, function ( string $param ) use ( $allowed ) {
			return in_array( $param, $allowed );
		}, ARRAY_FILTER_USE_KEY );
	}

}
