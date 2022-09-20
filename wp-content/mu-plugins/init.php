<?php

/**
 * 载入Cavalcade用来增强WordPress的Cron服务
 */

use LitePress\Logger\Logger;

require __DIR__ . '/cavalcade/plugin.php';

/**
 * 载入日志服务
 */
require __DIR__ . '/logger/logger.php';

/**
 * 载入中文格式化服务
 */
require __DIR__ . '/chinese-format/chinese-format.php';

/**
 * 加载所有依赖的第三方库
 */
require __DIR__ . '/library/loader.php';

/**
 * 载入平台自定义的工具类
 */
require __DIR__ . '/tools/loader.php';

/**
 * 新用户注册时在用户meta信息中添加邮箱的hash值（通过md5），这样方便直接通过邮箱hash索引头像
 */
add_action( 'user_register', function ( $user_id ) {
	global $wpdb;

	if ( empty( $_POST['user_email-3'] ) ) {
		return $user_id;
	}

	$address = strtolower( trim( $_POST['user_email-3'] ) );
	$wpdb->replace( 'wp_9_avatar', array(
		'md5'       => md5( $address ),
		'email'     => $address,
		'user_id'   => $user_id,
		'is_master' => 1,
	) );

	return $user_id;
} );

/**
 * 用户更新邮箱自动添加邮箱hash到cavatar的头像服务
 */
add_action( 'profile_update', function ( int $user_id, WP_User $old_user_data ) {
	global $wpdb;
	$new_user = get_user_by( 'ID', $user_id );

	$new_address = strtolower( trim( $new_user->user_email ) );
	$old_address = strtolower( trim( $old_user_data->user_email ) );

	$wpdb->replace( 'wp_9_avatar', array(
		'md5'       => md5( $new_address ),
		'email'     => $new_address,
		'user_id'   => $user_id,
		'is_master' => 1,
	) );
}, 10, 2 );

/**
 * 为用户默认分配订阅者角色（因为用户并不是在网络中的每个站点上都有用户角色）
 */
add_action( 'wp_loaded', function () {
	$user = wp_get_current_user();
	if ( ! empty( $user ) && empty( $user->roles ) ) {
		$user->set_role( 'subscriber' );
	}
} );

/**
 * 如果用户在 URL 上拼接了 login_token 查询参数，则尝试解析 token 并使用其对应的用户来登录（如果已经登录其他用户则会切换为 token 对应的用户）
 */
add_action( 'wp_loaded', function () {
	if ( empty( $_GET['login_token'] ) ) {
		return;
	}

	$login_token = sanitize_text_field( $_GET['login_token'] );

	// 解析 token
	if ( ! class_exists( 'Jwt_Auth' ) || ! class_exists( 'Jwt_Auth_Public' ) ) {
		return;
	}
	$jwt        = new Jwt_Auth();
	$jwt_public = new Jwt_Auth_Public( $jwt->get_plugin_name(), $jwt->get_version() );

	$r = $jwt_public->validate_token( false, $login_token );

	if ( is_wp_error( $r ) ) {
		Logger::warning( 'Auth', '用户在网页端使用 login_token 登录时遇到了错误', array(
			'token' => $login_token,
			'error' => $r,
		) );

		return;
	}

	$user_id = (int) $r?->data?->user?->id;
	if ( empty( $user_id ) ) {
		return;
	}

	wp_set_current_user( $user_id );
	wp_set_auth_cookie( $user_id );
} );

/**
 * 统一使用LitePress的日志服务记录PHP错误
 */
$type_str = [
	1     => 'ERROR',
	2     => 'WARNING',
	4     => 'PARSE',
	8     => 'NOTICE',
	16    => 'CORE_ERROR',
	32    => 'CORE_WARNING',
	64    => 'COMPILE_ERROR',
	128   => 'COMPILE_WARNING',
	256   => 'USER_ERROR',
	512   => 'USER_WARNING',
	1024  => 'USER_NOTICE',
	2048  => 'STRICT',
	4096  => 'RECOVERABLE_ERROR',
	8192  => 'DEPRECATED',
	16384 => 'USER_DEPRECATED',
];
// 捕获全部异常
$error_handler = set_error_handler( function ( $code, $message, $file, $line ): bool {
	global $type_str;
	Logger::error( 'PHP', '出现致命性PHP脚本错误', array(
		'type'    => $type_str[ $code ] ?? $code,
		'file'    => $file,
		'line'    => $line,
		'message' => $message,
		'server'  => $_SERVER ?? '',
	) );

	return true;
} );
set_exception_handler( function ( $exception ) {
	global $type_str;
	Logger::error( 'PHP', '出现可捕获的PHP脚本错误', array(
		'type'    => $type_str[ $exception->getCode() ] ?? $exception->getCode(),
		'file'    => $exception->getFile(),
		'line'    => $exception->getLine(),
		'message' => $exception->getMessage(),
		'trace'   => $exception->getTraceAsString(),
		'server'  => $_SERVER ?? '',
	) );
} );
register_shutdown_function(
	function () use ( $error_handler ) {
		global $type_str;
		$error = error_get_last();
		if ( ! $error ) {
			return;
		}
		Logger::error( 'PHP', '出现非致命性PHP脚本错误', array(
			'type'    => $type_str[ $error['type'] ] ?? $error['type'],
			'file'    => $error['file'],
			'line'    => $error['line'],
			'message' => $error['message'],
			'server'  => $_SERVER ?? '',
		) );

		if ( $error_handler ) {
			restore_error_handler();
		}
	}
);
