<?php
/**
 * The Template for displaying feature request archives.
 *
 * @author        James Kemp
 * @version       1.0.0
 *
 * @var WP_Query $jck_sfr_requests
 * @var bool     $sidebar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $jck_sfr_requests; ?>

<?php
/**
 * jck_sfr_before_wrapper hook.
 */
do_action( 'jck_sfr_before_wrapper', $args );
?>

	<main class="container">
        <div class="row">
		<?php
		/**
		 * jck_sfr_before_columns hook.
		 */
		do_action( 'jck_sfr_before_columns', $args );
		?>

		<article class=" jck-sfr-container__col col-xl-9">
            <section class="theme-boxshadow bg-white jck-sfr-section">
			<?php
			/**
			 * jck_sfr_before_main_content hook.
			 *
			 * @hooked JCK_SFR_Notices::print_notices() - 10
			 * @hooked JCK_SFR_Template_Hooks::submission_form() - 20
			 * @hooked JCK_SFR_Template_Hooks::filters() - 30
			 */
			do_action( 'jck_sfr_before_main_content', $args );
			?>

			<div class="jck-sfr-content">
				<?php if ( $jck_sfr_requests->have_posts() ) : ?>
					<?php while ( $jck_sfr_requests->have_posts() ) : $jck_sfr_requests->the_post(); ?>
						<?php
						/**
						 * jck_sfr_loop hook.
						 *
						 * @hooked JCK_SFR_Template_Hooks::loop_content() - 10
						 */
						do_action( 'jck_sfr_loop', $args );
						?>
					<?php endwhile;
					wp_reset_postdata(); ?>
				<?php else: ?>

					<?php
					/**
					 * jck_sfr_no_requests_found hook.
					 *
					 * @hooked JCK_SFR_Template_Hooks::no_requests_found() - 10
					 */
					do_action( 'jck_sfr_no_requests_found', $args );
					?>

				<?php endif; ?>
			</div>

			<?php
			/**
			 * jck_sfr_after_main_content hook.
			 *
			 * @hooked JCK_SFR_Template_Hooks::pagination() - 10
			 */
			do_action( 'jck_sfr_after_main_content', $args );
			?>
            </section>
		</article>

		<?php if ( $sidebar ) { ?>
			<aside class="jck-sfr-container__col col-xl-3">
				<?php
				/**
				 * jck_sfr_sidebar hook.
				 */
				do_action( 'jck_sfr_sidebar', $args );
				?>
			</aside>
		<?php } ?>

        </div>
	</main>

<?php
/**
 * jck_sfr_after_wrapper hook.
 */
do_action( 'jck_sfr_after_wrapper', $args );
?>