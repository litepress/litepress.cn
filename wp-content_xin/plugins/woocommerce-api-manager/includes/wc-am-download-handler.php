<?php

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * WooCommerce API Manager Download Handler Class
 *
 * Time limited hash authentication used for download security.
 *
 * @since       2.0
 *
 * @author      Todd Lahman LLC
 * @copyright   Copyright (c) Todd Lahman LLC
 * @package     WooCommerce API Manager/Download Handler
 */
class WC_AM_Download_Handler {

	/**
	 * @var null
	 */
	private static $_instance = null;

	/**
	 * @static
	 * @return \WC_AM_Download_Handler
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Constructor
	 */
	private function __construct() {
		if ( isset( $_GET[ 'user_id' ], $_GET[ 'am_download_file' ], $_GET[ 'am_order' ], $_GET[ 'remote_url' ] ) ) {
			add_action( 'init', array( $this, 'download_product' ) );
		}

		add_action( 'woocommerce_download_file_redirect', array( $this, 'download_file_redirect' ), 10, 2 );
		add_action( 'woocommerce_download_file_xsendfile', array( $this, 'download_file_xsendfile' ), 10, 2 );
		add_action( 'woocommerce_download_file_force', array( $this, 'download_file_force' ), 10, 2 );
	}

	/**
	 * Check if we need to download a file and check validity.
	 */
	public function download_product() {
		$user_id    = absint( $_GET[ 'user_id' ] );
		$product_id = absint( $_GET[ 'am_download_file' ] );
		$order_id   = wc_clean( $_GET[ 'am_order' ] );
		$product    = WC_AM_PRODUCT_DATA_STORE()->get_product_object( $product_id );
		$remote_url = $_GET[ 'remote_url' ] == 'yes' ? true : false;
		$data_store = WC_Data_Store::load( 'customer-download' );

		if ( ! is_object( $product ) || empty( $_GET[ 'hname' ] ) || empty( $_GET[ 'hkey' ] ) || empty( $_GET[ 'hexpires' ] ) ) {
			$this->download_error( esc_html__( 'Invalid download link.', 'woocommerce-api-manager' ) );
		}

		$hash_name = $_GET[ 'hname' ];
		$password  = $_GET[ 'hkey' ];
		$expires   = (int) $_GET[ 'hexpires' ];

		// Check if hash is expired.
		if ( empty( $expires ) || WC_AM_HASH()->is_expired( $expires, $user_id, $hash_name ) ) {
			$this->download_error( esc_html__( 'Access expired.', 'woocommerce-api-manager' ) );
		}

		// Hash authentication.
		if ( WC_AM_HASH()->password_verify( $password, $hash_name, $user_id ) === false ) {
			$this->download_error( esc_html__( 'Authentication failed.', 'woocommerce-api-manager' ) );
		}

		// Just in case there is a download file on the product, but there are no downloadable product permissions on the order or WC Subscription.
		$file_path = WC_AM_PRODUCT_DATA_STORE()->get_first_download_url( $product_id );

		/**
		 * If this is a remote URL, send to download URL immediately.
		 *
		 * @since 2.0
		 */
		if ( $remote_url ) {
			if ( ! $file_path ) {
				// Was the $product_id sent in the request?
				$this->download_error( esc_html__( 'No file defined', 'woocommerce-api-manager' ) );
			}

			do_action( 'wc_am_download_product', $user_id, $order_id, $product_id );

			header( 'Location: ' . esc_url( $file_path ) );
			exit;
		} else { // This is a local server download.
			if ( ! $file_path ) {
				$download_ids = $data_store->get_downloads( array(
					                                            'user_id'    => $user_id,
					                                            'order_id'   => $order_id,
					                                            'product_id' => $product_id,
					                                            'orderby'    => 'downloads_remaining',
					                                            'order'      => 'DESC',
					                                            'limit'      => 1,
					                                            'return'     => 'ids',
				                                            ) );

				if ( empty( $download_ids ) ) {
					$this->download_error( esc_html__( 'Invalid download link.', 'woocommerce-api-manager' ) );
				}

				$download    = new WC_Customer_Download( current( $download_ids ) );
				$download_id = $download->get_download_id();
				$file_path   = $product->get_file_download_path( $download_id );

				$this->check_order_is_valid( $download );

				if ( WCAM()->get_wc_version() < '3.3' ) {
					$count = $download->get_download_count();
					//$remaining = $download->get_downloads_remaining();
					$download->set_download_count( $count + 1 );
					$download->save();
				}

				if ( WCAM()->get_wc_version() >= '3.3' ) {
					// Track the download in logs and change remaining/counts.
					$current_user_id = $user_id;
					$ip_address      = WC_Geolocation::get_ip_address();

					$parsed_file_path = $this->parse_file_path( $file_path );

					if ( $parsed_file_path ) {
						$download_range = $this->get_download_range( @filesize( $parsed_file_path[ 'file_path' ] ) );

						if ( ! $download_range[ 'is_range_request' ] ) {
							$download->track_download( $current_user_id > 0 ? $current_user_id : null, ! empty( $ip_address ) ? $ip_address : null );
						}
					}
				}
			}

			if ( ! $file_path ) {
				// Was the $product_id sent in the request?
				$this->download_error( esc_html__( 'No file defined', 'woocommerce-api-manager' ) );
			}

			do_action( 'wc_am_download_product', $user_id, $order_id, $product_id );

			//$this->download( $file_path, $download->get_product_id() );
			$this->download( $file_path, $product_id );
		}
	}

	/**
	 * Check if an order is valid for downloading from.
	 *
	 * @param WC_Customer_Download $download
	 */
	private function check_order_is_valid( $download ) {
		if ( $download->get_order_id() ) {
			$order = wc_get_order( $download->get_order_id() );

			if ( $order && ! $order->is_download_permitted() ) {
				$this->download_error( esc_html__( 'Invalid order.', 'woocommerce-api-manager' ), '', 403 );
			}
		}
	}

	/**
	 * Check if there are downloads remaining.
	 *
	 * @deprecated since 2.0
	 *
	 * @param WC_Customer_Download $download
	 */
	private function check_downloads_remaining( $download ) {
		if ( '' !== $download->get_downloads_remaining() && 0 >= $download->get_downloads_remaining() ) {
			$this->download_error( esc_html__( 'Sorry, you have reached your download limit for this file', 'woocommerce-api-manager' ), '', 403 );
		}
	}

	/**
	 * Check if the download has expired.
	 *
	 * @deprecated since 2.0
	 *
	 * @param WC_Customer_Download $download
	 */
	private function check_download_expiry( $download ) {
		if ( ! is_null( $download->get_access_expires() ) && $download->get_access_expires()->getTimestamp() < strtotime( 'midnight', current_time( 'timestamp', true ) ) ) {
			$this->download_error( esc_html__( 'Sorry, this download has expired', 'woocommerce-api-manager' ), '', 403 );
		}
	}

	/**
	 * Download a file - hook into init function.
	 *
	 * @param string  $file_path  URL to file
	 * @param integer $product_id of the product being downloaded
	 */
	public function download( $file_path, $product_id ) {
		$filename = basename( $file_path );

		if ( strpos( $filename, '?' ) !== false ) {
			$filename = current( explode( '?', $filename ) );
		}

		$filename             = apply_filters( 'woocommerce_file_download_filename', $filename, $product_id );
		$file_download_method = apply_filters( 'woocommerce_file_download_method', get_option( 'woocommerce_file_download_method', 'force' ), $product_id );

		// Add action to prevent issues in IE
		add_action( 'nocache_headers', array( $this, 'ie_nocache_headers_fix' ) );
		// Trigger download via one of the methods
		do_action( 'woocommerce_download_file_' . $file_download_method, $file_path, $filename );
	}

	/**
	 * Redirect to a file to start the download.
	 *
	 * @param string $file_path
	 * @param string $filename
	 */
	public function download_file_redirect( $file_path, $filename = '' ) {
		header( 'Location: ' . $file_path );
		exit;
	}

	/**
	 * Parse file path and see if its remote or local.
	 *
	 * @param string $file_path
	 *
	 * @return array
	 */
	public function parse_file_path( $file_path ) {
		$wp_uploads     = wp_upload_dir();
		$wp_uploads_dir = $wp_uploads[ 'basedir' ];
		$wp_uploads_url = $wp_uploads[ 'baseurl' ];

		/**
		 * Replace uploads dir, site url etc with absolute counterparts if we can.
		 * Note the str_replace on site_url is on purpose, so if https is forced
		 * via filters we can still do the string replacement on a HTTP file.
		 */
		$replacements = array(
			$wp_uploads_url                                                   => $wp_uploads_dir,
			network_site_url( '/', 'https' )                                  => ABSPATH,
			str_replace( 'https:', 'http:', network_site_url( '/', 'http' ) ) => ABSPATH,
			site_url( '/', 'https' )                                          => ABSPATH,
			str_replace( 'https:', 'http:', site_url( '/', 'http' ) )         => ABSPATH,
		);

		$file_path        = str_replace( array_keys( $replacements ), array_values( $replacements ), $file_path );
		$parsed_file_path = wp_parse_url( $file_path );
		$remote_file      = true;

		// Paths that begin with '//' are always remote URLs.
		if ( '//' === substr( $file_path, 0, 2 ) ) {
			return array(
				'remote_file' => true,
				'file_path'   => is_ssl() ? 'https:' . $file_path : 'http:' . $file_path,
			);
		}

		// See if path needs an abspath prepended to work
		if ( file_exists( ABSPATH . $file_path ) ) {
			$remote_file = false;
			$file_path   = ABSPATH . $file_path;
		} elseif ( '/wp-content' === substr( $file_path, 0, 11 ) ) {
			$remote_file = false;
			$file_path   = realpath( WP_CONTENT_DIR . substr( $file_path, 11 ) );
			// Check if we have an absolute path
		} elseif ( ( ! isset( $parsed_file_path[ 'scheme' ] ) || ! in_array( $parsed_file_path[ 'scheme' ], array(
					'http',
					'https',
					'ftp'
				), true ) ) && isset( $parsed_file_path[ 'path' ] ) && file_exists( $parsed_file_path[ 'path' ] ) ) {
			$remote_file = false;
			$file_path   = $parsed_file_path[ 'path' ];
		}

		return array(
			'remote_file' => $remote_file,
			'file_path'   => $file_path,
		);
	}

	/**
	 * Download a file using X-Sendfile, X-Lighttpd-Sendfile, or X-Accel-Redirect if available.
	 *
	 * @param string $file_path
	 * @param string $filename
	 */
	public function download_file_xsendfile( $file_path, $filename ) {
		$parsed_file_path = $this->parse_file_path( $file_path );

		if ( function_exists( 'apache_get_modules' ) && in_array( 'mod_xsendfile', apache_get_modules(), true ) ) {
			$this->download_headers( $parsed_file_path[ 'file_path' ], $filename );
			header( "X-Sendfile: " . $parsed_file_path[ 'file_path' ] );
			exit;
		} elseif ( stristr( getenv( 'SERVER_SOFTWARE' ), 'lighttpd' ) ) {
			$this->download_headers( $parsed_file_path[ 'file_path' ], $filename );
			header( "X-Lighttpd-Sendfile: " . $parsed_file_path[ 'file_path' ] );
			exit;
		} elseif ( stristr( getenv( 'SERVER_SOFTWARE' ), 'nginx' ) || stristr( getenv( 'SERVER_SOFTWARE' ), 'cherokee' ) ) {
			$this->download_headers( $parsed_file_path[ 'file_path' ], $filename );

			$xsendfile_path = trim( preg_replace( '`^' . str_replace( '\\', '/', getcwd() ) . '`', '', $parsed_file_path[ 'file_path' ] ), '/' );

			header( "X-Accel-Redirect: /$xsendfile_path" );
			exit;
		}

		// Fallback
		$this->download_file_force( $file_path, $filename );
	}

	/**
	 * Parse the HTTP_RANGE request from iOS devices.
	 * Does not support multi-range requests.
	 *
	 * @param int $file_size        Size of file in bytes.
	 *
	 * @return array {
	 *     Information about range download request: beginning and length of
	 *     file chunk, whether the range is valid/supported and whether the request is a range request.
	 *
	 * @type int  $start            Byte offset of the beginning of the range. Default 0.
	 * @type int  $length           Length of the requested file chunk in bytes. Optional.
	 * @type bool $is_range_valid   Whether the requested range is a valid and supported range.
	 * @type bool $is_range_request Whether the request is a range request.
	 * }
	 */
	protected function get_download_range( $file_size ) {
		$start          = 0;
		$download_range = array(
			'start'            => $start,
			'is_range_valid'   => false,
			'is_range_request' => false,
		);

		if ( ! $file_size ) {
			return $download_range;
		}

		$end                        = $file_size - 1;
		$download_range[ 'length' ] = $file_size;

		if ( isset( $_SERVER[ 'HTTP_RANGE' ] ) ) { // @codingStandardsIgnoreLine.
			$http_range                           = sanitize_text_field( wp_unslash( $_SERVER[ 'HTTP_RANGE' ] ) ); // WPCS: input var ok.
			$download_range[ 'is_range_request' ] = true;
			$c_start                              = $start;
			$c_end                                = $end;

			// Extract the range string.
			list( , $range ) = explode( '=', $http_range, 2 );

			// Make sure the client hasn't sent us a multibyte range.
			if ( strpos( $range, ',' ) !== false ) {
				return $download_range;
			}

			/*
			 * If the range starts with an '-' we start from the beginning, else forward the file pointer
			 * and make sure to get the end byte if specified.
			 */
			if ( '-' === $range[ 0 ] ) {
				// The n-number of the last bytes is requested.
				$c_start = $file_size - substr( $range, 1 );
			} else {
				$range   = explode( '-', $range );
				$c_start = ( isset( $range[ 0 ] ) && is_numeric( $range[ 0 ] ) ) ? (int) $range[ 0 ] : 0;
				$c_end   = ( isset( $range[ 1 ] ) && is_numeric( $range[ 1 ] ) ) ? (int) $range[ 1 ] : $file_size;
			}

			/*
			 * Check the range and make sure it's treated according to the specs: http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html.
			 * End bytes can not be larger than $end.
			 */
			$c_end = ( $c_end > $end ) ? $end : $c_end;

			// Validate the requested range and return an error if it's not correct.
			if ( $c_start > $c_end || $c_start > $file_size - 1 || $c_end >= $file_size ) {
				return $download_range;
			}

			$start                              = $c_start;
			$end                                = $c_end;
			$length                             = $end - $start + 1;
			$download_range[ 'start' ]          = $start;
			$download_range[ 'length' ]         = $length;
			$download_range[ 'is_range_valid' ] = true;
		}

		return $download_range;
	}

	/**
	 * Force download - this is the default method.
	 *
	 * @param string $file_path
	 * @param string $filename
	 */
	public function download_file_force( $file_path, $filename ) {
		$parsed_file_path = $this->parse_file_path( $file_path );
		$download_range   = $this->get_download_range( @filesize( $parsed_file_path[ 'file_path' ] ) ); // @codingStandardsIgnoreLine.

		$this->download_headers( $parsed_file_path[ 'file_path' ], $filename, $download_range );

		$start  = isset( $download_range[ 'start' ] ) ? $download_range[ 'start' ] : 0;
		$length = isset( $download_range[ 'length' ] ) ? $download_range[ 'length' ] : 0;

		if ( ! $this->readfile_chunked( $parsed_file_path[ 'file_path' ], $start, $length ) ) {
			if ( $parsed_file_path[ 'remote_file' ] ) {
				$this->download_file_redirect( $file_path );
			} else {
				$this->download_error( esc_html__( 'File not found', 'woocommerce-api-manager' ) );
			}
		}

		exit;
	}

	/**
	 * Get content type of a download.
	 *
	 * @param string $file_path
	 *
	 * @return string
	 */
	private function get_download_content_type( $file_path ) {
		$file_extension = strtolower( substr( strrchr( $file_path, "." ), 1 ) );
		$ctype          = "application/force-download";

		foreach ( get_allowed_mime_types() as $mime => $type ) {
			$mimes = explode( '|', $mime );

			if ( in_array( $file_extension, $mimes, true ) ) {
				$ctype = $type;
				break;
			}
		}

		return $ctype;
	}

	/**
	 * Set headers for the download.
	 *
	 * @param string $file_path
	 * @param string $filename
	 * @param array  $download_range Array containing info about range download request (see {@see get_download_range} for structure).
	 */
	private function download_headers( $file_path, $filename, $download_range = array() ) {
		$this->check_server_config();
		$this->clean_buffers();
		wc_nocache_headers();

		header( 'X-Robots-Tag: noindex, nofollow', true );
		header( 'Content-Type: ' . $this->get_download_content_type( $file_path ) );
		header( 'Content-Description: File Transfer' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '";' );
		header( 'Content-Transfer-Encoding: binary' );

		$file_size = @filesize( $file_path ); // phpcs:ignore Generic.PHP.NoSilencedErrors.Discouraged
		if ( $file_size ) {
			if ( isset( $download_range[ 'is_range_request' ] ) && true === $download_range[ 'is_range_request' ] ) {
				if ( false === $download_range[ 'is_range_valid' ] ) {
					header( 'HTTP/1.1 416 Requested Range Not Satisfiable' );
					header( 'Content-Range: bytes 0-' . ( $file_size - 1 ) . '/' . $file_size );
					exit;
				}

				$start  = $download_range[ 'start' ];
				$end    = $download_range[ 'start' ] + $download_range[ 'length' ] - 1;
				$length = $download_range[ 'length' ];

				header( 'HTTP/1.1 206 Partial Content' );
				header( "Accept-Ranges: 0-$file_size" );
				header( "Content-Range: bytes $start-$end/$file_size" );
				header( "Content-Length: $length" );
			} else {
				header( 'Content-Length: ' . $file_size );
			}
		}
	}

	/**
	 * Check and set certain server config variables to ensure downloads work as intended.
	 */
	private function check_server_config() {
		wc_set_time_limit( 0 );

		if ( function_exists( 'apache_setenv' ) ) {
			@apache_setenv( 'no-gzip', 1 ); // phpcs:ignore Generic.PHP.NoSilencedErrors.Discouraged, WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_apache_setenv
		}

		@ini_set( 'zlib.output_compression', 'Off' ); // phpcs:ignore Generic.PHP.NoSilencedErrors.Discouraged, WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_ini_set
		@session_write_close(); // phpcs:ignore Generic.PHP.NoSilencedErrors.Discouraged, WordPress.VIP.SessionFunctionsUsage.session_session_write_close
	}

	/**
	 * Clean all output buffers.
	 *
	 * Can prevent errors, for example: transfer closed with 3 bytes remaining to read.
	 */
	private function clean_buffers() {
		if ( ob_get_level() ) {
			$levels = ob_get_level();

			for ( $i = 0; $i < $levels; $i ++ ) {
				@ob_end_clean(); // phpcs:ignore Generic.PHP.NoSilencedErrors.Discouraged
			}
		} else {
			@ob_end_clean(); // phpcs:ignore Generic.PHP.NoSilencedErrors.Discouraged
		}
	}

	/**
	 * Read file chunked.
	 *
	 * Reads file in chunks so big downloads are possible without changing PHP.INI - http://codeigniter.com/wiki/Download_helper_for_large_files/.
	 *
	 * @param string $file
	 * @param int    $start  Byte offset/position of the beginning from which to read from the file.
	 * @param int    $length Length of the chunk to be read from the file in bytes, 0 means full file.
	 *
	 * @return bool Success or fail
	 */
	public function readfile_chunked( $file, $start = 0, $length = 0 ) {
		if ( ! defined( 'WC_CHUNK_SIZE' ) ) {
			define( 'WC_CHUNK_SIZE', 1024 * 1024 );
		}
		$handle = @fopen( $file, 'r' ); // phpcs:ignore Generic.PHP.NoSilencedErrors.Discouraged, WordPress.WP.AlternativeFunctions.file_system_read_fopen

		if ( false === $handle ) {
			return false;
		}

		if ( ! $length ) {
			$length = @filesize( $file ); // phpcs:ignore Generic.PHP.NoSilencedErrors.Discouraged
		}

		$read_length = (int) WC_CHUNK_SIZE;

		if ( $length ) {
			$end = $start + $length - 1;

			@fseek( $handle, $start ); // phpcs:ignore Generic.PHP.NoSilencedErrors.Discouraged

			$p = @ftell( $handle ); // phpcs:ignore Generic.PHP.NoSilencedErrors.Discouraged

			while ( ! @feof( $handle ) && $p <= $end ) { // phpcs:ignore Generic.PHP.NoSilencedErrors.Discouraged
				// Don't run past the end of file.
				if ( $p + $read_length > $end ) {
					$read_length = $end - $p + 1;
				}

				echo @fread( $handle, $read_length ); // phpcs:ignore Generic.PHP.NoSilencedErrors.Discouraged, WordPress.XSS.EscapeOutput.OutputNotEscaped, WordPress.WP.AlternativeFunctions.file_system_read_fread

				$p = @ftell( $handle ); // phpcs:ignore Generic.PHP.NoSilencedErrors.Discouraged

				if ( ob_get_length() ) {
					ob_flush();
					flush();
				}
			}
		} else {
			while ( ! @feof( $handle ) ) { // @codingStandardsIgnoreLine.
				echo @fread( $handle, $read_length ); // @codingStandardsIgnoreLine.

				if ( ob_get_length() ) {
					ob_flush();
					flush();
				}
			}
		}

		return @fclose( $handle ); // phpcs:ignore Generic.PHP.NoSilencedErrors.Discouraged, WordPress.WP.AlternativeFunctions.file_system_read_fclose
	}

	/**
	 * Filter headers for IE to fix issues over SSL.
	 *
	 * IE bug prevents download via SSL when Cache Control and Pragma no-cache headers set.
	 *
	 * @param array $headers
	 *
	 * @return array
	 */
	public function ie_nocache_headers_fix( $headers ) {
		if ( is_ssl() && ! empty( $GLOBALS[ 'is_IE' ] ) ) {
			$headers[ 'Cache-Control' ] = 'private';
			unset( $headers[ 'Pragma' ] );
		}

		return $headers;
	}

	/**
	 * Die with an error message if the download fails.
	 *
	 * @param string  $message
	 * @param string  $title
	 * @param integer $status
	 */
	private function download_error( $message, $title = '', $status = 404 ) {
		if ( strpos( $message, '<a ' ) === false ) {
			$message .= ' <a href="' . esc_url( wc_get_page_permalink( 'shop' ) ) . '" class="wc-forward">' . esc_html__( 'Go to shop', 'woocommerce-api-manager' ) . '</a>';
		}

		wp_die( $message, $title, array( 'response' => $status ) );
	}

} // end of class