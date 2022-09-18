<?php
/**
 * The Template for displaying the back to archive link.
 *
 * @author        James Kemp
 * @version       1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! JCK_SFR_Post_Types::is_type( 'single' ) ) {
	return;
}
?>

<div class="jck-sfr-sidebar-widget jck-sfr-sidebar-widget--back bg-white theme-boxshadow mb-3">
	<?php JCK_SFR_Template_Methods::back_to_archive_link(); ?>
</div>
