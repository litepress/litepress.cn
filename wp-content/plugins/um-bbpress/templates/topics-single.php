<?php
/**
 * Template for the UM bbPress "Topics Started" subtab items
 * Used on the "Profile" page, "Forums" tab
 * Called from the um_bbpress_load_topics() function
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-bbpress/topics-single.php
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

while ( $loop->have_posts() ) {
	$loop->the_post();
	$topic_id = get_the_ID(); ?>

	<div class="um-item">
		<div class="um-item-link"><?php if ( bbp_get_topic_status( $topic_id ) == 'closed' ) { ?><i class="um-faicon-lock" title="<?php esc_attr_e( 'Locked', 'um-bbpress' ); ?>"></i><?php } ?><a href="<?php echo esc_url( get_the_permalink() ); ?>"><?php bbp_topic_title( $topic_id ); ?></a></div>
		<div class="um-item-meta">
			<span><?php printf( __('in: <a href="%1$s">%2$s</a>', 'um-bbpress' ), bbp_get_forum_permalink( bbp_get_topic_forum_id( $topic_id ) ), bbp_get_forum_title( bbp_get_topic_forum_id( $topic_id ) ) ); ?></span>
			<span><?php _e( 'Voices', 'um-bbpress' );?>: <?php echo bbp_get_topic_voice_count( $topic_id ); ?></span>
			<span><?php _e( 'Replies', 'um-bbpress' );?>: <?php echo bbp_get_topic_reply_count( $topic_id ); ?></span>
			<?php echo ( bbp_get_topic_last_active_time( $topic_id ) ) ? '<span>' . sprintf( __( 'Last active %s', 'um-bbpress' ), bbp_get_topic_last_active_time( $topic_id ) ) . '</span>' : ''; ?>
		</div>
	</div>

<?php }

if ( isset( $modified_args ) && $loop->have_posts() && $loop->found_posts >= 10 ) { ?>

	<div class="um-load-items">
		<a href="javascript:void(0);" class="um-ajax-paginate um-button" data-hook="um_bbpress_load_topics" data-args="<?php echo $modified_args; ?>"><?php _e( 'load more topics', 'um-bbpress' ); ?></a>
	</div>

<?php }