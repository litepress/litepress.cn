<?php

use LitePress\Cravatar\Inc\Upyun;

define( 'CA_ROOT_PATH', get_stylesheet_directory() );
define( 'CA_ROOT_URL', get_stylesheet_directory_uri() );

const CA_LOG_NAME = 'Cravatar';

require CA_ROOT_PATH . '/inc/class-upyun.php';

require CA_ROOT_PATH . '/inc/helpers.php';

require CA_ROOT_PATH . '/inc/enqueue-scripts.php';

require CA_ROOT_PATH . '/inc/DataObject/class-avatar-status.php';

require CA_ROOT_PATH . '/inc/avatar-verify.php';

require CA_ROOT_PATH . '/inc/avatar.php';

$upyun = new Upyun();
/*
$r = $upyun->get( 'flow/common_data', array(
	'start_time'  => '2021-7-1 10:0:0',
	'end_time'    => '2021-7-2 10:0:0',
	'query_type'  => 'domain',
	'query_value' => 'd.w.org.ibadboy.net',
	'flow_type'   => 'cdn',
	'flow_source' => 'cdn',
) );
*/
/*
$upyun = new Upyun();
$r = $upyun->post( 'buckets/purge/batch', array(
	'noif'  => 1,
	'source_url'    => 'https://download.wp-china-yes.net/image/*',
) );
var_dump($r);
exit;
*/

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
		return 'https://cravatar.cn/avatar/' . $hash . '?s=200&d=mp&r=' . time();
	}, 99999, 3 );
}

