<?php
/**
 * The Template for displaying a single feature request.
 *
 * @author        James Kemp
 * @version       1.0.0
 *
 * @var WP_Query $request_query
 * @var bool     $sidebar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
} ?>

<?php
/**
 * jck_sfr_before_wrapper hook.
 */
do_action( 'jck_sfr_before_wrapper' );
?>

    <main class="container">
    <div class="row">
		<?php
		/**
		 * jck_sfr_before_columns hook.
		 */
		do_action( 'jck_sfr_before_columns' );
		?>
		<article class=" jck-sfr-container__col col-xl-9">
            <section class="theme-boxshadow bg-white jck-sfr-section">
			<?php if ( $request_query->have_posts() ) : ?>
				<?php while ( $request_query->have_posts() ) : $request_query->the_post(); ?>
					<?php
					/**
					 * jck_sfr_before_single_loop hook.
					 *
					 * @hooked JCK_SFR_Notices::print_notices() - 10
					 */
					do_action( 'jck_sfr_before_single_loop' );

					/**
					 * jck_sfr_loop hook.
					 *
					 * @hooked JCK_SFR_Template_Hooks::loop_content() - 10
					 */
					do_action( 'jck_sfr_loop' );

					/**
					 * jck_sfr_after_single_loop hook.
					 *
					 * @hooked JCK_SFR_Template_Hooks::comments() - 10
					 */
					do_action( 'jck_sfr_after_single_loop' );
					?>
				<?php endwhile; ?>
			<?php endif; ?>
            </section>
		</article>

		<?php if ( $sidebar ) { ?>
    <aside class="jck-sfr-container__col col-xl-3">
				<?php
				/**
				 * jck_sfr_sidebar hook.
				 */
				do_action( 'jck_sfr_sidebar' );
				?>
    </aside>
		<?php } ?>


    </div>
    </main>

<?php
/**
 * jck_sfr_after_wrapper hook.
 */
do_action( 'jck_sfr_after_wrapper' );
?>