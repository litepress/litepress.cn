<?php if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * @param $data
 * @param $ref
 * @param $prefs
 *
 * @return mixed
 */
function um_notification_mycred_apply_notification_tpl( $data, $ref, $prefs ) {
	if ( empty( $data ) && ( ! empty( $prefs[ $ref ]['notification_tpl'] ) || ! empty( $prefs[ $ref ]['deduct_notification_tpl'] ) ) ) {
		$data = array();
	}

	if ( ! empty( $prefs[ $ref ]['notification_tpl'] ) ) {
		$data['notification_tpl'] = $prefs[ $ref ]['notification_tpl'];
	} elseif ( ! empty( $prefs[ $ref ]['deduct_notification_tpl'] ) ) {
		$data['notification_tpl'] = $prefs[ $ref ]['deduct_notification_tpl'];
	}

	return $data;
}
add_filter( 'um_mycred_hooks_data', 'um_notification_mycred_apply_notification_tpl', 10, 3 );


/**
 * Log core myCRED actions
 *
 * @param $array
 * @param $mycred
 *
 * @return mixed
 */
function um_notification_mycred_default_log( $array, $mycred ) {
	if ( um_user( 'ID' ) ) {
		$global_user = um_user( 'ID' );
	}

	$user_id = $array['user_id'];

	$vars['mycred_object'] = $mycred;
	$vars['mycred_run_array'] = $array;
	$vars['photo'] = um_get_avatar_url( get_avatar( $user_id, 40 ) );

	if ( $array['amount'] > 0 ) {
		$vars['mycred_points'] = ( $array['amount'] == 1 ) ? sprintf( __( '%s %%_singular%%', 'um-notifications' ), $array['amount'] ) : sprintf( __( '%s %%_plural%%', 'um-notifications' ), $array['amount'] );
	} else {
		$vars['mycred_points'] = ( $array['amount'] == -1 ) ? sprintf( __( '%s %%_singular%%', 'um-notifications' ), absint( $array['amount'] ) ) : sprintf( __( '%s %%_plural%%', 'um-notifications' ), absint( $array['amount'] ) );
	}

	$vars['mycred_points'] = $mycred->template_tags_general( $vars['mycred_points'] );

	$vars['mycred_task'] = preg_replace( "/%[^%]*%/", "", $array['entry'] );

	if ( $array['amount'] > 0 ) {
		UM()->Notifications_API()->api()->store_notification( $user_id, 'mycred_award', $vars );
	} else {
		UM()->Notifications_API()->api()->store_notification( $user_id, 'mycred_deduct', $vars );
	}

	um_reset_user();

	if ( isset( $global_user ) ) {
		um_fetch_user( $global_user );
	}

	return $array;
}
add_filter( 'mycred_run_this', 'um_notification_mycred_default_log', 100, 2 );


/**
 * Log UM balance transfer
 *
 * @param $to
 * @param $amount
 * @param $from
 */
function um_notification_log_mycred_points_sent( $to, $amount, $from ) {
	remove_filter('mycred_run_this', 'um_notification_mycred_default_log', 100, 2);

	$vars = array();
	$vars['photo'] = um_get_avatar_url( get_avatar( $to, 40 ) );
	$vars['mycred_points'] = sprintf( __('%s points','um-notifications'), $amount );

	$sender = get_userdata( $from );
	$vars['mycred_sender'] = $sender->display_name;

	UM()->Notifications_API()->api()->store_notification( $to, 'mycred_points_sent', $vars );
}
add_action( 'um_mycred_credit_balance_transfer', 'um_notification_log_mycred_points_sent', 10, 3 );


/**
 * @param $hook
 * @param $k
 * @param $prefs
 * @param $class myCRED_Hook
 */
function um_mycred_notification_template( $hook, $k, $prefs, $class ) { ?>
	<div class="row">
		<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
			<div class="form-group">
				<label for="<?php echo esc_attr( $class->field_id( array( $hook, 'notification_tpl' ) ) ); ?>">
					<?php _e( 'Notification template', 'um-notifications' ); ?>
				</label>
				<input type="text" name="<?php echo esc_attr( $class->field_name( array( $hook, 'notification_tpl' ) ) ); ?>"
				       id="<?php echo esc_attr( $class->field_id( array( $hook, 'notification_tpl' ) ) ); ?>"
				       value="<?php echo sanitize_text_field( stripslashes( $prefs[ $hook ]['notification_tpl'] ) ); ?>" class="form-control" />
				<span class="description"><?php echo $class->core->available_template_tags( array( 'general' ) ); ?></span>
			</div>
		</div>
	</div>
<?php }
add_action( 'um_mycred_hooks_option_extended', 'um_mycred_notification_template', 10, 4 );


/**
 * @param array $new_data
 * @param $callback
 *
 * @return mixed
 */
function um_mycred_notification_add_notification_to_sanitize( $new_data, $callback ) {
	foreach ( $callback->um_hooks as $hook => $k ) {
		$new_data[ $hook ]['notification_tpl'] = ! empty( $data[ $hook ]['notification_tpl'] ) ? sanitize_text_field( $data[ $hook ]['notification_tpl'] ) : $callback->defaults[ $hook ]['notification_tpl'];
	}

	return $new_data;
}
add_filter( 'um_mycred_sanitise_pref', 'um_mycred_notification_add_notification_to_sanitize', 10, 2 );


/**
 * @param $defaults
 * @param $hook
 * @param $hook_data
 * @param $um_hooks
 *
 * @return mixed
 */
function um_mycred_notification_notification_tpl_default( $defaults, $hook, $hook_data, $um_hooks ) {
	switch ( $hook ) {
		default:
			$defaults['notification_tpl'] = apply_filters( 'um_mycred_notification_tpl_default', '', $hook, $hook_data, $um_hooks );
			break;
		case 'update_account':
		case 'um_user_login':
		case 'member_search':
		case 'signup':
			$defaults['notification_tpl'] = sprintf( __( 'You\'ve gained <strong>%%cred_f%% %%_plural%%</strong> for <strong>%s</strong>.', 'um-notifications' ), $hook_data['action'] );
			break;
		case 'profile_photo':
		case 'update_profile':
		case 'cover_photo':
			$defaults['notification_tpl'] = sprintf( __( 'You\'ve gained <strong>%%cred_f%% %%_plural%%</strong> for <strong>%s</strong>.', 'um-notifications' ), $hook_data['action'] );
			break;
		case 'remove_profile_photo':
		case 'remove_cover_photo':
			$defaults['notification_tpl'] = sprintf( __( 'You\'ve taken away <strong>%%cred_f%% %%_plural%%</strong> for <strong>%s</strong>.', 'um-notifications' ), $hook_data['action'] );
			break;
	}

	return $defaults;
}
add_filter( 'um_mycred_hook_defaults', 'um_mycred_notification_notification_tpl_default', 10, 4 );


/**
 * @param $networks
 *
 * @return mixed
 */
function um_mycred_social_login_notification_template( $networks ) {
	if ( empty( $networks ) ) {
		return $networks;
	}

	foreach ( $networks as $provider => &$data ) {
		$data['notification_tpl'] = sprintf( __( 'You\'ve gained <strong>%%cred_f%% %%_plural%%</strong> for connecting %s account</strong>.', 'um-notifications' ), $data['name'] );
		$data['deduct_notification_tpl'] = sprintf( __( 'You\'ve taken away <strong>%%cred_f%% %%_plural%%</strong> for disconnecting %s account</strong>.', 'um-notifications' ), $data['name'] );
	}

	return $networks;
}
add_filter( 'um_social_login_networks', 'um_mycred_social_login_notification_template', 10, 1 );