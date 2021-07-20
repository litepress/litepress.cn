<?php
global $blog_id;

/**
 * 全局静态文件引入
 */
add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('bootstrap', get_stylesheet_directory_uri() . '/assets/css/bootstrap.min.css');
    wp_enqueue_style('ui-global-style', get_stylesheet_directory_uri() . '/assets/css/common.css');
    wp_enqueue_style('fontawesome', get_stylesheet_directory_uri() . '/assets/fontawesome/css/all.min.css');
    wp_enqueue_style('cropper', get_stylesheet_directory_uri() . '/assets/css/cropper.min.css');
    wp_enqueue_style('heti', get_stylesheet_directory_uri() . '/assets/css/heti.min.css');


    wp_enqueue_script('ui-global-script', get_stylesheet_directory_uri() . '/assets/js/common.js', ['jquery'], false, true);
    wp_enqueue_script('bootstrap-bundle', get_stylesheet_directory_uri() . '/assets/js/bootstrap.bundle.min.js', ['jquery'], false, true);
    wp_enqueue_script('wp-util', get_stylesheet_directory_uri() . 'wp-includes/js/wp-util.js', ['jquery'], false, true);
    wp_enqueue_script('cropper-js', get_stylesheet_directory_uri() . '/assets/js/cropper.min.js', ['jquery'], false, true);

});

class Wp_Sub_Menu extends Walker_Nav_Menu {

    function start_lvl(&$output, $depth = 0, $args = null) {
        $indent = str_repeat("\t", $depth);
        $output .= "$indent<ul class=\"dropdown-menu\">";
    }

    function end_lvl(&$output, $depth = 0, $args = null) {
        $indent = str_repeat("\t", $depth);
        $output .= "$indent</ul>";
    }

}
if ( function_exists( 'add_theme_support' ) ) {
//开启导航菜单主题支持
    add_theme_support( 'nav-menus' );
//注册导航菜单位置
    register_nav_menus( array(
        'primary_menu'  => '主菜单',
        'register_menu' => '登录菜单',
    ) );
}
/**
 * 为nav添加class
 */
add_filter( 'nav_menu_link_attributes', function ( $attr ) {
    $attr['class'] = 'nav-link';
    return $attr;
} );

/*支持 SVG 上传*/
function wp_mime_types( $mimes ) {
    $mimes['svg'] = 'image/svg+xml';

    return $mimes;
}

add_filter( 'upload_mimes', 'wp_mime_types' );


// 注册logo
add_theme_support( 'custom-logo' );
