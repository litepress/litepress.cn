<?php
get_header();
?>

    <main class="wp-body">
        <div class="container">
            <div class="row">
                <div class="col-xl-9">
                    <article class="card theme-boxshadow py-3 px-4 blog-article-list">
						<?php if ( ! is_single() && ! is_page() && ! is_home() ): ?>
                            <h5><?php the_archive_title() ?></h5>
						<?php endif; ?>

						<?php while ( have_posts() ) :
						the_post(); ?>
                        <header>
                            <h5><?php the_title(); ?></h5>
                            <div class="post-meta">
                                发布于：<?php the_date() ?>
								<?php if ( ! empty( get_the_category() ) ): ?>
                                    | 分类：<?php the_category( ',' ); ?>
								<?php endif; ?>
                                |
                                作者：<a href="/user/<?php the_author_meta( 'user_login' ) ?>" target="_blank"><?php the_author_meta( 'display_name' ) ?></a>
                                | 阅读：<?php echo get_post_meta( get_the_ID(), 'views', true ) ?: 0; ?> 次
                            </div>
                        </header>
                        <hr class="dropdown-divider mb-3">
                        <div class="content heti "><?php the_content(); ?></div>
                        <section class="ltp-single-content mt-3">
							<?php if ( comments_open() || get_comments_number() ) :
								comments_template();
							endif; ?>
							<?php endwhile; ?>
                        </section>
                        <div class="pagination">
                            <div class="previous"><?php previous_posts_link( '上一页' ); ?></div>
                            <div class="next"><?php next_posts_link( '下一页' ); ?></div>
                        </div>
                    </article>
                </div>
                <section class="col-xl-3  blog-aside">
					<?php get_sidebar(); ?>
                </section>
                </section>
            </div>
        </div>
    </main>
<?php
get_footer();
?>