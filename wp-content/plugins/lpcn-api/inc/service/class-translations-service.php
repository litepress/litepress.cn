<?php

namespace LitePress\API\Inc\Service;

use JetBrains\PhpStorm\ArrayShape;

/**
 * Class Translations_Service
 *
 * 主题服务
 *
 * @package LitePress\API\Inc\Service
 */
class Translations_Service {

	public function update_check( array $projects, array $translations, string $query_type, string $display_type ): array {
		global $wpdb;

		$update_exists = array();

		$domains = array();
		foreach ( $projects as $domain => $version ) {
			$domains[] = $wpdb->prepare( '%s', $domain );
		}

		$domains = join( ',', $domains );
		if ( 'core' === $query_type ) {
			$version = current( $projects );
			$sql     = $wpdb->prepare( "select domain, version, updated from language_packs where type=%s and version=%s and active=1", $query_type, $version );
		} else {
			$sql = $wpdb->prepare( "select domain, version, updated from language_packs where type=%s and domain in ({$domains}) and active=1", $query_type, );
		}
		$r = $wpdb->get_results( $sql, ARRAY_A );

		/**
		 * 初始从数据库中取出的数据需要进一步处理，因为其中会同时存在一个应用的多个版本，例如：
		 *
		 * {
		 * "domain":"advanced-gutenberg",
		 * "version":"2.10.3",
		 * "updated":"2021-11-01 14:50:25"
		 * },
		 * {
		 * "domain":"advanced-gutenberg",
		 * "version":"2.9.2",
		 * "updated":"2021-09-06 16:08:02"
		 * },
		 */
		$translate_packs_tmp = array();
		foreach ( $r as $item ) {
			$translate_packs_tmp[ $item['domain'] ][] = $item;
		}

		// 再次对其处理，每一项只保留最新一组数据
		$translate_packs = array();
		foreach ( $translate_packs_tmp as $domain => $item ) {
			$prior_sub_item = array();
			foreach ( $item as $sub ) {
				if ( strtotime( $prior_sub_item['updated'] ?? 0 ) < strtotime( $sub['updated'] ?? 0 ) ) {
					$prior_sub_item = $sub;
				}
			}

			$translate_packs[ $domain ] = $prior_sub_item;
		}

		if ( 'core' === $query_type ) {
			/**
			 * 发行版核心的翻译检查逻辑与插件的不一样
			 * 发行版的包中包含四个子项目，只有当所有子项目的最后修改时间都小于当前数据库中的项目最后更新时间时才判定为存在更新
			 * 另外，虽然是四个子项目，但在数据库中是统一记录为一个的，所以数据库中的最后修改时间也只有一个，也就是四个时间与这一个时间对比
			 */
			// 发行版的翻译只会检索出一条活跃数据，所以也没必要遍历数组，直接取第一个即可
			$item = current( $translate_packs );
			if ( $item ) {
				$core_is_update = true;

				foreach ( $translations as $translation ) {
					$request_item              = $translation['zh_CN'] ?? array();
					$request_item_last_updated = $request_item['PO-Revision-Date'] ?? '';

					if ( strtotime( $request_item_last_updated ) >= strtotime( $item['updated'] ) ) {
						$core_is_update = false;
					}
				}

				if ( $core_is_update ) {
					$update_exists[] = $this->prepare_db_translation_info( $item, $display_type );
				}
			}
		} else {
			foreach ( $translate_packs as $domain => $item ) {
				$request_item              = $translations[ $domain ]['zh_CN'] ?? array();
				$request_item_last_updated = $request_item['PO-Revision-Date'] ?? '';

				if ( strtotime( $request_item_last_updated ) < strtotime( $item['updated'] ) ) {
					$update_exists[] = $this->prepare_db_translation_info( $item, $display_type );
				}
			}
		}

		return $update_exists;
	}

	#[ArrayShape( [
		'type'       => "string",
		'slug'       => "string",
		'language'   => "string",
		'version'    => "string",
		'updated'    => "string",
		'package'    => "string",
		'autoupdate' => "bool"
	] )] private function prepare_db_translation_info(
		array $info, string $type
	): array {
		$slug    = $info['domain'] ?? '';
		$version = $info['version'] ?? '';
		$updated = $info['updated'] ?? '';

		return array(
			'type'       => $type,
			'slug'       => 'core' === $type ? 'default' : $slug,
			'language'   => 'zh_CN',
			'version'    => $version,
			'updated'    => $updated,
			'package'    => "https://litepress.cn/wp-content/language-pack/{$type}s/{$slug}/{$version}/zh_CN.zip",
			'autoupdate' => true
		);
	}

}
