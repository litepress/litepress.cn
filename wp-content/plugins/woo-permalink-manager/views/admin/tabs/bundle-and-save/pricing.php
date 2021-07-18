<?php

if (! defined('ABSPATH')) {
    exit;
}

$premmercePlugins = [
    [
       'title' => 'Premmerce Permalink Manager for WooCommerce',
       'link'  => '#1118',
    ],
    [
       'title' => 'Premmerce WooCommerce Product Filter',
       'link'  => '#1818',
    ],
    [
       'title' => 'Premmerce WooCommerce Variation Swatches',
       'link'  => '#4173',
    ],
    [
       'title' => 'WooCommerce Product Search',
       'link'  => '#1847',
    ]
];

if (!function_exists('premmerce_bundle_licence_header')) {
    function premmerce_bundle_licence_header($title, $symbol, $price, $savePrice) {
        ?>
<div class="c-license__header">
    <div class="c-license__inner">
        <div class="c-license__title">
            <?php echo $title; ?>
        </div>
        <div class="c-license__price">
            <span class="woocommerce-Price-amount amount"><span
                    class="woocommerce-Price-currencySymbol"><?php echo $symbol; ?></span><?php echo $price; ?></span>
        </div>
        <div class="c-license__save-price">
            <span class="woocommerce-Price-amount amount"><span
                    class="woocommerce-Price-currencySymbol"><?php echo $symbol; ?></span><?php echo $savePrice; ?></span>
        </div>

    </div>
</div>
<?php
    }
}

if (!function_exists('premmerce_bundle_licence_body')) {
    function premmerce_bundle_licence_body($description, $plugins) {
        ?>
<div class="c-license__body">
    <div class="c-license__inner">
        <div class="c-license__body-inner">
            <div class="c-license__body-description">
                <span><?php echo $description; ?></span>
            </div>
        </div>
        <div class="c-license__body-inner">
            <ul class="c-license__plugins-list">
                <?php foreach ($plugins as $key => $plugin) : ?>
                <li class="c-license__plugin">
                    <a class="c-license__plugin-link" href="<?php echo $plugin['link']; ?>">
                        <i class="fa fa-check" aria-hidden="true"></i>
                        <?php echo $plugin['title']; ?>
                    </a>
                </li>
                <?php endforeach; ?>

            </ul>
        </div>
    </div>
</div>
<?php
    }
}

if (!function_exists('premmerce_bundle_licence_footer')) {
    function premmerce_bundle_licence_footer($licence, $freemius_image) {
        ?>
<div class="c-license__footer">
    <div class="c-license__inner">
        <div class="c-license__purchase">
            <button class="o-button o-button--primary o-button--lg o-button--caps purchase" target="_blank"
                data-freemius-bundle data-licence="<?php echo esc_attr($licence); ?>"
                data-freemius-image="<?php echo esc_url($freemius_image); ?>">
                <?php _e('Buy now', 'premmerce-url-manager'); ?>
            </button>
        </div>
    </div>
</div>
<?php
    }
}
?>

<div class="c-section">
    <div class="c-section__container wow animated" style="visibility: visible;">
        <div class="c-section__content">
            <div class="row row--ib row--vindent-m">
                <div class="variations-tabs">
                    <!-- Variation tabs -->
                    <div class="variations-tabs__header variations-tabs__header--inline">
                        <button class="variations-tabs__link is-active" type="button"
                            data-variation-tab-target="annual">
                            <?php _e('Annual', 'premmerce-url-manager'); ?>
                        </button>
                        <button class="variations-tabs__link " type="button" data-variation-tab-target="lifetime">
                            <?php _e( 'Lifetime', 'premmerce-url-manager'); ?>
                        </button>
                    </div>
                    <!-- Variation tabs body annual -->
                    <div class="variations-tabs__body " data-variation-tab-content="annual">

                        <div class="variant-list variant-list--in-tab">
                            <div class="row row--ib row--vindent-m row--center">

                                <div class="col-xs-12 col-sm-12 col-md-4">
                                    <div class="c-license c-license--1">
                                        <?php premmerce_bundle_licence_header('One Site', '$', '99.99', '219'); ?>

                                        <?php premmerce_bundle_licence_body('Access to Best Premmerce plugins for 1 domain', $premmercePlugins); ?>

                                        <?php premmerce_bundle_licence_footer('1', $premmerce_logo); ?>

                                    </div>

                                </div>

                                <div class="col-xs-12 col-sm-12 col-md-4">
                                    <div class="c-license c-license--20">

                                        <?php premmerce_bundle_licence_header('Pro', '$', '299.99', '1,100'); ?>

                                        <?php premmerce_bundle_licence_body('Access to Best Premmerce plugins for 5 domains', $premmercePlugins); ?>

                                        <?php premmerce_bundle_licence_footer('5', $premmerce_logo); ?>

                                    </div>

                                </div>

                            </div>
                        </div>

                    </div>

                    <!-- Variation tabs body lifetime -->
                    <div class="variations-tabs__body hidden" data-variation-tab-content="lifetime">

                        <div class="variant-list variant-list--in-tab">
                            <div class="row row--ib row--vindent-m row--center">

                                <div class="col-xs-12 col-sm-12 col-md-4">
                                    <div class="c-license c-license--1">

                                        <?php premmerce_bundle_licence_header('One Site', '$', '299.99', '660'); ?>

                                        <?php premmerce_bundle_licence_body('Access to Best Premmerce plugins for 1 domain', $premmercePlugins); ?>

                                        <?php premmerce_bundle_licence_footer('1', $premmerce_logo); ?>

                                    </div>

                                </div>


                                <div class="col-xs-12 col-sm-12 col-md-4">
                                    <div class="c-license c-license--20">

                                        <?php premmerce_bundle_licence_header('Pro', '$', '999.99', '3,300'); ?>

                                        <?php premmerce_bundle_licence_body('Access to Best Premmerce plugins for 5 domains', $premmercePlugins); ?>

                                        <?php premmerce_bundle_licence_footer('5', $premmerce_logo); ?>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
