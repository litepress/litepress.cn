<?php

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * WooCommerce API Manager API Resource Data Store Class
 *
 * @since       2.0
 *
 * @author      Todd Lahman LLC
 * @copyright   Copyright (c) Todd Lahman LLC
 * @package     WooCommerce API Manager/API Resourc Data Store
 */
class WC_AM_API_Resource_Data_Store {

	/**
	 * @var null
	 */
	private static $_instance = null;

	/**
	 * @static
	 * @return null|\WC_AM_API_Resource_Data_Store
	 */
	public static function instance() {

		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	private function __construct() { }

	/**
	 * Return all API resource order item rows matching the order_id.
	 *
	 * @see   set_transient() option_name length limit change https://core.trac.wordpress.org/changeset/34030
	 *
	 * @since 2.0
	 *
	 * @param int $order_id
	 *
	 * @throws \Exception
	 *
	 * @return array
	 */
	public function get_all_api_resources_for_order_id( $order_id ) {
		global $wpdb;

		if ( ! WCAM()->get_db_cache() ) {
			$sql = "
				SELECT *
				FROM {$wpdb->prefix}" . WC_AM_USER()->get_api_resource_table_name() . "
				WHERE order_id = %d
				ORDER BY product_title
			";

			// Get the API resource order items for this user.
			$resources = $wpdb->get_results( $wpdb->prepare( $sql, $order_id ) );

			// Only return the active API resources.
			$resources = $this->get_active_resources( $resources );

			return $resources ? $resources : array();
		} else {
			$trans_name_sql                   = 'wc_am_get_all_api_resources_for_order_id_' . $order_id;
			$trans_name_active_resources      = 'wc_am_get_all_api_resources_for_order_id_ar_' . $order_id;
			$resources_sql_trans              = WC_AM_SMART_CACHE()->set_or_get_cache( $trans_name_sql );
			$resources_active_resources_trans = WC_AM_SMART_CACHE()->set_or_get_cache( $trans_name_active_resources );

			if ( $resources_active_resources_trans !== false ) {
				$resources = $resources_active_resources_trans;
			} else {
				if ( $resources_sql_trans === false ) {
					$sql = "
						SELECT *
						FROM {$wpdb->prefix}" . WC_AM_USER()->get_api_resource_table_name() . "
						WHERE order_id = %d
						ORDER BY product_title
					";

					// Get the API resource order items for this user.
					$resources_sql = $wpdb->get_results( $wpdb->prepare( $sql, $order_id ) );

					if ( ! empty( $resources_sql ) ) {
						WC_AM_SMART_CACHE()->set_or_get_cache( $trans_name_sql, $resources_sql, WCAM()->get_db_cache_expires() * MINUTE_IN_SECONDS );
					} else {
						WC_AM_SMART_CACHE()->queue_delete_transient( $trans_name_sql );
					}
				}

				// Only return the active API resources.
				$resources = $this->get_active_resources( ! empty( $resources_sql ) ? $resources_sql : $resources_sql_trans );

				if ( ! empty( $resources ) ) {
					WC_AM_SMART_CACHE()->set_or_get_cache( $trans_name_active_resources, $resources, WCAM()->get_db_cache_expires() * MINUTE_IN_SECONDS );
				} else {
					WC_AM_SMART_CACHE()->queue_delete_transient( $trans_name_active_resources );
				}
			}

			return $resources;
		}
	}

	/**
	 * Return all non WooCommerce Subscription API resource order item rows matching the order_id.
	 *
	 * @since 2.0
	 *
	 * @param int $order_id
	 *
	 * @return array
	 * @throws \Exception
	 *
	 */
	public function get_all_api_non_wc_subscription_resources_for_order_id( $order_id ) {
		global $wpdb;

		if ( ! WCAM()->get_db_cache() ) {
			$sql = "
				SELECT *
				FROM {$wpdb->prefix}" . WC_AM_USER()->get_api_resource_table_name() . "
				WHERE order_id = %d
				AND order_item_id > 0
			";

			// Get the API resource order items for this user.
			$resources = $wpdb->get_results( $wpdb->prepare( $sql, $order_id ) );

			// Only return the active API resources.
			$resources = $this->get_active_resources( $resources );

			return $resources ? $resources : array();
		} else {
			$trans_name_sql                   = 'wc_am_get_all_api_non_wc_sub_resources_for_order_id_' . $order_id;
			$trans_name_active_resources      = 'wc_am_get_all_api_non_wc_sub_resources_for_order_id_ar_' . $order_id;
			$resources_sql_trans              = WC_AM_SMART_CACHE()->set_or_get_cache( $trans_name_sql );
			$resources_active_resources_trans = WC_AM_SMART_CACHE()->set_or_get_cache( $trans_name_active_resources );

			if ( $resources_active_resources_trans !== false ) {
				$resources = $resources_active_resources_trans;
			} else {
				if ( $resources_sql_trans === false ) {
					$sql = "
						SELECT *
						FROM {$wpdb->prefix}" . WC_AM_USER()->get_api_resource_table_name() . "
						WHERE order_id = %d
						AND order_item_id > 0
					";

					// Get the API resource order items for this user.
					$resources_sql = $wpdb->get_results( $wpdb->prepare( $sql, $order_id ) );

					if ( ! empty( $resources_sql ) ) {
						WC_AM_SMART_CACHE()->set_or_get_cache( $trans_name_sql, $resources_sql, WCAM()->get_db_cache_expires() * MINUTE_IN_SECONDS );
					} else {
						WC_AM_SMART_CACHE()->queue_delete_transient( $trans_name_sql );
					}
				}

				// Only return the active API resources.
				$resources = $this->get_active_resources( ! empty( $resources_sql ) ? $resources_sql : $resources_sql_trans );

				if ( ! empty( $resources ) ) {
					WC_AM_SMART_CACHE()->set_or_get_cache( $trans_name_active_resources, $resources, WCAM()->get_db_cache_expires() * MINUTE_IN_SECONDS );
				} else {
					WC_AM_SMART_CACHE()->queue_delete_transient( $trans_name_active_resources );
				}
			}

			return $resources;
		}
	}

	/**
	 * Return all API resource order item rows matching the sub_parent_id.
	 *
	 * @since 2.0
	 *
	 * @param int $sub_parent_id
	 *
	 * @return array
	 * @throws \Exception
	 *
	 */
	public function get_all_api_resources_for_sub_parent_id( $sub_parent_id ) {
		global $wpdb;

		if ( ! WCAM()->get_db_cache() ) {
			$sql = "
				SELECT *
				FROM {$wpdb->prefix}" . WC_AM_USER()->get_api_resource_table_name() . "
				WHERE sub_parent_id = %d
			";

			// Get the API resource order items for this user.
			$resources = $wpdb->get_results( $wpdb->prepare( $sql, $sub_parent_id ) );

			// Only return the active API resources.
			$resources = $this->get_active_resources( $resources );

			return $resources ? $resources : array();
		} else {
			$trans_name_sql                   = 'wc_am_get_all_api_resources_for_sub_parent_id_' . $sub_parent_id;
			$trans_name_active_resources      = 'wc_am_get_all_api_resources_for_sub_parent_id_ar_' . $sub_parent_id;
			$resources_sql_trans              = WC_AM_SMART_CACHE()->set_or_get_cache( $trans_name_sql );
			$resources_active_resources_trans = WC_AM_SMART_CACHE()->set_or_get_cache( $trans_name_active_resources );

			if ( $resources_active_resources_trans !== false ) {
				$resources = $resources_active_resources_trans;
			} else {
				if ( $resources_sql_trans === false ) {
					$sql = "
						SELECT *
						FROM {$wpdb->prefix}" . WC_AM_USER()->get_api_resource_table_name() . "
						WHERE sub_parent_id = %d
					";

					// Get the API resource order items for this user.
					$resources_sql = $wpdb->get_results( $wpdb->prepare( $sql, $sub_parent_id ) );

					if ( ! empty( $resources_sql ) ) {
						WC_AM_SMART_CACHE()->set_or_get_cache( $trans_name_sql, $resources_sql, WCAM()->get_db_cache_expires() * MINUTE_IN_SECONDS );
					} else {
						WC_AM_SMART_CACHE()->queue_delete_transient( $trans_name_sql );
					}
				}

				// Only return the active API resources.
				$resources = $this->get_active_resources( ! empty( $resources_sql ) ? $resources_sql : $resources_sql_trans );

				if ( ! empty( $resources ) ) {
					WC_AM_SMART_CACHE()->set_or_get_cache( $trans_name_active_resources, $resources, WCAM()->get_db_cache_expires() * MINUTE_IN_SECONDS );
				} else {
					WC_AM_SMART_CACHE()->queue_delete_transient( $trans_name_active_resources );
				}
			}

			return $resources;
		}
	}

	/**
	 * Return all API resource order item rows matching the user_id.
	 *
	 * @since 2.0
	 *
	 * @param int $user_id
	 *
	 * @return array
	 * @throws \Exception
	 *
	 */
	public function get_api_resources_for_user_id( $user_id ) {
		global $wpdb;

		if ( ! WCAM()->get_db_cache() ) {
			$sql = "
				SELECT *
				FROM {$wpdb->prefix}" . WC_AM_USER()->get_api_resource_table_name() . "
				WHERE user_id = %d
			";

			// Get the API resource order items for this user.
			$resources = $wpdb->get_results( $wpdb->prepare( $sql, $user_id ) );

			// Only return the active API resources.
			$resources = $this->get_active_resources( $resources );

			return $resources ? $resources : array();
		} else {
			$trans_name_sql                   = 'wc_am_get_api_resources_for_user_id_' . $user_id;
			$trans_name_active_resources      = 'wc_am_get_api_resources_for_user_id_ar_' . $user_id;
			$resources_sql_trans              = WC_AM_SMART_CACHE()->set_or_get_cache( $trans_name_sql );
			$resources_active_resources_trans = WC_AM_SMART_CACHE()->set_or_get_cache( $trans_name_active_resources );

			if ( $resources_active_resources_trans !== false ) {
				$resources = $resources_active_resources_trans;
			} else {
				if ( $resources_sql_trans === false ) {
					$sql = "
						SELECT *
						FROM {$wpdb->prefix}" . WC_AM_USER()->get_api_resource_table_name() . "
						WHERE user_id = %d
					";

					// Get the API resource order items for this user.
					$resources_sql = $wpdb->get_results( $wpdb->prepare( $sql, $user_id ) );

					if ( ! empty( $resources_sql ) ) {
						WC_AM_SMART_CACHE()->set_or_get_cache( $trans_name_sql, $resources_sql, WCAM()->get_db_cache_expires() * MINUTE_IN_SECONDS );
					} else {
						WC_AM_SMART_CACHE()->queue_delete_transient( $trans_name_sql );
					}
				}

				// Only return the active API resources.
				$resources = $this->get_active_resources( ! empty( $resources_sql ) ? $resources_sql : $resources_sql_trans );

				if ( ! empty( $resources ) ) {
					WC_AM_SMART_CACHE()->set_or_get_cache( $trans_name_active_resources, $resources, WCAM()->get_db_cache_expires() * MINUTE_IN_SECONDS );
				} else {
					WC_AM_SMART_CACHE()->queue_delete_transient( $trans_name_active_resources );
				}
			}

			return $resources;
		}
	}

	/**
	 * Return all API resource order item rows matching the user_id, and sort by product title.
	 *
	 * @since 2.0
	 *
	 * @param int $user_id
	 *
	 * @return array
	 * @throws \Exception
	 *
	 */
	public function get_api_resources_for_user_id_sort_by_product_title( $user_id ) {
		global $wpdb;

		if ( ! WCAM()->get_db_cache() ) {
			$sql = "
				SELECT *
				FROM {$wpdb->prefix}" . WC_AM_USER()->get_api_resource_table_name() . "
				WHERE user_id = %d
				ORDER BY product_title
			";

			// Get the API resource order items for this user.
			$resources = $wpdb->get_results( $wpdb->prepare( $sql, $user_id ) );

			// Only return the active API resources.
			$resources = $this->get_active_resources( $resources );

			return $resources ? $resources : array();
		} else {
			$trans_name_sql                   = 'wc_am_get_api_resources_for_user_id_sort_by_product_title_' . $user_id;
			$trans_name_active_resources      = 'wc_am_get_api_resources_for_user_id_sort_by_product_title_ar_' . $user_id;
			$resources_sql_trans              = WC_AM_SMART_CACHE()->set_or_get_cache( $trans_name_sql );
			$resources_active_resources_trans = WC_AM_SMART_CACHE()->set_or_get_cache( $trans_name_active_resources );

			if ( $resources_active_resources_trans !== false ) {
				$resources = $resources_active_resources_trans;
			} else {
				if ( $resources_sql_trans === false ) {
					$sql = "
						SELECT *
						FROM {$wpdb->prefix}" . WC_AM_USER()->get_api_resource_table_name() . "
						WHERE user_id = %d
						ORDER BY product_title
					";

					// Get the API resource order items for this user.
					$resources_sql = $wpdb->get_results( $wpdb->prepare( $sql, $user_id ) );

					if ( ! empty( $resources_sql ) ) {
						WC_AM_SMART_CACHE()->set_or_get_cache( $trans_name_sql, $resources_sql, WCAM()->get_db_cache_expires() * MINUTE_IN_SECONDS );
					} else {
						WC_AM_SMART_CACHE()->queue_delete_transient( $trans_name_sql );
					}
				}

				// Only return the active API resources.
				$resources = $this->get_active_resources( ! empty( $resources_sql ) ? $resources_sql : $resources_sql_trans );

				if ( ! empty( $resources ) ) {
					WC_AM_SMART_CACHE()->set_or_get_cache( $trans_name_active_resources, $resources, WCAM()->get_db_cache_expires() * MINUTE_IN_SECONDS );
				} else {
					WC_AM_SMART_CACHE()->queue_delete_transient( $trans_name_active_resources );
				}
			}

			return $resources;
		}
	}

	/**
	 * Return all API resource order item rows matching the Master API Key.
	 *
	 * @since 2.0
	 *
	 * @param string $mak Master API Key.
	 *
	 * @return array
	 * @throws \Exception
	 *
	 */
	public function get_api_resources_for_master_api_key( $mak ) {
		global $wpdb;

		if ( ! WCAM()->get_db_cache() ) {
			$sql = "
				SELECT *
				FROM {$wpdb->prefix}" . WC_AM_USER()->get_api_resource_table_name() . "
				WHERE master_api_key = %s
			";

			// Get the API resource order items for this user.
			$resources = $wpdb->get_results( $wpdb->prepare( $sql, $mak ) );

			// Only return the active API resources.
			$resources = $this->get_active_resources( $resources );

			return $resources ? $resources : array();
		} else {
			$trans_name_sql                   = 'wc_am_get_ar_for_mac_' . $mak;
			$trans_name_active_resources      = 'wc_am_get_ar_for_mac_ar_' . $mak;
			$resources_sql_trans              = WC_AM_SMART_CACHE()->set_or_get_cache( $trans_name_sql );
			$resources_active_resources_trans = WC_AM_SMART_CACHE()->set_or_get_cache( $trans_name_active_resources );

			if ( $resources_active_resources_trans !== false ) {
				$resources = $resources_active_resources_trans;
			} else {
				if ( $resources_sql_trans === false ) {
					$sql = "
						SELECT *
						FROM {$wpdb->prefix}" . WC_AM_USER()->get_api_resource_table_name() . "
						WHERE master_api_key = %s
					";

					// Get the API resource order items for this user.
					$resources_sql = $wpdb->get_results( $wpdb->prepare( $sql, $mak ) );

					if ( ! empty( $resources_sql ) ) {
						WC_AM_SMART_CACHE()->set_or_get_cache( $trans_name_sql, $resources_sql, WCAM()->get_db_cache_expires() * MINUTE_IN_SECONDS );
					} else {
						WC_AM_SMART_CACHE()->queue_delete_transient( $trans_name_sql );
					}
				}

				// Only return the active API resources.
				$resources = $this->get_active_resources( ! empty( $resources_sql ) ? $resources_sql : $resources_sql_trans );

				if ( ! empty( $resources ) ) {
					WC_AM_SMART_CACHE()->set_or_get_cache( $trans_name_active_resources, $resources, WCAM()->get_db_cache_expires() * MINUTE_IN_SECONDS );
				} else {
					WC_AM_SMART_CACHE()->queue_delete_transient( $trans_name_active_resources );
				}
			}

			return $resources;
		}
	}

	/**
	 * Return all API resource order item rows matching the Product Order API Key.
	 *
	 * @since 2.0
	 *
	 * @param string $poak Product Order API Key.
	 * @param string $product_id
	 *
	 * @return array
	 * @throws \Exception
	 *
	 */
	public function get_api_resources_for_product_order_api_key( $poak, $product_id ) {
		global $wpdb;

		if ( ! WCAM()->get_db_cache() ) {
			$sql = "
				SELECT *
				FROM {$wpdb->prefix}" . WC_AM_USER()->get_api_resource_table_name() . "
				WHERE product_order_api_key = %s
				AND product_id = %d
			";

			// Get the API resource order items for this user.
			$resources = $wpdb->get_results( $wpdb->prepare( $sql, $poak, $product_id ) );

			// Only return the active API resources.
			$resources = $this->get_active_resources( $resources );

			return $resources ? $resources : array();
		} else {
			$trans_name_sql                   = 'wc_am_get_ar_for_product_order_api_key_' . $poak;
			$trans_name_active_resources      = 'wc_am_get_ar_for_product_order_api_key_ar_' . $poak;
			$resources_sql_trans              = get_transient( $trans_name_sql );
			$resources_active_resources_trans = get_transient( $trans_name_active_resources );

			if ( $resources_active_resources_trans !== false ) {
				$resources = $resources_active_resources_trans;
			} else {
				if ( $resources_sql_trans === false ) {
					$sql = "
						SELECT *
						FROM {$wpdb->prefix}" . WC_AM_USER()->get_api_resource_table_name() . "
						WHERE product_order_api_key = %s
						AND product_id = %d
					";

					// Get the API resource order items for this user.
					$resources_sql = $wpdb->get_results( $wpdb->prepare( $sql, $poak, $product_id ) );

					if ( ! empty( $resources_sql ) ) {
						set_transient( $trans_name_sql, $resources_sql, WCAM()->get_db_cache_expires() * MINUTE_IN_SECONDS );
					} else {
						WC_AM_SMART_CACHE()->queue_delete_transient( $trans_name_sql );
					}
				}

				// Only return the active API resources.
				$resources = $this->get_active_resources( ! empty( $resources_sql ) ? $resources_sql : $resources_sql_trans );

				if ( ! empty( $resources ) ) {
					set_transient( $trans_name_active_resources, $resources, WCAM()->get_db_cache_expires() * MINUTE_IN_SECONDS );
				} else {
					WC_AM_SMART_CACHE()->queue_delete_transient( $trans_name_active_resources );
				}
			}

			return $resources;
		}
	}

	/**
	 * Return an array of product IDs for either a Master API Key, or a Product Order API Key.
	 *
	 * @since 2.0
	 *
	 * @param $api_key
	 *
	 * @return array|bool
	 */
	public function get_product_ids_by_api_key( $api_key ) {
		if ( $api_key ) {
			global $wpdb;

			$sql = "
				SELECT product_id
				FROM {$wpdb->prefix}" . WC_AM_USER()->get_api_resource_table_name() . "
				WHERE master_api_key = %s
				OR product_order_api_key = %s
			";

			// Get the API resource order items for this user.
			$product_ids = $wpdb->get_col( $wpdb->prepare( $sql, $api_key, $api_key ) );

			if ( empty( $product_ids ) ) {
				$product_ids = $wpdb->get_col( $wpdb->prepare( "
	                SELECT product_id
	                FROM {$wpdb->prefix}" . WC_AM_USER()->get_api_resource_table_name() . "
	                WHERE api_resource_id = %d
	                LIMIT 1
		        ", WC_AM_ASSOCIATED_API_KEY_DATA_STORE()->get_api_resource_id_by_associated_api_key( $api_key ) ) );
			}

			return $product_ids ? $product_ids : false;
		}

		return false;
	}

	/**
	 * Return all API resource order item rows matching a Master API Key, a Product Order API Key, or an Associated API Key.
	 *
	 * @since 2.0
	 *
	 * @param string     $api_key A Master API Key, a Product Order API Key, or an Associated API Key.
	 * @param string|int $product_id
	 *
	 * @return array|bool
	 * @throws \Exception
	 *
	 */
	public function get_api_resources_for_api_key_by_product_id( $api_key, $product_id ) {
		$ids = array();

		if ( $api_key ) {
			global $wpdb;

			// Get an array of product IDs using a legacy Software Title (string).
			if ( ! is_numeric( $product_id ) && is_string( $product_id ) ) {
				/**
				 * Get an array list of integer product IDs to lookup legacy Software Titles (strings) to use for comparison.
				 */
				$product_ids = $this->get_product_ids_by_api_key( $api_key );

				if ( ! empty( $product_ids ) ) {
					foreach ( $product_ids as $id ) {
						// Compare the string $product_id to the legacy software title to determine the numeric product ID.
						if ( strcmp( $product_id, WC_AM_PRODUCT_DATA_STORE()->get_product_legacy_api_software_title( $id ) ) === 0 ) {
							$ids[] = $id;
						}
					}
				}
			}

			// A product ID integer was passed in. WooCommerce API Manager >= 2.0, and API Manager PHP Library > 1.2.
			if ( is_numeric( $product_id ) ) {
				$sql = "
					SELECT *
					FROM {$wpdb->prefix}" . WC_AM_USER()->get_api_resource_table_name() . "
					WHERE ( master_api_key = %s OR product_order_api_key = %s )
					AND product_id = %d
				";

				// Get the API resource order items for this user.
				$resources = $wpdb->get_results( $wpdb->prepare( $sql, $api_key, $api_key, $product_id ) );

				// Get resources using Associated API Key.
				if ( empty( $resources ) ) {
					$resources = $wpdb->get_results( $wpdb->prepare( "
		                SELECT *
						FROM {$wpdb->prefix}" . WC_AM_USER()->get_api_resource_table_name() . "
						WHERE api_resource_id = %d
						AND product_id = %d
		            ", WC_AM_ASSOCIATED_API_KEY_DATA_STORE()->get_api_resource_id_by_associated_api_key( $api_key ), $product_id ) );
				}

				// Only return the active API resources.
				$resources = $this->get_active_resources( $resources );

				return ! empty( $resources ) ? $resources : false;
			} elseif ( ! empty( $ids ) && is_array( $ids ) ) {
				// A product ID string was passed in. WooCommerce API Manager < 2.0, and API Manager PHP Library <= 1.2.

				// Find the Master API Key resources.
				$resources = array();

				foreach ( $ids as $product_id ) {
					$sql = "
						SELECT *
						FROM {$wpdb->prefix}" . WC_AM_USER()->get_api_resource_table_name() . "
						WHERE master_api_key = %s
					";

					$args = array(
						$api_key,
					);

					$sql    .= " AND product_id = %d";
					$args[] = $product_id;

					// Get the API resource order items for this user.
					$resources = array_merge( $resources, $wpdb->get_results( $wpdb->prepare( $sql, $args ) ) );
				}

				// For some reason, multiple identical product IDs will loop twice each, so we need to remove the duplicates.
				$resources = WC_AM_ARRAY()->array_unique_object( $resources );

				// Only return the active API resources.
				$resources = $this->get_active_resources( $resources );

				if ( $resources ) {
					return $resources;
				}

				// Find the Product Order API Key resources, since no Master API Key resources were found.
				$resources = array();

				foreach ( $ids as $product_id ) {
					$sql = "
						SELECT DISTINCT *
						FROM {$wpdb->prefix}" . WC_AM_USER()->get_api_resource_table_name() . "
						WHERE product_order_api_key = %s
					";

					$args = array(
						$api_key,
					);

					$sql    .= " AND product_id = %d";
					$args[] = $product_id;

					// Get the API resource order items for this user.
					$resources = array_merge( $resources, $wpdb->get_results( $wpdb->prepare( $sql, $args ) ) );
				}

				// Only return the active API resources.
				$resources = $this->get_active_resources( $resources );

				if ( $resources ) {
					return $resources;
				}

				// Find the Associated API Key resources, since no Product Order API Key resources were found.
				$resources = array();

				foreach ( $ids as $product_id ) {
					$sql = "
						SELECT DISTINCT *
						FROM {$wpdb->prefix}" . WC_AM_USER()->get_api_resource_table_name() . "
						WHERE api_resource_id = %d
					";

					$args = array(
						WC_AM_ASSOCIATED_API_KEY_DATA_STORE()->get_api_resource_id_by_associated_api_key( $api_key ),
					);

					$sql    .= " AND product_id = %d";
					$args[] = $product_id;

					// Get the API resource order items for this user.
					$resources = array_merge( $resources, $wpdb->get_results( $wpdb->prepare( $sql, $args ) ) );
				}

				// Only return the active API resources.
				$resources = $this->get_active_resources( $resources );

				if ( $resources ) {
					return $resources;
				}
			}
		}

		return false;
	}

	/**
	 * Return the product order api key.
	 *
	 * @since 2.0
	 *
	 * @param int $order_id
	 * @param int $product_id
	 *
	 * @return bool|null|string
	 */
	public function get_api_resource_product_order_api_key( $order_id, $product_id ) {
		if ( $order_id && $product_id ) {
			global $wpdb;

			$api_key = $wpdb->get_var( $wpdb->prepare( "
				SELECT product_order_api_key
				FROM {$wpdb->prefix}" . WC_AM_USER()->get_api_resource_table_name() . "
				WHERE order_id = %d
				AND product_id = %d
				LIMIT 1
			", $order_id, $product_id ) );

			return $api_key ? (string) $api_key : false;
		}

		return false;
	}

	/**
	 * Return the Parent ID of the Product ID.
	 *
	 * @since 2.0
	 *
	 * @param int $product_id
	 *
	 * @return bool|null|string
	 */
	public function get_api_resource_parent_id( $product_id ) {
		if ( ! empty( $product_id ) ) {
			global $wpdb;

			$parent_id = $wpdb->get_var( $wpdb->prepare( "
				SELECT 		parent_id
				FROM {$wpdb->prefix}" . WC_AM_USER()->get_api_resource_table_name() . "
				WHERE 		product_id = %d
			", $product_id ) );

			return ! empty( $parent_id ) ? (int) $parent_id : false;
		}

		return false;
	}

	/**
	 * Return the API Resource ID.
	 *
	 * @since 2.0
	 *
	 * @param int $order_id
	 * @param int $product_id
	 *
	 * @return bool|null|string
	 */
	public function get_api_resource_id_by_order_id_and_product_id( $order_id, $product_id ) {
		if ( ! empty( $product_id ) ) {
			global $wpdb;

			$api_resource_id = $wpdb->get_var( $wpdb->prepare( "
				SELECT 		api_resource_id
				FROM {$wpdb->prefix}" . WC_AM_USER()->get_api_resource_table_name() . "
				WHERE 		product_id = %d
				AND 		order_id = %d
			", $product_id, $order_id ) );

			return ! empty( $api_resource_id ) ? (int) $api_resource_id : false;
		}

		return false;
	}

	/**
	 * Returns the original array with non-active API resources removed.
	 *
	 * @since 2.0
	 *
	 * @param array $resources Get the resources first, then passed it in.
	 *
	 * @return array
	 * @throws \Exception
	 *
	 */
	public function get_active_resources( $resources ) {
		$active_resources = array();
		$is_wc_sub        = false;

		if ( $resources ) {
			foreach ( $resources as $resource ) {
				/**
				 * Update activations_purchased_total if product is set for Unlimited Activations, then refresh the cache.
				 *
				 * @since 2.2.0
				 */
				$is_sub       = false;
				$is_unlimited = WC_AM_PRODUCT_DATA_STORE()->is_api_product_unlimited_activations( $resource->product_id );
				$instance_id  = WC_AM_API_ACTIVATION_DATA_STORE()->get_instance_id_by_api_resource_id( $resource->api_resource_id );

				if ( $is_unlimited && WCAM()->get_unlimited_activation_limit() > $resource->activations_purchased_total ) {
					if ( ! empty( $resource->sub_item_id ) ) {
						$item_id = $resource->sub_item_id;
						$is_sub  = true;
					} else {
						$item_id = $resource->order_item_id;
					}

					$this->update_activations_purchased_and_activations_purchased_total( $resource->user_id, $resource->product_id, $item_id, $resource->item_qty, WCAM()->get_unlimited_activation_limit(), $is_sub );
					$this->delete_inactive_resource_cache( $resource );
				}

				// If the _api_resource_product_id meta value is missing on the product, add it now.
				WC_AM_PRODUCT_DATA_STORE()->update_missing_api_resource_product_id( $resource->product_id, $resource->parent_id );

				// Delete excess API Key activations by activation resource ID.
				WC_AM_API_ACTIVATION_DATA_STORE()->delete_excess_api_key_activations_by_activation_id( $resource->activation_ids, $resource->activations_purchased_total );

				if ( WCAM()->get_wc_subs_exist() ) {
					$is_wc_sub = WC_AM_SUBSCRIPTION()->is_wc_subscription( $resource->product_id );
				}

				$is_expired = $this->is_access_expired( $resource->access_expires );

				// Delete activations for expired non-Subscription API Keys.
				if ( $is_expired ) {
					WC_AM_API_ACTIVATION_DATA_STORE()->delete_api_key_activation_by_instance_id( $instance_id );
					$this->delete_inactive_resource_cache( $resource );

					continue;
				} elseif ( WCAM()->get_wc_subs_exist() && ! empty( $resource->sub_id ) && $is_wc_sub ) {
					$is_item_on_sub = WC_AM_SUBSCRIPTION()->is_subscription_line_item_on_subscription( $resource->sub_item_id, $resource->sub_id );
					$is_active      = WC_AM_SUBSCRIPTION()->is_subscription_for_order_active( $resource->sub_id );

					// Delete activations for expired Subscription API Keys, or removed line items.
					if ( ! $is_item_on_sub || ! $is_active ) {
						WC_AM_API_ACTIVATION_DATA_STORE()->delete_api_key_activation_by_instance_id( $instance_id );
						$this->delete_inactive_resource_cache( $resource );
					} elseif ( $is_item_on_sub && $is_active ) {
						$active_resources[] = $resource;
					}
				} elseif ( ! $is_expired ) {
					$active_resources[] = $resource;
				}
			}
		}

		return ! empty( $active_resources ) ? $active_resources : array();
	}

	/**
	 * Returns the original array with non-active API resources removed,
	 * and only resources that match the product ID (integer) provided.
	 *
	 * @since 2.0
	 *
	 * @param array $resources Get the resources first, then passed it in.
	 * @param int   $product_id
	 *
	 * @return array|bool
	 * @throws \Exception
	 *
	 */
	public function get_active_api_resources_by_product_id( $resources, $product_id ) {
		$active_resources = array();
		$is_wc_sub        = false;

		if ( $resources && $product_id ) {
			foreach ( $resources as $resource ) {
				if ( $product_id == $resource->product_id ) {
					/**
					 * Update activations_purchased_total if product is set for Unlimited Activations, then refresh the cache.
					 *
					 * @since 2.2.0
					 */
					$is_sub       = false;
					$is_unlimited = WC_AM_PRODUCT_DATA_STORE()->is_api_product_unlimited_activations( $resource->product_id );
					$instance_id  = WC_AM_API_ACTIVATION_DATA_STORE()->get_instance_id_by_api_resource_id( $resource->api_resource_id );

					if ( $is_unlimited && WCAM()->get_unlimited_activation_limit() > $resource->activations_purchased_total ) {
						if ( ! empty( $resource->sub_item_id ) ) {
							$item_id = $resource->sub_item_id;
							$is_sub  = true;
						} else {
							$item_id = $resource->order_item_id;
						}

						$this->update_activations_purchased_and_activations_purchased_total( $resource->user_id, $resource->product_id, $item_id, $resource->item_qty, WCAM()->get_unlimited_activation_limit(), $is_sub );
						$this->delete_inactive_resource_cache( $resource );
					}

					// If the _api_resource_product_id meta value is missing on the product, add it now.
					WC_AM_PRODUCT_DATA_STORE()->update_missing_api_resource_product_id( $resource->product_id, $resource->parent_id );

					// Delete excess API Key activations by activation resource ID.
					WC_AM_API_ACTIVATION_DATA_STORE()->delete_excess_api_key_activations_by_activation_id( $resource->activation_ids, $resource->activations_purchased_total );

					if ( WCAM()->get_wc_subs_exist() ) {
						$is_wc_sub = WC_AM_SUBSCRIPTION()->is_wc_subscription( $resource->product_id );
					}

					$is_expired = $this->is_access_expired( $resource->access_expires );

					if ( $is_expired ) {
						WC_AM_API_ACTIVATION_DATA_STORE()->delete_api_key_activation_by_instance_id( $instance_id );
						$this->delete_inactive_resource_cache( $resource );

						continue;
					} elseif ( WCAM()->get_wc_subs_exist() && ! empty( $resource->sub_id ) && $is_wc_sub ) {
						$is_item_on_sub = WC_AM_SUBSCRIPTION()->is_subscription_line_item_on_subscription( $resource->sub_item_id, $resource->sub_id );
						$is_active      = WC_AM_SUBSCRIPTION()->is_subscription_for_order_active( $resource->sub_id );

						// Delete activations for expired Subscription API Keys, or removed line items.
						if ( ! $is_item_on_sub || ! $is_active ) {
							WC_AM_API_ACTIVATION_DATA_STORE()->delete_api_key_activation_by_instance_id( $instance_id );
							$this->delete_inactive_resource_cache( $resource );
						} elseif ( $is_item_on_sub && $is_active ) {
							$active_resources[] = $resource;
						}
					} elseif ( ! $is_expired ) {
						$active_resources[] = $resource;
					}
				}
			}
		}

		return ! empty( $active_resources ) ? $active_resources : false;
	}

	/**
	 * Returns the original array with non-active API resources removed,
	 * and only resources that match the product ID (integer) provided.
	 *
	 * @since 2.0
	 *
	 * @param int $user_id
	 * @param int $product_id
	 *
	 * @return array|bool
	 * @throws \Exception
	 *
	 */
	public function get_active_api_resources_for_user_id_by_product_id_int( $user_id, $product_id ) {
		$resources = $this->get_api_resources_for_user_id( $user_id );

		return $resources ? $this->get_active_api_resources_by_product_id( $resources, $product_id ) : false;
	}

	/**
	 * Returns an array of active API resources.
	 *
	 * @since 2.0
	 *
	 * @param string     $api_key    Master API Key, or a Product Order API Key.
	 * @param string|int $product_id String (Title), or an integer.
	 *
	 * @return array|bool
	 * @throws \Exception
	 *
	 */
	public function get_active_api_resources( $api_key, $product_id ) {
		$resources = $this->get_api_resources_for_api_key_by_product_id( $api_key, $product_id );

		if ( ! is_numeric( $product_id ) && is_string( $product_id ) ) {
			return $resources;
		}

		return $resources ? $this->get_active_api_resources_by_product_id( $resources, $product_id ) : false;
	}

	/**
	 * Return the total number of activations for a product assigned to an API Key.
	 *
	 * @since 2.0
	 *
	 * @param string     $api_key    Master API Key, or a Product Order API Key.
	 * @param string|int $product_id String (Title), or an integer.
	 * @param int        $user_id
	 *
	 * @return int|null|string
	 * @throws \Exception
	 *
	 */
	public function get_total_activations_for_product_by_api_key( $api_key, $product_id, $user_id = 0 ) {
		$total_activations = 0;
		$user_id           = $user_id ? $user_id : WC_AM_USER()->get_user_id_by_api_key( $api_key );
		$resources         = $this->get_active_api_resources( $api_key, $product_id );

		if ( $resources ) {
			global $wpdb;

			$product_id_list = wp_list_pluck( $resources, 'product_id' );

			foreach ( $product_id_list as $pid ) {
				$sql = "
					SELECT activations_purchased_total
					FROM {$wpdb->prefix}" . WC_AM_USER()->get_api_resource_table_name() . "
					WHERE user_id = %s
				";

				$args = array(
					$user_id,
				);

				$sql    .= " AND product_id = %d";
				$args[] = $pid;

				$total_activations = $total_activations + $wpdb->get_var( $wpdb->prepare( $sql, $args ) );
			}
		}

		return $total_activations ? $total_activations : 0;
	}

	/**
	 * Returns an array of product IDs the user has available as API resources.
	 *
	 * @since 2.0
	 *
	 * @param int $user_id
	 *
	 * @return array|bool
	 */
	public function get_user_products( $user_id ) {
		global $wpdb;

		$sql = "
			SELECT DISTINCT product_id
			FROM {$wpdb->prefix}" . WC_AM_USER()->get_api_resource_table_name() . "
			WHERE user_id = %d
		";

		$products = $wpdb->get_col( $wpdb->prepare( $sql, $user_id ) );

		return $products ? $products : false;
	}

	/**
	 * Get the number of activations for a single product item on a single order
	 * before refunds, and quantity changes have been calculated.
	 *
	 * @since 2.0
	 *
	 * @param int $order_id
	 * @param int $product_id
	 *
	 * @return int|null|string
	 */
	public function get_per_product_activations( $order_id, $product_id ) {
		global $wpdb;

		$sql = "
			SELECT activations_purchased
			FROM {$wpdb->prefix}" . WC_AM_USER()->get_api_resource_table_name() . "
			WHERE order_id = %d
			AND product_id = %d
		";

		$resources = $wpdb->get_var( $wpdb->prepare( $sql, $order_id, $product_id ) );

		return $resources ? $resources : 0;
	}

	/**
	 * Return the total number of activations purchased for a product.
	 *
	 * @since 2.0
	 *
	 * @param array $resources An array of resources containing a specific product.
	 *
	 * @return int The number of of activations for a product, before those activations have been activated.
	 */
	public function get_total_activations_purchased( $resources ) {
		$total_activations = (int) array_sum( wp_list_pluck( $resources, 'activations_purchased_total' ) );

		return $total_activations ? $total_activations : 0;
	}

	/**
	 * Return the total number of active activations for a product.
	 *
	 * @since 2.0
	 *
	 * @param array $resources An array of resources containing a specific product.
	 *
	 * @return int The number of of activations for a product, before those activations have been activated.
	 */
	public function get_total_activations( $resources ) {
		$total_activations = (int) array_sum( wp_list_pluck( $resources, 'activations_total' ) );

		return $total_activations ? $total_activations : 0;
	}

	/**
	 * Return a resource row as an object of data.
	 *
	 * @since 2.0
	 *
	 * @param int $api_resource_id
	 *
	 * @return null|object
	 */
	public function get_resources_by_api_resource_id( $api_resource_id ) {
		global $wpdb;

		return $wpdb->get_row( $wpdb->prepare( "
			SELECT *
			FROM {$wpdb->prefix}" . WC_AM_USER()->get_api_resource_table_name() . "
			WHERE api_resource_id = %d
		", $api_resource_id ) );
	}

	/**
	 * Return total number of API Resources.
	 *
	 * @since 2.1
	 *
	 * @return int|string|null
	 */
	public function get_api_resource_count() {
		global $wpdb;

		$api_resource_count = $wpdb->get_var( "
			SELECT COUNT(api_resource_id)
			FROM {$wpdb->prefix}" . WC_AM_USER()->get_api_resource_table_name() . "
		" );

		return ! empty( $api_resource_count ) ? $api_resource_count : 0;
	}

	/**
	 * Return array of Associated API Key IDs.
	 *
	 * @since 2.0
	 *
	 * @param int $api_resource_id
	 *
	 * @return array|mixed|object
	 */
	public function get_associated_api_key_ids_by_api_resource_id( $api_resource_id ) {
		global $wpdb;

		$associated_api_key_ids = $wpdb->get_var( $wpdb->prepare( "
			SELECT associated_api_key_ids
			FROM {$wpdb->prefix}" . WC_AM_USER()->get_api_resource_table_name() . "
			WHERE api_resource_id = %d
		", $api_resource_id ) );

		return ! empty( $associated_api_key_ids ) ? json_decode( $associated_api_key_ids, true ) : array();
	}

	/**
	 * Get the numeric Product ID from the database and return it.
	 *
	 * @since 2.0
	 *
	 * @param int    $product_id
	 * @param string $api_key
	 *
	 * @return bool|int
	 */
	public function get_api_information_and_update_product_id( $product_id, $api_key = '' ) {
		global $wpdb;

		$pid = 0;

		// If Product ID is numeric for >= 2.0
		if ( is_numeric( $product_id ) ) {
			$sql = "
				SELECT product_id
				FROM {$wpdb->prefix}" . WC_AM_USER()->get_api_resource_table_name() . "
				WHERE product_id = %d
				LIMIT 1
			";

			$pid = $wpdb->get_var( $wpdb->prepare( $sql, $product_id ) );

			if ( empty( $pid ) ) {
				$sql = "
					SELECT ID
					FROM $wpdb->posts
					WHERE ID = %d
					AND ( post_type = %s OR post_type = %s )
					LIMIT 1
				";

				$pid = $wpdb->get_var( $wpdb->prepare( $sql, $product_id, 'product', 'product_variation' ) );
			}
		}

		/**
		 * After the first two queries, the odds of finding the correct Product ID begin to fade, if it is
		 *  a Variable product with all variations using the same Software Title as the product_id.
		 */
		if ( empty( $pid ) && is_string( $product_id ) && ! empty( $api_key ) ) {// If legacy Product ID (Software Title) is a string for WC AM < 2.0.

			// Search using Associated API Key.
			$sql = "
				SELECT product_id
				FROM {$wpdb->prefix}" . WC_AM_USER()->get_associated_api_key_table_name() . "
				WHERE associated_api_key = %s
				LIMIT 1
			";

			$pid = $wpdb->get_var( $wpdb->prepare( $sql, $api_key ) );

			if ( empty( $pid ) ) {
				// Search using API Key, which could be any type in the Activation Table.
				$sql = "
					SELECT assigned_product_id
					FROM {$wpdb->prefix}" . WC_AM_USER()->get_api_activation_table_name() . "
					WHERE product_id = %s
					AND api_key = %s
					LIMIT 1
				";

				$pid = $wpdb->get_var( $wpdb->prepare( $sql, $product_id, $api_key ) );

				if ( empty( $pid ) ) {
					// Search using Master API Key or Product Order API Key.
					$sql = "
						SELECT product_id
						FROM {$wpdb->prefix}" . WC_AM_USER()->get_api_resource_table_name() . "
						WHERE product_title = %s
						AND ( product_order_api_key = %s OR master_api_key = %s )
						LIMIT 1
					";

					$pid = $wpdb->get_var( $wpdb->prepare( $sql, $product_id, $api_key, $api_key ) );

					// Search using Product ID as the Product Title.
					if ( empty( $pid ) ) {
						// Search using Product Title in API Resource table.
						$sql = "
							SELECT product_id
							FROM {$wpdb->prefix}" . WC_AM_USER()->get_api_resource_table_name() . "
							WHERE product_title = %s
							LIMIT 1
						";

						$pid = $wpdb->get_var( $wpdb->prepare( $sql, $product_id ) );

						if ( empty( $pid ) ) {
							// Search using API Resource Title in order meta.
							$sql = "
								SELECT post_id
								FROM {$wpdb->prefix}" . 'postmeta' . "
								WHERE meta_key = %s
								AND meta_value = %s
								LIMIT 1
							";

							$pid = $wpdb->get_var( $wpdb->prepare( $sql, '_api_resource_title', $product_id ) );
						}
					}
				}
			}
		}

		return ! empty( $pid ) ? (int) $pid : false;
	}

	/**
	 * Get the product data row from the resource table using the product ID.
	 *
	 * @since 2.0
	 *
	 * @param string|int $product_id
	 *
	 * @return bool|null|string
	 */
	public function get_row_data_by_product_id( $product_id ) {
		global $wpdb;

		$sql = "
			SELECT *
			FROM {$wpdb->prefix}" . WC_AM_USER()->get_api_resource_table_name() . "
			WHERE product_id = %s
			LIMIT 1
		";

		$row = $wpdb->get_row( $wpdb->prepare( $sql, $product_id ) );

		return ! empty( $row ) ? $row : false;
	}

	/**
	 * Get the product data row from the resource table using the API Key.
	 *
	 * @since 2.0
	 *
	 * @param string|int $product_id
	 * @param string     $api_key
	 *
	 * @return bool|null|string
	 */
	public function get_row_data_by_api_key( $product_id, $api_key ) {
		global $wpdb;

		// Get data using the Master API Key, or the Product Order API Key.
		$sql = "
			SELECT *
			FROM {$wpdb->prefix}" . WC_AM_USER()->get_api_resource_table_name() . "
			WHERE product_id = %d
			AND ( product_order_api_key = %s OR master_api_key = %s )
			AND ( access_expires = %d OR access_expires > %d )
			LIMIT 1
		";

		$row = $wpdb->get_row( $wpdb->prepare( $sql, $product_id, $api_key, $api_key, 0, WC_AM_ORDER_DATA_STORE()->get_current_time_stamp() ) );

		// Get data using Associated API Key.
		if ( empty( $row ) ) {
			$row = $wpdb->get_row( $wpdb->prepare( "
				SELECT *
				FROM {$wpdb->prefix}" . WC_AM_USER()->get_api_resource_table_name() . "
				WHERE api_resource_id = %d
				AND product_id = %d
				AND ( access_expires = %d OR access_expires > %d )
			", WC_AM_ASSOCIATED_API_KEY_DATA_STORE()->get_api_resource_id_by_associated_api_key( $api_key ), $product_id, 0, WC_AM_ORDER_DATA_STORE()->get_current_time_stamp() ) );
		}

		return ! empty( $row ) ? $row : false;
	}

	/**
	 * Get the user_id using the order_id.
	 *
	 * @since 2.0
	 *
	 * @param $order_id
	 *
	 * @return bool
	 */
	public function get_user_id_by_order_id( $order_id ) {
		$order = WC_AM_ORDER_DATA_STORE()->get_order_object( $order_id );

		if ( is_object( $order ) ) {
			$user_id = WC_AM_ORDER_DATA_STORE()->get_customer_id( $order );

			if ( ! empty( $user_id ) ) {
				return $user_id;
			} else {
				global $wpdb;

				$sql = "
	            SELECT user_id
	            FROM {$wpdb->prefix}" . WC_AM_USER()->get_api_resource_table_name() . "
	            WHERE order_id = %d
	            LIMIT 1
            ";

				$user_id = $wpdb->get_var( $wpdb->prepare( $sql, $order_id ) );
			}

			return ! empty( $user_id ) ? $user_id : false;
		}

		return false;
	}

	/**
	 * Get all API Resource Order IDs.
	 *
	 * @since 2.1
	 *
	 * @return array|bool
	 */
	public function get_all_order_ids() {
		global $wpdb;

		$order_ids = $wpdb->get_col( "
			SELECT order_id
			FROM {$wpdb->prefix}" . WC_AM_USER()->get_api_resource_table_name() . "
		" );

		return ! empty( $order_ids ) ? $order_ids : false;
	}

	/**
	 * Return order_id.
	 *
	 * @since 2.1
	 *
	 * @param int $order_item_id
	 *
	 * @return bool|string|null
	 */
	public function get_order_id_by_order_item_id( $order_item_id ) {
		global $wpdb;

		$order_id = $wpdb->get_var( $wpdb->prepare( "
			SELECT order_id
			FROM {$wpdb->prefix}" . WC_AM_USER()->get_api_resource_table_name() . "
			WHERE order_item_id = %d
		", $order_item_id ) );

		return ! empty( $order_id ) ? $order_id : false;
	}

	/**
	 * Return order_id.
	 *
	 * @since 2.1
	 *
	 * @param int $sub_item_id
	 *
	 * @return bool|string|null
	 */
	public function get_order_id_by_sub_item_id( $sub_item_id ) {
		global $wpdb;

		$order_id = $wpdb->get_var( $wpdb->prepare( "
			SELECT order_id
			FROM {$wpdb->prefix}" . WC_AM_USER()->get_api_resource_table_name() . "
			WHERE sub_item_id = %d
		", $sub_item_id ) );

		return ! empty( $order_id ) ? $order_id : false;
	}

	/**
	 * Return order_id.
	 *
	 * @since 2.2.8
	 *
	 * @param int $sub_id
	 *
	 * @return bool|string|null
	 */
	public function get_order_id_by_sub_id( $sub_id ) {
		global $wpdb;

		$order_id = $wpdb->get_var( $wpdb->prepare( "
			SELECT order_id
			FROM {$wpdb->prefix}" . WC_AM_USER()->get_api_resource_table_name() . "
			WHERE sub_id = %d
		", $sub_id ) );

		return ! empty( $order_id ) ? $order_id : false;
	}

	/**
	 * Returns true if the resource already has a matching order ID.
	 *
	 * @since 2.0
	 *
	 * @param int $order_id
	 * @param int $product_id
	 *
	 * @return bool
	 */
	public function has_order( $order_id, $product_id ) {
		global $wpdb;

		$sql = "
			SELECT *
			FROM {$wpdb->prefix}" . WC_AM_USER()->get_api_resource_table_name() . "
			WHERE order_id = %d
			AND product_id = %d
		";

		$args = array(
			$order_id,
			$product_id
		);

		$result = $wpdb->get_row( $wpdb->prepare( $sql, $args ) );

		return ! empty( $result ) ? true : false;
	}

	/**
	 * Check if the API resource table is empty.
	 *
	 * @since 2.0.5
	 *
	 * @return array|bool|object|null
	 */
	public function is_api_resource_table_empty() {
		global $wpdb;

		$sql = "
			SELECT *
			FROM {$wpdb->prefix}" . WC_AM_USER()->get_api_resource_table_name() . "
			LIMIT 1
		";

		// Get the API resource order items for this user.
		$resources = $wpdb->get_results( $sql );

		return ! empty( $resources ) ? $resources : false;
	}

	/**
	 * Returns true if $access_expires has a timestamp > 0.
	 *
	 * @since 2.0
	 *
	 * @param int $access_expires
	 *
	 * @return bool
	 */
	public function is_access_expires_set( $access_expires ) {
		return ! empty( $access_expires ) && (int) $access_expires > 0 ? true : false;
	}

	/**
	 * Returns true if the access_expires time stamp has expired ($access_expires < current_time).
	 *
	 * @since 2.0
	 *
	 * @param $access_expires
	 *
	 * @return bool
	 */
	public function is_access_expired( $access_expires ) {
		return $this->is_access_expires_set( $access_expires ) ? WC_AM_ORDER_DATA_STORE()->is_time_expired( $access_expires ) : false;
	}

	/**
	 * Delete Activation IDs for Associated API Key.
	 *
	 * @since 2.0
	 *
	 * @param int $api_resource_id
	 * @param int $activation_id
	 *
	 * @return bool
	 */
	public function delete_api_resource_id_activation_ids( $api_resource_id, $activation_id ) {
		global $wpdb;

		$activation_ids = WC_AM_API_ACTIVATION_DATA_STORE()->get_activation_ids_by_api_resource_id( $api_resource_id );

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
			'api_resource_id' => $api_resource_id
		);

		$data_format = array(
			'%s'
		);

		$where_format = array(
			'%d'
		);

		$update = $wpdb->update( $wpdb->prefix . WC_AM_USER()->get_api_resource_table_name(), $data, $where, $data_format, $where_format );

		return ! empty( $update );
	}

	/**
	 * Deletes cache for inactive resources.
	 *
	 * @since 2.2.8
	 *
	 * @param object $resource
	 */
	public function delete_inactive_resource_cache( $resource ) {
		WC_AM_SMART_CACHE()->delete_cache( array(
			                                   'admin_resources' => array(
				                                   'order_id'      => $resource->order_id,
				                                   'sub_parent_id' => ! empty( $resource->sub_parent_id ) ? $resource->sub_parent_id : $resource->order_id,
				                                   'api_key'       => $resource->master_api_key,
				                                   'product_id'    => $resource->product_id,
				                                   'user_id'       => $resource->user_id
			                                   )
		                                   ), true );
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

			$wpdb->update( $wpdb->prefix . WC_AM_USER()->get_api_resource_table_name(), $data, $where, $data_format, $where_format );
		}
	}

	/**
	 * Update activations_purchased_total to match unlimited_activation_limit value.
	 *
	 * @since 2.2.0
	 *
	 * @param int  $user_id
	 * @param int  $product_id
	 * @param int  $item_id
	 * @param int  $item_qty
	 * @param int  $unlimited_activation_limit
	 * @param bool $is_sub
	 */
	public function update_activations_purchased_and_activations_purchased_total( $user_id, $product_id, $item_id, $item_qty, $unlimited_activation_limit, $is_sub ) {
		global $wpdb;

		$item_type = $is_sub ? 'sub_item_id' : 'order_item_id';

		$data = array(
			'activations_purchased'       => (int) $unlimited_activation_limit,
			'activations_purchased_total' => (int) $unlimited_activation_limit * (int) $item_qty
		);

		$where = array(
			'user_id'    => (int) $user_id,
			'product_id' => (int) $product_id,
			$item_type   => (int) $item_id
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

		$wpdb->update( $wpdb->prefix . WC_AM_USER()->get_api_resource_table_name(), $data, $where, $data_format, $where_format );
	}
}