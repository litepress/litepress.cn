<?php

namespace LitePress\GlotPress\MT;

use Stichoza\GoogleTranslate\GoogleTranslate;

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
		if ( isset( $_GET['debug'] ) ) {
			//add_action('gp_translations_imported', 9999);
			$args = array(
				'verify' => false
			);
			$tr   = new GoogleTranslate( 'zh-CN', null, $args );
			$tr->setUrl( 'https://translate.google.cn/translate_a/single' );
			$tr->setTarget( 'zh' );
			//echo $tr->translate( 'If a new user is created by WordPress, a new password will be randomly generated and the new user&#8217;s role will be set as %s. Manually changing the new user&#8217;s details will be necessary.' );
			//exit;
		}
	}

}
