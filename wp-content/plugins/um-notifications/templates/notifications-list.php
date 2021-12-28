<?php
/**
 * Template for the UM Real-time Notifications List
 * Used in "Notifications" sidebar and in [ultimatemember_notifications] shortcode
 * 
 * Called from the um-notifications/templates/notifications.php template
 * Called from the Notifications_Main_API->ajax_check_update() method
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-notifications/notifications-list.php
 */
if ( ! defined( 'ABSPATH' ) ) exit;


foreach ( $notifications as $notification ) {
	if ( ! isset( $notification->id ) ) {
		continue;
	} ?>

	<div class="um-notification <?php echo esc_attr( $notification->status ); ?>" data-notification_id="<?php echo esc_attr( $notification->id ); ?>" data-notification_uri="<?php echo esc_url( $notification->url ); ?>">

		<img src="<?php echo esc_url( um_secure_media_uri( $notification->photo ) ); ?>" data-default="<?php echo esc_url( um_secure_media_uri( um_get_default_avatar_uri() ) ); ?>" alt="" class="um-notification-photo" />

		<?php echo stripslashes( $notification->content ); ?>

		<span class="b2" data-time-raw="<?php echo $notification->time; ?>">
			<?php echo UM()->Notifications_API()->api()->get_icon( $notification->type );
			echo UM()->Notifications_API()->api()->nice_time( $notification->time ); ?>
		</span>
		<span class="um-notification-hide"><a href="javascript:void(0);"><i class="um-icon-android-close"></i></a></span>

	</div>

	<?php
}
