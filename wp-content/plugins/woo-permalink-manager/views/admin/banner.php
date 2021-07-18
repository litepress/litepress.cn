<?php

if ( ! defined('WPINC')) {
    die;
}

/**
 * @var $fm \Premmerce\SDK\V2\FileManager\FileManager
 */
?>

<div class="notice woo-permalink-manager-banner" data-get-woo-permalink-manager-banner>
    <p>
        <img src="<?php echo esc_url($fm->locateAsset('admin/images/filter-icon.png')); ?>"
             alt="<? esc_attr_e( 'Premmerce WooCommerce Product Filter Premium', 'premmerce-url-manager' ); ?>">
    </p>
    <h1 class="woo-permalink-manager-banner--heading">
        <?php esc_html_e( 'Premmerce WooCommerce Product Filter Premium', 'premmerce-url-manager' ); ?>
    </h1>
    <p>
        <?php esc_html_e( 'Get clean URLs and additional Pro SEO settings for your Filter pages with Premmerce WooCommerce Filter Premium for only $69!', 'premmerce-url-manager' ); ?>
        <a
                href="<?php echo esc_url(add_query_arg(array('action' => 'premmerce_url_manager_ignore_banner'),
                    site_url('wp-admin/admin-ajax.php'))); ?>"
                class="notice-dismiss dashicons dashicons-dismiss dashicons-dismiss-icon"
                data-get-woo-permalink-manager-banner--ignore></a>

    </p>
    <p>
        <a href="<?php echo esc_url( 'https://premmerce.com/premmerce-woocommerce-product-filter/' ); ?>"
           class="button button-primary button-hero" style="text-decoration: none;">
            <?php esc_html_e( 'Get it now', 'saleszone' ); ?>
        </a>
    </p>
</div>