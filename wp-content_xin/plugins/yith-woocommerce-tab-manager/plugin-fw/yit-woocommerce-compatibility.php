<?php
/**
 * Functions for WooCommerce 3.0 compatibility.
 *
 * @package YITH\PluginFramework
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

if ( ! function_exists( 'WC' ) ) {
	return;
}

$changed_objects = array();

if ( ! function_exists( 'yit_get_prop' ) ) {
	/**
	 * Retrieve a property.
	 *
	 * @param object $object  The object.
	 * @param string $key     The Meta Key.
	 * @param bool   $single  Return first found meta with key, or all.
	 * @param string $context What the value is for. Valid values are view and edit.
	 *
	 * @return mixed|null The related value or null (if the $object is not a valid object).
	 * @deprecated 3.5 | use the WooCommerce CRUD directly instead.
	 */
	function yit_get_prop( $object, $key, $single = true, $context = 'view' ) {
		$prop_map   = yit_return_new_attribute_map();
		$is_wc_data = $object instanceof WC_Data;

		if ( $is_wc_data ) {
			$key    = ( array_key_exists( $key, $prop_map ) ) ? $prop_map[ $key ] : $key;
			$getter = false;
			if ( method_exists( $object, "get{$key}" ) ) {
				$getter = "get{$key}";
			} elseif ( method_exists( $object, "get_{$key}" ) ) {
				$getter = "get_{$key}";
			}

			if ( $getter ) {
				return $object->$getter( $context );
			} else {
				return $object->get_meta( $key, $single );
			}
		} else {
			$key = ( in_array( $key, $prop_map, true ) ) ? array_search( $key, $prop_map, true ) : $key;

			if ( isset( $object->$key ) ) {
				return $object->$key;
			} elseif ( yit_wc_check_post_columns( $key ) ) {
				return $object->post->$key;
			} else {
				$object_id = 0;
				$getter    = $object instanceof WC_Customer ? 'get_user_meta' : 'get_post_meta';

				if ( ! ! $object ) {
					$object_id = is_callable( array( $object, 'get_id' ) ) ? $object->get_id() : $object->id;
				}

				return ! ! $object_id ? $getter( $object_id, $key, true ) : null;
			}
		}
	}
}

if ( ! function_exists( 'yit_set_prop' ) ) {
	/**
	 * Set prop or props.
	 *
	 * @param object       $object The object.
	 * @param array|string $arg1   The key of the prop to set, or an array of props to set.
	 * @param false        $arg2   The value to set, or false if you want to set an array of props.
	 *
	 * @deprecated 3.5 | use the WooCommerce CRUD directly instead.
	 */
	function yit_set_prop( $object, $arg1, $arg2 = false ) {
		if ( ! is_array( $arg1 ) ) {
			$arg1 = array(
				$arg1 => $arg2,
			);
		}

		$prop_map   = yit_return_new_attribute_map();
		$is_wc_data = $object instanceof WC_Data;

		foreach ( $arg1 as $key => $value ) {
			if ( $is_wc_data ) {
				$key = ( array_key_exists( $key, $prop_map ) ) ? $prop_map[ $key ] : $key;

				$setter = false;
				if ( method_exists( $object, "set{$key}" ) ) {
					$setter = "set{$key}";
				} elseif ( method_exists( $object, "set_{$key}" ) ) {
					$setter = "set_{$key}";
				}

				if ( $setter ) {
					$object->$setter( $value );
				} else {
					$object->update_meta_data( $key, $value );
				}
			} else {
				$key = ( in_array( $key, $prop_map, true ) ) ? array_search( $key, $prop_map, true ) : $key;
				if ( ( strpos( $key, '_' ) === 0 ) ) {
					$key = substr( $key, 1 );
				}

				if ( yit_wc_check_post_columns( $key ) ) {
					$object->post->$key = $value;
				} else {
					$object->$key = $value;
				}
			}
		}
	}
}

if ( ! function_exists( 'yit_save_prop' ) ) {
	/**
	 * Save prop or props.
	 *
	 * @param object       $object       The object.
	 * @param array|string $arg1         The key of the prop to set, or an array of props to set.
	 * @param false        $arg2         The value to set, or false if you want to set an array of props.
	 * @param false        $force_update Unused attribute.
	 *
	 * @deprecated 3.5 | use the WooCommerce CRUD directly instead.
	 */
	function yit_save_prop( $object, $arg1, $arg2 = false, $force_update = false ) {
		if ( ! is_array( $arg1 ) ) {
			$arg1 = array(
				$arg1 => $arg2,
			);
		}

		$is_wc_data = $object instanceof WC_Data;

		foreach ( $arg1 as $key => $value ) {
			yit_set_prop( $object, $key, $value );

			if ( ! $is_wc_data ) {

				if ( yit_wc_check_post_columns( $key ) ) {
					yit_store_changes( $object->post, $key, $value );
				} else {
					$object_id = is_callable( array( $object, 'get_id' ) ) ? $object->get_id() : $object->id;

					update_post_meta( $object_id, $key, $value );
				}
			}
		}

		if ( $is_wc_data ) {
			$object->save();
		}
	}
}

if ( ! function_exists( 'yit_delete_prop' ) ) {
	/**
	 * Delete a prop.
	 *
	 * @param object $object The object.
	 * @param string $key    The key.
	 * @param string $value  The value.
	 *
	 * @deprecated 3.5 | use the WooCommerce CRUD directly instead.
	 */
	function yit_delete_prop( $object, $key, $value = '' ) {
		$prop_map   = yit_return_new_attribute_map();
		$is_wc_data = $object instanceof WC_Data;

		if ( $is_wc_data ) {
			$key = ( array_key_exists( $key, $prop_map ) ) ? $prop_map[ $key ] : $key;

			$getter = false;
			$setter = false;
			if ( method_exists( $object, "get{$key}" ) ) {
				$getter = "get{$key}";
			} elseif ( method_exists( $object, "get_{$key}" ) ) {
				$getter = "get_{$key}";
			}

			if ( method_exists( $object, "set{$key}" ) ) {
				$setter = "set{$key}";
			} elseif ( method_exists( $object, "set_{$key}" ) ) {
				$setter = "set_{$key}";
			}

			if ( $setter && $getter && method_exists( $object, $setter ) && method_exists( $object, $getter ) && ( ! $value || $object->$getter === $value ) ) {
				$object->$setter( '' );
			} elseif ( ( ! $value || $object->get_meta( $key ) === $value ) ) {
				$object->delete_meta_data( $key, $value );
			}

			$object->save();
		} else {
			if ( yit_wc_check_post_columns( $key ) && ( ! $value || $object->post->$key === $value ) ) {
				yit_store_changes( $object->post, $key, '' );
			} else {
				$object_id = is_callable( array( $object, 'get_id' ) ) ? $object->get_id() : $object->id;

				delete_post_meta( $object_id, $key, $value );
			}
		}
	}
}

if ( ! function_exists( 'yit_return_new_attribute_map' ) ) {
	/**
	 * Return the attribute map array.
	 *
	 * @return string[]
	 * @deprecated 3.5
	 */
	function yit_return_new_attribute_map() {
		return array(
			'post_parent'                => 'parent_id',
			'post_title'                 => 'name',
			'post_status'                => 'status',
			'post_content'               => 'description',
			'post_excerpt'               => 'short_description',
			// Orders --------------------.
			'paid_date'                  => 'date_paid',
			'_paid_date'                 => '_date_paid',
			'completed_date'             => 'date_completed',
			'_completed_date'            => '_date_completed',
			'_order_date'                => '_date_created',
			'order_date'                 => 'date_created',
			'order_total'                => 'total',
			'customer_user'              => 'customer_id',
			'_customer_user'             => 'customer_id',
			// Products ---------------------.
			'visibility'                 => 'catalog_visibility',
			'_visibility'                => '_catalog_visibility',
			'sale_price_dates_from'      => 'date_on_sale_from',
			'_sale_price_dates_from'     => '_date_on_sale_from',
			'sale_price_dates_to'        => 'date_on_sale_to',
			'_sale_price_dates_to'       => '_date_on_sale_to',
			'product_attributes'         => 'attributes',
			'_product_attributes'        => '_attributes',
			// Coupons ---------------------.
			'coupon_amount'              => 'amount',
			'exclude_product_ids'        => 'excluded_product_ids',
			'exclude_product_categories' => 'excluded_product_categories',
			'customer_email'             => 'email_restrictions',
			'expiry_date'                => 'date_expires',
		);
	}
}

if ( ! function_exists( 'yit_store_changes' ) ) {
	/**
	 * Store changes
	 *
	 * @param object      $object The object.
	 * @param string      $key    The key.
	 * @param false|mixes $value  The value.
	 *
	 * @deprecated 3.5
	 */
	function yit_store_changes( $object, $key, $value = false ) {
		global $changed_objects;

		$is_wc_data = $object instanceof WC_Data;

		if ( $is_wc_data ) {
			$object_reference = $object->get_id();

			$changed_objects[ $object_reference ]['object']          = $object;
			$changed_objects[ $object_reference ]['changes'][ $key ] = $value;

		} else {
			$changed_objects[ $object->ID ][ $key ] = $value;
		}
	}
}

if ( ! function_exists( 'yit_send_changes_to_db' ) ) {
	/**
	 * Send changes to DB.
	 *
	 * @deprecated 3.5
	 */
	function yit_send_changes_to_db() {
		global $changed_objects;

		if ( ! empty( $changed_objects ) ) {
			foreach ( $changed_objects as $id => $data ) {
				if ( version_compare( WC()->version, '2.7.0', '>=' ) ) {
					$object = is_a( $data['object'], 'WC_Product' ) ? wc_get_product( $id ) : wc_get_order( $id );

					yit_set_prop( $object, $data['changes'] );
					$object->save();
				} else {
					$data['ID'] = $id;
					wp_update_post( $data );
				}
			}
		}
	}
}

if ( ! function_exists( 'yit_get_orders' ) ) {
	/**
	 * Retrieve orders
	 *
	 * @param array $args Arguments.
	 *
	 * @return WC_Order[]
	 * @deprecated 3.5 | use wc_get_orders instead.
	 */
	function yit_get_orders( $args ) {
		if ( version_compare( WC()->version, '2.7', '<' ) ) {
			$args['fields'] = 'objects';
			$posts          = get_posts( $args );

			return array_map( 'wc_get_order', $posts );
		} else {
			return wc_get_orders( $args );
		}
	}
}

if ( ! function_exists( 'yit_get_products' ) ) {
	/**
	 * Retrieve products.
	 *
	 * @param array $args Arguments.
	 *
	 * @return WC_Product[]
	 * @deprecated 3.5 | use wc_get_orders instead.
	 */
	function yit_get_products( $args ) {
		if ( version_compare( WC()->version, '2.7', '<' ) ) {
			$args['fields'] = 'objects';
			$posts          = get_posts( $args );

			return array_map( 'wc_get_product', $posts );
		} else {
			return wc_get_products( $args );
		}
	}
}

if ( ! function_exists( 'yit_update_product_stock' ) ) {
	/**
	 * Update product stock.
	 *
	 * @param WC_Product $product        The product.
	 * @param int        $stock_quantity The stock quantity.
	 * @param string     $operation      The operation. Available values: set, increase, decrease.
	 *
	 * @return bool|int|null
	 * @deprecated 3.5 | use wc_update_product_stock instead.
	 */
	function yit_update_product_stock( $product, $stock_quantity = 1, $operation = 'set' ) {
		if ( function_exists( 'wc_update_product_stock' ) ) {
			$stock = wc_update_product_stock( $product, $stock_quantity, $operation );
		} else {
			switch ( $operation ) {
				case 'increase':
					$stock = $product->increase_stock( $stock_quantity );
					break;
				case 'decrease':
					$stock = $product->reduce_stock( $stock_quantity );
					break;
				case 'set':
				default:
					$stock = $product->set_stock( $stock_quantity );
					break;
			}
		}

		return $stock;
	}
}

if ( ! function_exists( 'yit_wc_deprecated_filters' ) ) {
	/**
	 * Deprecated filters.
	 *
	 * @return mixed|void
	 * @deprecated 3.5
	 */
	function yit_wc_deprecated_filters() {
		$filters = array(
			'woocommerce_email_order_schema_markup'      => 'woocommerce_structured_data_order',
			'woocommerce_product_width'                  => 'woocommerce_product_get_width',
			'woocommerce_product_height'                 => 'woocommerce_product_get_height',
			'woocommerce_product_length'                 => 'woocommerce_product_get_length',
			'woocommerce_product_weight'                 => 'woocommerce_product_get_weight',
			'woocommerce_get_sku'                        => 'woocommerce_product_get_sku',
			'woocommerce_get_price'                      => 'woocommerce_product_get_price',
			'woocommerce_get_price'                      => 'woocommerce_product_variation_get_price',
			'woocommerce_get_regular_price'              => 'woocommerce_product_get_regular_price',
			'woocommerce_get_sale_price'                 => 'woocommerce_product_get_sale_price',
			'woocommerce_product_tax_class'              => 'woocommerce_product_get_tax_class',
			'woocommerce_get_stock_quantity'             => 'woocommerce_product_get_stock_quantity',
			'woocommerce_get_product_attributes'         => 'woocommerce_product_get_attributes',
			'woocommerce_product_gallery_attachment_ids' => 'woocommerce_product_get_gallery_image_ids',
			'woocommerce_product_review_count'           => 'woocommerce_product_get_review_count',
			'woocommerce_product_files'                  => 'woocommerce_product_get_downloads',
			'woocommerce_get_currency'                   => 'woocommerce_order_get_currency',
			'woocommerce_order_amount_discount_total'    => 'woocommerce_order_get_discount_total',
			'woocommerce_order_amount_discount_tax'      => 'woocommerce_order_get_discount_tax',
			'woocommerce_order_amount_shipping_total'    => 'woocommerce_order_get_shipping_total',
			'woocommerce_order_amount_shipping_tax'      => 'woocommerce_order_get_shipping_tax',
			'woocommerce_order_amount_cart_tax'          => 'woocommerce_order_get_cart_tax',
			'woocommerce_order_amount_total'             => 'woocommerce_order_get_total',
			'woocommerce_order_amount_total_tax'         => 'woocommerce_order_get_total_tax',
			'woocommerce_order_amount_total_discount'    => 'woocommerce_order_get_total_discount',
			'woocommerce_order_amount_subtotal'          => 'woocommerce_order_get_subtotal',
			'woocommerce_order_tax_totals'               => 'woocommerce_order_get_tax_totals',
			'woocommerce_refund_amount'                  => 'woocommerce_get_order_refund_get_amount',
			'woocommerce_refund_reason'                  => 'woocommerce_get_order_refund_get_reason',
			'default_checkout_country'                   => 'default_checkout_billing_country',
			'default_checkout_state'                     => 'default_checkout_billing_state',
			'default_checkout_postcode'                  => 'default_checkout_billing_postcode',
		);

		return apply_filters( 'yit_wc_deprecated_filters', $filters );
	}
}

if ( ! function_exists( 'yit_fix_wc_deprecated_filters' ) ) {
	/**
	 * Fix WooCommerce deprecated filters.
	 *
	 * @deprecated 3.5
	 */
	function yit_fix_wc_deprecated_filters() {
		if ( ! version_compare( WC()->version, '2.7.0', '<' ) ) {
			return;
		}

		$deprecated_filters = yit_wc_deprecated_filters();
		foreach ( $deprecated_filters as $old => $new ) {
			add_filter( $old, 'yit_wc_deprecated_filter_mapping', 10, 100 );
		}
	}
}

if ( ! function_exists( 'yit_wc_deprecated_filter_mapping' ) ) {
	/**
	 * Deprecated filter mapping.
	 *
	 * @return mixed
	 * @deprecated 3.5
	 */
	function yit_wc_deprecated_filter_mapping() {
		$deprecated_filters = yit_wc_deprecated_filters();

		$filter = current_filter();
		$args   = func_get_args();
		$data   = $args[0];

		if ( isset( $deprecated_filters[ $filter ] ) ) {
			if ( has_filter( $deprecated_filters[ $filter ] ) ) {
				$data = apply_filters_ref_array( $deprecated_filters[ $filter ], $args );
			}
		}

		return $data;
	}
}

if ( ! function_exists( 'yit_wc_check_post_columns' ) ) {
	/**
	 * Check the post columns.
	 *
	 * @param string $key The key.
	 *
	 * @return bool
	 * @deprecated 3.5
	 */
	function yit_wc_check_post_columns( $key ) {
		$columns = array(
			'post_author',
			'post_date',
			'post_date_gmt',
			'post_content',
			'post_title',
			'post_excerpt',
			'post_status',
			'comment_status',
			'ping_status',
			'post_password',
			'post_name',
			'to_ping',
			'pinged',
			'post_modified',
			'post_modified_gmt',
			'post_content_filtered',
			'post_parent',
			'guid',
			'menu_order',
			'post_type',
			'post_mime_type',
			'comment_count',
		);

		return in_array( $key, $columns, true );
	}
}


/*  Shortcuts for common functions   */

if ( ! function_exists( 'yit_get_order_id' ) ) {
	/**
	 * Retrieve the order id
	 *
	 * @param WC_Order $order The Order.
	 *
	 * @return int
	 * @deprecated 3.5 | use $order->get_id() instead.
	 */
	function yit_get_order_id( $order ) {
		return yit_get_prop( $order, 'id' );
	}
}

if ( ! function_exists( 'yit_get_product_id' ) ) {
	/**
	 * Retrieve the product id
	 *
	 * @param WC_Product $product The product.
	 *
	 * @return mixed
	 * @deprecated 3.5 | use $product->get_id() instead.
	 */
	function yit_get_product_id( $product ) {
		return yit_get_prop( $product, 'id' );
	}
}

if ( ! function_exists( 'yit_get_base_product_id' ) ) {
	/**
	 * Retrieve the parent product ID for WC_Product_Variation instances
	 * or the product ID in the other cases.
	 *
	 * @param WC_Product $product The product.
	 *
	 * @return int
	 */
	function yit_get_base_product_id( $product ) {

		return $product instanceof WC_Data && $product->is_type( 'variation' ) ?
			yit_get_prop( $product, 'parent_id' ) :
			yit_get_prop( $product, 'id' );
	}
}

if ( ! function_exists( 'yit_get_display_price' ) ) {
	/**
	 * Get the display price.
	 *
	 * @param WC_Product $product The product.
	 * @param string     $price   The price.
	 * @param int        $qty     The quantity.
	 *
	 * @return string The price to display
	 */
	function yit_get_display_price( $product, $price = '', $qty = 1 ) {
		if ( version_compare( WC()->version, '2.7.0', '>=' ) ) {
			$price = wc_get_price_to_display(
				$product,
				array(
					'qty'   => $qty,
					'price' => $price,
				)
			);
		} else {
			$price = $product->get_display_price( $price, $qty );
		}

		return $price;
	}
}

if ( ! function_exists( 'yit_get_price_excluding_tax' ) ) {
	/**
	 * Get price excluding taxes.
	 *
	 * @param WC_Product $product The product.
	 * @param int        $qty     The quantity.
	 * @param string     $price   The price.
	 *
	 * @return float|string
	 */
	function yit_get_price_excluding_tax( $product, $qty = 1, $price = '' ) {
		if ( version_compare( WC()->version, '2.7.0', '>=' ) ) {
			$price = wc_get_price_excluding_tax(
				$product,
				array(
					'qty'   => $qty,
					'price' => $price,
				)
			);
		} else {
			$price = $product->get_price_excluding_tax( $qty, $price );
		}

		return $price;
	}
}

if ( ! function_exists( 'yit_get_price_including_tax' ) ) {
	/**
	 * Get price including taxes.
	 *
	 * @param WC_Product $product The product.
	 * @param int        $qty     The quantity.
	 * @param string     $price   The price.
	 *
	 * @return float|string
	 */
	function yit_get_price_including_tax( $product, $qty = 1, $price = '' ) {
		if ( version_compare( WC()->version, '2.7.0', '>=' ) ) {
			$price = wc_get_price_including_tax(
				$product,
				array(
					'qty'   => $qty,
					'price' => $price,
				)
			);
		} else {
			$price = $product->get_price_including_tax( $qty, $price );
		}

		return $price;
	}
}

if ( ! function_exists( 'yit_get_product_image_id' ) ) {
	/**
	 * Get the product image ID
	 *
	 * @param WC_Product $product The product.
	 * @param string     $context What the value is for. Valid values are view and edit.
	 *
	 * @return mixed
	 * @deprecated 3.5 | use $product->get_image_id() instead.
	 */
	function yit_get_product_image_id( $product, $context = 'view' ) {
		if ( version_compare( WC()->version, '2.7.0', '>=' ) ) {
			$image_id = $product->get_image_id( $context );
		} else {
			$image_id = $product->get_image_id();
		}

		return $image_id;
	}
}

if ( ! function_exists( 'yit_get_refund_amount' ) ) {
	/**
	 * Get the refund amount.
	 *
	 * @param WC_Order_Refund $refunded_order The Refunded order.
	 * @param string          $context        What the value is for. Valid values are view and edit.
	 *
	 * @return float
	 * @deprecated 3.5 | use $refunded_order->get_amount() instead.
	 */
	function yit_get_refund_amount( $refunded_order, $context = 'view' ) {
		$is_wc_data = $refunded_order instanceof WC_Data;

		if ( $is_wc_data ) {
			return $refunded_order->get_amount( $context );
		} else {
			return $refunded_order->get_refund_amount();
		}
	}
}

if ( ! function_exists( 'yit_set_refund_amount' ) ) {
	/**
	 * Set the refund amount.
	 *
	 * @param WC_Order_Refund $refunded_order The Refunded order.
	 * @param float           $amount         The amount.
	 *
	 * @throws WC_Data_Exception Exception if the amount is invalid.
	 * @deprecated 3.5 | use $refunded_order->set_amount() instead.
	 */
	function yit_set_refund_amount( $refunded_order, $amount ) {
		$is_wc_data = $refunded_order instanceof WC_Data;

		if ( $is_wc_data ) {
			$refunded_order->set_amount( $amount );
		} else {
			$refunded_order->refund_amount = $amount;
		}
	}
}

if ( ! function_exists( 'yit_get_refund_reason' ) ) {
	/**
	 * Retrieve the refund reason.
	 *
	 * @param WC_Order_Refund $refunded_order The Refunded order.
	 *
	 * @return string
	 * @deprecated 3.5 | use $refunded_order->get_reason() instead.
	 */
	function yit_get_refund_reason( $refunded_order ) {
		$is_wc_data = $refunded_order instanceof WC_Data;

		if ( $is_wc_data ) {
			return $refunded_order->get_reason();
		} else {
			return $refunded_order->get_refund_reason();
		}
	}
}

if ( ! function_exists( 'yit_product_visibility_meta' ) ) {
	/**
	 * Visibility meta query.
	 *
	 * @param array $args Arguments.
	 *
	 * @return array
	 */
	function yit_product_visibility_meta( $args ) {
		if ( version_compare( WC()->version, '2.7.0', '<' ) ) {
			$args['meta_query']   = isset( $args['meta_query'] ) ? $args['meta_query'] : array(); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			$args['meta_query'][] = WC()->query->visibility_meta_query();
		} elseif ( taxonomy_exists( 'product_visibility' ) ) {
			$product_visibility_term_ids = wc_get_product_visibility_term_ids();
			$args['tax_query']           = isset( $args['tax_query'] ) ? $args['tax_query'] : array(); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
			$args['tax_query'][]         = array(
				'taxonomy' => 'product_visibility',
				'field'    => 'term_taxonomy_id',
				'terms'    => is_search() ? $product_visibility_term_ids['exclude-from-search'] : $product_visibility_term_ids['exclude-from-catalog'],
				'operator' => 'NOT IN',
			);
		}

		return $args;
	}
}

if ( ! function_exists( 'yit_datetime_to_timestamp' ) ) {
	/**
	 * Convert string date to timestamp.
	 *
	 * @param string $date The date.
	 *
	 * @return false|int
	 */
	function yit_datetime_to_timestamp( $date ) {
		if ( ! is_int( $date ) ) {
			$date = strtotime( $date );
		}

		return $date;
	}
}

yit_fix_wc_deprecated_filters();
add_action( 'shutdown', 'yit_send_changes_to_db' );
