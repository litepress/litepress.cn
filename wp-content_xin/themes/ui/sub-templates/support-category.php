<?php
/**
 * 文档平台的目录列表
 */

get_header();
?>
<main class="wp-body">
    <div class="container">
        <div class="row g-4">
<?php while ( have_posts() ) : the_post(); ?>
    <div class="col-xl-4">
        <div class="card theme-boxshadow p-5">
        <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
        <span>
            <?php
            $content = get_the_content();
            $content = preg_replace( '|<\w+[^>]*>|', '', $content );
            ?>
            <?php echo mb_substr( $content, 0, 200 ) ?>
        </span>

    </div>
    </div>

<?php endwhile; ?>

<div class="pagination justify-content-center">
    <div class="previous"><?php previous_posts_link( '上一页' ); ?></div>
    <div class="next"><?php next_posts_link( '下一页' ); ?></div>
</div>
        </div>
    </div>
</main>
<?php get_footer(); ?>
