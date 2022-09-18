<?php
/**
 * 企业实名认证Rest API控制器
 *
 * @package WP_REAL_PERSON_VERIFY
 */

namespace WCY\WC_Product_Vendor_Registration\Src\Controller\RestApi;

use WCY\WC_Product_Vendor_Registration\Src\Service\Enterprise_Verify as Enterprise_Verify_Service;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

class Enterprise_Verify extends Base {

	private Enterprise_Verify_Service $_enterprise_verify_service;

	public function __construct( Enterprise_Verify_Service $enterprise_verify_service ) {
		$this->_enterprise_verify_service = $enterprise_verify_service;
	}

	/**
	 * 验证企业三要素
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 */
	public function verify( WP_REST_Request $request ): WP_REST_Response {
		/** 接口频率限制为单IP1小时内3次请求 */
		wprpv_api_frequency_limit( 3 );

		$args = $this->_prepare_verify_data( $request );
		if ( is_wp_error( $args ) ) {
			return $this->error( $args->get_error_message() );
		}

		$data = $this->_enterprise_verify_service->verify(
			$args['code'], $args['name'], $args['license_img']
		);
		if ( is_wp_error( $data ) ) {
			return $this->error( $data->get_error_message() );
		}

		$args = array(
			'passed' => $data
		);
		return $this->success( $data ? '认证通过' : '认证未通过', $args );
	}

	public function verify_permissions_check( WP_REST_Request $request ): bool {
		return parent::base_permissions_check();
	}

	/**
	 * 为查询人脸认证情况准备数据
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return array|WP_Error
	 */
	private function _prepare_verify_data( WP_REST_Request $request ) {
		$args = array();
		$args['code']              = sanitize_text_field( $request['code'] );
		$args['name']              = sanitize_text_field( $request['name'] );
		$args['license_img']       = $request->get_file_params()['license_img'] ?? null;

		if ( empty( $args['code'] ) ) {
			return new WP_Error( 'field_missing', '企业社会信用代码为必填字段' );
		}
		if ( empty( $args['name'] ) ) {
			return new WP_Error( 'field_missing', '企业名称为必填字段' );
		}
		if ( empty( $args['license_img'] ) ) {
			return new WP_Error( 'field_missing', '营业执照为必填字段' );
		}

		/** 限制通过字段上传的文件类型，防止有人来骗、来注入，我69岁的老接口 */
		$allowed_ext = array( 'jpeg', 'jpg', 'png' );
		$extension = explode( '.', $args['license_img']['name'] );
		if ( ! in_array( end( $extension ), $allowed_ext ) ) {
			return new WP_Error( 'file_type_error', '只允许上传类型为jpeg、jpg、png的图片' );
		}

		return $args;
	}

}
