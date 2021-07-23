<?php
get_header();

$sidebar = get_post_meta( $post->ID, 'sidebar', true );
?>
    <main class="main-body d-flex align-items-center">
        <div class="container">
            <div class="row justify-content-center">

                <div class="col-xl-4">
                    <section class="email-box wp-card p-3">
                    <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                        <div class="heti">
                        <?php the_content(); ?>
                        </div>
                    </article>
                    <?php while (have_posts()) : the_post(); ?>
                        <!-- #post -->
                    <?php endwhile; // end of the loop. ?>
                    </section>
                </div>
                <?php
                if (('on' === $sidebar || empty($sidebar)) && class_exists('bbPress')) {
	                get_sidebar();
                }
                ?>
                <!-- #content -->

            </div>
        </div>
    </main>


<?php get_footer(); ?>