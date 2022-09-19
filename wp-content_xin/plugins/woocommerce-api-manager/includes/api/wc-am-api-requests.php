<?php

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * WooCommerce API Manager API Requests Class
 *
 * @since       2.0
 *
 * @author      Todd Lahman LLC
 * @copyright   Copyright (c) Todd Lahman LLC
 * @package     WooCommerce API Manager/API Requests
 */
class WC_AM_API_Requests {

	private $request            = array();
	private $user_id            = 0;
	private $resources          = null;
	private $send_response_data = false;
	private $debug_log          = false;
	private $error_log          = false;
	private $response_log       = false;
	private $time_start         = 0;
	private $trusted_sources    = array();

	/**
	 * @var null
	 */
	private static $_instance = null;

	/**
	 * @static
	 *
	 * @param array $request
	 *
	 * @return null|\WC_AM_API_Requests
	 */
	public static function instance( $request ) {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self( $request );
		}

		return self::$_instance;
	}

	/**
	 * WC_AM_API_Requests constructor.
	 *
	 * @since 2.0
	 *
	 * @param array $request
	 */
	private function __construct( $request ) {
		// Set start time for API call.
		$this->time_start = microtime( true );

		if ( defined( 'WC_AM_TRUSTED_SOURCES' ) ) {
			$this->trusted_sources = WC_AM_TRUSTED_SOURCES;
		}

		if ( get_option( 'woocommerce_api_manager_api_response_data' ) == 'yes' ) {
			$this->send_response_data = true;
		}

		if ( get_option( 'woocommerce_api_manager_api_debug_log' ) == 'yes' ) {
			$this->debug_log = true;
		}

		if ( get_option( 'woocommerce_api_manager_api_error_log' ) == 'yes' ) {
			$this->error_log = true;
		}

		if ( get_option( 'woocommerce_api_manager_api_response_log' ) == 'yes' ) {
			$this->response_log = true;
		}

		/**
		 * $request[ 'request' ] is for legacy backward compatibility pre 2.1.
		 *
		 * @since 2.1
		 */
		if ( ! empty( $request[ 'wc_am_action' ] ) || ! empty( $request[ 'request' ] ) ) {
			// Get the real IP address sent, not just the IP address sent in the request query.
			$request[ 'user_ip' ] = $this->get_ip_address();
			$this->request        = wc_clean( $request );

			// Translate old keys to new keys.
			$this->translate_keys();

			/**
			 * Validate trusted IP address sources if set.
			 */
			if ( ! empty( $this->trusted_sources ) ) {
				if ( empty( $this->request[ 'ip_address' ] ) || ! $this->is_ip_address( $this->request[ 'ip_address' ] ) ) {
					if ( $this->error_log ) {
						WC_AM_Log()->api_error_log( PHP_EOL . esc_html__( 'Error in __construct() method. Error message is "Error code 100. The source did not send a required IP address."', 'woocommerce-api-manager' ) . PHP_EOL . wc_print_r( $this->request, true ) );
					}

					$this->error_response( '100', esc_html__( 'The source did not send a required IP address.', 'woocommerce-api-manager' ) );
				}

				$found = false;

				foreach ( $this->trusted_sources as $k => $source ) {
					if ( $this->is_ip_address( $source ) ) {
						if ( $source == $this->request[ 'ip_address' ] && $source == $this->request[ 'user_ip' ] ) {
							$found = true;
						} else {
							WC_AM_Log()->api_error_log( PHP_EOL . esc_html__( 'Error in __construct() method. Error message is "Error code 100. The source did not send a required IP address."', 'woocommerce-api-manager' ) . PHP_EOL . wc_print_r( 'The request IP address sent in the request query was ' . $this->request[ 'ip_address' ] . '. The real IP Address from the request was ' . $this->request[ 'user_ip' ] . '.', true ) );
						}
					} else {
						if ( $this->error_log ) {
							WC_AM_Log()->api_error_log( PHP_EOL . esc_html__( 'Error in __construct() method. Error message is "Error code 100. The trusted source list does not contain a valid IP address."', 'woocommerce-api-manager' ) . PHP_EOL . wc_print_r( $this->request, true ) );
						}
					}
				}

				if ( ! $found ) {
					if ( $this->error_log ) {
						WC_AM_Log()->api_error_log( PHP_EOL . esc_html__( 'Error in __construct() method. Error message is "Error code 100. The source is not trusted."', 'woocommerce-api-manager' ) . PHP_EOL . wc_print_r( $this->request, true ) );
					}

					$this->error_response( '100', esc_html__( 'The source is not trusted.', 'woocommerce-api-manager' ) );
				}
			}

			$this->route_request();
		} else {
			if ( $this->error_log ) {
				WC_AM_Log()->api_error_log( PHP_EOL . esc_html__( 'Error in __construct() method. Error message is "Error code 100. No request value received."', 'woocommerce-api-manager' ) . PHP_EOL . wc_print_r( $this->request, true ) );
			}

			$this->error_response( '100', esc_html__( 'No request value received.', 'woocommerce-api-manager' ) );
		}
	}

	/**
	 * Migrate old keys to new keys, flatten array.
	 *
	 * @since 2.0
	 */
	private function translate_keys() {
		foreach ( $this->request as $key => $value ) {
			if ( $key == 'wc-api' ) {
				continue;
			}

			// Excess garbage
			if ( $key == 'woocommerce-login-nonce' ) {
				unset( $this->request[ $key ] );
			}

			// Excess garbage
			if ( $key == '_wpnonce' ) {
				unset( $this->request[ $key ] );
			}

			// Excess garbage
			if ( $key == 'woocommerce-reset-password-nonce' ) {
				unset( $this->request[ $key ] );
			}

			if ( $value == 'activation' ) {
				unset( $this->request[ $key ] );
				$this->request[ $key ] = 'activate';
			}

			if ( $value == 'deactivation' ) {
				unset( $this->request[ $key ] );
				$this->request[ $key ] = 'deactivate';
			}

			if ( $key == 'software_id' ) {
				unset( $this->request[ $key ] );
				$this->request[ 'product_id' ] = ! empty( $value ) ? $value : '';
			}

			if ( $key == 'software_version' ) {
				unset( $this->request[ $key ] );
				$this->request[ 'version' ] = ! empty( $value ) ? $value : '';
			}

			if ( $key == 'licence_key' ) {
				unset( $this->request[ $key ] );
				$this->request[ 'api_key' ] = ! empty( $value ) ? $value : '';
			}

			if ( $key == 'license_key' ) {
				unset( $this->request[ $key ] );
				$this->request[ 'api_key' ] = ! empty( $value ) ? $value : '';
			}

			if ( $key == 'platform' ) {
				unset( $this->request[ $key ] );
				$this->request[ 'object' ] = ! empty( $value ) ? $value : '';
			}

			/**
			 * Changed 'request' to 'wc_am_action' to avoid some security solutions from blocking API 'request' queries.
			 *
			 * @since 2.1
			 */
			if ( $key == 'request' ) {
				$this->request[ 'wc_am_action' ] = $this->request[ $key ];
				unset( $this->request[ $key ] );
			}
		}
	}

	/**
	 * Routes the API request to a method or returns an error.
	 *
	 * @since 2.0
	 */
	private function route_request() {
		switch ( $this->request[ 'wc_am_action' ] ) {
			case 'activate':
				$this->activate();
				break;
			case 'deactivate':
				$this->deactivate();
				break;
			case 'status':
				$this->status();
				break;
			case 'information':
				$this->information( 'json' );
				break;
			case 'plugininformation':
				$this->information( 'serialize' );
				break;
			case 'update':
				$this->update( 'json' );
				break;
			case 'pluginupdatecheck':
				$this->update( 'serialize' );
				break;
			default:
				if ( $this->error_log ) {
					WC_AM_Log()->api_error_log( PHP_EOL . esc_html__( 'Error in route_request() method. Error message is "Error code 100. Request value does not exist."', 'woocommerce-api-manager' ) . PHP_EOL . wc_print_r( $this->request, true ) );
				}

				$this->error_response( '100', esc_html__( 'Request value does not exist.', 'woocommerce-api-manager' ) );
				break;
		}
	}

	/**
	 * Verify the user exists, and has an active Master API Key.
	 *
	 * @since 2.0
	 */
	private function verify_user() {
		if ( empty( $this->request[ 'api_key' ] ) ) {
			if ( $this->error_log ) {
				WC_AM_Log()->api_error_log( PHP_EOL . esc_html__( 'Error in verify_user() method. Error message is "Error code 100. An empty API Key was sent."', 'woocommerce-api-manager' ) . PHP_EOL . wc_print_r( $this->request, true ) );
			}

			$this->error_response( '100', esc_html__( 'An empty API Key was sent.', 'woocommerce-api-manager' ) );
		}

		$user_id = WC_AM_USER()->get_user_id_by_api_key( $this->request[ 'api_key' ] );

		$user_object = WC_AM_USER()->get_user_data_by_user_id( $user_id );

		if ( ! $user_object ) {
			if ( $this->error_log ) {
				WC_AM_Log()->api_error_log( PHP_EOL . esc_html__( 'Error in verify_user() method. Error message is "Error code 100. A customer account does not exist for this API Key."', 'woocommerce-api-manager' ) . PHP_EOL . wc_print_r( $this->request, true ) );
			}

			$this->error_response( '100', esc_html__( 'A customer account does not exist for this API Key.', 'woocommerce-api-manager' ) );
		}

		$this->user_id = $user_id;

		$master_api_key_status = WC_AM_USER()->has_api_access( $this->user_id );

		if ( ! $master_api_key_status ) {
			$mak = WC_AM_USER()->get_master_api_key( $this->user_id );

			if ( empty( $mak ) ) {
				/**
				 * Every customer must have a Master API Key, and it is missing, so create it now.
				 */
				WC_AM_USER()->set_registration_master_key_and_status( $this->user_id );

				if ( $this->error_log ) {
					WC_AM_Log()->api_error_log( PHP_EOL . esc_html__( 'Error in verify_user() method. Error message is "Error code 100. A Master API Key did not exist for this customer, and all customers are required to have a Master API Key, so a Master API Key has been created."', 'woocommerce-api-manager' ) . PHP_EOL . wc_print_r( array( 'User ID' => $this->user_id ), true ) );
				}

				$this->error_response( '100', esc_html__( 'A Master API Key did not exist for this account, but is required, so a A Master API Key was created. Please try your request again, or contact support.', 'woocommerce-api-manager' ) );
			} else {
				if ( $this->error_log ) {
					WC_AM_Log()->api_error_log( PHP_EOL . esc_html__( 'Error in verify_user() method. Error message is "Error code 100. The API access for this API Key has been disabled, or an API Key was sent that does not exist on this store."', 'woocommerce-api-manager' ) . PHP_EOL . wc_print_r( $this->request, true ) );
				}

				$this->error_response( '100', esc_html__( 'The API access for this API Key has been disabled, or an API Key was sent that does not exist on this store.', 'woocommerce-api-manager' ) );
			}
		}

		if ( $this->debug_log ) {
			WC_AM_Log()->api_debug_log( PHP_EOL . esc_html__( 'Details from verify_user() method.', 'woocommerce-api-manager' ) . PHP_EOL . wc_print_r( $this->request, true ) . PHP_EOL . 'User ID' . PHP_EOL . wc_print_r( $this->user_id, true ) );
		}
	}

	/**
	 * Verfies that each required key and value are not empty, otherwise an error is returned to the client.
	 *
	 * @since 2.0
	 *
	 * @param array $required_fields
	 */
	private function required( $required_fields ) {
		$missing = array();

		foreach ( $required_fields as $required_field ) {
			if ( empty( $this->request[ $required_field ] ) ) {
				$missing[] = $required_field;
			}
		}

		if ( $missing ) {
			if ( $this->error_log ) {
				WC_AM_Log()->api_error_log( PHP_EOL . esc_html__( 'Error in required() method. Error message is "Error code 100. The following required query string data is missing:"', 'woocommerce-api-manager' ) . PHP_EOL . wc_print_r( join( ', ', $missing ), true ) );
			}

			$this->error_response( '100', esc_html__( 'The following required query string data is missing', 'woocommerce-api-manager' ) . ': ' . join( ', ', $missing ) );
		}
	}

	/**
	 * Gets the API resources, or sends an error response.
	 *
	 * @since 2.0
	 */
	private function get_resources() {
		try {
			$resources = WC_AM_API_RESOURCE_DATA_STORE()->get_active_api_resources( $this->request[ 'api_key' ], $this->request[ 'product_id' ] );

			if ( empty( $resources ) ) {
				if ( $this->error_log ) {
					WC_AM_Log()->api_error_log( PHP_EOL . esc_html__( 'Error in get_resources() method. Error message is "Error code 100. No API resources exist. Verify there are activations remaining, and the API Key and Product ID are correct."', 'woocommerce-api-manager' ) . PHP_EOL . wc_print_r( $this->request, true ) );
				}

				$this->error_response( '100', sprintf( __( 'No API resources exist. Login to %sMy Account%s to verify there are activations remaining, and the API Key and Product ID are correct.', 'woocommerce-api-manager' ), '<a href="' . esc_url( WC_AM_API_ACTIVATION_DATA_STORE()->get_api_keys_url() ) . '" target="blank">', '</a>' ) );
			}

			if ( $this->debug_log ) {
				WC_AM_Log()->api_debug_log( PHP_EOL . esc_html__( 'Details from get_resources() method.', 'woocommerce-api-manager' ) . PHP_EOL . wc_print_r( $resources, true ) );
			}

			$this->resources = $resources;
		} catch ( Exception $exception ) {
			$this->error_response( '100', esc_html__( 'There was an error getting API resources', 'woocommerce-api-manager' ) . ': ' . $exception );

			if ( $this->debug_log ) {
				WC_AM_Log()->api_error_log( PHP_EOL . esc_html__( 'Details from get_resources() method.', 'woocommerce-api-manager' ) . PHP_EOL . $exception );
			}
		}
	}

	/**
	 * Activates an API Key to make resources available.
	 *
	 * @since 2.0
	 */
	private function activate() {
		$data           = array();
		$top_level_data = array();

		$this->required( array( 'api_key', 'product_id', 'instance' ) );
		// Verify the user account exists before proceeding.
		$this->verify_user();
		// Get the API resources for this customer to verify there are resources available.
		$this->get_resources();

		/**
		 * Delete cache.
		 *
		 * @since 2.0.18
		 */
		WC_AM_SMART_CACHE()->delete_cache( array_merge( $this->request, array( 'resources' => $this->resources ) ) );

		$is_active = WC_AM_API_ACTIVATION_DATA_STORE()->is_instance_activated( $this->request[ 'instance' ] );

		if ( $is_active ) {
			/**
			 * Activation error. The API Key has already been activated with the same unique instance ID sent with this request.
			 *
			 * @since 2.1
			 *
			 * @param boolean Default false. Not intended for return value.
			 * @param Array ( $this->resources ). Array of customer API Resources.
			 * @param Array ( $this->request ). Array of customer API request data.
			 */
			do_action( 'wc_api_manager_activate_api_key_already_activated_error', false, $this->resources, $this->request, $this->user_id );

			if ( $this->error_log ) {
				WC_AM_Log()->api_error_log( PHP_EOL . esc_html__( 'Error in activate() method. Error message is "Error code 100. Cannot activate API Key. The API Key has already been activated with the same unique instance ID sent with this request."', 'woocommerce-api-manager' ) . PHP_EOL . esc_html__( 'Resources available:', 'woocommerce-api-manager' ) . PHP_EOL . wc_print_r( $this->resources, true ) . PHP_EOL . esc_html__( 'Request data received:', 'woocommerce-api-manager' ) . PHP_EOL . wc_print_r( $this->request, true ) );
			}

			$this->error_response( '100', esc_html__( 'Cannot activate API Key. The API Key has already been activated with the same unique instance ID sent with this request.', 'woocommerce-api-manager' ) );
		}

		if ( is_numeric( $this->request[ 'product_id' ] ) ) {
			$product_id = WC_AM_API_RESOURCE_DATA_STORE()->get_api_information_and_update_product_id( $this->request[ 'product_id' ] );
		} else {
			$product_id = WC_AM_API_RESOURCE_DATA_STORE()->get_api_information_and_update_product_id( $this->request[ 'product_id' ], $this->request[ 'api_key' ] );
		}

		$data[ 'unlimited_activations' ]       = WC_AM_PRODUCT_DATA_STORE()->is_api_product_unlimited_activations( $product_id ); // since 2.2
		$data[ 'total_activations_purchased' ] = $total_activations_purchased = WC_AM_API_RESOURCE_DATA_STORE()->get_total_activations_purchased( $this->resources );
		$data[ 'total_activations' ]           = $total_activations = WC_AM_API_RESOURCE_DATA_STORE()->get_total_activations( $this->resources ) + 1;
		$total_activations_remaining           = $total_activations_purchased - $total_activations;
		$data[ 'activations_remaining' ]       = $total_activations_remaining;
		// Legacy API Manager < 2.0, and API Manager PHP Library <= 1.2.
		$top_level_data[ 'message' ] = sprintf( __( '%s out of %s activations remaining', 'woocommerce-api-manager' ), $total_activations_remaining, $total_activations_purchased );

		$add_api_key_activation = WC_AM_API_ACTIVATION_DATA_STORE()->add_api_key_activation( $this->user_id, $this->resources, $this->request );
		// Legacy API Manager < 2.0, and API Manager PHP Library <= 1.2.
		$top_level_data[ 'activated' ] = true;

		if ( ! $add_api_key_activation ) {
			/**
			 * Activation error. Cannot activate API Key. No API resources available.
			 *
			 * @since 2.1
			 *
			 * @param boolean Default false. Not intended for return value.
			 * @param Array ( $this->resources ). Array of customer API Resources.
			 * @param Array ( $this->request ). Array of customer API request data.
			 */
			do_action( 'wc_api_manager_activate_no_api_resources_available_error', false, $this->resources, $this->request, $this->user_id );

			if ( $this->error_log ) {
				WC_AM_Log()->api_error_log( PHP_EOL . esc_html__( 'Error in activate() method. Error message is "Error code 100. Cannot activate API Key. No API resources available."', 'woocommerce-api-manager' ) . PHP_EOL . esc_html__( 'Resources available:', 'woocommerce-api-manager' ) . PHP_EOL . wc_print_r( $this->resources, true ) . PHP_EOL . esc_html__( 'Request data received:', 'woocommerce-api-manager' ) . PHP_EOL . wc_print_r( $this->request, true ) );
			}

			$this->error_response( '100', esc_html__( 'Cannot activate API Key. No API resources available.', 'woocommerce-api-manager' ) );
		}

		if ( $this->debug_log ) {
			WC_AM_Log()->api_debug_log( PHP_EOL . esc_html__( 'Details from activate() method.', 'woocommerce-api-manager' ) . PHP_EOL . 'Resources:' . PHP_EOL . wc_print_r( $this->resources, true ) . PHP_EOL . 'Request:' . PHP_EOL . wc_print_r( $this->request, true ) );
		}

		if ( $this->send_response_data ) {
			// Refresh resource data to include activation.
			try {
				$data[ 'resources' ] = WC_AM_API_RESOURCE_DATA_STORE()->get_active_api_resources( $this->request[ 'api_key' ], $this->request[ 'product_id' ] );
			} catch ( Exception $exception ) {
				if ( $this->debug_log ) {
					WC_AM_Log()->api_error_log( PHP_EOL . esc_html__( 'Details from activate() method.', 'woocommerce-api-manager' ) . PHP_EOL . $exception );
				}

				$this->error_response( '100', esc_html__( 'There was an error getting API resources in activate()', 'woocommerce-api-manager' ) . ': ' . $exception );
			}
		}

		/**
		 * Activation success.
		 *
		 * @since 2.1
		 *
		 * @param boolean Default false. Not intended for return value.
		 * @param Array ( $this->resources ). Array of customer API Resources.
		 * @param Array ( $this->request ). Array of customer API request data.
		 * @param Array $top_level_data
		 * @param Array $data
		 * @param int ($this->user_id)
		 */
		do_action( 'wc_api_manager_activate_success', false, $this->resources, $this->request, $top_level_data, $data, $this->user_id );

		/**
		 * Activation success response.
		 *
		 * @since 2.0
		 *
		 * @param Array $top_level_data
		 * @param Array $data
		 * @param Array ( $this->resources ). Array of customer API Resources.
		 * @param Array ( $this->request ). Array of customer API request data.
		 * @param int ( $this->user_id )
		 */
		$this->success_response( apply_filters( 'wc_api_manager_activation_top_level_data', $top_level_data ), apply_filters( 'wc_api_manager_activation_data', $data, $this->resources, $this->request, $this->user_id ) );
	}

	/**
	 * Deactivates an API Key to make resources unavailable.
	 *
	 * @since 2.0
	 */
	private function deactivate() {
		$data           = array();
		$top_level_data = array();

		$this->required( array( 'api_key', 'product_id', 'instance' ) );
		// Verify the user account exists before proceeding.
		$this->verify_user();
		// Get the API resources for this customer to verify there are resources available.
		$this->get_resources();

		/**
		 * Delete cache.
		 *
		 * @since 2.0.11
		 */
		WC_AM_SMART_CACHE()->delete_cache( array_merge( $this->request, array( 'resources' => $this->resources ) ) );

		if ( is_numeric( $this->request[ 'product_id' ] ) ) {
			$product_id = WC_AM_API_RESOURCE_DATA_STORE()->get_api_information_and_update_product_id( $this->request[ 'product_id' ] );
		} else {
			$product_id = WC_AM_API_RESOURCE_DATA_STORE()->get_api_information_and_update_product_id( $this->request[ 'product_id' ], $this->request[ 'api_key' ] );
		}

		$data[ 'unlimited_activations' ]       = WC_AM_PRODUCT_DATA_STORE()->is_api_product_unlimited_activations( $product_id ); // since 2.2
		$data[ 'total_activations_purchased' ] = $total_activations_purchased = WC_AM_API_RESOURCE_DATA_STORE()->get_total_activations_purchased( $this->resources );
		$data[ 'total_activations' ]           = $total_activations = WC_AM_API_RESOURCE_DATA_STORE()->get_total_activations( $this->resources ) - 1;
		$total_activations_remaining           = $total_activations_purchased - $total_activations;
		$data[ 'activations_remaining' ]       = $total_activations_remaining;
		// Legacy API Manager < 2.0, and API Manager PHP Library <= 1.2.
		$top_level_data[ 'activations_remaining' ] = sprintf( __( '%s out of %s activations remaining', 'woocommerce-api-manager' ), $total_activations_remaining, $total_activations_purchased );

		$delete_api_key_activation = WC_AM_API_ACTIVATION_DATA_STORE()->delete_api_key_activation_by_instance_id( $this->request[ 'instance' ] );
		// Legacy API Manager < 2.0, and API Manager PHP Library <= 1.2.
		$top_level_data[ 'deactivated' ] = true;

		if ( ! $delete_api_key_activation ) {
			/**
			 * Activation error. Cannot activate API Key. No API resources available.
			 *
			 * @since 2.1
			 *
			 * @param boolean Default false. Not intended for return value.
			 * @param Array ( $this->resources ). Array of customer API Resources.
			 * @param Array ( $this->request ). Array of customer API request data.
			 */
			do_action( 'wc_api_manager_deactivate_cannot_deactivate_api_key_error', false, $this->resources, $this->request, $this->user_id );

			$this->error_response( '100', esc_html__( 'The API Key could not be deactivated.', 'woocommerce-api-manager' ) );
		}

		if ( $this->debug_log ) {
			WC_AM_Log()->api_debug_log( PHP_EOL . esc_html__( 'Details from deactivate() method.', 'woocommerce-api-manager' ) . PHP_EOL . 'Resources:' . PHP_EOL . wc_print_r( $this->resources, true ) . PHP_EOL . 'Request:' . PHP_EOL . wc_print_r( $this->request, true ) );
		}

		if ( $this->send_response_data ) {
			// Refresh resource data to include deactivation.
			try {
				$data[ 'resources' ] = WC_AM_API_RESOURCE_DATA_STORE()->get_active_api_resources( $this->request[ 'api_key' ], $this->request[ 'product_id' ] );
			} catch ( Exception $exception ) {
				if ( $this->debug_log ) {
					WC_AM_Log()->api_error_log( PHP_EOL . esc_html__( 'Details from deactivate() method.', 'woocommerce-api-manager' ) . PHP_EOL . $exception );
				}

				$this->error_response( '100', esc_html__( 'There was an error getting API resources in deactivate()', 'woocommerce-api-manager' ) . ': ' . $exception );
			}
		}

		/**
		 * Deactivation success.
		 *
		 * @since 2.1
		 *
		 * @param boolean Default false. Not intended for return value.
		 * @param Array ( $this->resources ). Array of customer API Resources.
		 * @param Array ( $this->request ). Array of customer API request data.
		 * @param Array $top_level_data
		 * @param Array $data
		 * @param int ($this->user_id)
		 */
		do_action( 'wc_api_manager_deactivate_success', false, $this->resources, $this->request, $top_level_data, $data, $this->user_id );

		/**
		 * Deactivation success response.
		 *
		 * @since 2.0
		 *
		 * @param Array $top_level_data
		 * @param Array $data
		 * @param Array ( $this->resources ). Array of customer API Resources.
		 * @param Array ( $this->request ). Array of customer API request data.
		 * @param int ( $this->user_id )
		 */
		$this->success_response( apply_filters( 'wc_api_manager_deactivation_top_level_data', $top_level_data ), apply_filters( 'wc_api_manager_deactivation_data', $data, $this->resources, $this->request, $this->user_id ) );
	}

	/**
	 * Checks the availability of resources for an API Key.
	 *
	 * @since 2.0
	 */
	private function status() {
		$data                      = array( 'resources' => $this->resources );
		$top_level_data            = array();
		$trans_data_name           = '';
		$trans_top_level_data_name = '';

		$this->required( array( 'api_key', 'product_id', 'instance' ) );

		if ( WCAM()->get_db_cache() ) {
			/**
			 * Data Cache
			 *
			 * @since 2.0.11
			 */
			$trans_hash                  = md5( $this->request[ 'api_key' ] . $this->request[ 'product_id' ] . $this->request[ 'instance' ] );
			$trans_data_name             = 'wc_am_api_status_func_data_' . $trans_hash;
			$trans_top_level_data_name   = 'wc_am_api_status_func_top_level_data_' . $trans_hash;
			$status_data_trans           = WC_AM_SMART_CACHE()->set_or_get_cache( $trans_data_name );
			$status_top_level_data_trans = WC_AM_SMART_CACHE()->set_or_get_cache( $trans_top_level_data_name );

			if ( $status_data_trans !== false ) {
				$this->success_response( apply_filters( 'wc_api_manager_status_top_level_data', $status_top_level_data_trans !== false ? $status_top_level_data_trans : $top_level_data ), apply_filters( 'wc_api_manager_status_data', $status_data_trans ) );
			}
		}

		// Verify the user account exists before proceeding.
		$this->verify_user();
		// Get the API resources for this customer to verify there are resources available.
		$this->get_resources();

		if ( $this->debug_log ) {
			WC_AM_Log()->api_debug_log( PHP_EOL . esc_html__( 'Details from status() method.', 'woocommerce-api-manager' ) . PHP_EOL . 'Resources:' . PHP_EOL . wc_print_r( $data, true ) . PHP_EOL . 'Request:' . PHP_EOL . wc_print_r( $this->request, true ) );
		}

		if ( $this->send_response_data ) {
			// Refresh resource data to include activation.
			try {
				$data[ 'resources' ] = WC_AM_API_RESOURCE_DATA_STORE()->get_active_api_resources( $this->request[ 'api_key' ], $this->request[ 'product_id' ] );
			} catch ( Exception $exception ) {
				$this->error_response( '100', esc_html__( 'There was an error getting API resources in status()', 'woocommerce-api-manager' ) . ': ' . $exception );

				if ( $this->debug_log ) {
					WC_AM_Log()->api_error_log( PHP_EOL . esc_html__( 'Details from status() method.', 'woocommerce-api-manager' ) . PHP_EOL . $exception );
				}
			}
		} else {
			$data = array();
		}

		$is_active = WC_AM_API_ACTIVATION_DATA_STORE()->is_instance_activated( $this->request[ 'instance' ] );

		if ( is_numeric( $this->request[ 'product_id' ] ) ) {
			$product_id = WC_AM_API_RESOURCE_DATA_STORE()->get_api_information_and_update_product_id( $this->request[ 'product_id' ] );
		} else {
			$product_id = WC_AM_API_RESOURCE_DATA_STORE()->get_api_information_and_update_product_id( $this->request[ 'product_id' ], $this->request[ 'api_key' ] );
		}

		/**
		 * @since 2.0.10
		 */
		if ( $is_active ) {
			$delete_api_key_activation = false;

			if ( $product_id ) {
				$product_data = WC_AM_API_RESOURCE_DATA_STORE()->get_row_data_by_api_key( $product_id, $this->request[ 'api_key' ] );

				// The API Resource does not exist, or the product_id and API Key given do not match the API Resource.
				if ( ! $product_data ) {
					$delete_api_key_activation = WC_AM_API_ACTIVATION_DATA_STORE()->delete_api_key_activation_by_instance_id( $this->request[ 'instance' ] );

					if ( $delete_api_key_activation ) {
						$is_active = false;

						if ( $this->error_log ) {
							// Refresh resources.
							$this->get_resources();
							WC_AM_Log()->api_error_log( PHP_EOL . esc_html__( 'Error in status() method. Error message is "The product ID ' . wc_print_r( $this->request[ 'product_id' ], true ) . ' has an activated API Key, but it does not exist in your API resources. The API Key activation has been deleted as it should no longer exist without an authorized API resource."', 'woocommerce-api-manager' ) );
						}
					} else {
						if ( $this->error_log ) {
							WC_AM_Log()->api_error_log( PHP_EOL . esc_html__( 'Error in status() method. Error message is "The product ID ' . wc_print_r( $this->request[ 'product_id' ], true ) . ' has an activated API Key, but it does not exist in your API resources. The API Key activation with Instance ID ' . wc_print_r( $this->request[ 'instance' ] ) . ' could not be deleted automatically."', 'woocommerce-api-manager' ) );
						}
					}
				}
			}

			if ( ! $delete_api_key_activation && ! empty( $this->request[ 'version' ] ) ) {
				WC_AM_API_ACTIVATION_DATA_STORE()->update_version( $this->request[ 'instance' ], $this->request[ 'version' ] );
			}
		}

		$data[ 'unlimited_activations' ]       = WC_AM_PRODUCT_DATA_STORE()->is_api_product_unlimited_activations( $product_id ); // since 2.2
		$data[ 'total_activations_purchased' ] = $total_activations_purchased = WC_AM_API_RESOURCE_DATA_STORE()->get_total_activations_purchased( $this->resources );
		$data[ 'total_activations' ]           = $total_activations = WC_AM_API_RESOURCE_DATA_STORE()->get_total_activations( $this->resources );
		$data[ 'activations_remaining' ]       = $total_activations_purchased - $total_activations;
		$data[ 'activated' ]                   = $is_active;

		// Legacy API Manager < 2.0, and API Manager PHP Library <= 1.2.
		$top_level_data[ 'status_check' ] = $is_active ? 'active' : 'inactive';

		/**
		 * Data Cache
		 *
		 * @since 2.0.11
		 */
		if ( WCAM()->get_db_cache() ) {
			WC_AM_SMART_CACHE()->set_or_get_cache( $trans_data_name, apply_filters( 'wc_api_manager_status_data', $data ), WCAM()->get_api_cache_expires() * MINUTE_IN_SECONDS );
			WC_AM_SMART_CACHE()->set_or_get_cache( $trans_top_level_data_name, apply_filters( 'wc_api_manager_status_top_level_data', $top_level_data ), WCAM()->get_api_cache_expires() * MINUTE_IN_SECONDS );
		}

		/**
		 * Status success.
		 *
		 * @since 2.1.7
		 *
		 * @param boolean Default false. Not intended for return value.
		 * @param Array ( $this->resources ). Array of customer API Resources.
		 * @param Array ( $this->request ). Array of customer API request data.
		 * @param Array $top_level_data
		 * @param Array $data
		 * @param int ($this->user_id)
		 */
		do_action( 'wc_api_manager_status_success', false, $this->resources, $this->request, $top_level_data, $data, $this->user_id );

		/**
		 * Status success response.
		 *
		 * @since 2.0
		 *
		 * @param Array $top_level_data
		 * @param Array $data
		 * @param Array ( $this->resources ). Array of customer API Resources.
		 * @param Array ( $this->request ). Array of customer API request data.
		 * @param int ( $this->user_id )
		 */

		$this->success_response( apply_filters( 'wc_api_manager_status_top_level_data', $top_level_data ), apply_filters( 'wc_api_manager_status_data', $data, $this->resources, $this->request, $this->user_id ) );
	}

	/**
	 * Provides product specific details related to software updates.
	 * Data is sent to the client serialized, or as JSON, depending on the request value.
	 *
	 * @see   wp-admin/includes/plugin-install.php
	 *
	 * @since 2.0
	 *
	 * @param $type 'serialize' for the old clients, or 'json' going forward into infinity and beyond.
	 */
	private function information( $type ) {
		$this->required( array( 'product_id', 'plugin_name' ) );

		$is_active                 = false;
		$response                  = new stdClass();
		$product_data              = null;
		$download                  = null;
		$data                      = array();
		$top_level_data            = array();
		$trans_response_name       = '';
		$trans_data_name           = '';
		$trans_top_level_data_name = '';

		if ( is_numeric( $this->request[ 'product_id' ] ) ) {
			$product_id = WC_AM_API_RESOURCE_DATA_STORE()->get_api_information_and_update_product_id( $this->request[ 'product_id' ] );
		} else {
			$this->required( array( 'api_key' ) );
			$product_id = WC_AM_API_RESOURCE_DATA_STORE()->get_api_information_and_update_product_id( $this->request[ 'product_id' ], $this->request[ 'api_key' ] );
		}

		if ( ! $product_id ) {
			if ( $this->error_log ) {
				WC_AM_Log()->api_error_log( PHP_EOL . esc_html__( 'Error in information() method. Error message is "The product ID ' . wc_print_r( $this->request[ 'product_id' ], true ) . ' could not be found in this store."', 'woocommerce-api-manager' ) );
			}

			if ( $type == 'serialize' ) {
				// Legacy API Manager < 2.0, and API Manager PHP Library <= 1.2.
				$this->send_serial_error( 'pluginupdatecheck', array( 'download_revoked' => 'download_revoked' ) );
			} else {
				$this->error_response( '100', esc_html__( 'The product ID ' . wc_print_r( $this->request[ 'product_id' ], true ) . ' could not be found in this store.', 'woocommerce-api-manager' ) );
			}
		}

		/**
		 * Data Cache
		 *
		 * @since 2.0.11
		 */
		if ( WCAM()->get_db_cache() && ( empty( $this->request[ 'instance' ] ) || empty( $this->request[ 'api_key' ] ) ) ) {
			$trans_hash                = md5( $this->request[ 'product_id' ] );
			$trans_response_name       = 'wc_am_api_information_func_response_' . $trans_hash;
			$trans_data_name           = 'wc_am_api_information_func_data_' . $trans_hash;
			$trans_top_level_data_name = 'wc_am_api_information_func_top_level_data_' . $trans_hash;

			// Legacy API Manager < 2.0, and API Manager PHP Library <= 1.2.
			if ( $type == 'serialize' ) {
				$information_response_trans = WC_AM_SMART_CACHE()->set_or_get_cache( $trans_response_name );

				if ( $information_response_trans !== false ) {
					nocache_headers();

					die( serialize( apply_filters( 'wc_api_manager_info_response', $information_response_trans ) ) );
				}
			} else {
				$information_data_trans       = WC_AM_SMART_CACHE()->set_or_get_cache( $trans_data_name );
				$information_level_data_trans = WC_AM_SMART_CACHE()->set_or_get_cache( $trans_top_level_data_name );

				if ( $information_data_trans !== false ) {
					$this->success_response( apply_filters( 'wc_api_manager_info_top_level_data', $information_level_data_trans !== false ? $information_level_data_trans : $top_level_data ), apply_filters( 'wc_api_manager_info_data', $information_data_trans ) );
				}
			}
		} elseif ( WCAM()->get_db_cache() && ! empty( $this->request[ 'instance' ] ) && ! empty( $this->request[ 'api_key' ] ) ) {
			$trans_hash                = md5( $this->request[ 'api_key' ] . $this->request[ 'product_id' ] );
			$trans_response_name       = 'wc_am_api_information_func_response_active_' . $trans_hash;
			$trans_data_name           = 'wc_am_api_information_func_data_active_' . $trans_hash;
			$trans_top_level_data_name = 'wc_am_api_information_func_top_level_data_active_' . $trans_hash;
		}

		$product_object = WC_AM_PRODUCT_DATA_STORE()->get_product_object( $product_id );

		/**
		 * Use the instance ID to find an activation, because authentication is required to provide a download URL.
		 * Requests that do not have an activation in the database are not provided a download URL.
		 */
		if ( ! empty( $this->request[ 'instance' ] ) ) {
			$is_active = WC_AM_API_ACTIVATION_DATA_STORE()->is_instance_activated( $this->request[ 'instance' ] );
		}

		if ( $is_active ) {
			$this->required( array( 'api_key', 'instance', 'version' ) );

			/**
			 * Data Cache
			 *
			 * @since 2.0.11
			 */
			if ( WCAM()->get_db_cache() ) {
				// Legacy API Manager < 2.0, and API Manager PHP Library <= 1.2.
				if ( $type == 'serialize' ) {
					$information_response_trans = WC_AM_SMART_CACHE()->set_or_get_cache( $trans_response_name );

					if ( $information_response_trans !== false ) {
						nocache_headers();

						die( serialize( apply_filters( 'wc_api_manager_info_response', $information_response_trans ) ) );
					}
				} else {
					$information_data_trans       = WC_AM_SMART_CACHE()->set_or_get_cache( $trans_data_name );
					$information_level_data_trans = WC_AM_SMART_CACHE()->set_or_get_cache( $trans_top_level_data_name );

					if ( $information_data_trans !== false ) {
						$this->success_response( apply_filters( 'wc_api_manager_info_top_level_data', $information_level_data_trans !== false ? $information_level_data_trans : $top_level_data ), apply_filters( 'wc_api_manager_info_data', $information_data_trans ) );
					}
				}
			}

			$product_data = WC_AM_API_RESOURCE_DATA_STORE()->get_row_data_by_api_key( $product_id, $this->request[ 'api_key' ] );

			// The API Resource does not exist, or the product_id and API Key given do not match the API Resource.
			if ( ! $product_data ) {
				$delete_api_key_activation = WC_AM_API_ACTIVATION_DATA_STORE()->delete_api_key_activation_by_instance_id( $this->request[ 'instance' ] );

				if ( $this->error_log ) {
					if ( $delete_api_key_activation ) {
						WC_AM_Log()->api_error_log( PHP_EOL . esc_html__( 'Error in information() method. Error message is "The product ID ' . wc_print_r( $this->request[ 'product_id' ], true ) . ' has an activated API Key, but it does not exist in your API resources. The API Key activation has been deleted as it should no longer exist without an authorized API resource."', 'woocommerce-api-manager' ) );
					} else {
						WC_AM_Log()->api_error_log( PHP_EOL . esc_html__( 'Error in information() method. Error message is "The product ID ' . wc_print_r( $this->request[ 'product_id' ], true ) . ' has an activated API Key, but it does not exist in your API resources. The API Key activation with Instance ID ' . wc_print_r( $this->request[ 'instance' ] ) . ' could not be deleted automatically."', 'woocommerce-api-manager' ) );
					}
				}

				/**
				 * Bad API Key, so send client update data without update package URL rather than an error.
				 */
				$is_active = false;
				//$this->error_response( '100', esc_html__( 'The product ID ' . wc_print_r( $this->request[ 'product_id' ], true ) . ' resource could not be found even though it has an activated API Key.', 'woocommerce-api-manager' ) );
			} else {
				// Verify the user account exists before proceeding.
				$this->verify_user();
				// Get the API resources for this customer to verify there are resources available.
				$this->get_resources();
				// Not part of WordPress API data
				$data[ 'package' ][ 'product_id' ] = ! empty( $product_data->product_id ) ? $product_data->product_id : 0;
				$response->name                    = $data[ 'info' ][ 'name' ] = ! empty( $product_data->product_title ) ? $product_data->product_title : '';

				//try {
				//	$data_store   = WC_Data_Store::load( 'customer-download' );
				//	$download_ids = $data_store->get_downloads( array(
				//		                                            'user_id'    => $this->user_id,
				//		                                            'order_key'  => ! empty( $product_data->order_key ) ? $product_data->order_key : '',
				//		                                            'product_id' => $product_id,
				//		                                            'orderby'    => 'downloads_remaining',
				//		                                            'order'      => 'DESC',
				//		                                            'limit'      => 1,
				//		                                            'return'     => 'ids',
				//	                                            ) );
				//
				//	if ( ! empty( $download_ids ) ) {
				//		$download = new WC_Customer_Download( current( $download_ids ) );
				//	}
				//
				//	if ( ! empty( $download ) ) {
				//		$response->downloaded = $data[ 'info' ][ 'downloaded' ] = $download->get_download_count();
				//	}
				//} catch ( Exception $exception ) {
				//	$response->downloaded = $data[ 'info' ][ 'downloaded' ] = 0;
				//}

				// Temporary.
				$response->downloaded = $data[ 'info' ][ 'downloaded' ] = 0;

				$response->active_installs = $data[ 'info' ][ 'active_installs' ] = WC_AM_API_RESOURCE_DATA_STORE()->get_total_activations( $this->resources );
			}
		}

		$response->name = $data[ 'info' ][ 'name' ] = $product_object->get_title();

		if ( $this->debug_log ) {
			WC_AM_Log()->api_debug_log( PHP_EOL . esc_html__( 'Details from information() method.', 'woocommerce-api-manager' ) . PHP_EOL . wc_print_r( $product_id, true ) . PHP_EOL . wc_print_r( $this->request, true ) . PHP_EOL . wc_print_r( $product_data, true ) );
		}

		// Correct incorrectly formatted slug.
		if ( strpos( $this->request[ 'plugin_name' ], '.php' ) !== false ) {
			$slug = dirname( $this->request[ 'plugin_name' ] );
		} else {
			$slug = $this->request[ 'plugin_name' ];
		}

		$response->slug          = $data[ 'info' ][ 'slug' ] = ! empty( $this->request[ 'slug' ] ) ? $this->request[ 'slug' ] : $slug;
		$response->version       = $data[ 'info' ][ 'version' ] = $api_data[ '_api_new_version' ] = WC_AM_PRODUCT_DATA_STORE()->get_meta( $product_id, '_api_new_version' );
		$response->author        = $data[ 'info' ][ 'author' ] = $api_data[ '_api_author' ] = WC_AM_PRODUCT_DATA_STORE()->get_meta( $product_id, '_api_author' );
		$response->homepage      = $data[ 'info' ][ 'homepage' ] = $api_data[ '_api_plugin_url' ] = WC_AM_PRODUCT_DATA_STORE()->get_meta( $product_id, '_api_plugin_url' );
		$response->requires      = $data[ 'info' ][ 'requires' ] = $api_data[ '_api_version_required' ] = WC_AM_PRODUCT_DATA_STORE()->get_meta( $product_id, '_api_version_required' );
		$response->tested        = $data[ 'info' ][ 'tested' ] = $api_data[ '_api_tested_up_to' ] = WC_AM_PRODUCT_DATA_STORE()->get_meta( $product_id, '_api_tested_up_to' );
		$response->last_updated  = $data[ 'info' ][ 'last_updated' ] = $api_data[ '_api_last_updated' ] = WC_AM_PRODUCT_DATA_STORE()->get_meta( $product_id, '_api_last_updated' );
		$response->requires_php  = $data[ 'info' ][ 'requires_php' ] = $api_data[ '_api_requires_php' ] = WC_AM_PRODUCT_DATA_STORE()->get_meta( $product_id, '_api_requires_php' );
		$response->compatibility = $data[ 'info' ][ 'compatibility' ] = $response->tested;
		$response->sections      = $data[ 'info' ][ 'sections' ] = $this->api_doc_tab_choices( $product_id );
		//$response->short_description  = '';
		//$response->added              = '';
		//$response->tags               = '';
		//$response->rating             = '';
		//$response->ratings            = '';
		//$response->banners            = '';
		//$response->icons              = '';

		//Not used, but still exists in WordPress, so it is here as a placeholder.
		//$response->download_link = $data[ 'info' ]['download_link'] = $download_link;

		/**
		 * @since 2.0.10
		 */
		if ( $is_active && ! empty( $this->request[ 'version' ] ) ) {
			WC_AM_API_ACTIVATION_DATA_STORE()->update_version( $this->request[ 'instance' ], $this->request[ 'version' ] );
		}

		// Legacy API Manager < 2.0, and API Manager PHP Library <= 1.2.
		if ( $type == 'serialize' ) {
			/**
			 * Data Cache
			 *
			 * @since 2.0.11
			 */
			if ( WCAM()->get_db_cache() ) {
				WC_AM_SMART_CACHE()->set_or_get_cache( $trans_response_name, apply_filters( 'wc_api_manager_info_response', $response ), WCAM()->get_api_cache_expires() * MINUTE_IN_SECONDS );
			}

			nocache_headers();

			die( serialize( apply_filters( 'wc_api_manager_info_response', $response ) ) );
		} else {
			if ( $is_active && $this->send_response_data ) {
				$data[ 'product' ] = $product_data;
			}

			/**
			 * Data Cache
			 *
			 * @since 2.0.11
			 */
			if ( WCAM()->get_db_cache() ) {
				WC_AM_SMART_CACHE()->set_or_get_cache( $trans_data_name, apply_filters( 'wc_api_manager_info_data', $data ), WCAM()->get_api_cache_expires() * MINUTE_IN_SECONDS );
				WC_AM_SMART_CACHE()->set_or_get_cache( $trans_top_level_data_name, apply_filters( 'wc_api_manager_info_top_level_data', $top_level_data ), WCAM()->get_api_cache_expires() * MINUTE_IN_SECONDS );
			}

			$this->success_response( apply_filters( 'wc_api_manager_info_top_level_data', $top_level_data ), apply_filters( 'wc_api_manager_info_data', $data ) );
		}
	}

	/**
	 * Informs the client if there is a software update available. Clients that send an instance ID
	 * associated with an activation receive a download URL if an update is available, all other clients
	 * get the update data, but the download URL is empty.
	 *
	 * Data is sent to the client serialized, or as JSON, depending on the request value.
	 *
	 * @see   https://meta.trac.wordpress.org/browser/sites/trunk/wordpress.org/public_html/wp-content/plugins/plugin-directory/readme/class-parser.php
	 *
	 * @since 2.0
	 *
	 * @param $type 'serialize' for the old clients, or 'json' going forward into infinity and beyond.
	 */
	private function update( $type ) {
		$this->required( array( 'product_id', 'plugin_name' ) );

		$is_active                 = false;
		$response                  = new stdClass();
		$download_link             = '';
		$product_data              = null;
		$data                      = array();
		$top_level_data            = array();
		$trans_response_name       = '';
		$trans_data_name           = '';
		$trans_top_level_data_name = '';

		if ( is_numeric( $this->request[ 'product_id' ] ) ) {
			$product_id = WC_AM_API_RESOURCE_DATA_STORE()->get_api_information_and_update_product_id( $this->request[ 'product_id' ] );
		} else {
			$this->required( array( 'api_key' ) );
			$product_id = WC_AM_API_RESOURCE_DATA_STORE()->get_api_information_and_update_product_id( $this->request[ 'product_id' ], $this->request[ 'api_key' ] );
		}

		if ( ! $product_id ) {
			/**
			 * Update error. The Product ID could not be found.
			 *
			 * @since 2.1.4
			 *
			 * @param boolean Default false. Not intended for return value.
			 * @param Array ( $this->resources ). Array of customer API Resources.
			 * @param Array ( $this->request ). Array of customer API request data.
			 */
			do_action( 'wc_api_manager_update_product_id_not_found_error', false, $this->resources, $this->request );

			if ( $this->error_log ) {
				WC_AM_Log()->api_error_log( PHP_EOL . esc_html__( 'Error in update() method. Error message is "The product ID ' . wc_print_r( $this->request[ 'product_id' ], true ) . ' could not be found in this store."', 'woocommerce-api-manager' ) );
			}

			// Legacy API Manager < 2.0, and API Manager PHP Library <= 1.2.
			if ( $type == 'serialize' ) {
				// Legacy API Manager < 2.0, and API Manager PHP Library <= 1.2.
				$this->send_serial_error( 'pluginupdatecheck', array( 'download_revoked' => 'download_revoked' ) );
			} else { // JSON response
				$this->error_response( '100', esc_html__( 'The product ID ' . wc_print_r( $this->request[ 'product_id' ], true ) . ' could not be found in this store.', 'woocommerce-api-manager' ) );
			}
		}

		// Not authenticated since instance or API Key were not sent.
		if ( WCAM()->get_db_cache() && ( empty( $this->request[ 'instance' ] ) || empty( $this->request[ 'api_key' ] ) ) ) {
			/**
			 * Data Cache
			 *
			 * @since 2.0.11
			 */
			$trans_hash                = md5( $this->request[ 'product_id' ] );
			$trans_response_name       = 'wc_am_api_update_func_response_' . $trans_hash;
			$trans_data_name           = 'wc_am_api_update_func_data_' . $trans_hash;
			$trans_top_level_data_name = 'wc_am_api_update_func_top_level_data_' . $trans_hash;

			// Legacy API Manager < 2.0, and API Manager PHP Library <= 1.2.
			if ( $type == 'serialize' ) {
				$update_response_trans = WC_AM_SMART_CACHE()->set_or_get_cache( $trans_response_name );

				if ( $update_response_trans !== false ) {
					nocache_headers();

					die( serialize( apply_filters( 'wc_api_manager_api_update_response', $update_response_trans ) ) );
				}
			} else {
				$update_data_trans       = WC_AM_SMART_CACHE()->set_or_get_cache( $trans_data_name );
				$update_level_data_trans = WC_AM_SMART_CACHE()->set_or_get_cache( $trans_top_level_data_name );

				if ( $update_data_trans !== false ) {
					/**
					 * Update success cached response. Not authenticated since instance or API Key were not sent.
					 *
					 * @since 2.1.4
					 *
					 * @param boolean Default false. Not intended for return value.
					 * @param Array ( $this->resources ). Array of customer API Resources.
					 * @param Array ( $this->request ). Array of customer API request data.
					 * @param Array $update_level_data_trans cached data or $top_level_data is empty array.
					 * @param Array $update_data_trans       cached data.
					 */
					do_action( 'wc_api_manager_update_non_authenticated_cached_success', false, $this->resources, $this->request, $update_level_data_trans !== false ? $update_level_data_trans : $top_level_data, $update_data_trans );

					// Send cached response.
					$this->success_response( apply_filters( 'wc_api_manager_update_top_level_data', $update_level_data_trans !== false ? $update_level_data_trans : $top_level_data ), apply_filters( 'wc_api_manager_update_data', $update_data_trans ) );
				}
			}
		} elseif ( WCAM()->get_db_cache() && ! empty( $this->request[ 'instance' ] ) && ! empty( $this->request[ 'api_key' ] ) ) {
			$trans_hash                = md5( $this->request[ 'api_key' ] . $this->request[ 'product_id' ] );
			$trans_response_name       = 'wc_am_api_update_func_response_active_' . $trans_hash;
			$trans_data_name           = 'wc_am_api_update_func_data_active_' . $trans_hash;
			$trans_top_level_data_name = 'wc_am_api_update_func_top_level_data_active_' . $trans_hash;
		}

		/**
		 * Use the instance ID to find an activation, because authentication is required to provide a download URL.
		 * Requests that do not have an activation in the database are not provided a download URL.
		 */
		if ( ! empty( $this->request[ 'instance' ] ) ) {
			$is_active = WC_AM_API_ACTIVATION_DATA_STORE()->is_instance_activated( $this->request[ 'instance' ] );
		}

		if ( $is_active ) {
			$core_required_keys   = array( 'api_key', 'instance' );
			$active_required_keys = array_merge( $core_required_keys, array( 'version' ) );

			/**
			 * @since 2.0.9
			 */
			$this->required( apply_filters( 'wc_api_manager_update_active_required_keys', $active_required_keys ) );

			/**
			 * Data Cache
			 *
			 * @since 2.0.11
			 */
			if ( WCAM()->get_db_cache() ) {
				// Legacy API Manager < 2.0, and API Manager PHP Library <= 1.2.
				if ( $type == 'serialize' ) {
					$update_response_trans = WC_AM_SMART_CACHE()->set_or_get_cache( $trans_response_name );

					if ( $update_response_trans !== false ) {
						nocache_headers();

						die( serialize( apply_filters( 'wc_api_manager_api_update_response', $update_response_trans ) ) );
					}
				} else {
					$update_data_trans       = WC_AM_SMART_CACHE()->set_or_get_cache( $trans_data_name );
					$update_level_data_trans = WC_AM_SMART_CACHE()->set_or_get_cache( $trans_top_level_data_name );

					if ( $update_data_trans !== false ) {
						/**
						 * Update success cached response. Not authenticated since instance or API Key were not sent.
						 *
						 * @since 2.1.4
						 *
						 * @param boolean Default false. Not intended for return value.
						 * @param Array ( $this->resources ). Array of customer API Resources.
						 * @param Array ( $this->request ). Array of customer API request data.
						 * @param Array $update_level_data_trans cached data or $top_level_data is empty array.
						 * @param Array $update_data_trans       cached data.
						 */
						do_action( 'wc_api_manager_update_authenticated_cached_success', false, $this->resources, $this->request, $update_level_data_trans !== false ? $update_level_data_trans : $top_level_data, $update_data_trans );

						// Send cached response.
						$this->success_response( apply_filters( 'wc_api_manager_update_top_level_data', $update_level_data_trans !== false ? $update_level_data_trans : $top_level_data ), apply_filters( 'wc_api_manager_update_data', $update_data_trans ) );
					}
				}
			}

			$product_data = WC_AM_API_RESOURCE_DATA_STORE()->get_row_data_by_api_key( $product_id, $this->request[ 'api_key' ] );

			// The API Resource does not exist, or the product_id and API Key given do not match the API Resource.
			if ( ! $product_data ) {
				$delete_api_key_activation = WC_AM_API_ACTIVATION_DATA_STORE()->delete_api_key_activation_by_instance_id( $this->request[ 'instance' ] );

				if ( $this->error_log ) {
					if ( $delete_api_key_activation ) {
						WC_AM_Log()->api_error_log( PHP_EOL . esc_html__( 'Error in update() method. Error message is "The product ID ' . wc_print_r( $this->request[ 'product_id' ], true ) . ' has an activated API Key, but it does not exist in your API resources. The API Key activation has been deleted as it should no longer exist without an authorized API resource."', 'woocommerce-api-manager' ) );
					} else {
						WC_AM_Log()->api_error_log( PHP_EOL . esc_html__( 'Error in update() method. Error message is "The product ID ' . wc_print_r( $this->request[ 'product_id' ], true ) . ' has an activated API Key, but it does not exist in your API resources. The API Key activation with Instance ID ' . wc_print_r( $this->request[ 'instance' ] ) . ' could not be deleted automatically."', 'woocommerce-api-manager' ) );
					}
				}

				/**
				 * Bad API Key, so send client update data without update package URL rather than an error.
				 */
				$is_active = false;
				//$this->error_response( '100', esc_html__( 'The product ID ' . wc_print_r( $this->request[ 'product_id' ], true ) . ' has an activated API Key, but it does not exist in your API resources.', 'woocommerce-api-manager' ) );
			} else {
				/**
				 * Good API Key, so send client update data with update package URL.
				 */
				// Verify the user account exists before proceeding.
				$this->verify_user();
				// Get the API resources for this customer to verify there are resources available.
				$this->get_resources();

				// Not part of WordPress API data
				$data[ 'package' ][ 'product_id' ] = ! empty( $product_data->product_id ) ? $product_data->product_id : 0;
			}
		}

		//$product_object = WC_AM_PRODUCT_DATA_STORE()->get_product_object( $product_id );
		//$response->id = $data[ 'package' ][ 'id' ] = $product_object ? $product_object->get_title() : '';

		if ( strpos( $this->request[ 'plugin_name' ], '.php' ) !== false ) {
			$response->id = $data[ 'package' ][ 'id' ] = dirname( $this->request[ 'plugin_name' ] ) . '-' . $product_id;
		} else {
			$response->id = $data[ 'package' ][ 'id' ] = $this->request[ 'plugin_name' ] . '-' . $product_id;
		}

		if ( $this->debug_log ) {
			WC_AM_Log()->api_debug_log( PHP_EOL . esc_html__( 'Details from update() method.', 'woocommerce-api-manager' ) . PHP_EOL . wc_print_r( $product_id, true ) . PHP_EOL . wc_print_r( $this->request, true ) . PHP_EOL . wc_print_r( $product_data, true ) );
		}

		// Correct incorrectly formatted slug.
		if ( strpos( $this->request[ 'plugin_name' ], '.php' ) !== false ) {
			$slug = dirname( $this->request[ 'plugin_name' ] );
		} else {
			$slug = $this->request[ 'plugin_name' ];
		}

		$response->slug           = $data[ 'package' ][ 'slug' ] = ! empty( $this->request[ 'slug' ] ) ? $this->request[ 'slug' ] : $slug;
		$response->plugin         = $data[ 'package' ][ 'plugin' ] = $this->request[ 'plugin_name' ];
		$response->new_version    = $data[ 'package' ][ 'new_version' ] = WC_AM_PRODUCT_DATA_STORE()->get_meta( $product_id, '_api_new_version' );
		$response->url            = $data[ 'package' ][ 'url' ] = WC_AM_PRODUCT_DATA_STORE()->get_meta( $product_id, '_api_plugin_url' );
		$response->tested         = $data[ 'package' ][ 'tested' ] = WC_AM_PRODUCT_DATA_STORE()->get_meta( $product_id, '_api_tested_up_to' );
		$response->upgrade_notice = $data[ 'package' ][ 'upgrade_notice' ] = WC_AM_PRODUCT_DATA_STORE()->get_meta( $product_id, '_api_upgrade_notice' );

		if ( $is_active ) {
			$url        = WC_AM_PRODUCT_DATA_STORE()->get_first_download_url( $product_id );
			$remote_url = WC_AM_URL()->is_download_external_url( $product_id );

			// If Amazon S3 URL.
			if ( ! empty( $url ) && WC_AM_URL()->find_amazon_s3_in_url( $url ) === true ) {
				$download_link = WC_AM_URL()->format_secure_s3_v4_url( $url );
			} elseif ( ! empty( $remote_url ) ) { // If remote URL that is not an Amazon S3 URL.
				$download_link = WC_AM_ORDER_DATA_STORE()->get_secure_order_download_url( $this->user_id, $product_data->order_id, $product_id, $remote_url );
			} else { // Local URL.
				if ( ! empty( $product_data->sub_order_key ) ) {
					// WooCommerce Subscriptions >= 2.0
					if ( empty( $download_link ) ) {
						$download_link = WC_AM_Order_Data_Store()->get_secure_order_download_url( (int) $this->user_id, $product_data->sub_id, $product_id );
					}
				} else {
					// Build the order specific download URL
					$download_link = WC_AM_Order_Data_Store()->get_secure_order_download_url( (int) $this->user_id, $product_data->order_id, $product_id );
				}
			}

			//$has_download_permission = WC_AM_PRODUCT_DATA_STORE()->has_download_permission( ! empty( $product_data->sub_order_key ) ? $product_data->sub_order_key : $product_data->order_key, $product_id );
			//
			//if ( ! $has_download_permission ) {
			//	// Legacy API Manager < 2.0, and API Manager PHP Library <= 1.2.
			//	if ( $type == 'serialize' ) {
			//		$this->send_serial_error( 'pluginupdatecheck', array( 'download_revoked' => 'download_revoked' ) );
			//	} else { // JSON response
			//		$this->error_response( '100', esc_html__( 'This API resource does not have download permission.', 'woocommerce-api-manager' ) );
			//	}
			//
			//	$download_link = '';
			//}
		}

		$response->package = $data[ 'package' ][ 'package' ] = $download_link;

		/**
		 * @since 2.0.10
		 */
		if ( $is_active && ! empty( $this->request[ 'version' ] ) ) {
			WC_AM_API_ACTIVATION_DATA_STORE()->update_version( $this->request[ 'instance' ], $this->request[ 'version' ] );
		}

		// Legacy API Manager < 2.0, and API Manager PHP Library <= 1.2.
		if ( $type == 'serialize' ) {
			/**
			 * Data Cache
			 *
			 * @since 2.0.11
			 */
			if ( WCAM()->get_db_cache() ) {
				WC_AM_SMART_CACHE()->set_or_get_cache( $trans_response_name, apply_filters( 'wc_api_manager_api_update_response', $response ), WCAM()->get_api_cache_expires() * MINUTE_IN_SECONDS );
			}

			nocache_headers();

			die( serialize( apply_filters( 'wc_api_manager_api_update_response', $response ) ) );
		} else { // JSON response
			if ( $is_active && $this->send_response_data ) {
				$data[ 'product' ] = $product_data;
			}

			/**
			 * Data Cache
			 *
			 * @since 2.0.11
			 */
			if ( WCAM()->get_db_cache() ) {
				WC_AM_SMART_CACHE()->set_or_get_cache( $trans_data_name, apply_filters( 'wc_api_manager_update_data', $data ), WCAM()->get_api_cache_expires() * MINUTE_IN_SECONDS );
				WC_AM_SMART_CACHE()->set_or_get_cache( $trans_top_level_data_name, apply_filters( 'wc_api_manager_update_top_level_data', $top_level_data ), WCAM()->get_api_cache_expires() * MINUTE_IN_SECONDS );
			}

			/**
			 * Update success.
			 *
			 * @since 2.1.3
			 *
			 * @param boolean Default false. Not intended for return value.
			 * @param Array ( $this->resources ). Array of customer API Resources.
			 * @param Array ( $this->request ). Array of customer API request data.
			 * @param Array $top_level_data
			 * @param Array $data
			 * @param int ($this->user_id)
			 */
			do_action( 'wc_api_manager_update_success', false, $this->resources, $this->request, $top_level_data, $data, $this->user_id );

			/**
			 * Update success response.
			 *
			 * @since 2.0
			 *
			 * @param Array $top_level_data
			 * @param Array $data
			 * @param Array ( $this->resources ). Array of customer API Resources.
			 * @param Array ( $this->request ). Array of customer API request data.
			 * @param int ( $this->user_id )
			 */
			$this->success_response( apply_filters( 'wc_api_manager_update_top_level_data', $top_level_data ), apply_filters( 'wc_api_manager_update_data', $data, $this->resources, $this->request, $this->user_id ) );
		}
	}

	/**
	 * Send a JSON response back to an API request, indicating success, and data if set.
	 *
	 * @since 2.0
	 *
	 * @param array $top_level_data Data one level above $data.
	 * @param null  $data           Data to encode as JSON, then print and die.
	 * @param null  $status_code    The HTTP status code to output.
	 */
	function success_response( $top_level_data = array(), $data = null, $status_code = null ) {
		$response = array( 'success' => true );

		if ( ! empty( $top_level_data ) ) {
			$response = array_merge( $top_level_data, $response );
		}

		if ( ! empty( $data ) ) {
			$response[ 'data' ] = $data;
		}

		// Amount of time for the API call to complete.
		$response[ 'api_call_execution_time' ] = round( microtime( true ) - $this->time_start, 6 ) . ' seconds';

		if ( $this->response_log ) {
			WC_AM_Log()->api_response_log( PHP_EOL . esc_html__( 'Details from success_response() method. This is the success response from the API:', 'woocommerce-api-manager' ) . PHP_EOL . wc_print_r( $response, true ) );
		}

		/**
		 * TODO:WCY
		 *
		 * 为总的API返回增加过滤器，方便记录API日志
		 */
		wp_send_json( apply_filters( 'wc_api_success_response', $response ), $status_code );
	}

	/**
	 * Sends a JSON error response.
	 *
	 * @since 2.0
	 *
	 * @param int    $code           A number that can be used by client software for their own interpretation.
	 * @param string $message        A friendly message explaining the error.
	 * @param array  $top_level_data Data one level above $data.
	 * @param int    $status_code    The HTTP status code to output.
	 */
	private function error_response( $code, $message, $top_level_data = array(), $status_code = null ) {
		// Legacy API Manager < 2.0, and API Manager PHP Library < 1.2.
		$top_level_data[ 'code' ]  = $code;
		$top_level_data[ 'error' ] = $message;

		$response = array( 'success' => false );
		$data     = array( 'error_code' => $code, 'error' => $message );

		if ( ! empty( $top_level_data ) ) {
			$response = array_merge( $top_level_data, $response );
		}

		if ( isset( $data ) ) {
			if ( is_wp_error( $data ) ) {
				$result = array();

				if ( ! empty( $data->errors ) ) {
					foreach ( $data->errors as $code => $messages ) {
						foreach ( $messages as $message ) {
							$result[] = array( 'code' => $code, 'message' => $message );
						}
					}
				}

				$response[ 'data' ] = $result;
			} else {
				$response[ 'data' ] = $data;
			}
		}

		// Amount of time for the API call to complete.
		$response[ 'api_call_execution_time' ] = round( microtime( true ) - $this->time_start, 6 ) . ' seconds';

		if ( $this->response_log ) {
			WC_AM_Log()->api_response_log( PHP_EOL . esc_html__( 'Details from error_response() method. This is the error response from the API:', 'woocommerce-api-manager' ) . PHP_EOL . wc_print_r( $response, true ) );
		}

		/**
		 * TODO:WCY
		 *
		 * 为总的API返回增加过滤器，方便记录API日志
		 */
		wp_send_json( apply_filters( 'wc_api_error_response', $response ), $status_code );
	}

	/**
	 * Plugin and Theme Update API error method for serialized data.
	 *
	 * @since  1.0
	 *
	 * @param string $request
	 * @param array  $errors
	 */
	private function send_serial_error( $request, $errors ) {
		$response = new stdClass();

		switch ( $request ) {
			case 'pluginupdatecheck':
				$response->slug        = '';
				$response->plugin      = '';
				$response->new_version = '';
				$response->url         = '';
				$response->tested      = '';
				$response->package     = '';
				$response->errors      = $errors;

				break;

			case 'plugininformation':
				$response->version       = '';
				$response->slug          = '';
				$response->author        = '';
				$response->homepage      = '';
				$response->requires      = '';
				$response->tested        = '';
				$response->downloaded    = '';
				$response->last_updated  = '';
				$response->requires_php  = '';
				$response->download_link = '';
				$response->sections      = array(
					'description'  => '',
					'installation' => '',
					'faq'          => '',
					'screenshots'  => '',
					'changelog'    => '',
					'other_notes'  => ''
				);

				$response->errors = $errors;

				break;
		}

		nocache_headers();

		die( serialize( $response ) );
	}

	/**
	 * Prepare API Doc sections, according to the choices on the settings screen,
	 * to only display those tab choices on the Plugin Information (View version details) screen.
	 *
	 * @since 2.0
	 *
	 * @param string|int $product_id
	 *
	 * @return array
	 */
	private function api_doc_tab_choices( $product_id ) {
		// API Doc Choices
		$description  = get_option( 'woocommerce_api_manager_description' );
		$installation = get_option( 'woocommerce_api_manager_installation' );
		$faq          = get_option( 'woocommerce_api_manager_faq' );
		$screenshots  = get_option( 'woocommerce_api_manager_screenshots' );
		$other_notes  = get_option( 'woocommerce_api_manager_other_notes' );
		$sections     = array();

		/**
		 * Caching
		 *
		 * @since 2.2.0
		 */
		$api_description         = false;
		$api_installation        = false;
		$api_faq                 = false;
		$api_screenshots         = false;
		$api_other_notes         = false;
		$api_changelog           = false;
		$trans_description_name  = 'wc_am_doc_tab_api_description_' . $product_id;
		$trans_installation_name = 'wc_am_doc_tab_api_installation_' . $product_id;
		$trans_faq_name          = 'wc_am_doc_tab_api_faq_' . $product_id;
		$trans_screenshots_name  = 'wc_am_doc_tab_api_screenshots_' . $product_id;
		$trans_other_notes_name  = 'wc_am_doc_tab_api_other_notes_' . $product_id;
		$trans_changelog_name    = 'wc_am_doc_tab_api_changelog_' . $product_id;

		if ( $description == 'yes' ) {
			if ( WCAM()->get_db_cache() ) {
				$api_description = WC_AM_SMART_CACHE()->set_or_get_cache( $trans_description_name );
			}

			if ( $api_description !== false ) {
				$sections[ 'description' ] = $api_description;
			} else {
				/**
				 * TODO:WCY
				 *
				 * 直接从Meta里调取产品信息而不是某个文章
				 */
				$get_description_section   = WC_AM_PRODUCT_DATA_STORE()->get_meta( $product_id, '51_default_editor' );
				$sections[ 'description' ] = $get_description_section;

				if ( WCAM()->get_db_cache() ) {
					WC_AM_SMART_CACHE()->set_or_get_cache( $trans_description_name, $get_description_section, WCAM()->get_api_cache_expires() * MINUTE_IN_SECONDS );
				}
			}
		}

		if ( $installation == 'yes' ) {
			if ( WCAM()->get_db_cache() ) {
				$api_installation = WC_AM_SMART_CACHE()->set_or_get_cache( $trans_installation_name );
			}

			if ( $api_installation !== false ) {
				$sections[ 'installation' ] = $api_installation;
			} else {
				/**
				 * TODO:WCY
				 *
				 * 直接从Meta里调取产品信息而不是某个文章
				 */
				$get_installation_section   = WC_AM_PRODUCT_DATA_STORE()->get_meta( $product_id, '365_default_editor' );
				$sections[ 'installation' ] = $get_installation_section;

				if ( WCAM()->get_db_cache() ) {
					WC_AM_SMART_CACHE()->set_or_get_cache( $trans_installation_name, $get_installation_section, WCAM()->get_api_cache_expires() * MINUTE_IN_SECONDS );
				}
			}
		}

		if ( $faq == 'yes' ) {
			if ( WCAM()->get_db_cache() ) {
				$api_faq = WC_AM_SMART_CACHE()->set_or_get_cache( $trans_faq_name );
			}

			if ( $api_faq !== false ) {
				$sections[ 'faq' ] = $api_faq;
			} else {
				/**
				 * TODO:WCY
				 *
				 * 直接从Meta里调取产品信息而不是某个文章
				 */
				$faqs_array = WC_AM_PRODUCT_DATA_STORE()->get_meta( $product_id, '46_custom_list_faqs' );
				$faqs = '';

				if ( ! empty( $faqs_array ) ){
					foreach( $faqs_array as $faq ) {
						$faqs .= '<div class="tab-faq-wrapper">';
						$faqs .= '<div class="tab-faq-title">';
						$faqs .= '<span class="tab-faq-icon closed"></span>';
						$faqs .= '<h4>' . wp_unslash( $faq['question'] ) . '</h4>';
						$faqs .= '</div>';
						$faqs .= '<div class="tab-faq-item">';
						$faqs .= '<div class="tab-faq-item-content">';
						$faqs .= '<p>' . wp_unslash( $faq['answer'] ) . '</p>';
						$faqs .= '</div>';
						$faqs .= '</div>';
						$faqs .= '</div>';
					}
				}

				$sections[ 'faq' ] = $faqs;

				if ( WCAM()->get_db_cache() ) {
					WC_AM_SMART_CACHE()->set_or_get_cache( $trans_faq_name, $faqs, WCAM()->get_api_cache_expires() * MINUTE_IN_SECONDS );
				}
			}
		}

		if ( $screenshots == 'yes' ) {
			if ( WCAM()->get_db_cache() ) {
				$api_screenshots = WC_AM_SMART_CACHE()->set_or_get_cache( $trans_screenshots_name );
			}

			if ( $api_screenshots !== false ) {
				$sections[ 'screenshots' ] = $api_screenshots;
			} else {
				/**
				 * TODO:WCY
				 *
				 * 直接从Meta里调取产品信息而不是某个文章
				 */
				$gallery_image_ids = wc_get_product( $product_id )->get_gallery_image_ids();
				$gallery_image_html = '';
				foreach ( $gallery_image_ids as $gallery_image_id ) {
					$gallery_image_html .= wp_get_attachment_image( $gallery_image_id, 450 );
				}

				$sections[ 'screenshots' ] = $gallery_image_html;

				if ( WCAM()->get_db_cache() ) {
					WC_AM_SMART_CACHE()->set_or_get_cache( $trans_screenshots_name, $gallery_image_html, WCAM()->get_api_cache_expires() * MINUTE_IN_SECONDS );
				}
			}
		}

		if ( $other_notes == 'yes' ) {
			if ( WCAM()->get_db_cache() ) {
				$api_other_notes = WC_AM_SMART_CACHE()->set_or_get_cache( $trans_other_notes_name );
			}

			if ( $api_other_notes !== false ) {
				$sections[ 'other_notes' ] = $api_other_notes;
			} else {
				$get_other_notes_section   = $this->get_page_content( get_post( WC_AM_PRODUCT_DATA_STORE()->get_meta( $product_id, '_api_other_notes' ) ) );
				$sections[ 'other_notes' ] = $get_other_notes_section;

				if ( WCAM()->get_db_cache() ) {
					WC_AM_SMART_CACHE()->set_or_get_cache( $trans_other_notes_name, $get_other_notes_section, WCAM()->get_api_cache_expires() * MINUTE_IN_SECONDS );
				}
			}
		}

		// Changelog is required.
		if ( WCAM()->get_db_cache() ) {
			$api_changelog = WC_AM_SMART_CACHE()->set_or_get_cache( $trans_changelog_name );
		}

		if ( $api_changelog !== false ) {
			$sections[ 'changelog' ] = $api_changelog;
		} else {
			/**
			 * TODO:WCY
			 *
			 * 直接从Meta里调取产品信息而不是某个文章
			 */
			$get_changelog_section   = WC_AM_PRODUCT_DATA_STORE()->get_meta( $product_id, '47_default_editor' );
			$sections[ 'changelog' ] = $get_changelog_section;

			if ( WCAM()->get_db_cache() ) {
				WC_AM_SMART_CACHE()->set_or_get_cache( $trans_changelog_name, $get_changelog_section, WCAM()->get_api_cache_expires() * MINUTE_IN_SECONDS );
			}
		}

		return $sections;
	}

	/**
	 * Check if is a valid IP address.
	 *
	 * @since  2.0
	 *
	 * @param string $ip_address IP address.
	 *
	 * @return string|bool The valid IP address, otherwise false.
	 */
	private function is_ip_address( $ip_address ) {
		// WP 4.7+ only.
		if ( function_exists( 'rest_is_ip_address' ) ) {
			return rest_is_ip_address( $ip_address );
		}

		// Support for WordPress 4.4 to 4.6.
		if ( ! class_exists( 'Requests_IPv6', false ) ) {
			include_once( WCAM()->plugin_path() . '/includes/vendor/class-requests-ipv6.php' );
		}

		$ipv4_pattern = '/^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/';

		if ( class_exists( 'Requests_IPv6' ) && ( ! preg_match( $ipv4_pattern, $ip_address ) && ! Requests_IPv6::check_ipv6( $ip_address ) ) ) {
			return false;
		}

		return $ip_address;
	}

	/**
	 * Get current user IP Address.
	 *
	 * @since 2.0
	 *
	 * @return string
	 */
	private function get_ip_address() {
		if ( isset( $_SERVER[ 'HTTP_X_REAL_IP' ] ) ) { // WPCS: input var ok, CSRF ok.
			return sanitize_text_field( wp_unslash( $_SERVER[ 'HTTP_X_REAL_IP' ] ) );  // WPCS: input var ok, CSRF ok.
		} elseif ( isset( $_SERVER[ 'HTTP_X_FORWARDED_FOR' ] ) ) { // WPCS: input var ok, CSRF ok.
			// Proxy servers can send through this header like this: X-Forwarded-For: client1, proxy1, proxy2
			// Make sure we always only send through the first IP in the list which should always be the client IP.
			return (string) rest_is_ip_address( trim( current( preg_split( '/,/', sanitize_text_field( wp_unslash( $_SERVER[ 'HTTP_X_FORWARDED_FOR' ] ) ) ) ) ) ); // WPCS: input var ok, CSRF ok.
		} elseif ( isset( $_SERVER[ 'REMOTE_ADDR' ] ) ) { // @codingStandardsIgnoreLine
			return sanitize_text_field( wp_unslash( $_SERVER[ 'REMOTE_ADDR' ] ) ); // @codingStandardsIgnoreLine
		}

		return '';
	}

	/**
	 * Returns page content if it exists.
	 *
	 * @since 2.0
	 *
	 * @param object $page_obj
	 *
	 * @return string
	 */
	public function get_page_content( $page_obj ) {
		if ( isset( $page_obj ) && is_object( $page_obj ) ) {
			if ( ! empty( $page_obj->post_content ) ) {
				return wp_kses_post( $page_obj->post_content );
			} else {
				return '';
			}
		}

		return '';
	}

}