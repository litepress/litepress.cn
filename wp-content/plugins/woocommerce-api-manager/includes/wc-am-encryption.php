<?php

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * WooCommerce API Manager Encryption Class
 *
 * Encrypts a string value.
 *
 * @package    WooCommerce API Manager/Encryption
 * @author     Todd Lahman LLC
 * @copyright  Copyright (c) Todd Lahman LLC
 * @since      1.3.2
 *
 */
class WC_AM_Encryption {

	private $auth_key;
	private $encryption_key;
	private $cipher_algo;
	private $hash_algo;
	private $key_hash;

	/**
	 * @var null
	 */
	private static $_instance = null;

	/**
	 * @static
	 *
	 * @return \WC_AM_Encryption
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	private function __construct() {
		$this->cipher_algo    = $this->get_available_cipher_method( 'AES-256-CTR' );
		$this->hash_algo      = $this->get_available_md_method( 'SHA256' );
		$this->auth_key       = substr( defined( 'AUTH_KEY' ) ? AUTH_KEY : '$$+Fs+*aSM)&m@ZkgG2pf2bM@COe&SG;W9GC4~;JS:6&-QE`?dCAwzS4}-%Ql*tV', 0, openssl_cipher_iv_length( $this->cipher_algo ) );
		$nonce_salt           = defined( 'NONCE_SALT' ) ? NONCE_SALT : ']K4eR{$@^@.Cb*P6+i0 jg&qEa8+V H-@N>:WuL/pW^z9nEte j|]{w!i!B~|saD';
		$nonce_key            = defined( 'NONCE_KEY' ) ? NONCE_KEY : 'b3-(Da2Wh+8p%RKc-7e=YDtQ#mN_wVde&[]L2?x`#<ok#8Hu?|R%PAY{4QXWg1>G';
		$this->encryption_key = $nonce_salt . $nonce_key;
		$this->key_hash       = openssl_digest( $this->encryption_key, $this->hash_algo, true );
	}

	/**
	 * Return a Cipher Method available on this object.
	 *
	 * @since 1.5
	 *
	 * @param string $cipher_algo
	 *
	 * @return mixed
	 */
	public function get_available_cipher_method( $cipher_algo = '' ) {
		$cipher_method = openssl_get_cipher_methods( true );

		if ( in_array( strtoupper( $cipher_algo ), $cipher_method ) ) {
			return $cipher_algo;
		} elseif ( in_array( strtolower( $cipher_algo ), $cipher_method ) ) {
			return $cipher_algo;
		}

		return $cipher_method[ 0 ];
	}

	/**
	 * Return a Hash/Digest Method available on this object.
	 *
	 * @since 1.5
	 *
	 * @param string $hash_algo
	 *
	 * @return mixed
	 */
	public function get_available_md_method( $hash_algo = '' ) {
		$hash_method = openssl_get_md_methods( true );

		if ( in_array( strtoupper( $hash_algo ), $hash_method ) ) {
			return $hash_algo;
		} elseif ( in_array( strtolower( $hash_algo ), $hash_method ) ) {
			return $hash_algo;
		}

		return $hash_method[ 0 ];
	}

	/**
	 * Encrypt a string.
	 *
	 * @since 1.5
	 *
	 * @param  string $data
	 *
	 * @return string
	 * @throws \Exception
	 */
	public function encrypt( $data ) {
		$encrypted = openssl_encrypt( $data, $this->cipher_algo, $this->key_hash, 0, $this->auth_key );

		if ( $encrypted === false ) {
			throw new Exception( sprintf( 'Encryption failed: ', openssl_error_string() ) );
		}

		return $encrypted;
	}

	/**
	 * Decrypt a string.
	 *
	 * @since      1.5
	 *
	 * @param null|0 $format Output encoding.
	 *
	 * @return string
	 * @throws \Exception
	 */
	public function decrypt( $data ) {
		$decrypted = openssl_decrypt( $data, $this->cipher_algo, $this->key_hash, 0, $this->auth_key );

		if ( $decrypted === false ) {
			throw new Exception( sprintf( 'Encryption failed: ', openssl_error_string() ) );
		}

		return $decrypted;
	}
}