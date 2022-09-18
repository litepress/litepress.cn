<?php
/**
 * Plugin Name: 为Woo API管理插件在后台增加授权管理界面
 * Description: 该插件依赖Woo的API管理插件，为后台增加授权管理菜单
 * Author: LitePress社区
 * Author URI:https://litepress.cn
 * Version: 1.0.0
 * License: GPLv3 or later
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace LitePress\LM;

define( 'LM_ROOT_PATH', plugin_dir_path( __FILE__ ) );

define( 'LM_ROOT_URL', plugin_dir_url( __FILE__ ) );

require_once 'loader.php';
