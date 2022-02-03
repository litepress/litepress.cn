<?php
/**
 * Template name: 手册资源首页模板
 */

get_header();
?>
    <main class="wp-body">
        <div class="container">
            <div class="row g-5">
				<?php echo do_shortcode( '[wedocs col="3"]' ) ?>
            </div>
        </div>
    </main>
<?php get_footer(); ?>