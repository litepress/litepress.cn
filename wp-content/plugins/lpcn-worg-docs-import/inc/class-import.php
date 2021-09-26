<?php

namespace LitePress\Docs\Import;

use LitePress\Logger\Logger;
use LitePress\WP_Http\WP_Http;
use WP_Error;
use function LitePress\WP_Http\wp_remote_get;
use function LitePress\Helper\compress_html;

class Import {

	private array $cat_map = array();

	private string $gp_project_prefix = 'docs';

	public function job() {
		if ( ! isset( $_GET['debug'] ) ) {
			return;
		}

		/**
		 * 初始化 w.org 上分类 ID 与本地的分类 ID 的对照关系
		 */
		$this->cat_map = $this->update_category();

		$total_pages = 100;
		for ( $page = 1; $page <= $total_pages; $page ++ ) {
			$url  = "http://wordpress.org/support/wp-json/wp/v2/articles?per_page=100&page=$page";
			$data = $this->remote_get( $url );
			if ( is_wp_error( $data ) ) {
				Logger::error( 'DOCS', $data->get_error_message(), array(
					'url' => $url,
				) );

				return;
			}

			foreach ( $data as $item ) {
				$cat_ids = array_map( function ( $item ) {
					return $this->cat_map[ $item ];
				}, $item->category );

				$r = $this->insert( $item->id, $item->slug, $item->title?->rendered, $item->menu_order, $item->content?->rendered, $item->excerpt?->rendered, 0, $cat_ids );
				if ( is_wp_error( $r ) ) {
					Logger::error( 'DOCS', '创建文章失败：' . $r->get_error_message() );
				}
			}
		}

	}

	private function update_category(): array {
		$url  = "http://wordpress.org/support/wp-json/wp/v2/category?per_page=100";
		$data = $this->remote_get( $url );
		if ( is_wp_error( $data ) ) {
			Logger::error( 'DOCS', $data->get_error_message(), array(
				'url' => $url,
			) );

			return array();
		}

		$cat_map = array();
		foreach ( $data as $item ) {
			if ( ! function_exists( 'wp_insert_category' ) ) {
				require ABSPATH . '/wp-admin/includes/taxonomy.php';
			}

			if ( ! has_category( $item->slug ) ) {
				$args = array(
					'taxonomy' => 'category',
					'cat_name' => $item->name,
				);
				wp_insert_category( $args );
			}

			$r = get_category_by_slug( $item->slug );

			$cat_map[ $item->id ] = $r?->term_id;
		}

		return $cat_map;
	}

	private function remote_get( string $url ): array|WP_Error {
		$args = array(
			'timeout' => 600,
		);

		$r = wp_remote_get( $url, $args );

		if ( is_wp_error( $r ) ) {
			return new WP_Error( 'docs_import_error', '从 w.org 抓取文档失败：' . $r->get_error_message() );
		}

		$status_code = wp_remote_retrieve_response_code( $r );
		if ( WP_Http::OK !== $status_code ) {
			return new WP_Error( 'docs_import_error', '从 w.org 抓取文档失败，接口返回状态码：' . $status_code );
		}

		$body = wp_remote_retrieve_body( $r );

		return json_decode( $body );
	}

	private function insert( int $id, string $post_name, string $title, int $order, string $content = '', string $excerpt = '', int $post_parent = 0, array $cat_ids = array() ): int|WP_Error {
		$is_exist = get_post( $id );
		if ( ! $is_exist ) {
			$this->insert_empty_post( $id );
		}

		$content = compress_html( $content );

		$args = array(
			'ID'            => $id,
			'post_author'   => 517,
			'post_content'  => $content,
			'post_title'    => $title,
			'post_name'     => $post_name,
			'post_excerpt'  => $excerpt,
			'post_status'   => 'publish',
			'post_type'     => 'post',
			'post_parent'   => $post_parent,
			'menu_order'    => $order,
			'post_category' => $cat_ids,
		);

		// 文档导入后为翻译平台安排一个计划任务来将文档同步过去
		global $blog_id;

		$current_id = $blog_id;
		switch_to_blog( 4 );
		wp_schedule_single_event( time() + 60, 'lpcn_gp_doc_import', array(
			'name'    => $title,
			'slug'    => "$this->gp_project_prefix-$post_name",
			'content' => $content,
		) );
		switch_to_blog( $current_id );

		return wp_insert_post( $args );
	}

	/**
	 * 创建 ID 为给定值的空文章
	 *
	 * @param int $id
	 *
	 * @return bool
	 */
	private function insert_empty_post( int $id ): bool {
		global $wpdb;

		$wpdb->insert( $wpdb->posts, array(
				'ID' => $id,
			)
		);

		return true;
	}

}
