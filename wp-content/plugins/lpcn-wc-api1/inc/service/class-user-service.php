<?php

namespace LitePress\WCAPI\Inc\Service;

/**
 * Class User_Service
 *
 * 核心服务
 *
 * @package LitePress\WCAPI\Inc\Service
 */
class User_Service {

//	public function update_check( array $params ): array {
//
//		$update_exists = array();
//
//		// 版本号对比（支持SP/Beta版本号判断）
//		if ( ( version_compare( strtolower( $params['version'] ), LITEPRESS_VERSION, '>=' ) && ! strpos( LITEPRESS_VERSION, 'SP' ) && ! strpos( $params['version'], 'SP' ) && ! strpos( $params['version'], 'Beta' ) ) || ( version_compare( strtolower( $params['version'] ), LITEPRESS_VERSION . 'RC', '>=' ) && strpos( $params['version'], 'SP' ) ) || ( version_compare( strtolower( $params['version'] ), LITEPRESS_VERSION . 'Beta', '>=' ) && strpos( $params['version'], 'Beta' ) ) ) {
//			$update_exists[0] = array(
//				"response"        => "latest",
//				"download"        => 'https://dl-cdn.haozi.xyz/litepress/LitePress-' . LITEPRESS_VERSION . '.zip',
//				"locale"          => "zh_CN",
//				"packages"        => array(
//					"full"        => 'https://dl-cdn.haozi.xyz/litepress/LitePress-' . LITEPRESS_VERSION . '.zip',
//					"no_content"  => 'https://dl-cdn.haozi.xyz/litepress/LitePress-' . LITEPRESS_VERSION . '.zip',
//					"new_bundled" => false,
//					"partial"     => false,
//					"rollback"    => false
//				),
//				"current"         => LITEPRESS_VERSION,
//				"version"         => LITEPRESS_VERSION,
//				"php_version"     => "5.6.20",
//				"mysql_version"   => "5.0",
//				"new_bundled"     => "5.6",
//				"partial_version" => false
//			);
//		} elseif ( ( version_compare( strtolower( $params['version'] ), LITEPRESS_VERSION, '>' ) && strpos( LITEPRESS_VERSION, 'SP' ) && ! strpos( $params['version'], 'SP' ) && version_compare( strtolower( $params['php'] ), 7.3, '>=' ) && version_compare( strtolower( $params['mysql'] ), 5.5, '>=' ) ) || ( version_compare( strtolower( $params['version'] ), LITEPRESS_VERSION, '<' ) && version_compare( strtolower( $params['php'] ), 7.3, '>=' ) && version_compare( strtolower( $params['mysql'] ), 5.5, '>=' ) ) ) {
//			$update_exists[0] = array(
//				"response"        => "development",
//				"download"        => 'https://dl-cdn.haozi.xyz/litepress/LitePress-' . LITEPRESS_VERSION . '.zip',
//				"locale"          => "zh_CN",
//				"packages"        => array(
//					"full"        => 'https://dl-cdn.haozi.xyz/litepress/LitePress-' . LITEPRESS_VERSION . '.zip',
//					"no_content"  => false,
//					"new_bundled" => false,
//					"partial"     => false,
//					"rollback"    => false
//				),
//				"current"         => LITEPRESS_VERSION,
//				"version"         => LITEPRESS_VERSION,
//				"php_version"     => "5.6.20",
//				"mysql_version"   => "5.0",
//				"new_bundled"     => "5.6",
//				"partial_version" => false
//			);
//			$update_exists[1] = array(
//				"response"        => "upgrade",
//				"download"        => 'https://dl-cdn.haozi.xyz/litepress/LitePress-' . LITEPRESS_VERSION . '.zip',
//				"locale"          => "zh_CN",
//				"packages"        => array(
//					"full"        => 'https://dl-cdn.haozi.xyz/litepress/LitePress-' . LITEPRESS_VERSION . '.zip',
//					"no_content"  => 'https://dl-cdn.haozi.xyz/litepress/LitePress-' . LITEPRESS_VERSION . '.zip',
//					"new_bundled" => false,
//					"partial"     => false,
//					"rollback"    => false
//				),
//				"current"         => LITEPRESS_VERSION,
//				"version"         => LITEPRESS_VERSION,
//				"php_version"     => "5.6.20",
//				"mysql_version"   => "5.0",
//				"new_bundled"     => "5.6",
//				"partial_version" => false
//			);
//			$update_exists[2] = array(
//				"response"        => "autoupdate",
//				"download"        => 'https://dl-cdn.haozi.xyz/litepress/LitePress-' . LITEPRESS_VERSION . '.zip',
//				"locale"          => "zh_CN",
//				"packages"        => array(
//					"full"        => 'https://dl-cdn.haozi.xyz/litepress/LitePress-' . LITEPRESS_VERSION . '.zip',
//					"no_content"  => 'https://dl-cdn.haozi.xyz/litepress/LitePress-' . LITEPRESS_VERSION . '.zip',
//					"new_bundled" => false,
//					"partial"     => false,
//					"rollback"    => false
//				),
//				"current"         => LITEPRESS_VERSION,
//				"version"         => LITEPRESS_VERSION,
//				"php_version"     => "5.6.20",
//				"mysql_version"   => "5.0",
//				"new_bundled"     => "5.6",
//				"partial_version" => false,
//				"new_files"       => true
//			);
//		}
//
//		return $update_exists;
//	}
}
