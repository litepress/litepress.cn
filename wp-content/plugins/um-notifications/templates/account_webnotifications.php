<?php
/**
 * Template for the UM Real-time Notifications "Web notifications" settings
 * Used on "Account" page, "Web notifications" tab
 *
 * Called from the um_account_content_hook_webnotifications() function
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-notifications/account_webnotifications.php
 */
if ( ! defined( 'ABSPATH' ) ) exit; ?>

<!-- um-notifications/templates/account_webnotifications.php -->
<div class="um-field" data-key="">
	<div class="um-field-label"><strong><?php _e( 'Receiving Notifications', 'um-notifications' ); ?></strong></div>
	<div class="um-field-area">

		<?php foreach ( $logs as $key => $array ) {

			if ( ! UM()->options()->get( 'log_' . $key ) ) {
				continue;
			}

			$enabled = UM()->Notifications_API()->api()->user_enabled( $key, $user_id );

			if ( $enabled ) { ?>

				<label class="um-field-checkbox active">
					<input type="checkbox" name="um-notifyme[<?php echo esc_attr( $key ); ?>]" value="1" checked />
					<span class="um-field-checkbox-state"><i class="um-icon-android-checkbox-outline"></i></span>
					<span class="um-field-checkbox-option"><?php echo $array['account_desc']; ?></span>
				</label>

			<?php } else { ?>

				<label class="um-field-checkbox">
					<input type="checkbox" name="um-notifyme[<?php echo esc_attr( $key ); ?>]" value="1" />
					<span class="um-field-checkbox-state"><i class="um-icon-android-checkbox-outline-blank"></i></span>
					<span class="um-field-checkbox-option"><?php echo $array['account_desc']; ?></span>
				</label>

			<?php }
		} ?>

		<div class="um-clear"></div>
	</div>
</div>