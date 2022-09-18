<?php

use function LitePress\SVN_Browse\Inc\get_current_dir_path_html;

add_filter('wp_title', function () {
	return '目录浏览 - LitePress SVN';
});

get_header();

require __DIR__ . '/header.php';
?>
    <main class="container body">
        <section class="pt-3 svn">
            <div class="dir-path">
		        <?php echo get_current_dir_path_html( 'dir', $path ) ?>
            </div>

	        <?php do_action( 'svn-browse-error-message' ); ?>

            <?php if ( ! empty( $body ) ): ?>
            <div class="container mt-3">
                <?php echo $body; ?>
            </div>
            <?php endif; ?>
        </section>
    </main>
<?php
get_footer();
