<?php

namespace LitePress\API\Inc\Service;

use function LitePress\Helper\get_products_from_es;

/**
 * Class Themes_Service
 *
 * 插件服务
 *
 * @package LitePress\API\Inc\Service
 */
class Themes_Service {

	public function update_check( $themes ): array {
		$slugs = array();

		foreach ( $themes as $theme => $meta ) {
			$slugs[] = $meta['Stylesheet'] ?? '';
		}

		$fields    = array(
			'ID',
			'slug',
			'_price',
			'meta._thumbnail_id.long',
			'meta._banner.value',
			'meta._api_new_version.value',
			'meta._api_tested_up_to.value',
			'meta._api_requires_php.value',
			'meta._download_url.value',
			'meta._api_version_required.value',
		);
		$db_themes = get_products_from_es( $slugs, 'theme', $fields );
		$db_themes = $this->prepare_db_themes( $db_themes );

		$update_exists    = array();
		$no_update_exists = array();
		foreach ( $themes as $theme ) {
			$slug            = $theme['Stylesheet'] ?? '';
			$db_theme        = $db_themes[ $slug ] ?? array();
			$request_version = $theme['Version'] ?? '';
			$db_version      = $db_theme['new_version'] ?? '';

			if ( version_compare( $request_version, $db_version, '<' ) ) {
				$update_exists[ $slug ] = $db_theme;
			} elseif ( isset( $db_themes[ $slug ] ) ) {
				$no_update_exists[ $slug ] = $db_theme;
			}
		}

		return array(
			'update'    => $update_exists,
			'no_update' => $no_update_exists
		);
	}

	private function prepare_db_themes( array $db_themes ): array {
		$current_blog_id = get_current_blog_id();
		switch_to_blog( 3 );

		$data = array();

		foreach ( $db_themes['hits']['hits'] ?? array() as $item ) {
			// TODO:不对付费插件检查更新，付费插件应该由内置在插件中的单独的 SDK 进行更新
			$price = (int) ( $item['_source']['_price'] ?? 0 );
			if ( $price > 0 ) {
				continue;
			}

			$slug = $item['_source']['slug'] ?? '';

			$requires = $item['_source']['meta']['_api_version_required'][0]['value'] ?? false;
			$requires = $requires === '0' || empty( $requires ) ? false : $requires;

			$requires_php = $item['_source']['meta']['_api_requires_php'][0]['value'] ?? false;
			$requires_php = $requires_php === '0' || empty( $requires_php ) ? false : $requires_php;

			$package = $item['_source']['meta']['_download_url'][0]['value'] ?? '';
			if ( empty( $package ) ) {
				$package = lp_get_woo_download_url( $item['_source']['ID'] ?? '' );
			}

			$args                                   = array(
				'theme'        => $slug,
				'new_version'  => $item['_source']['meta']['_api_new_version'][0]['value'] ?? '',
				'url'          => 'https://litepress.cn/themes/' . $slug,
				'package'      => $package,
				'requires'     => $requires,
				'requires_php' => $requires_php,
			);
			$data[ $item['_source']['slug'] ?? '' ] = $args;
		}

		switch_to_blog( $current_blog_id );

		return $data;
	}

}
