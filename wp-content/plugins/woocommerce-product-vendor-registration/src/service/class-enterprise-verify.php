<?php
/**
 * 企业认证服务
 *
 * 我估计可能会有人喷我：为什么不用控制反转的设计模式，而要搞得耦合如此严重，这样将来集成其他认证服务商不方便云云……统一解释下：这个插件首先是
 * 自用，自用的话就只需要一个服务商。如果将来这个插件市场需求量大，才会考虑扩展其他认证服务商，而后才会融入新的设计模式使代码具有可扩展性，否则
 * 在开发之初就各种设计模式一顿怼的话，我觉得是在过度设计，是增大试错陈本的一种错误行为。
 *
 * @package WP_REAL_PERSON_VERIFY
 */

namespace WCY\WC_Product_Vendor_Registration\Src\Service;

use WCY\WC_Product_Vendor_Registration\Src\Util\Enterprise_Verify as Enterprise_Verify_Util;
use WP_Error;

class Enterprise_Verify {

	private Enterprise_Verify_Util $_enterprise_verify_util;

	private int $_user_id = 0;

	/**
	 * 不需要扩展性，未来只支持天眼查这一种接口，所以依赖关系直接硬编码
	 *
	 * Enterprise_Verify constructor.
	 */
	public function __construct( int $user_id = null ) {
		$this->_enterprise_verify_util = new Enterprise_Verify_Util();
		$this->_user_id = $user_id ?: get_current_user_id();
	}

	/**
	 * 企业三要素验证服务
	 *
	 * @param string $code 三码(注册号 /组织机构代码 /统一社会信用代码)
	 * @param string $name 公司名
	 * @param array  $license_img 营业执照照片的上传信息
	 *
	 * @return bool|WP_Error
	 */
	public function verify( string $code, string $name,  array $license_img ) {
		/** 必须得先通过个人实人认证才可以进行企业认证 */
		$personal_name = get_user_meta( $this->_user_id, 'wprpv_real_name', true );
		$personal_cert_no = get_user_meta( $this->_user_id, 'wprpv_cert_no', true );
		if ( empty( $personal_name ) || empty( $personal_cert_no ) ) {
			return new WP_Error( 'lack_of_dependence', '您必须先完成个人实人认证才可以进行企业认证哦' );
		}

		/** 检查是否重复提交认证 */
		$old_enterprise_code = get_user_meta( $this->_user_id, 'wprpv_enterprise_code', true );
		$old_enterprise_name = get_user_meta( $this->_user_id, 'wprpv_enterprise_name', true );
		if ( $old_enterprise_code === $code || $old_enterprise_name === $name ) {
			return new WP_Error( 'repeat_real_name', "您的企业{$old_enterprise_name}已通过认证，请不要重复提交哦" );
		}

		$data = $this->_enterprise_verify_util->verify( $code, $name, $personal_name );
		if ( is_wp_error( $data ) ) {
			/**
			 * @var WP_Error
			 */
			return $data;
		}

		/** 企业认证通过后将实名信息更新上 */
		if ( $data ) {
			$img_url = wprpv_save_img( $license_img );
			if ( is_wp_error( $img_url ) ) {
				/**
				 * @var WP_Error
				 */
				return $img_url;
			}

			update_user_meta( $this->_user_id, 'wprpv_enterprise_code', $code );
			update_user_meta( $this->_user_id, 'wprpv_enterprise_name', $name );
			update_user_meta( $this->_user_id, 'wprpv_enterprise_license_img', $img_url );
		}

		return $data;
	}

}
