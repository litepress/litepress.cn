<?php
/**
 * Plugin name: GlotPress路由定制
 * Description: GlotPress默认是不支持分页等基本功能的，需要重定义路由并重写控制器
 * Version: 1.0
 * Author: WP中国本土化社区
 * Author URI:https://wp-china.org/
 * License: GPLv3 or later
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

require_once 'routes/project.php';
require_once 'routes/index.php';
require_once 'inc/class-local.php';

add_action( 'wp_print_styles', function () {
    // wp_deregister_style('astra-theme-css');
});

add_action( 'plugins_loaded', function () {

    add_action( 'template_redirect', function () {

        GP::$router->prepend("/", ['WPCY_Route_Index', 'index']);
        GP::$router->prepend("/projects/(plugins|themes|docs|wordpress)", ['WPCY_Route_Project', 'single']);

    }, 5 );
} );
