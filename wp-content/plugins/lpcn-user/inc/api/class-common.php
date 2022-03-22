<?php

namespace LitePress\User\Inc\Api;

use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use function LitePress\User\Inc\send_sms_code;
use function LitePress\User\Inc\tcaptcha_check;

/**
 * Class Common
 *
 * 一些公共 API 接口
 *
 * @package LitePress\User\Inc\Api\Common
 */
class Common extends Base {

	public function __construct() {
		register_rest_route( 'lpcn/user', 'send_sms_code', array(
			'methods'  => WP_REST_Server::CREATABLE,
			'callback' => array( $this, 'send_sms_code' ),
		) );
	}

	public function send_sms_code( WP_REST_Request $request ): WP_REST_Response {
		$params = $this->prepare_params( $request->get_params() );
		if ( is_wp_error( $params ) ) {
			return $this->error( $params->get_error_message() );
		}

		if ( ! tcaptcha_check( $params['tcaptcha-ticket'], $params['tcaptcha-randstr'] ) ) {
			return $this->error( '滑块验证码错误' );
		}

		$r = send_sms_code( $params['mobile'] );
		if ( is_wp_error( $r ) ) {
			return $this->error( '验证码发送失败：' . $r->get_error_message() );
		}

		return $this->success( '发送成功，有效期 5 分钟' );
	}

	private function prepare_params( array $params ): array|WP_Error {
		$allowed = array(
			'mobile',
			'tcaptcha-ticket',
			'tcaptcha-randstr',
		);

		if ( empty( $params['mobile'] ) ) {
			return new WP_Error( 'required_field_is_empty', '手机号不能为空' );
		}

		if ( empty( $params['tcaptcha-ticket'] ) || empty( $params['tcaptcha-randstr'] ) ) {
			return new WP_Error( 'required_field_is_empty', '必须完成滑块验证才可发送验证码' );
		}

		foreach ( $params as $key => $param ) {
			$params[ $key ] = sanitize_text_field( $param );
		}

		return array_filter( $params, function ( string $param ) use ( $allowed ) {
			return in_array( $param, $allowed );
		}, ARRAY_FILTER_USE_KEY );
	}

}
