<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( class_exists( 'JCK_SFR_Core_Autoloader' ) ) {
	return;
}

/**
 * JCK_SFR_Core_Autoloader.
 *
 * @class    JCK_SFR_Core_Autoloader
 * @version  1.0.1
 * @author   Iconic
 */
class JCK_SFR_Core_Autoloader {
	/**
	 * Single instance of the JCK_SFR_Core_Autoloader object.
	 *
	 * @var JCK_SFR_Core_Autoloader
	 */
	public static $single_instance = null;

	/**
	 * Class args.
	 *
	 * @var array
	 */
	public static $args = array();

	/**
	 * Creates/returns the single instance JCK_SFR_Core_Autoloader object.
	 *
	 * @return JCK_SFR_Core_Autoloader
	 */
	public static function run( $args = array() ) {
		if ( null === self::$single_instance ) {
			self::$args            = $args;
			self::$single_instance = new self();
		}

		return self::$single_instance;
	}

	/**
	 * Construct.
	 */
	private function __construct() {
		spl_autoload_register( array( __CLASS__, 'autoload' ) );
	}

	/**
	 * Autoloader
	 *
	 * Classes should reside within /inc and follow the format of
	 * Iconic_The_Name ~ class-the-name.php or {{class-prefix}}The_Name ~ class-the-name.php
	 */
	private static function autoload( $class_name ) {
		/**
		 * If the class being requested does not start with our prefix,
		 * we know it's not one in our project
		 */
		if ( 0 !== strpos( $class_name, self::$args['prefix'] ) ) {
			return;
		}

		$file_name = strtolower( str_replace(
			array( self::$args['prefix'], '_' ), // Prefix | Underscores
			array( '', '-' ),                    // Remove | Replace with hyphens
			$class_name
		) );

		$file = self::$args['inc_path'] . 'class-' . $file_name . '.php';

		// Include found file.
		if ( file_exists( $file ) ) {
			require( $file );

			return;
		}
	}
}
