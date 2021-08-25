<?php

namespace LitePress\API\Inc\Service;

use function LitePress\Helper\get_products_from_es;

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
			$slugs[] = $meta['TextDomain'] ?? '';
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

		$update_exists = array_filter( $plugins, function ( $plugin ) use ( $db_plugins ) {
			$slug = $plugin['TextDomain'] ?? '';

			return version_compare( $plugin['Version'] ?? '', $db_plugins[ $slug ]['new_version'] ?? '', '<' );
		} );

		// 格式化存在翻译的主题，以生成最终要返回给客户端的数据格式
		foreach ( $update_exists as $key => &$item ) {
			$slug           = $item['TextDomain'] ?? '';
			$item           = $db_plugins[ $slug ];
			$item['plugin'] = $key;
		}
		unset( $item );

		return $update_exists;
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
				$package = lp_get_woo_download_url( $item['_source']['ID'] ?? '' );
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
