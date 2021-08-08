<?php
/**
 * 静态文件引入
 */
add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('svn-browse', PLUGIN_URL . '/assets/svn-browse.css');
	wp_enqueue_style('agate', PLUGIN_URL . '/assets/agate.min.css');

    wp_enqueue_script('svn-browse', PLUGIN_URL . '/assets/svn-browse.js', ['jquery'], false, true);
	wp_enqueue_script('highlight', PLUGIN_URL . '/assets/highlight.min.js', ['jquery'], false, true);
});
