<?php

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * WooCommerce API Manager Hash Class
 * Hashes passwords, checks hashed passwords against stored hashes, and manages hashed passwords in the database.
 *
 * @since       1.3.4
 *
 * @author      Todd Lahman LLC
 * @copyright   Copyright (c) Todd Lahman LLC
 * @package     WooCommerce API Manager/Hash
 */
class WC_AM_Hash {

	/**
	 * @var null
	 */
	private static $_instance = null;

	/**
	 * @static
	 * @return \WC_AM_Hash
	 */
	public static function instance() {

		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	private function __construct() { }

	/**
	 * Hash the password using the most secure PHP algorithm available.
	 *
	 * @param int    $user_id
	 * @param string $password
	 * @param string $algorithm
	 * @param string $options
	 *
	 * @return array|bool
	 */
	public function password_hash( $user_id, $password = '', $algorithm = '', $options = '' ) {
		$this->cleanup_hash();

		if ( empty( $password ) ) {
			$password = trim( $this->generate_key( 16 ) );
		}

		if ( function_exists( 'password_hash' ) ) {
			if ( empty( $algorithm ) ) {
				$algorithm = PASSWORD_DEFAULT;
			}

			$hash = password_hash( $password, $algorithm, $options );

			if ( ! empty( $hash ) ) {
				$hash_data = $this->add_hash( $user_id, $hash );

				if ( ! empty( $hash_data ) ) {
					return array( 'hname' => $hash_data[ 'hash_name' ], 'hkey' => $password, 'hexpires' => $hash_data[ 'hash_time' ] );
				} else {
					return false;
				}
			}
		}

		return false;
	}

	/**
	 * Verify hashed password with password.
	 *
	 * @param string $password
	 * @param string $hash_name
	 * @param int    $user_id
	 *
	 * @return boolean
	 */
	public function password_verify( $password, $hash_name, $user_id ) {
		$this->cleanup_hash();

		$hash_data = $this->get_hash( $user_id, $hash_name );

		if ( empty( $hash_data ) ) {
			return false;
		}

		if ( function_exists( 'password_verify' ) ) {
			if ( password_verify( $password, $hash_data->hash_value ) ) {
				if ( $this->hash_expired( $hash_data ) ) {
					$this->delete_hash( $hash_data->hash_value );
				}

				return true;
			} else {
				return false;
			}
		}

		return false;
	}

	/**
	 * Gets the hash from the database.
	 *
	 * @param int    $user_id
	 * @param string $hash_name
	 *
	 * @return object|boolean
	 */
	public function get_hash( $user_id, $hash_name ) {
		global $wpdb;

		$sql = "
			SELECT hash_user_id,hash_name,hash_value,hash_time
			FROM {$wpdb->prefix}wc_am_secure_hash
			WHERE hash_user_id = %d
			AND hash_name = %s";

		$args = array(
			$user_id,
			$hash_name,
		);

		// Returns an Object
		$result = $wpdb->get_row( $wpdb->prepare( $sql, $args ) );

		if ( is_object( $result ) ) {
			return $result;
		}

		return false;
	}

	/**
	 * Adds hash data row to the API Manager secure hash table.
	 *
	 * @param int    $user_id
	 * @param string $hash
	 *
	 * @return array|bool
	 */
	public function add_hash( $user_id, $hash ) {
		global $wpdb;

		$hash_name = $this->generate_key( 12 );

		// hash could contain up to 255 characters

		$data = array(
			'hash_user_id' => $user_id,
			'hash_name'    => $hash_name,
			'hash_value'   => $hash,
			'hash_time'    => WC_AM_ORDER_DATA_STORE()->get_current_time_stamp() + ( get_option( 'woocommerce_api_manager_url_expire' ) * DAY_IN_SECONDS )
		);

		$format = array(
			'%d',
			'%s',
			'%s',
			'%d'
		);

		$inserted = $wpdb->insert( $wpdb->prefix . 'wc_am_secure_hash', $data, $format );

		if ( $inserted ) {
			return $data;
		}

		return false;
	}

	/**
	 * Delete hash immediately.
	 *
	 * @param string $hash_value The hashed password.
	 */
	public function delete_hash( $hash_value ) {
		global $wpdb;

		$sql = "
			DELETE FROM {$wpdb->prefix}wc_am_secure_hash
			WHERE hash_value = %s
			";

		$wpdb->query( $wpdb->prepare( $sql, $hash_value ) );
	}

	/**
	 * Deletes expired hashes.
	 */
	public function cleanup_hash() {
		global $wpdb;

		$sql = "
			DELETE FROM {$wpdb->prefix}wc_am_secure_hash
			WHERE hash_time < %d
			";

		$wpdb->query( $wpdb->prepare( $sql, WC_AM_ORDER_DATA_STORE()->get_current_time_stamp() ) );
	}

	/**
	 * Check if the local secure download URL has expired as set in Settings > URL Expire Time.
	 * Used in WC_AM_Downloads class.
	 *
	 * @param int    $expires
	 * @param int    $user_id
	 * @param string $hash_name
	 *
	 * @return boolean
	 */
	public function is_expired( $expires, $user_id, $hash_name ) {
		$this->cleanup_hash();

		$hash_data = $this->get_hash( $user_id, $hash_name );

		if ( ! empty( $hash_data ) && $hash_data->hash_time == $expires ) {
			if ( WC_AM_ORDER_DATA_STORE()->is_time_expired( $hash_data->hash_time ) ) {
				return true;
			} else {
				return false;
			}
		}

		return true;
	}

	/**
	 * Hash expires as set in Settings > URL Expire Time.
	 *
	 * @since 1.5
	 *
	 * @param $hash_data
	 *
	 * @return bool
	 */
	public function hash_expired( $hash_data ) {
		$expire_time = $hash_data->hash_time + ( get_option( 'woocommerce_api_manager_url_expire' ) * DAY_IN_SECONDS );

		return WC_AM_ORDER_DATA_STORE()->is_time_expired( $expire_time );
	}

	/**
	 * Return a random hash.
	 *
	 * @since 2.0
	 *
	 * @param int $bytes
	 *
	 * @return string
	 */
	function rand_hash( $bytes = 20 ) {
		if ( function_exists( 'openssl_random_pseudo_bytes' ) ) {
			// 16 returns 32 chars, and 20 returns 40 chars.
			return bin2hex( openssl_random_pseudo_bytes( $bytes ) );
		} else {
			return sha1( wp_rand() );
		}
	}

	/**
	 * Return a long hash.
	 *
	 * @since 2.0
	 *
	 * @param string $data
	 *
	 * @return false|string
	 */
	function hmac_hash( $data = '' ) {
		return empty( $data ) ? hash_hmac( 'sha256', $this->rand_hash(), 'api-key' ) : hash_hmac( 'sha256', $data, 'api-key' );
	}

	/**
	 * Creates a unique pattern used in constructing a unique key.
	 *
	 * @since 2.0
	 *
	 * @param int $length
	 *
	 * @return string
	 */
	public function generate_key( $length = 12 ) {
		return trim( wp_generate_password( $length, false ) );
	}

}