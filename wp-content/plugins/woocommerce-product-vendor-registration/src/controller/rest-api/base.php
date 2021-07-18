<?php
/**
 * Rest Api基类
 *
 * @package WP_REAL_PERSON_VERIFY
 */

namespace WCY\WC_Product_Vendor_Registration\Src\Controller\RestApi;

use WP_REST_Response;
use WP_REST_Server;

class Base {

	/**
	 * Rest Api的基本权限验证
	 *
	 * 该函数主要验证请求者是否登录，至于Nonce的验证将由rest api自动完成
	 *
	 * @return bool
	 */
	protected function base_permissions_check(): bool {
		if ( is_user_logged_in() ) {
			return true;
		}

		return false;
	}

	/**
	 * 返回状态为成功的数据
	 *
	 * 因为整个项目并不复杂，所以所有成功消息的状态码都是200
	 *
	 * @param string $message  消息字段
	 * @param array $data      要负载的数据
	 *
	 * @return WP_REST_Response
	 */
	public function success( string $message, array $data = array() ): WP_REST_Response {
		$args = array(
			'message' => $message,
			'data'    => $data,
		);

		return new WP_REST_Response( $args, 200 );
	}

	/**
	 * 返回状态为失败的数据
	 *
	 * 因为整个项目并不复杂，所以所有成功消息的状态码都是500
	 *
	 * @param string $message  消息字段
	 *
	 * @return WP_REST_Response
	 */
	public function error( string $message ): WP_REST_Response {
		$args = array(
			'message' => $message,
		);

		return new WP_REST_Response( $args, 500 );
	}

}
