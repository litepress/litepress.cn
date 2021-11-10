<?php
/**
 * Template name: 文档平台首页模板
 * Description: 该模板是文档平台首页模板
 */

get_header();
?>
<main class="wp-body">
    <div class="container">
        <div class="row g-5">



<?php foreach ( get_categories() as $category ) : ?>

    <div class="col-xl-4 info-box">
        <div class="card theme-boxshadow p-5">
        <span class="d-flex justify-content-center icon-wrapper"><i class="fab fa-wordpress"></i></span>
        <h3 class="text-center"><a href="<?php echo get_category_link( $category ) ?>"><?php echo $category->name ?></a></h3>
        <p>Learn about WordPress, both as a free software, and a community.</p>
        <?php
		// 获取当前分类下的头五篇文章
		$args  = array(
			'numberposts' => 5,
			'category'    => $category->ID,
		);
		$posts = get_posts( $args );

		echo '<ul class="meta-list">';
		foreach ( $posts as $post ) {
			$link = get_permalink( $post->ID );
			echo "<li><a href='{$link}'>{$post->post_title}</a></li>";
		}
		echo '</ul>';
		?>
        </div>
    </div>

<?php endforeach; ?>
        </div>
    </div>
</main>
<?php get_footer(); ?>