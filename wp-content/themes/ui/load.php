<?php

// 引入针对子站点的 functions 文件。他们在主题的 inc/func/sub 目录下以站点 slug 命名
global $blog_id;

$site = get_site( $blog_id );

$site = str_replace( '/', '', (string) $site?->path );

$sub_functions_path = UI_ROOT_PATH . "/inc/func/sub/$site.php";

if ( file_exists( $sub_functions_path ) ) {
	require $sub_functions_path;
}

if ( ! is_admin() ) {
	require 'inc/func/no-admin.php';
	require 'inc/enqueue-scripts.php';
	require 'inc/class-wcy-sub-menu.php';
} else {
	require 'inc/enqueue-scripts-admin.php';
}

if ( is_admin() ) {
	require 'inc/func/only-admin.php';
}

require 'inc/func/global.php';

require 'inc/bbpress/class-walker-reply.php';

/**
 * 注册所有meta box
 */
require 'inc/metabox/register.php';

/**
 * 注册所有shortcode
 */
require 'inc/shortcode/register.php';

/**
 * 引入主题专属的glotpree函数库文件
 */
require 'glotpress/helper-functions.php';

/**
 * 引入终极会员相关代码
 */
require __DIR__ . '/inc/ultimate-member/register.php';
