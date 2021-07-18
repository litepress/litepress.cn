<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Astra compatibility Class.
 */
class JCK_SFR_Compat_Astra {
	/**
	 * Init.
	 */
	public static function run() {
		$theme = wp_get_theme();

		if ( 'astra' !== $theme->template ) {
			return;
		}

		add_filter( 'get_post_metadata', array( __CLASS__, 'assign_parent_meta' ), 10, 4 );
		add_action( 'do_meta_boxes', array( __CLASS__, 'remove_metabox' ) );
	}

	/**
	 * Filter single feature request meta so it returns parent settings.
	 *
	 * @param $value
	 * @param $object_id
	 * @param $meta_key
	 * @param $single
	 *
	 * @return mixed
	 */
	public static function assign_parent_meta( $value, $object_id, $meta_key, $single ) {
		if ( is_admin() ) {
			return $value;
		}

		$parent_id = self::get_feature_request_parent_id();

		if ( ! $parent_id || $object_id === $parent_id ) {
			return $value;
		}

		$filter_meta = array(
			'site-sidebar-layout',
			'site-content-layout',
			'site-post-title',
			'footer-sml-layout',
			'theme-transparent-header-meta',
		);

		if ( 0 !== strpos( $meta_key, 'ast-' ) && ! in_array( $meta_key, $filter_meta ) ) {
			return $value;
		}

		return get_post_meta( $parent_id, $meta_key, $single );
	}

	/**
	 * Get feature request parent ID.
	 *
	 * @return bool|int
	 */
	public static function get_feature_request_parent_id() {
		global $post;

		$post_type = get_post_type( $post->ID );

		if ( JCK_SFR_Post_Types::$key !== $post_type ) {
			return false;
		}

		if ( ! isset( $post->post_parent ) ) {
			return false;
		}

		return $post->post_parent;
	}

	/**
	 * Remove astra metabox from single feature requests (inherit parent settings).
	 */
	public static function remove_metabox() {
		$post_type = get_post_type();

		if ( JCK_SFR_Post_Types::$key !== $post_type ) {
			return;
		}

		$screen = get_current_screen();

		remove_meta_box( 'astra_settings_meta_box', $screen, 'side' );
	}
}
