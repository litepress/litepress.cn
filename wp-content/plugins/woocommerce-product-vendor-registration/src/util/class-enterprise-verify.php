<?php
/**
 * 企业认证工具类
 *
 * @package WP_REAL_PERSON_VERIFY
 */

namespace WCY\WC_Product_Vendor_Registration\Src\Util;

use WP_Error;
use WP_Http;

class Enterprise_Verify {

	private string $_token;

	public function __construct() {
		$this->_token = TYC_TOKEN;
	}

	/**
	 * 验证企业三要素验证
	 *
	 * @param string $code 三码(注册号 /组织机构代码 /统一社会信用代码)
	 * @param string $name 公司名
	 * @param string $legal_person_name 法人姓名
	 *
	 * @return bool|WP_Error
	 */
	public function verify( string $code, string $name, string $legal_person_name ) {
		$url = sprintf(
			'https://open.api.tianyancha.com/services/open/ic/verify/2.0?code=%s&name=%s&legalPersonName=%s',
			$code, $name, $legal_person_name
		);
		$http = new WP_Http;
		$args = array(
			'headers' => array(
				'Authorization' => $this->_token,
			),
		);

		$result = $http->request( $url, $args );
		if ( is_wp_error( $result ) ) {
			/**
			 * result的值可能是array也可能是WP_Error，但在这个if中只可能是WP_Error
			 *
			 * @var WP_Error
			 */
			return $result;
		}

		$body = json_decode( $result['body'], true );

		/**
		 * 是否认证成功，1成功 0失败
		 *
		 * @var int
		 */
		$is_ok = (int)( $body['result']['result'] ?? 0 );

		return 1 === $is_ok;
	}

}