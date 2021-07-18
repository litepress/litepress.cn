<?php
if (! defined('ABSPATH')) {
    exit;
}

$premmerceTestimonials = [
    [
        'content' => __('Great, free, useful plugin if you want your category link to be yourURL/category name and product link to be yourURL/product-name. Even skips parent category names. Thank you so much!', 'premmerce-url-manager'),
        'name'    => 'Riley Pearcy',
        'img'     => $riley_pearcy
    ],
    [
        'content' => __('THi All, We us this plugin for a big website we run. This plugin works with the famous WPML plugin. It’s removes the Product category base and the shop link in a Multilingual website. Must have for all WooCommerce users!!! Regards from a satisfied user.', 'premmerce-url-manager'),
        'name'    => 'Lian Perry',
        'img'     => $lian_perry
    ],
    [
        'content' => __('The Premmerce Filter is BY FAR the best product filter plugin available for WordPress. It works well out of the box with a great layout. It’s something that you would think would be so simple, but finding a great product filter tool sure wasn’t easy. Really love how well it integrates with the Premmerce Brands plugin as well. Thanks!!', 'premmerce-url-manager'),
        'name'    => 'Rommie Mercer',
        'img'     => $rommie_mercer
    ]
];
?>
<section class="c-section c-section--dark-bg">
    <div class="c-section__container wow animated" style="visibility: visible;">
        <h2 class="c-section__title fade-up fade-up--step-1">
            <span style="font-weight: 400;">
                <?php _e('What clients say about Premmerce', 'premmerce-url-manager'); ?>
            </span>
        </h2>
        <div class="c-section__content fade-up fade-up--step-2">
            <div class="widget-primary" data-slider="widget-primary">
                <div class="widget-primary__inner">
                    <div class="row row--ib">

                        <div aria-live="polite" class="draggable">
                            <div class="">
                                <?php foreach ($premmerceTestimonials as $key => $testimonial) { ?>
                                <div class="col-xs-12 col-sm-6 col-md-4">
                                    <div class="testimonials" data-same-height="testimonials"
                                        style="min-height: 371px;">
                                        <div class="testimonials__content"><?php echo $testimonial['content'] ?></div>
                                        <div class="testimonials__user">
                                            <div class="testimonials__user-image"><img class="aligncenter size-full"
                                                    src="<?php echo $testimonial['img'] ?>"
                                                    alt="<?php echo $testimonial['name'] ?>"></div>
                                            <div class="testimonials__user-info">
                                                <div class="testimonials__user-name">
                                                    <?php echo $testimonial['name'] ?>
                                                </div>
                                                <div class="testimonials__user-link"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
