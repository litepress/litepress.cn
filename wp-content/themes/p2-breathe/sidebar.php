<?php
/**
 * The Sidebar containing the main widget areas.
 *
 * @package p2-breathe
 */

if( ! is_active_sidebar( 'sidebar-1' ) && ! ( is_page() && is_active_sidebar( 'sidebar-pages' ) ) )
	return;
?>
	<div id="primary-modal"></div>
	<div id="secondary" class="widget-area" role="complementary">
		<div id="secondary-content">
			<?php do_action( 'before_sidebar' ); ?>
			<?php 
				if ( is_page() && is_active_sidebar( 'sidebar-pages' ) ) {
					dynamic_sidebar( 'sidebar-pages' );
				} else {
					dynamic_sidebar( 'sidebar-1' );
				}
			?>
		</div>
	</div><!-- #secondary -->
