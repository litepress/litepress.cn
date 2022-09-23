<?php
/**
 * Plugin Name: IPUA (IP属地和User-Agent插件)
 * Description: 使用腾讯位置服务(https://lbs.qq.com/location/)为你的WordPress增加IP属地及UserAgent展示
 * Author: WePublish@耗子
 * Author URI: https://hzbk.net/
 * Version: 1.3.0
 * License: GPLv3 or later
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace WePublish\IPUA;

use WePublish\IPUA\Inc\Plugin;
use WePublish\Autoload;

const VERSION     = '1.3.0';

if ( ! class_exists( '\WePublish\Autoload\Autoloader', false ) ) {
	include __DIR__ . '/vendor/wepublish/autoload/class-autoloader.php';
}

Autoload\register_class_path( __NAMESPACE__ . '\Inc', __DIR__ . '/inc' );

include __DIR__ . '/inc/helper.php';

new Plugin();
