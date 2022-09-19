<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$timezone = ! empty( $vendor_data['timezone'] ) ? sanitize_text_field( $vendor_data['timezone'] ) : '';
?>
<div class="wrap">
	<div id="poststuff">
		<div id="post-body" class="metabox-holder columns-2">
			<div id="postbox-container-1" class="postbox-container">
				<div id="woocommerce-order-notes" class="postbox">
					<div class="inside">
						<h2><?php esc_html_e( 'Order Notes', 'woocommerce-product-vendors' ); ?></h2>
						<?php $this->order_notes->output( $post ); ?>
					</div><!--.inside-->
				</div><!--#woocommerce-order-notes-->
			</div><!--#postbox-container-1-->

			<div id="postbox-container-2" class="postbox-container">
				<div id="woocommerce-order-data" class="postbox">
					<div class="inside">
						<div class="panel-wrap woocommerce">
							<div id="order_data" class="panel">

								<h2><?php printf( esc_html__( 'Order #%s Details', 'woocommerce-product-vendors' ), $order->get_order_number() ); ?></h2>

								<div class="order_data_column_container">
									<div class="order_data_column">
										<h4><?php esc_html_e( 'General Details', 'woocommerce-product-vendors' ); ?></h4>

										<p class="form-field form-field-wide"><label for="order_date"><?php esc_html_e( 'Order date:', 'woocommerce-product-vendors' ) ?></label>
											<input type="text" class="date-picker" name="order_date" id="order_date" maxlength="10" value="<?php echo WC_Product_Vendors_Utils::format_date( sanitize_text_field( $post->post_date_gmt ), $timezone ); ?>" disabled="disabled" />
										</p>

										<p class="form-field form-field-wide wc-order-status"><label for="order_status"><?php esc_html_e( 'Order status:', 'woocommerce-product-vendors' ) ?></label>

										<span class="wcpv-order-status-<?php echo esc_attr( $order->get_status() ); ?>"><?php echo esc_html( WC_Product_Vendors_Utils::format_order_status( $order->get_status() ) ); ?></span></p>
									</div><!--.order_data_column-->

									<div class="order_data_column">
										<h4><?php esc_html_e( 'Billing Details', 'woocommerce-product-vendors' ); ?></h4>
										<div class="address">
											<?php
											if ( $order->get_formatted_billing_address() ) {
												echo '<p><strong>' . esc_html__( 'Address', 'woocommerce-product-vendors' ) . ':</strong>' . wp_kses( $order->get_formatted_billing_address(), array( 'br' => array() ) ) . '</p>';
											} else {
												echo '<p class="none_set"><strong>' . esc_html__( 'Address', 'woocommerce-product-vendors' ) . ':</strong> ' . esc_html__( 'No shipping address set.', 'woocommerce-product-vendors' ) . '</p>';
											}

											$address = $order->get_address();

											?>
											<p>
												<strong><?php esc_html_e( 'Email:', 'woocommerce-product-vendors' ); ?></strong>
												<a href="mailto:<?php echo esc_attr( $address['email'] ); ?>"><?php echo $address['email']; ?></a>
											</p>

											<p>
												<strong><?php esc_html_e( 'Phone:', 'woocommerce-product-vendors' ); ?></strong>
												<?php echo $address['phone']; ?>
											</p>
										</div>
									</div><!--.order_data_column-->

									<div class="order_data_column">
										<h4><?php esc_html_e( 'Shipping Details', 'woocommerce-product-vendors' ); ?></h4>
										<div class="address">
											<?php
											if ( $order->get_formatted_shipping_address() ) {
												echo '<p><strong>' . esc_html__( 'Address', 'woocommerce-product-vendors' ) . ':</strong>' . wp_kses( $order->get_formatted_shipping_address(), array( 'br' => array() ) ) . '</p>';
											} else {
												echo '<p class="none_set"><strong>' . esc_html__( 'Address', 'woocommerce-product-vendors' ) . ':</strong> ' . esc_html__( 'No shipping address set.', 'woocommerce-product-vendors' ) . '</p>';
											}

											$address = $order->get_address();

											?>
										</div>
									</div><!--.order_data_column-->

									<?php do_action( 'wcpv_vendor_order_detail_order_data_column', $order ); ?>
								</div><!--.order_data_column_container-->

								<div class="clear"></div>
							</div><!--.panel-->
						</div><!--.panel-wrap-->
					</div><!--.inside-->
				</div><!--.postbox-->

				<div id="woocommerce-order-items" class="postbox">
					<h2><span><?php esc_html_e( 'Order Items', 'woocommerce-product-vendors' ); ?></span></h2>

					<div class="inside">
						<div class="panel-wrap woocommerce">
							<div id="order_data" class="panel">

								<div class="order_data_column_container">
									<form id="wcpv-vendor-order-detail" action="" method="post">
										<input type="hidden" name="page" value="wcpv-vendor-order&id=<?php echo esc_attr( $order_id ); ?>" />
										<?php $order_list->display(); ?>
									</form>
								</div><!--.order_data_column_container-->

								<div class="clear"></div>
							</div><!--.panel-->
						</div><!--.panel-wrap-->
					</div><!--.inside-->
				</div><!--.postbox-->

                <?php
                /**
                 * TODO:WCY
                 *
                 * 让订单页面支持自定义卡片
                 */
                do_action( 'wcpv_woocommerce_order_items' );
                ?>
			</div><!--#postbox-container-2-->
		</div><!--#post-body-->
		<br class="clear" />
	</div><!--#poststuff-->
</div><!--.wrap-->
