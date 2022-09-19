<?php
/**
 * 人脸认证Rest API控制器
 *
 * @package WP_REAL_PERSON_VERIFY
 */

namespace WCY\WC_Product_Vendor_Registration\Src\Controller\RestApi;

use WCY\WC_Product_Vendor_Registration\Src\Service\Face_Verify as Face_Verify_Service;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

class Face_Verify extends Base {

	private Face_Verify_Service $_face_verify_service;

	public function __construct( Face_Verify_Service $face_verify_service ) {
		$this->_face_verify_service = $face_verify_service;
	}

	/**
	 * 初始化本体实名认证任务
	 *
	 * URL有效期为5分钟
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 */
	public function init_local_task( WP_REST_Request $request ): WP_REST_Response {
		$args = $this->_prepare_init_local_task_data( $request );
		if ( is_wp_error( $args ) ) {
			return $this->error( $args->get_error_message() );
		}

		$task = $this->_face_verify_service->init_local_task( $args['name'], $args['cert_no'] );
		if ( is_wp_error( $task ) ) {
			return $this->error( $task->get_error_message() );
		}

		$data = array();
		$data['task_id'] = $task['id'];
		$data['url'] = esc_url( home_url( 'real-person-verify/job-face-verify' ) );

		return $this->success( "成功获取二维码，有效期5分钟。您最多可以尝试实名{$task['verify_limit']}次。", $data );
	}

	public function init_local_task_permissions_check( WP_REST_Request $request ): bool {
		return parent::base_permissions_check();
	}

	/**
	 * 初始化阿里云扫脸服务
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 */
	public function init_aliyun_task( WP_REST_Request $request ): WP_REST_Response {
		$args = $this->_prepare_init_aliyun_task_data( $request );
		if ( is_wp_error( $args ) ) {
			return $this->error( $args->get_error_message() );
		}

		$data = $this->_face_verify_service->init_aliyun_task(
			$args['task_id'], $args['name'], $args['cert_no'], $args['return_url'], $args['meta_info']
		);
		if ( is_wp_error( $data ) ) {
			return $this->error( $data->get_error_message() );
		}

		return $this->success( '认证服务初始化成功', $data );
	}

	public function init_aliyun_task_permissions_check(): bool {
		/** 这个接口默认允许请求而不做权限校验，因为该接口使用具有时效性的任务ID来标识用户 */
		return true;
	}

	/**
	 * 根据认证任务ID查询认证是否通过
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 */
	public function describe_task( WP_REST_Request $request ): WP_REST_Response {
		$args = $this->_prepare_describe_task_data( $request );
		if ( is_wp_error( $args ) ) {
			return $this->error( $args->get_error_message() );
		}

		$data = $this->_face_verify_service->describe( $args['task_id'] );
		if ( is_wp_error( $data ) ) {
			return $this->error( $data->get_error_message() );
		}

		$args = array(
			'passed' => $data
		);

		return $this->success( $data ? '认证通过' : '认证未通过', $args );
	}

	public function describe_task_permissions_check(): bool {
		return parent::base_permissions_check();
	}

	/**
	 * 为返回手机端扫脸页面URL准备数据
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return array|WP_Error
	 */
	private function _prepare_init_local_task_data( WP_REST_Request $request ) {
		$args = array();
		$args['name']    = sanitize_text_field( $request['name'] );
		$args['cert_no'] = sanitize_text_field( $request['cert_no'] );

		if ( empty( $args['name'] ) ) {
			return new WP_Error( 'field_missing', '姓名为必填字段' );
		}

		if ( empty( $args['cert_no'] ) ) {
			return new WP_Error( 'field_missing', '身份证号码为必填字段' );
		}

		return $args;
	}

	/**
	 * 为初始化人脸服务准备数据
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return array|WP_Error
	 */
	private function _prepare_init_aliyun_task_data( WP_REST_Request $request ) {
		$args = array();
		$args['meta_info'] = sanitize_text_field( $request['meta_info'] );
		$args['task_id']     = sanitize_text_field( $request['task_id'] );
		if ( empty( $args['meta_info'] ) ) {
			return new WP_Error( 'field_missing', 'meta_info为必填字段' );
		}
		if ( empty( $args['task_id'] ) ) {
			return new WP_Error( 'invalid_request', '非法请求' );
		}

		$verify_data = get_transient( 'face-verify-temporary-order-' . $args['task_id'] );
		if ( ! $verify_data ) {
			return new WP_Error( 'invalid_request', '当前URL已过期，请重新获取' );
		}

		$user_id = get_current_user_id() ?: 0;
		$args['return_url'] = esc_url(
			home_url( "/real-person-verify/face-verify-complete?task_id={$args['task_id']}&user_id={$user_id}" )
		);

		return array_merge( $args, $verify_data );
	}

	/**
	 * 为查询人脸认证情况准备数据
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return array|WP_Error
	 */
	private function _prepare_describe_task_data( WP_REST_Request $request ) {
		$args = array();
		$args['task_id'] = sanitize_text_field( $request['task_id'] );

		if ( empty( $args['task_id'] ) ) {
			return new WP_Error( 'field_missing', '任务ID为必填字段' );
		}

		return $args;
	}

}
