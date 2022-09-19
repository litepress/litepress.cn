<?php
/**
 * API Keys Order Complete Email
 *
 * Shows downloads on the account page.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/api-keys-order-complete.php.
 *
 * HOWEVER, on occasion WooCommerce API Manager will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @author  Todd Lahman LLC
 * @package WooCommerce API Manager/Templates/Order Complete Email
 * @version 2.0
 */

defined( 'ABSPATH' ) || exit;

if ( is_object( $order ) && ! empty( $resources ) ) :
	$hide_product_order_api_keys = WC_AM_USER()->hide_product_order_api_keys();

	if ( $order->has_downloadable_item() ) :
		?>
        <h2>
			<?php esc_html_e( 'API Downloads', 'woocommerce-api-manager' ); ?>
        </h2>
        <p>
            <a href="<?php echo esc_url( get_permalink( wc_get_page_id( 'myaccount' ) ) ); ?>"><?php esc_html_e( 'Download Your Files', 'woocommerce-api-manager' ); ?></a>
        </p>
	<?php endif; ?>
    <h2>
		<?php esc_html_e( 'Master API Key', 'woocommerce-api-manager' ); ?>
    </h2>
    <p>
		<?php esc_html_e( 'Master API Key:', 'woocommerce-api-manager' ); ?>
        <strong><?php echo esc_attr( WC_AM_USER()->get_master_api_key( $order->get_customer_id() ) ) ?></strong>
    </p>
    <p>
		<?php esc_html_e( 'A Master API Key can be used to activate any and all products.', 'woocommerce-api-manager' ); ?>
    </p>
	<?php
	if ( ! $hide_product_order_api_keys ) : ?>
        <h3>
			<?php esc_html_e( 'Product Order API Keys', 'woocommerce-api-manager' ); ?>
        </h3>
        <p>
			<?php esc_html_e( 'A Product Order API Key can be used to limit activation to a single product from a single order.', 'woocommerce-api-manager' ); ?>
        </p>
	<?php
	endif;
	foreach ( $resources as $resource ) :
		$product_object = WC_AM_PRODUCT_DATA_STORE()->get_product_object( $resource->product_id );
		?>
        <h4>
			<?php echo esc_attr( $product_object->get_title() ); ?>
        </h4>
        <p><?php esc_html_e( 'Product ID:', 'woocommerce-api-manager' ); ?><strong><?php echo absint( $resource->product_id ) ?></strong>
        </p>
		<?php if ( ! $hide_product_order_api_keys ) : ?>
        <ul>
            <li>
				<?php esc_html_e( 'Product Order API Key(s):', 'woocommerce-api-manager' ); ?>
                <br><strong><?php echo esc_attr( $resource->product_order_api_key ); ?></strong>
            </li>
        </ul>
	<?php endif;
	endforeach;
endif;