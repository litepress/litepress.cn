<?php

namespace LitePress\API\Inc\Service;

use function LitePress\Helper\get_products_from_es;
use function LitePress\Helper\get_woo_download_url;

/**
 * Class Plugins_Service
 *
 * 插件服务
 *
 * @package LitePress\API\Inc\Service
 */
class Plugins_Service {

	public function update_check( $plugins ): array {
		$slugs = array();

		foreach ( $plugins as $plugin => $meta ) {
			list( $slug ) = explode( '/', $plugin );

			$slugs[] = $slug;
		}

		$fields     = array(
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
		$db_plugins = get_products_from_es( $slugs, 'plugin', $fields );
		$db_plugins = $this->prepare_db_plugins( $db_plugins );

		$update_exists    = array();
		$no_update_exists = array();
		foreach ( $plugins as $key => $plugin ) {
			$slug            = $plugin['TextDomain'] ?? '';
			$db_plugin       = $db_plugins[ $slug ] ?? array();
			$request_version = $plugin['Version'] ?? '';
			$db_version      = $db_plugin['new_version'] ?? '';

			$db_plugin['plugin'] = $key;

			if ( version_compare( $request_version, $db_version, '<' ) ) {
				$update_exists[ $key ] = $db_plugin;
			} elseif ( isset( $db_plugins[ $slug ] ) ) {
				$no_update_exists[ $key ] = $db_plugin;
			}
		}

		return array(
			'update'    => $update_exists,
			'no_update' => $no_update_exists
		);
	}

	private function prepare_db_plugins( array $db_plugins ): array {
		$current_blog_id = get_current_blog_id();
		switch_to_blog( 3 );

		$data = array();

		foreach ( $db_plugins['hits']['hits'] ?? array() as $item ) {
			// TODO:不对付费插件检查更新，付费插件应该由内置在插件中的单独的 SDK 进行更新
			$price = (int) ( $item['_source']['_price'] ?? 0 );
			if ( $price > 0 ) {
				continue;
			}

			$slug = $item['_source']['slug'] ?? '';

			$icons   = array();
			$icon_id = $item['_source']['meta']['_thumbnail_id'][0]['long'] ?? '';
			if ( ! empty( $icon_id ) ) {
				$post = get_post( $icon_id );

				$icons = array(
					'1x' => $post->guid,
				);
			}

			$banner  = $item['_source']['meta']['_banner'][0]['value'] ?? '';
			$banners = empty( $banner ) ? array() : array(
				'1x' => $item['_source']['meta']['_banner'][0]['value'] ?? ''
			);

			$requires = $item['_source']['meta']['_api_version_required'][0]['value'] ?? false;
			$requires = $requires === '0' || empty( $requires ) ? false : $requires;

			$tested = $item['_source']['meta']['_api_tested_up_to'][0]['value'] ?? false;
			$tested = $tested === '0' || empty( $tested ) ? false : $tested;

			$requires_php = $item['_source']['meta']['_api_requires_php'][0]['value'] ?? false;
			$requires_php = $requires_php === '0' || empty( $requires_php ) ? false : $requires_php;

			$package = $item['_source']['meta']['_download_url'][0]['value'] ?? '';
			if ( empty( $package ) ) {
				$package = get_woo_download_url( $item['_source']['ID'] ?? '' );
			}

			$args                                   = array(
				'id'            => 'litepress.cn/plugins/' . $slug,
				'slug'          => $slug,
				'new_version'   => $item['_source']['meta']['_api_new_version'][0]['value'] ?? '',
				'url'           => 'https://litepress.cn/plugins/' . $slug,
				'package'       => $package,
				'icons'         => $icons,
				'banners'       => $banners,
				'banners_rtl'   => array(),
				'requires'      => $requires,
				'tested'        => $tested,
				'requires_php'  => $requires_php,
				'compatibility' => array(),
			);
			$data[ $item['_source']['slug'] ?? '' ] = $args;
		}

		switch_to_blog( $current_blog_id );

		return $data;
	}

}
