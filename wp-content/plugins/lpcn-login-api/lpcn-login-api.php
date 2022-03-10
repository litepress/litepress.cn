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

namespace LitePress\Login_Api;

use LitePress\Autoload;

const PLUGIN_FILE = __FILE__;
const PLUGIN_DIR  = __DIR__;

if ( ! class_exists( '\LitePress\Autoload\Autoloader', false ) ) {
	include __DIR__ . '/vendor/litepress/autoload/class-autoloader.php';
}

Autoload\register_class_path( __NAMESPACE__, __DIR__ . '/inc' );

Plugin::get_instance();
