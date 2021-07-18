<?php
if (! defined('ABSPATH')) {
    exit;
}

$featuredInCompanies = [
    [
        'title' => 'Wordpress',
        'img'   => $wp_logo
    ],
    [
        'title' => 'WP Rocket',
        'img'   => $wp_rocket_logo
    ],
    [
        'title' => 'LearnWoo',
        'img'   => $learnwoo_logo
    ],
    [
        'title' => 'WPLift',
        'img'   => $wp_lift_logo
    ],
    [
        'title' => 'WP Mayor',
        'img'   => $mayor_logo
    ],
    [
        'title' => 'ManageWP',
        'img'   => $managewp_logo
    ],
];
?>
<div class="c-section c-section--grey c-section--md wow hidden-md hidden-sm hidden-xs animated"
    style="visibility: visible;">
    <div class="c-section__container">
        <div class="c-section__title fade-up fade-up--step-1">
            <?php _e('Featured in', 'premmerce-url-manager'); ?>
        </div>
        <div class="c-section__content">
            <div class="row row--ib row--ib-mid">

                <?php foreach ($featuredInCompanies as $company) : ?>
                <div class="col-md-2">
                    <div class="c-vendor-logo">
                        <img class="c-vendor-logo__img" src="<?php echo $company['img']; ?>"
                            alt="<?php echo $company['title']; ?>">
                    </div>
                </div>
                <?php endforeach; ?>


            </div>
        </div>
    </div>
</div>
