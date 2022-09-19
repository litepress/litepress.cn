<?php

use function LitePress\Cravatar\Inc\get_user_emails;
use function LitePress\Cravatar\Inc\purge_avatar_cache;

define( 'CA_ROOT_PATH', get_stylesheet_directory() );
define( 'CA_ROOT_URL', get_stylesheet_directory_uri() );

/**
 * 替换终极会员插件的gravatar头像地址
 *
 * 需要排除用于生成头像的网址
 */
if ( ! stristr( $_SERVER['REQUEST_URI'], '/avatar/' ) ) {
	add_filter( 'um_user_avatar_url_filter', function ( $url, $user_id, $data ) {
		$user = get_user_by( 'ID', $user_id );

		// 邮箱转小写并去除首尾空格
		$address = strtolower( trim( $user->user_email ) );

		// 获取邮箱的MD5哈希值
		$hash = md5( $address );

		// 拼接出最终的头像URL
		return 'https://cravatar.cn/avatar/' . $hash . '?s=200&test=1&d=mp&r=' . time();
	}, 99999, 3 );
}


/**
 * 设置标题
 */
add_filter( 'wp_title', function ( $title, $sep, $seplocation ) {
	$uri = $_SERVER['REQUEST_URI'];
	list( $uri ) = explode( '?', $uri );
	list( $uri ) = explode( '#', $uri );

	$site_title = get_bloginfo( 'name' );

	if ( '/' === $uri ) {
		$title = 'Cravatar &#8211; 互联网公共头像服务';
	} else {
		$title .= $site_title;
	}

	return $title;
}, 9999, 3 );


require CA_ROOT_PATH . '/inc/helpers.php';
require CA_ROOT_PATH . '/inc/enqueue-scripts.php';

