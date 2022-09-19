<?php

namespace LitePress\LM\Inc\Model;

use WC_Product_Vendors_Utils;
use wpdb;

final class Licence {

	private $vendor_id = 0;

	private wpdb $wpdb;

	public function __construct() {
		global $wpdb;

		$this->wpdb = $wpdb;

		$this->vendor_id = WC_Product_Vendors_Utils::get_logged_in_vendor() ?: 0;
	}

	public static function disable_api( $order_id, $comment = '' ) {
		global $wpdb;

		$wpdb->insert(
			$wpdb->prefix . 'wc_am_api_disabled',
			array(
				'order_id' => $order_id,
				'comment' => $comment,
			)
		);

		return true;
	}

	public static function enable_api( $order_id ) {
		global $wpdb;

		$wpdb->delete(
			$wpdb->prefix . 'wc_am_api_disabled',
			array(
				'order_id' => $order_id,
			)
		);
	}

	public static function is_disabled( $key ) {
		global $wpdb;
		$sql = $wpdb->prepare(
			"SELECT comment FROM {$wpdb->prefix}wc_am_api_disabled WHERE order_id=%d;",
			self::get_order_id_by_key( $key ),
		);

		$r = $wpdb->get_row( $sql, ARRAY_A );

		return empty( $r ) ? false : $r['comment'];
	}

	private static function get_order_id_by_key( $key ) {
		global $wpdb;
		$sql = $wpdb->prepare(
			"SELECT order_id FROM {$wpdb->prefix}wc_am_api_resource WHERE master_api_key=%s OR product_order_api_key=%s;",
			$key,
			$key
		);

		return $wpdb->get_row( $sql )->order_id ?: 0;
	}

	public function get( $paged = 1 ) {

		$limit = ( $paged - 1 ) * 20;

		$sql = 'SELECT * FROM ' . $this->wpdb->prefix . 'wc_am_api_activation AS api_activation LEFT JOIN ' . $this->wpdb->prefix . 'wcpv_commissions AS commission ON commission.order_id=api_activation.order_id ';
		$sql .= ' WHERE 1=1';
		$sql .= " AND commission.vendor_id = {$this->vendor_id}";

		if ( isset( $_GET["filter_by"] ) && isset( $_GET['filter_value'] ) ) {
			$sql .= sprintf( ' AND %s="%s"', $_GET["filter_by"], $_GET['filter_value'] );
		}

		$sql .= " ORDER BY api_activation.activation_id DESC";

		$sql .= " LIMIT {$limit},20";

		return $this->wpdb->get_results( $sql );
	}

	public function get_count() {
		$sql = 'SELECT COUNT(activation_id) FROM ' . $this->wpdb->prefix . 'wc_am_api_activation AS api_activation LEFT JOIN ' . $this->wpdb->prefix . 'wcpv_commissions AS commission ON commission.order_id=api_activation.order_id ';
		$sql .= ' WHERE 1=1';
		$sql .= " AND commission.vendor_id = {$this->vendor_id}";

		if ( isset( $_GET["filter_by"] ) && isset( $_GET['filter_value'] ) ) {
			$sql .= sprintf( ' AND %s="%s"', $_GET["filter_by"], $_GET['filter_value'] );
		}

		$count = 0;
		foreach ( $this->wpdb->get_row( $sql ) as $v ) {
			$count = $v;
		}

		return $count;
	}

	public static function get_vendor_id_by_order_id( $order_id ) {
		global $wpdb;

		$sql = $wpdb->prepare( 'SELECT vendor_id FROM wp_3_wcpv_commissions WHERE order_id=%d', $order_id );

		return $wpdb->get_row( $sql )->vendor_id;
	}

}