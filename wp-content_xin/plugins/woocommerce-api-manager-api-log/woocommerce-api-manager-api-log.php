<?php
/**
 * Plugin Name: 为WooCommerce API管理插件增加API日志支持
 * Description: 日志保留7天，可按要求进行查询
 * Author: LitePress社区
 * Author URI:https://litepress.cn
 * Version: 1.0.0
 * License: GPLv3 or later
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace LitePress\WAMAL;

define( 'WAMAL_ROOT_PATH', plugin_dir_path( __FILE__ ) );

define( 'WAMAL_ROOT_URL', plugin_dir_url( __FILE__ ) );

require_once 'loader.php';
