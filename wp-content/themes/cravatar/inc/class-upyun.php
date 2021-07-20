<?php

namespace LitePress\Cravatar\Inc;

use WP_Http;

final class Upyun {

	private string $token = UPYUN_TOKEN;

	public function get( string $path, array $params ) {
		return $this->send_request( 'GET', $path, $params );
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

		return $r['body'] ?? '';
	}

	public function post( string $path, array $params ) {
		return $this->send_request( 'POST', $path, $params );
	}

}
