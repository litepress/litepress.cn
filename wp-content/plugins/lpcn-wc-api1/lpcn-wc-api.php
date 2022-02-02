<?php
/**
 * Plugin Name: LitePress.cn 的 WooCommerce API
 * Description: 该 API 旨在替代 WooCommerce 原版REST API
 * Version: 1.0.0
 * Author: LitePress团队
 * Author URI: http://litepress.cn
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace LitePress\WCAPI;

use LitePress\WCAPI\Inc\Plugin;
use LitePress\Autoload;

const PLUGIN_FILE       = __FILE__;
const PLUGIN_DIR        = __DIR__;

if ( ! class_exists( '\LitePress\Autoload\Autoloader', false ) ) {
	include __DIR__ . '/vendor/litepress/autoload/class-autoloader.php';
}

Autoload\register_class_path( __NAMESPACE__ . '\Inc', __DIR__ . '/inc' );
Autoload\register_class_path( __NAMESPACE__ . '\Inc\Api\Plugins', __DIR__ . '/inc/api/plugins' );
Autoload\register_class_path( __NAMESPACE__ . '\Inc\Service', __DIR__ . '/inc/service' );

include __DIR__ . '/inc/helper.php';

Plugin::get_instance();
