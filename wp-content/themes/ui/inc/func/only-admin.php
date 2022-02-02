<?php

global $blog_id;

if ( function_exists( 'add_theme_support' ) ) {
//开启导航菜单主题支持
	add_theme_support( 'nav-menus' );
//注册导航菜单位置
	register_nav_menus( array(
		'primary_menu'  => '主菜单',
		'register_menu' => '登录菜单',
		'forum_menu'    => '论坛菜单'
	) );
}

/**
 * 删除Woo自带的Meta Box
 */
add_action( 'add_meta_boxes', function () {
	remove_meta_box( 'woocommerce-product-data', 'product', 'normal' );
	remove_meta_box( 'postexcerpt', 'product', 'normal' );

	/**
	 * 删除产品类型meta box
	 */
	remove_meta_box( 'product_catdiv', 'product', 'side' );

	/**
	 * 删除供应商列表meta，这个傻逼meta每次加载几万条供应商数据
	 */
	remove_meta_box( 'wcpv_product_vendorsdiv', 'product', 'side' );
}, 9999 );

add_filter( 'wpseo_accessible_post_types', function ( $args ) {
	unset( $args['product'] );

	return $args;
} );

/**
 * 禁掉傻逼供应商插件的快速编辑产品供应商功能，这鸟功能直接把页面卡死了
 */
add_filter( 'wp_terms_checklist_args', function ( $args ) {
	if ( $args['taxonomy'] === 'wcpv_product_vendors' ) {
		$args = array(
			'descendants_and_self' => 0,
			'selected_cats'        => false,
			'popular_cats'         => false,
			'walker'               => null,
			'taxonomy'             => 'category',
			'checked_ontop'        => true,
			'echo'                 => false,
		);
	}

	return $args;
}, 9999 );

/**
 * 傻逼SEO插件处理分类信息的时候略过傻逼供应商插件
 */
add_filter( 'wpseo_primary_term_taxonomies', function ( $all_taxonomies, $post_type ) {
	unset( $all_taxonomies['wcpv_product_vendors'] );

	return $all_taxonomies;
}, 999, 3 );

/**
 * 供应商只能查看自己创建的优惠券
 */
add_filter( 'pre_get_posts', function ( WP_Query $obj ) {
	if ( key_exists( 'post_type', $obj->query_vars ) && 'shop_coupon' === $obj->query_vars['post_type'] && ! current_user_can( 'level_10' ) ) {
		$obj->query_vars['author'] = get_current_user_id();

		return $obj;
	}

	return $obj;
} );

/**
 * 该特性暂时移除，还需要寻找更好的解决方案
 *
 * 2021年11月1日
 */
/*
add_action( 'save_post_product', function ( $post_id, WP_Post $post, $update ) {
	global $wpdb;

	if ( isset( $_SERVER['HTTP_REFERER'] ) &&
	     (
		     stristr( $_SERVER['HTTP_REFERER'], '/store/wp-admin/post.php' ) ||
		     stristr( $_SERVER['HTTP_REFERER'], '/store/wp-admin/post-new.php' )
	     )
	) {
		$pos = strpos( $post->post_name, 'lp-' );
		if ( $pos !== 0 ) {
			// 这里手工操作数据库，否则会触发递归，造成死循环
			$wpdb->update( $wpdb->prefix . 'posts', array(
				'post_name'   => 'lp-' . $post->post_name,
			), array(
				'ID' => $post->ID,
				'post_type'   => 'product',
				'post_status' => 'publish',
			) );
		}
	}

}, 9999, 3 );
*/

/**
 * 恢复链接管理功能
 */
add_filter( 'pre_option_link_manager_enabled', '__return_true' );


/**
 * 添加百度统计代码
 */
add_action( 'admin_footer', function () {
	echo <<<html
<script>
var _hmt = _hmt || [];
(function() {
  var hm = document.createElement("script");
  hm.src = "https://hm.baidu.com/hm.js?b09919b0ba91f0ea1f1f6d62c3c78a1f";
  var s = document.getElementsByTagName("script")[0]; 
  s.parentNode.insertBefore(hm, s);
})();
</script>
html;
} );

// 在管理后台禁止解析简码，否则诸如 ep 等插件无法正常运行
remove_all_shortcodes();
