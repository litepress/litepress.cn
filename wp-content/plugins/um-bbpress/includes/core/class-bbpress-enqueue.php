<?php
namespace um_ext\um_bbpress\core;


if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class bbPress_Enqueue
 * @package um_ext\um_bbpress\core
 */
class bbPress_Enqueue {


	/**
	 * bbPress_Enqueue constructor.
	 */
	function __construct() {
		add_action('wp_enqueue_scripts',  array(&$this, 'wp_enqueue_scripts'), 0);
	}


	/**
	 *
	 */
	function wp_enqueue_scripts() {
		$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG || defined( 'UM_SCRIPT_DEBUG' ) ) ? '' : '.min';
		wp_register_style('um_bbpress', um_bbpress_url . 'assets/css/um-bbpress' . $suffix . '.css', array(), um_bbpress_version );
	}

}