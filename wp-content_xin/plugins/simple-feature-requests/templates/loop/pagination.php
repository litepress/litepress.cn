<?php
/**
 * The template for displaying pagination.
 *
 * @var $jck_sfr_requests WP_Query
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $jck_sfr_requests; ?>

<nav class="jck-sfr-pagination">
	<?php
	if ( $jck_sfr_requests->max_num_pages > 1 ) {
		echo paginate_links( apply_filters( 'jck_sfr_pagination_args', array(
			'base'      => esc_url_raw( sprintf( '%s%%_%%', JCK_SFR_Post_Types::get_archive_url() ) ),
			'add_args'  => isset( $add_args ) ? $add_args : false,
			'current'   => isset( $jck_sfr_requests->query_vars['paged'] ) ? $jck_sfr_requests->query_vars['paged'] : 1,
			'total'     => $jck_sfr_requests->max_num_pages,
			'prev_text' => '&larr;',
			'next_text' => '&rarr;',
			'type'      => 'list',
			'end_size'  => 3,
			'mid_size'  => 3,
		), $jck_sfr_requests ) );
	}
	?>
</nav>
