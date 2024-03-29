<?php

namespace LitePress\User\Inc\Api\User_Center;

use LitePress\User\Inc\Api\Base;
use PasswordHash;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use function LitePress\Helper\check_tncode;
use function LitePress\Helper\check_sms_code;
use function LitePress\Helper\check_email_code;

/**
 * Class Security
 *
 * 用户中心的 “安全” Tab 中的设置项目及绑定项目
 *
 * @package LitePress\User\Inc\Api\User_Cente\Security
 */
class Security extends Base {

	public function __construct() {
		register_rest_route( 'center', 'security/bind_mobile', array(
			'methods'             => WP_REST_Server::EDITABLE,
			'callback'            => array( $this, 'bind_mobile' ),
			'permission_callback' => 'is_user_logged_in',
		) );

		register_rest_route( 'center', 'security/bind_email', array(
			'methods'             => WP_REST_Server::EDITABLE,
			'callback'            => array( $this, 'bind_email' ),
			'permission_callback' => 'is_user_logged_in',
		) );

		register_rest_route( 'center', 'security/bind_qq', array(
			'methods'             => WP_REST_Server::EDITABLE,
			'callback'            => array( $this, 'bind_qq' ),
			'permission_callback' => 'is_user_logged_in',
		) );

		register_rest_route( 'center', 'security/reset_passwd', array(
			'methods'             => WP_REST_Server::EDITABLE,
			'callback'            => array( $this, 'reset_passwd' ),
			'permission_callback' => 'is_user_logged_in',
		) );

		register_rest_route( 'center', 'security/destroy', array(
			'methods'             => WP_REST_Server::DELETABLE,
			'callback'            => array( $this, 'destroy' ),
			'permission_callback' => 'is_user_logged_in',
		) );

		register_rest_route( 'center', 'security/unbind_qq', array(
			'methods'             => WP_REST_Server::DELETABLE,
			'callback'            => array( $this, 'unbind_qq' ),
			'permission_callback' => 'is_user_logged_in',
		) );

		register_rest_route( 'center', 'security/unbind_email', array(
			'methods'             => WP_REST_Server::DELETABLE,
			'callback'            => array( $this, 'unbind_email' ),
			'permission_callback' => 'is_user_logged_in',
		) );
	}

	/**
	 * 解绑QQ
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 */
	public function unbind_qq( WP_REST_Request $request ): WP_REST_Response {

		$user_id = get_current_user_id();

		delete_user_meta( $user_id, 'qq_openid' );
		delete_user_meta( $user_id, 'qq_nickname' );

		return $this->success( '已成功解绑QQ' );
	}

	/**
	 * 解绑邮箱
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 */
	public function unbind_email( WP_REST_Request $request ): WP_REST_Response {

		$user_id = get_current_user_id();

		// 更新用户邮箱
		wp_update_user( array(
			'ID'         => $user_id,
			'user_email' => $user_id . '@litepress.cn',
		) );

		return $this->success( '已成功解绑邮箱' );
	}

	/**
	 * 注销账号
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 */
	public function destroy( WP_REST_Request $request ): WP_REST_Response {
		if ( ! is_user_logged_in() ) {
			return $this->error( '你必须先登录。' );
		}

		$params = $this->prepare_destroy_params( $request->get_params() );
		if ( is_wp_error( $params ) ) {
			return $this->error( $params->get_error_message() );
		}

		$user_id = get_current_user_id();

		// 需要验证短信
		if ( ! check_sms_code( $params['mobile'], $params['sms_code'] ) ) {
			return $this->error( '短信验证码不匹配！' );
		}

		/**
		 * 清除用户信息
		 *
		 * Meta 中的信息可能涉及商品订单之类的数据直接删除会导致系统错乱，故这里暂时只清除用户的邮箱、手机号、密码、用户名数据
		 */
		wp_update_user( array(
			'ID'            => $user_id,
			'user_nicename' => '已注销',
			'display_name'  => '已注销',
			'user_email'    => '',
			'user_pass'     => '',
			'user_url'      => '',
		) );

		delete_user_meta( $user_id, 'mobile' );
		delete_user_meta( $user_id, 'qq_openid' );

		return $this->success( '该用户已注销' );
	}

	/**
	 * 注销账号参数验证
	 *
	 * @param array $params
	 *
	 * @return array|WP_Error
	 */
	private function prepare_destroy_params( array $params ): array|WP_Error {
		$allowed = array(
			'mobile',
			'sms_code',
		);

		foreach ( $params as $key => $param ) {
			$params[ $key ] = sanitize_text_field( $param );
		}

		if ( empty( $params['mobile'] ) || empty( $params['sms_code'] ) ) {
			return new WP_Error( 'required_field_is_empty', '手机号或手机验证码为空' );
		}

		return array_filter( $params, function ( string $param ) use ( $allowed ) {
			return in_array( $param, $allowed );
		}, ARRAY_FILTER_USE_KEY );
	}

	/**
	 * 绑定邮箱
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 */
	public function bind_email( WP_REST_Request $request ): WP_REST_Response {

		$params = $this->prepare_bind_email_params( $request->get_params() );
		if ( is_wp_error( $params ) ) {
			return $this->error( $params->get_error_message() );
		}

		$user_id = get_current_user_id();

		if ( ! check_tncode() ) {
			return $this->error( '滑块验证码错误' );
		}

		// 需要验证邮箱验证码
		if ( ! check_email_code( $params['email'], $params['email_code'] ) ) {
			return $this->error( '邮箱验证码不匹配！' );
		}

		// 保存用户邮箱信息
		$r = wp_update_user( array(
			'ID'         => $user_id,
			'user_email' => $params['email'],
		) );
		if ( is_wp_error( $r ) ) {
			return $this->error( $r->get_error_message() );
		}

		return $this->success( '邮箱绑定成功' );
	}

	/**
	 * 绑定邮箱参数验证
	 *
	 * @param array $params
	 *
	 * @return array|WP_Error
	 */
	private function prepare_bind_email_params( array $params ): array|WP_Error {
		$allowed = array(
			'email',
			'email_code',
		);

		foreach ( $params as $key => $param ) {
			$params[ $key ] = sanitize_text_field( $param );
		}

		if ( empty( $params['email'] ) || empty( $params['email_code'] ) ) {
			return new WP_Error( 'required_field_is_empty', '邮箱或邮箱验证码为空' );
		}

		return array_filter( $params, function ( string $param ) use ( $allowed ) {
			return in_array( $param, $allowed );
		}, ARRAY_FILTER_USE_KEY );
	}

	/**
	 * 绑定手机
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 */
	public function bind_mobile( WP_REST_Request $request ): WP_REST_Response {

		$params = $this->prepare_bind_mobile_params( $request->get_params() );
		if ( is_wp_error( $params ) ) {
			return $this->error( $params->get_error_message() );
		}

		$user_id = get_current_user_id();

		if ( ! check_tncode() ) {
			return $this->error( '滑块验证码错误' );
		}

		// 判断手机号是否已经存在
		global $wpdb;

		$r = $wpdb->get_row( $wpdb->prepare( "select * from wp_usermeta where meta_key='mobile' and meta_value=%s", $params['mobile'] ) );
		if ( ! empty( $r ) ) {
			return $this->error( '此手机号已被使用' );
		}

		// 需要验证短信
		if ( ! check_sms_code( $params['mobile'], $params['sms_code'] ) ) {
			return $this->error( '短信验证码不匹配！' );
		}

		// 保存用户 Meta 表数据
		update_user_meta( $user_id, 'mobile', $params['mobile'] );

		return $this->success( '手机号绑定成功' );
	}

	/**
	 * 绑定手机参数验证
	 *
	 * @param array $params
	 *
	 * @return array|WP_Error
	 */
	private function prepare_bind_mobile_params( array $params ): array|WP_Error {
		$allowed = array(
			'mobile',
			'sms_code',
		);

		foreach ( $params as $key => $param ) {
			$params[ $key ] = sanitize_text_field( $param );
		}

		if ( empty( $params['mobile'] ) || empty( $params['sms_code'] ) ) {
			return new WP_Error( 'required_field_is_empty', '手机号或手机验证码为空' );
		}

		return array_filter( $params, function ( string $param ) use ( $allowed ) {
			return in_array( $param, $allowed );
		}, ARRAY_FILTER_USE_KEY );
	}

	/**
	 * 重置密码
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 */
	public function reset_passwd( WP_REST_Request $request ): WP_REST_Response {

		$params = $this->prepare_reset_passwd_params( $request->get_params() );
		if ( is_wp_error( $params ) ) {
			return $this->error( $params->get_error_message() );
		}

		$current_user = wp_get_current_user();

		if ( ! class_exists( 'PasswordHash' ) ) {
			require 'wp-includes/class-phpass.php';
		}

		$wp_hasher       = new PasswordHash( 8, true );
		$password_hashed = $current_user->user_pass;
		$plain_password  = $params['old_passwd'];
		if ( ! $wp_hasher->CheckPassword( $plain_password, $password_hashed ) ) {
			return $this->error( '旧密码不匹配，如果你未设置旧密码请留空。' );
		}

		// 更新用户密码
		wp_update_user( array( 'ID' => $current_user->ID, 'user_pass' => $params['new_passwd'] ) );

		return $this->success( '密码重置成功' );
	}

	/**
	 * 重置密码参数验证
	 *
	 * @param array $params
	 *
	 * @return array|WP_Error
	 */
	private function prepare_reset_passwd_params( array $params ): array|WP_Error {
		$allowed = array(
			'old_passwd',
			'new_passwd',
		);

		foreach ( $params as $key => $param ) {
			$params[ $key ] = sanitize_text_field( $param );
		}

		if ( empty( $params['old_passwd'] ) ) {
			return new WP_Error( 'required_field_is_empty', '旧密码不能为空' );
		}

		if ( empty( $params['new_passwd'] ) ) {
			return new WP_Error( 'required_field_is_empty', '新密码不能为空' );
		}

		return array_filter( $params, function ( string $param ) use ( $allowed ) {
			return in_array( $param, $allowed );
		}, ARRAY_FILTER_USE_KEY );
	}

}
