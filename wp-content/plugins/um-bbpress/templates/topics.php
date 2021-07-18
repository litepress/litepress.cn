<?php
/**
 * Template for the UM bbPress "Topics Started" subtab
 * Used on the "Profile" page, "Forums" tab
 * Called from the um_bbpress_user_topics() function
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-bbpress/topics.php
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( $loop->have_posts() ) {
	$t_args = compact( 'args', 'loop' );
	UM()->get_template( 'topics-single.php', um_bbpress_plugin, $t_args, true ); ?>
	
	<div class="um-ajax-items">
	
		<!--Ajax output-->
		
		<?php if ( $loop->found_posts >= 10 ) { ?>
		
			<div class="um-load-items">
				<a href="javascript:void(0);" class="um-ajax-paginate um-button" data-hook="um_bbpress_load_topics" data-args="topic,10,10,<?php echo esc_attr( um_user( 'ID' ) ); ?>"><?php _e( 'load more topics', 'um-bbpress' ); ?></a>
			</div>
		
		<?php } ?>
		
	</div>
		
<?php } else { ?>

	<div class="um-profile-note">
		<span><?php echo ( um_profile_id() == get_current_user_id() ) ? __( 'You have not created any topics.', 'um-bbpress' ) : __( 'This user has not created any topics.', 'um-bbpress' ); ?></span>
	</div>

<?php }