<?php
global $blog_id;

/**
 * 全局静态文件引入
 */
add_action( 'wp_enqueue_scripts', function () {
	wp_enqueue_style( 'bootstrap', get_stylesheet_directory_uri() . '/assets/css/bootstrap.min.css' );
	wp_enqueue_style( 'ui-global-style', get_stylesheet_directory_uri() . '/assets/css/ui-global-style.css' );
	wp_enqueue_style( 'fontawesome', get_stylesheet_directory_uri() . '/assets/fontawesome/css/all.min.css' );
	wp_enqueue_style( 'agate', get_stylesheet_directory_uri() . '/assets/css/agate.min.css' );
	wp_enqueue_style( 'heti', get_stylesheet_directory_uri() . '/assets/css/heti.min.css' );
	wp_enqueue_style( 'ui-member', get_stylesheet_directory_uri() . '/assets/css/ui-member.css' );
	wp_enqueue_style( 'nprogress', get_stylesheet_directory_uri() . '/assets/css/nprogress.min.css' );
	wp_enqueue_style( 'lightgallery', get_stylesheet_directory_uri() . '/assets/css/lightgallery-bundle.min.css' );

	wp_enqueue_script( 'lightgallery', get_stylesheet_directory_uri() . '/assets/js/lightgallery.umd.js', [], false, true );
	wp_enqueue_script( 'nprogress', get_stylesheet_directory_uri() . '/assets/js/nprogress.min.js', [], false, true );
	wp_enqueue_script( 'highlight', get_stylesheet_directory_uri() . '/assets/js/highlight.min.js', [], false, true );
	wp_enqueue_script( 'clipboard', get_stylesheet_directory_uri() . '/assets/js/clipboard.min.js', [], false, true );
	wp_enqueue_script( 'ui-global-script', get_stylesheet_directory_uri() . '/assets/js/ui-global-script.js', [ 'jquery' ], false, true );
	wp_enqueue_script( 'bootstrap-bundle', get_stylesheet_directory_uri() . '/assets/js/bootstrap.bundle.min.js', [ 'jquery' ], false, true );
} );

/**
 * bbpress静态文件引入，同时去除其自带的样式
 */
add_filter( 'bbp_default_styles', function () {
	wp_enqueue_style( 'ui-bbpress', get_stylesheet_directory_uri() . '/assets/css/ui-bbpress.css' );
	wp_enqueue_script( 'ui-bbpress', get_stylesheet_directory_uri() . '/assets/js/ui-bbpress.js', [
		'jquery',
		'bootstrap-bundle',
		'highlight',
		'clipboard'
	], false, true );

	return array();
} );

/**
 * 翻译平台静态文件引入
 */
add_action( 'gp_init', function () {
	wp_enqueue_style( 'ui-glotpress', get_stylesheet_directory_uri() . '/assets/css/ui-glotpress.css' );
} );

/**
 * Woo商城静态文件引入
 */
add_action( 'woocommerce_init', function () {
	wp_enqueue_style( 'ui-woo', get_stylesheet_directory_uri() . '/assets/css/ui-woo.css' );
	wp_enqueue_script( 'ui-woo', get_stylesheet_directory_uri() . '/assets/js/ui-woo.js', [ 'jquery' ], false, true );
	wp_enqueue_script( 'urljs', get_stylesheet_directory_uri() . '/assets/js/url.min.js', [], false, true );
} );

/**
 * 应用市场“我的账户”页静态文件引入
 */
add_action( 'woocommerce_account_content', function () {
	wp_enqueue_style( 'ui-woo-my-account', get_stylesheet_directory_uri() . '/assets/css/ui-woo-my-account.css' );
} );

/**
 * 删除终极会员的css和js
 */
/*
add_action('wp_enqueue_scripts', function () {
	wp_deregister_style('um_fonticons_ii');
	wp_deregister_style('um_fonticons_fa');
	wp_deregister_style('um_crop');
	wp_deregister_style('um_tipsy');
	wp_deregister_style('um_raty');
	wp_deregister_style('select2');
	wp_deregister_style('um_fileupload');
	wp_deregister_style('um_datetime');
	wp_deregister_style('um_datetime_date');
	wp_deregister_style('um_datetime_time');
	wp_deregister_style('um_scrollbar');
	wp_deregister_style('um_rtl');
	wp_deregister_style('um_default_css');
	wp_deregister_style('um_modal');
	wp_deregister_style('um_responsive');
	wp_deregister_style('um_styles');
	wp_deregister_style('um_ui');
	wp_deregister_style('um_members');
	wp_deregister_style('um_profile');
	wp_deregister_style('um_account');
	wp_deregister_style('um_misc');



	wp_deregister_script( 'select2');
	wp_deregister_script( 'um_scrollbar');
	wp_deregister_script( 'um_jquery_form');
	wp_deregister_script( 'um_fileupload');
	wp_deregister_script( 'um_datetime');
	wp_deregister_script( 'um_datetime_date');
	wp_deregister_script( 'um_datetime_time');
	wp_deregister_script('um_datetime_locale');
	wp_deregister_script( 'um_tipsy');
	wp_deregister_script( 'um_raty');
	wp_deregister_script( 'um_crop');
	wp_deregister_script( 'um_modal');
	wp_deregister_script('um_functions');
	wp_deregister_script( 'um_responsive');
	wp_deregister_script( 'um-gdpr');
	wp_deregister_script('um_conditional');
	wp_deregister_script('um_scripts');
	wp_deregister_script( 'um_scripts');
	wp_deregister_script('um_dropdown');
	wp_deregister_script('um_members');
	wp_deregister_script('um_profile');
	wp_deregister_script('um_account');
	wp_deregister_script( 'um_gchart');
}, 999);
*/

add_action( 'wp_enqueue_scripts', function () {
	if ( ! function_exists( 'is_product_category' ) || ! function_exists( 'is_product_tag' ) ) {
		return;
	}

	global $wp;
	if ( is_product_category() || is_product_tag() || isset( $wp->query_vars['wcpv_product_vendors'] ) ) {
		$data = <<<html
	    $.each(categories,function (i,val) {
	        var urlsearch = $(location).attr('search');
	        var url_noparm = location.protocol + '//' + location.host + location.pathname;
	        var url_noparm4 = url_noparm.split("/").splice(0, 4).join("/");
	        var urlsearch_price =   urlsearch.split(/(?=&)/g).splice(1, 2).join("");
	        var urlsearch_category =   urlsearch.split("&").splice(0, 1).join("");
	        var content = "";
	        console.log(val);
	        if (is_taxonomy == 1){
	            content+="<li><a "+"class='categories-a' "+"href='"+url_noparm+"?category="+val.slug+urlsearch_price+"'"+ "rel='"+ val.slug +"'>" +val.name+"</a></li>";
	            $(".filter-categories .all").remove(); 
	            $(".filter-categories  .categories-a:first").addClass("active"); 
	        }
	        else {
	            content+="<li><a "+"class='categories-a' "+"href='"+url_noparm4+"/"+val.slug+urlsearch+"'"+ "rel='"+ val.slug +"'>" +val.name+"</a></li>";
	        }
	
	        $('.filter-categories .all a').attr("href",url_noparm4+urlsearch);
	        $('.filter-cost .all a').attr("href",url_noparm);
	        $('.filter-categories .filter-cost-ul').append(content);
	    });
	    $(".categories-a").each(function(){
	        if(this.href==window.location.href){
	            $(this).addClass("active").parent("li").siblings().find("a").removeClass('active');
	        }
	        $(this).on('click',function () {
	           $(this).addClass("active").parent("li").siblings().find("a").removeClass('active');
	        })
	    });
	    $(".filter-cost a").each(function(){
	        var urlsearch = $(location).attr('search');
	        var rel = $(this).attr('rel');
	        if(urlsearch.indexOf(rel) >= 0){
	            $(this).addClass("active").parent("li").siblings().find("a").removeClass('active');
	        }
	        $(this).on('click',function () {
	           $(this).addClass("active").parent("li").siblings().find("a").removeClass('active');
	        })
	    });
html;
		wp_add_inline_script( 'ui-woo', $data );
	}
} );


// 翻译平台依赖的静态资源
if ( 4 === (int) $blog_id ) {
	add_action( 'wp_enqueue_scripts', function () {
		wp_enqueue_script( 'ui-glotpress', get_stylesheet_directory_uri() . '/assets/js/ui-glotpress.js', [], false, true );
	} );
}

// 开发文档依赖的静态资源
if ( 6 === (int) $blog_id ) {
	add_action( 'wp_enqueue_scripts', function () {
		wp_enqueue_style( 'ui-developer', get_stylesheet_directory_uri() . '/assets/css/ui-developer.css', array( 'wedocs-styles' ) );
		wp_enqueue_script( 'ui-developer', get_stylesheet_directory_uri() . '/assets/js/ui-developer.js', array( 'jquery' ) );
	} );
}


// 需求统计依赖的静态资源
if ( 8 === (int) $blog_id ) {
	add_action( 'wp_enqueue_scripts', function () {
		wp_enqueue_style( 'needs', get_stylesheet_directory_uri() . '/assets/css/needs.css' );
	} );
}
