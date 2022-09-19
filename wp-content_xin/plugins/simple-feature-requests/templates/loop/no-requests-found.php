<?php
/**
 * The Template for displaying no requests found message.
 *
 * @author        James Kemp
 * @version       1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
<?php $default_plural_name = apply_filters('jck_sfr_plural_request_name', 'feature requests', false ); ?>
<p class="jck-sfr-no-requests-found"><?php printf( __( 'Sorry, no %s were found.', 'simple-feature-requests' ), $default_plural_name ); ?></p>