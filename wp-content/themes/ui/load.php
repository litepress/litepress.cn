<?php
if ( ! is_admin() ) {
	require_once 'inc/func/no-admin.php';
	require_once 'inc/enqueue-scripts.php';
	require_once 'inc/class-wcy-sub-menu.php';
} else {
	require_once 'inc/enqueue-scripts-admin.php';
}

if ( is_admin() ) {
	require_once 'inc/func/only-admin.php';
}

require_once 'inc/func/global.php';

require_once 'inc/bbpress/class-walker-reply.php';

/**
 * 注册所有meta box
 */
require_once 'inc/metabox/register.php';

/**
 * 注册所有shortcode
 */
require_once 'inc/shortcode/register.php';

/**
 * 引入主题专属的glotpree函数库文件
 */
require_once 'glotpress/helper-functions.php';

/**
 * 引入支付网关
 */
require_once 'inc/woo-pay/class-xunhu-wechat.php';

/**
 * 引入终极会员相关代码
 */
require_once __DIR__ . '/inc/ultimate-member/register.php';