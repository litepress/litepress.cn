<?php

namespace LitePress\Cravatar\Inc;

use LitePress\Upyun\Upyun;
use LitePress\Wjdun\Wjdun;

/**
 * 主动 CDN 刷新缓存
 *
 * 缓存包括CDN及本地磁盘中的缓存
 */
function purge_avatar_cache( array $emails, bool $purge_local = true, bool $only_local = false, $type = 'gravatar' ) {
	$urls        = array();
	$local_paths = array();
	foreach ( $emails as $email ) {
		// 只有当传入的是邮箱时才进行 Hash，否则直接使用其值
		if ( stristr( $email, '@' ) ) {
			$address = strtolower( trim( $email ) );
			$hash    = md5( $address );
		} else {
			$hash = $email;
		}

		// 如果 Hash 为空则跳过，否则会导致所有缓存被清空，从而压垮源站。
		if ( empty( $hash ) ) {
			continue;
		}

		$local_paths[] = "/www/cravatar-cache/$type/$hash.png";
		$urls[]        = "https://cravatar.cn/avatar/{$hash}*";
	}

	// 先刷新本地缓存
	if ( $purge_local ) {
		foreach ( $local_paths as $local_path ) {
			if ( file_exists( $local_path ) ) {
				unlink( $local_path );
			}
		}
	}

	// 最后刷新乌鸡盾缓存
	if ( ! $only_local ) {
		$wjdun = new Wjdun();
		$wjdun->purge_urls( $urls );
	}
}

/**
 * 获取昨日的 CDN 的统计分析数据
 */
function get_last_day_cdn_analysis() {
	$data = wp_cache_get( 'last_day_cdn_analysis', 'cravatar' );
	if ( ! $data ) {
		$start_time = date( 'Y-m-d', time() - 86400 );
		$end_time   = date( 'Y-m-d', time() );

		$upyun = new Upyun();
		$data  = $upyun->get_common_data( 'cravatar.cn', $start_time, $end_time );
		if ( ! $data || ! is_array( $data ) ) {
			return false;
		}

		$wjdun = new Wjdun();
		$data2 = $wjdun->get_common_data( 'cravatar.cn', $start_time . '%2000:00:00', $end_time . '%2000:00:00' );
		if ( ! $data2 || ! is_array( $data2 ) || $data2['code'] != 0 ) {
			return false;
		}

		$req_num = 0;
		$bytes   = 0;

		foreach ( $data as $item ) {
			if ( isset( $item['reqs'] ) ) {
				$req_num += $item['reqs'];
			}

			if ( isset( $item['bytes'] ) ) {
				$bytes += $item['bytes'];
			}
		}

		foreach ( $data2['data'] as $item ) {
			if ( isset( $item[1] ) ) {
				$req_num += $item[1];
			}
		}

		// 之所以返回数组，是因为将来可能还会返回其他数据
		$data = array(
			'req_num' => $req_num,
		);
		wp_cache_set( 'last_day_cdn_analysis', $data, 'cravatar', 43200 );
	}

	return $data;
}
