<?php
/**
 * bbpress帖子内容模板文件
 */
get_header();

$sidebar = get_post_meta( $post->ID, 'sidebar', true );
?>
    <main class="wp-body">
            <div class="container">
                <div class="row">
                    <div class="col-xl-<?php echo ( 'on' === $sidebar || empty( $sidebar ) ) && class_exists( 'bbPress' ) ? '9' : '12'; ?>">
	                    <?php while ( have_posts() ) : the_post(); ?>
                        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                            <div id="lightgallery">
								<?php the_content(); ?>
                            </div>
                        </article>
                            <!-- #post -->
						<?php endwhile; // end of the loop. ?>
                    </div>
					<?php
					if ( ( 'on' === $sidebar || empty( $sidebar ) ) && class_exists( 'bbPress' ) ) {
						get_sidebar();
					}
					?>
                    <!-- #content -->
                </div>
            </div>
    </main>
    <!-- #primary -->


<?php get_footer(); ?>