<?php

add_action( 'wp_loaded', function () {
	list( $uri ) = explode( '?', $_SERVER['REQUEST_URI'] );
	if ( '/user/sso/login' === $uri ) {
		?>
        这里是登录页面
		<?php
        exit;
	}
} );