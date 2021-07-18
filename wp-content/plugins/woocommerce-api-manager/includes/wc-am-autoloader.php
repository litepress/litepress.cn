<?php

defined( 'ABSPATH' ) || exit;

/**
 * WooCommerce API Manager Autoloader Class
 *
 * @package     WooCommerce API Manager/Autoloader
 * @author      Todd Lahman LLC
 * @copyright   Copyright (c) Todd Lahman LLC
 * @since       1.5
 *
 */
class WC_AM_Autoloader {

	/**
	 * @since 1.5
	 *
	 * WC_AM_Autoloader constructor.
	 */
	public function __construct() {
		if ( function_exists( "__autoload" ) ) {
			spl_autoload_register( "__autoload" );
		}

		spl_autoload_register( array( $this, 'autoload' ) );
	}

	/**
	 * Make class name lowercase, then replace underscores with dashes, and append a .php.
	 *
	 * @since 1.5
	 *
	 * @param string $class
	 *
	 * @return string
	 */
	private function get_file_name_from_class( $class ) {
		return str_replace( '_', '-', strtolower( $class ) ) . '.php';
	}

	/**
	 * Make sure the file is readable, then load it.
	 *
	 * @since 1.5
	 *
	 * @param string $path
	 *
	 * @return bool
	 */
	private function load_file( $path ) {
		if ( $path && is_readable( $path ) ) {
			require_once( $path );

			return true;
		}

		return false;
	}

	/**
	 * Autoload the class if it has not been loaded already.
	 *
	 * @since 1.5
	 *
	 * @param string $class_name
	 */
	private function autoload( $class_name ) {
		// If WC_AM_ is found at position 0, then this is the class we're looking for.
		if ( strpos( $class_name, 'WC_AM_' ) === 0 ) {
			$file = $this->get_file_name_from_class( $class_name );

			$paths = array(
				WCAM()->plugin_path() . '/includes/' . $file,
				WCAM()->plugin_path() . '/includes/admin/' . $file,
				WCAM()->plugin_path() . '/includes/api/' . $file,
				WCAM()->plugin_path() . '/includes/data-stores/' . $file,
			);

			foreach ( $paths as $key => $path ) {
				$this->load_file( $path );
			}
		}
	}
}

new WC_AM_Autoloader();