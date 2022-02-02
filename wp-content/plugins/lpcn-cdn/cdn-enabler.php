<?php
/*
Plugin Name: LitePress.cn 的 CDN
Description: 基于 CDN Enabler 开发，主要增加了对多站点的支持
Author: LitePress 社区团队
License: GPLv2 or later
Version: 1.0.0
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// constants
define( 'CDN_ENABLER_VERSION', '2.0.4' );
define( 'CDN_ENABLER_MIN_PHP', '5.6' );
define( 'CDN_ENABLER_MIN_WP', '5.1' );
define( 'CDN_ENABLER_FILE', __FILE__ );
define( 'CDN_ENABLER_BASE', plugin_basename( __FILE__ ) );
define( 'CDN_ENABLER_DIR', __DIR__ );

// hooks
add_action( 'plugins_loaded', array( 'CDN_Enabler', 'init' ) );
register_activation_hook( __FILE__, array( 'CDN_Enabler', 'on_activation' ) );
register_uninstall_hook( __FILE__, array( 'CDN_Enabler', 'on_uninstall' ) );

// register autoload
spl_autoload_register( 'cdn_enabler_autoload' );

// load required classes
function cdn_enabler_autoload( $class_name ) {
    if ( in_array( $class_name, array( 'CDN_Enabler', 'CDN_Enabler_Engine' ) ) ) {
        require_once sprintf(
            '%s/inc/%s.class.php',
            CDN_ENABLER_DIR,
            strtolower( $class_name )
        );
    }
}

// load WP-CLI command
if ( defined( 'WP_CLI' ) && WP_CLI && class_exists( 'WP_CLI' ) ) {
    require_once CDN_ENABLER_DIR . '/inc/cdn_enabler_cli.class.php';
}
