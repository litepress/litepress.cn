<?php
/**
 * Template for the UM Real-time Notifications sidebar
 * Used to show "Notifications" sidebar if there is no notifications
 *
 * Called from the um-notifications/templates/feed.php template
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-notifications/no-notifications.php
 */
if ( ! defined( 'ABSPATH' ) ) exit; ?>

<!-- um-notifications/templates/no-notifications.php -->
<div class="um-notification-header">
	<div class="um-notification-left"><?php _e( 'Notifications', 'um-notifications' ); ?></div>
	<div class="um-notification-right">
		<a href="<?php echo esc_url( UM()->account()->tab_link( 'webnotifications' ) ); ?>" class="um-notification-i-settings"><i class="um-faicon-cog"></i></a>
		<a href="javascript:void(0);" class="um-notification-i-close"><i class="um-icon-android-close"></i></a>
	</div>
	<div class="um-clear"></div>
</div>

<div class="um-notification-ajax">

</div>

<div class="um-notifications-none">
	<i class="um-icon-ios-bell"></i>
	<?php _e( 'No new notifications', 'um-notifications' ); ?>
</div>