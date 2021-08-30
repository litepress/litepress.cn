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
		$sql     = $wpdb->prepare( "select domain, version, updated from language_packs where type=%s and domain in ({$domains})", $query_type, );
		$r       = $wpdb->get_results( $sql, ARRAY_A );

		foreach ( $r as $item ) {
			$request_item              = $translations[ $item['domain'] ]['zh_CN'] ?? array();
			$request_item_last_updated = $request_item['PO-Revision-Date'] ?? '';

			if ( strtotime( $request_item_last_updated ) < strtotime( $item['updated'] ) ) {
				$update_exists[] = $this->prepare_db_translation_info( $item, $display_type );
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
			'slug'       => $slug,
			'language'   => 'zh_CN',
			'version'    => $version,
			'updated'    => $updated,
			'package'    => "https://litepress.cn/wp-content/language-pack/{$type}s/{$slug}/{$version}/zh_CN.zip",
			'autoupdate' => true
		);
	}

}
