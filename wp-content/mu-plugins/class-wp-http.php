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

	/**
	 * 检测坏的代理IP
	 *
	 * 并非是标记是坏的就不会再次取用，通常只有累计三次以上才会被抛弃。
	 */
	static public function check_all(): bool {
		$ips     = self::get_all_proxy_ip();
		$wp_http = new Original_WP_Http();

		foreach ( $ips as $ip ) {
			$ip = $ip['ip'] ?? '';

			$args = array(
				'headers' => array(
					'Host'            => 'wordpress.org',
					'Accept-Encoding' => 'gzip',
				),
				'method'  => 'GET',
				'timeout' => 20,
			);

			$r = $wp_http->request( 'http://' . $ip, $args );
			if ( is_wp_error( $r ) ) {
				global $wpdb;

				$wpdb->query( "update lp_proxy_ip_pool set bad_num=bad_num+1 where ip = '$ip'" );

				wp_cache_delete( 'proxy_ips' );
			}
		}

		return true;
	}

	/**
	 * 获取全部代理IP
	 */
	static private function get_all_proxy_ip(): array {
		global $wpdb;

		$ips = wp_cache_get( 'proxy_ips' );
		if ( empty( $ips ) ) {
			$sql = sprintf( 'select ip from %s where bad_num < 3', self::PROXY_IP_POOL_TABLE );
			$ips = $wpdb->get_results( $sql, ARRAY_A );

			wp_cache_set( 'proxy_ips', $ips, '', 84000 );
		}

		return $ips;
	}

	public function request( $url, $args = array() ): WP_Error|array {
		$allowed = array(
			'translate.google.cn',
			'translate.wordpress.org',
			'wordpress.org',
		);

		foreach ( $allowed as $item ) {
			preg_match( "/^https?:\/\/$item/", $url, $matches );
			if ( ! empty( $matches ) ) {
				$url                     = preg_replace( "/^(https?):\/\/$item/", '$1://' . self::get_proxy_ip(), $url );
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
	static public function get_proxy_ip(): string|false {
		$ips = self::get_all_proxy_ip();

		return $ips[ rand( 0, count( $ips ) - 1 ) ]['ip'] ?? false;
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

// 定期检查代理IP是否有损坏的
add_action( 'plugins_loaded', function () {
	global $blog_id;

	if ( 1 === (int) $blog_id ) {
		add_action( 'lpcn_check_all_proxy_ip', array( WP_Http::class, 'check_all' ) );

		$timestamp = wp_next_scheduled( 'lpcn_check_all_proxy_ip' );
		if ( empty( $timestamp ) ) {
			wp_schedule_event( time(), 'hourly', 'lpcn_check_all_proxy_ip' );
		}
	}
} );
