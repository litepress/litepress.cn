<?php

namespace LitePress\GlotPress\Generate_Pack;

use GP;

class Plugin {

	const ALLOWED_TYPE = array(
		'plugin' => 1,
		'theme'  => 2,
		'other'  => 5,
	);

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

	/**
	 * Initializes the plugin.
	 */
	public function plugins_loaded() {
		add_action( 'lpcn_generate_all_language_pack', array( $this, 'generate_all_language_pack' ) );

		$timestamp = wp_next_scheduled( 'lpcn_generate_all_language_pack' );
		if ( empty( $timestamp ) ) {
			wp_schedule_event( strtotime( date( 'Y-m-d', strtotime( '+1 day' ) ) ) + 3600, 'daily', 'lpcn_generate_all_language_pack' );
		}
	}

	public function generate_all_language_pack() {
		$generate_pack = new Generate_Pack();

		foreach ( self::ALLOWED_TYPE as $type => $id ) {
			$products = GP::$project->find_many( array( 'parent_project_id' => $id ) );

			foreach ( $products as $product ) {
				$version = gp_get_meta( 'project', $product->id, 'version' ) ?: '';

				$generate_pack->job(
					$product->slug,
					$type,
					$version,
				);
			}
		}
	}

}
