<?php
/**
 * The Template for displaying the loop content.
 *
 * @author        James Kemp
 * @version       1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $post;

$feature_request = new JCK_SFR_Feature_Request( $post ); ?>

<?php
/**
 * jck_sfr_before_loop_item hook.
 */
do_action( 'jck_sfr_before_loop_item', $feature_request );
?>

<article <?php $feature_request->wrapper_class(); ?>>
	<?php
	/**
	 * jck_sfr_loop_item_vote_badge hook.
	 *
	 * @hooked JCK_SFR_Template_Methods::loop_item_vote_badge() - 10
	 */
	do_action( 'jck_sfr_loop_item_vote_badge', $feature_request );
	?>

	<div <?php $feature_request->item_class(); ?>>
		<?php
		/**
		 * jck_sfr_loop_item_title hook.
		 *
		 * @hooked JCK_SFR_Template_Methods::loop_item_title() - 10
		 */
		do_action( 'jck_sfr_loop_item_title', $feature_request );
		?>

		<div class="jck-sfr-loop-item__text">
			<?php
			/**
			 * jck_sfr_loop_item_text hook.
			 *
			 * @hooked JCK_SFR_Template_Hooks::loop_item_text() - 10
			 */
			do_action( 'jck_sfr_loop_item_text', $feature_request );
			?>
		</div>

		<?php
		/**
		 * jck_sfr_loop_item_after_text hook.
		 */
		do_action( 'jck_sfr_loop_item_after_text', $feature_request );
		?>

		<div class="jck-sfr-loop-item__meta">
			<?php
			/**
			 * jck_sfr_loop_item_meta hook.
			 *
			 * @hooked JCK_SFR_Template_Methods::loop_item_status_badge() - 10
			 * @hooked JCK_SFR_Template_Methods::loop_item_author() - 20
			 * @hooked JCK_SFR_Template_Methods::loop_item_posted_date() - 30
			 * @hooked JCK_SFR_Template_Methods::loop_item_comment_count() - 40
			 */
			do_action( 'jck_sfr_loop_item_meta', $feature_request );
			?>
		</div>

		<?php
		/**
		 * jck_sfr_loop_item_after_meta hook.
		 *
		 * @hooked JCK_SFR_Template_Methods::comments() - 10
		 */
		do_action( 'jck_sfr_loop_item_after_meta', $feature_request );
		?>
	</div>
</article>

<?php
/**
 * jck_sfr_after_loop_item hook.
 */
do_action( 'jck_sfr_after_loop_item', $feature_request );
?>