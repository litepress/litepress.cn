<?php

namespace LitePress\User\Inc\Api;

use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * Class Common
 *
 * 一些公共 API 接口
 *
 * @package LitePress\User\Inc\Api\Common
 */
class Common extends Base {

	public function __construct() {

		/**
		 * 检查是否绑定手机号
		 */
		register_rest_route( 'lpcn/user', 'check_mobile_bind', array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => array( $this, 'check_mobile_bind' ),
			'permission_callback' => 'is_user_logged_in',
		) );
	}

	public function check_mobile_bind( WP_REST_Request $request ): WP_REST_Response {
		$mobile = get_user_meta( get_current_user_id(), 'mobile', true );
		if ( empty( $mobile ) ) {
			return $this->error( '未绑定手机号' );
		}

		return $this->success( '已绑定手机号' );
	}

}
