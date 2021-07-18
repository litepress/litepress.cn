<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Setup ajax methods.
 */
class JCK_SFR_AJAX {
	/**
	 * Run class.
	 */
	public static function run() {
		self::add_ajax_events();
	}

	/**
	 * Hook in methods - uses WordPress ajax handlers (admin-ajax).
	 */
	public static function add_ajax_events() {
		$ajax_events = array(
			'update_vote_count'       => true,
			'search_feature_requests' => true,
		);

		foreach ( $ajax_events as $ajax_event => $nopriv ) {
			add_action( 'wp_ajax_jck_sfr_' . $ajax_event, array( __CLASS__, $ajax_event ) );

			if ( $nopriv ) {
				add_action( 'wp_ajax_nopriv_jck_sfr_' . $ajax_event, array( __CLASS__, $ajax_event ) );
			}
		}
	}

	/**
	 * Update vote count for post.
	 */
	public static function update_vote_count() {
		$response = array(
			'success' => false,
			'message' => null,
			'votes'   => null,
		);

		$check_nonce = check_ajax_referer( 'jck-sfr-nonce', 'nonce', false );

		if ( ! $check_nonce ) {
			$response['message'] = __( 'Nonce check failed.', 'simple-feature-requests' );
			wp_send_json( $response );
		}

		$post_id = filter_input( INPUT_POST, 'post_id', FILTER_SANITIZE_NUMBER_INT );
		$type    = filter_input( INPUT_POST, 'type', FILTER_SANITIZE_STRING );

		if ( ! $post_id ) {
			$response['message'] = __( 'Simple Feature Requests: No post ID sent to AJAX.', 'simple-feature-requests' );
			wp_send_json( $response );
		}

		$post = get_post( $post_id );

		if ( ! $post ) {
			$response['message'] = sprintf( __( 'Simple Feature Requests: No post found for ID %d', 'simple-feature-requests' ), $post_id );
			wp_send_json( $response );
		}

		$feature_request = new JCK_SFR_Feature_Request( $post );

		$update_vote_count = $feature_request->set_votes_count( $type );

		if ( $update_vote_count['success'] === false ) {
			$response['message'] = $update_vote_count['reason'];
			wp_send_json( $response );
		}

		$response['success']       = true;
		$response['votes']         = $update_vote_count['updated_votes_count'];
		$response['votes_wording'] = _n( 'vote', 'votes', $update_vote_count, 'simple-feature-requests' );

		wp_send_json( $response );
	}

	/**
	 * Search feature requests.
	 */
	public static function search_feature_requests() {
		global $jck_sfr_requests;

		$response = array(
			'success'    => false,
			'html'       => null,
			'pagination' => null,
			'count'      => null,
			'search'     => sanitize_text_field( $_POST['search'] ),
			'message'    => null,
		);

		$check_nonce = check_ajax_referer( 'jck-sfr-nonce', 'nonce', false );

		if ( ! $check_nonce ) {
			$response['message'] = __( 'Nonce check failed.', 'simple-feature-requests' );
			wp_send_json( $response );
		}

		$paged = absint( filter_input( INPUT_POST, 'paged', FILTER_SANITIZE_NUMBER_INT ) );
		$paged = $paged ? $paged : 1;

		$args = apply_filters( 'jck_sfr_search_feature_requests_args', array(
			'post_type'      => 'cpt_feature_requests',
			'jck_sfr_ajax'   => true,
			'posts_per_page' => JCK_SFR_Post_Types::get_posts_per_page(),
			'paged'          => $paged,
		) );

		$jck_sfr_requests        = new WP_Query( $args );
		$jck_sfr_requests->paged = $paged;

		$response['count'] = $jck_sfr_requests->found_posts;

		ob_start();

		if ( $jck_sfr_requests->have_posts() ) {
			$response['success'] = true;
			while ( $jck_sfr_requests->have_posts() ) : $jck_sfr_requests->the_post();
				do_action( 'jck_sfr_loop' );
			endwhile;
		} else {
			do_action( 'jck_sfr_no_requests_found' );
		}

		$response['html'] = ob_get_clean();

		ob_start();
		JCK_SFR_Template_Hooks::include_template( 'loop/pagination', array(
			'add_args' => array(
				'search' => $response['search'],
			),
		) );
		$response['pagination'] = ob_get_clean();

		$response = apply_filters( 'jck_sfr_search_feature_requests_response', $response );

		wp_send_json( $response );
	}
}