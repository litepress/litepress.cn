<?php

namespace LitePress\Cravatar\Inc\Service;

use WP_REST_Request;
use WP_REST_Response;

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
	public function get_all(): array {
		global $wpdb;

		$sql = $wpdb->prepare( "SELECT email,image_id FROM {$wpdb->prefix}avatar WHERE user_id=%d;", $this->user_id );

		$avatars = array();
		foreach ( (array) $wpdb->get_results( $sql ) as $item ) {
			$avatars[ $item->email ] = sprintf( "https://cravatar.cn/avatar/%s?s=400&r=G&d=mp", md5( $item->email ) );
		}

		return $avatars;
	}

}
