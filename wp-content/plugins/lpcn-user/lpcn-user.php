<?php
/**
 * Plugin Name: LitePress.cn 网页端的用户登录、注册 API
 * Description: 该插件提供一组 API 处理网页端用户认证授权相关的事务
 * Version: 1.0
 * Author: LitePress社区团队
 * Author URI: http://litepress.cn
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace LitePress\User;

use LitePress\Autoload;
use LitePress\User\Inc\Plugin;

const PLUGIN_FILE = __FILE__;
const PLUGIN_DIR  = __DIR__;

if ( ! class_exists( '\LitePress\Autoload\Autoloader', false ) ) {
	include __DIR__ . '/vendor/litepress/autoload/class-autoloader.php';
}

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/inc/helpers.php';
require __DIR__ . '/inc/pages/user-bind.php';
require __DIR__ . '/inc/oauth/qq/api/qqConnectAPI.php';
require __DIR__ . '/inc/oauth/qq/index.php';
require __DIR__ . '/inc/oauth/qq/callback.php';

Autoload\register_class_path( __NAMESPACE__ . '\Inc', __DIR__ . '/inc' );
Autoload\register_class_path( __NAMESPACE__ . '\Inc\Api', __DIR__ . '/inc/api' );

Plugin::get_instance();
