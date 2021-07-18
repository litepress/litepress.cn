<?php

add_action( 'wp', function () {
	global $wp_query;

	if ( is_admin() || ! $wp_query->is_tax( get_object_taxonomies( 'product' ) ) ) {
		return;
	}

	/** 如果当前用户是登录用户则不执行数据缓存过程（但仍会读取和返回缓存），这样做主要是防止缓存一些自会有一登陆用户才能看到的东西，但让登录用户看未登录用户看到的东西则没有问题 */
	if ( is_user_logged_in() ) {
		return;
	}

	$current_uri = $_SERVER['REQUEST_URI'];
	if ( stristr( $current_uri, 'product-vendors' ) ) {
		return;
	}

	$cache_data = array(
		'posts'         => $wp_query->posts,
		'found_posts'   => $wp_query->found_posts,
		'max_num_pages' => $wp_query->max_num_pages,
	);

	set_transient( sanitize_key( $current_uri ), $cache_data, 3600 );
} );

add_filter( 'posts_pre_query', function ( $posts, WP_Query $wp_query ) {

	if ( is_admin() || ! $wp_query->is_tax( get_object_taxonomies( 'product' ) ) ) {
		return $posts;
	}

	$current_uri = $_SERVER['REQUEST_URI'];
	if ( stristr( $current_uri, 'product-vendors' ) ) {
		return $posts;
	}

	$cache_data = get_transient( sanitize_key( $current_uri ) );
	if ( ! empty( $cache_data ) ) {
		$wp_query->found_posts   = $cache_data['found_posts'];
		$wp_query->max_num_pages = $cache_data['max_num_pages'];

		return $cache_data['posts'] ?? $posts;
	}

	return $posts;
}, 9990, 2 );
