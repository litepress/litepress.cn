<?php
/**
 * 该文件用作博客的首页列表
 */
get_header();
?>
    <main class="wp-body">
        <div class="container">
            <div class="row">
                <div class="col-xl-9">
                    <article class="card theme-boxshadow py-3 px-4 blog-article-list">

                    <?php while (have_posts()) :
                        the_post(); ?>


                        <div class=" pb-3 my-2 event">
                            <h6 class="title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h6>
                            <div class="des">
                <?php the_excerpt(); ?>
              </div>
                            <span class="post-meta">
                发布于：<?php the_date() ?> | 分类：<?php the_category(','); ?> | 阅读量：<?php echo get_post_meta(get_the_ID(), 'views', true) ?: 0; ?>
              </span>
                        </div>


                    <?php endwhile; ?>
                    </article>
                </div>
                <section class="col-xl-3 blog-aside">
                <?php get_sidebar(); ?>
                </section>
            </div>
        </div>
    </main>
<?php
get_footer();