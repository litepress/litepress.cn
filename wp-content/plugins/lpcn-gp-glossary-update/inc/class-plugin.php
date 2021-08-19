<?php

namespace LitePress\GlotPress\Glossary_Update;

use DiDom\Document;
use DiDom\Query;
use GP;
use LitePress\Logger\Logger;
use function LitePress\WP_Http\wp_remote_get;

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
		if ( isset( $_GET['gu'] ) ) {
			add_action( 'wp_loaded', array( $this, 'job' ) );
		}
	}

	public function job() {
		$args = array(
			'timeout' => 20,
		);
		$r    = wp_remote_get( 'http://wordpress.org/support/article/glossary/', $args );
		if ( is_wp_error( $r ) ) {
			Logger::error( 'GlotPress', '从w.org下载术语表失败：' . $r->get_error_message() );

			return;
		}

		$document = new Document( $r['body'] );

		$posts = $document->find( '//*[@class="container"]/*[@class="table-of-contents"]/ul/li', Query::TYPE_XPATH );


		foreach ( $posts as $post ) {
			$item = $post->find( 'a' )[0]->text();

			$exist = GP::$glossary_entry->find( array( 'term' => $item ) );
			if ( empty( $exist ) ) {
				GP::$glossary_entry->create( array(
					'glossary_id'    => 2,
					'term'           => $item,
					'part_of_speech' => 'noun',
					'last_edited_by' => 517,
				) );
			}
		}
	}

}
