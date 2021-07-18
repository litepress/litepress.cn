<?php

use Elementor\Core\Files\CSS\Post as Post_CSS;
use Elementor\Plugin;

/**
 * Elementor compatibility Class.
 *
 * @since 4.6.11
 */
class JCK_SFR_Compat_Elementor {
	/**
	 * Init.
	 */
	public static function run() {
		if ( ! JCK_SFR_Core_Helpers::is_plugin_active( 'elementor/elementor.php' ) ) {
			return;
		}

		add_filter( 'get_post_metadata', array( __CLASS__, 'elementor_edit_mode' ), 10, 4 );
		add_filter( 'elementor/documents/get/post_id', array( __CLASS__, 'elementor_document_id' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_styles' ) );
	}

	/**
	 * Ensure elementor edit mode refers to the parent, not the single post.
	 *
	 * This will check whether the parent archive page uses elementor, and
	 * assign the same styling to individual requests.
	 *
	 * @param $value
	 * @param $object_id
	 * @param $meta_key
	 * @param $single
	 *
	 * @return mixed
	 */
	public static function elementor_edit_mode( $value, $object_id, $meta_key, $single ) {
		if ( '_elementor_edit_mode' !== $meta_key ) {
			return $value;
		}

		if ( JCK_SFR_Post_Types::$key !== get_post_type( $object_id ) ) {
			return $value;
		}

		$archive_id = JCK_SFR_Post_Types::get_archive_page_id();

		if ( ! $archive_id ) {
			return $value;
		}

		return get_post_meta( $archive_id, $meta_key, $single );
	}

	/**
	 * Update single request document ID.
	 *
	 * @param $post_id
	 *
	 * @return bool|int
	 */
	public static function elementor_document_id( $post_id ) {
		$post_type = get_post_type( $post_id );

		if ( ! JCK_SFR_Post_Types::is_type( 'single' ) || $post_type !== JCK_SFR_Post_Types::$key ) {
			return $post_id;
		}

		$archive_id = JCK_SFR_Post_Types::get_archive_page_id();

		if ( ! $archive_id ) {
			return $post_id;
		}

		return $archive_id;
	}

	/**
	 * Enqueue archive page styles on single page.
	 */
	public static function enqueue_styles() {
		if ( Plugin::$instance->preview->is_preview_mode() || ! JCK_SFR_Post_Types::is_type( 'single' ) ) {
			return;
		}

		$archive_id = JCK_SFR_Post_Types::get_archive_page_id();

		if ( ! $archive_id ) {
			return;
		}

		$css_file = Post_CSS::create( $archive_id );
		$css_file->enqueue();
	}
}
