<?php
/**
 * The Template for displaying product archives, including the main shop page which is a post type archive
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/archive-product.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.4.0
 */

defined('ABSPATH') || exit;

get_header('shop');
?>

    <!--<header class="woocommerce-products-header">-->


    <section class="woocommerce_archive_description">
        <div class="container">

            <?php
            /**
             * Hook: woocommerce_archive_description.
             *
             * @hooked woocommerce_taxonomy_archive_description - 10
             * @hooked woocommerce_product_archive_description - 10
             */
            do_action('woocommerce_archive_description');
            ?>

        </div>
    </section>
    <!--</header>-->

<?php
/**
 * Hook: woocommerce_before_main_content.
 *
 * @hooked woocommerce_output_content_wrapper - 10 (outputs opening divs for the content)
 * @hooked woocommerce_breadcrumb - 20
 * @hooked WC_Structured_Data::generate_website_data() - 30
 */
do_action('woocommerce_before_main_content');

?>

    <section class="wp-filter">
        <div class="row theme-boxshadow">
            <ul>
                <li>
                    <i>价格：</i>
                    <span class="filter-cost">
                        <ul class="filter-cost-ul">
                            <li class="all"><a href="?" class="active">全部</a></li>
                            <li><a href="javascript:;" rel="max_price=0.1"
                                   onclick="Url.updateSearchParam('min_price');Url.updateSearchParam({ max_price: 0.1});window.location.href=Url.getLocation();">免费</a></li>
                            <li><a href="javascript:;" rel="min_price=0.1" onclick="myFunction()">付费</a></li>
                            <!--                            <li><a href="javascript:;" rel="min_price=0.1&max_price=10000" onclick="Url.updateSearchParam({min_price: 0.1, max_price: 10000});window.location.href=Url.getLocation();">付费</a></li>-->
                       <script>
function myFunction() {
    Url.updateSearchParam("max_price");Url.updateSearchParam({ min_price: 0.1});window.location.href=Url.getLocation();
/*    url_noparm = location.protocol + '//' + location.host + location.pathname;
    url_noparm5 = url_noparm.split("/").splice(0, 5).join("/");
urlprice = url_noparm5+"?min_price=0.1"
    window.location.href = urlprice;*/
}
                       </script>
                        </ul>
                </span>
                </li>
                <li>
                    <i>分类：</i>
                    <span class="filter-categories">
                    <ul class="filter-cost-ul">
                        <li class="all"><a href="?" class="categories-a active">全部</a></li>
                    </ul>
                </span>
            </ul>
        </div>
    </section>
<?php

if (woocommerce_product_loop()) {

    /**
     * Hook: woocommerce_before_shop_loop.
     *
     * @hooked woocommerce_output_all_notices - 10
     * @hooked woocommerce_result_count - 20
     * @hooked woocommerce_catalog_ordering - 30
     */
    do_action('woocommerce_before_shop_loop');

    woocommerce_product_loop_start();

    if (wc_get_loop_prop('total')) {
        while (have_posts()) {
            the_post();

            /**
             * Hook: woocommerce_shop_loop.
             */
            do_action('woocommerce_shop_loop');

            wc_get_template_part('content', 'product');
        }
    }

    woocommerce_product_loop_end();

    /**
     * Hook: woocommerce_after_shop_loop.
     *
     * @hooked woocommerce_pagination - 10
     */
    do_action('woocommerce_after_shop_loop');
} else {
    /**
     * Hook: woocommerce_no_products_found.
     *
     * @hooked wc_no_products_found - 10
     */
    do_action('woocommerce_no_products_found');
}

/**
 * Hook: woocommerce_after_main_content.
 *
 * @hooked woocommerce_output_content_wrapper_end - 10 (outputs closing divs for the content)
 */
do_action('woocommerce_after_main_content');

/**
 * Hook: woocommerce_sidebar.
 *
 * @hooked woocommerce_get_sidebar - 10
 */


get_footer('shop');
