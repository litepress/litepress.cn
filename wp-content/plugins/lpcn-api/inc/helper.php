<?php

namespace LitePress\API;

use LitePress\WP_Http\WP_Http;
use WP_Error;

function request_wporg( string $path ): WP_Error|array {
	$http = new WP_Http();

	// 统一去除字符串转义，否则数据无法被 JSON 解析
	$post = array();
	foreach ( $_POST ?: array() as $k => $v ) {
		$post[ $k ] = stripcslashes( $v );
	}

	return $http->request(
		'http://23.105.218.231' . $path,
		array(
			'method'  => $_SERVER['REQUEST_METHOD'],
			'body'    => $post,
			'timeout' => 20,
			'headers' => array(
				'Host'       => 'api.w.org.ibadboy.net',
				'User-Agent' => $_SERVER['HTTP_USER_AGENT']
			)
		)
	);
}
