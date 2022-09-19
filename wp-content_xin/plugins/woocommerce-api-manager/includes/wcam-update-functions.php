<?php
/**
 * WooCommerce API Manager Updates
 *
 * @since       2.0
 * @author      Todd Lahman LLC
 * @copyright   Copyright (c) Todd Lahman LLC
 * @package     WooCommerce API Manager/Updates
 */
defined( 'ABSPATH' ) || exit;

/**
 * Copies all API prooduct orders to API resources table.
 *
 * @since 2.0
 */
function wc_am_update_200_create_master_api_key() {
	global $wpdb;

	if ( get_option( 'wc_am_master_api_key_created' ) === false ) {
		$id_list = $wpdb->get_col( "
				SELECT ID
				FROM $wpdb->users
			" );

		if ( ! empty( $id_list ) ) {
			foreach ( $id_list as $key => $user_id ) {
				$user_master_api_key = WC_AM_USER()->get_master_api_key( $user_id );

				if ( empty( $user_master_api_key ) ) {
					WC_AM_USER()->set_registration_master_key_and_status( $user_id );
				}
			}

			update_option( 'wc_am_master_api_key_created', 'yes' );
		}

		unset( $id_list );
	}
}

/**
 * Copies all API prooduct orders to API resources table.
 *
 * @since 2.0
 */
function wc_am_update_200_data_migrate_orders() {
	global $wpdb;

	$user_meta_key_orders = 'wc_am_orders';

	$id_list = $wpdb->get_col( "
		SELECT ID
		FROM $wpdb->users
	" );

	if ( ! empty( $id_list ) ) {
		foreach ( $id_list as $key => $user_id ) {
			$user_master_api_key = WC_AM_USER()->get_master_api_key( $user_id );

			if ( empty( $user_master_api_key ) ) {
				WC_AM_USER()->set_registration_master_key_and_status( $user_id );
			}

			// Get old resources.
			$old_resource = get_metadata( 'user', $user_id, $wpdb->get_blog_prefix() . $user_meta_key_orders, true );

			if ( ! empty( $old_resource ) ) {
				foreach ( $old_resource as $item_key => $item ) {
					if ( ! empty( $item ) ) {
						if ( ! empty( $item[ 'order_key' ] ) ) {
							$api_key             = ( ! empty( $item[ 'api_key' ] ) ) ? $item[ 'api_key' ] : $item[ 'order_key' ];
							$parent_product_id   = ! empty( $item[ 'parent_product_id' ] ) && WC_AM_PRODUCT_DATA_STORE()->has_valid_product_status( $item[ 'parent_product_id' ] ) ? $item[ 'parent_product_id' ] : 0;
							$variable_product_id = ! empty( $item[ 'variable_product_id' ] ) && WC_AM_PRODUCT_DATA_STORE()->has_valid_product_status( $item[ 'variable_product_id' ] ) ? $item[ 'variable_product_id' ] : 0;

							if ( empty( $parent_product_id ) ) {
								continue;
							}

							$product_id             = ! empty( $variable_product_id ) ? $variable_product_id : $parent_product_id;
							$is_parent_api_product  = WC_AM_PRODUCT_DATA_STORE()->get_meta( $parent_product_id, '_is_api' );
							$is_variabl_api_product = WC_AM_PRODUCT_DATA_STORE()->get_meta( $variable_product_id, '_is_api' );

							if ( $is_parent_api_product == 'yes' && $is_variabl_api_product != 'yes' && ! empty( $variable_product_id ) ) {
								WC_AM_PRODUCT_DATA_STORE()->update_meta( $variable_product_id, '_is_api', 'yes' );
							}

							// Only add the order once, and only if the product is valid, meaning it is not in the trash or has been deleted.
							if ( ! WC_AM_API_RESOURCE_DATA_STORE()->has_order( $item[ 'order_id' ], $product_id ) ) {
								// Get the old parent/simple product activations.
								$api_activations_parent = WC_AM_PRODUCT_DATA_STORE()->get_meta( $product_id, '_api_activations_parent' );

								// Move the old parent/simple product activations to new meta key.
								if ( ! empty( $api_activations_parent ) ) {
									WC_AM_PRODUCT_DATA_STORE()->update_meta( $product_id, '_api_activations', $api_activations_parent );
									// Delete the old parent/simple product activations.
									WC_AM_PRODUCT_DATA_STORE()->delete_meta( $product_id, '_api_activations_parent' );
								}

								WC_AM_ORDER()->update_order( $item[ 'order_id' ] );

								$valid_product = WC_AM_PRODUCT_DATA_STORE()->has_valid_product_status( $product_id );
								$is_api        = WC_AM_PRODUCT_DATA_STORE()->is_api_product( $product_id );

								if ( $valid_product && $is_api ) {
									WC_AM_ASSOCIATED_API_KEY_DATA_STORE()->add_associated_api_key( $api_key, $item[ 'order_id' ], $product_id );
								}
							}
						}
					}

					// Free some memory.
					unset( $item_key, $item );
				}
			}

			unset( $old_resource );

			// Delete old resources.
			delete_metadata( 'user', $user_id, $wpdb->get_blog_prefix() . $user_meta_key_orders );
			// Free some memory.
			unset( $key, $user_id );
		}
	}

	unset( $id_list );
}

/**
 * Copies all activations to the new activation table, and links activations to resource table.
 *
 * @since 2.0
 */
function wc_am_update_200_data_migrate_activations() {
	global $wpdb;

	$user_meta_key_activations = 'wc_am_activations_';
	$wc_am_api_resource        = 'wc_am_api_resource';
	$wc_am_api_activation      = 'wc_am_api_activation';

	$id_list = $wpdb->get_col( "
		SELECT ID
		FROM $wpdb->users
	" );

	if ( ! empty( $id_list ) ) {
		foreach ( $id_list as $key => $user_id ) {
			$master_api_key = WC_AM_USER()->get_master_api_key( $user_id );

			$sql = "
				SELECT *
				FROM {$wpdb->prefix}" . $wc_am_api_resource . "
				WHERE user_id = %d
			";

			// Get the API resource order items for this user.
			$resources = $wpdb->get_results( $wpdb->prepare( $sql, $user_id ) );

			if ( ! empty( $resources ) ) {
				foreach ( $resources as $k => $resource ) {
					// Get old activations.
					$activation_resource = get_metadata( 'user', $user_id, $wpdb->get_blog_prefix() . $user_meta_key_activations . $resource->order_key, true );

					if ( ! empty( $activation_resource ) ) {
						$count = WC_AM_FORMAT()->count( $activation_resource );

						if ( $count > 0 ) {
							for ( $i = 0; $i < $count; $i ++ ) {
								if ( ! empty( $resource->order_id ) ) {
									$order = WC_AM_ORDER_DATA_STORE()->get_order_object( $resource->order_id );

									// Make sure the order exists.
									if ( is_object( $order ) ) {
										$activation                  = $activation_resource[ $i ];
										$api_key                     = get_post_meta( $resource->order_id, '_api_license_key_0', true );
										$associated_api_key_resource = WC_AM_ASSOCIATED_API_KEY_DATA_STORE()->get_associated_api_key_resources_by_api_key( $api_key );

										if ( ! empty( $associated_api_key_resource ) && ! empty( $activation[ 'instance' ] ) && ! empty( $activation[ 'product_id' ] ) ) {
											$resource = WC_AM_API_RESOURCE_DATA_STORE()->get_resources_by_api_resource_id( $associated_api_key_resource->api_resource_id );

											if ( ! empty( $activation[ 'activation_time' ] ) ) {
												try {
													$date = new DateTime( $activation[ 'activation_time' ] );
													$time = $date->format( 'U' );
												} catch ( Exception $exception ) {
													$time = 0;
												}
											}

											$data = array(
												'activation_time'       => ! empty( $time ) ? (int) $time : 0,
												'api_key'               => ! empty( $api_key ) ? (string) $api_key : '',
												'api_resource_id'       => ! empty( $associated_api_key_resource->api_resource_id ) ? (int) $associated_api_key_resource->api_resource_id : 0,
												'assigned_product_id'   => ! empty( $resource->product_id ) ? (int) $resource->product_id : 0,
												'associated_api_key_id' => ! empty( $associated_api_key_resource->associated_api_key_id ) ? (int) $associated_api_key_resource->associated_api_key_id : 0,
												'ip_address'            => '',
												'instance'              => ! empty( $activation[ 'instance' ] ) ? (string) $activation[ 'instance' ] : '',
												'master_api_key'        => $master_api_key,
												'object'                => ! empty( $activation[ 'activation_domain' ] ) ? (string) $activation[ 'activation_domain' ] : '',
												'order_id'              => ! empty( $resource->order_id ) ? (int) $resource->order_id : 0,
												'product_id'            => ! empty( $activation[ 'product_id' ] ) ? (string) $activation[ 'product_id' ] : '',
												'product_order_api_key' => ! empty( $resource->product_order_api_key ) ? (string) $resource->product_order_api_key : '',
												'sub_id'                => ! empty( $resource->sub_id ) ? (int) $resource->sub_id : 0,
												'sub_item_id'           => ! empty( $resource->sub_item_id ) ? (int) $resource->sub_item_id : 0,
												'sub_parent_id'         => ! empty( $resource->sub_parent_id ) ? (int) $resource->sub_parent_id : 0,
												'version'               => ! empty( $activation[ 'software_version' ] ) ? (string) $activation[ 'software_version' ] : '',
												'update_requests'       => 0,
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
												'%s',
												'%s',
												'%d',
												'%d',
												'%d',
												'%s',
												'%d',
												'%d'
											);

											$result = $wpdb->insert( $wpdb->prefix . $wc_am_api_activation, $data, $format ) ? true : false;

											if ( $result ) {
												WC_AM_ASSOCIATED_API_KEY_DATA_STORE()->update_associated_api_key_activation_ids_list( $api_key, $wpdb->insert_id );

												$activation_ids = WC_AM_API_ACTIVATION_DATA_STORE()->get_activations_by_order_id( $resource->order_id );
												$activation_ids = ! empty( $activation_ids ) ? array_merge( $activation_ids, array( $wpdb->insert_id ) ) : array( $wpdb->insert_id );

												$data = array(
													'activation_ids'    => WC_AM_FORMAT()->json_encode( $activation_ids ),
													'activations_total' => $resource->activations_total + 1
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

												$wpdb->update( $wpdb->prefix . $wc_am_api_resource, $data, $where, $data_format, $where_format );
											}
										}

										unset( $resource );
									}
								}
							}
						}
					}

					// Delete old activations.
					delete_metadata( 'user', $user_id, $wpdb->get_blog_prefix() . $user_meta_key_activations . $resource->order_key );
					unset( $activation_resource, $k, $resource );
				}
			}

			unset( $resources, $key, $user_id );
		}
	}

	unset( $id_list );
}

/**
 * Adds a unique product ID to all products to be used as a product ID. Uses the numerical product ID from the product.
 *
 * @since 2.0
 */
function wc_am_update_200_data_add_product_id_and_add_api_orders_processed_flag_to_api_products() {
	global $wpdb;

	$sql = "
        SELECT ID
        FROM {$wpdb->prefix}posts
        WHERE ( post_type = %s OR post_type = %s )
    ";

	$product_ids = $wpdb->get_col( $wpdb->prepare( $sql, 'product', 'product_variation' ) );

	if ( ! empty( $product_ids ) ) {
		foreach ( $product_ids as $key => $product_id ) {
			$valid_product = WC_AM_PRODUCT_DATA_STORE()->has_valid_product_status( $product_id );
			$is_api        = WC_AM_PRODUCT_DATA_STORE()->is_api_product( $product_id );

			if ( $valid_product && $is_api ) {
				$resource_product_id  = WC_AM_PRODUCT_DATA_STORE()->get_meta( $product_id, '_api_resource_product_id' );
				$api_orders_processed = WC_AM_PRODUCT_DATA_STORE()->get_meta( $product_id, '_api_orders_processed' );

				if ( empty( $resource_product_id ) ) {
					WC_AM_PRODUCT_DATA_STORE()->update_meta( $product_id, '_api_resource_product_id', $product_id );
				}

				if ( $api_orders_processed != 'yes' ) {
					WC_AM_PRODUCT_DATA_STORE()->update_meta( $product_id, '_api_orders_processed', 'yes' );
				}

				delete_post_meta( $product_id, 'parent_product_id' );
				delete_post_meta( $product_id, 'variable_product_id' );
			}

			unset( $key, $product_id );
		}
	}

	unset( $product_ids );
}

/**
 * Merge all variations of software title meta_key(s) into single legacy meta_key.
 *
 * @since 2.0
 */
function wc_am_update_200_data_merge_software_title() {
	global $wpdb;

	$sql = "
        SELECT ID
        FROM {$wpdb->prefix}posts
        WHERE ( post_type = %s OR post_type = %s )
    ";

	$product_ids = $wpdb->get_col( $wpdb->prepare( $sql, 'product', 'product_variation' ) );

	if ( ! empty( $product_ids ) ) {
		foreach ( $product_ids as $key => $product_id ) {
			$valid_product = WC_AM_PRODUCT_DATA_STORE()->has_valid_product_status( $product_id );
			$is_api        = WC_AM_PRODUCT_DATA_STORE()->is_api_product( $product_id );

			if ( $valid_product && $is_api ) {
				$software_title = $wpdb->get_var( $wpdb->prepare( "
					SELECT meta_value
					FROM $wpdb->postmeta
					WHERE meta_key = %s
					AND post_id = %d
				", 'software_title', $product_id ) );

				if ( ! empty( $software_title ) ) {
					WC_AM_PRODUCT_DATA_STORE()->update_meta( $product_id, '_api_resource_title', $software_title );

					delete_post_meta( $product_id, 'software_title' );
					delete_post_meta( $product_id, '_software_product_id' );
					delete_post_meta( $product_id, '_api_software_title_parent' );
					delete_post_meta( $product_id, '_api_software_title_var' );
				} else {
					$software_product_id = $wpdb->get_var( $wpdb->prepare( "
						SELECT meta_value
						FROM $wpdb->postmeta
						WHERE meta_key = %s
						AND post_id = %d
					", '_software_product_id', $product_id ) );

					$api_software_title_parent = $wpdb->get_var( $wpdb->prepare( "
						SELECT meta_value
						FROM $wpdb->postmeta
						WHERE meta_key = %s
						AND post_id = %d
					", '_api_software_title_parent', $product_id ) );

					$api_software_title_var = $wpdb->get_var( $wpdb->prepare( "
						SELECT meta_value
						FROM $wpdb->postmeta
						WHERE meta_key = %s
						AND post_id = %d
					", '_api_software_title_var', $product_id ) );

					if ( ! empty( $software_product_id ) ) {
						WC_AM_PRODUCT_DATA_STORE()->update_meta( $product_id, '_api_resource_title', $software_product_id );

						delete_post_meta( $product_id, 'software_title' );
						delete_post_meta( $product_id, '_software_product_id' );
						delete_post_meta( $product_id, '_api_software_title_parent' );
						delete_post_meta( $product_id, '_api_software_title_var' );
					} elseif ( ! empty( $api_software_title_parent ) ) {
						WC_AM_PRODUCT_DATA_STORE()->update_meta( $product_id, '_api_resource_title', $api_software_title_parent );

						delete_post_meta( $product_id, 'software_title' );
						delete_post_meta( $product_id, '_software_product_id' );
						delete_post_meta( $product_id, '_api_software_title_parent' );
						delete_post_meta( $product_id, '_api_software_title_var' );
					} elseif ( ! empty( $api_software_title_var ) ) {
						WC_AM_PRODUCT_DATA_STORE()->update_meta( $product_id, '_api_resource_title', $api_software_title_var );

						delete_post_meta( $product_id, 'software_title' );
						delete_post_meta( $product_id, '_software_product_id' );
						delete_post_meta( $product_id, '_api_software_title_parent' );
						delete_post_meta( $product_id, '_api_software_title_var' );
					}
				}

				unset( $key, $product_id );
			}
		}

		unset( $product_ids );
	}
}

/**
 * Updates the update database version.
 *
 * @since 2.0
 */
function wc_am_update_200_db_version() {
	WC_AM_INSTALL()->update_db_version( '2.0.0' );
}

/**
 * Update access_granted time to order creation time API resources table.
 *
 * @since 2.0.1
 */
function wc_am_update_201_data_migrate_access_granted_to_order_created_time() {
	global $wpdb;

	$order_ids = WC_AM_API_RESOURCE_DATA_STORE()->get_all_order_ids();

	if ( is_array( $order_ids ) && ! empty( $order_ids ) ) {
		foreach ( $order_ids as $order_id ) {
			if ( ! empty( $order_id ) ) {
				$order_created_time = WC_AM_ORDER_DATA_STORE()->get_order_time_to_epoch_time_stamp( $order_id );

				if ( ! empty( $order_created_time ) ) {
					$data = array(
						'access_granted' => $order_created_time
					);

					$where = array(
						'order_id' => $order_id
					);

					$data_format = array(
						'%d'
					);

					$where_format = array(
						'%d'
					);

					$wpdb->update( $wpdb->prefix . WC_AM_USER()->get_api_resource_table_name(), $data, $where, $data_format, $where_format );
				}

				unset( $order_id );
			}
		}
	}

	unset( $order_ids );
}

/**
 * Updates the update database version.
 *
 * @since 2.0.1
 */
function wc_am_update_201_db_version() {
	WC_AM_INSTALL()->update_db_version( '2.0.1' );
}

/**
 * Migrates data if failed previously.
 *
 * @since 2.0.5
 */
function wc_am_update_205_check_if_api_resources_table_is_empty() {
	$empty = WC_AM_API_RESOURCE_DATA_STORE()->is_api_resource_table_empty();

	if ( empty( $empty ) ) {
		wc_am_update_200_data_migrate_orders();
		wc_am_update_200_data_migrate_activations();
		wc_am_update_201_data_migrate_access_granted_to_order_created_time();

		$empty = WC_AM_API_RESOURCE_DATA_STORE()->is_api_resource_table_empty();

		if ( empty( $empty ) ) {
			global $wpdb;

			$sql = "
		        SELECT ID
		        FROM {$wpdb->prefix}posts
		        WHERE ( post_type = %s OR post_type = %s )
		    ";

			$product_ids = $wpdb->get_col( $wpdb->prepare( $sql, 'product', 'product_variation' ) );

			if ( ! empty( $product_ids ) ) {
				foreach ( $product_ids as $key => $product_id ) {
					WC_AM_ORDER()->add_new_api_product_orders( $product_id );

					unset( $key, $product_id );
				}

				unset( $product_ids );
			}
		}
	}
}

/**
 * Updates the update database version.
 *
 * @since 2.0.5
 */
function wc_am_update_205_db_version() {
	WC_AM_INSTALL()->update_db_version( '2.0.5' );
}

/**
 * Updates the update database version.
 *
 * @since 2.2.6
 */
function wc_am_update_2_2_6_db_version() {
	WC_AM_INSTALL()->update_db_version( '2.2.6' );
}