<?php
/**
 * Template for the UM Real-time Notifications feed
 * Used to show "Notifications" sidebar and "Notifications" button in the footer
 *
 * Called from the um_notification_show_feed() function
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-notifications/feed.php
 */
if ( ! defined( 'ABSPATH' ) ) exit; ?>

<!-- um-notifications/templates/feed.php -->
<?php
	if ( version_compare( get_bloginfo('version'),'5.4', '<' ) ) {
		echo do_shortcode( '[ultimatemember_notifications_button static="0"]' );
	} else {
		echo apply_shortcodes( '[ultimatemember_notifications_button static="0"]' );
	}
?>

<div class="um-notification-live-feed">
	<div class="um-notification-live-feed-inner">

		<?php 
		$t_args = compact( 'notifications', 'unread', 'unread_count' );
		UM()->get_template( $template, um_notifications_plugin, $t_args, true );
		?>
		
	</div>
</div>