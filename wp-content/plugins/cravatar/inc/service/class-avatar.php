<?php

namespace LitePress\Cravatar\Inc\Service;

use WP_Error;
use function LitePress\Cravatar\Inc\purge_avatar_cache;

/**
 * Class Avatar
 *
 * 用于管理头像的服务类
 *
 * @package LitePress\User\Inc\Api\Service
 */
class Avatar {

	private int $user_id;

	/**
	 * @param int $user_id
	 */
	public function __construct( int $user_id = 0 ) {
		$this->user_id = $user_id;
	}

	/**
	 * 获取当前用户的所有头像数据
	 *
	 * @return array
	 */
	public function all(): array {
		global $wpdb;

		$sql = $wpdb->prepare( "SELECT md5,email,image_id FROM {$wpdb->prefix}avatar WHERE user_id=%d;", $this->user_id );

		$now     = time();
		$avatars = array();
		foreach ( (array) $wpdb->get_results( $sql ) as $item ) {
			$avatars[] = array(
				'id'    => $item->md5,
				'email' => $item->email,
				'image' => sprintf( "/avatar/%s?s=400&r=G&d=mp&ver=" . $now, md5( $item->email ) ),
			);
		}

		return $avatars;
	}

	/**
	 * 添加头像
	 *
	 * @param string $email
	 * @param int $image_id
	 *
	 * @return bool|\WP_Error 成功返回 true，失败返回 WP_Error
	 */
	public function add( string $email, int $image_id ): bool|WP_Error {
		global $wpdb;

		/**
		 * 添加前需要执行一系列检查，任何检查不通过都无法添加此头像
		 *
		 * 1. 邮箱是否重复（如果重复还需要判断老邮箱所绑定的账号是否有效，如果原账号已失效则允许新地绑定）
		 * 2. 申请绑定的图像是否有效
		 */
		$r = $wpdb->get_row(
			$wpdb->prepare( "SELECT user_id FROM {$wpdb->prefix}avatar WHERE email=%s;", $email )
		);
		if ( ! empty( $r ) ) {
			if ( get_userdata( $r->user_id ) ) {
				return new WP_Error( 'email_already_exists', '邮箱已存在' );
			}
		}

		$image_url = wp_get_attachment_image_url( $image_id );
		if ( ! $image_url ) {
			return new WP_Error( 'image_id_invalid', '图像 ID 无效' );
		}

		// 开始绑定
		$r = $wpdb->insert( "{$wpdb->prefix}avatar", array(
			'md5'      => md5( $email ),
			'email'    => $email,
			'image_id' => $image_id,
			'user_id'  => $this->user_id,
		), array(
			'%s',
			'%s',
			'%d',
			'%d',
		) );

		if ( ! $r ) {
			return new WP_Error( 'insert_database_failed', '数据入库失败' );
		}

		return true;
	}

	/**
	 * 修改头像
	 *
	 * @param string $md5
	 * @param int $image_id
	 *
	 * @return bool|\WP_Error 成功返回 true，失败返回 WP_Error
	 */
	public function edit( string $md5, int $image_id ): bool|WP_Error {
		global $wpdb;

		$image_url = wp_get_attachment_image_url( $image_id );
		if ( ! $image_url ) {
			return new WP_Error( 'image_id_invalid', '图片 ID 无效' );
		}

		// 修改绑定
		$r = $wpdb->update( "{$wpdb->prefix}avatar", array(
			'image_id' => $image_id,
		), array(
			'md5' => $md5,
		), array(
			'%d',
		), array(
			'%s',
		) );

		if ( ! is_integer( $r ) ) {
			return new WP_Error( 'insert_database_failed', '数据更新失败' );
		}

		// 成功更换头像后需要刷新 CDN 缓存
		purge_avatar_cache( array( $md5 ), false );

		return true;
	}

	/**
	 * 删除头像
	 *
	 * @param string $md5
	 *
	 * @return bool|\WP_Error 成功返回 true，失败返回 WP_Error
	 */
	public function delete( string $md5 ): bool|WP_Error {
		global $wpdb;

		// 删除绑定
		$r = $wpdb->delete( "{$wpdb->prefix}avatar", array(
			'md5' => $md5,
		), array(
			'%s',
		) );

		if ( ! $r ) {
			return new WP_Error( 'insert_database_failed', '数据入库失败' );
		}

		return true;
	}

}
