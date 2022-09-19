<?php
/**
 * Plugin Name: Cravatar
 * Description: 提供类似 Gravatar 的头像托管服务
 * Version: 1.0
 * Author: LitePress社区团队
 * Author URI: http://litepress.cn
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace LitePress\Cravatar;

use LitePress\Autoload;
use LitePress\Router;
use LitePress\Cravatar\Inc\Plugin;

const PLUGIN_FILE = __FILE__;
const PLUGIN_DIR  = __DIR__;
define( 'PLUGIN_URL', plugin_dir_url( __FILE__ ) );

if ( ! class_exists( '\LitePress\Autoload\Autoloader', false ) ) {
	include __DIR__ . '/vendor/litepress/autoload/class-autoloader.php';
}

if ( ! class_exists( '\LitePress\Router\Router', false ) ) {
	include __DIR__ . '/vendor/litepress/router/class-router.php';
}

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/inc/helpers.php';
require __DIR__ . '/inc/enqueue-scripts.php';

Autoload\register_class_path( __NAMESPACE__ . '\Inc', __DIR__ . '/inc' );
Autoload\register_class_path( __NAMESPACE__ . '\Inc\Api', __DIR__ . '/inc/api' );

Router\register_route( '/sso/login', PLUGIN_DIR . '/inc/pages/login.php' );
Router\register_route( '/avatars', PLUGIN_DIR . '/pages/avatars.php' );

Plugin::get_instance();
