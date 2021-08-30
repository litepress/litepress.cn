<?php
/**
 * Plugin Name: 自定义GlotPress
 * Description: 为GlotPress添加细节权限控制、翻译格式化等有用的小功能
 * Version: 1.0
 * Author: LitePress社区团队
 * Author URI: http://litepress.cn
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace LitePress\GlotPress\Customizations;

use LitePress\Autoload;
use LitePress\GlotPress\Customizations\Inc\Plugin;

const PLUGIN_FILE = __FILE__;
const PLUGIN_DIR  = __DIR__;

if ( ! class_exists( '\LitePress\Autoload\Autoloader', false ) ) {
	include __DIR__ . '/vendor/litepress/autoload/class-autoloader.php';
}

Autoload\register_class_path( __NAMESPACE__ . '\Inc', __DIR__ . '/inc' );

// 这自动装载有 BUG，懒得查了，先手工引入吧
// 这个 BUG 似乎是因为 GlotPress 使用某种机制引用回调，导致未触发自动装载
require __DIR__ . '/inc/routes/index.php';
require __DIR__ . '/inc/routes/project.php';
require __DIR__ . '/inc/class-local.php';
require __DIR__ . '/inc/project-card.php';

Plugin::get_instance();
