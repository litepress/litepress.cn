<?php
/**
 * The Template for displaying the filters.
 *
 * @author        James Kemp
 * @version       1.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$filters = JCK_SFR_Template_Methods::get_filters();
$search  = filter_input( INPUT_GET, 'search', FILTER_SANITIZE_STRING );

if ( empty( $filters ) ) {
	return;
}
?>

<ul class="jck-sfr-filters" <?php if ( $search ) { echo 'style="display: none;"'; } ?>>
	<?php foreach ( $filters as $key => $filter ) { ?>
		<?php $filter['key'] = $key; ?>
		<li class="jck-sfr-filters__filter-item jck-sfr-filters__filter-item--<?php echo esc_attr( $key ); ?> jck-sfr-filters__filter-item--<?php esc_attr_e( $filter['type'] ); ?>">
			<?php echo JCK_SFR_Template_Methods::get_filter_html( $filter ); ?>
		</li>
	<?php } ?>
</ul>