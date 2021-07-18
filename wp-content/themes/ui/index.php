<?php
/**
 * The main template file
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * E.g., it puts together the home page when no home.php file exists.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package WordPress
 * @subpackage Twenty_Twenty
 * @since Twenty Twenty 1.0
 */

get_header();
?>
    <main class="container body">
		<?php if ( have_posts() ) : ?>
			<?php while ( have_posts() ) : the_post(); ?>

                <div class="serach-result serach-menu">
					<?php the_content(); ?>
                </div>
			<?php endwhile; ?>
		<?php else : ?>

            <style>
                main.container {
                    flex: 1;
                    display: flex;
                    align-items: center;
                }

                .breadcrumb {
                    display: none;
                }
            </style>
            <section class="text-center w-100">
                <div class="container">
                    <h1 class="display-1 mb-1">404! 😭</h1>
                    <h5 class="text-gray-soft text-regular mb-4">这里似乎没有东西。</h5>
                    <a class="btn btn-primary" href="/">回到首页</a>
                </div>
            </section>

		<?php endif; ?>
    </main>
<?php
get_footer();
