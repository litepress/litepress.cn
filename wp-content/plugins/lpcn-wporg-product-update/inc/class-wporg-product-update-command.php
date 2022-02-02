<?php

namespace LitePress\Store\WPOrg_Product_Update;

use DiDom\Document;
use DiDom\Query;
use Exception;
use LitePress\Logger\Logger;
use LitePress\Redis\Redis;
use Throwable;
use WC_Product_Simple;
use WP_CLI_Command;
use WP_CLI;
use WP_Error;
use WP_Http;
use function LitePress\Helper\get_product_type_by_category_ids;
use function LitePress\WP_Http\wp_remote_get;

class WPOrg_Product_Update extends WP_CLI_Command {

	public function delete_all_product() {
		global $wpdb;

		$ids = $wpdb->get_results( "select ID
from wp_3_posts
where post_type = 'product'
  and post_name='WooCommerce'
  and post_author = 1" );

		$ids = array_map( function ( $item ) {
			return $item->ID;
		}, $ids );

		foreach ( $ids as $id ) {
			$p = wc_get_product( $id );
			if ( $p ) {
				$p->delete( true );
			}

			unset( $p );
		}

		var_dump( 'over' );
		exit;
	}

	public function worker() {
		if ( Redis::get_instance() ) {
			while ( true ) {
				//$type = 'plugin';
				//$slug = 'wpgetapi';

				//$type = 'theme';
				//$slug = 'twentytwentyone';

				//$this->update( $type, $slug );

				//exit;

				$msg = Redis::get_instance()->xRead( array( 'slug_update_check' => '0-0' ), 1, true );
				if ( $msg && isset( $msg['slug_update_check'] ) && ! empty( $msg['slug_update_check'] ) ) {
					$data = current( $msg['slug_update_check'] );
					$type = key( $data );
					$slug = end( $data );

					Redis::get_instance()->xDel( 'slug_update_check', array( key( $msg['slug_update_check'] ) ) );

					try {
						$this->update( $type, $slug );
					} catch ( Throwable $exception ) {
						Logger::error( Logger::STORE, '应用市场爬虫抓取数据失败', array(
							'slug'    => $slug,
							'type'    => $type,
							'message' => $exception->getMessage(),
							'file'    => $exception->getFile(),
							'line'    => $exception->getLine()
						) );
					}
				}
			}
		} else {
			WP_CLI::line( '连接 Redis 失败。' );
		}
	}

	private function update( string $type, string $slug ) {
		switch ( $type ) {
			case 'plugin':
				$this->plugin_update( $slug );
				break;
			case 'theme':
				$this->theme_update( $slug );
				break;
		}
	}

	/**
	 * @throws \Exception
	 */
	private function plugin_update( string $slug ): bool {
		$url  = "http://wordpress.org/plugins/$slug/";
		$html = $this->http_get( $url );
		if ( ! $html ) {
			return false;
		}

		$info = $this->parse_plugin_info_by_html( $html );
		//file_put_contents( WP_CONTENT_DIR . '/aaa.txt', json_encode( $info ) . PHP_EOL, FILE_APPEND );
		// 为了防止插件和主题的 Slug 冲突，入库时统一添加前缀
		$full_slug = "plugin-$slug";

		$this->update_product( 15, $full_slug, $info );

		return true;
	}

	private function http_get( $url ): string|bool {
		$r = wp_remote_get( $url );
		if ( is_wp_error( $r ) ) {
			Logger::error( Logger::STORE, '应用市场爬虫抓取数据时失败', array(
				'url'   => $url,
				'error' => $r->get_error_message(),
			) );

			return false;
		}

		$status = wp_remote_retrieve_response_code( $r );
		if ( WP_Http::OK !== $status ) {
			Logger::error( Logger::STORE, '应用市场爬虫抓取数据时失败，接口返回了意料之外的状态码', array(
				'url'    => $url,
				'status' => $status,
			) );

			return false;
		}

		return wp_remote_retrieve_body( $r );
	}

	/**
	 * @throws \DiDom\Exceptions\InvalidSelectorException
	 */
	private function parse_plugin_info_by_html( string $html ): array {
		$document = new Document( $html );

		$meta_str = $document->first( '//script[@type="application/ld+json"]/text()', Query::TYPE_XPATH )?->text();
		if ( empty( $meta_str ) ) {
			throw new Exception( '数据抓取失败，该插件可能已被下架' );
		}

		$meta = current( json_decode( $meta_str, true ) );

		$article         = $document->first( '//article', Query::TYPE_XPATH );
		$plugin_meta_dom = $article->find( '//div[@class="widget plugin-meta"]/ul/li', Query::TYPE_XPATH );

		$all_key = array( 'Active installations:', 'WordPress Version:', 'Tested up to:', 'PHP Version:', 'Tags:' );
		foreach ( $plugin_meta_dom as $item ) {
			$key = trim( $item->first( 'text()', Query::TYPE_XPATH ) );
			if ( ! in_array( $key, $all_key ) ) {
				continue;
			}

			if ( 'Tags:' === $key ) { // 标签的 HTML 结构和其他的不一样，所以单独抓取
				$tags = $item->find( 'div[@class="tags"]/a/text()', Query::TYPE_XPATH );

				$meta['tags'] = $tags;

				continue;
			}

			$value = trim( $item->first( 'strong/text()', Query::TYPE_XPATH ) );

			if ( $key === $all_key[0] ) {
				preg_match( '|(.*?)(\d+)|', str_replace( ',', '', $value ), $matches );
				$value_int = $matches[2] ?? 0;
				if ( str_contains( $value, 'million' ) ) {
					$value_int .= '000000';
				}
				$meta['active_installation'] = $value_int;
			} else if ( $key === $all_key[1] ) {
				preg_match( '~[\d+|.]+~', $value, $matches );
				$value                     = $matches[0] ?? 0;
				$meta['wordpress_version'] = $value;
			} else if ( $key === $all_key[2] ) {
				preg_match( '~[\d+|.]+~', $value, $matches );
				$value             = $matches[0] ?? 0;
				$meta['tested_up'] = $value;
			} else if ( $key === $all_key[3] ) {
				preg_match( '~[\d+|.]+~', $value, $matches );
				$value               = $matches[0] ?? 0;
				$meta['php_version'] = $value;
			}
		}

		$icon_url   = '';
		$banner_url = '';
		if ( key_exists( 'image', $meta ) ) {
			foreach ( $meta['image'] as $url ) {
				$url = str_replace( 'ps.w.org', 'ps.w.org.ibadboy.net', $this->prepare_url( $url ) );
				if ( str_contains( $url, '/assets/icon-' ) ) {
					$icon_url = $url;
				} else if ( str_contains( $url, '/assets/banner-' ) ) {
					$banner_url = $url;
				}
			}
		}

		$description = '';
		foreach ( $article->find( '//div[@id="tab-description"]/*[not(@id="description-header")]', Query::TYPE_XPATH ) as $item ) {
			$description .= $item->html();
		}

		$screenshots = array();
		foreach ( $article->find( '//div[@id="screenshots"]/ul/li/figure', Query::TYPE_XPATH ) as $item ) {
			$screenshots[] = array(
				'url' => str_replace( 'ps.w.org', 'ps.w.org.ibadboy.net', $this->prepare_url( $item->first( 'a/img/@src', Query::TYPE_XPATH ) ) ),
				'alt' => $item->first( 'figcaption/text()', Query::TYPE_XPATH ) ?: '',
			);
		}

		$faqs    = array();
		$faq_dl  = $article->first( '//div[@id="faq"]/dl', Query::TYPE_XPATH );
		$faq_dts = $faq_dl?->find( 'dt/h3/text()', Query::TYPE_XPATH );
		$faq_dds = $faq_dl?->find( 'dd', Query::TYPE_XPATH );

		/**
		 * @var string $dt
		 */
		foreach ( (array) $faq_dts as $key => $dt ) {
			$dd     = $faq_dds[ $key ]->html();
			$faqs[] = array(
				'question' => $dt,
				'answer'   => str_replace( array( '<dd>', '</dd>', "\n" ), '', $dd ),
			);
		}

		$installation = '';
		foreach ( $article->find( '//div[@id="tab-installation"]/*[not(@id="installation-header")]', Query::TYPE_XPATH ) as $item ) {
			$installation .= $item->html();
		}

		$changelog = '';
		foreach ( $article->find( '//div[@id="tab-changelog"]/*[not(@id="changelog-header")]', Query::TYPE_XPATH ) as $item ) {
			$changelog .= $item->html();
		}

		return array(
			'name'                => $meta['name'],
			'author'              => $article->first( '//*[@class="author vcard"]//text()', Query::TYPE_XPATH ),
			'thumbnail'           => str_replace( 'ps.w.org', 'ps.w.org.ibadboy.net', $this->prepare_url( (string) $document->first( '//meta[@name="thumbnail"]/@content', Query::TYPE_XPATH ) ) ),
			'short_description'   => $meta['description'],
			'description'         => $description,
			'screenshots'         => $screenshots,
			'faqs'                => $faqs,
			'installation'        => $installation,
			'changelog'           => $changelog,
			'version'             => $meta['softwareVersion'],
			'download_url'        => str_replace( 'downloads.wordpress.org', 'd.w.org.ibadboy.net', $this->prepare_url( $meta['downloadUrl'] ) ),
			'date_modified'       => $meta['dateModified'],
			'rating'              => array(
				'rating_value' => $meta['aggregateRating']['ratingValue'] ?? 0,
				'rating_count' => $meta['aggregateRating']['ratingCount'] ?? 0,
			),
			'icon'                => $icon_url,
			'banner'              => $banner_url,
			'active_installation' => $meta['active_installation'] ?? 0,
			'wordpress_version'   => $meta['wordpress_version'] ?? 0,
			'tested_up'           => $meta['tested_up'] ?? 0,
			'php_version'         => $meta['php_version'] ?? 0,
			'tags'                => $meta['tags'] ?? array(),
		);
	}

	/**
	 * 格式化 URL，去除参数
	 */
	private function prepare_url( string $url ): string {
		@list( $url, $query ) = explode( '?', $url );

		return $url;
	}

	private function update_product( int $category_id, string $full_slug, array $info ): bool {
		global $wpdb;

		$r = $wpdb->get_var( $wpdb->prepare( "select ID from {$wpdb->prefix}posts where post_name=%s limit 1;", $full_slug ) );
		if ( empty( $r ) ) {
			$product = new WC_Product_Simple();
		} else {
			$product = wc_get_product( $r );
		}

		$product->set_name( $info['name'] );
		$product->set_slug( $full_slug );

		$product->set_image_id( $this->update_image( $info['thumbnail'] ) );
		$product->set_short_description( $info['short_description'] ?? '' );

		$product->add_meta_data( '51_default_editor', $info['description'], true );
		$product->add_meta_data( '47_default_editor', $info['changelog'] ?? '', true );
		$product->add_meta_data( '46_custom_list_faqs', $info['faqs'] ?? array(), true );
		$product->add_meta_data( '365_default_editor', $info['installation'] ?? '', true );

		$image_ids = array();
		foreach ( $info['screenshots'] ?? array() as $screenshot ) {
			$image_ids[] = $this->update_image( $screenshot['url'], $screenshot['alt'] );
		}
		$product->set_gallery_image_ids( $image_ids );

		$tags = array();
		foreach ( $info['tags'] as $tag ) {
			$term = get_term_by( 'slug', sanitize_title( $tag ), 'product_tag', ARRAY_A );
			if ( ! $term ) {
				$term = wp_insert_term( $tag, 'product_tag' );

				$term = get_term_by( 'id', $term['term_id'], 'product_tag', ARRAY_A );
			}

			if ( is_wp_error( $term ) ) {
				throw new Exception( $term->get_error_message() );
			}

			if ( ! $term ) {
				throw new Exception( "创建标签失败，失败的标签：{$tag}" );
			}

			$tags[] = $term['term_id'];
		}
		$product->set_tag_ids( $tags );
		$product->set_category_ids( array( $category_id ) );

		$product->add_meta_data( '_api_new_version', $info['version'], true );
		$product->add_meta_data( '_api_version_required', $info['wordpress_version'], true );
		$product->add_meta_data( '_api_tested_up_to', $info['tested_up'] ?? '', true );
		$product->add_meta_data( '_api_requires_php', $info['php_version'], true );

		$product->add_meta_data( '_download_url', $info['download_url'], true );
		$product->add_meta_data( '_no_auth_download', 'yes', true );
		$product->add_meta_data( '_banner', $info['banner'] ?? '', true );
		if ( isset( $info['preview_url'] ) ) {
			$product->add_meta_data( 'preview_url', $info['preview_url'], true );
		}

		// WordPress.org 上的商品的评分数据和下载数据都单独记录
		$product->add_meta_data( 'wporg_rating', array(
			'rating_value' => $info['rating']['rating_value'],
			'rating_count' => $info['rating']['rating_count'],
		), true );
		$product->add_meta_data( 'wporg_active_installation', $info['active_installation'], true );

		$product->set_regular_price( '0' );

		$product->set_date_created( $info['date_modified'] );
		$product->set_date_modified( $info['date_modified'] );

		$product->save();

		// 商品创建完后应该创建并绑定作者
		$term = get_term_by( 'slug', sanitize_title( $info['author'] ), WC_PRODUCT_VENDORS_TAXONOMY, ARRAY_A );
		if ( ! $term ) {
			$term = wp_insert_term( $info['author'], WC_PRODUCT_VENDORS_TAXONOMY );

			// no errors, term added, continue
			if ( ! is_wp_error( $term ) ) {
				$term = get_term_by( 'id', $term['term_id'], WC_PRODUCT_VENDORS_TAXONOMY, ARRAY_A );

				$vendor_data                         = array();
				$vendor_data['admins']               = 1;
				$vendor_data['per_product_shipping'] = 'yes';
				$vendor_data['commission_type']      = 'percentage';
				$vendor_data['description']          = '';
				update_term_meta( $term['term_id'], 'vendor_data', $vendor_data );
			}
		}

		if ( is_wp_error( $term ) ) {
			/**
			 * @var WP_Error
			 */
			throw new Exception( $term->get_error_message() );
		}

		wp_set_post_terms( $product->get_id(), $term['slug'], WC_PRODUCT_VENDORS_TAXONOMY, false );

		// 手工触发 save_post_product 钩子，以使诸如 EP 等插件监控到产品的更新
		do_action( 'save_post_product', $product->get_id(), $product );

		// 触发一个自定义钩子，方便在产品更新后执行一些平台特有的操作，比如刷新 CDN、更新翻译
		do_action( 'lpcn_wp_product_updated', $product->get_slug(), get_product_type_by_category_ids( array( $category_id ) ) );

		unset( $product );

		return true;
	}

	private function update_image( string $url, string $alt = '' ): int {
		global $wpdb;

		$r = $wpdb->get_var( $wpdb->prepare( "select ID from {$wpdb->prefix}posts where guid=%s limit 1;", esc_url_raw( $url ) ) );
		if ( ! empty( $r ) ) {
			$attachment_id = $r;
		} else {
			$mime_type    = '';
			$tmp          = explode( '.', $url );
			$tmp_last_key = array_key_last( $tmp );
			if ( $tmp_last_key > 0 ) {
				$mime_type = 'image/' . $tmp[0] ?? 'jpg';
			}
			$args          = array(
				'post_author'    => 0,
				'post_content'   => $alt,
				'post_status'    => 'inherit',
				'post_type'      => 'attachment',
				'comment_status' => 'closed',
				'ping_status'    => 'closed',
				'post_mime_type' => $mime_type,
				'guid'           => esc_url_raw( $url ),
			);
			$attachment_id = wp_insert_post( $args );
		}

		return $attachment_id;
	}

	/**
	 * @throws \Exception
	 */
	private function theme_update( string $slug ) {
		$url  = "http://wordpress.org/themes/$slug/";
		$html = $this->http_get( $url );
		if ( ! $html ) {
			return false;
		}

		$info = $this->parse_theme_info_by_html( $html );

		//file_put_contents( WP_CONTENT_DIR . '/aaa.txt', json_encode( $info ) . PHP_EOL, FILE_APPEND );

		// 为了防止插件和主题的 Slug 冲突，入库时统一添加前缀
		$full_slug = "theme-$slug";

		$this->update_product( 17, $full_slug, $info );

		return true;
	}

	private function parse_theme_info_by_html( string $html ): array {
		$document = new Document( $html );

		$meta_str = $document->first( '//*[@id="wporg-theme-js-extra"]/text()', Query::TYPE_XPATH )->text();
		if ( empty( $meta_str ) ) {
			throw new Exception( '数据抓取失败，该主题可能已被下架' );
		}

		$meta_str = str_replace( array(
			"\n/* <![CDATA[ */\nvar _wpThemeSettings = ",
			";\n/* ]]> */\n"
		), '', $meta_str );

		$meta = json_decode( $meta_str, true );

		return array(
			'name'                => $meta['query']['themes'][0]['name'],
			'author'              => $meta['query']['themes'][0]['author']['display_name'],
			'thumbnail'           => $meta['query']['themes'][0]['screenshot_url'],
			'preview_url'         => $meta['query']['themes'][0]['preview_url'],
			'description'         => $meta['query']['themes'][0]['description'],
			'version'             => $meta['query']['themes'][0]['version'],
			'download_url'        => str_replace( 'downloads.wordpress.org', 'd.w.org.ibadboy.net', $this->prepare_url( $meta['query']['themes'][0]['download_link'] ) ),
			'date_modified'       => $meta['query']['themes'][0]['last_updated'],
			'rating'              => array(
				'rating_value' => (string) ( (int) ( $meta['query']['themes'][0]['rating'] ) / 20 ),
				'rating_count' => $meta['query']['themes'][0]['num_ratings'],
			),
			'active_installation' => $meta['query']['themes'][0]['active_installs'],
			'wordpress_version'   => $meta['query']['themes'][0]['requires'],
			'php_version'         => $meta['query']['themes'][0]['requires_php'],
			'tags'                => $meta['query']['themes'][0]['tags'],
		);
	}

}

WP_CLI::add_command( 'lpcn wporg_product_update', __NAMESPACE__ . '\WPOrg_Product_Update' );
