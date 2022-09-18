<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Get statuses.
 *
 * @param array $excludes
 *
 * @return array
 */
function jck_sfr_get_statuses( $excludes = array() ) {
	$statuses = array(
		'pending'      => __( 'Pending', 'simple-feature-requests' ),
		'publish'      => __( 'Published', 'simple-feature-requests' ),
		'under-review' => __( 'Under Review', 'simple-feature-requests' ),
		'planned'      => __( 'Planned', 'simple-feature-requests' ),
		'started'      => __( 'Started', 'simple-feature-requests' ),
		'completed'    => __( 'Completed', 'simple-feature-requests' ),
		'declined'     => __( 'Declined', 'simple-feature-requests' ),
	);

	$statuses = apply_filters( 'jck_sfr_statuses', $statuses );

	if ( ! empty( $excludes ) ) {
		foreach ( $excludes as $exclude ) {
			unset( $statuses[ $exclude ] );
		}
	}

	$statuses = array_filter( $statuses );

	return $statuses;
}

/**
 * Get status descriptions.
 *
 * @param string $status Status key.
 *
 * @return bool|string
 */
function jck_sfr_get_status_description( $status ) {
	$default_single_name = apply_filters( 'jck_sfr_single_request_name', 'request', false );
	$descriptions = array(
		'pending'      => sprintf( __( 'The %s is pending approval from an admin.', 'simple-feature-requests' ), $default_single_name ),
		'publish'      => sprintf( __( 'The %s is now published on the site.', 'simple-feature-requests' ), $default_single_name ),
		'under-review' => sprintf( __( 'The %s is being considered for development.', 'simple-feature-requests' ), $default_single_name ),
		'planned'      => sprintf( __( 'Good news! The %s has been planned for development.', 'simple-feature-requests' ), $default_single_name ),
		'started'      => sprintf( __( 'Good news! The %s has been started.', 'simple-feature-requests' ), $default_single_name ),
		'completed'    => sprintf( __( 'Good news! The %s has now been completed.', 'simple-feature-requests' ), $default_single_name ),
		'declined'     => sprintf( __( 'Sorry, the %s has been declined.', 'simple-feature-requests' ), $default_single_name ),
	);

	$descriptions = apply_filters( 'jck_sfr_status_descriptions', $descriptions );

	if ( ! isset( $descriptions[ $status ] ) ) {
		return false;
	}

	return $descriptions[ $status ];
}

function jck_sfr_get_custom_statuses() {
	$statuses = wpsf_get_setting( 'jck_sfr', 'general_setup', 'statuses' );

	return $statuses;
}

function jck_sfr_get_custom_labels( $form = 'single' ) {

	$custom_label_single = wpsf_get_setting( 'jck_sfr', 'general_labels', 'single_request_label');
	$custom_label_plural = wpsf_get_setting( 'jck_sfr', 'general_labels', 'plural_request_label');

	if( empty( $custom_label_single ) && $form == 'single' ) {
		$custom_label_single = "Request";
	}
	
	if( empty( $custom_label_plural ) && $form == 'plural' ) {
		$custom_label_plural = "Requests";
	}

	if( $form == 'single' ) {
		return $custom_label_single;
	} else if( $form == 'plural' ) {
		return $custom_label_plural;
	}
}

/**
 * Get viewable statuses.
 *
 * @return array
 */
function jck_sfr_get_viewable_statuses() {
	$statuses = jck_sfr_get_statuses();

	if ( ! is_user_logged_in() ) {
		unset( $statuses['pending'] );
	}

	$statuses = array_keys( $statuses );

	return apply_filters( 'jck_sfr_viewable_statuses', $statuses );
}

/**
 * Get terms for filter.
 *
 * @param string $taxonomy
 * @param bool   $hide_empty
 *
 * @return array
 */
function jck_sfr_get_term_options( $taxonomy, $hide_empty = true ) {
	static $filter_terms = array();

	$key = sprintf( '%s_%d', $taxonomy, $hide_empty );

	if ( ! empty( $filter_terms[ $key ] ) ) {
		return $filter_terms[ $key ];
	}

	$filter_terms[ $key ] = array();

	$terms = get_terms( $taxonomy, array(
		'hide_empty' => $hide_empty,
	) );

	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		return $filter_terms[ $key ];
	}

	foreach ( $terms as $term ) {
		$filter_terms[ $key ][ $term->slug ] = $term->name;
	}

	return $filter_terms[ $key ];
}

function jck_sfr_get_status_slug( $title ) {
	return sanitize_title_with_dashes( $title );
}

/**
 * Get status label.
 *
 * @param $status
 *
 * @return string
 */
function jck_sfr_get_status_label( $status ) {
	$statuses = jck_sfr_get_statuses();

	if ( ! isset( $statuses[ $status ] ) ) {
		return '';
	}

	return $statuses[ $status ];
}

function jck_sfr_save_settings( $settings ) {
	if ( ! empty( $settings ) && is_array( $settings ) ) {
		update_option( 'jck_sfr_settings', $settings );
		return true;
	} else {
		return false;
	}
}

function jck_sfr_fix_post_statuses( $old_slug, $new_slug ) {
	if ( empty( $old_slug ) ) return false;
	if ( empty( $new_slug ) ) {
		$new_slug = apply_filters( 'jck_sfr_default_blank_slug_status', 'pending' );
	}

	$args = array(
		'post_type' => 'cpt_feature_requests',
		'meta_query' => array(
			array(
				'key' => 'jck_sfr_status',
				'value' => $old_slug,
				'compare' => '='
			)
		)
	);

	if ( $requests = get_posts( $args ) ) {
		foreach ( $requests as $request ) {
			update_post_meta( $request->ID, 'jck_sfr_status', $new_slug );
		}
		return true;
	}
}

/**
 * Get status colours.
 *
 * @param $status
 *
 * @return mixed
 */
function jck_sfr_get_status_colors( $status ) {
	$colors = array(
		'pending'      => array(
			'background' => '#FFE26C',
			'color'      => '#0F0F14',
		),
		'publish'      => array(
			'background' => '#CCC',
			'color'      => '#0F0F14',
		),
		'completed'    => array(
			'background' => '#4dcea6',
			'color'      => '#fff',
		),
		'under-review' => array(
			'background' => '#CCC',
			'color'      => '#0F0F14',
		),
		'planned'      => array(
			'background' => '#FFC05F',
			'color'      => '#0F0F14',
		),
		'declined'     => array(
			'background' => '#F45B7C',
			'color'      => '#fff',
		),
		'started'      => array(
			'background' => '#568ECD',
			'color'      => '#fff',
		),
		'default'      => array(
			'background' => '#CCC',
			'color'      => '#0F0F14',
		),
	);

	$colors = apply_filters( 'jck_sfr_status_colors', $colors );

	return isset( $colors[ $status ] ) ? $colors[ $status ] : $colors['default'];
}

/**
 * Get default post status.
 *
 * @return string
 */
function jck_sfr_get_default_post_status() {
	global $simple_feature_requests_class;

	$default_status = 'pending';
	$settings       = $simple_feature_requests_class->settings;

	if ( ! empty ( $settings ) ) {
		$default_status_setting = $settings->get_setting( 'general_setup_default_status' );
		$default_status         = ! empty( $default_status_setting ) ? $default_status_setting : $default_status;
	}

	return apply_filters( 'jck_sfr_get_default_post_status', $default_status );
}

/**
 * Get archive URL with filters.
 *
 * @param array $excludes
 *
 * @return string
 */
function jck_sfr_get_archive_url_with_filters( $excludes = array() ) {
	$url_parts = array(
		'base'  => JCK_SFR_Post_Types::get_archive_url(),
		'query' => $_SERVER['QUERY_STRING'],
	);

	// Remove params of "pending" request.
	$excludes[] = 'p';
	$excludes[] = 'post_type';

	if ( ! empty( $excludes ) && ! empty( $url_parts['query'] ) ) {
		parse_str( $url_parts['query'], $query );

		foreach ( $excludes as $exclude ) {
			unset( $query[ $exclude ] );
		}

		$url_parts['query'] = http_build_query( $query );
	}

	if ( empty( $url_parts['query'] ) ) {
		return $url_parts['base'];
	}

	return implode( '?', $url_parts );
}

/**
 * Are comments enabled?
 *
 * @return bool
 */
function jck_sfr_comments_enabled() {
	$settings = JCK_SFR_Settings::get_settings();

	return ! empty( $settings['general_comments_enable'] );
}

/**
 * Does post have attachments?
 * 
 * @return bool
 */
function jck_sfr_has_attachments() {
	$attachments = get_attached_media( 'image', get_the_ID() );

	if( $attachments ) { return true; }
	else { return false; }
}