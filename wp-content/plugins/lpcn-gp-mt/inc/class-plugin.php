<?php

namespace LitePress\GlotPress\MT;

use GP;
use GP_Route;

class Plugin {

	/**
	 * @var Plugin|null The singleton instance.
	 */
	private static ?Plugin $instance = null;

	/**
	 * Instantiates a new Plugin object.
	 */
	private function __construct() {
		add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );
	}

	/**
	 * Initializes the plugin.
	 */
	public function plugins_loaded() {
		GP::$router->add( "/gp-mt/(.+?)", array( Translate::class, 'schedule_gp_mt' ), 'get' );
		GP::$router->add( "/gp-mt/(.+?)", array( Translate::class, 'schedule_gp_mt' ), 'post' );
		//if ( isset( $_GET['testo'] ) ) {
		//add_action( 'gp_originals_imported', array( $this, 'schedule_gp_mt' ), 999 );
		add_action( 'lpcn_schedule_gp_mt', array( Translate::class, 'job' ), 999, 3 );
		//}
	}

	/**
	 * Returns always the same instance of this plugin.
	 *
	 * @return Plugin
	 */
	public static function get_instance(): Plugin {
		if ( ! ( self::$instance instanceof Plugin ) ) {
			self::$instance = new Plugin();
		}

		return self::$instance;
	}



}
