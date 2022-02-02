<?php

namespace LitePress\Upyun;

use LitePress\Logger\Logger;
use WP_Error;
use WP_Http;

/**
 * 又拍云官方提供的 SDK 功能太多了，考虑到需求的应用场景并不复杂，所以干脆自己实现一个
 */
final class Upyun {

	private string $token = UPYUN_TOKEN;

	/**
	 * 按规则刷新 CDN 缓存（又拍云限制每天最大 1000 条，所以非必要不使用此函数）
	 * 另外此函数的函数名取错了，但是不改了，怕改了一个地方引起一堆 BUG
	 */
	public function batch_purge( array $urls ): bool {
		$r = $this->post( 'buckets/purge/batch', array(
			'noif'       => 1,
			'source_url' => join( PHP_EOL, $urls ),
		) );

		$r_array = json_decode( $r, true )[0] ?? array();
		if ( ! isset( $r_array['code'] ) ) {
			Logger::error( Logger::GLOBAL, '按规则刷新又拍云CDN缓存失败：接口返回空数据', $r_array );

			return false;
		}

		if ( 1 !== (int) $r_array['code'] ) {
			Logger::error( Logger::GLOBAL, "按规则刷新又拍云CDN缓存失败：{$r_array['status']}", $r_array );

			return false;
		}

		return true;
	}

	public function post( string $path, array $params ) {
		return $this->send_request( 'POST', $path, $params );
	}

	private function send_request( string $method, string $path, array $params ) {
		$url = 'https://api.upyun.com/' . $path;

		$http = new WP_Http();

		$args = array(
			'method'  => $method,
			'headers' => array(
				'Authorization' => 'Bearer ' . $this->token,
			),
		);

		if ( 'POST' === $method ) {
			$args['headers']['Content-Type'] = 'application/json';
			$args['body']                    = json_encode( $params );
		} else {
			$url = add_query_arg( $params, $url );
		}

		$r = $http->request( $url, $args );
		if ( is_wp_error( $r ) ) {
			return new WP_Error( 'upyun_request_error', '请求又拍云 CDN 接口失败' );
		}

		return $r['body'] ?? '';
	}

	/**
	 * 刷新 CDN 缓存
	 */
	public function purge( array $urls ): bool {
		$r = $this->post( 'purge', array(
			'urls' => join( PHP_EOL, $urls ),
		) );

		$r_array = json_decode( $r, true )['result'][0] ?? array();
		if ( ! isset( $r_array['code'] ) ) {
			Logger::error( Logger::GLOBAL, '刷新又拍云CDN缓存失败：接口返回空数据', $r_array );

			return false;
		}

		if ( 1 !== (int) $r_array['code'] ) {
			Logger::error( Logger::GLOBAL, "刷新又拍云CDN缓存失败：{$r_array['status']}", $r_array );

			return false;
		}

		return true;
	}

	public function get_common_data( string $domain = '', string $start_time = '', string $end_time = '' ): bool|array {
		$r = $this->get( 'flow/common_data', array(
			'start_time'  => $start_time,
			'end_time'    => $end_time,
			'query_type'  => 'domain',
			'query_value' => $domain,
			'flow_type'   => 'cdn',
			'flow_source' => 'cdn',
		) );

		$r_array = json_decode( $r, true );
		if ( empty( $r_array ) ) {
			Logger::error( Logger::GLOBAL, '获取又拍云 CDN 流量数据失败：接口返回空数据', $r_array );

			return false;
		}

		if ( isset( $r_array['error_code'] ) ) {
			Logger::error( Logger::GLOBAL, "获取又拍云 CDN 流量数据失败：{$r_array['message']}", $r_array );

			return false;
		}

		return $r_array;
	}

	public function get( string $path, array $params ) {
		return $this->send_request( 'GET', $path, $params );
	}

}
