<?php

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * WooCommerce API Manager API Activation Data Store Class
 *
 * @since       2.0
 *
 * @author      Todd Lahman LLC
 * @copyright   Copyright (c) Todd Lahman LLC
 * @package     WooCommerce API Manager/API Activation Data Store
 */
class WC_AM_API_Activation_Data_Store {

	/**
	 * @var null
	 */
	private static $_instance = null;

	/**
	 * @static
	 * @return null|\WC_AM_API_Activation_Data_Store
	 */
	public static function instance() {

		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	private function __construct() {
		add_action( 'init', array( $this, 'delete_my_account_activation' ) );
	}

	/**
	 * Get the total number of activations for a product using a
	 * Master API Key or Product Order API Key.
	 *
	 * @since 2.0
	 *
	 * @param string     $api_key Master API Key or Product Order API Key
	 * @param string|int $product_id
	 *
	 * @return int|null|string
	 */
	public function get_total_activations_resources_for_api_key_by_product_id( $api_key, $product_id ) {
		global $wpdb;

		$sql = "
				SELECT *
				FROM {$wpdb->prefix}" . WC_AM_USER()->get_api_activation_table_name() . "
				WHERE ( master_api_key = %s OR product_order_api_key = %s )
				AND ( assigned_product_id = %d OR product_id = %s )
			";

		$activation_resources = $wpdb->get_results( $wpdb->prepare( $sql, $api_key, $api_key, $product_id, $product_id ) );

		return ! empty( $activation_resources ) ? $activation_resources : false;
	}

	/**
	 * Get all activations assigned to user_id grouped by product ID.
	 *
	 * @since 2.0
	 *
	 * @param int $user_id
	 *
	 * @return array|bool|null|object
	 */
	public function get_activation_resources_by_user_id( $user_id ) {
		global $wpdb;

		$sql = "
				SELECT *
				FROM {$wpdb->prefix}" . WC_AM_USER()->get_api_activation_table_name() . "
				WHERE user_id = %d
				ORDER BY product_id
			";

		$activation_resources = $wpdb->get_results( $wpdb->prepare( $sql, $user_id ) );

		return ! empty( $activation_resources ) ? $activation_resources : false;
	}

	/**
	 * Get all activations assigned to order_id grouped by product ID.
	 *
	 * @since 2.0
	 *
	 * @param int $order_id
	 *
	 * @return array|bool|null|object
	 */
	public function get_activation_resources_by_order_id( $order_id ) {
		global $wpdb;

		$sql = "
			SELECT *
			FROM {$wpdb->prefix}" . WC_AM_USER()->get_api_activation_table_name() . "
			WHERE order_id = %d
			ORDER BY assigned_product_id
		";

		$activation_resources = $wpdb->get_results( $wpdb->prepare( $sql, $order_id ) );

		return ! empty( $activation_resources ) ? $activation_resources : false;
	}

	/**
	 * Get all activations assigned to sub_parent_id grouped by product ID.
	 *
	 * @since 2.0
	 *
	 * @param int $sub_parent_id
	 *
	 * @return array|bool|null|object
	 */
	public function get_activation_resources_by_sub_parent_id( $sub_parent_id ) {
		global $wpdb;

		$sql = "
			SELECT *
			FROM {$wpdb->prefix}" . WC_AM_USER()->get_api_activation_table_name() . "
			WHERE sub_parent_id = %d
			ORDER BY assigned_product_id
		";

		$activation_resources = $wpdb->get_results( $wpdb->prepare( $sql, $sub_parent_id ) );

		return ! empty( $activation_resources ) ? $activation_resources : false;
	}

	/**
	 * Gets the total number of activations for this resource.
	 *
	 * @since 2.0
	 *
	 * @param array $resources
	 *
	 * @return int
	 */
	public function get_total_activations( $resources ) {
		$total_activations = (int) array_sum( wp_list_pluck( $resources, 'activations_total' ) );

		return $total_activations ? $total_activations : 0;
	}

	/**
	 * Returns the API Keys endpoint URL.
	 *
	 * @since 2.0
	 *
	 * @return mixed|void
	 */
	public function get_api_keys_url() {
		$api_keys_url = wc_get_endpoint_url( 'api-keys', '', wc_get_page_permalink( 'myaccount' ) );

		return apply_filters( 'wc_api_manager_get_api_keys_url', $api_keys_url );
	}

	/**
	 * Returns the api_resource_id for the Associated API Key using the activation_id.
	 *
	 * @since 2.0
	 *
	 * @param int $activation_id
	 *
	 * @return bool|int
	 */
	public function get_api_resource_id_by_activation_id( $activation_id ) {
		global $wpdb;

		$api_resource_id = $wpdb->get_var( $wpdb->prepare( "
			SELECT api_resource_id
			FROM {$wpdb->prefix}" . WC_AM_USER()->get_api_activation_table_name() . "
			WHERE activation_id = %d
		", $activation_id ) );

		return ! empty( $api_resource_id ) ? (int) $api_resource_id : false;
	}

	/**
	 * Returns the associated_api_key_id for the Associated API Key using the activation_id.
	 *
	 * @since 2.0
	 *
	 * @param int $activation_id
	 *
	 * @return bool|int
	 */
	public function get_associated_api_key_id_by_activation_id( $activation_id ) {
		global $wpdb;

		$associated_api_key_id = $wpdb->get_var( $wpdb->prepare( "
			SELECT associated_api_key_id
			FROM {$wpdb->prefix}" . WC_AM_USER()->get_api_activation_table_name() . "
			WHERE activation_id = %d
		", $activation_id ) );

		return ! empty( $associated_api_key_id ) ? (int) $associated_api_key_id : false;
	}

	/**
	 * Return the $available_resource where an activation can be added, otherwise
	 * return false if there are no resources available to add an activation.
	 *
	 * @since 2.0
	 *
	 * @param array $resources
	 *
	 * @return array|bool
	 */
	public function get_available_product_api_resource_for_activation( $resources ) {
		$total_activations_purchased = WC_AM_API_RESOURCE_DATA_STORE()->get_total_activations_purchased( $resources );
		$total_activations           = $this->get_total_activations( $resources );
		$available_resource          = array();

		if ( $total_activations < $total_activations_purchased ) {
			foreach ( $resources as $resource ) {
				// Boolean value
				$sub_id = ! empty( $resource->sub_id );

				if ( WCAM()->get_wc_subs_exist() && $sub_id ) {
					if ( WC_AM_SUBSCRIPTION()->is_subscription_for_order_active( $resource->sub_id ) && $resource->activations_total < $resource->activations_purchased_total ) {
						$available_resource[ 'api_resource_id' ]     = $resource->api_resource_id;
						$available_resource[ 'assigned_product_id' ] = $resource->product_id;
						$available_resource[ 'activations_total' ]   = $resource->activations_total;
						$available_resource[ 'order_id' ]            = $resource->order_id;
						$available_resource[ 'order_item_id' ]       = $resource->order_item_id;
						$available_resource[ 'sub_id' ]              = $resource->sub_id;
						$available_resource[ 'sub_item_id' ]         = $resource->sub_item_id;
						$available_resource[ 'sub_parent_id' ]       = $resource->sub_parent_id;
						$available_resource[ 'activation_ids' ]      = json_decode( $resource->activation_ids, true );

						return $available_resource;
					}
				} elseif ( ! $sub_id && $resource->activations_total < $resource->activations_purchased_total ) {
					$available_resource[ 'api_resource_id' ]     = $resource->api_resource_id;
					$available_resource[ 'assigned_product_id' ] = $resource->product_id;
					$available_resource[ 'activations_total' ]   = $resource->activations_total;
					$available_resource[ 'order_id' ]            = $resource->order_id;
					$available_resource[ 'order_item_id' ]       = $resource->order_item_id;
					$available_resource[ 'activation_ids' ]      = json_decode( $resource->activation_ids, true );

					return $available_resource;
				}
			}
		}

		return false;
	}

	/**
	 * Returns object of data row using the instance_id.
	 *
	 * @since 2.0
	 *
	 * @param int $instance_id
	 *
	 * @return bool|object|void|null
	 */
	public function get_row_data_by_instance_id( $instance_id ) {
		global $wpdb;

		$activation_resource = $wpdb->get_row( $wpdb->prepare( "
			SELECT *
			FROM {$wpdb->prefix}" . WC_AM_USER()->get_api_activation_table_name() . "
			WHERE instance = %s
		", $instance_id ) );

		return ! empty( $activation_resource ) ? $activation_resource : false;
	}

	/**
	 * Returns object of data row using the activation_id.
	 *
	 * @since 2.0
	 *
	 * @param int $activation_id
	 *
	 * @return bool|object|void|null
	 */
	public function get_activation_resource_by_activation_id( $activation_id ) {
		global $wpdb;

		$activation_resource = $wpdb->get_row( $wpdb->prepare( "
			SELECT *
			FROM {$wpdb->prefix}" . WC_AM_USER()->get_api_activation_table_name() . "
			WHERE activation_id = %s
		", $activation_id ) );

		return ! empty( $activation_resource ) ? $activation_resource : false;
	}

	/**
	 * Returns object of data row using the sub_item_id.
	 *
	 * @since 2.1.2
	 *
	 * @param int $sub_item_id
	 *
	 * @return bool|object|void|null
	 */
	public function get_activation_resource_by_sub_item_id( $sub_item_id ) {
		global $wpdb;

		$activation_resource = $wpdb->get_row( $wpdb->prepare( "
			SELECT *
			FROM {$wpdb->prefix}" . WC_AM_USER()->get_api_activation_table_name() . "
			WHERE sub_item_id = %s
		", $sub_item_id ) );

		return ! empty( $activation_resource ) ? $activation_resource : false;
	}

	/**
	 * Return total number of activations for an api_resource_id.
	 *
	 * @since 2.0
	 *
	 * @param $api_resource_id
	 *
	 * @return bool|string|null
	 */
	public function get_activation_count_by_activation_id( $api_resource_id ) {
		global $wpdb;

		$activations_count = $wpdb->get_var( $wpdb->prepare( "
			SELECT COUNT(activation_id)
			FROM {$wpdb->prefix}" . WC_AM_USER()->get_api_activation_table_name() . "
			WHERE api_resource_id = %d
		", $api_resource_id ) );

		return ! empty( $activations_count ) ? $activations_count : false;
	}

	/**
	 * Return total number of activations.
	 *
	 * @since 2.1
	 *
	 * @return int|string|null
	 */
	public function get_activation_count() {
		global $wpdb;

		$activations_count = $wpdb->get_var( "
			SELECT COUNT(activation_id)
			FROM {$wpdb->prefix}" . WC_AM_USER()->get_api_activation_table_name() . "
		" );

		return ! empty( $activations_count ) ? $activations_count : 0;
	}

	/**
	 * Get array of activation IDs using an order ID.
	 *
	 * @since 2.0
	 *
	 * @param int $order_id
	 *
	 * @return array|bool
	 */
	public function get_activations_by_order_id( $order_id ) {
		global $wpdb;

		$activation_id_list = array();

		$sql = "
            SELECT activation_ids
            FROM {$wpdb->prefix}" . WC_AM_USER()->get_api_resource_table_name() . "
            WHERE order_id = %d
        ";

		$activation_ids = $wpdb->get_results( $wpdb->prepare( $sql, $order_id ), ARRAY_A );

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
	 * Get array of activation IDs by sub_item_id.
	 *
	 * @since 2.0
	 *
	 * @param int $sub_item_id
	 *
	 * @return array|bool
	 */
	public function get_activations_by_sub_item_id( $sub_item_id ) {
		global $wpdb;

		$activation_id_list = array();

		$sql = "
            SELECT activation_ids
            FROM {$wpdb->prefix}" . WC_AM_USER()->get_api_resource_table_name() . "
            WHERE sub_item_id = %d
        ";

		$activation_ids = $wpdb->get_results( $wpdb->prepare( $sql, $sub_item_id ), ARRAY_A );

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
	 * Get array of activation IDs by sub_id.
	 *
	 * @since 2.0
	 *
	 * @param int $sub_id
	 *
	 * @return array|bool
	 */
	public function get_activations_by_subscription_order_id( $sub_id ) {
		global $wpdb;

		$activation_id_list = array();

		$sql = "
            SELECT activation_ids
            FROM {$wpdb->prefix}" . WC_AM_USER()->get_api_resource_table_name() . "
            WHERE sub_id = %d
        ";

		$activation_ids = $wpdb->get_results( $wpdb->prepare( $sql, $sub_id ), ARRAY_A );

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
	 * Get array of activation IDs by order_item_id.
	 *
	 * @since 2.0
	 *
	 * @param int $order_item_id
	 *
	 * @return array|bool
	 */
	public function get_activations_by_order_item_id( $order_item_id ) {
		global $wpdb;

		$activation_id_list = array();

		$sql = "
            SELECT activation_ids
            FROM {$wpdb->prefix}" . WC_AM_USER()->get_api_resource_table_name() . "
            WHERE order_item_id = %d
        ";

		$activation_ids = $wpdb->get_results( $wpdb->prepare( $sql, $order_item_id ), ARRAY_A );

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
	 * Get array of activation IDs.
	 *
	 * @since 2.0
	 *
	 * @param int $api_resource_id
	 *
	 * @return array|bool
	 */
	public function get_activation_ids_by_api_resource_id( $api_resource_id ) {
		global $wpdb;

		$activation_id_list = array();

		$sql = "
            SELECT activation_ids
            FROM {$wpdb->prefix}" . WC_AM_USER()->get_api_resource_table_name() . "
            WHERE api_resource_id = %d
        ";

		$activation_ids = $wpdb->get_results( $wpdb->prepare( $sql, $api_resource_id ), ARRAY_A );

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
	 * Returns the Instance ID.
	 *
	 * @since 2.2.8
	 *
	 * @param string $api_resource_id
	 *
	 * @return bool|string
	 */
	public function get_instance_id_by_api_resource_id( $api_resource_id ) {
		global $wpdb;

		$instance_id = $wpdb->get_var( $wpdb->prepare( "
			SELECT instance
			FROM {$wpdb->prefix}" . WC_AM_USER()->get_api_activation_table_name() . "
			WHERE api_resource_id = %d
		", $api_resource_id ) );

		return ! empty( $instance_id ) ? $instance_id : false;
	}

	/**
	 * Returns true if activations exist for an order.
	 *
	 * @since 2.1.5
	 *
	 * @param int $order_id
	 *
	 * @return bool
	 */
	public function has_activations_for_order_id( $order_id ) {
		global $wpdb;

		$sql = "
			SELECT activation_id
			FROM {$wpdb->prefix}" . WC_AM_USER()->get_api_activation_table_name() . "
			WHERE order_id = %d
			LIMIT 1
		";

		$has_activations = $wpdb->get_var( $wpdb->prepare( $sql, $order_id ) );

		return ! empty( $has_activations ) ? true : false;
	}

	/**
	 * Returns true if the instance ID is associatiated with an activation.
	 *
	 * @since 2.0
	 *
	 * @param string $instance
	 *
	 * @return bool
	 */
	public function is_instance_activated( $instance ) {
		global $wpdb;

		$sql = "
			SELECT instance
			FROM {$wpdb->prefix}" . WC_AM_USER()->get_api_activation_table_name() . "
			WHERE instance = %s
		";

		$activation = $wpdb->get_var( $wpdb->prepare( $sql, $instance ) );

		return ! empty( $activation ) ? true : false;
	}

	/**
	 * Add the activation data to the api_activation_table, and update the
	 * api_resource_table table as required.
	 *
	 * @since 2.0
	 *
	 * @param int   $user_id
	 * @param array $resources
	 * @param array $request_data
	 *
	 * @return bool
	 */
	public function add_api_key_activation( $user_id, $resources, $request_data ) {
		$available_resource = $this->get_available_product_api_resource_for_activation( $resources );

		if ( ! empty( $available_resource ) && ! empty( $resources ) && ! empty( $request_data ) ) {
			global $wpdb;

			$master_api_key = current( wp_list_pluck( $resources, 'master_api_key' ) );

			if ( empty( $master_api_key ) ) {
				return false;
			}

			$product_order_api_key = current( wp_list_pluck( $resources, 'product_order_api_key' ) );
			$associated_api_key_id = WC_AM_ASSOCIATED_API_KEY_DATA_STORE()->get_associated_api_key_id_by_associated_api_key( $request_data[ 'api_key' ] );
			$associated_api_key_id = ! empty( $associated_api_key_id ) && ( $request_data[ 'api_key' ] != $master_api_key || $request_data[ 'api_key' ] != $product_order_api_key ) ? (int) $associated_api_key_id : 0;

			$data = array(
				'activation_time'       => (int) WC_AM_ORDER_DATA_STORE()->get_current_time_stamp(),
				'api_key'               => ! empty( $request_data[ 'api_key' ] ) ? (string) $request_data[ 'api_key' ] : '',
				'api_resource_id'       => ! empty( $available_resource[ 'api_resource_id' ] ) ? (int) $available_resource[ 'api_resource_id' ] : 0,
				'assigned_product_id'   => ! empty( $available_resource[ 'assigned_product_id' ] ) ? (int) $available_resource[ 'assigned_product_id' ] : 0,
				'associated_api_key_id' => $associated_api_key_id,
				'ip_address'            => ! empty( $request_data[ 'user_ip' ] ) ? (string) $request_data[ 'user_ip' ] : '',
				'instance'              => ! empty( $request_data[ 'instance' ] ) ? (string) $request_data[ 'instance' ] : '',
				'master_api_key'        => (string) $master_api_key,
				'object'                => ! empty( $request_data[ 'object' ] ) ? (string) $request_data[ 'object' ] : '',
				'order_id'              => ! empty( $available_resource[ 'order_id' ] ) ? (int) $available_resource[ 'order_id' ] : 0,
				'order_item_id'         => ! empty( $available_resource[ 'order_item_id' ] ) ? (int) $available_resource[ 'order_item_id' ] : 0,
				'product_id'            => ! empty( $request_data[ 'product_id' ] ) ? (string) $request_data[ 'product_id' ] : '',
				'product_order_api_key' => ! empty( $product_order_api_key ) ? (string) $product_order_api_key : '',
				'sub_id'                => ! empty( $available_resource[ 'sub_id' ] ) ? (int) $available_resource[ 'sub_id' ] : 0,
				'sub_item_id'           => ! empty( $available_resource[ 'sub_item_id' ] ) ? (int) $available_resource[ 'sub_item_id' ] : 0,
				'sub_parent_id'         => ! empty( $available_resource[ 'sub_parent_id' ] ) ? (int) $available_resource[ 'sub_parent_id' ] : 0,
				'version'               => ! empty( $request_data[ 'version' ] ) ? (string) $request_data[ 'version' ] : '',
				'update_requests'       => ! empty( $request_data[ 'update_requests' ] ) ? (int) $request_data[ 'update_requests' ] : 0,
				'user_id'               => (int) $user_id
			);

			$format = array(
				'%d',
				'%s',
				'%d',
				'%d',
				'%d',
				'%s',
				'%s',
				'%s',
				'%s',
				'%d',
				'%d',
				'%s',
				'%s',
				'%d',
				'%d',
				'%d',
				'%s',
				'%d',
				'%d'
			);

			$result = $wpdb->insert( $wpdb->prefix . WC_AM_USER()->get_api_activation_table_name(), $data, $format ) ? true : false;

			if ( $result ) {
				$activation_id = $wpdb->insert_id;

				if ( ! empty( $associated_api_key_id ) ) {
					WC_AM_ASSOCIATED_API_KEY_DATA_STORE()->update_associated_api_key_activation_ids( $associated_api_key_id, $activation_id );
				}

				$activation_ids = ! empty( $available_resource[ 'activation_ids' ] ) ? array_merge( $available_resource[ 'activation_ids' ], array( $activation_id ) ) : array( $activation_id );

				$data = array(
					'activation_ids'    => WC_AM_FORMAT()->json_encode( $activation_ids ),
					'activations_total' => $available_resource[ 'activations_total' ] + 1
				);

				$where = array(
					'api_resource_id' => $available_resource[ 'api_resource_id' ]
				);

				$data_format = array(
					'%s',
					'%d'
				);

				$where_format = array(
					'%d'
				);

				$wpdb->update( $wpdb->prefix . WC_AM_USER()->get_api_resource_table_name(), $data, $where, $data_format, $where_format );

				return true;
			}
		}

		return false;
	}

	/**
	 * Update the version data for an activation.
	 *
	 * @since 2.0.10
	 *
	 * @param $instance
	 * @param $version
	 */
	public function update_version( $instance, $version ) {
		if ( ! empty( $instance ) && ! empty( $version ) ) {
			global $wpdb;

			$data = array(
				'version' => $version
			);

			$where = array(
				'instance' => $instance
			);

			$data_format = array(
				'%s'
			);

			$where_format = array(
				'%s'
			);

			$wpdb->update( $wpdb->prefix . WC_AM_USER()->get_api_activation_table_name(), $data, $where, $data_format, $where_format );
		}
	}

	/**
	 * Replace the Master API Key value.
	 *
	 * @since 2.0.12
	 *
	 * @param string $mak
	 * @param int    $user_id
	 */
	public function update_master_api_key( $mak, $user_id ) {
		if ( ! empty( $mak ) ) {
			global $wpdb;

			$data = array(
				'master_api_key' => $mak
			);

			$where = array(
				'user_id' => (int) $user_id
			);

			$data_format = array(
				'%s'
			);

			$where_format = array(
				'%d'
			);

			$wpdb->update( $wpdb->prefix . WC_AM_USER()->get_api_activation_table_name(), $data, $where, $data_format, $where_format );
		}
	}

	/**
	 * Delete the API Key activation assigned to the unique instance.
	 *
	 * @since 2.0
	 *
	 * @param array $instance_id
	 *
	 * @return bool
	 */
	public function delete_api_key_activation_by_instance_id( $instance_id ) {
		if ( ! empty( $instance_id ) ) {
			global $wpdb;

			$activation_ids = '';
			$resource       = $result = false;

			$activation_resource = $this->get_row_data_by_instance_id( $instance_id );

			if ( ! empty( $activation_resource->api_resource_id ) ) {
				$resource = WC_AM_API_RESOURCE_DATA_STORE()->get_resources_by_api_resource_id( $activation_resource->api_resource_id );
			}

			if ( ! empty( $resource ) ) {
				// Delete the activation_id from the Associated API Key row.
				if ( ! empty( $activation_resource->associated_api_key_id ) ) {
					WC_AM_ASSOCIATED_API_KEY_DATA_STORE()->delete_associated_api_key_activation_ids( $activation_resource->associated_api_key_id, $activation_resource->activation_id );
				}

				if ( ! empty( $resource->activation_ids ) ) {
					$api_resource_id_count = $wpdb->get_col( $wpdb->prepare( "
						SELECT COUNT(api_resource_id)
						FROM {$wpdb->prefix}" . WC_AM_USER()->get_api_activation_table_name() . "
						WHERE api_resource_id = %d
					", $activation_resource->api_resource_id ) );

					$activation_ids       = json_decode( $resource->activation_ids, true );
					$activation_ids_count = WC_AM_FORMAT()->count( $activation_ids );

					/**
					 * Delete orphaned activations.
					 * There should never be a different between the total activations, activation IDs, and activations.
					 * If there is a difference, reset all activations back to zero to fix the discrepancy, and start over.
					 */
					if ( $resource->activations_total != $activation_ids_count || $resource->activations_total != $api_resource_id_count[ 0 ] ) {
						// Delete activations associate with this API Resource ID row.
						$where = array(
							'api_resource_id' => $activation_resource->api_resource_id
						);

						$where_format = array(
							'%d'
						);

						$wpdb->delete( $wpdb->prefix . WC_AM_USER()->get_api_activation_table_name(), $where, $where_format );

						// Set activations to zero.
						$data = array(
							'activation_ids'    => '',
							'activations_total' => 0
						);

						$where = array(
							'api_resource_id' => $activation_resource->api_resource_id
						);

						$data_format = array(
							'%d'
						);

						$where_format = array(
							'%d'
						);

						$result = $wpdb->update( $wpdb->prefix . WC_AM_USER()->get_api_resource_table_name(), $data, $where, $data_format, $where_format );

						return ! empty( $result ) ? true : false;
					} elseif ( ! empty( $activation_ids ) && $resource->activations_total > 1 ) {
						$key = array_search( $activation_resource->activation_id, $activation_ids );
						// Remove the activation ID from the array.
						unset( $activation_ids[ $key ] );
						// Reindex the array keys.
						$activation_ids = array_values( $activation_ids );
					} else {
						$activation_ids = '';
					}
				}

				// Update activation totals by API Resource ID.
				$data = array(
					'activation_ids'    => ! empty( $activation_ids ) ? WC_AM_FORMAT()->json_encode( $activation_ids ) : '',
					'activations_total' => $resource->activations_total > 0 ? $resource->activations_total - 1 : 0
				);

				$where = array(
					'api_resource_id' => $resource->api_resource_id
				);

				$data_format = array(
					'%s',
					'%d'
				);

				$where_format = array(
					'%d'
				);

				$wpdb->update( $wpdb->prefix . WC_AM_USER()->get_api_resource_table_name(), $data, $where, $data_format, $where_format );

				// Delete only the row for the activation ID removed from the array that also matches the instance ID.
				if ( $activation_resource->activation_id ) {
					$where = array(
						'instance'      => $instance_id,
						'activation_id' => $activation_resource->activation_id
					);

					$where_format = array(
						'%s',
						'%d'
					);

					$result = $wpdb->delete( $wpdb->prefix . WC_AM_USER()->get_api_activation_table_name(), $where, $where_format );
				} else { // Delete only the row that matches the instance ID.
					$where = array(
						'instance' => $instance_id
					);

					$where_format = array(
						'%s'
					);

					$result = $wpdb->delete( $wpdb->prefix . WC_AM_USER()->get_api_activation_table_name(), $where, $where_format );
				}
			}
		}

		return ! empty( $result ) ? true : false;
	}

	/**
	 * Delete activation in My Account > API Keys row.
	 *
	 * @since 2.0
	 */
	public function delete_my_account_activation() {
		if ( isset( $_GET[ 'delete_activation' ] ) && isset( $_GET[ 'instance' ] ) && isset( $_GET[ '_wpnonce' ] ) ) {
			if ( wp_verify_nonce( $_GET[ '_wpnonce' ] ) === false ) {
				wc_add_notice( esc_html__( 'The activation could not be deleted.', 'woocommerce-api-manager' ), 'error' );
			}

			$result = $this->delete_api_key_activation_by_instance_id( wc_clean( $_GET[ 'instance' ] ) );

			/**
			 * Delete cache.
			 *
			 * @since 2.2.0
			 */
			WC_AM_SMART_CACHE()->delete_cache( wc_clean( array(
				                                             'admin_resources' => array(
					                                             'instance'      => $_GET[ 'instance' ],
					                                             'order_id'      => $_GET[ 'order_id' ],
					                                             'sub_parent_id' => $_GET[ 'sub_parent_id' ],
					                                             'api_key'       => $_GET[ 'api_key' ],
					                                             'product_id'    => $_GET[ 'product_id' ],
					                                             'user_id'       => $_GET[ 'user_id' ]
				                                             )
			                                             ) ), true );

			if ( $result ) {
				wp_safe_redirect( esc_url( $this->get_api_keys_url() ) );

				exit();
			} else {
				wc_add_notice( esc_html__( 'The activation could not be deleted.', 'woocommerce-api-manager' ), 'error' );
			}
		}
	}

	/**
	 * Deletes all the API Key activations with the activation ID.
	 *
	 * @since 2.0
	 *
	 * @param int $activation_id
	 *
	 * @return bool
	 */
	public function delete_api_key_activation_by_activation_id( $activation_id ) {
		return $this->delete_by( array( 'activation_id' => $activation_id ), array( '%d' ) );
	}

	/**
	 * Delete API Key activation by activation resource ID.
	 *
	 * @since 2.0
	 *
	 * @param int $api_resource_id
	 *
	 * @return bool
	 */
	public function delete_api_key_activation_by_api_resource_id( $api_resource_id ) {
		return $this->delete_by( array( 'api_resource_id' => $api_resource_id ), array( '%d' ) );
	}

	/**
	 * Delete API Key activation by sub_item_id.
	 *
	 * @since 2.1
	 *
	 * @param int $sub_item_id
	 *
	 * @return bool
	 */
	public function delete_api_key_activation_by_sub_item_id( $sub_item_id ) {
		return $this->delete_by( array( 'sub_item_id' => $sub_item_id ), array( '%d' ) );
	}

	/**
	 * Deletes all the API Key activations with the User ID.
	 *
	 * @since 2.0
	 *
	 * @param int $user_id
	 *
	 * @return bool
	 */
	public function delete_api_key_activation_by_user_id( $user_id ) {
		return $this->delete_by( array( 'user_id' => $user_id ), array( '%d' ) );
	}

	/**
	 * Delete API Key activation by order_id.
	 *
	 * @since 2.0
	 *
	 * @param int $order_id
	 *
	 * @return bool
	 */
	public function delete_api_key_activation_by_order_id( $order_id ) {
		return $this->delete_by( array( 'order_id' => $order_id ), array( '%d' ) );
	}

	/**
	 * Deletes all of the $needle.
	 *
	 * @since 2.0
	 *
	 * @param array $needle What to delete. i.e. array( 'user_id' => $user_id ). ( 'string' => int|string )
	 * @param array $format Either %s or %d. i.e. array( '%d' ). ( 'string' )
	 *
	 * @return bool
	 */
	public function delete_by( $needle, $format ) {
		global $wpdb;

		$result = $wpdb->delete( $wpdb->prefix . WC_AM_USER()->get_api_activation_table_name(), $needle, $format );

		return ! empty( $result ) ? true : false;
	}

	/**
	 * Delete all API Resource Activation IDs by User ID.
	 *
	 * @since 2.1.3
	 *
	 * @param int $user_id
	 */
	public function delete_all_api_resource_activation_ids_by_user_id( $user_id ) {
		global $wpdb;

		$data = array(
			'activation_ids'    => '',
			'activations_total' => 0
		);

		$where = array(
			'user_id' => $user_id
		);

		$data_format = array(
			'%s',
			'%d'
		);

		$where_format = array(
			'%d'
		);

		$wpdb->update( $wpdb->prefix . WC_AM_USER()->get_api_resource_table_name(), $data, $where, $data_format, $where_format );
	}

	/**
	 * Delete excess API Key activations by activation resource ID.
	 *
	 * @since 2.0
	 *
	 * @param array $activation_ids JSON formatted
	 * @param int   $activations_purchased_total
	 */
	public function delete_excess_api_key_activations_by_activation_id( $activation_ids, $activations_purchased_total ) {
		global $wpdb;

		$activation_ids         = json_decode( $activation_ids, true );
		$api_resource_ids_total = WC_AM_FORMAT()->count( $activation_ids );

		if ( $api_resource_ids_total && $api_resource_ids_total > 0 && ! empty( $activations_purchased_total ) && (int) $api_resource_ids_total > (int) $activations_purchased_total ) {
			for ( $i = 0; $i <= $activations_purchased_total; $i ++ ) {
				if ( ! empty( $activation_ids ) ) {
					$activation_id = current( $activation_ids );

					if ( ! empty( $activation_id ) ) {
						$activation_resource = $this->get_activation_resource_by_activation_id( $activation_id );

						if ( ! empty( $activation_resource ) ) {
							if ( ! empty( $activation_resource->api_resource_id ) ) {
								WC_AM_API_RESOURCE_DATA_STORE()->delete_api_resource_id_activation_ids( $activation_resource->api_resource_id, $activation_id );
							}

							if ( ! empty( $activation_resource->associated_api_key_id ) ) {
								WC_AM_ASSOCIATED_API_KEY_DATA_STORE()->delete_associated_api_key_activation_ids( $activation_resource->associated_api_key_id, $activation_id );
							}

							// Update the activations_total in the api_resource_table.
							$activation_ids_from_api_resource_table = $this->get_activation_ids_by_api_resource_id( $activation_resource->api_resource_id );
							$api_resource_activations_total         = WC_AM_FORMAT()->count( $activation_ids_from_api_resource_table );

							$data = array(
								'activations_total' => ! empty( $api_resource_activations_total ) ? $api_resource_activations_total : 0
							);

							$where = array(
								'api_resource_id' => $activation_resource->api_resource_id
							);

							$data_format = array(
								'%d'
							);

							$where_format = array(
								'%d'
							);

							$wpdb->update( $wpdb->prefix . WC_AM_USER()->get_api_resource_table_name(), $data, $where, $data_format, $where_format );
						}

						$this->delete_api_key_activation_by_activation_id( $activation_id );

						array_pop( $activation_ids );
					}
				}
			}
		}
	}
}