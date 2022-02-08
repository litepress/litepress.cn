<?php
/**
 * 细化控制每个站点加载的网络插件
 *
 * @author 孙锡源 <sxy@ibadboy.net>
 * @version 1.0.0
 */


// 因为 EP 需要在网络状态下启用 Woo 插件才能正常执行索引，但启用后所有站点都将加载 Woo 代码，无疑会增加系统负担，所以这里只为应用市场启用
add_filter( 'site_option_active_sitewide_plugins', function ( $value ) {
	global $blog_id;

	/**if ( 3 !== (int) $blog_id && 1 !== (int) $blog_id ) {
	 * unset( $value['woocommerce/woocommerce.php'] );
	 * }
	 */


	return $value;
} );

// 为论坛前台取消激活 Woo ，它会和区块编辑器加载冲突
add_filter( 'option_active_plugins', function ( $value ) {
	global $blog_id;

	if ( ! is_admin() && 1 === (int) $blog_id ) {
		$key = array_search( 'woocommerce/woocommerce.php', $value );

		if ( $key || 0 === $key ) {
			unset( $value[ $key ] );
		}
	}

	return $value;
} );
