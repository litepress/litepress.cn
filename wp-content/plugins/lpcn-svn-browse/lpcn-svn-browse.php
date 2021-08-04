<?php
/**
 * Plugin Name: SVN文件浏览
 * Description: 该插件只用于浏览SVN文件而不是存储。文件的来源分别是：plugins.svn.wordpress.org|themes.svn.wordpress.org
 * Version: 1.0
 * Author: LitePress社区团队
 * Author URI: http://litepress.cn
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace LitePress\SVN_Browse;

use LitePress\Autoload;

const PLUGIN_FILE = __FILE__;
const PLUGIN_DIR  = __DIR__;
define( 'PLUGIN_URL', plugins_url( '', __FILE__ ) );

if ( ! class_exists( '\LitePress\Autoload\Autoloader', false ) ) {
	include __DIR__ . '/vendor/litepress/autoload/class-autoloader.php';
}

include __DIR__ . '/inc/enqueue-scripts.php';

Autoload\register_class_path( __NAMESPACE__, __DIR__ . '/inc' );

Plugin::get_instance();
