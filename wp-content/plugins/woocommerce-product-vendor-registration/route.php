<?php
/**
 * 路由表
 *
 * 路由功能本应该使用WordPress自带的URL重写实现的，但是这鸟功能我试了一下午也没成功调用……
 *
 * @package WP_REAL_PERSON_VERIFY
 */

use WCY\WC_Product_Vendor_Registration\Src\Controller\Web\Verify;
use WCY\WC_Product_Vendor_Registration\Src\Service\Face_Verify as Face_Verify_Service;
use WCY\WC_Product_Vendor_Registration\Src\Controller\RestApi\Face_Verify;
use WCY\WC_Product_Vendor_Registration\Src\Service\Enterprise_Verify as Enterprise_Verify_Service;
use WCY\WC_Product_Vendor_Registration\Src\Controller\RestApi\Enterprise_Verify;

/**
 * Web路由
 */
add_action('parse_request', function () {
	global $wp;

	if ( ! isset( $wp->query_vars['pagename'] ) ) {
		return;
	}

	$pages = explode( '/', $wp->query_vars['pagename'] );

	if ( 'real-person-verify' === $pages[0] ) {
		$verify = new Verify();
		$sub_page = $pages[1] ?? '';

		switch ($sub_page) {
			case '':
			case 'select-type':
				$verify->select_type();
				break;
			case 'personal':
				$verify->personal();
				break;
			case 'enterprise':
				$verify->enterprise();
				break;
			case 'complete':
				$verify->complete();
				break;
			case 'job-face-verify':
				$verify->job_face_verify();
				break;
			case 'face-verify-complete':
				$verify->face_verify_complete();
				break;
			default:
				goto no_exit;
		}

		exit(0);

		/** 如果用户输入了意外的子目录，就将控制权重新交给WordPress，而不是直接退出程序。这里使用了万恶的goto语句…… */
		no_exit:
	}
});

/**
 * Rest Api路由
 */
add_action( 'rest_api_init', function () {
	$face_verify = new Face_Verify( new Face_Verify_Service() );

	/**
	 * 获取手机端扫脸页面的URL
	 *
	 * 期望前端将该URL转化为二维码，供用户在手机上打开并进行扫脸。接口实际上还是返回了一个本地的URL，因为阿里云实名接口需要通过JS采集用户的设
	 * 备信息，所以打开一个本地URL，然后通过另一个API提交环境信息给后端，之后后端返回真正的阿里云扫脸URL，最后前端控制一下网页跳转过去。于是
	 * 走完了全部流程。
	 */
	register_rest_route( 'wprpv/v1', '/init-local-face-verify-task', array(
		'methods'             => WP_REST_Server::CREATABLE,
		'callback'            => array( $face_verify, 'init_local_task' ),
		'permission_callback' => array( $face_verify, 'init_local_task_permissions_check' ),
	) );

	/**
	 * 将用户的环境信息传递给该接口以初始化扫脸认证服务，该接口会返回最终的阿里云扫脸认证页的URL
	 *
	 * 此接口不需要指定认证函数，因为接口函数在如果无法通过token读取到用户实名数据就预示着这是个未能拼接正确token的非法请求
	 */
	register_rest_route( 'wprpv/v1', '/init-aliyun-face-verify-task', array(
		'methods'             => WP_REST_Server::CREATABLE,
		'callback'            => array( $face_verify, 'init_aliyun_task' ),
		'permission_callback' => array( $face_verify, 'init_aliyun_task_permissions_check' ),
	) );

	/**
	 * 查询认证情况接口
	 */
	register_rest_route( 'wprpv/v1', '/describe-face-verify-task', array(
		'methods'             => WP_REST_Server::READABLE,
		'callback'            => array( $face_verify, 'describe_task' ),
		'permission_callback' => array( $face_verify, 'describe_task_permissions_check' ),
	) );


	$enterprise_verify = new Enterprise_Verify( new Enterprise_Verify_Service() );

	/**
	 * 验证企业三要素接口
	 */
	register_rest_route( 'wprpv/v1', '/enterprise-verify', array(
		'methods'             => WP_REST_Server::CREATABLE,
		'callback'            => array( $enterprise_verify, 'verify' ),
		'permission_callback' => array( $enterprise_verify, 'verify_permissions_check' ),
	) );
} );
