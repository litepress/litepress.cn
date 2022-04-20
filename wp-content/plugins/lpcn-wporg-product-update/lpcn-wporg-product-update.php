<?php
/**
 * Plugin Name: 更新来自 WordPress.org 的商品
 * Description: 该插件每隔半小时监控一次 WordPress 官方 SVN 仓库的变更情况，并同步产生变化的插件和主题。
 * Version: 1.0
 * Author: LitePress社区团队
 * Author URI: http://litepress.cn
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace LitePress\Store\WPOrg_Product_Update;

use LitePress\Autoload;

const PLUGIN_FILE = __FILE__;
const PLUGIN_DIR  = __DIR__;

if ( ! class_exists( '\LitePress\Autoload\Autoloader', false ) ) {
	include __DIR__ . '/vendor/litepress/autoload/class-autoloader.php';
}

Autoload\register_class_path( __NAMESPACE__, __DIR__ . '/inc' );

// 加载命令行
if ( class_exists( 'WP_CLI' ) ) {
	require __DIR__ . '/command/class-sync-wporg-svn.php';
}

Plugin::get_instance();
