<?php
namespace um_ext\um_notifications\core;


if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class Notifications_Main_API
 * @package um_ext\um_notifications\core
 */
class Notifications_Main_API {


	/**
	 * Did user enable this web notification?
	 *
	 * @param $key
	 * @param $user_id
	 *
	 * @return bool
	 */
	function user_enabled( $key, $user_id ) {
		if ( ! UM()->options()->get( 'log_' . $key ) ) {
			return false;
		}
		$prefs = get_user_meta( $user_id, '_notifications_prefs', true );
		if ( isset( $prefs[ $key ] ) && ! $prefs[ $key ] ) {
			return false;
		}

		// if all checkboxes were not selected
		if ( $prefs === array('') ) {
			return false;
		}

		return true;
	}


	/**
	 * Register notification types
	 *
	 * @return array
	 */
	function get_log_types() {

		$logs = array(
			'upgrade_role'  => array(
				'title'         => __( 'Role upgrade', 'um-notifications' ),
				'account_desc'  => __( 'When my membership level is changed', 'um-notifications' ),
			),
			'comment_reply'  => array(
				'title'         => __( 'New comment reply', 'um-notifications' ),
				'account_desc'  => __( 'When a member replies to one of my comments', 'um-notifications' ),
			),
			'user_comment'  => array(
				'title'         => __( 'New user comment', 'um-notifications' ),
				'account_desc'  => __( 'When a member comments on my posts', 'um-notifications' ),
			),
			'guest_comment'  => array(
				'title'         => __( 'New guest comment', 'um-notifications' ),
				'account_desc'  => __( 'When a guest comments on my posts', 'um-notifications' ),
			),
			'profile_view'  => array(
				'title'         => __( 'User view profile', 'um-notifications' ),
				'account_desc'  => __( 'When a member views my profile', 'um-notifications' ),
			),
			'profile_view_guest'  => array(
				'title'         => __( 'Guest view profile', 'um-notifications' ),
				'account_desc'  => __( 'When a guest views my profile', 'um-notifications' ),
			),
		);

		$logs = apply_filters( 'um_notifications_core_log_types', $logs );

		return $logs;
	}


	/**
	 * Get unread count by user ID
	 *
	 * @param int $user_id
	 * @return int
	 */
	function unread_count( $user_id = 0 ) {
		global $wpdb;

		$user_id = ( $user_id > 0 ) ? $user_id : get_current_user_id();

		$table_name = "wp_um_notifications";
		$results = $wpdb->get_results( $wpdb->prepare(
		"SELECT id
			FROM {$table_name}
			WHERE user = %d AND
			      status='unread'",
			$user_id
		) );

		if ( $wpdb->num_rows == 0 ) {
			return 0;
		} else {
			return $wpdb->num_rows;
		}
	}


	/**
	 * Deletes a notification by its ID
	 *
	 * @param $notification_id
	 */
	function delete_log( $notification_id ) {
		global $wpdb;
		if ( ! is_user_logged_in() ) {
			return;
		}
		$table_name = "wp_um_notifications";
		$wpdb->delete( $table_name, array( 'id' => $notification_id ) );
	}


	/**
	 * Gets icon for notification
	 *
	 * @param $type
	 *
	 * @return null|string
	 */
	function get_icon( $type ) {
		$output = null;
		switch( $type ) {

			default:
				$output = apply_filters( 'um_notifications_get_icon', $output, $type );
				break;

			case 'comment_reply':
				$output = '<i class="um-icon-chatboxes" style="color: #00b56c"></i>';
				break;
			case 'user_comment':
			case 'guest_comment':
				$output = '<i class="um-faicon-comment" style="color: #DB6CD2"></i>';
				break;

			case 'user_review':
				$output = '<i class="um-faicon-star" style="color: #FFD700"></i>';
				break;

			case 'profile_view':
			case 'profile_view_guest':
				$output = '<i class="um-faicon-eye" style="color: #6CB9DB"></i>';
				break;

			case 'bbpress_user_reply':
			case 'bbpress_guest_reply':
				$output = '<i class="um-faicon-comments" style="color: #67E264"></i>';
				break;

			case 'upgrade_role':
				$output = '<i class="um-faicon-exchange" style="color: #999"></i>';
				break;

		}

		return $output;
	}


	/**
	 * Gets time in user-friendly way
	 *
	 * @param $time
	 *
	 * @return string
	 */
	function nice_time( $time ) {

		$from_time_unix = strtotime( $time );
		$offset = get_option( 'gmt_offset' );
		$offset = apply_filters("um_notifications_time_offset", $offset );

		$from_time = $from_time_unix - $offset * HOUR_IN_SECONDS;
		$from_time = apply_filters("um_notifications_time_from", $from_time, $time );

		$current_time = current_time('timestamp') - $offset * HOUR_IN_SECONDS;
		$current_time = apply_filters("um_notifications_current_time", $current_time );

		$nice_time = human_time_diff( $from_time, $current_time  );
		$nice_time = apply_filters("um_notifications_time_nice", $nice_time, $from_time, $current_time );

		$time = sprintf(__('%s ago','um-notifications'), $nice_time );

		return $time;
	}


	/**
	 * Gets notifications
	 *
	 * @param int $per_page
	 * @param bool $unread_only
	 * @param bool $count
	 * @return array|bool|int|null|object
	 */
	function get_notifications( $per_page = 10, $unread_only = false, $count = false ) {
		global $wpdb;
		$user_id = get_current_user_id();
		$table_name = "wp_um_notifications";

		if ( $unread_only == 'unread' && $count == true ) {

			$results = $wpdb->get_results( $wpdb->prepare(
				"SELECT *
				FROM {$table_name}
				WHERE user = %d AND
					  status = 'unread'",
				$user_id
			) );

			return $wpdb->num_rows;

		} elseif ( $unread_only == 'unread' ) {

			$results = $wpdb->get_results( $wpdb->prepare(
				"SELECT *
				FROM {$table_name}
				WHERE user = %d AND
					  status = 'unread'
				ORDER BY time DESC
				LIMIT %d",
				$user_id,
				$per_page
			) );

		} else {

			$results = $wpdb->get_results( $wpdb->prepare(
				"SELECT *
				FROM {$table_name}
				WHERE user = %d
				ORDER BY time DESC
				LIMIT %d",
				$user_id,
				$per_page
			) );

		}

		if ( ! empty( $results ) ) {
			return apply_filters( 'um_notifications_get_notifications_response', $results, $per_page, $unread_only, $count );
		}

		return false;
	}


	function replace_content_placeholders( $content, $vars ) {
		if ( $vars ) {
			foreach ( $vars as $key => $var ) {
				if ( $key == 'mycred_object' || $key == 'mycred_run_array' ) {
					continue;
				}
				$content = str_replace( '{' . $key . '}', $var, $content );
			}
		}

		$content = implode( ' ', array_unique( explode( ' ', $content ) ) );
	}


	/**
	 * Saves a notification
	 *
	 * @param $user_id
	 * @param $type
	 * @param array $vars
	 */
	function store_notification( $user_id, $type, $vars = array() ) {
		global $wpdb;

		// Check if user opted-in
		if ( ! $this->user_enabled( $type, $user_id ) ) {
			return;
		}

		if ( UM()->external_integrations()->is_wpml_active() ) {
			$content = $this->wpml_store_notification( $type, $vars );
		} else {
			$content = $this->get_notify_content( $type, $vars );
		}

		if ( $vars && isset( $vars['photo'] ) ) {
			$photo = $vars['photo'];
		} else {
			$photo = um_get_default_avatar_uri();
		}

		$url = '';
		if ( $vars && isset( $vars['notification_uri'] ) ) {
			$url = $vars['notification_uri'];
		}

		$table_name = "wp_um_notifications";

		$exclude_type = apply_filters( 'um_notifications_exclude_types', array(
			'comment_reply',
			'new_wall_post',
			'new_wall_comment',
			'bbpress_user_reply',
			'bbpress_guest_reply'
		) );

		if ( ! in_array( $type, $exclude_type ) && ! empty( $content ) ) {
			// Try to update a similar log
			$result = $wpdb->get_var( $wpdb->prepare(
				"SELECT id
				FROM {$table_name}
				WHERE user = %d AND
					  type = %s AND
					  content = %s
				ORDER BY time DESC",
				$user_id,
				$type,
				$content
			) );

			if ( ! empty( $result ) ) {
				$wpdb->update(
					$table_name,
					array(
						'status'    => 'unread',
						'time'      => current_time( 'mysql' ),
						'url'       => $url,
						'photo'       => $photo,
					),
					array(
						'user'      => $user_id,
						'type'      => $type,
						'content'   => $content
					)
				);
				$do_not_insert = true;
			}
		}

		if ( isset( $do_not_insert ) ) {
			return;
		}

		if ( ! empty( $content ) ) {
			$wpdb->insert(
				$table_name,
				array(
					'time'      => current_time( 'mysql' ),
					'user'      => $user_id,
					'status'    => 'unread',
					'photo'     => $photo,
					'type'      => $type,
					'url'       => $url,
					'content'   => $content
				)
			);
		}
	}


	/**
	 * Saves a notification when WPML is active
	 *
	 * @param string $type
	 * @param array $vars
	 *
	 * @return string
	 */
	function wpml_store_notification( $type, $vars ) {
		global $sitepress;

		$content = array(
			''  => UM()->options()->get( 'log_' . $type . '_template' ),
		);

		$active_languages = $sitepress->get_active_languages();

		if ( ! empty( $active_languages ) ) {
			$current_lang = $sitepress->get_current_language();

			foreach ( array_keys( $active_languages ) as $language ) {
				$sitepress->switch_lang( $language );
				$content[ $language ] = $this->get_notify_content( $type, $vars );
			}

			$sitepress->switch_lang( $current_lang );
		}

		return serialize( $content );
	}


	/**
	 * Get notification content
	 *
	 * @param $type
	 * @param array $vars
	 *
	 * @return string|null
	 */
	function get_notify_content( $type, $vars = array() ) {
		$content = UM()->options()->get( 'log_' . $type . '_template' );
		$content = apply_filters( 'um_notification_modify_entry', $content, $type, $vars );
		$content = apply_filters( "um_notification_modify_entry_{$type}", $content, $vars );

		if ( $vars ) {
			foreach ( $vars as $key => $var ) {
				if ( $key == 'mycred_object' || $key == 'mycred_run_array' ) {
					continue;
				}
				$content = str_replace( '{' . $key . '}', $var, $content );
			}
		}

		// This code breaks the content. It removes words that are used multiple times.
		//$content = implode( ' ', array_unique( explode( ' ', $content ) ) );

		$content = apply_filters( 'um_notification_modify_entry_with_placeholders', $content, $type, $vars );
		$content = apply_filters( "um_notification_modify_entry_{$type}_with_placeholders", $content, $vars );
		return $content;
	}


	/**
	 * Mark as read
	 *
	 * @param $notification_id
	 */
	function set_as_read( $notification_id ) {
		global $wpdb;
		$user_id = get_current_user_id();
		$table_name = "wp_um_notifications";
		$wpdb->update(
			$table_name,
			array(
				'status'    => 'read',
			),
			array(
				'user'  => $user_id,
				'id'    => $notification_id
			)
		);
	}


	/**
	 * Checks if notification is unread
	 *
	 * @param $notification_id
	 *
	 * @return bool
	 */
	function is_unread( $notification_id ) {
		$user_id = get_current_user_id();
		$saved_id = get_post_meta( $notification_id, '_belongs_to', true );
		if ( $saved_id == $user_id ) {
			$is_unread = get_post_meta( $notification_id, 'status', true );
			if ( $is_unread == 'unread' ) {
				return true;
			}
		}
		return false;
	}


	/**
	 *
	 */
	function ajax_delete_log() {
		UM()->check_ajax_nonce();

		if ( ! isset( $_POST['notification_id'] ) || ! is_user_logged_in() ) {
			wp_send_json_error();
		}

		$this->delete_log( absint( $_POST['notification_id'] ) );

		wp_send_json_success();
	}


	/**
	 * Mark a notification as read
	 */
	function ajax_mark_as_read() {
		UM()->check_ajax_nonce();

		if ( ! isset( $_POST['notification_id'] ) || ! is_user_logged_in() ) {
			wp_send_json_error();
		}

		$this->set_as_read( absint( $_POST['notification_id'] ) );

		wp_send_json_success();
	}


	/**
	 * Checks for update
	 */
	function ajax_check_update() {
		UM()->check_ajax_nonce();

		extract( $_POST );

		$refresh_count = 0;
		$unread_html = '';
		$unread = $this->get_notifications( 0, 'unread', true );

		if ( $unread ) {

			$refresh_count = ( absint( $unread ) > 9 ) ? '+9' : absint( $unread );
			$notifications = $this->get_notifications( 1, 'unread' );

			if ( $notifications ) {
				$t_args = compact( 'notifications' );
				$unread_html = UM()->get_template( 'notifications-list.php', um_notifications_plugin, $t_args );
			}
		}

		$output = array(
			'refresh_count' => $refresh_count,
			'unread_count'  => absint( $unread ),
			'unread'        => preg_replace(
					array( '/^\s+/im', '/\\r\\n/im', '/\\n/im', '/\\t+/im' ),
					array( '', ' ', ' ', ' ' ), $unread_html ),
		);

		wp_send_json_success( apply_filters( 'um_notifications_ajax_check_update', $output ) );
	}
}
