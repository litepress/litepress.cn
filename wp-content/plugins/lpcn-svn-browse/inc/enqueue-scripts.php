<?php
/**
 * 静态文件引入
 */
add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('svn-browse', PLUGIN_URL . '/assets/svn-browse.css');

    wp_enqueue_script('svn-browse', PLUGIN_URL . '/assets/svn-browse.js', ['jquery'], false, true);
});
