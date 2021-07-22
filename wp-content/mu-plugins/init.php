<?php

/**
 * 载入Cavalcade用来增强WordPress的Cron服务
 */
require __DIR__ . '/cavalcade/plugin.php';

/**
 * 载入日志服务
 */
require __DIR__ . '/logger/logger.php';

/**
 * 新用户注册时在用户meta信息中添加邮箱的hash值（通过md5），这样方便直接通过邮箱hash索引头像
 */
add_action( 'user_register', function ( $user_id ) {
	global $wpdb;

	$address = strtolower( trim( $_POST['user_email-3'] ) );
	$wpdb->replace( 'wp_9_avatar_email', array(
		'md5'       => md5( $address ),
		'email'     => $address,
		'user_id'   => $user_id,
		'is_master' => 1,
	) );
} );

/**
 * 用户更新邮箱自动添加邮箱hash到lavatar的头像服务
 */
add_action( 'profile_update', function ( int $user_id, WP_User $old_user_data ) {
	global $wpdb;
	$new_user = get_user_by( 'ID', $user_id );

	$new_address = strtolower( trim( $new_user->user_email ) );
	$old_address = strtolower( trim( $old_user_data->user_email ) );

	$wpdb->replace( 'wp_9_avatar_email', array(
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
