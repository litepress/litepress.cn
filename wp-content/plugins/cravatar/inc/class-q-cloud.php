<?php

namespace LitePress\Cravatar\Inc;

use LitePress\Logger\Logger;
use WP_Http;

/**
 * 腾讯云 SDK 封装
 */
final class Q_Cloud {

	private string $accessKey = Q_CLOUD_ACCESS_Key;

	private string $secretKey = Q_CLOUD_SECRET_Key;

	public function sensitive_content_recognition( string $host, string $detect_url ): bool {
		$url = "https://$host/";

		$http = new WP_Http();

		$args = array(
			'method'  => 'GET',
			'headers' => array(
				'Authorization' => $this->createAuthorization( 'GET', '/' ),
			),
			'timeout' => 10,
		);

		$url = add_query_arg( array(
			'ci-process'  => 'sensitive-content-recognition',
			'detect-type' => 'porn,politics',
			'detect-url'  => $detect_url,
		), $url );

		$r = $http->request( $url, $args );
		if ( is_wp_error( $r ) ) {
			Logger::error(
				'Q_Cloud',
				'敏感内容识别接口请求失败：' . $r->get_error_message(),
				array(
					'url'  => $url,
					'data' => $args
				)
			);

			return true;
		}

		$body = wp_remote_retrieve_body( $r );

		$xml_obj = simplexml_load_string( $body );

		$verified = json_decode( json_encode( $xml_obj ), true );

		$status_code = wp_remote_retrieve_response_code( $r );
		if ( WP_Http::OK !== $status_code ) {
			Logger::error(
				'Q_Cloud',
				'敏感内容识别接口请求失败，接口返回状态码为：' . $status_code,
				array(
					'url'  => $url,
					'data' => $args,
					'r'    => $body,
				)
			);

			return true;
		}

		if (
			! key_exists( 'PornInfo', $verified ) ||
			! key_exists( 'PoliticsInfo', $verified )
		) {
			Logger::error(
				'Q_Cloud',
				'敏感内容识别接口返回了非预期的值',
				array(
					'url'  => $url,
					'data' => $args,
					'r'    => $verified,
				)
			);

			return true;
		}

		/**
		 * 按腾讯云文档代码应该三个级别：0 正常 1 敏感 2 疑似。这里把敏感和疑似都按敏感处理
		 */
		if (
			0 !== (int) $verified['PornInfo']['HitFlag'] ||
			0 !== (int) $verified['PoliticsInfo']['HitFlag']
		) {
			return false;
		} else {
			return true;
		}
	}

	private function createAuthorization( string $method, string $path, $expires = '+30 minutes' ): string {
		if ( is_null( $expires ) || ! strtotime( $expires ) ) {
			$expires = '+30 minutes';
		}
		$signTime = ( time() - 60 ) . ';' . strtotime( $expires );

		$httpString       = strtolower( $method ) . "\n" . urldecode( $path ) . "\n\n\n";
		$sha1edHttpString = sha1( $httpString );
		$stringToSign     = "sha1\n$signTime\n$sha1edHttpString\n";
		$signKey          = hash_hmac( 'sha1', $signTime, $this->secretKey );
		$signature        = hash_hmac( 'sha1', $stringToSign, $signKey );

		return 'q-sign-algorithm=sha1&q-ak=' . $this->accessKey .
		       "&q-sign-time=$signTime&q-key-time=$signTime&" .
		       "q-header-list=&q-url-param-list=&q-signature=$signature";
	}
}
