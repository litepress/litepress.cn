<?php
/**
 * 该文件定义了一组帮助寒湖是
 */

namespace LitePress\Cravatar\Inc;

use LitePress\Cravatar\Inc\DataObject\Avatar_Status;
use LitePress\Logger\Logger;
use WP_Error;

/**
 * 通过哈希值尝试获取用户的QQ邮箱
 */
function get_qq_for_hash( string $hash ): string|false {
	$table = 'email_hash_' . ( hexdec( substr( $hash, 0, 10 ) ) ) % 5001 + 1;

	$conn = mysqli_connect( LOW_DB_HOST, LOW_DB_USER, LOW_DB_PASSWORD, LOW_DB_NAME );

	$sql   = "select qq from {$table} where md5='{$hash}';";
	$query = mysqli_query( $conn, $sql );
	if ( is_bool( $query ) ) {
		Logger::error( 'Cravatar', 'QQ邮箱数据库查询返回布尔变量', array(
			'hash' => $hash,
			'sql'  => $sql,
		) );

		return false;
	}
	$row = mysqli_fetch_array( $query, MYSQLI_ASSOC );

	if ( isset( $row['qq'] ) && ! empty( $row['qq'] ) ) {
		return (string) $row['qq'];
	}

	return false;
}

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

function get_gravatar_to_file( string $hash, string $query ): string {
	$url = "http://sdn.geekzu.org/avatar/{$hash}" . ( ! empty( $query ) ? "?$query" : '' );

	/**
	 * 默认从Gravatar加载尺寸为400的图片，太大的话没啥用还浪费带宽
	 */
	$url = add_query_arg( array(
		's'       => 400,
		'size'    => 400,
		'r'       => 'g',
		'rating'  => 'g',
		'd'       => '404',
		'default' => '404',
	), $url );

	return get_avatar_to_file( $hash, $url, 'gravatar' );
}

function get_qqavatar_to_file( string $hash, string $qq ): string {
	$url = "http://q1.qlogo.cn/g?b=qq&nk={$qq}&s=640";

	add_filter( 'avatar_is_404', function ( $is_404, $avatar_hash ): bool {
		if ( 'bad9cbb852b22fe58e62f3f23c7d63d2' === $avatar_hash ||
		     'acef72340ac0e914090bd35799f5594e' === $avatar_hash ) {
			return true;
		}

		return false;
	}, 10, 2 );

	/**
	 * 有一部分 QQ 头像可能是因为腾讯服务器 BUG 的原因，导致在 100 清晰度下是最佳显示效果，但是在 640 清晰度下则显示出了几十分辨率的屎。
	 *
	 * 比如：
	 * http://q1.qlogo.cn/g?b=qq&nk=1327444568&s=100
	 * http://q1.qlogo.cn/g?b=qq&nk=1327444568&s=640
	 *
	 * 所以这里判断一下，如果通过 640 尺寸获取到的图的实际大小小于 100 则转而获取尺寸未 100 的图
	 */
	$file_path = get_avatar_to_file( $hash, $url, 'qq' );
	list( $width, $height, $type, $attr ) = getimagesize( $file_path );

	if ( ! empty( $width ) && 100 > (int) $width ) {
		$url = "http://q1.qlogo.cn/g?b=qq&nk={$qq}&s=100";

		// 重新获取图片之前需要先清除本地缓存
		purge_avatar_cache( array( $hash ), true, true, 'qq' );

		return get_avatar_to_file( $hash, $url, 'qq' );
	} else {
		return $file_path;
	}
}

/**
 * 将远程头像转化为本地临时文件并返回文件路径
 *
 * 如果存在缓存则直接返回缓存，否则就先从给定的URL拉取再返回缓存路径。缓存有效期为15天。
 *
 * @param string $hash
 * @param string $url
 *
 * @return string
 */
function get_avatar_to_file( string $hash, string $url, string $type = 'gravatar' ): string {
	global $wpdb;

	$file_path = "/www/cravatar-cache/$type/$hash.png";

	/**
	 * 不存在缓存或缓存是15天前创建的就从Gravatar获取数据
	 *
	 * 这里缓存时间15天是因为CDN缓存时间为30天，避免CDN回源时命中本地缓存造成数据被缓存60天
	 */
	if ( ! file_exists( $file_path ) || filemtime( $file_path ) < ( time() - 1313280 ) ) {
		$r = wp_remote_get( $url );
		if ( is_wp_error( $r ) || ! isset( $r['body'] ) || empty( $r['body'] ) ) {
			return '';
		}

		$status_code = wp_remote_retrieve_response_code( $r );
		if ( 200 !== (int) $status_code ) {
			return '';
		}
		$avatar = $r['body'];

		// 记录文件MD5信息方便信息审查
		$avatar_hash = md5( $avatar );

		// 有些时候可能要根据文件的md5值决定是否当前是否返回的是404，比如说QQ的头像接口就总是返回一个默认图
		if ( apply_filters( 'avatar_is_404', false, $avatar_hash ) ) {
			return '';
		}

		// 最后将头像数据缓存到磁盘
		file_put_contents( $file_path, $avatar );
	}

	return $file_path;
}

/**
 * 检查给定的图片是否是给定的状态
 */
function is_status_for_avatar( string $email_hash, string $filename, int $status, string $type ): bool {
	global $wpdb;

	if ( ! file_exists( $filename ) ) {
		return false;
	}

	$avatar_file = file_get_contents( $filename );
	$avatar_hash = md5( $avatar_file );

	$sql = $wpdb->prepare( "SELECT status FROM {$wpdb->prefix}avatar_verify WHERE image_md5=%s;", $avatar_hash );
	$r   = $wpdb->get_row( $sql );

	/**
	 * 如果数据库中未记录该张图的话就记录并异步进行违规图检测
	 *
	 * 如果图片被检测为违规图的话，会主动刷新CDN缓存，使下次回源时命中违规标志
	 */
	if ( empty( $r ) ) {
		$avatar_url = "https://cravatar.cn/avatar/$email_hash.png?s=400";

		$sql = $wpdb->prepare( "SELECT status FROM {$wpdb->prefix}avatar_verify WHERE image_md5=%s;", $avatar_hash );
		if ( ! isset( $wpdb->get_row( $sql )->status ) ) {
			$wpdb->insert( $wpdb->prefix . 'avatar_verify', array(
				'image_md5' => $avatar_hash,
				'user_id'   => get_user_id_by_hash( $avatar_hash ),
				'url'       => $avatar_url,
				'type'      => $type,
				'status'    => Avatar_Status::WAIT,
			) );
		}

		//do_action( 'lpcn_sensitive_content_recognition', $avatar_url, $avatar_hash, $email_hash );
		$timestamp = wp_next_scheduled( 'lpcn_sensitive_content_recognition' );
		if ( empty( $timestamp ) ) {
			wp_schedule_single_event( time() + 10, 'lpcn_sensitive_content_recognition', array(
				'url'        => $avatar_url,
				'image_md5'  => $avatar_hash,
				'email_hash' => $email_hash,
			) );
		}

		return false;
	} else {
		$status_for_db = $r->status;
	}

	return $status === (int) $status_for_db;
}

/**
 * 检查违规图
 *
 * @param string $url
 */
function sensitive_content_recognition( string $url, string $image_md5, string $email_hash ): void {
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

/**
 * 获取一张默认图
 *
 * @param string $default 默认图，可以是一个图片URL，也可以是一组内置的默认图类型，具体参见函数内的 $default_types
 *
 * @return string
 */
function get_default_avatar_filename( string $default ): string {
	// mp有几个别名，需要特别处理下
	$default = match ( $default ) {
		'mm' => 'mp',
		'mystery' => 'mp',
		default => $default,
	};

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
		// 只有当用户给定的默认图中包含 .jpg、.jpeg、.gif、.png 时才尝试获取此默认图
		if ( str_contains( $default, '.jpg' ) ||
		     str_contains( $default, '.jpeg' ) ||
		     str_contains( $default, '.gif' ) ||
		     str_contains( $default, '.png' )
		) {
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
 * 主动刷新缓存
 *
 * 缓存包括CDN及本地磁盘中的缓存
 */
function purge_avatar_cache( array $emails, bool $purge_local = true, bool $only_local = false, $type = 'gravatar' ) {
	$urls        = array();
	$local_paths = array();
	foreach ( $emails as $email ) {
		// 只有当传入的是邮箱时才进行 Hash，否则直接使用其值
		if ( stristr( $email, '@' ) ) {
			$address = strtolower( trim( $email ) );
			$hash    = md5( $address );
		} else {
			$hash = $email;
		}

		// 如果 Hash 为空则跳过，否则会导致所有缓存被清空，从而压垮源站。
		if ( empty( $hash ) ) {
			continue;
		}

		$local_paths[] = "/www/cravatar-cache/$type/$hash.png";
		$urls[]        = "https://cravatar.cn/avatar/{$hash}*";
	}

	// 先刷新本地缓存
	if ( $purge_local ) {
		foreach ( $local_paths as $local_path ) {
			if ( file_exists( $local_path ) ) {
				unlink( $local_path );
			}
		}
	}

	// 然后按URL规则刷新又拍云缓存
	if ( ! $only_local ) {
		$upyun = new Upyun();
		$r     = $upyun->post( 'buckets/purge/batch', array(
			'noif'       => 1,
			'source_url' => join( PHP_EOL, $urls ),
		) );

		$r_array = json_decode( $r, true )[0] ?? array();
		if ( ! isset( $r_array['code'] ) ) {
			Logger::error( CA_LOG_NAME, '刷新又拍云CDN缓存失败：接口返回空数据', $r_array );
		}

		if ( 1 !== (int) $r_array['code'] ) {
			Logger::error( CA_LOG_NAME, "刷新又拍云CDN缓存失败：{$r_array['status']}", $r_array );
		}
	}
}

