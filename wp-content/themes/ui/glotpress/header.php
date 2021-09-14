<?php
add_filter('body_class', function ($classes) {
    $classes[] = 'no-js';
    return $classes;
});

remove_action( 'wp_head', '_wp_render_title_tag', 1 );
add_action('wp_head', function () {
    echo '<title>'.gp_title().'</title>';
    gp_head();
    //gp_enqueue_styles( 'gp-base' );
}, 1);

get_header();
?>
<div class="gp-content">
    <div id="gp-js-message" class="gp-js-message"></div>

    <?php if ( gp_notice( 'error' ) ) : ?>
        <div class="error">
            <?php echo gp_notice( 'error' ); ?>
        </div>
    <?php endif; ?>

    <?php if ( gp_notice() ) : ?>
    <div class="container">
        <div class="notice mt-4">
            <?php echo gp_notice(); ?>
        </div>
        </div>
    <?php endif; ?>

    <?php
    /**
     * Fires after the error and notice elements on the header.
     *
     * @since 1.0.0
     */
    do_action( 'gp_after_notices' );
