<?php

namespace LitePress\WAMAL\Inc\Model;

use wpdb;

final class Api_Log {

	private string $table;

	private wpdb $wpdb;

	public function __construct() {
		global $wpdb;

		$this->wpdb = $wpdb;

		$this->table = $this->wpdb->prefix . 'wc_am_api_log';
	}

	public function insert( $request, $response ) {
		$data = array(
			'user_id'       => get_post_field( 'post_author', $request['product_id'] ),
			'client_ip'     => $_SERVER['REMOTE_ADDR'],
			'client_domain' => $request['object'],
			'product_id'    => $request['product_id'],
			'request'       => json_encode( $request, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT ),
			'response'      => json_encode( $response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT ),
			'created_at'    => date( 'Y/m/d H:i:s', time() ),
		);

		$format = array(
			'%d',
			'%s',
			'%s',
			'%d',
			'%s',
			'%s',
			'%s',
		);

		return false !== $this->wpdb->insert( $this->table, $data, $format );
	}

	public function get( $paged = 1 ) {
		$filter = isset( $_GET['filter_by'] ) && isset( $_GET['filter_value'] ) ? sprintf( 'AND %s="%s"', $_GET["filter_by"], $_GET['filter_value'] ) : '';
		$sql    = $this->wpdb->prepare(
			"SELECT * FROM {$this->table} WHERE user_id=%d " . $filter . " ORDER BY created_at DESC LIMIT %d,20;",
			get_current_user_id(),
			( $paged - 1 ) * 20
		);

		return $this->wpdb->get_results( $sql );
	}

	public function get_count() {
		$filter = isset( $_GET['filter_by'] ) && isset( $_GET['filter_value'] ) ? sprintf( 'AND %s="%s"', $_GET["filter_by"], $_GET['filter_value'] ) : '';
		$sql = $this->wpdb->prepare( "SELECT COUNT(*) FROM {$this->table} WHERE user_id=%d " . $filter . ";", get_current_user_id() );

		$count = 0;
		foreach ( $this->wpdb->get_row( $sql ) as $v ) {
			$count = $v;
		}

		return $count;
	}

}
