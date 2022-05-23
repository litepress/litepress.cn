<?php

namespace LitePress\Cravatar\Inc\Service;

use WP_Error;

/**
 * Class Image
 *
 * 用于管理图片的服务类
 *
 * @package LitePress\User\Inc\Api\Service
 */
class Image {

	private int $user_id;

	/**
	 * @param int $user_id
	 */
	public function __construct( int $user_id = 0 ) {
		$this->user_id = $user_id;
	}

	/**
	 * 获取当前用户的所有图片数据
	 *
	 * @return array
	 */
	public function all(): array {
		$args        = array(
			'post_type'   => 'attachment',
			'numberposts' => 20, // 每个用户最多只允许托管 20 张图片
		);
		$attachments = get_posts( $args );

		$images = array();
		foreach ( $attachments as $attachment ) {
			$images[ $attachment->ID ] = $attachment->guid;
		}

		return $images;
	}

	/**
	 * 添加图片
	 *
	 * @param array $file 前端表单上传的文件信息
	 *
	 * @return bool|\WP_Error 成功返回 true，失败返回 WP_Error
	 */
	public function add( array $file ): bool|WP_Error {
		global $wpdb;

		// 每个用户最多允许上传 20 个图片，这是为了防止个别人拿平台当图床用。
		$sql   = $wpdb->prepare( "SELECT COUNT(*) FROM wp_9_posts WHERE post_type = 'attachment' AND post_status = 'inherit' AND post_author = %d", $this->user_id );
		$count = $wpdb->get_var( $sql );
		if ( (int) $count >= 20 ) {
			return new WP_Error( 'exceeding_the_limit', '为防止滥用，每用户最多添加 20 张图片，请删除旧图片后再添加新图。' );
		}

		if ( ! function_exists( 'wp_handle_upload' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/file.php' );
		}

		$upload_overrides = array(
			'test_form' => false
		);
		$move_file        = wp_handle_upload( $file, $upload_overrides );
		if ( $move_file && ! isset( $move_file['error'] ) ) {
			$upload_id = wp_insert_attachment( array(
				'guid'           => $move_file['url'],
				'post_mime_type' => $move_file['type'],
				'post_title'     => preg_replace( '/\.[^.]+$/', '', $file['name'] ),
				'post_content'   => '',
				'post_status'    => 'inherit'
			), $move_file['file'] );

			if ( is_wp_error( $upload_id ) ) {
				return $upload_id;
			}

			if ( 0 === $upload_id ) {
				return new WP_Error( 'upload_image_failed', '未知原因导致图片数据插入数据库失败，请联系管理员处理。' );
			}
		} else {
			return new WP_Error( 'upload_image_failed', '图片保存失败：' . $move_file['error'] );
		}

		return true;
	}

	/**
	 * 删除图片
	 *
	 * @param int $image_id
	 *
	 * @return bool|\WP_Error 成功返回 true，失败返回 WP_Error
	 */
	public function delete( int $image_id ): bool|WP_Error {
		$image = get_post( $image_id );
		if ( empty( $image ) ) {
			return new WP_Error( 'delete_image_failed', '不是有效的图片 ID' );
		}
		if ( $this->user_id !== (int) $image->post_author ) {
			return new WP_Error( 'delete_image_failed', '你无权操作此图片' );
		}

		$r = wp_delete_attachment( $image_id );
		if ( ! $r ) {
			return new WP_Error( 'delete_image_failed', '图片因未知原因删除失败，请联系管理员处理。' );
		}

		return true;
	}

}
