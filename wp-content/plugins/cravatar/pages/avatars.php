<?php
/**
 * 头像管理页面
 */

use const LitePress\Cravatar\PLUGIN_DIR;

get_header();

readfile( PLUGIN_DIR . '/frontend/dist/index.html' );

get_footer();
