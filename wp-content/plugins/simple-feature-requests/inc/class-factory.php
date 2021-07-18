<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Feature Request creation/updating/deleting.
 */
class JCK_SFR_Factory {
	/**
	 * Run class.
	 */
	public static function run() {
		add_action( 'save_post', array( __CLASS__, 'save_request' ), 10, 3 );
		add_action( 'jck_sfr_status_updated', array( __CLASS__, 'status_updated' ), 10, 2 );
	}

	/**
	 * On save feature request.
	 *
	 * @param int     $post_id
	 * @param WP_Post $post
	 * @param bool    $update
	 */
	public static function save_request( $post_id, $post, $update ) {
		if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) || ! $update ) {
			return;
		}

		if ( get_post_type( $post_id ) !== 'cpt_feature_requests' ) {
			return;
		}

		if ( $post->post_status === 'trash' ) {
			return;
		}

		$feature_request = new JCK_SFR_Feature_Request( $post_id );

		$status = filter_input( INPUT_POST, 'jck_sfr_status' );
		$votes  = filter_input( INPUT_POST, 'jck_sfr_votes_count', FILTER_SANITIZE_NUMBER_INT );

		$meta = array(
			'status' => $status ? $status : $feature_request->get_status(),
			'votes'  => is_numeric( $votes ) ? absint( $votes ) : $feature_request->get_votes_count(),
		);

		remove_action( 'save_post', array( __CLASS__, 'save_request' ), 10 );

		foreach ( $meta as $key => $value ) {
			$method_name = sprintf( 'set_%s', $key );

			if ( ! method_exists( $feature_request, $method_name ) ) {
				continue;
			}

			call_user_func_array( array( $feature_request, $method_name ), array( $value ) );
		}

		add_action( 'save_post', array( __CLASS__, 'save_request' ), 10, 3 );
	}

	/**
	 * On status updated.
	 *
	 * @param $status
	 * @param $feature_request
	 */
	public static function status_updated( $status, $feature_request ) {
		if ( ! $status || $feature_request->post->post_status === 'draft' ) {
			return;
		}

		$post_status = 'publish';

		self::update( $feature_request->post->ID, array(
			'post_status' => $post_status,
		) );
	}

	/**
	 * Create a feature request.
	 *
	 * @param array $args
	 *
	 * @return bool|int
	 */
	public static function create( $args = array() ) {
		$defaults = array(
			'title'       => null,
			'description' => null,
			'votes'       => 1,
			'user'        => false,
			'taxonomies'  => false,
		);

		$args = apply_filters( 'jck_sfr_create_post_args', wp_parse_args( $args, $defaults ) );

		if ( in_array( null, $args, true ) || empty( $args['user'] ) ) {
			return false; // Title or description was empty.
		}

		if ( ! function_exists( 'post_exists' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/post.php' );
		}

		$notices = JCK_SFR_Notices::instance();

		$args['title'] = wp_strip_all_tags( $args['title'] );

		if ( post_exists( $args['title'] ) ) {
			$notices->add( __( 'A request with that title already exists.', 'simple-feature-requests' ), 'error' );

			return false; // Request with that title already exists.
		}

		// Not allowed to use these as request slugs.
		$reserved  = apply_filters( 'jck_sfr_reserved_slugs', array( 'page' ) );
		$page_name = apply_filters( 'jck_sfr_request_slug', sanitize_title( $args['title'] ), $args );
		$page_name = in_array( $page_name, $reserved, true ) ? $page_name . '-1' : $page_name;

		$post_args = array(
			'post_type'    => 'cpt_feature_requests',
			'post_title'   => $args['title'],
			'post_content' => wp_kses_post( $args['description'] ),
			'post_status'  => 'publish',
			'post_author'  => $args['user']->ID,
			'post_name'    => $page_name,
		);

		$inserted_id = wp_insert_post( $post_args );

		if ( ! $inserted_id || is_wp_error( $inserted_id ) ) {
			$notices->add( __( 'There was an error adding your feature request. Please try again.', 'simple-feature-requests' ), 'error' );

			return false; // Error adding feature request.
		}

		$default_status  = jck_sfr_get_default_post_status();
		$feature_request = new JCK_SFR_Feature_Request( $inserted_id );

		$feature_request->set_votes_count( 'add', $args['votes'], $args['user']->ID );
		$feature_request->set_status( $default_status, true );
		self::status_updated( $default_status, $feature_request );
		$feature_request->update_taxonomies( $args['taxonomies'] );

		do_action( 'jck_sfr_post_created', $inserted_id );

		$success_notice = __( 'Thank you for your request.', 'simple-feature-requests' );

		if ( $default_status !== 'publish' ) {
			$success_notice .= ' ' . sprintf( __( 'It is currently %s.', 'simple-feature-requests' ), $default_status );
		}

		$notices->add( $success_notice );

		return $inserted_id;
	}

	/**
	 * Update feature request.
	 *
	 * @param $feature_request_id
	 * @param $args
	 */
	public static function update( $feature_request_id, $args ) {
		if ( empty( $args ) ) {
			return;
		}

		$args['ID'] = $feature_request_id;

		wp_update_post( $args );
	}

	/**
	 * Merge array of requests into a single primary request.
	 *
	 * @param int   $primary_request_id Merge all requests into this one.
	 * @param array $requests           Array of request IDs.
	 * @param array $options            Array of merge options.
	 *
	 * @return bool
	 */
	public static function merge( $primary_request_id, $requests = array(), $options = array() ) {
		$defaults = array(
			'merge_votes' => true,
			'title'       => get_the_title( $primary_request_id ),
		);

		$options = wp_parse_args( $options, $defaults );

		$primary_request = new JCK_SFR_Feature_Request( $primary_request_id );

		if ( ! $primary_request || empty( $requests ) ) {
			return false;
		}

		foreach ( $requests as $request_id ) {
			if ( 'cpt_feature_requests' !== get_post_type( $request_id ) ) {
				continue;
			}

			$request = new JCK_SFR_Feature_Request( $request_id );

			// Merge votes.
			if ( $options['merge_votes'] ) {
				$voters = $request->get_voters();

				if ( ! empty( $voters ) ) {
					$primary_request->add_voters( $voters );
				}
			}

			// Trash merged request.
			self::update(
				$request_id,
				array(
					'post_status' => 'trash',
				)
			);

			do_action( 'jck_sfr_request_merged', $primary_request, $request );
		}
	}
}