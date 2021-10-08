<?php
/**
 * Template name: 文档平台首页模板
 * Description: 该模板是文档平台首页模板
 */

get_header();
?>

<?php foreach ( get_categories() as $category ) : ?>

    <div>
        <h2><a href="<?php echo get_category_link( $category ) ?>"><?php echo $category->name ?></a></h2>
		<?php
		// 获取当前分类下的头五篇文章
		$args  = array(
			'numberposts' => 5,
			'category'    => $category->ID,
		);
		$posts = get_posts( $args );

		echo '<ul>';
		foreach ( $posts as $post ) {
			$link = get_permalink( $post->ID );
			echo "<li><a href='{$link}'>{$post->post_title}</a></li>";
		}
		echo '</ul>';
		?>
    </div>

<?php endforeach; ?>

<?php get_footer(); ?>