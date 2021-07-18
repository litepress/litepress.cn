<?php
/**
 * Email order details.
 *
 * @version 2.0.0
 * @since 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>

<?php do_action( 'woocommerce_email_before_order_table', $order, true, false, $email ); ?>

<table class="td" cellspacing="0" cellpadding="6" style="width: 100%; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;" border="1">
	<thead>
		<tr>
			<th class="td" scope="col" style="text-align:left;"><?php esc_html_e( 'Product', 'woocommerce-product-vendors' ); ?></th>
			<th class="td" scope="col" style="text-align:left;"><?php esc_html_e( 'Quantity', 'woocommerce-product-vendors' ); ?></th>
			<th class="td" scope="col" style="text-align:left;"><?php esc_html_e( 'Price', 'woocommerce-product-vendors' ); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php
			$pass_shipping = false;

			foreach( $order->get_items() as $item_id => $item ) {
				$_product     = apply_filters( 'woocommerce_order_item_product', $order->get_product_from_item( $item ), $item );
				$item_meta    = version_compare( WC_VERSION, '3.0', '<' ) ? new WC_Order_Item_Meta( $item, $_product ) : new WC_Order_Item_Product( $item_id );

				if ( version_compare( WC_VERSION, '3.0.0', '<' ) ) {
					$product_id = $_product->id;
				} else {
					$product_id = ( 'product_variation' === $_product->post_type ) ? $_product->get_parent_id() : $_product->get_id();
				}

				$pass_shipping |= 'yes' === get_post_meta( $product_id, '_wcpv_product_default_pass_shipping_tax', true );
				$vendor_id = WC_Product_Vendors_Utils::get_vendor_id_from_product( $product_id );

				// remove the order items that are not from this vendor
				if ( $this_vendor !== $vendor_id ) {
					continue;
				}

				if ( apply_filters( 'woocommerce_order_item_visible', true, $item ) ) {
					?>
					<tr class="<?php echo esc_attr( apply_filters( 'woocommerce_order_item_class', 'order_item', $item, $order ) ); ?>">
						<td class="td" style="text-align:left; vertical-align:middle; border: 1px solid #eee; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; word-wrap:break-word;"><?php

							$show_image = apply_filters( 'wcpv_email_order_details_show_image', false );

							// Show title/image etc
							if ( $show_image ) {
								echo apply_filters( 'woocommerce_order_item_thumbnail', '<div style="margin-bottom: 5px"><img src="' . ( $_product->get_image_id() ? current( wp_get_attachment_image_src( $_product->get_image_id(), 'thumbnail') ) : wc_placeholder_img_src() ) .'" alt="' . esc_attr__( 'Product Image', 'woocommerce-product-vendors' ) . '" height="' . esc_attr( $image_size[1] ) . '" width="' . esc_attr( $image_size[0] ) . '" style="vertical-align:middle; margin-right: 10px;" /></div>', $item );
							}

							// Product name
							echo apply_filters( 'woocommerce_order_item_name', $item['name'], $item, false );

							// SKU
							if ( is_object( $_product ) && $_product->get_sku() ) {
								echo ' (#' . $_product->get_sku() . ')';
							}

							// allow other plugins to add additional product information here
							do_action( 'woocommerce_order_item_meta_start', $item_id, $item, $order );

							// Variation
							if ( version_compare( WC_VERSION, '3.0', '<' ) ) {
								if ( ! empty( $item_meta->meta ) ) {
									echo '<br/><small>' . nl2br( $item_meta->display( true, true, '_', "\n" ) ) . '</small>';
								}
							} else {
								wc_display_item_meta( $item );
							}

							// allow other plugins to add additional product information here
							do_action( 'woocommerce_order_item_meta_end', $item_id, $item, $order );

						?></td>
						<td class="td" style="text-align:left; vertical-align:middle; border: 1px solid #eee; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;"><?php echo apply_filters( 'woocommerce_email_order_item_quantity', $item['qty'], $item ); ?></td>
						<td class="td" style="text-align:left; vertical-align:middle; border: 1px solid #eee; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;"><?php echo wp_kses_post( $order->get_formatted_line_subtotal( $item ) ); ?></td>
					</tr>
					<?php
				}
			}
		?>
	</tbody>
	<?php $shipping_method = $order->get_shipping_method(); ?>
	<?php if ( $pass_shipping && ! empty( $shipping_method ) ) : ?>
	<tfoot>
		<tr>
			<th class="td" scope="row" colspan="2" style="text-align: left;"><?php esc_html_e( 'Shipping method', 'woocommerce-product-vendors' ); ?></th>
			<td class="td" style="text-align: left;"><?php echo esc_html( $shipping_method ); ?></td>
		</tr>
	</tfoot>
	<?php endif; ?>
</table>

<?php do_action( 'woocommerce_email_after_order_table', $order, true, false, $email ); ?>
