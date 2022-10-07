<?php
/**
 * The template for displaying product content in the single-product.php template
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/content-single-product.php.
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

use function LitePress\Helper\exist_gp_project;
use function LitePress\Helper\get_product_type_by_category_ids;
use function LitePress\Helper\get_woo_download_url;

defined( 'ABSPATH' ) || exit;

global $product;

/**
 * Hook: woocommerce_before_single_product.
 *
 * @hooked woocommerce_output_all_notices - 10
 */
do_action( 'woocommerce_before_single_product' );

if ( post_password_required() ) {
	echo get_the_password_form(); // WPCS: XSS ok.

	return;
}
?>


<section class="theme-boxshadow bg-white plugin-section-header">
	<?php if ( $product->get_meta( '_banner' ) ): ?>
        <div class="img-container">
            <div class="plugin-banner  text-center" id="plugin-banner-jetpack"
                 style="background:rgb(0 0 0 / 50%)  url(<?php echo $product->get_meta( '_banner' ); ?>) no-repeat center;background-blend-mode: multiply;background-size: cover;">
                <img src="<?php echo $product->get_meta( '_banner' ); ?>" class="img-fluid">
            </div>
        </div>
	<?php endif; ?>
    <header class="plugin-header row align-items-center">

        <div class="entry-thumbnail col-xl-1 col-3">
			<?php echo $product->get_image( 256 ); ?>
        </div>
        <div class="col-xl-9 col-9">
            <h4 class="plugin-title "><?php echo $product->get_title(); ?>
            </h4>
            <span class="byline">By <span class="author vcard"><?php do_action( 'wcy_product_vendor' ); ?></span></span>
        </div>
        <div class="plugin-actions col-xl-2 col-12 text-center">
			<?php $product_price = $product->get_price(); ?>
			<?php if ( 0 === (int) $product_price ): ?>
                <span class="price">
                            <span class="woocommerce-Price-amount amount">
                                <bdi>
                                    <span class="free">免费</span>
                                </bdi>
                            </span>
                        </span>
                <a class="plugin-download btn download-button button-large btn-primary"
                   href="<?php echo $product->get_meta( '_download_url' ) ?: get_woo_download_url( $product->get_id() ); ?>"><i
                            class="fas fa-download"></i> 立即下载</a>
			<?php else: ?>
                <span class="price">
                            <span class="woocommerce-Price-amount amount">
                                <bdi>
                                    <span class="woocommerce-Price-currencySymbol">¥</span><?php echo $product_price; ?>
                                </bdi>
                            </span>
                        </span>
                <a class="plugin-download btn download-button button-large btn-primary"
                   href="<?php echo $product->add_to_cart_url(); ?>"><i class="fad fa-shopping-cart"></i> 立即购买</a>
			<?php endif; ?>
        </div>
    </header>
	<?php $product_type = get_product_type_by_category_ids( $product->get_category_ids() ); ?>
	<?php if ( exist_gp_project( $product->get_slug(), $product_type ) ): ?>
        <div class="locale-banner" dir="auto">
            帮助改进此插件的 <a href="/translate/projects/<?php echo $product_type; ?>s/<?php echo $product->get_slug(); ?>/" target="_blank">简体中文</a> 翻译~ 同时，你可以通过安装 <a href="https://litepress.cn/store/?woo-free-download=273479" target="_blank">WP-China-Yes</a> 插件，并切换应用市场为“LitePress 应用市场”来接收翻译推送。
        </div>
	<?php endif; ?>
</section>


<div id="product-<?php the_ID(); ?>" <?php wc_product_class( '', $product ); ?>>
    <section class="plug-in-details card">
        <div class="woocommerce-tabs wc-tabs-wrapper container theme-boxshadow">
            <div class="row">
                <article class="col-xxl-9">
					<?php
					add_action( 'wcy_woo_product_data_tabs', 'woocommerce_output_product_data_tabs' );

					do_action( 'wcy_woo_product_data_tabs' );


					/**
					 * Hook: woocommerce_before_single_product_summary.
					 *
					 * @hooked woocommerce_show_product_sale_flash - 10
					 * @hooked woocommerce_show_product_images - 20
					 */
					//            do_action('woocommerce_before_single_product_summary');
					?>
                </article>
                <aside class="col-xxl-3 product-aside">
                    <div class="widget plugin-meta">
                        <h2 class="screen-reader-text">Meta</h2>

                        <ul>

                            <li>
                                版本: <strong><?php echo $product->get_meta( '_api_new_version' ) ?: '未知'; ?></strong>
                            </li>

                            <li>
                                最后更新：
                                <strong><span><?php echo human_time_diff( strtotime( $product->get_date_modified() ), time() ); ?>前</span></strong>
                            </li>
                            <li>
                                已启用安装数：
                                <strong><?php echo wcy_prepare_installed_num( $product->get_total_sales() ) ?: '未知'; ?></strong>
                            </li>

                            <li>
                                至少需要WordPress版本：
                                <strong><?php echo $product->get_meta( '_api_version_required' ) ?: '未知'; ?>  </strong>
                            </li>

                            <li>
                                兼容WordPress版本至:
                                <strong><?php echo $product->get_meta( '_api_tested_up_to' ) ?: '未知'; ?> </strong>
                            </li>
                            <li>
                                至少需要PHP版本：<strong><?php echo $product->get_meta( '_api_requires_php' ) ?: '未知'; ?> </strong>
                            </li>
							<?php $tag_ids = wc_get_product_tag_list( $product->get_id() ); ?>
							<?php if ( ! empty( $tag_ids ) ): ?>
                                <li class="clear">标签：
                                    <div class="tags">
										<?php echo str_replace( ', ', '', $tag_ids ); ?>
                                    </div>
                                </li>
							<?php endif; ?>
                        </ul>

                    </div>


                    <div class="widget plugin-ratings">
                        <header>
                            <h4 class="widget-title">评分</h4>
                        </header>
                        <div class="star-rating "><span
                                    style="width:<?php echo $product->get_average_rating() * 20 ?>%"><strong
                                        class="rating"></strong></span></div>

                        <div class="user-rating">
                            <a class="btn btn-primary btn-small"
                               href="#tab-reviews">添加我的评价</a>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </section>

    <div class="summary entry-summary">
		<?php
		/**
		 * Hook: woocommerce_single_product_summary.
		 *
		 * @hooked woocommerce_template_single_title - 5
		 * @hooked woocommerce_template_single_rating - 10
		 * @hooked woocommerce_template_single_price - 10
		 * @hooked woocommerce_template_single_excerpt - 20
		 * @hooked woocommerce_template_single_add_to_cart - 30
		 * @hooked woocommerce_template_single_meta - 40
		 * @hooked woocommerce_template_single_sharing - 50
		 * @hooked WC_Structured_Data::generate_product_data() - 60
		 */
		//                do_action('woocommerce_single_product_summary');
		?>
    </div>

	<?php
	/**
	 * Hook: woocommerce_after_single_product_summary.
	 *
	 * @hooked woocommerce_output_product_data_tabs - 10
	 * @hooked woocommerce_upsell_display - 15
	 * @hooked woocommerce_output_related_products - 20
	 */
	//do_action('woocommerce_after_single_product_summary');
	?>
</div>

<?php do_action( 'woocommerce_after_single_product' ); ?>
</div>
</main>