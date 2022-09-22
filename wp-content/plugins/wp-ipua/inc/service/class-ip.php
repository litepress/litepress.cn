<?php

namespace WePublish\IPUA\Inc\Service;

use function WePublish\IPUA\Inc\request;

/**
 * Class IP
 * 该类用于实现插件的IP属地功能
 * @package WePublish\IPUA\Inc\Service\IP
 */
class IP {

	private mixed $setting;
	private mixed $ip;
	private mixed $ip_info;

	public function __construct( $ip = "" ) {
		$this->setting = get_option( 'wp_ipua_setting' );
		$this->ip      = $ip;
	}

	public function get_ip_info() {

		if ( empty( $this->ip ) || ! is_string( $this->ip ) ) {
			return [];
		}
		if ( ! $this->get_ip_info_from_cache( $this->ip ) ) {
			$api_url    = "https://apis.map.qq.com/ws/location/v1/ip?key={$this->setting['tx_key']}&ip={$this->ip}&output=json";
			$result_raw = request( $api_url );
			// 判断是否为JSON格式
			if ( empty( $result_raw ) || ! is_string( $result_raw ) || ! is_array( json_decode( $result_raw, true ) ) ) {
				return [];
			} else {
				$result = json_decode( $result_raw, true );
			}
			// 判断响应是否正确
			if ( isset( $result['status'] ) && $result['status'] == 0 ) {
				$this->ip_info = $result['result']['ad_info'];
				$this->set_ip_info_cache( $this->ip, $this->ip_info );
			} else {
				return [];
			}

			return $this->ip_info;
		}


		return $this->ip_info;

	}

	private function get_ip_info_from_cache( $ip ): bool {
		// 通过将IP分割进行缓存，可以大大提高缓存命中率
		list( $ip1, $ip2, $ip3, $ip4 ) = explode( ".", $ip );
		$cache_key = "wp_ipua_{$ip1}_{$ip2}_{$ip3}";
		if ( wp_cache_get( $cache_key ) ) {
			$this->ip_info = wp_cache_get( $cache_key );

			return true;
		}

		return false;
	}

	private function set_ip_info_cache( $ip, $ip_info ): bool {

		// 如果用户设置了不缓存，则不缓存
		if ( $this->setting['cache'] == 'no' ) {
			return false;
		}
		// 通过将IP分割进行缓存，可以大大提高缓存命中率
		list( $ip1, $ip2, $ip3, $ip4 ) = explode( ".", $ip );
		$cache_key = "wp_ipua_{$ip1}_{$ip2}_{$ip3}";

		return wp_cache_set( $cache_key, $ip_info, $flag = '', $expire = 84000 * $this->setting['cache'] );
	}
}
