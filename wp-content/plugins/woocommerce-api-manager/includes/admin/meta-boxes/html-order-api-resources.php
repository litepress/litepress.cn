<?php
/**
 * Order items HTML for meta box.
 *
 * @package WooCommerce API Manager/Admin/Meta boxes
 */

defined( 'ABSPATH' ) || exit;

$expires = '';

if ( ! empty( $resource ) ) {
	if ( ! empty( $resource->sub_id ) ) {
		if ( WCAM()->get_wc_subs_exist() ) {
			$expires = WC_AM_SUBSCRIPTION()->get_subscription_end_date_to_display( $resource->order_id, $resource->product_id );
		} else {
			$expires = $resource->access_expires == 0 ? esc_html__( 'never', 'woocommerce-api-manager' ) : esc_attr( WC_AM_FORMAT()->get_human_time_diff( $resource->access_expires ) );
		}
	} else {
		if ( WC_AM_API_RESOURCE_DATA_STORE()->is_access_expired( $resource->access_expires ) ) {
			$expires = 'Expired';
		} else {
			$expires = $resource->access_expires == 0 ? esc_html__( 'never', 'woocommerce-api-manager' ) : esc_attr( WC_AM_FORMAT()->get_human_time_diff( $resource->access_expires ) );
		}
	}

	$version = WC_AM_PRODUCT_DATA_STORE()->get_meta( $resource->product_id, '_api_new_version' );
	?>

    <div class="wc-metaboxes">
        <div class="wc-metabox closed">
            <h3 class="fixed">
                <span class="handlediv" title="<?php esc_html_e( 'Click to toggle', 'woocommerce-api-manager' ); ?>"></span>
                <strong><?php printf( __( 'Product ID: %s | Product Title: %s | Activations: %s out of %s | Current Version: %s | Expires: %s', 'woocommerce-api-manager' ), $resource->product_id, $resource->product_title, $resource->activations_total, $resource->activations_purchased_total, esc_attr( ! empty( $version ) ? $version : '' ), esc_html( $expires ) ); ?></strong>
            </h3>
            <table cellpadding="0" cellspacing="0" class="wc-metabox-content">
                <tbody>
                <tr>
                    <td>
                        <label for="poak<?php echo $i; ?>"><?php esc_html_e( 'Product Order API Key:', 'woocommerce-api-manager' ); ?></label>
                        <input type="text" class="short am_expand_text_box" id="poak<?php echo $i; ?>" name="product_order_api_key[<?php echo $i; ?>]"
                               value="<?php echo esc_attr( $resource->product_order_api_key ); ?>" readonly/>
                    </td>
                    <td>
                        <label><?php esc_html_e( 'Activation Limit', 'woocommerce-api-manager' ); ?>:</label>
                        <input type="number" class="short" name="activations_purchased_total[<?php echo $i; ?>]" step="1" min="1"
                               value="<?php echo esc_attr( $resource->activations_purchased_total ) ?>"
                               placeholder="<?php esc_html_e( '1', 'woocommerce-api-manager' ); ?>"/>
                    </td>
                    <td>
                        <label><?php esc_html_e( 'Current Version', 'woocommerce-api-manager' ); ?>:</label>
                        <input type="text" class="short" name="version[<?php echo $i; ?>]"
                               value="<?php echo esc_attr( ! empty( $version ) ? $version : '' ) ?>"
                               placeholder="<?php esc_html_e( 'Required', 'woocommerce-api-manager' ); ?>" readonly/>
                    </td>
                </tr>
                <tr>
                    <td>
                        <label><?php esc_html_e( 'Resource Title', 'woocommerce-api-manager' ); ?>:</label>
                        <input type="text" class="am_tooltip short am_expand_text_box" name="product_title[<?php echo $i; ?>]"
                               value="<?php echo esc_attr( $resource->product_title ) ?>"
                               placeholder="<?php esc_html_e( 'Required', 'woocommerce-api-manager' ); ?>" readonly/>
                    </td>
                    <td>
                        <label><?php esc_html_e( 'Product ID', 'woocommerce-api-manager' ); ?>:</label><?php echo '<a href="' . esc_url( admin_url() . 'post.php?post=' . WC_AM_PRODUCT_DATA_STORE()->get_parent_id_from_product_id( $resource->product_id ) . '&action=edit' ) . '" target="_blank">'?><span style="text-decoration:none;" class="dashicons dashicons-admin-links"></span></a>
                        <input type="text" class="short" name="product_id[<?php echo $i; ?>]"
                               value="<?php echo esc_attr( $resource->product_id ) ?>"
                               placeholder="<?php esc_html_e( 'Required', 'woocommerce-api-manager' ); ?>" readonly/>
                    </td>
                    <td>
                        <label><?php esc_html_e( 'Access Expires', 'woocommerce-api-manager' ); ?>:</label>
                        <input type="text" class="short" name="access_expires[<?php echo $i; ?>]"
                               value="<?php echo $expires ?>"
                               placeholder="<?php esc_html_e( 'Required', 'woocommerce-api-manager' ); ?>" readonly/>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
<?php } ?>