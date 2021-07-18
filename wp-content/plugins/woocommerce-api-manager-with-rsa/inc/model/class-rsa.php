<?php

namespace LitePress\WAMWR\Inc\Model;

use wpdb;

final class Rsa {

	private string $table;

	private wpdb $wpdb;

	public function __construct() {
		global $wpdb;

		$this->wpdb = $wpdb;

		$this->table = $this->wpdb->prefix . 'wc_am_rsa';
	}

	public function insert( int $user_id, string $public_key, string $private_key ): bool {
		$data = array(
			'user_id'     => $user_id,
			'public_key'  => $public_key,
			'private_key' => $private_key,
		);

		$format = array(
			'%d',
			'%s',
			'%s',
		);

		return false !== $this->wpdb->insert( $this->table, $data, $format );
	}

	public function delete( $id ): bool {
		$where = array(
			'ID' => $id,
		);

		$format = array(
			'%d',
		);

		return false !== $this->wpdb->delete( $this->table, $where, $format );
	}

	public function get_all_public_key( $user_id ): array {
		$sql     = $this->wpdb->prepare( "SELECT ID, public_key FROM {$this->table} WHERE user_id=%d;", $user_id );
		$results = $this->wpdb->get_results( $sql );

		$args = array();
		foreach ( $results as $result ) {
			array_push( $args, array(
				'id'         => $result->ID,
				'public_key' => $result->public_key,
			) );
		}

		return $args;
	}

	public function get_private_key( $id ) {
		$sql = $this->wpdb->prepare( "SELECT private_key FROM {$this->table} WHERE ID=%d;", $id );
		$row = $this->wpdb->get_row( $sql );
		if ( ! empty( $row ) ) {
			return $row->private_key;
		}

		return null;
	}

}
