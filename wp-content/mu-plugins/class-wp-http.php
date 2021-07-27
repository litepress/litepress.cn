<?php
/**
 * Plugin Name: 为特性域名增加代理支持的WP_Http类
 * Description: 该类是WP官方的WP_Http的子类，考虑到代理节点建立的方便性，目前采用HTTP反代，当前支持的域名包括：translate.google.cn|translate.wordpress.org|wordpress.org
 * Version: 1.0
 * Author: LitePress社区团队
 * Author URI: http://litepress.cn
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace LitePress\WP_Http;

use WP_Error;
use WP_Http as Original_WP_Http;

class WP_Http extends Original_WP_Http {

	const PROXY_IP_POOL_TABLE = 'lp_proxy_ip_pool';

	public function request( $url, $args = array() ): WP_Error|array {
		$allowed = array(
			'translate.google.cn',
			'translate.wordpress.org',
			'wordpress.org',
		);

		foreach ( $allowed as $item ) {
			if ( stristr( $url, $item ) ) {
				$url                     = str_replace( $item, $this->get_proxy_ip(), $url );
				$args['headers']['Host'] = $item;
			}
		}

		return parent::request( $url, $args );
	}

	/**
	 * 轮询返回代理池中的IP
	 *
	 * @return string|false
	 */
	private function get_proxy_ip(): string|false {
		global $wpdb;

		$ips = wp_cache_get( 'proxy_ips' );
		if ( empty( $ips ) ) {
			$ips = $wpdb->get_results( 'select ip from ' . self::PROXY_IP_POOL_TABLE );

			wp_cache_set( 'proxy_ips', $ips, '', 84000 );
		}

		return $ips[ rand( 0, count( $ips ) - 1 ) ]->ip ?? false;
	}

}

/**
 * 重定义一组Helper函数
 */

function _wp_http_get_object(): ?WP_Http {
	static $http = null;

	if ( is_null( $http ) ) {
		$http = new WP_Http();
	}

	return $http;
}

function wp_remote_get( $url, $args = array() ): WP_Error|array {
	$args['reject_unsafe_urls'] = true;
	$args['method']             = 'GET';
	$http                       = _wp_http_get_object();

	return $http->request( $url, $args );
}

function wp_remote_post( $url, $args = array() ): WP_Error|array {
	$args['reject_unsafe_urls'] = true;
	$args['method']             = 'POST';
	$http                       = _wp_http_get_object();

	return $http->request( $url, $args );
}
