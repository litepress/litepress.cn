<?php
/**
 * Plugin Name: 为WooCommerce API管理插件增加RSA验签支持
 * Description: 为了增加授权系统的安全性，所以对其增加RSA验签支持
 * Author: LitePress社区
 * Author URI:https://litepress.cn
 * Version: 1.0.0
 * License: GPLv3 or later
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace LitePress\WAMWR;

define( 'WAMWR_ROOT_PATH', plugin_dir_path( __FILE__ ) );

define( 'WAMWR_ROOT_URL', plugin_dir_url( __FILE__ ) );

require_once 'loader.php';
