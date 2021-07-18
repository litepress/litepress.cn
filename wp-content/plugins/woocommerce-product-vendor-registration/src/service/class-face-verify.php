<?php
/**
 * 人脸认证服务
 *
 * 我估计可能会有人喷我：为什么不用控制反转的设计模式，而要搞得耦合如此严重，这样将来集成其他认证服务商不方便云云……统一解释下：这个插件首先是
 * 自用，自用的话就只需要一个服务商。如果将来这个插件市场需求量大，才会考虑扩展其他认证服务商，而后才会融入新的设计模式使代码具有可扩展性，否则
 * 在开发之初就各种设计模式一顿怼的话，我觉得是在过度设计，是增大试错陈本的一种错误行为。
 *
 * @package WP_REAL_PERSON_VERIFY
 */

namespace WCY\WC_Product_Vendor_Registration\Src\Service;

use WCY\WC_Product_Vendor_Registration\Src\Util\Face_Verify as Face_Verify_Util;
use WP_Error;

class Face_Verify {

    private Face_Verify_Util $_face_verify_util;

    private int $_verify_limit;

    private int $_user_id;

	/**
	 * 不需要扩展性，未来只支持阿里云这一种接口，所以依赖关系直接硬编码
	 *
	 * Face_Verify constructor.
	 */
	public function __construct( int $user_id = null ) {
		$this->_face_verify_util = new Face_Verify_Util();
		$this->_user_id = $user_id ?: get_current_user_id();
		$this->_verify_limit = get_user_meta( $this->_user_id, 'wprpv_verify_limit', true ) ?: WPRPV_VERIFY_LIMIT;
	}

	/**
	 * 初始化本地任务
	 *
	 * @param string $name
	 * @param string $cert_no
	 *
	 * @return array|WP_Error
	 * @todo 应该创建数据库订单
	 */
	public function init_local_task( string $name, string $cert_no ) {
		if ( $this->_verify_limit <= 0 ) {
			return new WP_Error( 'verify_num_overrun', '您的账户已没有剩余实人认证额度' );
		}

		$old_name = get_user_meta( $this->_user_id, 'wprpv_real_name', true );
		$old_cert_no = get_user_meta( $this->_user_id, 'wprpv_cert_no', true );
		if ( $old_name === $name && $old_cert_no === $cert_no ) {
			return new WP_Error( 'verify_repeat', "您已成功完成了{$name}的实名认证，不要重复提交哦" );
		}

		$args = array(
			'user_id' => $this->_user_id,
			'name'    => $name,
			'cert_no' => $cert_no
		);
		$task_id = md5( rand() );
		set_transient( 'face-verify-temporary-order-' . $task_id, $args, ( 60 * 5 ) );

		return array(
			'verify_limit' => $this->_verify_limit,
			'id'      => $task_id,
		);
	}

	/**
	 * 初始化阿里云人脸认证任务
	 *
	 * 该函数通过调用阿里云实人认证API创建一个实人认证任务
	 *
	 * @param string $task_id 本地任务ID
	 * @param string $name 姓名
	 * @param string $cert_no 身份证号
	 * @param string $return_url 认证后的回调URL
	 * @param string $meta_info 用户认证环境的元信息，需引入https://cn-shanghai-aliyun-cloudauth.oss-cn-shanghai.aliyuncs.com/web_sdk_js/jsvm_all.js
	 *                          而后调用getMetaInfo()函数，该函数会返回通过JS读取的环境信息。环境信息包括用户使用的浏览器、操作系统、IP地址等。
	 *
	 * @return array|WP_Error 成功返回包含认证ID和认证URL的数组，失败则返回WP_Error
	 */
	public function init_aliyun_task( string $task_id, string $name, string $cert_no, string $return_url, string $meta_info ) {
		$data = $this->_face_verify_util->init( $name, $cert_no, $return_url, $meta_info );
		if ( is_wp_error( $data ) ) {
			/**
			 * @var WP_Error
			 */
			return $data;
		}

		$args = get_transient( 'face-verify-temporary-order-' . $task_id );
		$args['certify_id'] = $data['certify_id'];
		set_transient( 'face-verify-temporary-order-' . $task_id, $args, ( 60 * 5 ) );

		return $data;
	}

	/**
	 * 查询人脸认证结果
	 *
	 * @param string $task_id 本地任务ID
	 *
	 * @return bool|WP_Error 认证成功返回true，否则返回false，发生错误返回WP_Error
	 */
	public function describe( string $task_id ) {
		$args = get_transient( 'face-verify-temporary-order-' . $task_id );

		/** TODO:Test */
		//$args['certify_id'] = '4fc7f02cd5657c61fe0c6d401640a2c0';

		if ( ! isset( $args['certify_id'] ) || empty( $args['certify_id'] ) ) {
			return new WP_Error( 'overtime', '当前任务已超时失效，请重新发起认证' );
		}

		$data = $this->_face_verify_util->describe( $args['certify_id'] );
		if ( is_wp_error( $data ) ) {
			/**
			 * @var WP_Error
			 */
			return $data;
		}

		/** 认证成功后为用户更新上实名信息 */
		if ( $data ) {
			update_user_meta( $this->_user_id, 'wprpv_real_name', $args['name'] );
			update_user_meta( $this->_user_id, 'wprpv_cert_no', $args['cert_no'] );
		}

		/** 执行收尾工作 */
		$this->done( $task_id );

		return $data;
	}

	/**
	 * 实人认证流程执行完成后的收尾工作
	 */
	public function done( string $task_id ) {
		$args = get_transient( 'face-verify-temporary-order-' . $task_id );

		if ( $this->_user_id !== 0 && ( ! isset( $args['locked'] ) || ! $args['locked'] ) ) {
			update_user_meta( $this->_user_id, 'wprpv_verify_limit', ( $this->_verify_limit - 1 ) );
			/** 上锁，防止二次扣量 */
			$args['locked'] = true;
			set_transient( 'face-verify-temporary-order-' . $task_id, $args, ( 60 * 5 ) );
		}
	}

}
