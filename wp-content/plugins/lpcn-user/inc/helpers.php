<?php

namespace LitePress\User\Inc;

/**
 * 通过用户 ID 登录用户
 */
function login_by_user_id( $user_id ): bool {
	wp_set_current_user( $user_id );
	wp_set_auth_cookie( $user_id, true );

	// 需要在登录成功后设置此 Cookie 以绕过 ols 的缓存
	//setcookie( '_lscache_vary', 'abc', time() + ( 365 * 24 * 60 * 60 ), '/' );

	return true;
}
