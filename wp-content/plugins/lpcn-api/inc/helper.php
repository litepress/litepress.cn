<?php

namespace LitePress\API;

use LitePress\WP_Http\WP_Http;
use WP_Error;

function request_wporg(): WP_Error|array {
	$http = new WP_Http();

	return $http->request(
		'http://23.105.218.231' . add_query_arg( array() ),
		array(
			'method'  => $_SERVER['REQUEST_METHOD'],
			'body'    => $_POST,
			'timeout' => 20,
			'headers' => array(
				'Host'       => 'api.w.org.ibadboy.net',
				'User-Agent' => $_SERVER['HTTP_USER_AGENT']
			)
		)
	);
}
