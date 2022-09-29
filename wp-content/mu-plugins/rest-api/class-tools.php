<?php

namespace LitePress\RestApi;

use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use function LitePress\Helper\check_tncode;
use function LitePress\Helper\send_email_code;
use function LitePress\Helper\send_sms_code;

/**
 * Class Tools
 *
 * 提供一些全平台公用的 API 接口
 *
 * @package LitePress\RestApi
 */
class Tools extends Base {

	public function __construct() {
		/**
		 * 发送短信验证码
		 */
		register_rest_route( 'lpcn/tools', 'send-sms-code', array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => array( $this, 'send_sms_code' ),
			'permission_callback' => '__return_true',
		) );

		/**
		 * 发送邮件验证码
		 */
		register_rest_route( 'lpcn/tools', 'send-email-code', array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => array( $this, 'send_email_code' ),
			'permission_callback' => '__return_true',
		) );

	}

	public function send_sms_code( WP_REST_Request $request ): WP_REST_Response {
		$params = $this->prepare_send_sms_code_params( $request->get_params() );
		if ( is_wp_error( $params ) ) {
			return $this->error( $params->get_error_message() );
		}

		if ( ! check_tncode() ) {
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

		if ( ! check_tncode() ) {
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
