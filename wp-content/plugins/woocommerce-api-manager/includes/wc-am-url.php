<?php

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * WooCommerce API Manager URL Class
 *
 * @since       2.0
 *
 * @author      Todd Lahman LLC
 * @copyright   Copyright (c) Todd Lahman LLC
 * @package     WooCommerce API Manager/URL
 */
class WC_AM_URL {

	/**
	 * @var null
	 */
	private static $_instance = null;

	/**
	 * @static
	 * @return \WC_AM_URL
	 */
	public static function instance() {

		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	private function __construct() { }

	/**
	 * @deprecated 2.1
	 *
	 * Formats an Amazon S3 URL for secure Query String Request Authentication to the S3 REST API
	 * Works with either path-style request (s3.amazonaws.com/bucket), or virtual hosted-style (bucket.s3.amazonaws.com).
	 *
	 * http://docs.aws.amazon.com/AmazonS3/latest/dev/RESTAuthentication.html#RESTAuthenticationQueryStringAuth
	 * http://docs.aws.amazon.com/AmazonS3/latest/dev/RESTAuthentication.html#RESTAuthenticationExamples
	 * http://docs.aws.amazon.com/AmazonS3/latest/dev/RESTAuthentication.html
	 * http://docs.aws.amazon.com/AmazonS3/latest/UG/Introduction.html
	 *
	 * @since      1.3.2
	 *
	 * @param string $url
	 * @param bool   $expire
	 *
	 * @return string
	 */
	public function format_secure_s3_url( $url, $expire = false ) {
		if ( ! empty( $url ) ) {
			try {
				$secret_key = defined( 'WC_AM_AWS3_SECRET_ACCESS_KEY' ) ? WC_AM_AWS3_SECRET_ACCESS_KEY : WC_AM_ENCRYPTION()->decrypt( get_option( 'woocommerce_api_manager_amazon_s3_secret_access_key' ) );

				if ( $expire === false ) {
					$expire = time() + ( get_option( 'woocommerce_api_manager_url_expire' ) * MINUTE_IN_SECONDS );
				}

				$objectpath       = parse_url( $url, PHP_URL_PATH );
				$signature        = utf8_encode( "GET\n\n\n$expire\n" . $objectpath );
				$hashed_signature = base64_encode( hash_hmac( 'sha1', $signature, $secret_key, true ) );
				$query_string     = array(
					'AWSAccessKeyId' => defined( 'WC_AM_AWS3_ACCESS_KEY_ID' ) ? WC_AM_AWS3_ACCESS_KEY_ID : get_option( 'woocommerce_api_manager_amazon_s3_access_key_id' ),
					'Expires'        => $expire,
					'Signature'      => $hashed_signature
				);

				return $url . '?' . http_build_query( $query_string, '', '&' );
			} catch ( Exception $e ) {
				return '';
			}
		}

		return '';
	}

	/**
	 * Formats an Amazon S3 URL for secure Query String Request Authentication to the AWS S3 REST API using the AWS Signature Version 4.
	 * Works with either path-style request (s3.amazonaws.com/bucket), or virtual hosted-style (bucket.s3.amazonaws.com).
	 *
	 * Since The Amazon S3 servers are synced to UTC (Coordinated Universal Time), all pre-signed download URLs
	 * must also use UTC (Coordinated Universal Time). This results in an expiration time that is UTC + expiration time, so it varies a bit.
	 *
	 * @see   https://docs.aws.amazon.com/AmazonS3/latest/API/sigv4-query-string-auth.html
	 * @see   https://docs.aws.amazon.com/general/latest/gr/sigv4-create-canonical-request.html
	 * @see   https://docs.aws.amazon.com/general/latest/gr/sigv4-create-string-to-sign.html
	 * @see   https://docs.aws.amazon.com/general/latest/gr/sigv4-calculate-signature.html
	 * @see   https://docs.aws.amazon.com/general/latest/gr/sigv4-add-signature-to-request.html
	 * @see   https://docs.aws.amazon.com/AmazonS3/latest/dev/VirtualHosting.html
	 *
	 * @since 2.1
	 *
	 * @param string $url
	 *
	 * @return string
	 */
	public function format_secure_s3_v4_url( $url ) {
		if ( ! empty( $url ) ) {
			try {
				$aws_access_key_id = defined( 'WC_AM_AWS3_ACCESS_KEY_ID' ) ? WC_AM_AWS3_ACCESS_KEY_ID : get_option( 'woocommerce_api_manager_amazon_s3_access_key_id' );
				$secret_key        = defined( 'WC_AM_AWS3_SECRET_ACCESS_KEY' ) ? WC_AM_AWS3_SECRET_ACCESS_KEY : WC_AM_ENCRYPTION()->decrypt( get_option( 'woocommerce_api_manager_amazon_s3_secret_access_key' ) );

				if ( empty( $aws_access_key_id ) || empty( $secret_key ) ) {
					return $url;
				}

				$url_expire_time = get_option( 'woocommerce_api_manager_url_expire' );
				$expires         = ! empty( $url_expire_time ) ? $url_expire_time * DAY_IN_SECONDS : 1 * DAY_IN_SECONDS;
				$aws_region      = get_option( 'woocommerce_api_manager_aws_s3_region' );
				$timestamp       = new DateTime( 'UTC' ); // Must use UTC (Coordinated Universal Time).
				$time_text       = $timestamp->format( 'Ymd\THis\Z' );
				$date_text       = $timestamp->format( 'Ymd' );
				$parsed_url      = parse_url( $url );
				$host            = $parsed_url[ 'host' ];
				$path            = $parsed_url[ 'path' ]; // i.e. /toddlahman/simple-comments/137886/simple-comments.zip
				$encoded_uri     = str_replace( '%2F', '/', rawurlencode( $path ) );
				$algorithm       = 'AWS4-HMAC-SHA256';
				//$timestamp->sub( new DateInterval( 'PT' . 1 * DAY_IN_SECONDS . 'S' ) );
				//$timestamp->getTimestamp();
				//$bucket            = explode( '.', $host )[ 0 ];
				//$filename          = basename( $url );

				// Hostname for the S3 endpoint.
				$hostname              = $host;
				$header_string         = 'host:' . $hostname . "\n";
				$signed_headers_string = 'host';

				// Scope
				$scope = $date_text . '/' . $aws_region . '/s3/aws4_request';

				// Query String Parameters.
				$x_amz_params = array(
					'X-Amz-Algorithm'     => $algorithm,
					'X-Amz-Credential'    => $aws_access_key_id . '/' . $scope,
					'X-Amz-Date'          => $time_text,
					'X-Amz-Expires'       => $expires,
					'X-Amz-SignedHeaders' => $signed_headers_string
				);

				// Amazon wants these sorted.
				ksort( $x_amz_params );

				$query_string = '';

				foreach ( $x_amz_params as $key => $value ) {
					$query_string .= rawurlencode( $key ) . '=' . rawurlencode( $value ) . '&';
				}

				// Cut off the trailing ampersand (&) character.
				$query_string = substr( $query_string, 0, - 1 );

				// Hash time.
				$canonical_request = "GET\n" . $encoded_uri . "\n" . $query_string . "\n" . $header_string . "\n" . $signed_headers_string . "\nUNSIGNED-PAYLOAD";
				$string_to_sign    = $algorithm . "\n" . $time_text . "\n" . $scope . "\n" . hash( 'sha256', $canonical_request, false );
				$signing_key       = hash_hmac( 'sha256', 'aws4_request', hash_hmac( 'sha256', 's3', hash_hmac( 'sha256', $aws_region, hash_hmac( 'sha256', $date_text, 'AWS4' . $secret_key, true ), true ), true ), true );
				$signature         = hash_hmac( 'sha256', $string_to_sign, $signing_key );

				return $url . '?' . $query_string . '&X-Amz-Signature=' . $signature;
			} catch ( Exception $e ) {
				return '';
			}
		}

		return $url;
	}

	/**
	 * Determines if this is an Amazon S3 URL
	 *
	 * @since 1.3.2
	 *
	 * @param string $url
	 *
	 * @return boolean
	 */
	public function find_amazon_s3_in_url( $url ) {
		$result = preg_match( '!\b(amazonaws.com)\b!', $url );

		if ( $result == 1 ) {
			return true;
		}

		return false;
	}

	/**
	 * Strips the http:// or https:// prefix from a URL
	 *
	 * Prevents Apache from blocking URLs containing :, %3A, or %2F
	 * Apache exhibits this behavior as a bug, somtimes as default behavior and if
	 * AllowEncodedSlashes is set to Off|NoDecode.
	 * Apache as default may encode some values, such as : or $3A twice.
	 * https://issues.apache.org/bugzilla/show_bug.cgi?id=35256
	 * https://issues.apache.org/bugzilla/show_bug.cgi?id=34602
	 * https://issues.apache.org/bugzilla/show_bug.cgi?id=39746
	 * http://httpd.apache.org/docs/2.2/mod/core.html#allowencodedslashes
	 *
	 * @since 1.3
	 *
	 * @param string $url
	 *
	 * @return string Shortened URL
	 *
	 */
	public function remove_url_prefix( $url ) {
		$disallowed = array( 'http://', 'https://' );

		foreach ( $disallowed as $d ) {
			// If the prefix was found in the first position.
			if ( strpos( $url, $d ) === 0 ) {
				return str_replace( $d, '', $url );
			}
		}

		return $url;
		//return preg_replace( '!\b((http?|https)://)\b!', '', $url );
	}

	/**
	 * Nonce URL
	 *
	 * @see   http://codex.wordpress.org/Function_Reference/add_query_arg
	 * @since 1.2.1
	 *
	 * @param mixed $args string or array
	 *
	 * @return string
	 */
	public function nonce_url( $args ) {
		$action_url = wp_nonce_url( esc_url_raw( add_query_arg( $args ) ) );

		return $action_url;
	}

	/**
	 * Checks and cleans a URL, but does not add an http:// prefix if it doesn't have one.
	 * This is used for hosts that have security restrictions that block : or //.
	 *
	 * A number of characters are removed from the URL. If the URL is for displaying
	 * (the default behaviour) ampersands are also replaced. The 'clean_url' filter
	 * is applied to the returned cleaned URL.
	 *
	 * @since 1.3.6
	 *
	 * @param string $url       The URL to be cleaned.
	 * @param array  $protocols Optional. An array of acceptable protocols.
	 *                          Defaults to 'http', 'https', 'ftp', 'ftps', 'mailto', 'news', 'irc', 'gopher', 'nntp', 'feed', 'telnet', 'mms',
	 *                          'rtsp', 'svn' if not set.
	 * @param string $_context  Private. Use esc_url_raw() for database usage.
	 *
	 * @return string The cleaned $url after the 'clean_url' filter is applied.
	 * @uses  wp_kses_bad_protocol() To only permit protocols in the URL set
	 *        via $protocols or the common ones set in the function.
	 *
	 */
	public function esc_url_no_scheme( $url, $protocols = null, $_context = 'display' ) {
		$original_url = $url;

		if ( '' == $url ) {
			return $url;
		}

		$url   = preg_replace( '|[^a-z0-9-~+_.?#=!&;,/:%@$\|*\'()\\x80-\\xff]|i', '', $url );
		$strip = array( '%0d', '%0a', '%0D', '%0A' );
		$url   = _deep_replace( $strip, $url );
		$url   = str_replace( ';//', '://', $url );

		// Replace ampersands and single quotes only when displaying.
		if ( 'display' == $_context ) {
			$url = wp_kses_normalize_entities( $url );
			$url = str_replace( '&amp;', '&#038;', $url );
			$url = str_replace( "'", '&#039;', $url );
		}

		if ( '/' === $url[ 0 ] ) {
			$good_protocol_url = $url;
		} else {
			if ( ! is_array( $protocols ) ) {
				$protocols = wp_allowed_protocols();
			}

			$good_protocol_url = wp_kses_bad_protocol( $url, $protocols );

			if ( strtolower( $good_protocol_url ) != strtolower( $url ) ) {
				return '';
			}
		}

		/**
		 * Filter a string cleaned and escaped for output as a URL.
		 *
		 * @since 1.3.6
		 *
		 * @param string $good_protocol_url The cleaned URL to be returned.
		 * @param string $original_url      The URL prior to cleaning.
		 * @param string $_context          If 'display', replace ampersands and single quotes only.
		 */
		return apply_filters( 'clean_url_no_scheme', $good_protocol_url, $original_url, $_context );
	}

	/**
	 * Performs esc_url_no_scheme() for database usage, but does not add an http:// prefix if it doesn't have one.
	 * This is used for hosts that have security restrictions that block : or //.
	 *
	 * @since 1.3.6
	 *
	 * @param string $url       The URL to be cleaned.
	 * @param array  $protocols An array of acceptable protocols.
	 *
	 * @return string The cleaned URL.
	 * @uses  esc_url_no_scheme()
	 *
	 */
	public function esc_url_raw_no_scheme( $url, $protocols = null ) {
		return $this->esc_url_no_scheme( $url, $protocols, 'db' );
	}

	/**
	 * Returns the download URL for the product if it is an external URL, it does not match the local store URL, or an Amazon S3 URL, and it is a valid URL.
	 *
	 * @since 2.0
	 *
	 * @param int $product_id
	 *
	 * @return bool|string
	 */
	public function is_download_external_url( $product_id ) {
		$url = WC_AM_PRODUCT_DATA_STORE()->get_first_download_url( $product_id );

		if ( $url && wp_http_validate_url( $url ) ) {
			$parsed_home = @parse_url( get_home_url() );
			$parsed_url  = @parse_url( $url );
			$aws_url     = @parse_url( 'https://s3.amazonaws.com/' );
			$url_is_s3   = $this->find_amazon_s3_in_url( $url );

			if ( ! $url_is_s3 && strtolower( $parsed_url[ 'host' ] ) !== strtolower( $parsed_home[ 'host' ] ) && strtolower( $parsed_url[ 'host' ] ) !== $aws_url[ 'host' ] ) {
				return $url;
			}
		}

		return false;
	}

	/**
	 * Determines if $url is a URL based on the prefixes 'http://', 'https://'.
	 *
	 * @since 2.0.6
	 *
	 * @param $url
	 *
	 * @return bool
	 */
	public function is_url( $url ) {
		$sallowed = array( 'http://', 'https://' );

		foreach ( $sallowed as $d ) {
			// If the prefix was found in the first position.
			if ( strpos( $url, $d ) === 0 ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Determines if $url has an https:// prefix.
	 *
	 * @since 2.0.6
	 *
	 * @param $url
	 *
	 * @return bool
	 */
	public function is_https_url( $url ) {
		// If the prefix was found in the first position.
		return strpos( $url, 'https://' ) === 0;
	}

	/**
	 * Return true if on the root URL for the site, which should match the 'home' option value.
	 *
	 * @since 2.0.12
	 *
	 * @return bool
	 */
	public function is_home() {
		return is_front_page();
	}

}