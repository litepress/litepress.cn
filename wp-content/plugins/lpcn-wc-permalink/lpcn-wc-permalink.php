<?php
/**
 * Plugin Name: WooCommerce 的固定链接修改
 * Description: 该插件为 LitePress.cn 定制了一套固定链接规则
 * Version: 1.0
 * Author: LitePress社区团队
 * Author URI: http://litepress.cn
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace LitePress\WC_Permalink;

use LitePress\Autoload;

const PLUGIN_FILE = __FILE__;
const PLUGIN_DIR  = __DIR__;

if ( ! class_exists( '\LitePress\Autoload\Autoloader', false ) ) {
	include __DIR__ . '/vendor/litepress/autoload/class-autoloader.php';
}

Autoload\register_class_path( __NAMESPACE__, __DIR__ . '/inc' );

Plugin::get_instance();
