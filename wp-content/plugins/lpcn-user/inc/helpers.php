<?php

namespace LitePress\User\Inc;

use Firebase\JWT\JWT;

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

/**
 * 生成 JWT 登录 Token
 */
function generate_login_token(int $user_id): string {
	$issuedAt = time();
	$notBefore = apply_filters('jwt_auth_not_before', $issuedAt, $issuedAt);
	$expire = $issuedAt + 30;

	$token = array(
		'iss' => network_site_url(),
		'iat' => $issuedAt,
		'nbf' => $notBefore,
		'exp' => $expire,
		'data' => array(
			'user' => array(
				'id' => $user_id,
			),
		),
	);

	return JWT::encode($token, JWT_AUTH_SECRET_KEY);
}
