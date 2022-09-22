<?php

namespace WePublish\IPUA\Inc;

use WP_Http;
use WP_Error;
use WePublish\IPUA\Inc\Service\IP as IP;
use WePublish\IPUA\Inc\Service\UA as UA;

function request( string $url, string $method = 'GET' ): WP_Error|string {
	$http = new WP_Http();

	// 统一去除字符串转义，否则数据无法被 JSON 解析
	foreach ( $method == 'post' ?: array() as $k => $v ) {
		$post[ $k ] = stripcslashes( $v );
	}

	return $http->request(
		$url,
		array(
			'method'      => $method,
			'body'        => $post ?? null,
			'timeout'     => 20,
			'httpversion' => '1.1',
			'sslverify'   => false
		)
	)['body'];
}

function get_ip_address( $ip = "" ): ?string {
	if ( empty( $ip ) ) {
		try {
			global $comment;
			if ( ! isset( $comment->comment_author_IP ) ) {
				return '自动获取IP失败';
			}
			$ip = $comment->comment_author_IP;
		} catch ( \Exception $e ) {
			return '自动获取IP失败';
		}
	}

	$ips     = new IP( $ip );
	$ip_info = $ips->get_ip_info();
	$setting = get_option( 'wp_ipua_setting' );
	if ( ! is_array( $setting ) || empty( $setting ) ) {
		return '你需要先在后台配置插件';
	}

	$ip_info['nation']   = $ip_info['nation'] ?? '';
	$ip_info['province'] = $ip_info['province'] ?? '';
	$ip_info['city']     = $ip_info['city'] ?? '';

	return match ( $setting['ip_format'] ) {
		'n-p-c' => $ip_info['nation'] . ' ' . $ip_info['province'] . ' ' . $ip_info['city'],
		'np' => $ip_info['nation'] . $ip_info['province'],
		'np2' => ( $ip_info['nation'] == '中国' ? '' : $ip_info['nation'] ) . $ip_info['province'],
		'n-p  ' => $ip_info['nation'] . ' ' . $ip_info['province'],
		'pc' => $ip_info['province'] . $ip_info['city'],
		'p-c' => $ip_info['province'] . ' ' . $ip_info['city'],
		'n' => $ip_info['nation'],
		'p' => $ip_info['province'],
		'c' => $ip_info['city'],
		default => $ip_info['nation'] . $ip_info['province'] . $ip_info['city'],
	};
}

function get_ua_info( $ua = "" ): ?string {

	if ( empty( $ua ) ) {
		try {
			global $comment;
			if ( ! isset( $comment->comment_agent ) ) {
				return '自动获取UA失败';
			}
			$ua = $comment->comment_agent;
		} catch ( \Exception $e ) {
			return '自动获取UA失败';
		}
	}

	$uas     = new UA( $ua );
	$ua_info = $uas->get_ua_info();
	$setting = get_option( 'wp_ipua_setting' );
	if ( ! is_array( $setting ) || empty( $setting ) ) {
		return '你需要先在后台配置插件';
	}

	$ua_info['platform'] = $ua_info['platform'] ?? "";
	$ua_info['browser']  = $ua_info['browser'] ?? "";
	$ua_info['version']  = $ua_info['version'] ?? "";

	return match ( $setting['ua_format'] ) {
		'p' => $ua_info['platform'],
		'b' => $ua_info['browser'],
		default => $ua_info['platform'] . ' ' . $ua_info['browser'],
	};
}
