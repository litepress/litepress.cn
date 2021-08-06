<?php

use function LitePress\SVN_Browse\Inc\get_current_dir_path_html;

get_header();

require __DIR__ . '/header.php';
?>
    <main class="container body">
        <section class="pt-3 svn">
            <div class="dir-path">
				<?php echo get_current_dir_path_html( 'file', $path ) ?>
            </div>
            <div class="container mt-3">

                <?php do_action( 'svn-browse-error-message' ); ?>

				<?php if ( ! empty( $body ) ): ?>
                <div class="heti my-3">
				<pre>
                    <code class="php"><?php echo esc_html( $body ); ?>
                    </code>
                </pre>
					<?php endif; ?>
                </div>
        </section>
    </main>
<?php
get_footer();
