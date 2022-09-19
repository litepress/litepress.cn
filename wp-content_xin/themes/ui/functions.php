<?php

define( 'UI_ROOT_PATH', get_stylesheet_directory() );
define( 'UI_ROOT_URI', get_stylesheet_directory_uri() );

/**
 * TODO 调试
 */
ini_set( 'display_errors', 1 );
error_reporting( E_ERROR | E_WARNING | E_PARSE );

/**
 * 标记主题支持BBPress
 */
add_theme_support( 'bbpress' );

/**
 * 添加对woo的支持
 */
add_theme_support( 'woocommerce' );

require_once 'load.php';
