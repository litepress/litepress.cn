<?php

global $blog_id;

/**
 * 注册应用发布页的静态资源
 */
add_action( 'admin_enqueue_scripts', function ( $page ) use ( $blog_id ) {
	if ( ( 'post-new.php' !== $page && 'post.php' !== $page ) || 3 !== (int)$blog_id ) {
		return;
	}

	wp_enqueue_style( 'ui-app-release', get_stylesheet_directory_uri() . '/assets/admin/css/ui-app-release.css' );
	wp_enqueue_script( 'ui-app-release', get_stylesheet_directory_uri() . '/assets/admin/js/ui-app-release.js' );
} );

/**
 * 管理后台css
 */
add_action( 'admin_enqueue_scripts', function () {
	global $current_user;

	if ( 'wc_product_vendors_admin_vendor' === $current_user->roles[0] ) {
		add_action( 'admin_footer', function () {
			echo <<<html
<style>
#coupon-root {
	display: none;
}

#toplevel_page_premmerce {
	display: none;
}

#menu-dashboard > ul {
	display: none;
}

.toplevel_page_wcpv-vendor-support {
	display: none;
}

#menu-users {
	display: none;
}
</style>
html;
		} );
	}
} );
