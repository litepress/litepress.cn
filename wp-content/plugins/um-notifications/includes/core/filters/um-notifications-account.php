<?php if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Add tab to account page
 *
 * @param $tabs
 *
 * @return mixed
 */
function um_notification_account_tab( $tabs ) {

	$tabs[445]['webnotifications']['icon'] = 'um-faicon-bell';
	$tabs[445]['webnotifications']['title'] = __('Web notifications','um-notifications');
	$tabs[445]['webnotifications']['submit_title'] = __('Update Settings','um-notifications');

	return $tabs;
}
add_filter( 'um_account_page_default_tabs_hook', 'um_notification_account_tab', 100 );


/**
 * Add content to account tab
 *
 * @param $output
 *
 * @return string
 */
function um_account_content_hook_webnotifications( $output ) {
	$user_id = get_current_user_id();
	$logs = UM()->Notifications_API()->api()->get_log_types();

	$t_args = compact( 'logs', 'user_id' );
	$output .= UM()->get_template( 'account_webnotifications.php', um_notifications_plugin, $t_args );

	wp_reset_postdata();

	return $output;
}
add_filter( 'um_account_content_hook_webnotifications', 'um_account_content_hook_webnotifications' );