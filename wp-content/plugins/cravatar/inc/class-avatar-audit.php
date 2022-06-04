<?php

namespace LitePress\Cravatar\Inc;

use LitePress\Cravatar\Inc\DataObject\Avatar_Status;

class Avatar_Audit {

	private static ?Avatar_Audit $instance = null;

	public static function get_instance(): Avatar_Audit {
		if ( ! ( self::$instance instanceof Avatar_Audit ) ) {
			self::$instance = new Avatar_Audit();
		}

		return self::$instance;
	}

	public function worker( string $url, string $image_md5, string $email_hash ): void {
		$q_cloud = new Q_Cloud();

		$r = $q_cloud->sensitive_content_recognition( 'litepress-backup-1254444452.cos.ap-beijing.myqcloud.com', urlencode( $url ) );

		/**
		 * 如果验证不通过
		 */
		if ( ! $r ) {
			global $wpdb;

			// 将拦截状态更新到数据库
			$wpdb->update( $wpdb->prefix . 'avatar_verify', array(
				'status' => Avatar_Status::BAN,
			), array(
				'image_md5' => $image_md5,
			) );

			// 刷新 CDN 缓存，以使下次请求回源，方便命中拦截。
			purge_avatar_cache( array( $email_hash ), false );
		}
	}

}
