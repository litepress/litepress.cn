<?php
/**
 * 文档平台搜索页模板
 */

get_header();
?>

<?php while ( have_posts() ) : the_post(); ?>
	<div class="post-card">
		<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
		<span>
            <?php
            $content = get_the_content();
            $content = preg_replace( '|<\w+[^>]*>|', '', $content );
            ?>
            <?php echo mb_substr( $content, 0, 200 ) ?>
        </span>
	</div>
	<hr>
<?php endwhile; ?>

<div class="pagination">
	<div class="previous"><?php previous_posts_link( '上一页' ); ?></div>
	<div class="next"><?php next_posts_link( '下一页' ); ?></div>
</div>

<?php get_footer(); ?>
