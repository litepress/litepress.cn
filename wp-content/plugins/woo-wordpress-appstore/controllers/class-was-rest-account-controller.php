<?php

namespace WCWPAS\Controllers;

use WC_Product_Vendors_Utils;
use WC_REST_Customer_Downloads_Controller;
use WP_REST_Server;

class WAS_REST_Account_Controller extends WC_REST_Customer_Downloads_Controller {
	protected $namespace = 'was/v1';
	protected $rest_base = 'account';
	protected $product_ids = [];
	protected $product_order_api_keys = [];
	protected $user_id = 0;

	public function register_routes() {
		register_rest_route( $this->namespace, '/' . $this->rest_base, array(
			'args'   => array(
				'customer_id' => array(
					'description' => __( 'Unique identifier for the resource.', 'woocommerce' ),
					'type'        => 'integer',
				),
			),
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_items' ),
				'permission_callback' => array( $this, 'get_items_permissions_check' ),
				'args'                => $this->get_collection_params(),
			),
			'schema' => array( $this, 'get_public_item_schema' ),
		) );
	}

	public function get_items_permissions_check( $request ) {
		return true;
	}

	public function get_items( $request ) {
		$this->user_id = was_get_user_id();
		$resources     = WC_AM_API_RESOURCE_DATA_STORE()->get_api_resources_for_user_id_sort_by_product_title( absint( $request['customer'] ) );

		$data = [];

		/**
		 * 汇总所有拥有相同产品ID的订单API KEY
		 */
		foreach ( $resources as $resource_data ) {
			$product_order_api_key = [
				'product_order_api_key'       => $resource_data->product_order_api_key,
				"api_resource_id"             => $resource_data->api_resource_id,
				"activation_ids"              => $resource_data->activation_ids,
				"activations_total"           => $resource_data->activations_total,
				"activations_purchased"       => $resource_data->activations_purchased,
				"activations_purchased_total" => $resource_data->activations_purchased_total,
				"active"                      => $resource_data->active,
				"order_id"                    => $resource_data->order_id,
			];
			if ( key_exists( $resource_data->product_id, $this->product_order_api_keys ) ) {
				$this->product_order_api_keys[ $resource_data->product_id ][] = $product_order_api_key;
			} else {
				$this->product_order_api_keys[ $resource_data->product_id ] = [ $product_order_api_key ];
			}
		}

		foreach ( $resources as $resource_data ) {
			$resource = $this->prepare_item_for_response( (object) $resource_data, $request );
			if ( empty( $resource ) ) {
				continue;
			}
			$resource = $this->prepare_response_for_collection( $resource );

			$data[] = $resource;
		}

		return rest_ensure_response( $data );
	}

	public function prepare_item_for_response( $resource, $request ) {
		$this->product_ids[] = $resource->product_id;
		$total_product_ids   = array_count_values( $this->product_ids );
		if ( $total_product_ids[ $resource->product_id ] > 1 && in_array( $resource->product_id, $this->product_ids ) ) {
			return [];
		} else {
			$product          = wc_get_product( $resource->product_id );
			$attachment_ids[] = $product->get_image_id();
			@$product_img = wp_get_attachment_image_src( $attachment_ids[0], 'full' )[0];

			$vendor_id = WC_Product_Vendors_Utils::get_user_active_vendor(get_post($product->get_id())->post_author);
			$vendor_data = WC_Product_Vendors_Utils::get_vendor_data_by_id($vendor_id);

			$data = array(
				'id'                => $resource->product_id,
				'author'            => array(
					'name' => $vendor_data['name'],
					'slug' => $vendor_data['slug']
				),
				'thumbnail_src'     => $product_img,
				'slug'              => $product->get_slug(),
				'name'              => $resource->product_title,
				'short_description' => $product->get_short_description(),
				'version'           => $product->get_meta( '_api_new_version', true ),
				//'download_url'      => WC_AM_ORDER_DATA_STORE()->get_secure_order_download_url( $this->user_id, $resource->sub_id, $resource->product_id ),
				'order_api_keys'    => $this->product_order_api_keys[ $resource->product_id ],
			);
		}

		$context = ! empty( $request['context'] ) ? $request['context'] : 'view';
		$data    = $this->add_additional_fields_to_object( $data, $request );
		$data    = $this->filter_response_by_context( $data, $context );

		$response = rest_ensure_response( $data );

		return apply_filters( 'woocommerce_rest_prepare_customer_download', $response, $resource, $request );
	}

}
