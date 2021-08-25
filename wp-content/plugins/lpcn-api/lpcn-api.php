<?php
/**
 * Plugin Name: LitePress.cn 的对外 API
 * Description: 该 API 旨在替代 api.wordpress.org 并对其扩充
 * Version: 1.0
 * Author: LitePress社区团队
 * Author URI: http://litepress.cn
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace LitePress\API;

use LitePress\API\Inc\Plugin;
use LitePress\Autoload;

const PLUGIN_FILE = __FILE__;
const PLUGIN_DIR  = __DIR__;

if ( ! class_exists( '\LitePress\Autoload\Autoloader', false ) ) {
	include __DIR__ . '/vendor/litepress/autoload/class-autoloader.php';
}

Autoload\register_class_path( __NAMESPACE__ . '\Inc', __DIR__ . '/inc' );
Autoload\register_class_path( __NAMESPACE__ . '\Inc\Api\Plugins', __DIR__ . '/inc/api/plugins' );
Autoload\register_class_path( __NAMESPACE__ . '\Inc\Service', __DIR__ . '/inc/service' );

include __DIR__ . '/inc/helper.php';

Plugin::get_instance();
