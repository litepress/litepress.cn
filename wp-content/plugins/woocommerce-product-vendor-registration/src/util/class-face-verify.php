<?php
/**
 * 人脸认证工具类
 *
 * @package WP_REAL_PERSON_VERIFY
 */

namespace WCY\WC_Product_Vendor_Registration\Src\Util;

use AlibabaCloud\Client\AlibabaCloud;
use AlibabaCloud\Client\Exception\ClientException;
use AlibabaCloud\Client\Exception\ServerException;
use WP_Error;

class Face_Verify {

	/**
	 * @throws ClientException
	 */
	public function __construct() {
		AlibabaCloud::accessKeyClient( ALIYUN_ACCESS_ID, ALIYUN_ACCESS_KEY )
		            ->regionId( 'cn-hangzhou' )
		            ->asDefaultClient();
	}

	/**
	 * 初始化人脸认证服务
	 *
	 * 该函数通过调用阿里云实人认证API创建一个实人认证任务
	 *
	 * @param string $name 姓名
	 * @param string $cert_no 身份证号
	 * @param string $return_url 认证后的回调URL
	 * @param string $meta_info 用户认证环境的元信息，需引入https://cn-shanghai-aliyun-cloudauth.oss-cn-shanghai.aliyuncs.com/web_sdk_js/jsvm_all.js
	 *                          而后调用getMetaInfo()函数，该函数会返回通过JS读取的环境信息。环境信息包括用户使用的浏览器、操作系统、IP地址等。
	 *
	 * @return array|WP_Error 成功返回包含认证ID和认证URL的数组，失败则返回WP_Error
	 */
	public function init( string $name, string $cert_no, string $return_url, string $meta_info ) {
		try {
			$result = AlibabaCloud::cloudauth()
			                      ->v20190307()
			                      ->initFaceVerify()
			                      ->withSceneId( ALIYUN_SCENE_ID )
			                      ->withOuterOrderNo( "e0c34a77f5ac40a5aa5e6ed20c353888" )
			                      ->withProductCode( 'ID_PRO' )
			                      ->withCertType( 'IDENTITY_CARD' )
			                      ->withCertName( $name )
			                      ->withCertNo( $cert_no )
			                      ->withReturnUrl( $return_url )
			                      ->withMetaInfo( $meta_info )
			                      ->request();

			if ( ! isset( $result['ResultObject']['CertifyId'] ) || ! isset( $result['ResultObject']['CertifyUrl'] ) ) {
				return new WP_Error( 'aliyun_api_request_failed', '阿里云实人认证接口未返回有效的认证URL，可能是系统环境不受支持，请使用手机默认浏览器重新访问当前网址' );
			}

			return array(
				'certify_id' => $result['ResultObject']['CertifyId'],
				'certify_url' => $result['ResultObject']['CertifyUrl'],
			);
		} catch ( ClientException | ServerException $e ) {
			return new WP_Error( $e->getErrorCode(), $e->getErrorMessage() );
		}
	}

	/**
	 * 查询人脸认证结果
	 *
	 * @param string $certify_id 要查询的认证ID，这个ID是你在初始化一个实名认证任务后阿里云返回的，用以标识一个任务
	 *
	 * @return bool|WP_Error 认证成功返回true，否则返回false，若阿里云接口请求失败则返回WP_Error
	 */
	public function describe( string $certify_id ) {
		try {
			$result = AlibabaCloud::cloudauth()
			                      ->v20190307()
			                      ->describeFaceVerify()
			                      ->withSceneId( ALIYUN_SCENE_ID )
			                      ->withCertifyId( $certify_id )
			                      ->request();

			/**
			 * 是否认证成功，T成功 F失败
			 *
			 * @var string
			 */
			$is_ok = $result['ResultObject']['Passed'] ?? 'F';

			return 'T' === $is_ok;
		} catch ( ClientException | ServerException $e ) {
			if ( empty( $e->getErrorMessage() ) ) {
				return new WP_Error( $e->getErrorCode(), '请先在手机端完成认证后再点击此按钮' );
			}
			return new WP_Error( $e->getErrorCode(), $e->getErrorMessage() );
		}
	}

}
