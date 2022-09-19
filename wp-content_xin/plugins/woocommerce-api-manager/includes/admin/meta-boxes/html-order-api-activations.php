<?php
/**
 * Order items HTML for meta box.
 *
 * @package WooCommerce API Manager/Admin/Meta boxes
 */

defined( 'ABSPATH' ) || exit;
?>

<div id="woocommerce-order-items">
    <thead>
    <div class="woocommerce_order_items_wrapper wc-order-items-editable">
        <table id="activations-table" cellpadding="0" cellspacing="0" class="woocommerce_order_items">
            <tr>
                <th class="sortable"><?php esc_html_e( 'API Key Used', 'woocommerce-api-manager' ) ?></th>
                <th class="sortable"><?php esc_html_e( 'Product ID', 'woocommerce-api-manager' ) ?></th>
                <th class="sortable"><?php esc_html_e( 'Version', 'woocommerce-api-manager' ) ?></th>
                <th class="sortable"><?php esc_html_e( 'Time', 'woocommerce-api-manager' ) ?></th>
                <th class="sortable"><?php esc_html_e( 'Object', 'woocommerce-api-manager' ) ?></th>
            </tr>
            </thead>
            <tbody id="order_line_items">
			<?php $i = 0;

			if ( ! empty( $activation_resources ) ) {
				foreach ( $activation_resources as $activation_resource ) : $i ++ ?>
					<?php
					$activation_id = $activation_resource->activation_id;

					if ( $activation_resource->api_key == $activation_resource->master_api_key ) {
						$api_key_type = esc_html__( 'Master API Key', 'woocommerce-api-manager' );
					} elseif ( $activation_resource->api_key == $activation_resource->product_order_api_key ) {
						$api_key_type = esc_html__( 'Product Order API Key', 'woocommerce-api-manager' );
					} else {
						$api_key_type = esc_html__( 'Associated API Key', 'woocommerce-api-manager' );
					}
					?>
                    <tr<?php if ( $i % 2 == 0 )
						echo ' class="alternate"' ?>>
                        <td><?php echo $api_key_type; ?></td>
                        <td><?php echo '<a href="' . esc_url( admin_url() . 'post.php?post=' . WC_AM_PRODUCT_DATA_STORE()->get_parent_id_from_product_id( $activation_resource->assigned_product_id ) . '&action=edit' ) . '" target="_blank">' . esc_attr( $activation_resource->assigned_product_id ) . '</a>' ?></td>
                        <td style="padding-left: 1em; padding-right: 1em"><?php echo esc_attr( ! empty( $activation_resource->version ) ? $activation_resource->version : '' ); ?></td>
                        <td><?php echo WC_AM_FORMAT()->unix_timestamp_to_date_i18n( $activation_resource->activation_time ) ?></td>
                        <td>
							<?php
							// Remove the trailing forward slash, if it exists.
							$obj_length = strlen( $activation_resource->object );
							$object     = ! empty( $activation_resource->object ) && substr( $activation_resource->object, $obj_length - 1, $obj_length ) == '/' ? substr( $activation_resource->object, 0, $obj_length - 1 ) : $activation_resource->object;

							if ( filter_var( $activation_resource->object, FILTER_VALIDATE_URL ) ) {
								// If $object is a URL, then remove the http(s)//: prefix.
								echo '<a href="' . esc_url( $object ) . '" target="_blank">' . esc_attr( WC_AM_URL()->remove_url_prefix( $object ) ) . '</a>';
							} else {
								echo esc_attr( $object );
							} ?>
                        </td>
                        <td>
                            <button type="button"
                                    instance="<?php echo $activation_resource->instance; ?>" order_id="<?php echo $activation_resource->order_id; ?>"
                                    sub_parent_id="<?php echo $activation_resource->sub_parent_id; ?>" api_key="<?php echo $activation_resource->api_key; ?>"
                                    product_id="<?php echo $activation_resource->product_id; ?>" user_id="<?php echo $activation_resource->user_id; ?>"
                                    class="delete_api_key button"><?php esc_html_e( 'Delete', 'woocommerce-api-manager' ); ?></button>
                        </td>
                    </tr>
				<?php endforeach;
			} ?>
            </tbody>
        </table>
    </div>
</div>