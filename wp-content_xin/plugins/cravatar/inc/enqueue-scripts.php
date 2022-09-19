<?php
global $blog_id;

/**
 * 全局静态文件引入
 */
add_action( 'wp_enqueue_scripts', function () {
	// wp_enqueue_style( 'CSS 文件名', PLUGIN_URL . 'static/css/CSS 文件名.css' );

	// wp_enqueue_script( 'JS 文件名', PLUGIN_URL . 'static/js/JS 文件名', [], false, true );
} );
