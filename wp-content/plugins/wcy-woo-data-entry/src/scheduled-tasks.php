<?php
/*
add_action( 'lpcn_wp_product_updated', function ( $slug, $version, $type ) {
	global $blog_id;

	$args = array(
		'slug' => $slug,
		'type' => $type,
	);

	$current_id = $blog_id;
	switch_to_blog( 4 );
	wp_schedule_single_event( time() + 60, 'gp_import_from_wp_org', $args );
	switch_to_blog( $current_id );
}, 10, 3 );
*/
