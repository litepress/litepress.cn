<?php

namespace LitePress\GlotPress\MT;

use LitePress\Logger\Logger;
use LitePress\WP_Http\WP_Http;
use function LitePress\WP_Http\wp_remote_get;

/**
 * 代理
 *
 * 此类将获取代理IP
 */
class Proxy {

	/**
	 * 从html中过滤出ip地址
	 */
	private function filter_ip( string $data ) {

		preg_match_all( '/<td>(((2(5[0-5]|[0-4]\d))|[0-1]?\d{1,2})(\.((2(5[0-5]|[0-4]\d))|[0-1]?\d{1,2})){3})<\/td>/', $data, $ips );

		return $ips[1];

	}

	/**
	 * 从html中过滤出端口
	 */
	private function filter_port( string $data ): array {

		preg_match_all( '/<td>(\d{1,5})<\/td>/', $data, $ports );

		return $ports[1];

	}

	/**
	 * 获取快代理的ip列表并过滤返回
	 */
	public function get_ip(): array {

		$request = new WP_Http;

		$response = $request->request( 'http://www.66ip.cn/areaindex_33/1.html', array( 'method'     => 'GET',
		                                                                                'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/98.0.4758.80 Safari/537.36 Edg/98.0.1108.50'
		) );

		if ( $response['body'] ?? false ) {

			$ips   = $this->filter_ip( $response['body'] );
			$ports = $this->filter_port( $response['body'] );

			return array_combine( $ips, $ports );
		} else {

			return array();
		}
	}

}
