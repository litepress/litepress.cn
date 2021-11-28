<?php
/**
 * 有一些翻译需要在本地手工维护，这些翻译通过此文件加载
 */

add_filter( 'load_textdomain_mofile', function ( $mofile, $domain ) {
	$dir = WP_CONTENT_DIR . '/languages/loco/plugins';

	if ( 'glotpress' === $domain && ! str_contains( $mofile, $dir ) ) {
		$locale = apply_filters( 'plugin_locale', determine_locale(), $domain );
		$mofile = "$dir/$domain-$locale.mo";
	}

	return $mofile;
}, 10, 2 );
