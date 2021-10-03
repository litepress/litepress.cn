<?php

namespace LitePress\Docs\Translate;

use LitePress\I18n\i18n;
use WP_Post;

class Plugin {

	/**
	 * @var Plugin|null The singleton instance.
	 */
	private static ?Plugin $instance = null;

	private WP_Post $wp_post;

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
		add_filter( 'the_title', array( $this, 'translate' ), 1 );
		add_filter( 'the_content', array( $this, 'translate' ), 1 );
	}

	public function translate( string $content ): string {
		if ( empty( $this->wp_post ) ) {
			$post_id = get_the_ID();
			if ( empty( $post_id ) ) {
				return $content;
			}
			$this->wp_post = get_post( $post_id );

			$slug = "docs/docs-{$this->wp_post->post_name}/body";

			add_action( 'wp_footer', function () use ( $slug ) {
				echo <<<JS
<script>
const gp_project_path = '{$slug}';
</script>
JS;
			} );
		}

		$slug = "docs/docs-{$this->wp_post->post_name}/body";

		return i18n::get_instance()->translate( '', $content, $slug, true, true );
	}

}
