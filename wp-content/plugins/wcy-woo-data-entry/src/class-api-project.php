<?php

namespace WCY\WDE\Src;

use WC_Product_External;
use WC_Product_Simple;
use function LitePress\Helper\get_product_type_by_category_ids;

class Api_Project {

	const PROJECT_TABLE = 'lp_api_projects';

	public function init() {
		add_action( 'woocommerce_new_product', array( $this, 'update_api_data' ), 99999, 2 );
		add_action( 'woocommerce_update_product', array( $this, 'update_api_data' ), 99999, 2 );
	}

	/**
	 * 将录入的产品数据更新到api.litepress.cn中
	 */
	public function update_api_data( int $product_id, $product ) {
		/*
		$medium_image_url = wp_get_attachment_image_src( get_post_thumbnail_id( $product->get_id() ), 'medium' );
		if ( ! empty( $medium_image_url ) && isset( $medium_image_url[0] ) ) {
			$icons = json_encode( array(
				'default' => $medium_image_url[0],
			) );
		} else {
			$icons = json_encode( array() );
		}

		$banner = $product->get_meta( '_banner', true );
		if ( ! empty( $banner ) ) {
			$banners = json_encode( array(
				'1x' => $banner
			) );
		} else {
			$banners = json_encode( array() );
		}

		$slug = $_GET['slug'] ?? $product->get_slug();
		$type = get_product_type_by_category_ids( $product->get_category_ids() );

		if ( $product instanceof WC_Product_Simple ) {
			$download_url = $product->get_meta( '_download_url', true );
			if ( empty( $download_url ) ) {
				foreach ( $product->get_downloads() as $download ) {
					$download_url = $download['file'];
				}
			}

			$this->insert(
				$product->get_id(),
				$type,
				$product->get_name(),
				$slug,
				$icons,
				$banners,
				$product->get_meta( '_api_new_version', true ),
				$product->get_meta( '_api_tested_up_to', true ),
				$product->get_meta( '_api_version_required', true ),
				$product->get_meta( '_api_requires_php', true ),
				$download_url,
			);
		} else if ( $product instanceof WC_Product_External ) {
			$this->insert(
				$product->get_id(),
				$type,
				$product->get_name(),
				$slug,
				$icons,
				$banners,
				0,
				$product->get_meta( '_api_tested_up_to', true ),
				$product->get_meta( '_api_version_required', true ),
				$product->get_meta( '_api_requires_php', true ),
				'',
			);
		}*/

		$slug = $_GET['slug'] ?? $product->get_slug();
		$type = get_product_type_by_category_ids( $product->get_category_ids() );

		if ( ! str_starts_with( $slug, 'lp-' ) ) {
			/**
			 * 只有当当前产品是WordPress应用市场的产品且已更新时才会触发
			 *
			 * 这个钩子通常用来执行刷新缓存、翻译平台内容更新之类的操作
			 */
			do_action( 'lpcn_wp_product_updated', $slug, $product->get_meta( '_api_new_version', true ), $type );
		}
	}

	private function insert(
		int $product_id,
		string $type,
		string $name,
		string $slug,
		string $icons,
		string $banners,
		string $version,
		string $tested,
		string $requires,
		string $requires_php,
		string $package
	) {
		global $wpdb;

		$wpdb->replace(
			self::PROJECT_TABLE,
			array(
				'product_id'   => $product_id,
				'type'         => $type,
				'name'         => $name,
				'slug'         => $slug,
				'icons'        => $icons,
				'banners'      => $banners,
				'version'      => $version,
				'tested'       => $tested,
				'requires'     => $requires,
				'requires_php' => $requires_php,
				'package'      => $package,
				'updated_at'   => date( "Y-m-d H:i:s", time() ),
			)
		);
	}

}

$api_project = new Api_Project();
$api_project->init();
