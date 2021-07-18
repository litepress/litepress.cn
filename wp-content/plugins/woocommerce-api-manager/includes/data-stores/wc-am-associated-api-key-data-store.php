<?php

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * WooCommerce API Manager Associated API Key Data Store Class
 *
 * @since       2.0
 *
 * @author      Todd Lahman LLC
 * @copyright   Copyright (c) Todd Lahman LLC
 * @package     WooCommerce API Manager/Associated API Key Data Store
 */
class WC_AM_Associated_API_Key_Data_Store {

	/**
	 * @var null
	 */
	private static $_instance = null;

	/**
	 * @static
	 * @return null|\WC_AM_Associated_API_Key_Data_Store
	 */
	public static function instance() {

		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	private function __construct() { }

	/**
	 * Returns true if the resource already has a matching order ID.
	 *
	 * @since 2.0
	 *
	 * @param string $api_key
	 *
	 * @return bool
	 */
	public function has_associated_api_key( $api_key ) {
		global $wpdb;

		$sql = "
			SELECT associated_api_key
			FROM {$wpdb->prefix}" . WC_AM_USER()->get_associated_api_key_table_name() . "
			WHERE associated_api_key = %s
		";

		$api_key_exists = $wpdb->get_var( $wpdb->prepare( $sql, $api_key ) );

		return ! empty( $api_key_exists ) ? true : false;
	}

	/**
	 * Returns the associated_api_key_id for the Associated API Key.
	 *
	 * @since 2.0
	 *
	 * @param string $api_key
	 *
	 * @return bool|int
	 */
	public function get_associated_api_key_id( $api_key ) {
		global $wpdb;

		$sql = "
			SELECT associated_api_key_id
			FROM {$wpdb->prefix}" . WC_AM_USER()->get_associated_api_key_table_name() . "
			WHERE associated_api_key = %s
		";

		$associated_api_key_id = $wpdb->get_var( $wpdb->prepare( $sql, $api_key ) );

		return ! empty( $associated_api_key_id ) ? (int) $associated_api_key_id : false;
	}

	/**
	 * Get row of resources for associated API Key.
	 *
	 * @since 2.0
	 *
	 * @param string $api_key
	 *
	 * @return array|bool|null|object
	 */
	public function get_associated_api_key_resources_by_api_key( $api_key ) {
		global $wpdb;

		$sql = "
			SELECT *
			FROM {$wpdb->prefix}" . WC_AM_USER()->get_associated_api_key_table_name() . "
			WHERE associated_api_key = %s
		";

		$result = $wpdb->get_row( $wpdb->prepare( $sql, $api_key ) );

		return ! empty( $result ) ? $result : false;
	}

	/**
	 * Get array of activation IDs.
	 *
	 * @since 2.0
	 *
	 * @param string $api_key
	 *
	 * @return array|bool
	 */
	public function get_associated_api_key_activation_ids_by_api_key( $api_key ) {
		global $wpdb;

		$activation_id_list = array();

		$sql = "
            SELECT activation_ids
            FROM {$wpdb->prefix}" . WC_AM_USER()->get_associated_api_key_table_name() . "
            WHERE associated_api_key = %s
        ";

		$activation_ids = $wpdb->get_results( $wpdb->prepare( $sql, $api_key ), ARRAY_A );

		if ( ! empty( $activation_ids ) ) {
			foreach ( $activation_ids as $k => $activation_id ) {
				if ( ! empty( $activation_id[ 'activation_ids' ] ) ) {
					$activation_id_list = array_merge( $activation_id_list, json_decode( $activation_id[ 'activation_ids' ], true ) );
				}
			}
		}

		return ! empty( $activation_id_list ) ? $activation_id_list : false;
	}

	/**
	 * Use the Associated API Key to return the API Resource ID.
	 *
	 * @since 2.0
	 *
	 * @param string $associated_api_key
	 *
	 * @return bool|string|null
	 */
	public function get_api_resource_id_by_associated_api_key( $associated_api_key ) {
		global $wpdb;

		$api_resource_id = $wpdb->get_var( $wpdb->prepare( "
			SELECT api_resource_id
			FROM {$wpdb->prefix}" . WC_AM_USER()->get_associated_api_key_table_name() . "
			WHERE associated_api_key = %s
			LIMIT 1
		", $associated_api_key ) );

		return ! empty( $api_resource_id ) ? $api_resource_id : false;
	}

	/**
	 * Use the Associated API Key to return the Associated API Key ID.
	 *
	 * @since 2.0
	 *
	 * @param string $associated_api_key
	 *
	 * @return bool
	 */
	public function get_associated_api_key_id_by_associated_api_key( $associated_api_key ) {
		global $wpdb;

		$associated_api_key_id = $wpdb->get_var( $wpdb->prepare( "
			SELECT associated_api_key_id
			FROM {$wpdb->prefix}" . WC_AM_USER()->get_associated_api_key_table_name() . "
			WHERE associated_api_key = %s
			LIMIT 1
		", $associated_api_key ) );

		return ! empty( $associated_api_key_id ) ? $associated_api_key_id : false;
	}

	/**
	 * Get array of activation IDs.
	 *
	 * @since 2.0
	 *
	 * @param int $associated_api_key_id
	 *
	 * @return array|bool
	 */
	public function get_activation_ids_by_associated_api_key_id( $associated_api_key_id ) {
		global $wpdb;

		$activation_id_list = array();

		$sql = "
            SELECT activation_ids
            FROM {$wpdb->prefix}" . WC_AM_USER()->get_associated_api_key_table_name() . "
            WHERE associated_api_key_id = %d
        ";

		$activation_ids = $wpdb->get_results( $wpdb->prepare( $sql, $associated_api_key_id ), ARRAY_A );

		if ( ! empty( $activation_ids ) ) {
			foreach ( $activation_ids as $k => $activation_id ) {
				if ( ! empty( $activation_id[ 'activation_ids' ] ) ) {
					$activation_id_list = array_merge( $activation_id_list, json_decode( $activation_id[ 'activation_ids' ], true ) );
				}
			}
		}

		return ! empty( $activation_id_list ) ? $activation_id_list : false;
	}

	/**
	 * Return total number of Associated API Keys.
	 *
	 * @since 2.1
	 *
	 * @return int|string|null
	 */
	public function get_associated_api_key_count() {
		global $wpdb;

		$associated_api_key_count = $wpdb->get_var( "
			SELECT COUNT(associated_api_key_id)
			FROM {$wpdb->prefix}" . WC_AM_USER()->get_associated_api_key_table_name() . "
		" );

		return ! empty( $associated_api_key_count ) ? $associated_api_key_count : 0;
	}

	/**
	 * Add a unique API Key that is associated with an API resource.
	 *
	 * @since 2.0
	 *
	 * @param string $api_key
	 * @param int    $order_id
	 * @param int    $product_id
	 *
	 * @return bool|int
	 */
	public function add_associated_api_key( $api_key, $order_id, $product_id ) {
		global $wpdb;

		$result = false;

		if ( ! $this->has_associated_api_key( $api_key ) ) {
			$api_resource_id = WC_AM_API_RESOURCE_DATA_STORE()->get_api_resource_id_by_order_id_and_product_id( $order_id, $product_id );

			if ( ! empty( $api_key ) && ! empty( $api_resource_id ) ) {
				$data = array(
					'associated_api_key' => (string) $api_key,
					'api_resource_id'    => (int) $api_resource_id,
					'product_id'         => (int) $product_id
				);

				$format = array(
					'%s',
					'%d',
					'%d'
				);

				$result = $wpdb->insert( $wpdb->prefix . WC_AM_USER()->get_associated_api_key_table_name(), $data, $format );

				if ( $result ) {
					$associated_api_key_ids = WC_AM_API_RESOURCE_DATA_STORE()->get_associated_api_key_ids_by_api_resource_id( $api_resource_id );

					$data = array(
						'associated_api_key_ids' => WC_AM_FORMAT()->json_encode( ! empty( $associated_api_key_ids ) ? array_merge( $associated_api_key_ids, array( $wpdb->insert_id ) ) : array( $wpdb->insert_id ) )
					);

					$where = array(
						'api_resource_id' => $api_resource_id
					);

					$data_format = array(
						'%s'
					);

					$where_format = array(
						'%d'
					);

					$wpdb->update( $wpdb->prefix . WC_AM_USER()->get_api_resource_table_name(), $data, $where, $data_format, $where_format );

					return true;
				}
			}
		}

		return ! empty( $result ) ? true : false;
	}

	/**
	 * Add the activation_id reference number.
	 * Used for data migration from version 1.5.4 to 2.0.
	 *
	 * @since 2.0
	 *
	 * @param string $api_key
	 * @param int    $activation_id
	 *
	 * @return bool
	 */
	public function update_associated_api_key_activation_ids_list( $api_key, $activation_id ) {
		global $wpdb;

		$activation_ids_list = $this->get_associated_api_key_activation_ids_by_api_key( $api_key );

		$data = array(
			'activation_ids' => WC_AM_FORMAT()->json_encode( ! empty( $activation_ids_list ) ? array_merge( $activation_ids_list, array( $activation_id ) ) : array( $activation_id ) )
		);

		$where = array(
			'associated_api_key' => $api_key
		);

		$data_format = array(
			'%s'
		);

		$where_format = array(
			'%s'
		);

		$result = $wpdb->update( $wpdb->prefix . WC_AM_USER()->get_associated_api_key_table_name(), $data, $where, $data_format, $where_format );

		return ! empty( $result ) ? true : false;
	}

	/**
	 * Add Activation IDs for Associated API Key.
	 *
	 * @since 2.0
	 *
	 * @param int $associated_api_key_id
	 * @param int $activation_id
	 *
	 * @return bool
	 */
	public function update_associated_api_key_activation_ids( $associated_api_key_id, $activation_id ) {
		global $wpdb;

		$associated_api_key_activation_ids = $this->get_activation_ids_by_associated_api_key_id( $associated_api_key_id );

		$data = array(
			'activation_ids' => WC_AM_FORMAT()->json_encode( ! empty( $associated_api_key_activation_ids ) ? array_merge( $associated_api_key_activation_ids, array( $activation_id ) ) : array( $activation_id ) )
		);

		$where = array(
			'associated_api_key_id' => $associated_api_key_id
		);

		$data_format = array(
			'%s'
		);

		$where_format = array(
			'%d'
		);

		$update = $wpdb->update( $wpdb->prefix . WC_AM_USER()->get_associated_api_key_table_name(), $data, $where, $data_format, $where_format );

		return ! empty( $update ) ? true : false;
	}

	/**
	 * Delete Activation IDs for Associated API Key.
	 *
	 * @since 2.0
	 *
	 * @param int $associated_api_key_id
	 * @param int $activation_id
	 *
	 * @return bool
	 */
	public function delete_associated_api_key_activation_ids( $associated_api_key_id, $activation_id ) {
		global $wpdb;

		$activation_ids = $this->get_activation_ids_by_associated_api_key_id( $associated_api_key_id );

		if ( ! empty( $activation_ids ) ) {
			foreach ( $activation_ids as $key => $value ) {
				if ( (int) $value == (int) $activation_id ) {
					unset( $activation_ids[ $key ] );
				}
			}

			// Reindex the array keys.
			$activation_ids = array_values( $activation_ids );
		}

		$data = array(
			'activation_ids' => ! empty( $activation_ids ) ? WC_AM_FORMAT()->json_encode( $activation_ids ) : ''
		);

		$where = array(
			'associated_api_key_id' => $associated_api_key_id
		);

		$data_format = array(
			'%s'
		);

		$where_format = array(
			'%d'
		);

		$update = $wpdb->update( $wpdb->prefix . WC_AM_USER()->get_associated_api_key_table_name(), $data, $where, $data_format, $where_format );

		return ! empty( $update ) ? true : false;
	}
}