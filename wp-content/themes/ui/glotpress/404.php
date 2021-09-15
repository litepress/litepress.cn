<?php

gp_title( __( '似乎什么也没有 - LitePress翻译平台', 'glotpress' ) );

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
					<h1 class="display-1 mb-1">该项目未收录</h1>
					<h5 class="text-gray-soft text-regular mb-4">可能的原因是该项目不位于 wordpress.org 中，或是不支持多语言。<br/>对于类似 Elementor Pro 这样支持多语言的第三方项目，你可以向我们申请免费托管。</h5>
					<a class="btn btn-primary" href="/">回到首页</a>
				</div>
			</section>

		<?php endif; ?>
	</main>
<?php
get_footer();

