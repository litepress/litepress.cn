<?php

if (! defined('ABSPATH')) {
    exit;
}

use Premmerce\UrlManager\Admin\BundleAndSave;
?>

<div class="c-section wow hidden-md hidden-sm hidden-xs animated" style="visibility: visible;">
    <div class="c-section__container">
        <div class="active-users-reviews">
            <img class="active-users-reviews__thanks" src="<?php echo $thanks_img; ?>" alt="thanks">
            <?php _e('to our', 'premmerce-url-manager'); ?> <span class="active-users-reviews__count">70,000+</span>
            <?php _e('active users for 5 star', 'premmerce-url-manager'); ?>
            <span class="active-users-reviews__stars">
                <?php for ($i=0; $i < 5; $i++) : ?>
                <svg class="svg-icon svg-icon--rating-star">
                    <?php BundleAndSave::premmerce_use_svg_symbol($svg, 'rating-star'); ?>
                </svg>
                <?php endfor; ?>

            </span> <?php _e('reviews on', 'premmerce-url-manager'); ?> <a class="active-users-reviews__link"
                href="https://profiles.wordpress.org/premmerce#content-plugins" target="_blank"
                rel="noopener">WordPress.org</a>
        </div>
    </div>
</div>