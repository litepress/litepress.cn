<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Commission Class.
 *
 * Model for commissions to handle CRUD process.
 *
 * @category Commission
 * @package  WooCommerce Product Vendors/Commission
 * @version  2.0.0
 * @since 2.0.0
 */
class WC_Product_Vendors_Commission {
	public $table_name;
	protected $masspay;

	/**
	 * Constructor
	 *
	 * @access public
	 * @since 2.0.0
	 * @version 2.0.0
	 * @return bool
	 */
	public function __construct( WC_Product_Vendors_Vendor_Payout_Interface $masspay ) {
		global $wpdb;

		$this->table_name = WC_PRODUCT_VENDORS_COMMISSION_TABLE;

		$this->masspay = $masspay;

		return true;
	}

	/**
	 * Get the vendor total earned commission on a specified order.
	 *
	 * @since 2.1.0
	 * @version 2.1.3
	 * @param string $vendor_id
	 * @param string $order_id
	 *
	 * @return array | Boolean  array values or false if none found.
	 */
	public function get_vendor_earned_commission_by_order_id( $vendor_id, $order_id ) {
		global $wpdb;

		if ( empty( $vendor_id ) || empty( $order_id ) ) {
			return false;
		}

		$commission = $wpdb->get_var( $wpdb->prepare( "SELECT SUM( `total_commission_amount` ) FROM {$this->table_name} WHERE `order_id` = %d AND `vendor_id` = %d", absint( $order_id ), absint( $vendor_id ) ) );

		if ( ! empty( $commission ) ) {
			return $commission;
		}

		return false;
	}

	/**
	 * Gets all commission that is tied to a specific order id.
	 *
	 * @since 2.0.35
	 * @version 2.0.35
	 * @param int $order_id
	 * @param string $commission_status
	 * @return array $commissions
	 */
	public function get_commission_by_order_id( $order_id = null, $commission_status = 'all' ) {
		global $wpdb;

		if ( null === $order_id ) {
			return false;
		}

		if ( 'all' !== $commission_status ) {
			$commission_status = array( strtolower( wc_clean( $commission_status ) ) );
		} else {
			$commission_status = array( 'paid', 'unpaid', 'void' );
		}

		$commission_status = implode( ',', $commission_status );

		$commissions = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$this->table_name} WHERE `order_id` = %d AND `commission_status` IN (%s)", absint( $order_id ), $commission_status ) );

		if ( ! empty( $commissions ) ) {
			return $commissions;
		}

		return false;
	}

	/**
	 * Inserts a new commission record
	 *
	 * @access public
	 * @since 2.0.0
	 * @version 2.0.0
	 * @return bool
	 */
	public function insert( $order_id = '', $order_item_id = '', $order_date = NULL, $vendor_id = '', $vendor_name = '', $product_id = '', $variation_id = '', $product_name = '', $variation_attributes = '', $product_amount = '', $product_qty = '1', $product_shipping_amount = NULL, $product_shipping_tax_amount = NULL, $product_tax_amount = NULL, $product_commission_amount = '0', $total_commission_amount = '0', $commission_status = 'unpaid', $paid_date = NULL ) {

		global $wpdb;

		$sql = "INSERT INTO {$this->table_name} ( `order_id`, `order_item_id`, `order_date`, `vendor_id`, `vendor_name`, `product_id`, `variation_id`, `product_name`, `variation_attributes`, `product_amount`, `product_quantity`, `product_shipping_amount`, `product_shipping_tax_amount`, `product_tax_amount`, `product_commission_amount`, `total_commission_amount`, `commission_status`, `paid_date` )";

		$sql .= " VALUES ( %d, %d, %s, %d, %s, %d, %d, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s )";

		$wpdb->query( $wpdb->prepare( $sql, $order_id, $order_item_id, $order_date, $vendor_id, $vendor_name, $product_id, $variation_id, $product_name, $variation_attributes, $product_amount, $product_qty, $product_shipping_amount, $product_shipping_tax_amount, $product_tax_amount, $product_commission_amount, $total_commission_amount, $commission_status, $paid_date ) );

		$last_id = $wpdb->insert_id;

		do_action( 'wcpv_commissions_inserted' );

		return $last_id;
	}

	/**
	 * Pays the vendor their commission using passed in mass payments
	 *
	 * @since 2.0.0
	 * @version 2.0.0
	 * @param int $commission_ids list of commission ids
	 * @return bool
	 */
	public function pay( $commission_ids = array() ) {
		if ( empty( $commission_ids ) ) {
			return;
		}

		$commission_data = $this->get_commission_data( $commission_ids );

		if ( empty( $commission_data ) ) {
			throw new Exception( __( 'No valid commission to pay.', 'woocommerce-product-vendors' ) );
		}

		// we want to combine each vendors total commission so that store owners
		// will not be charged per transaction for all items for each vendor
		if ( apply_filters( 'wcpv_combine_total_commission_payout_per_vendor', true ) ) {
			$commission_data = $this->combine_total_commission_per_vendor_and_order( $commission_data );
		}

		$this->masspay->do_payment( $commission_data );

		return true;
	}

	/**
	 * Gets the list of commission data
	 *
	 * @access public
	 * @since 2.0.0
	 * @version 2.0.35
	 * @param int $commission_ids
	 * @return object $commission
	 */
	public function get_commission_data( $commission_ids = array() ) {
		global $wpdb;

		if ( empty( $commission_ids ) ) {
			return;
		}

		// get only the keys ( commission id )
		$commission_ids = array_keys( $commission_ids );

		// sanitize it
		$commission_ids = array_map( 'absint', $commission_ids );

		// prepare it
		$commission_ids = "'" . implode( "','", $commission_ids ) . "'";

		$commissions = $wpdb->get_results( "SELECT DISTINCT `id`, `order_id`, `order_item_id`, `vendor_id`, `total_commission_amount` FROM {$this->table_name} WHERE `id` IN ( $commission_ids ) AND `commission_status` = 'unpaid'" );

		return $commissions;
	}

	/**
	 * Combines the total commission per vendor and order id.
	 *
	 * @access public
	 * @since 2.0.6
	 * @since 2.1.8
	 * @param array $commission_data
	 * @return array $combined_commission
	 */
	public function combine_total_commission_per_vendor_and_order( $commission_data = array() ) {
		if ( empty( $commission_data ) ) {
			return null;
		}

		$combined_commission = array();

		foreach( $commission_data as $commission ) {
			$vendor_id = $commission->vendor_id;
			$order_id  = $commission->order_id;
			if ( ! isset( $combined_commission[ $order_id ][ $vendor_id ] ) ) {
				if ( ! isset( $combined_commission[ $order_id ] ) ) {
					$combined_commission[ $order_id ] = array();
				}
				$combined_commission[ $order_id ][ $vendor_id ] = $commission;
			} else {
				// add to the total commission
				$combined_commission[ $order_id ][ $vendor_id ]->total_commission_amount += (float) $commission->total_commission_amount;
			}
		}

		// Flatten to a 1D array.
		$flat = call_user_func_array( 'array_merge', $combined_commission );

		return $flat;
	}

	/**
	 * Gets the list of unpaid commission ids
	 *
	 * @access public
	 * @since 2.0.0
	 * @version 2.0.0
	 * @return array $filtered_commission_ids
	 */
	public function get_unpaid_commission_ids() {
		global $wpdb;

		$commissions = $wpdb->get_results( "SELECT DISTINCT `id`, `order_id` FROM {$this->table_name} WHERE `commission_status` = 'unpaid'" );

		$filtered_commission_ids = array();

		// filter out only commission that are unpaid and order status is processing or completed
		foreach( $commissions as $commission ) {
			$order = wc_get_order( $commission->order_id );

			if ( ! is_object( $order ) ) {
				continue;
			}

			$order_status = $order->get_status();

			if ( 'completed' === $order_status || 'processing' === $order_status ) {
				$filtered_commission_ids[ $commission->id ] = $commission->id;
			}
		}

		return $filtered_commission_ids;
	}

	/**
	 * Gets the list of unpaid commissions
	 *
	 * @access public
	 * @since 2.0.0
	 * @version 2.0.0
	 * @return array $commissions
	 */
	public function get_unpaid_commission_data() {
		global $wpdb;

		$commissions = $wpdb->get_results( "SELECT * FROM {$this->table_name} WHERE `commission_status` = 'unpaid'" );

		return $commissions;
	}

	/**
	 * Deletes a commission record
	 *
	 * @access public
	 * @since 2.0.0
	 * @version 2.0.0
	 * @param int $commission_id
	 * @return bool
	 */
	public function delete( $commission_id = 0 ) {
		global $wpdb;

		$order_id = $wpdb->get_var( $wpdb->prepare( "SELECT DISTINCT `order_id` FROM {$this->table_name} WHERE `id` = %d", absint( $commission_id ) ) );

		$wpdb->query( $wpdb->prepare( "DELETE FROM {$this->table_name} WHERE `id` = %d", absint( $commission_id ) ) );

		// also delete order post meta
		delete_post_meta( absint( $order_id ), '_wcpv_commission_added' );

		do_action( 'wcpv_commissions_deleted' );

		return true;
	}

	/**
	 * Deletes a commission record by order id.
	 *
	 * @access public
	 * @since 2.1.27
	 * @version 2.1.27
	 * @param int $order_id
	 * @return bool
	 */
	public function delete_by_order_id( $order_id = 0 ) {
		global $wpdb;

		$wpdb->query( $wpdb->prepare( "DELETE FROM {$this->table_name} WHERE `order_id` = %d", absint( $order_id ) ) );

		$order = wc_get_order( absint( $order_id ) );

		if ( $order ) {
			// also delete order post meta
			$order->delete_meta_data( '_wcpv_commission_added' );
			$order->save_meta_data();
		}

		do_action( 'wcpv_commissions_deleted' );

		return true;
	}

	/**
	 * Updates the paid status for a commission record
	 *
	 * @access public
	 * @since 2.0.0
	 * @version 2.0.0
	 * @param int $order_id
	 * @param int $order_item_id
	 * @param string $commission_status
	 * @return bool
	 */
	public function update_status( $commission_id = 0, $order_item_id, $commission_status ) {
		global $wpdb;

		$sql = "UPDATE {$this->table_name}";
		$sql .= " SET `commission_status` = %s,";

		if ( 'paid' === $commission_status ) {
			$sql .= " `paid_date` = %s";
			$date = date( 'Y-m-d H:i:s' );
		}

		if ( 'unpaid' === $commission_status ) {
			$sql .= " `paid_date` = %s";
			$date = '0000-00-00 00:00:00';
		}

		if ( 'void' === $commission_status ) {
			$sql .= " `paid_date` = %s";
			$date = '0000-00-00 00:00:00';
		}

		$sql .= " WHERE `id` = %d";
		$sql .= " AND `commission_status` != %s";

		// updates the commission table
		$wpdb->query( $wpdb->prepare( $sql, $commission_status, $date, (int) $commission_id, $commission_status ) );

		// also update the order item meta to leave a trail
		$sql = "UPDATE {$wpdb->prefix}woocommerce_order_itemmeta";
		$sql .= " SET `meta_value` = %s";
		$sql .= " WHERE `order_item_id` = %d";
		$sql .= " AND `meta_key` = %s";

		$status = $wpdb->get_var( $wpdb->prepare( $sql, $commission_status, $order_item_id, '_commission_status' ) );

		do_action( 'wcpv_commissions_updated', $commission_id, $order_item_id );

		return true;
	}

	/**
	 * Calculates the commission for a product based on an order
	 *
	 * @access public
	 * @since 2.0.0
	 * @version 2.0.0
	 * @param int $product_id
	 * @param int $vendor_id
	 * @param int $quantity
	 * @return float $commission
	 */
	public function calc_order_product_commission( $product_id, $vendor_id, $product_amount, $quantity = 1 ) {
		$vendor_data = WC_Product_Vendors_Utils::get_vendor_data_by_id( $vendor_id );

		$commission_array = WC_Product_Vendors_Utils::get_product_commission( $product_id, $vendor_data );

		$commission = $commission_array['commission'];
		$type       = $commission_array['type'];

		// bail if commission to set to 0.
		if ( '0' == $commission ) {
			return $commission;
		}

		if ( 'percentage' === $type ) {
			$commission = $product_amount * ( abs( $commission ) / 100 );

		} else { // fixed commission.
			$commission = ( abs( $commission ) * absint( $quantity ) );
		}

		return round( $commission, wc_get_rounding_precision() );
	}

	/**
	 * Queries the commission table based on filters
	 *
	 * @access public
	 * @since 2.0.0
	 * @version 2.0.0
	 * @param int $order_id
	 * @param string $year
	 * @param string $month
	 * @param string $commission_status
	 * @param int $vendor_id
	 * @return array $query
	 */
	public function csv_filtered_query( $order_id = '', $year = '', $month = '', $commission_status = '', $vendor_id = '' ) {
		global $wpdb;

		$query = array();

		$sql = "SELECT commission.order_id, commission.order_date, commission.vendor_name, commission.product_name, commission.variation_attributes, commission.product_amount, commission.product_quantity, commission.product_shipping_amount, commission.product_shipping_tax_amount, commission.product_tax_amount, commission.product_commission_amount, commission.total_commission_amount, commission.commission_status, commission.paid_date FROM " . WC_PRODUCT_VENDORS_COMMISSION_TABLE . " AS commission";

		$sql .= " WHERE 1=1";

		// if order_id is set (search), we don't need other filters
		// as search takes priority
		if ( $order_id ) {

			$sql .= " AND commission.order_id = {$order_id}";

		} else {

			if ( $month && $year ) {

				$time_filter = " AND MONTH( commission.order_date ) = {$month} AND YEAR( commission.order_date ) = {$year}";

				$sql .= $time_filter;
			}

			if ( $commission_status ) {
				$commission_status = esc_sql( $commission_status );

				$status_filter = " AND commission.commission_status = '{$commission_status}'";

				$sql .= $status_filter;
			}

			if ( $vendor_id ) {
				$vendor = absint( $vendor_id );

				$vendor_filter = " AND commission.vendor_id = '{$vendor}'";

				$sql .= $vendor_filter;
			}
		}

		$query = $wpdb->get_results( $sql, ARRAY_A );

		// we need to unserialize possible variation attributes
		$query = array_map( array( 'WC_Product_Vendors_Utils', 'unserialize_attributes' ), $query );

		// add column headers
		$headers = array(
			__( 'Order ID', 'woocommerce-product-vendors' ),
			__( 'Order Date', 'woocommerce-product-vendors' ),
			__( 'Vendor Name', 'woocommerce-product-vendors' ),
			__( 'Product Name', 'woocommerce-product-vendors' ),
			__( 'Variation Attributes', 'woocommerce-product-vendors' ),
			__( 'Product Amount', 'woocommerce-product-vendors' ),
			__( 'Product Quantity', 'woocommerce-product-vendors' ),
			__( 'Product Shipping Amount', 'woocommerce-product-vendors' ),
			__( 'Product Shipping Tax Amount', 'woocommerce-product-vendors' ),
			__( 'Product Tax Amount', 'woocommerce-product-vendors' ),
			__( 'Product Commission Amount', 'woocommerce-product-vendors' ),
			__( 'Total Commission Amount', 'woocommerce-product-vendors' ),
			__( 'Commission Paid Status', 'woocommerce-product-vendors' ),
			__( 'Commission Paid Date', 'woocommerce-product-vendors' ),
		);

		array_unshift( $query, $headers );

		// convert the array to string recursively
		$query = implode( PHP_EOL, array_map( array( 'WC_Product_Vendors_Utils', 'convert2string' ), $query ) );

		return $query;
	}
}
