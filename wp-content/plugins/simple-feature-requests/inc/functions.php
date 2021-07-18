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
	$statuses = apply_filters( 'jck_sfr_statuses', array(
		'pending'      => __( 'Pending', 'simple-feature-requests' ),
		'publish'      => __( 'Published', 'simple-feature-requests' ),
		'under-review' => __( 'Under Review', 'simple-feature-requests' ),
		'planned'      => __( 'Planned', 'simple-feature-requests' ),
		'started'      => __( 'Started', 'simple-feature-requests' ),
		'completed'    => __( 'Completed', 'simple-feature-requests' ),
		'declined'     => __( 'Declined', 'simple-feature-requests' ),
	) );

	if ( ! empty( $excludes ) ) {
		foreach ( $excludes as $exclude ) {
			unset( $statuses[ $exclude ] );
		}
	}

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
	$descriptions = apply_filters( 'jck_sfr_status_descriptions', array(
		'pending'      => __( 'The request is pending approval from an admin.', 'simple-feature-requests' ),
		'publish'      => __( 'The request is now published on the site.', 'simple-feature-requests' ),
		'under-review' => __( 'The request is being considered for development.', 'simple-feature-requests' ),
		'planned'      => __( 'Good news! The request has been planned for development.', 'simple-feature-requests' ),
		'started'      => __( 'Good news! The request has been started.', 'simple-feature-requests' ),
		'completed'    => __( 'Good news! The request has now been completed.', 'simple-feature-requests' ),
		'declined'     => __( 'Sorry, the request has been declined.', 'simple-feature-requests' ),
	) );

	if ( ! isset( $descriptions[ $status ] ) ) {
		return false;
	}

	return $descriptions[ $status ];
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

/**
 * Get status colours.
 *
 * @param $status
 *
 * @return mixed
 */
function jck_sfr_get_status_colors( $status ) {
	$colors = apply_filters( 'jck_sfr_status_colors', array(
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
	) );

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