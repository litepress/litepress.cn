<?php
if (! defined('ABSPATH')) {
    exit;
}

$premmercePluginsInfo = [
    [
        'id'    => '1118',
        'title' => __('Permalink Manager Plugin', 'premmerce-url-manager'),
        'text'  => __('Allows you to configure URL generation strategy for your WooCommerce based store. Removing /product-category base from URL. Able to generate a product category URL in such a way that only its slug can be seen, removing all the prefixes from URL, leaving just the category or product name, and automatic adds of the ‘rel=canonical’ attribute to duplicate pages for SEO ranking improving.', 'premmerce-url-manager'),
        'link'  => 'https://premmerce.com/woocommerce-permalink-manager',
        'img'   => $permalink_img
    ],
    [
        'id'    => '1818',
        'title' => __('Product Filter Plugin', 'premmerce-url-manager'),
        'text'  => __('Is a convenient and flexible tool for managing filters for WooCommerce products. Among the main features of this plugin there is a single widget that manages the display of all available filters. Comparing to the standard WooCommerce filters, Premmerce WooCommerce Product Filter has a well thought out caching system for the load speed improving.', 'premmerce-url-manager'),
        'link'  => 'https://premmerce.com/premmerce-woocommerce-product-filter',
        'img'   => $filter_img
    ],
    [
        'id'    => '4173',
        'title' => __('Variation Swatches Plugin', 'premmerce-url-manager'),
        'text'  => __('Flexibly extends standard features of the WooCommerce attributes and variations.The ability to highlight the main attributes and display them on the product category page and possibility to add a description to the attribute. The ability to add a product variation to the cart directly on the product category page.', 'premmerce-url-manager'),
        'link'  => 'https://premmerce.com/premmerce-woocommerce-variation-swatches',
        'img'   => $variants_img
    ],
    [
        'id'    => '1847',
        'title' => __('Product Search Plugin', 'premmerce-url-manager'),
        'text'  => __('Makes the WooCommerce product search more flexible and efficient and gives the additional search results due to the spell correction. With the help of this plugin, the products search results within your store will be as relevant as possible for your potential customers. With our plugin, you no longer have to create the databases of synonyms and duplicate the words with common mistakes in the product name or its description.', 'premmerce-url-manager'),
        'link'  => 'https://premmerce.com/premmerce-woocommerce-product-search',
        'img'   => $search_img
    ]
];
?>

<div class="c-section-price">
    <div class="c-section__container">
        <div class="c-section-price__row">
            <div class="c-section-price__content c-section-price__content--100">
                <p class="c-section-price__text">
                    <?php _e('The Premmerce bundle consists of interchangeably combined plugins to
                    ensure you’re getting the whole package. Each one of our most popular plugins performs smoothly on
                    its own and works wonders when paired with the other ones. From “clean” links and well-thought-out
                    filter widgets to displaying the product variation data right on the category page and actualizing
                    search results, we’ve got you covered. All of it and everything in between (bulk-generated landing
                    pages and spell correction, to name just a few) makes up for the full-fledged toolkit for high SEO
                    rankings, advanced search and filter management in categories. And you can transform your online
                    shop for more than 50% less compared to acquiring the plugins separately.', 'premmerce-url-manager'); ?>
                </p>
            </div>
        </div>
    </div>
</div>

<?php foreach ($premmercePluginsInfo as $key => $plugin) { ?>
<div class="c-section-price" id="<?php echo $plugin['id']; ?>" tabindex="-1">
    <div class="c-section__container">
        <div class="c-section-price__row">
            <div class="c-section-price__content">
                <h3 class="c-section-price__title">
                    <?php echo $plugin['title']; ?>
                </h3>
                <p class="c-section-price__text">
                    <?php echo $plugin['text']; ?>
                </p>
                <p>
                    <a class="c-section-price__more" href="<?php echo $plugin['link']; ?>" target="_blank">
                        <?php _e( 'Read more', 'premmerce-url-manager'); ?>
                    </a>
                </p>
            </div>
            <div class="c-section-price__image">
                <img class="aligncenter size-full" src="<?php echo $plugin['img']; ?>"
                    alt="<?php echo $plugin['title']; ?>">
            </div>
        </div>
    </div>
</div>
<?php } ?>
