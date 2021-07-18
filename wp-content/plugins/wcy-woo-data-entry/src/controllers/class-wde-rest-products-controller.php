<?php

namespace WCY\WDE\Src\Controllers;

use Exception;
use WC_Data;
use WC_REST_Exception;
use WC_REST_Products_Controller;
use WP_Error;
use WP_REST_Server;

class WDE_REST_Products_Controller extends WC_REST_Products_Controller {
	protected $namespace = 'wde/v1';
	protected $rest_base = 'products';

	public function __construct() {
		parent::__construct();
	}

	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'create_item' ),
				'permission_callback' => array( $this, 'create_item_permissions_check' ),
			)
		);
	}

	public function create_item( $request ) {
		/**
		 * 通过文章slug查询文章是否存在，如果存在的话就调用更新方法
		 * TODO:需要考虑插件和主题的slug可能重复的问题
		 */
		global $wpdb;

		/**
		 * 成功创建产品后触发一下为产品创建作者的回调
		 * @throws Exception
		 */
		add_action( 'woocommerce_rest_insert_product_object', function ( WC_Data $object, $request ) {
			/**
			 * 更新销量，从WordPress.org爬来的产品，其销量直接引用wordpress.org的，用户从本土市场下载这些商品的时候不会生成订单数据。
			 */
			//update_post_meta($object->get_id(), 'total_sales', $request['wp_active_installation']);
			$total_sales    = $request['wp_active_installation'];
			$average_rating = $request['_wc_average_rating'];
			$review_count   = $request['_wc_review_count'];
			update_post_meta( $object->get_id(), 'total_sales', $total_sales );
			update_post_meta( $object->get_id(), '_wc_average_rating', $average_rating );
			update_post_meta( $object->get_id(), '_wc_review_count', $review_count );
			update_post_meta( $object->get_id(), '_wc_rating_count', $review_count );

			/**
			 * Woo设计了一个独立于post meta表的meta存储表：wp_3_wc_product_meta_lookup，目的是方便多个字段关联排序
			 *
			 * 以上更新的四个meta都需要把值再通过sql语句更如这张表里。为啥使用sql语句而不使用Woo内置方法呢，因为我翻了半天代码也没发现咋用，索性不浪费时间了
			 */
			global $wpdb;
			$wpdb->update(
				"{$wpdb->prefix}wc_product_meta_lookup",
				array(
					'total_sales'    => $total_sales,
					'average_rating' => $average_rating,
					'rating_count'   => $review_count,
				),
				array(
					'product_id' => $object->get_id(),
				),
			);

			/**
			 * 更改作品发布日期为从wordpress.org上爬取的日期
			 */
			$wpdb->update(
				"{$wpdb->prefix}posts",
				array(
					'post_date'         => $request['date_modified'],
					'post_date_gmt'     => $request['date_modified'],
					'post_modified'     => $request['date_modified'],
					'post_modified_gmt' => $request['date_modified'],
				),
				array(
					'ID' => $object->get_id(),
				),
			);

			/**
			 * 为产品添加供应商
			 */
			$term = get_term_by( 'slug', sanitize_title( $request['author'] ), WC_PRODUCT_VENDORS_TAXONOMY, ARRAY_A );
			if ( ! $term ) {
				$term = wp_insert_term( $request['author'], WC_PRODUCT_VENDORS_TAXONOMY );

				// no errors, term added, continue
				if ( ! is_wp_error( $term ) ) {
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

			wp_set_post_terms( $object->get_id(), $term['slug'], WC_PRODUCT_VENDORS_TAXONOMY, false );
		}, 99999, 2 );

		$r = $wpdb->get_var( $wpdb->prepare( "select ID from {$wpdb->prefix}posts where post_name=%s limit 1;", $request['slug'] ) );

		if ( ! empty( $r ) ) {
			/**
			 * 检查一下查询到的产品的类别是否和请求创建的类别一致，因为wordpress.org允许插件和主题重名，所以需要做特别处理
			 */
			/**
			 * @var int
			 */
			$product_cat     = 0;
			$product_cat_ids = wc_get_product_cat_ids( $r );
			foreach ( $product_cat_ids as $v ) {
				switch ( $v ) {
					case 15:
						$product_cat = 15;
						break;
					case 17:
						$product_cat = 17;
						break;
					default:
						break;
				}
			}

			/**
			 * 如果数据库中记录的类别和爬虫post传来的不一样，也就代表着这是个重名的插件 or 主题
			 */
			if ( 0 !== $product_cat && isset( $request['categories'][0]['id'] ) && $product_cat !== (int) $request['categories'][0]['id'] ) {
				$urls      = get_option( 'permalink-manager-uris' );
				$urls_flip = array_flip( $urls );
				$type      = 15 === (int) $request['categories'][0] ? 'plugins' : 'themes';
				$url       = $type . '/' . $request['slug'];
				/**
				 * 如果当前URL不存在就是新建，否则更新
				 */
				if ( ! key_exists( $url, $urls_flip ) ) {
					$wc_data = parent::create_item( $request );

					if ( ! is_wp_error( $wc_data ) ) {
						$body_array = $wc_data->get_data();
						if ( key_exists( 'id', $body_array ) ) {
							$urls[ $wc_data->get_data()['id'] ] = $url;
							update_option( 'permalink-manager-uris', $urls );
						}
					}
				} else {
					$request['id'] = $urls_flip[ $url ];

					$wc_data = parent::update_item( $request );
				}

				return $wc_data;
			}

			/**
			 * 如果已存在的产品类型和请求创建的一致就继续原本的流程
			 */
			$request['id'] = $r;

			/** 对于更新请求需要删掉对分类目录的选择，否则会覆盖掉人工勾选的分类目录 */
			unset( $request['categories'] );

			return parent::update_item( $request );
		} else {
			return parent::create_item( $request );
		}
	}

	protected function set_product_images( $product, $images ) {
		$images = is_array( $images ) ? array_filter( $images ) : array();

		if ( ! empty( $images ) ) {
			$gallery = array();

			foreach ( $images as $index => $image ) {
				$attachment_id = isset( $image['id'] ) ? absint( $image['id'] ) : 0;

				if ( 0 === $attachment_id && isset( $image['src'] ) && ! empty( $image['src'] ) ) {
					/**
					 * Woo默认会将src中的图片下载到本地然后再插入wordpress的post表中，需要改改。直接将src保存到post中而不下载图片
					 * 因为wordpress.org的产品图一般都是：产品名/icon-124x124.png这样命名的，如果全保存到本地的话所有的文件名都是
					 * icon-124x124.pnn，就很无语……
					 */
					/*
					$upload = wc_rest_upload_image_from_url( esc_url_raw( $image['src'] ) );

					if ( is_wp_error( $upload ) ) {
						if ( ! apply_filters( 'woocommerce_rest_suppress_image_upload_error', false, $upload, $product->get_id(), $images ) ) {
							throw new WC_REST_Exception( 'woocommerce_product_image_upload_error', $upload->get_error_message(), 400 );
						} else {
							continue;
						}
					}

					$attachment_id = wc_rest_set_uploaded_image_as_attachment( $upload, $product->get_id() );
					*/

					global $wpdb;

					$r = $wpdb->get_var( $wpdb->prepare( "select ID from {$wpdb->prefix}posts where guid=%s limit 1;", esc_url_raw( $image['src'] ) ) );
					if ( ! empty( $r ) ) {
						$attachment_id = $r;
					} else {
						$mime_type    = '';
						$tmp          = explode( '.', $image['src'] );
						$tmp_last_key = array_key_last( $tmp );
						$tmp          = explode( '?', $tmp[ $tmp_last_key ] );
						if ( $tmp_last_key > 0 ) {
							$mime_type = 'image/' . $tmp[0] ?? 'jpg';
						}
						$args          = array(
							'post_author'    => 0,
							'post_content'   => '',
							'post_status'    => 'inherit',
							'post_type'      => 'attachment',
							'comment_status' => 'closed',
							'ping_status'    => 'closed',
							'post_mime_type' => $mime_type,
							'guid'           => esc_url_raw( $image['src'] ),
						);
						$attachment_id = wp_insert_post( $args );
					}
				}

				if ( 0 !== $index && ! wp_attachment_is_image( $attachment_id ) ) {
					/* translators: %s: image ID */
					throw new WC_REST_Exception( 'woocommerce_product_invalid_image_id', sprintf( __( '#%s is an invalid image ID.', 'woocommerce' ), $attachment_id ), 400 );
				}

				if ( 0 === $index && $attachment_id !== 0 ) {
					$product->set_image_id( $attachment_id );
				} else {
					$gallery[] = $attachment_id;
				}

				// Set the image alt if present.
				if ( ! empty( $image['alt'] ) ) {
					update_post_meta( $attachment_id, '_wp_attachment_image_alt', wc_clean( $image['alt'] ) );
				}

				// Set the image name if present.
				if ( ! empty( $image['name'] ) ) {
					wp_update_post(
						array(
							'ID'         => $attachment_id,
							'post_title' => $image['name'],
						)
					);
				}
			}

			$product->set_gallery_image_ids( $gallery );
		} else {
			$product->set_image_id( '' );
			$product->set_gallery_image_ids( array() );
		}

		return $product;
	}

}
