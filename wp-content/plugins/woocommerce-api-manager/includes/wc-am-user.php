<?php

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * WooCommerce API Manager User Class
 *
 * @since       2.0
 * @author      Todd Lahman LLC
 * @copyright   Copyright (c) Todd Lahman LLC
 * @package     WooCommerce API Manager/User
 * @version     2.0
 */
class WC_AM_User {

	private $api_resource_table          = 'wc_am_api_resource';
	private $api_activation_table        = 'wc_am_api_activation';
	private $associated_api_key_table    = 'wc_am_associated_api_key';
	private $secure_hash_table           = 'wc_am_secure_hash';
	private $master_api_key_meta_key     = 'wc_am_master_api_key';
	private $master_api_key_status       = 'wc_am_master_api_key_status';
	private $hide_product_order_api_keys = false;

	/**
	 * @var null
	 */
	private static $_instance = null;

	/**
	 * @static
	 * @return \WC_AM_User
	 */
	public static function instance() {

		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	private function __construct() {
		$this->hide_product_order_api_keys = get_option( 'woocommerce_api_manager_hide_product_order_api_keys' );

		// Create Master API Keys and status when user created.
		add_action( 'user_register', array( $this, 'set_registration_master_key_and_status' ), 10, 1 );
		// Delete Master API Keys and status when user deleted.
		add_action( 'delete_user', array( $this, 'delete_master_key_and_status' ), 10, 2 );
		add_action( 'show_user_profile', array( $this, 'add_api_key_fields' ) );
		add_action( 'edit_user_profile', array( $this, 'add_api_key_fields' ) );
		add_action( 'personal_options_update', array( $this, 'set_key_and_status' ) );
		add_action( 'edit_user_profile_update', array( $this, 'set_key_and_status' ) );
	}

	/**
	 * Returns the string name of the API Resource database table.
	 *
	 * @since 2.0
	 *
	 * @return string
	 */
	public function get_api_resource_table_name() {
		return $this->api_resource_table;
	}

	/**
	 * Returns the string name of the API Activation database table.
	 *
	 * @since 2.0
	 *
	 * @return string
	 */
	public function get_api_activation_table_name() {
		return $this->api_activation_table;
	}

	/**
	 * Returns the string name of the API Activation database table.
	 *
	 * @since 2.0
	 *
	 * @return string
	 */
	public function get_associated_api_key_table_name() {
		return $this->associated_api_key_table;
	}

	/**
	 * Returns the string name of the Secure Hash database table.
	 *
	 * @since 2.1
	 *
	 * @return string
	 */
	public function get_secure_hash_table_name() {
		return $this->secure_hash_table;
	}

	/**
	 * Returns the user_id using the master API Key, a Product Order API Key, or an Associated API Key.
	 *
	 * @since 2.0
	 *
	 * @param string $api_key
	 *
	 * @return bool|integer
	 */
	public function get_user_id_by_api_key( $api_key ) {
		if ( ! empty( $api_key ) ) {
			global $wpdb;

			// Get user_id $api_key is a Master API Key.
			$user_id = $wpdb->get_var( $wpdb->prepare( "
				SELECT user_id
				FROM $wpdb->usermeta
				WHERE meta_key = %s
				AND meta_value = %s
				LIMIT 1
			", $this->master_api_key_meta_key, $api_key ) );

			if ( ! empty( $user_id ) ) {
				return $user_id;
			}

			// Get user_id $api_key is a Product Order API Key.
			$user_id = $wpdb->get_var( $wpdb->prepare( "
                SELECT user_id
                FROM {$wpdb->prefix}" . $this->api_resource_table . "
                WHERE product_order_api_key = %s
                LIMIT 1
		    ", $api_key ) );

			if ( ! empty( $user_id ) ) {
				return $user_id;
			}

			// Get user_id $api_key is an Associated API Key.
			$user_id = $wpdb->get_var( $wpdb->prepare( "
                SELECT user_id
                FROM {$wpdb->prefix}" . $this->api_resource_table . "
                WHERE api_resource_id = %d
                LIMIT 1
		    ", WC_AM_ASSOCIATED_API_KEY_DATA_STORE()->get_api_resource_id_by_associated_api_key( $api_key ) ) );

			return $user_id ? $user_id : false;
		}

		return false;
	}

	/**
	 * Return WP_User object or false if user does not exist.
	 *
	 * @since 2.0
	 *
	 * @param int $user_id
	 *
	 * @return bool|\WP_User
	 */
	public function get_user_data_by_user_id( $user_id ) {
		if ( empty( $user_id ) ) {
			$user_id = get_current_user_id();
		}

		return ! empty( $user_id ) ? get_user_by( 'id', $user_id ) : false;
	}

	/**
	 * Return the Master API Key.
	 *
	 * @since 2.0
	 *
	 * @param int $user_id
	 *
	 * @return bool|string
	 */
	public function get_master_api_key( $user_id = 0 ) {
		if ( empty( $user_id ) ) {
			$user_id = get_current_user_id();
		}

		$master_key = get_user_meta( $user_id, $this->master_api_key_meta_key, true );

		return ! empty( $master_key ) ? $master_key : false;
	}

	/**
	 * Return the value of the  Master API Key status.
	 *
	 * @since 2.0
	 *
	 * @param int $user_id
	 *
	 * @return mixed
	 */
	public function get_master_api_key_status( $user_id = 0 ) {
		if ( empty( $user_id ) ) {
			$user_id = get_current_user_id();
		}

		return get_user_meta( $user_id, $this->master_api_key_status, true );
	}

	/**
	 * Create Master API Keys and status when user created.
	 *
	 * @since 2.0
	 *
	 * @param int $user_id
	 */
	public function set_registration_master_key_and_status( $user_id ) {
		if ( empty( $user_id ) ) {
			$user_id = get_current_user_id();
		}

		if ( ! empty( $user_id ) ) {
			$master_key = $this->get_master_api_key( (int) $user_id );

			if ( empty( $master_key ) ) {
				$this->set_master_api_key( (int) $user_id );
			}

			$master_key_status = $this->get_master_api_key_status( (int) $user_id );

			if ( empty( $master_key_status ) ) {
				$this->set_master_api_key_status( (int) $user_id, 'active' );
			}
		}
	}

	/**
	 * Set the value of the Master API Key.
	 *
	 * @since 2.0
	 *
	 * @param int  $user_id
	 * @param bool $delete_all_activations
	 *
	 * @return bool|int
	 */
	public function set_master_api_key( $user_id, $delete_all_activations = false ) {
		if ( empty( $user_id ) ) {
			$user_id = get_current_user_id();
		}

		$api_key = WC_AM_HASH()->rand_hash();

		WC_AM_API_RESOURCE_DATA_STORE()->update_master_api_key( $api_key, $user_id );
		WC_AM_API_ACTIVATION_DATA_STORE()->update_master_api_key( $api_key, $user_id );

		// Delete all activations when the Master API Key is changed.
		if ( $delete_all_activations ) {
			WC_AM_API_ACTIVATION_DATA_STORE()->delete_api_key_activation_by_user_id( $user_id );
			WC_AM_API_ACTIVATION_DATA_STORE()->delete_all_api_resource_activation_ids_by_user_id( $user_id );
		}

		return update_user_meta( $user_id, $this->master_api_key_meta_key, $api_key );
	}

	/**
	 * Set the value of the Master API Key status.
	 *
	 * @since 2.0
	 *
	 * @param int    $user_id
	 * @param string $status
	 *
	 * @return bool|int
	 */
	public function set_master_api_key_status( $user_id, $status ) {
		if ( empty( $user_id ) ) {
			$user_id = get_current_user_id();
		}

		return update_user_meta( $user_id, $this->master_api_key_status, $status );
	}

	/**
	 * Delete Master API Keys and status when user deleted.
	 *
	 * @since 2.0
	 *
	 * @param int $user_id ID of the user to delete.
	 */
	public function delete_master_key_and_status( $user_id ) {
		if ( empty( $user_id ) ) {
			$user_id = get_current_user_id();
		}

		if ( ! empty( $user_id ) ) {
			$this->delete_master_api_key( $user_id );
			$this->delete_master_api_key_status( $user_id );
		}
	}

	/**
	 * Delete the value of the Master API Key.
	 *
	 * @since 2.0
	 *
	 * @param int $user_id
	 *
	 * @return void
	 */
	public function delete_master_api_key( $user_id ) {
		if ( empty( $user_id ) ) {
			$user_id = get_current_user_id();
		}

		$master_key = $this->get_master_api_key( (int) $user_id );

		if ( ! empty( $master_key ) ) {
			delete_user_meta( $user_id, $this->master_api_key_meta_key );
		}
	}

	/**
	 * Delete the value of the Master API Key status.
	 *
	 * @since 2.0
	 *
	 * @param int $user_id
	 */
	public function delete_master_api_key_status( $user_id ) {
		if ( empty( $user_id ) ) {
			$user_id = get_current_user_id();
		}

		$master_key_status = $this->get_master_api_key_status( (int) $user_id );

		if ( ! empty( $master_key_status ) ) {
			delete_user_meta( $user_id, $this->master_api_key_status );
		}
	}

	/**
	 * Returns true if the Master API key is enabled.
	 *
	 * @since 2.0
	 *
	 * @param int $user_id
	 *
	 * @return bool
	 */
	public function has_api_access( $user_id = 0 ) {
		if ( empty( $user_id ) ) {
			$user_id = get_current_user_id();
		}

		$status = $this->get_master_api_key_status( (int) $user_id );

		return ! empty( $status ) && $status != 'disabled' ? true : false;
	}

	/**
	 * Display the API Key fields on the user profile.
	 *
	 * @since 2.0
	 *
	 * @param WP_User $user
	 */
	public function add_api_key_fields( $user ) {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}

		if ( current_user_can( 'edit_user', $user->ID ) ) {
			?>
            <table class="form-table">
                <tbody>
                <tr>
                    <th><label for="software_store_keys"><?php esc_html_e( 'API Manager API Key', 'woocommerce-api-manager' ); ?></label></th>
                    <td>
						<?php if ( empty( $user->wc_am_master_api_key ) ) : ?>
                            <input name="generate_master_api_key" type="checkbox"
                                   id="generate_master_api_key" value="0"/>
                            <span
                                    class="description"><?php esc_html_e( 'Generate Master API Key', 'woocommerce-api-manager' ); ?></span>
						<?php else : ?>
                            <strong><?php esc_html_e( 'Master API Key:', 'woocommerce-api-manager' ); ?>&nbsp;</strong><code
                                    id="wc_am_master_api_key"><?php echo esc_attr( $user->wc_am_master_api_key ) ?></code>
                            <br>
                            <input name="replace_master_api_key" type="checkbox"
                                   id="replace_master_api_key" value="0"/>
                            <span
                                    class="description"><?php esc_html_e( 'Replace Master API Key', 'woocommerce-api-manager' ); ?></span>
                            <br>
                            <input name="wc_am_master_api_key_status" type="checkbox"
                                   id="wc_am_master_api_key_status" <?php checked( isset( $user->wc_am_master_api_key_status ) ? $user->wc_am_master_api_key_status : '', 'disabled' ); ?> />
                            <span
                                    class="description"><?php esc_html_e( 'Disable Master API Key', 'woocommerce-api-manager' ); ?></span>
						<?php endif; ?>
                    </td>
                </tr>
                </tbody>
            </table>
			<?php
		}
	}

	/**
	 * Regenerate, save, or disable the API keys, and set the key status, for a user.
	 *
	 * @since 2.0
	 *
	 * @param int $user_id
	 */
	public function set_key_and_status( $user_id ) {
		if ( current_user_can( 'edit_user', $user_id ) ) {
			$user = get_userdata( $user_id );

			if ( isset( $_POST[ 'generate_master_api_key' ] ) ) {
				if ( empty( $user->wc_am_master_api_key ) ) {
					$this->set_master_api_key( (int) $user->ID );
				}
			}

			if ( isset( $_POST[ 'replace_master_api_key' ] ) ) {
				// Delete all activations when the Master API Key is changed, so pass true.
				$this->set_master_api_key( (int) $user->ID, true );
			}

			if ( isset( $_POST[ 'wc_am_master_api_key_status' ] ) ) {
				$this->set_master_api_key_status( (int) $user->ID, 'disabled' );
			} else {
				$this->set_master_api_key_status( (int) $user->ID, 'active' );
			}
		}
	}

	/**
	 * Returns true if the Product Order API Keys should be hidden from the user.
	 *
	 * @since 2.0
	 *
	 * @return bool
	 */
	public function hide_product_order_api_keys() {
		return $this->hide_product_order_api_keys == 'yes' ? true : false;
	}

}