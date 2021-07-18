<?php

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * WooCommerce API Manager API Log Class
 *
 * @since       2.0
 *
 * @author      Todd Lahman LLC
 * @copyright   Copyright (c) Todd Lahman LLC
 * @package     WooCommerce API Manager/Log
 */
class WC_AM_Log {

	/**
	 * @var null
	 */
	private static $_instance = null;

	/**
	 * @static
	 * @return \WC_AM_Log
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	private function __construct() { }

	/**
	 * Logs debug messages for the APIs.
	 *
	 * @since 2.0
	 *
	 * @param string $message
	 */
	public function api_debug_log( $message ) {
		$logger = wc_get_logger();

		$logger->debug( $message . PHP_EOL, array( 'source' => 'wc-am-api-query-log' ) );
	}

	/**
	 * Logs error messages for the APIs.
	 *
	 * @since 2.0
	 *
	 * @param string $message
	 */
	public function api_error_log( $message ) {
		$logger = wc_get_logger();

		$logger->error( $message . PHP_EOL, array( 'source' => 'wc-am-api-error-log' ) );
	}

	/**
	 * Logs response messages for the APIs.
	 *
	 * @since 2.0
	 *
	 * @param string $message
	 */
	public function api_response_log( $message ) {
		$logger = wc_get_logger();

		$logger->info( $message . PHP_EOL, array( 'source' => 'wc-am-api-response-log' ) );
	}

	/**
	 * Logs any error.
	 *
	 * @since 2.3.2
	 *
	 * @param string $message
	 */
	public function log_error( $message ) {
		$logger = wc_get_logger();

		$logger->error( $message . PHP_EOL, array( 'source' => 'wc-am-error-log' ) );
	}

	/**
	 * Logs any info.
	 *
	 * @since 2.3.2
	 *
	 * @param string $message
	 */
	public function log_info( $message ) {
		$logger = wc_get_logger();

		$logger->info( $message . PHP_EOL, array( 'source' => 'wc-am-info-log' ) );
	}

	/**
	 * Logs test messages.
	 *
	 * @since 2.0
	 *
	 * @param string $message
	 */
	public function test_log( $message ) {
		$logger = wc_get_logger();

		$logger->info( $message . PHP_EOL, array( 'source' => 'wc-am-api-test-log' ) );
	}

}