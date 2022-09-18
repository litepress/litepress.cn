<?php
/**
 * Plugin Name: 从 WordPress.org 导入文档
 * Description: 该插件从 WordPress.org 分别导入用户使用文档和开发文档并分别填充到对应的站点中
 * Version: 1.0
 * Author: LitePress社区团队
 * Author URI: http://litepress.cn
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace LitePress\Docs\Import;

use LitePress\Autoload;

const PLUGIN_FILE = __FILE__;
const PLUGIN_DIR  = __DIR__;

if ( ! class_exists( '\LitePress\Autoload\Autoloader', false ) ) {
	include __DIR__ . '/vendor/litepress/autoload/class-autoloader.php';
}

Autoload\register_class_path( __NAMESPACE__, __DIR__ . '/inc' );

Plugin::get_instance();