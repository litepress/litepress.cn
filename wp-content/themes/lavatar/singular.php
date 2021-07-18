<?php
get_header();

$sidebar = get_post_meta( $post->ID, 'sidebar', true );
?>
    <main class="main-body">
        <div class="container">
            <div class="row">
                <section class="email-box wp-card p-3">
                <div class="col-xl-<?php echo ('on' === $sidebar || empty($sidebar)) && class_exists('bbPress') ? '9' : '12'; ?>">
                    <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                        <div class="heti">
                        <?php the_content(); ?>
                        </div>
                    </article>
                    <?php while (have_posts()) : the_post(); ?>
                        <!-- #post -->
                    <?php endwhile; // end of the loop. ?>
                </div>
                <?php
                if (('on' === $sidebar || empty($sidebar)) && class_exists('bbPress')) {
	                get_sidebar();
                }
                ?>
                <!-- #content -->
                </section>
            </div>
        </div>
    </main>


<?php get_footer(); ?>