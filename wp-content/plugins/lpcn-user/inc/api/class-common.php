<?php

namespace LitePress\User\Inc\Api;

use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use function LitePress\User\Inc\send_sms_code;
use function LitePress\User\Inc\send_email_code;

/**
 * Class Common
 *
 * 一些公共 API 接口
 *
 * @package LitePress\User\Inc\Api\Common
 */
class Common extends Base {

	public function __construct() {
		register_rest_route( 'common', 'send-sms-code', array(
			'methods'  => WP_REST_Server::CREATABLE,
			'callback' => array( $this, 'send_sms_code' ),
		) );

		register_rest_route( 'common', 'send-email-code', array(
			'methods'  => WP_REST_Server::CREATABLE,
			'callback' => array( $this, 'send_email_code' ),
		) );
	}

	public function send_sms_code( WP_REST_Request $request ): WP_REST_Response {
		$params = $this->prepare_send_sms_code_params( $request->get_params() );
		if ( is_wp_error( $params ) ) {
			return $this->error( $params->get_error_message() );
		}

		if ( $_SESSION['tncode_check'] ) {
			return $this->error( '滑块验证码错误' );
		}

		$r = send_sms_code( $params['mobile'] );
		if ( is_wp_error( $r ) ) {
			return $this->error( '验证码发送失败：' . $r->get_error_message() );
		}

		return $this->success( '发送成功，有效期 5 分钟' );
	}

	public function send_email_code( WP_REST_Request $request ): WP_REST_Response {
		$params = $this->prepare_send_email_code_params( $request->get_params() );
		if ( is_wp_error( $params ) ) {
			return $this->error( $params->get_error_message() );
		}

		if ( $_SESSION['tncode_check'] ) {
			return $this->error( '滑块验证码错误' );
		}

		$r = send_email_code( $params['email'] );
		if ( is_wp_error( $r ) ) {
			return $this->error( '验证码发送失败：' . $r->get_error_message() );
		}

		return $this->success( '发送成功，有效期 5 分钟' );
	}

	private function prepare_send_sms_code_params( array $params ): array|WP_Error {
		$allowed = array(
			'mobile',
		);

		if ( empty( $params['mobile'] ) ) {
			return new WP_Error( 'required_field_is_empty', '手机号不能为空' );
		}

		foreach ( $params as $key => $param ) {
			$params[ $key ] = sanitize_text_field( $param );
		}

		return array_filter( $params, function ( string $param ) use ( $allowed ) {
			return in_array( $param, $allowed );
		}, ARRAY_FILTER_USE_KEY );
	}

	private function prepare_send_email_code_params( array $params ): array|WP_Error {
		$allowed = array(
			'email',
		);

		if ( empty( $params['email'] ) ) {
			return new WP_Error( 'required_field_is_empty', '邮箱不能为空' );
		}

		foreach ( $params as $key => $param ) {
			$params[ $key ] = sanitize_text_field( $param );
		}

		return array_filter( $params, function ( string $param ) use ( $allowed ) {
			return in_array( $param, $allowed );
		}, ARRAY_FILTER_USE_KEY );
	}

}
