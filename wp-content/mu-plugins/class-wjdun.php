<?php

namespace LitePress\Wjdun;

use LitePress\Logger\Logger;
use WP_Error;
use WP_Http;

/**
 * 乌鸡盾没有提供SDK，只能自己做一个了
 */
final class Wjdun {

	private string $key = WJDUN_KEY;
	private string $secret = WJDUN_SECRET;

	/**
	 * 按规则刷新 CDN 缓存（乌鸡盾限制每天最大 2000 条，所以非必要不使用此函数）
	 */
	public function purge_urls( array $urls ): bool {

		//[{"type":"clean_url","data":{"url":"https://cravatar.cn/"}},{"type":"clean_url","data":{"url":"https://litepress.cn/xxx/"}}]
		$req_data = array();
		foreach ( $urls as $k => $v ) {
			$req_data[ $k ]['type']        = 'clean_url';
			$req_data[ $k ]['data']['url'] = $v;
		}

		$r = $this->post( 'jobs', $req_data );

		$r_array = json_decode( $r, true ) ?? array();
		if ( ! isset( $r_array['code'] ) ) {
			Logger::error( Logger::GLOBAL, '按规则刷新乌鸡盾缓存失败：接口返回空数据', $r_array );

			return false;
		}

		if ( 0 !== (int) $r_array['code'] || '添加成功' !== (string) $r_array['msg'] ) {
			Logger::error( Logger::GLOBAL, "按规则刷新乌鸡盾缓存失败：{$r_array['msg']}", $r_array );

			return false;
		}

		return true;
	}

	/**
	 * 获取乌鸡盾的域名统计信息
	 */
	public function get_common_data( string $domain = '', string $start_time = '', string $end_time = '' ): bool|array {
		//https://user.wjdun.cn/monitor/site/realtime?type=req&start=2022-01-25%2000:00:46&end=2022-01-26%2000:00:46&domain=litepress.cn&server_port=

		$r = $this->get( 'monitor/site/realtime', array(
			'type'        => 'req',
			'start'       => $start_time,
			'end'         => $end_time,
			'domain'      => $domain,
			'server_port' => '',
		) );

		$r_array = json_decode( $r, true );
		if ( empty( $r_array ) ) {
			Logger::error( Logger::GLOBAL, '获取乌鸡盾流量数据失败：接口返回空数据', $r_array );

			return false;
		}

		if ( isset( $r_array['code'] ) && $r_array['code'] != 0 ) {
			Logger::error( Logger::GLOBAL, "获取乌鸡盾流量数据失败：{$r_array['code']}", $r_array );

			return false;
		}

		return $r_array;
	}

	private function send_request( string $method, string $path, array $params ) {
		$url = 'https://user.wjdun.cn/' . $path;

		$http = new WP_Http();

		$args = array(
			'method'  => $method,
			'headers' => array(
				'api_key'    => $this->key,
				'api_secret' => $this->secret
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
			return new WP_Error( 'wjdun_request_error', '请求乌鸡盾接口失败' );
		}

		return $r['body'] ?? '';
	}

	public function get( string $path, array $params ) {
		return $this->send_request( 'GET', $path, $params );
	}

	public function post( string $path, array $params ) {
		return $this->send_request( 'POST', $path, $params );
	}

}
