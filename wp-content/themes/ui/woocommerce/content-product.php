<?php
/**
 * The template for displaying product content within loops
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/content-product.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.6.0
 */

use LitePress\I18n\i18n;

defined( 'ABSPATH' ) || exit;

global $product;

// Ensure visibility.
if ( empty( $product ) || ! $product->is_visible() ) {
	return;
}
?>
<li <?php wc_product_class( '', $product ); ?>>
	<?php
	/**
	 * Hook: woocommerce_before_shop_loop_item.
	 *
	 * @hooked woocommerce_template_loop_product_link_open - 10
	 */
	//do_action( 'woocommerce_before_shop_loop_item' );

	/**
	 * Hook: woocommerce_before_shop_loop_item_title.
	 *
	 * @hooked woocommerce_show_product_loop_sale_flash - 10
	 * @hooked woocommerce_template_loop_product_thumbnail - 10
	 */
	//do_action( 'woocommerce_before_shop_loop_item_title' );

	/**
	 * Hook: woocommerce_shop_loop_item_title.
	 *
	 * @hooked woocommerce_template_loop_product_title - 10
	 */
	//do_action( 'woocommerce_shop_loop_item_title' );

	/**
	 * Hook: woocommerce_after_shop_loop_item_title.
	 *
	 * @hooked woocommerce_template_loop_rating - 5
	 * @hooked woocommerce_template_loop_price - 10
	 */
	//do_action( 'woocommerce_after_shop_loop_item_title' );

	/**
	 * Hook: woocommerce_after_shop_loop_item.
	 *
	 * @hooked woocommerce_template_loop_product_link_close - 5
	 * @hooked woocommerce_template_loop_add_to_cart - 10
	 */
	//do_action( 'woocommerce_after_shop_loop_item' );

	global $product;

	$product_price = $product->get_price();
	?>
	<?php if ( wcy_is_plugin_list() ): ?>
    <div class="card plugin theme-boxshadow">
        <div class="row g-0 ">
            <div class="project-top row">
                <div class="col-5 center">
                    <section class="img-box">
						<?php echo $product->get_image( 128 ); ?></section>
                </div>
                <div class="col-7">
                    <h5 class="card-title"><a
                                href="<?php echo $product->get_permalink(); ?>"
                                data-bs-toggle="tooltip"
                                data-bs-original-title="<?php echo $product->get_name(); ?>"><?php echo esc_html( $product->get_title() ); ?></a>
                    </h5>
                    <div class="woocommerce row"><span class="price col-4">
                            <span class="woocommerce-Price-amount amount">
                                <bdi>
                                    <?php if ( 0 < (int) $product_price ): ?>
                                        <span class="woocommerce-Price-currencySymbol">¥</span><?php echo $product_price ?>
                                    <?php else: ?>
                                        <span class="free">免费</span>
                                    <?php endif; ?>
                                </bdi>
                            </span>
                        </span>
                        <div class="star-rating "><span
                                    style="width:<?php echo $product->get_average_rating() * 20 ?>%">评分 <strong
                                        class="rating"></strong></span>
                        </div>
                    </div>
                    <!--<p class="card-text project-description">
                        <small><?php /*echo $product->get_short_description(); */ ?></small>
                    </p>-->
                    <div class="card-text project-description">
                        <pre><?php echo esc_html( $product->get_short_description() ); ?></pre>
                    </div>
                </div>
            </div>
			<?php wc_get_template_part( 'content', 'product-meta' ); ?>
        </div>
		<?php elseif ( wcy_is_theme_list() ): ?>
            <div class="card theme theme-boxshadow">
                <div class="theme-screenshot">
					<?php echo $product->get_image( 128 ); ?>
                    <a href="<?php echo $product->get_permalink(); ?>"><span class="more-details">查看详情</span></a>
                </div>
                <div class="card-body row align-items-center">
                    <p class="card-text">
                        <a href="<?php echo $product->get_permalink(); ?>"
                           data-bs-toggle="tooltip"
                           data-bs-original-title="<?php echo $product->get_name(); ?>"><?php echo esc_html( $product->get_title() ); ?></a>
                    </p>

                    <div class="woocommerce row"><span class="price col-4">
                            <span class="woocommerce-Price-amount amount">
                                <bdi>
                                    <?php if ( 0 < (int) $product_price ): ?>
                                        <span class="woocommerce-Price-currencySymbol">¥</span><?php echo $product->get_price(); ?>
                                    <?php else: ?>
                                        <span class="free">免费</span>
                                    <?php endif; ?>
                                </bdi>
                            </span>
                        </span>
                        <div class="star-rating "><span
                                    style="width:<?php echo $product->get_average_rating() * 20 ?>%">评分 <strong
                                        class="rating"></strong></span>
                        </div>
                    </div>
                </div>
				<?php wc_get_template_part( 'content', 'product-meta' ); ?>
            </div>
		<?php endif; ?>
</li>
