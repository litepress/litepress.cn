<?php
if( !isset( $lvBpSearchResult ) ) {
	return;
}

printf(
	'<h3 class="no-result-header">%s</h3>',
	esc_html__( "No results", 'lvbp-ajax-search' )
);