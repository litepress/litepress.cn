<?php
/**
 * 头像管理页面
 */

use const LitePress\Cravatar\PLUGIN_DIR;

add_filter( 'wp_title_parts', function ( $title ) {
	$title[0] = '头像管理';

	return $title;
} );

get_header();

if ( is_user_logged_in() ) {
	readfile( PLUGIN_DIR . '/frontend/dist/index.html' );
} else {
	echo <<<HTML
<main class="main-body flex-fill align-items-center d-flex">
    <div class="container">
        <div class="row">
            <section class="text-center w-100 ">
                <h4 class="display-1 mb-1"></h4>
                <h3 class="text-gray-soft text-regular mb-4">你需要 <a href="/login">登录</a> 才能添加头像! 😋</h3>
            </section>
        </div>
    </div>
</main>
HTML;
}

get_footer();
