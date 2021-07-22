<?php
/**
 * 该文件定义了一组帮助寒湖是
 */

namespace LitePress\Cravatar\Inc;

use LitePress\Cravatar\Inc\DataObject\Avatar_Status;
use WP_Error;

function get_email_hash( string $email ): string {
	$address = strtolower( trim( $email ) );

	return md5( $address );
}

function send_email_for_bind_email( string $address ): bool {
	$user = wp_get_current_user();

	// 生成一个随机字符串，并作为瞬态的key和邮箱绑定，这样用户点击激活后，就可以在目标页通过key取到用户邮箱，然后添加啦
	$token = md5( rand() );
	set_transient( 'email_bind_' . $token, array( 'user_id' => $user->ID, 'address' => $address ), 60000 );

	$active_url = home_url( 'emails/new?token=' . $token );

	$subject = '添加新邮箱到你的 Cravatar 账号';
	$message = <<<html
你好 {$user->display_name}:

这是你的邮箱激活地址:{$active_url}

此激活地址有效期10分钟哦
html;

	$headers[] = 'From: Cravatar <noreplay@litepress.cn>';

	return (bool) wp_mail( $address, $subject, $message, $headers );
}

function handle_email_bind( int $user_id, string $email ): bool {
	global $wpdb;

	$wpdb->replace( 'wp_9_avatar_email', array(
		'md5'     => get_email_hash( $email ),
		'email'   => $email,
		'user_id' => $user_id,
	) );

	return true;
}

function has_email( string $email ): bool {
	global $wpdb;

	$sql   = $wpdb->prepare( "SELECT md5 FROM {$wpdb->prefix}avatar_email WHERE email=%s;", $email );
	$exist = $wpdb->get_row( $sql )->md5 ?? 0;

	return ! empty( $exist );
}

/**
 * 通过邮箱的Hash地址获取用户ID
 */
function get_user_id_by_hash( string $md5 ): int {
	global $wpdb;

	$sql = $wpdb->prepare( "SELECT user_id FROM {$wpdb->prefix}avatar_email WHERE md5=%s;", $md5 );

	return $wpdb->get_row( $sql )->user_id ?? 0;
}

/**
 * 将Gravatar头像转化为本地临时文件并返回文件路径
 *
 * 如果存在缓存则直接返回缓存，否则就先从Gravatar拉取再返回缓存路径。缓存有效期为30天。
 *
 * @param string $hash
 * @param string $query
 *
 * @return string
 */
function get_gravatar_to_file( string $hash, string $query ): string {
	global $wpdb;

	$file_path = WP_CONTENT_DIR . '/cache/cravatar/' . $hash;

	/**
	 * 不存在缓存或缓存是一月前创建的就从Gravatar获取数据
	 */
	if ( ! file_exists( $file_path ) || fileatime( $file_path ) < ( time() - 2626560 ) ) {
		$url = "http://secure.gravatar.com/avatar/{$hash}" . ( ! empty( $query ) ? "?$query" : '' );

		/**
		 * 默认从Gravatar加载尺寸为400的图片，太大的话没啥用还浪费带宽
		 */
		$url = add_query_arg( array(
			's' => 400,
			'r' => 'g',
			'd' => '404',
		), $url );

		$r = wp_remote_get( $url );
		if ( is_wp_error( $r ) || ! isset( $r['body'] ) || empty( $r['body'] ) ) {
			return '';
		}

		$status_code = wp_remote_retrieve_response_code( $r );
		if ( 200 !== $status_code ) {
			return '';
		}

		$avatar = $r['body'];

		// 记录文件MD5信息方便信息审查
		$avatar_hash = md5( $avatar );
		$sql         = $wpdb->prepare( "SELECT status FROM {$wpdb->prefix}avatar_verify WHERE md5=%s;", $avatar_hash );
		if ( ! isset( $wpdb->get_row( $sql )->status ) ) {
			$wpdb->insert( $wpdb->prefix . 'avatar_verify', array(
				'md5'     => $avatar_hash,
				'user_id' => get_user_id_by_hash( $avatar_hash ),
				'url'     => explode( '?', $url )[0] ?? '',
				'status'  => Avatar_Status::WAIT,
			) );
		}

		// 最后将头像数据缓存到磁盘
		file_put_contents( $file_path, $avatar );
	}

	return $file_path;
}

/**
 * 检查给定的图片是否是给定的状态
 */
function is_status_for_avatar( string $filename, int $status ): bool {
	global $wpdb;

	if ( ! file_exists( $filename ) ) {
		return false;
	}

	$avatar_file = file_get_contents( $filename );
	$avatar_hash = md5( $avatar_file );

	$sql           = $wpdb->prepare( "SELECT status FROM {$wpdb->prefix}avatar_verify WHERE md5=%s;", $avatar_hash );
	$status_for_db = $wpdb->get_row( $sql )->status ?? 0;

	return $status === (int) $status_for_db;
}

/**
 * 获取一张默认图
 *
 * @param string $default 默认图，可以是一个图片URL，也可以是一组内置的默认图类型，具体参见函数内的 $default_types
 *
 * @return string
 */
function get_default_avatar_filename( string $default ): string {
	$default_types = array(
		'mp'        => 1,
		'ban'       => 1,
		'blank'     => 1,
		'identicon' => 1000,
		'monsterid' => 1000,
		'wavatar'   => 1000,
		'retro'     => 1000,
		'robohash'  => 1000,
	);

	$filename = CA_ROOT_PATH . '/assets/img/default-avatar/default.png';

	if ( key_exists( $default, $default_types ) ) {
		$filename = sprintf( '%s/assets/img/default-avatar/%s/%s.png', CA_ROOT_PATH, $default, rand( 1, $default_types[ $default ] ) );
	} elseif ( ! empty( $default ) ) {
		$r = wp_remote_get( $default );
		if ( ! is_wp_error( $r ) && isset( $r['body'] ) && ! empty( $r['body'] ) ) {
			$status_code = wp_remote_retrieve_response_code( $r );
			if ( 200 === $status_code ) {
				$avatar = $r['body'];

				/**
				 * 脚本结束时该临时文件会被自动删除
				 */
				$tmpfname = tempnam( sys_get_temp_dir(), '404_avatar_' );
				if ( $tmpfname ) {
					file_put_contents( $tmpfname, $avatar );

					$filename = $tmpfname;
				}
			}
		}
	}

	return $filename;
}

function get_user_emails( int $user_id ): object|array|null {
	global $wpdb;

	$sql = $wpdb->prepare( "SELECT email FROM {$wpdb->prefix}avatar_email WHERE user_id=%d;", $user_id );

	$emails = array();
	foreach ( (array) $wpdb->get_results( $sql ) as $item ) {
		$emails[] = $item->email;
	}

	return $emails;
}

function handle_email_delete( int $user_id, string $email ): WP_Error|bool {
	global $wpdb;

	// 用户主邮箱不可删除，判断是否是主邮箱
	$user = get_user_by( 'ID', $user_id );
	if ( empty( $user ) ) {
		return new WP_Error( 'Invalid user_id', '用户不存在' );
	}
	if ( $user->user_email === $email ) {
		return new WP_Error( 'Invalid email', '主邮箱不可删除' );
	}

	$wpdb->delete( 'wp_9_avatar_email', array(
		'email'   => $email,
		'user_id' => $user_id,
	) );

	return true;
}

/**
 * 用户添加或更换邮箱时主动刷新CDN缓存
 */
