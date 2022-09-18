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
			'update_vote_count'       		   => true,
			'search_feature_requests' 		   => true,
			'set_feature_request_attachments' => true,
			'get_feature_request_attachments' => true
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


	public static function set_feature_request_attachments(){

		$response = array(
			'success'    => false,
			'message'    => null,
		);

		$nonce = check_ajax_referer( 'jck-sfr-attachment-nonce', 'nonce', false );

		if ( ! $nonce ) {
			$response['message'] = __( 'Nonce check failed.', 'simple-feature-requests' );
			wp_send_json( $response );
		}

		$attachments = $_FILES['attachments'];

		if( ! isset( $attachments['name'] ) || ! count( $attachments['name'] ) ){
			$response['message'] = __( 'No attachments found.', 'simple-feature-requests' );
			wp_send_json( $response );
		}

		$attachment_ids = array();

		foreach( $attachments['name'] as $index => $name ){

			$file_data = array( 'tmp_name' => $attachments['tmp_name'][$index], 'size' => $attachments['size'][$index], 'name' => $name );
			$file      =  wp_handle_upload(  $file_data, array( 'test_form' => false ) );

			if( isset( $file['error'] ) && $file['error'] ){
				continue; // skip to next if uploading failed
			}

			$wp_filetype   = wp_check_filetype( $name, null );
			$attachment    = array(
				'guid' 			 => $file['url'],
				'post_mime_type' => $file['type'],
				'post_parent' 	 => 0,
				'post_title' 	 => preg_replace('/\.[^.]+$/', '', $name),
				'post_content' 	 => '',
				'post_status' 	 => 'inherit'
			);

			$attachment_id = wp_insert_attachment( $attachment, $file['file'], 0, true );

			if ( ! is_wp_error( $attachment_id ) ) {

				if ( ! function_exists( 'wp_crop_image' ) ) {
					include( ABSPATH . 'wp-admin/includes/image.php' );
				}

				$attachment_data = wp_generate_attachment_metadata( $attachment_id, $file['file'] );
				wp_update_attachment_metadata( $attachment_id,  $attachment_data );

				$attachment_ids[] = $attachment_id;
			}
		}

		$response['success'] 		 = true;
		$response['attachment_ids'] = $attachment_ids;

		wp_send_json_success( $response );
	}


	public static function get_feature_request_attachments(){

		$response = array(
			'success'    => false,
			'message'    => null,
		);

		$nonce = check_ajax_referer( 'jck-sfr-attachment-nonce', 'nonce', false );

		if ( ! $nonce ) {
			$response['message'] = __( 'Nonce check failed.', 'simple-feature-requests' );
			wp_send_json( $response );
		}

		$attachment_ids = $_GET['attachment_ids'];
		$attachments 	 = array();

		foreach( $attachment_ids as $attachment_id ){

			$dir  = wp_upload_dir();
			$url  = wp_get_attachment_image_src( $attachment_id, array( 50, 50 ) )[0];
			$size = filesize( get_attached_file( $attachment_id ) );

			$data = array(
				'name' => basename( $url ),
				'url'  => $url,
				'size' => $size
			);

			$attachments[$attachment_id] = $data;
		}

		$response['success'] 	  = true;
		$response['attachments'] = $attachments;

		wp_send_json_success( $response );
	}
}