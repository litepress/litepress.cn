<?php
/**
 * Order items HTML for meta box.
 *
 * @package WooCommerce API Manager/Admin/Meta boxes
 */

defined( 'ABSPATH' ) || exit;
?>

<?php if ( ! empty( $mak ) ) { ?>
    <div class="wc-metaboxes">
        <div class="wc-metabox closed">
            <h3 class="fixed">
                <strong><?php printf( __( '%s ', 'woocommerce-api-manager' ), $mak ); ?></strong>
            </h3>
        </div>
    </div>
<?php } ?>