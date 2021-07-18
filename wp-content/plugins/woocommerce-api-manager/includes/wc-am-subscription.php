<?php

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * WooCommerce API Manager Subscription Class
 * Compatiblity class for WooCommerce Subscriptions.
 *
 * @since       1.5
 *
 * @author      Todd Lahman LLC
 * @copyright   Copyright (c) Todd Lahman LLC
 * @package     WooCommerce API Manager/Subscription
 */
class WC_AM_Subscription {

	private $api_resource_table   = '';
	private $api_activation_table = '';

	/**
	 * @var null
	 */
	private static $_instance = null;

	/**
	 * @static
	 * @return \WC_AM_Subscription
	 */
	public static function instance() {

		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	private function __construct() {
		if ( WCAM()->get_wc_subs_exist() ) {
			$this->api_resource_table   = WC_AM_USER()->get_api_resource_table_name();
			$this->api_activation_table = WC_AM_USER()->get_api_activation_table_name();

			/**
			 * Passes $post_id.
			 *
			 * @see   update_status() in WC_Subscription
			 * @since 1.5
			 */
			add_action( 'woocommerce_subscription_status_cancelled', array( $this, 'delete_expired_subscription_and_activations' ) );
			add_action( 'woocommerce_subscription_status_expired', array( $this, 'delete_expired_subscription_and_activations' ) );
			add_action( 'subscriptions_cancelled_for_order', array( $this, 'delete_expired_subscription_and_activations' ) );
			add_action( 'subscriptions_expired_for_order', array( $this, 'delete_expired_subscription_and_activations' ) );
			add_action( 'woocommerce_subscription_status_cancelled', array( $this, 'delete_subscription' ) );
			add_action( 'woocommerce_subscription_status_expired', array( $this, 'delete_subscription' ) );
			add_action( 'woocommerce_subscription_trashed', array( $this, 'delete_subscription' ) );
			add_action( 'woocommerce_subscription_deleted', array( $this, 'delete_subscription' ) );
			add_action( 'woocommerce_api_delete_subscription', array( $this, 'delete_subscription' ) );
			add_action( 'woocommerce_subscription_item_switched', array( $this, 'update_order' ), 10, 4 );
			add_action( 'woocommerce_subscription_status_changed', array( $this, 'refresh_cache' ) );
		}
	}

	/**
	 * Get the subscription object.
	 *
	 * @since 2.0
	 *
	 * @param int|WC_Subscription $subscription
	 *
	 * @return \WC_Subscription
	 */
	public function get_subscription_object( $subscription ) {
		return is_object( $subscription ) ? $subscription : wcs_get_subscription( $subscription );
	}

	/**
	 * Returns the user subscription status fomratted for human eyes
	 * For WooCommerce >= 2.0
	 *
	 * @since 1.3.9.8
	 *
	 * @param int $post_id
	 *
	 * @return bool|string
	 */
	public function get_user_subscription_status( $post_id ) {
		global $wpdb;

		$status = false;

		if ( ! empty( $post_id ) ) {
			$post_status = $wpdb->get_var( $wpdb->prepare( "
				SELECT 		post_status
				FROM 		{$wpdb->prefix}posts
				WHERE 		post_parent = %d
				AND 		post_type = %s
			", absint( $post_id ), esc_attr( 'shop_subscription' ) ) );

			if ( ! empty( $post_status ) ) {
				$status = wcs_get_subscription_status_name( $post_status );
			}

			if ( empty( $status ) ) {
				/**
				 * This could be a switched subscription order which has no post_parent.
				 *
				 * @since 1.5
				 */
				$switched = $this->is_subscription_switch_order( $post_id );

				if ( $switched || $this->is_subscription_switched_status( $post_id ) ) {
					$id = $this->get_subscription_switch_order_parent_id( $post_id );

					if ( $id ) {
						$post_status = $wpdb->get_var( $wpdb->prepare( "
							SELECT 		post_status
							FROM 		{$wpdb->prefix}posts
							WHERE 		ID = %d
							AND 		post_type = %s
						", absint( $id ), esc_attr( 'shop_subscription' ) ) );
					}

					if ( ! empty( $post_status ) ) {
						$status = wcs_get_subscription_status_name( $post_status );
					}

					return ! is_wp_error( $status ) && ! empty( $status ) ? $status : false;
				}

				return false;
			}

			return ! is_wp_error( $status ) && ! empty( $status ) ? $status : false;
		}

		return false;
	}

	/**
	 * Returns the user subscription ID.
	 *
	 * @since 1.3.9.8
	 *
	 * @param int $order_id Subscription order ID.
	 *
	 * @return bool|null|string
	 */
	public function get_subscription_id( $order_id ) {
		global $wpdb;

		if ( ! is_int( $order_id ) ) {
			$order = WC_AM_ORDER_DATA_STORE()->get_order_object( $order_id );

			if ( is_object( $order ) ) {
				$order_id = $order->get_id();
			}
		}

		if ( ! empty( $order_id ) ) {
			if ( WCAM()->is_woocommerce_pre( '3.0' ) ) {
				$sub_id = $wpdb->get_var( $wpdb->prepare( "
					SELECT 	ID
					FROM 	{$wpdb->prefix}posts
					WHERE 	post_parent = %d
					AND 	post_type = %s
				", absint( $order_id ), esc_attr( 'shop_subscription' ) ) );

				/**
				 * This could be a switched subscription order, which has the sub ID in the postmeta.
				 *
				 * @since 1.5
				 */
				if ( empty( $sub_id ) ) {
					$switched = $this->is_subscription_switch_order( $order_id );

					if ( $switched ) {
						$sub_id = $this->get_subscription_switch_order_parent_id( $order_id );
					}
				}

				return ! empty( $sub_id ) ? $sub_id : false;
			}

			/**
			 * @since 2.0
			 */
			$subscription = $this->get_last_subscription_for_order( $order_id );

			if ( $subscription ) {
				return $subscription->get_id();
			}
		}

		return false;
	}

	/**
	 * Get parent Order ID for renewal, resubscribe, and/or switch orders of a subscription.
	 *
	 * @since 2.0
	 *
	 * @param int $order_id
	 *
	 * @return bool|int
	 */
	public function get_parent_id( $order_id ) {
		$parent       = 0;
		$sub_id       = $this->get_subscription_id( $order_id );
		$subscription = $this->get_subscription_object( $sub_id );

		if ( is_object( $subscription ) ) {
			$parent = $subscription->get_parent_id();

			if ( empty( $parent ) ) {
				$parent = $this->get_subscription_parent_order_id( $order_id );
			}
		}

		return ! empty( $parent ) ? $parent : false;
	}

	/**
	 * Returns a parent Order ID for renewal, resubscribe, and/or switch orders of a subscription.
	 *
	 * @since 1.5
	 *
	 * @param int $post_id Subscription order ID.
	 *
	 * @return bool|null|string
	 */
	public function get_subscription_parent_order_id( $post_id ) {
		global $wpdb;

		$sub_id = $this->get_subscription_id( $post_id );

		if ( ! empty( $sub_id ) ) {
			$parent_id = $wpdb->get_var( $wpdb->prepare( "
				SELECT 		post_parent
				FROM 		{$wpdb->prefix}posts
				WHERE 		ID = %d
				AND 		post_type = %s
			", absint( $sub_id ), esc_attr( 'shop_subscription' ) ) );

			return ! empty( $parent_id ) ? $parent_id : false;
		}

		return false;
	}

	/**
	 * Returns the user subscription order_key
	 * For WooCommerce >= 2.0
	 *
	 * @since 1.3.9.8
	 *
	 * @param int $sub_id
	 *
	 * @return bool|mixed
	 */
	public function get_subscription_order_key( $sub_id ) {
		if ( WCAM()->is_woocommerce_pre( '3.0' ) ) {
			$order_key = get_post_meta( $sub_id, '_order_key', true );

			return ! empty( $order_key ) ? $order_key : false;
		}

		$subscription = $this->get_subscription_object( $sub_id );

		return is_object( $subscription ) ? $subscription->get_order_key() : false;
	}

	/**
	 * Gets the Parent Post_ID of a switched subscription order.
	 *
	 * @since 1.5
	 *
	 * @param int $order_id
	 *
	 * @return bool|mixed
	 */
	public function get_subscription_switch_order_parent_id( $order_id ) {
		$sub_parent_id = get_post_meta( $order_id, '_subscription_switch', true );

		return ! empty( $sub_parent_id ) ? $sub_parent_id : false;
	}

	/**
	 * Returns the last subscription ID that contains the subscription object.
	 *
	 * @since 2.0
	 *
	 * @param int|WC_Order $order_id The post_id of a shop_order post or an instance of a WC_Order object
	 *
	 * @return mixed An array containing an object.
	 */
	public function get_last_subscription_for_order( $order_id ) {
		/**
		 * Storing sub in variable first avoids PHP notice:
		 * Only variables should be passed by reference.
		 */
		$sub = wcs_get_subscriptions_for_order( $order_id );

		return array_shift( $sub );
	}

	/**
	 * Returns a subscription array that contains the subscription object.
	 *
	 * @since 2.0
	 *
	 * @param int|WC_Order $order_id The post_id of a shop_order post or an instance of a WC_Order object
	 *
	 * @return mixed An array containing an object.
	 */
	public function get_all_subscriptions_for_order( $order_id ) {
		return wcs_get_subscriptions_for_order( $order_id );
	}

	/**
	 * Get all subscription IDs for an order. Could be one or more depending on the line items.
	 *
	 * @since 2.0
	 *
	 * @param int $order_id
	 *
	 * @return array
	 */
	public function get_all_subscription_ids_for_order( $order_id ) {
		$ids           = array();
		$subscriptions = $this->get_all_subscriptions_for_order( $order_id );

		if ( ! empty( $subscriptions ) ) {
			foreach ( $subscriptions as $subscription_id => $subscription_object ) {
				$ids[] = $subscription_id;
			}
		}

		return $ids;
	}

	/**
	 * Returns the status of a subscription.
	 *
	 * @since 2.0
	 *
	 * @param int|object $subscription
	 *
	 * @return bool|string
	 */
	public function get_subscription_status( $subscription ) {
		$subscription = $this->get_subscription_object( $subscription );

		if ( ! is_object( $subscription ) ) {
			return false;
		}

		$status = $subscription->get_status();

		return ! empty( $status ) ? $subscription->get_status() : false;
	}

	/**
	 * Returns switched subscription item id from previous order that was switched from.
	 *
	 * @since 2.0
	 *
	 * @param int $sub_item_id subscription item ID from previous order.
	 *
	 * @return mixed
	 * @throws \Exception
	 */
	public function get_previous_subscription_order_item_id( $sub_item_id ) {
		return WC_AM_ORDER_DATA_STORE()->get_order_item_meta( $sub_item_id, '_switched_subscription_item_id' );
	}

	/**
	 * Get the subscription expiration timestamp.
	 *
	 * @since 2.0
	 *
	 * @param Object|int $order
	 * @param int        $product_id
	 *
	 * @return bool|int|mixed
	 */
	public function get_subscription_expiration_timestamp( $order, $product_id ) {
		$sub_id       = $this->get_subscription_id( $order );
		$subscription = $this->get_subscription_object( $sub_id );

		return is_object( $subscription ) ? $subscription->get_time( 'next_payment' ) : false;
	}

	/**
	 * Get the subscription end date to display as human readable.
	 *
	 * @since 2.0
	 *
	 * @param Object|int $order
	 * @param int        $product_id
	 *
	 * @return bool|mixed
	 */
	public function get_subscription_end_date_to_display( $order, $product_id ) {
		$sub_id       = $this->get_subscription_id( $order );
		$subscription = $this->get_subscription_object( $sub_id );

		return is_object( $subscription ) ? $subscription->get_date_to_display( 'end' ) : false;
	}

	/**
	 * Get the related order IDs for a subscription based on an order type. Default type is any.
	 *
	 * @since 2.0
	 *
	 * @param int    $order_id
	 * @param string $order_type Can include 'any', 'parent', 'renewal', 'resubscribe' and/or 'switch'. Defaults to 'any'.
	 *
	 * @return array
	 */
	public function get_related_order_ids( $order_id, $order_type = 'any' ) {
		$related_order_ids = array();

		if ( class_exists( 'WCS_Related_Order_Store' ) ) {
			$parent_id = $this->get_parent_id( $order_id );

			if ( in_array( $order_type, array( 'any', 'parent' ) ) && $parent_id ) {
				$related_order_ids[ $parent_id ] = $parent_id;
			}

			if ( 'parent' !== $order_type ) {
				$sub_id       = $this->get_subscription_id( $order_id );
				$subscription = $this->get_subscription_object( $sub_id );

				if ( is_object( $subscription ) ) {
					$relation_types = ( 'any' === $order_type ) ? array( 'renewal', 'resubscribe', 'switch' ) : array( $order_type );

					foreach ( $relation_types as $relation_type ) {
						$related_order_ids = array_merge( $related_order_ids, WCS_Related_Order_Store::instance()->get_related_order_ids( $subscription, $relation_type ) );
					}
				}
			}
		}

		return $related_order_ids;
	}

	/**
	 * Return the previous to the current order ID of the last order related to a subscription.
	 *
	 * @since 2.0
	 *
	 * @param int $order_id
	 *
	 * @return mixed
	 */
	public function get_previous_order_id( $order_id ) {
		$order_ids = $this->get_related_order_ids( $order_id );
		// Reverse sort.
		rsort( $order_ids );
		// Remove the first (largest) array element.
		array_shift( $order_ids );

		// Return the largest element from the array, which will be the previous order number.
		return ! empty( $order_ids ) ? max( $order_ids ) : false;
	}

	/**
	 * Return the last order ID of the last order related to a subscription.
	 *
	 * @since 2.0
	 *
	 * @param int $order_id
	 *
	 * @return mixed
	 */
	public function get_last_order_id( $order_id ) {
		$order_ids = $this->get_related_order_ids( $order_id );

		return ! empty( $order_ids ) ? max( $order_ids ) : false;
	}

	/**
	 * Get the current subscription order item ID(s).
	 *
	 * @since 2.0
	 *
	 * @param int|object $subscription
	 *
	 * @return bool|int|null|string
	 */
	public function get_order_item_ids( $subscription ) {
		return WC_AM_ORDER_DATA_STORE()->get_order_item_ids( $subscription );
	}

	/**
	 * Returns a formatted array of subscription line item data from an order.
	 *
	 * @since 2.0
	 *
	 * @param int $order_id
	 * @param int $new_api_product_activations
	 *
	 * @return array
	 *
	 * @throws \Exception
	 */
	public function get_subscription_line_item_data_from_order( $order_id, $new_api_product_activations = 0 ) {
		$values                = array();
		$items                 = array();
		$sub_ids               = $this->get_all_subscription_ids_for_order( $order_id );
		$sub_previous_order_id = $this->get_previous_order_id( $order_id );
		$sub_previous_order_id = $sub_previous_order_id ? $sub_previous_order_id : $order_id;

		if ( ! empty( $sub_ids ) ) {
			foreach ( $sub_ids as $k => $subscription_id ) {
				$subscription = WC_AM_ORDER_DATA_STORE()->get_order_object( $subscription_id );

				if ( is_object( $subscription ) && WC_AM_FORMAT()->count( $subscription->get_items() ) > 0 ) {
					$obj_data = $subscription->get_data();

					foreach ( $subscription->get_items() as $item_id => $item ) {
						$data           = $item->get_data();
						$is_item_on_sub = $this->is_subscription_line_item_on_subscription( $item_id, $data[ 'order_id' ] );
						$valid_product  = WC_AM_PRODUCT_DATA_STORE()->has_valid_product_status( $data[ 'product_id' ] );
						$is_api         = WC_AM_PRODUCT_DATA_STORE()->is_api_product( $data[ 'product_id' ] );
						$is_wc_sub      = WC_AM_SUBSCRIPTION()->is_wc_subscription( $data[ 'product_id' ] );

						// Only store API resource data for API products that have an order status of completed, and are WooCommerce Subscription products.
						if ( $is_api && $valid_product && $is_wc_sub && $is_item_on_sub ) {
							// Make sure WooCommerce Subscription product has an active subscription.

							/**
							 * Can only check this if woocommerce_order_status_changed is used in Order class, otherwise check will failed, since WooCommerce Subscription status is
							 * set on that hook, but then it fires after the order completed emails are sent. :(
							 */ //$is_active = $this->is_subscription_for_order_active( $data[ 'order_id' ] );

							//if ( $is_active ) {
							//if ( $is_api && $is_wc_sub && $valid_product ) {
							//$values[ 'sub_title' ]             = $data[ 'name' ];
							$variation_id                      = ! empty( $data[ 'variation_id' ] ) && WC_AM_PRODUCT_DATA_STORE()->has_valid_product_status( $data[ 'variation_id' ] ) ? $data[ 'variation_id' ] : 0;
							$item_qty                          = $data[ 'quantity' ];
							$values[ 'sub_id' ]                = $data[ 'order_id' ];
							$values[ 'sub_item_id' ]           = $item_id;
							$values[ 'sub_order_key' ]         = $obj_data[ 'order_key' ];
							$values[ 'sub_parent_id' ]         = $obj_data[ 'parent_id' ];
							$values[ 'sub_previous_order_id' ] = $sub_previous_order_id;
							$values[ 'user_id' ]               = $obj_data[ 'customer_id' ];
							$values[ 'variation_id' ]          = $variation_id;
							$values[ 'parent_id' ]             = $data[ 'product_id' ];
							$values[ 'product_id' ]            = ! empty( $variation_id ) ? $variation_id : $values[ 'parent_id' ];
							$values[ 'item_qty' ]              = $item_qty;
							$refund_qty                        = WC_AM_ORDER_DATA_STORE()->get_qty_refunded_for_product_id( $order_id, $values[ 'product_id' ] );
							$values[ 'refund_qty' ]            = absint( $refund_qty );

							if ( $values[ 'refund_qty' ] >= $values[ 'item_qty' ] ) {
								continue;
							}

							$api_product_activations       = WC_AM_PRODUCT_DATA_STORE()->get_api_activations( $values[ 'product_id' ] );
							$values[ 'api_activations' ]   = ! empty( $api_product_activations ) ? $api_product_activations : apply_filters( 'wc_api_manager_custom_default_api_activations', 1, $values[ 'product_id' ] );
							$product_object                = WC_AM_PRODUCT_DATA_STORE()->get_product_object( $values[ 'product_id' ] );
							$values[ 'product_title' ]     = is_object( $product_object ) ? $product_object->get_title() : '';
							$values[ 'status' ]            = $obj_data[ 'status' ];
							$values[ 'activations_total' ] = ( $values[ 'api_activations' ] * $item_qty ) + ( $refund_qty * $values[ 'api_activations' ] );

							if ( empty( $values[ 'api_activations' ] ) ) {
								$values[ 'api_activations' ]   = apply_filters( 'wc_api_manager_custom_default_api_activations', 1, $values[ 'product_id' ] );
								$values[ 'activations_total' ] = ( $values[ 'api_activations' ] * $item_qty ) + ( $refund_qty * $values[ 'api_activations' ] );
							}

							$items[] = $values;
							//}
						}
					}
				}
			}

			return $items;
		}

		return array();
	}

	/**
	 * Return array of order_item_id for subscription. Results ordered by most recent order_item_id.
	 *
	 * @since 2.0
	 *
	 * @param int|object $subscription
	 *
	 * @return array|bool
	 */
	public function get_subscription_line_item_ids( $subscription ) {
		$sub_item_ids = false;
		$subscription = $this->get_subscription_object( $subscription );

		if ( is_object( $subscription ) ) {
			$sub_item_ids = $this->get_line_items_ids_from_subscription( $subscription );

			if ( empty( $sub_item_ids ) && ! empty( $subscription->get_id() ) ) {
				$sub_item_ids = WC_AM_ORDER_DATA_STORE()->get_item_ids( $subscription->get_id(), 'line_item' );
			}
		}

		return is_array( $sub_item_ids ) && ! empty( $sub_item_ids ) ? $sub_item_ids : false;
	}

	/**
	 * Return array of order_item_id, which are used to index (keys) subscription line item properties.
	 *
	 * @since 2.0
	 *
	 * @param $subscription
	 *
	 * @return array|bool
	 */
	public function get_line_items_ids_from_subscription( $subscription ) {
		$subscription = $this->get_subscription_object( $subscription );
		$sub_items    = is_object( $subscription ) ? $subscription->get_items() : false;

		return is_array( $sub_items ) && ! empty( $sub_items ) ? array_keys( $sub_items ) : false;
	}

	/**
	 * Return array of line_item_switched items for subscription.
	 *
	 * @since 2.0
	 *
	 * @param int|object $subscription
	 *
	 * @return array|bool
	 */
	public function get_subscription_switched_line_items( $subscription ) {
		$subscription = $this->get_subscription_object( $subscription );

		return is_object( $subscription ) ? WC_AM_ORDER_DATA_STORE()->get_item_ids( $subscription->get_id(), $type = 'line_item_switched' ) : false;
	}

	/**
	 * Get the subscription ID by the order item ID. Relies on a database table.
	 *
	 * @since 2.0
	 *
	 * @param int $item_id
	 *
	 * @return int
	 * @throws \Exception
	 */
	public function get_susubscription_id_by_order_item_id( $item_id ) {
		return WC_AM_ORDER_DATA_STORE()->get_order_id_by_order_item_id( $item_id );
	}

	/**
	 * Finds the subscription ID using the item ID, then confirms the it is a line_item on the subscription.
	 * If not, return false.
	 * This is useful if subscription status is not active or pending-cancel yet, such as when it is still
	 * pending status as the order is processed.
	 *
	 * @since 2.0
	 *
	 * @param int $item_id
	 * @param int $sub_id
	 *
	 * @return bool
	 * @throws \Exception
	 */
	public function is_subscription_line_item_on_subscription( $item_id, $sub_id = 0 ) {
		$exists = false;
		$sub_id = ! empty( $sub_id ) ? $sub_id : $this->get_susubscription_id_by_order_item_id( $item_id );

		if ( ! empty( $sub_id ) ) {
			$items = $this->get_subscription_line_item_ids( $sub_id );

			if ( ! empty( $items ) ) {
				foreach ( $items as $k => $id ) {
					if ( (int) $id == (int) $item_id ) {
						// The line item exists on the subscription.
						$exists = true;
					}
				}
			}
		}

		// Is the subscription active or pending-cancel status, or neither.
		return $exists;
	}

	/**
	 * Finds the subscription ID using the item ID, then confirms the it is a line_item on the subscription.
	 * If not, return false, otherwise return the status which should be true for active or pending-cancel.
	 *
	 * TODO: Retest. Was not working consistently on new subscription orders.
	 *
	 * @since 2.0
	 *
	 * @param int $item_id
	 * @param int $sub_id
	 *
	 * @return bool
	 * @throws \Exception
	 */
	public function is_subscription_line_item_on_active_subscription( $item_id, $sub_id = 0 ) {
		$sub_id = ! empty( $sub_id ) ? $sub_id : $this->get_susubscription_id_by_order_item_id( $item_id );
		$exists = $this->is_subscription_line_item_on_subscription( $item_id, $sub_id );

		// Is the subscription active or pending-cancel status, or neither.
		return ! empty( $exists ) && ! empty( $sub_id ) ? $this->is_subscription_for_order_active( $sub_id ) : false;
	}

	/**
	 * Determine if an order is a subscription renewal order or not.
	 * For WooCommerce >= 2.0
	 *
	 * @since 1.3.9.8
	 *
	 * @param WC_Order|int $order The WC_Order object or ID of a WC_Order order.
	 *
	 * @return bool|mixed
	 */
	public function is_subscription_renewal_order( $order ) {
		if ( function_exists( 'wcs_order_contains_renewal' ) ) {
			return wcs_order_contains_renewal( $order );
		}

		return false;
	}

	/**
	 * Determine if an order is a subscription resubscribe order or not.
	 * For WooCommerce >= 2.0
	 *
	 * @since 1.5
	 *
	 * @param WC_Order|int $order The WC_Order object or ID of a WC_Order order.
	 *
	 * @return bool
	 */
	public function is_subscription_resubscribe_order( $order ) {
		if ( function_exists( 'wcs_order_contains_resubscribe' ) ) {
			return wcs_order_contains_resubscribe( $order );
		}

		return false;
	}

	/**
	 * Determine if an order is a subscription switch order or not.
	 * For WooCommerce >= 2.0
	 *
	 * @since 1.5
	 *
	 * @param WC_Order|int $order The WC_Order object or ID of a WC_Order order.
	 *
	 * @return bool
	 */
	public function is_subscription_switch_order( $order ) {
		if ( function_exists( 'wcs_order_contains_switch' ) ) {
			return wcs_order_contains_switch( $order );
		}

		return false;
	}

	/**
	 * Returns true if the user subscription is still active
	 * For WooCommerce >= 2.0
	 *
	 * @since 1.3.9.8
	 *
	 * @param string $status
	 *
	 * @return bool
	 */
	public function is_user_subscription_active( $status ) {
		return $status == 'Active' || $status == 'Pending Cancellation' ? true : false;
	}

	/**
	 * Returns true if is a WC Subscription product.
	 *
	 * @since 2.0
	 *
	 * @param int $product_id
	 *
	 * @return bool
	 */
	public function is_wc_subscription( $product_id ) {
		$product = WC_AM_PRODUCT_DATA_STORE()->get_product_object( $product_id );

		if ( is_object( $product ) && $product->is_type( array(
			                                                 'subscription',
			                                                 'simple-subscription',
			                                                 'variable-subscription',
			                                                 'subscription_variation'
		                                                 ) ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Returns true if a subscription has an active or pending-cancel status.
	 *
	 * @since 2.0
	 *
	 * @param int|object $subscription
	 *
	 * @return bool
	 */
	public function is_subscription_for_order_active( $subscription ) {
		$subscription = $this->get_subscription_object( $subscription );

		if ( is_object( $subscription ) ) {
			$status = $this->is_user_subscription_active( wcs_get_subscription_status_name( get_post_status( $subscription->get_id() ) ) );

			if ( $status ) {
				return true;
			}

			return $subscription->has_status( array( 'active', 'pending-cancel' ) ) ? true : false;
		}

		return false;
	}

	/**
	 * Return true if the subscription is not cancelled, expired, or switched.
	 *
	 * @since 2.0
	 *
	 * @param int|object $subscription
	 *
	 * @return bool
	 */
	public function is_subscription_cancelled_status( $subscription ) {
		$subscription = $this->get_subscription_object( $subscription );

		return is_object( $subscription ) && $subscription->has_status( array(
			                                                                'cancelled',
			                                                                'expired',
			                                                                'trash',
			                                                                'switched'
		                                                                ) ) ? true : false;
	}

	/**
	 * Return true if the subscription is not cancelled, expired, or switched.
	 *
	 * @since 2.0
	 *
	 * @param int|object $subscription
	 *
	 * @return bool
	 */
	public function is_subscription_switched_status( $subscription ) {
		$subscription = $this->get_subscription_object( $subscription );

		return is_object( $subscription ) && $subscription->has_status( 'switched' ) ? true : false;
	}

	/**
	 * Return true if the subscription is switched.
	 *
	 * @since 2.0
	 *
	 * @param int|object $subscription
	 *
	 * @return bool
	 */
	public function is_subscription_switched( $subscription ) {
		$subscription = $this->get_subscription_object( $subscription );

		return is_object( $subscription ) && $subscription->has_status( 'switched' ) ? true : false;
	}

	/**
	 * Verifies the user has an active subscription for a specific product.
	 *
	 * @since 2.3.9
	 *
	 * @param int $user_id
	 * @param int $product_id
	 *
	 * @return bool
	 */
	function user_has_active_product_subscription( $user_id, $product_id ) {
		$subscriptions = wcs_get_users_subscriptions( $user_id );

		foreach ( $subscriptions as $subscription ) {
			if ( $subscription->has_product( $product_id ) && ( $subscription->has_status( 'active' ) || $subscription->has_status( 'pending-cancel' ) ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Delete expired subscription API Keys and API Key activations.
	 *
	 * @since 2.0
	 *
	 * @param object $order
	 *
	 * @throws \Exception
	 */
	public function delete_expired_subscription_and_activations( $order ) {
		global $wpdb;

		$order    = wc_get_order( $order );
		$order_id = $order->get_id();

		/**
		 * Delete the activations assigned to resources that are assigned to this order_id.
		 */
		$activation_ids = WC_AM_API_ACTIVATION_DATA_STORE()->get_activations_by_subscription_order_id( $order_id );

		if ( $activation_ids ) {
			foreach ( $activation_ids as $k => $activation_id ) {
				WC_AM_API_ACTIVATION_DATA_STORE()->delete_api_key_activation_by_activation_id( $activation_id );
			}
		}

		/**
		 * Delete the subscription API resource.
		 */
		$where = array(
			'sub_id' => $order_id
		);

		$where_format = array(
			'%d'
		);

		$wpdb->delete( $wpdb->prefix . $this->api_resource_table, $where, $where_format );

		WC_AM_SMART_CACHE()->refresh_cache_by_order_id( $order_id );
	}

	/**
	 * Delete the API resource order item when the subscription is cancelled or expired.
	 *
	 * @since 2.0
	 *
	 * @param int|WC_Subscription $subscription
	 *
	 * @throws \Exception
	 */
	public function delete_subscription( $subscription ) {
		global $wpdb;

		$subscription = $this->get_subscription_object( $subscription );

		if ( is_object( $subscription ) ) {
			$sub_id   = $subscription->get_id();
			$order_id = WC_AM_API_RESOURCE_DATA_STORE()->get_order_id_by_sub_id( $sub_id );

			/**
			 * Delete the activations assigned to resources that are assigned to this order_id.
			 */
			$activation_ids = WC_AM_API_ACTIVATION_DATA_STORE()->get_activations_by_subscription_order_id( $sub_id );

			if ( $activation_ids ) {
				foreach ( $activation_ids as $k => $activation_id ) {
					WC_AM_API_ACTIVATION_DATA_STORE()->delete_api_key_activation_by_activation_id( $activation_id );
				}
			}

			$where = array(
				'sub_id' => $sub_id
			);

			$where_format = array(
				'%d'
			);

			$wpdb->delete( $wpdb->prefix . $this->api_resource_table, $where, $where_format );

			WC_AM_SMART_CACHE()->refresh_cache_by_order_id( $order_id );
		}
	}

	/**
	 * Removes old API Resource, then creates new API Resource when the Subscription is switched.
	 *
	 * Why are activations deleted?
	 *
	 * Activations are created by the client who sends an API Key and Product ID. When a subscription
	 * is switched it gets a new Product ID, since it is a Variable Product variation with its own
	 * unique Product ID. The client will now have to activate the software again using the new
	 * Product ID. The API Key will remain the same if it is the Master API Key, but would change
	 * if the Product Order API Key was used.
	 *
	 * @since 2.1
	 *
	 * @param object $order                 WC_Order.
	 * @param object $subscription          WC_Subscription.
	 * @param int    $add_line_item_data    New sub_item_id.
	 * @param int    $remove_line_item_data Old sub_item_id.
	 *
	 * @throws \Exception
	 */
	public function update_order( $order, $subscription, $add_line_item_data, $remove_line_item_data ) {
		// Create the new API Resource.
		WC_AM_ORDER()->update_order( $order->id );
		// Delete the old API Resource.
		WC_AM_ORDER()->delete_sub_order_item( (int) $remove_line_item_data );
		// Delete the old API Resource API Key Activations.
		WC_AM_API_ACTIVATION_DATA_STORE()->delete_api_key_activation_by_sub_item_id( $remove_line_item_data );
	}

	/**
	 * Returns true if an order contains a subscription.
	 *
	 * @since 2.0
	 *
	 * @param $order WC_Order object or the ID of the order which the subscription was purchased in.
	 *
	 * @return bool
	 */
	public function order_contains_subscription( $order ) {
		return wcs_order_contains_subscription( $order );
	}

	/**
	 * Update Parent order subscription with new details from the switched order. Trim excess activations if any.
	 *
	 * @since 2.0
	 *
	 * @param object $subscription                  $subscription->id is new switched subscription ID.
	 * @param int    $new_order_item                New subscription item_id.
	 * @param int    $switched_subscription_item_id Old subscription item_id.
	 *
	 * @throws \Exception
	 */
	public function update_after_subscription_switch( $subscription, $new_order_item, $switched_subscription_item_id ) {
		//$subscription = $this->get_subscription_object( $subscription );

		/**
		 * Delete the old API Resource that is being switched from.
		 */
		if ( ! empty( $switched_subscription_item_id ) && ! empty( $new_order_item ) ) {
			if ( (int) $switched_subscription_item_id != (int) $new_order_item ) {
				WC_AM_ORDER()->delete_sub_order_item( $switched_subscription_item_id );
			}
		}

		//$order_obj = WC_AM_ORDER_DATA_STORE()->get_order_object( $order );

		/**
		 * Create the new API Resource that is being switched to.
		 */ //if ( is_object( $order_obj ) ) {
		//	WC_AM_ORDER()->update_wc_subscription_order( $order->get_id() );
		//}
	}

	/**
	 * Remove orphan items from order.
	 *
	 * @since 2.0
	 *
	 * @param object $order
	 *
	 * @throws \Exception
	 */
	public function subscriptions_switch_completed( $order ) {
		$order_id              = $order->get_id();
		$sub_previous_order_id = $this->get_previous_order_id( $order_id );
		$sub_previous_order_id = $sub_previous_order_id ? $sub_previous_order_id : $order_id;
		$sub_ids               = $this->get_all_subscription_ids_for_order( $sub_previous_order_id );

		if ( ! empty( $sub_ids ) ) {
			foreach ( $sub_ids as $k => $subscription_id ) {
				$subscription = WC_AM_ORDER_DATA_STORE()->get_order_object( $subscription_id );

				if ( is_object( $subscription ) && WC_AM_FORMAT()->count( $subscription->get_items() ) > 0 ) {
					foreach ( $subscription->get_items() as $item_id => $item ) {
						$is_item_on_sub = $this->is_subscription_line_item_on_subscription( $item_id, $order_id );

						if ( ! $is_item_on_sub ) {
							WC_AM_ORDER()->delete_sub_order_item( $item_id );
						}
					}
				}
			}
		}
	}

	/**
	 * Triggered for subscription renewals, including manual, where the order status completed hook may be bypassed.
	 *
	 * @since 2.0
	 *
	 * @param int $order_id
	 *
	 * @throws \Exception
	 */
	public function update_activated_resource_for_order( $order_id ) {
		if ( is_object( $order_id ) ) {
			WC_AM_ORDER()->update_wc_subscription_order( $order_id->get_id() );
		} else {
			WC_AM_ORDER()->update_wc_subscription_order( $order_id );
		}
	}

	/**
	 * Update API resource when renewal payment is complete and order created to record renewal.
	 *
	 * @since 2.0
	 *
	 * @param object $subscription
	 * @param object $renewal_order
	 */
	public function renewal_payment_complete( $renewal_order, $subscription ) {
		global $wpdb;

		$order                 = WC_AM_ORDER_DATA_STORE()->get_order_object( $renewal_order );
		$order_id              = $order->get_id();
		$sub_id                = $subscription->get_id();
		$sub_previous_order_id = $this->get_previous_order_id( $order_id );
		$sub_previous_order_id = $sub_previous_order_id ? $sub_previous_order_id : $order_id;
		$sub_parent_id         = $this->get_parent_id( $order_id );
		$items                 = $order->get_items();

		if ( is_object( $order ) && WC_AM_FORMAT()->count( $items ) > 0 ) {
			foreach ( $items as $item_id => $item ) {
				$parent_product_id = WC_AM_PRODUCT_DATA_STORE()->get_parent_product_id( $item );
				$is_api            = WC_AM_PRODUCT_DATA_STORE()->is_api_product( $parent_product_id );

				// Only store API resource data for API products that have an order status of completed.
				if ( $is_api ) {
					$variation_id = ! empty( $item->get_variation_id() ) && WC_AM_PRODUCT_DATA_STORE()->has_valid_product_status( $item->get_variation_id() ) ? $item->get_variation_id() : 0;
					$product_id   = ! empty( $variation_id ) ? $variation_id : $item->get_product_id();

					$data = array(
						'order_id'      => (int) $order_id,
						'order_item_id' => (int) $item_id
					);

					$where = array(
						'order_id'   => ! empty( $sub_previous_order_id ) ? $sub_previous_order_id : $sub_parent_id,
						'product_id' => $product_id,
						'sub_id'     => $sub_id
					);

					$data_format = array(
						'%d',
						'%d'
					);

					$where_format = array(
						'%d',
						'%d',
						'%d'
					);

					/**
					 * Update an existing API resource for this order item if the order status changed from Completed to something
					 * other than Completed, the item was updated, then the order status was changed back to Completed status.
					 *
					 * The order cannot be edited once it has a Completed status, so API resource updates only happen when
					 * the order status is changed back to Completed.
					 */
					$wpdb->update( $wpdb->prefix . $this->api_resource_table, $data, $where, $data_format, $where_format );
				}
			}

			//if ( is_object( $renewal_order ) ) {
			//	WC_AM_ORDER()->update_wc_subscription_order( $renewal_order->get_id() );
			//} else {
			//	$order_obj = WC_AM_ORDER_DATA_STORE()->get_order_object( $renewal_order );
			//
			//	WC_AM_ORDER()->update_wc_subscription_order( $order_obj );
			//}
		}
	}

	/**
	 * Refreshes/deletes API and database cache when subscription status changes.
	 *
	 * @since 2.2.8
	 *
	 * @param int $sub_id
	 *
	 * @throws \Exception
	 */
	public function refresh_cache( $sub_id ) {
		if ( ! empty( $sub_id ) ) {
			$order_id = WC_AM_API_RESOURCE_DATA_STORE()->get_order_id_by_sub_id( $sub_id );

			WC_AM_SMART_CACHE()->refresh_cache_by_order_id( $order_id );
		}
	}
}