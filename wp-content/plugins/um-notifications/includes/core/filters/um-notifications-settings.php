<?php if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Extend settings
 *
 * @param array $settings
 *
 * @return array
 */
function um_notifications_settings( $settings ) {
	$settings['licenses']['fields'][] = array(
		'id'        => 'um_notifications_license_key',
		'label'     => __( 'Real-time Notifications License Key', 'um-notifications' ),
		'item_name' => 'Real-time Notifications',
		'author'    => 'Ultimate Member',
		'version'   => um_notifications_version,
	);

	$key = ! empty( $settings['extensions']['sections'] ) ? 'notifications' : '';
	$settings['extensions']['sections'][$key] = array(
		'title'     => __( 'Notifications', 'um-notifications' ),
		'fields'    => array(
			array(
				'id'        => 'realtime_notify',
				'type'      => 'checkbox',
				'label'     => __( 'Enable real-time instant notification', 'um-notifications' ),
				'tooltip'   => __( 'Turn off please If your server is getting some load.', 'um-notifications' ),
			),
			array(
				'id'            => 'realtime_notify_timer',
				'type'          => 'text',
				'label'         => __( 'How often do you want the ajax notifier to check for new notifications? (in seconds)', 'um-notifications'),
				'validate'      => 'numeric',
				'conditional'   => array( 'realtime_notify', '=', 1 ),
				'size'          => 'small',
			),
			array(
				'id'            => 'notify_pos',
				'type'          => 'select',
				'label'         => __( 'Where should the notification icon appear?', 'um-notifications' ),
				'options'       => array(
					'right' => __( 'Right bottom', 'um-notifications' ),
					'left'  => __( 'Left bottom', 'um-notifications' )
				),
				'placeholder'   => __( 'Select...', 'um-notifications' ),
				'size'          => 'small',
			),
			array(
				'id'        => 'notification_icon_visibility',
				'type'      => 'checkbox',
				'label'     => __( 'Always display the notification icon', 'um-notifications' ),
				'tooltip'   => __( 'If turned off, the icon will only show when there\'s a new notification.', 'um-notifications' ),
			),
			array(
				'id'        => 'notification_sound',
				'type'      => 'checkbox',
				'label'     => __( 'Notification sound', 'um-notifications' ),
				'tooltip'   => __( 'Play sound when new notification appear. It may not work in Chrome due to Autoplay Policy.', 'um-notifications' ),
				'conditional'   => array( 'realtime_notify', '=', 1 ),
			),
			array(
				'id'        => 'account_tab_webnotifications',
				'type'      => 'checkbox',
				'label'     => __( 'Account Tab', 'um-notifications' ),
				'tooltip'   => __( 'Show or hide an account tab that shows the web notifications.', 'um-notifications' ),
			)
		)
	);

	$lang_prefix = '';
	if ( UM()->external_integrations()->is_wpml_active() ) {
		global $sitepress;

		$current_lang = $sitepress->get_current_language();
		if ( $current_lang != $sitepress->get_default_language() ) {
			$lang_prefix = '_' . $current_lang;
		}
	}

	foreach ( UM()->Notifications_API()->api()->get_log_types() as $k => $desc ) {

		$settings['extensions']['sections'][ $key ]['fields'] = array_merge( $settings['extensions']['sections'][ $key ]['fields'], array(
			array(
				'id'    => 'log_' . $k,
				'type'  => 'checkbox',
				'label' => $desc['title'],
			),
			array(
				'id'            => 'log_' . $k . $lang_prefix . '_template',
				'type'          => 'textarea',
				'label'         => __( 'Template', 'um-notifications' ),
				'conditional'   => array( 'log_' . $k, '=', 1 ),
				'rows'          => 2,
			)
		) );
	}

	return $settings;
}
add_filter( 'um_settings_structure', 'um_notifications_settings', 10, 1 );


/**
 * @param string $content
 * @param string $type
 * @param array $vars
 *
 * @return string
 */
function um_wpml_notifications_change_notification_content( $content, $type, $vars ) {
	if ( UM()->external_integrations()->is_wpml_active() ) {
		global $sitepress;
		$current_lang = $sitepress->get_current_language();
		$default_lang = $sitepress->get_default_language();

		$lang_prefix = '';
		if ( $current_lang != $default_lang ) {
			$lang_prefix = '_' . $current_lang;
		}

		$translated_content = UM()->options()->get( 'log_' . $type . $lang_prefix . '_template' );

		if ( ! empty( $translated_content ) ) {
			$content = $translated_content;
		}
	}

	return $content;
}
add_filter( 'um_notification_modify_entry', 'um_wpml_notifications_change_notification_content', 10, 3 );


/**
 * @param $results
 * @param $per_page
 * @param $unread_only
 * @param $count
 *
 * @return mixed
 */
function um_get_notification_content_wpml( $results, $per_page, $unread_only, $count ) {
	if ( UM()->external_integrations()->is_wpml_active() ) {
		global $sitepress;

		$current_lang = $sitepress->get_current_language();
		$default_lang = $sitepress->get_default_language();

		foreach ( $results as &$notification ) {
			$notification->content = maybe_unserialize( $notification->content );

			if ( is_array( $notification->content ) ) {
				if ( ! empty( $notification->content[ $current_lang ] ) ) {
					$notification->content = $notification->content[ $current_lang ];
				} elseif ( ! empty( $notification->content[ $default_lang ] ) ) {
					$notification->content = $notification->content[ $default_lang ];
				} else {
					$notification->content = $notification->content[ '' ];
				}
			}
		}
	} else {

		foreach ( $results as &$notification ) {
			$notification->content = maybe_unserialize( $notification->content );
			if ( is_array( $notification->content ) ) {
				$notification->content = $notification->content[''];
			}
		}

	}

	return $results;
}
add_filter( 'um_notifications_get_notifications_response', 'um_get_notification_content_wpml', 10, 4 );