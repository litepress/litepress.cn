<?php
namespace um_ext\um_notifications\core;


if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class Notifications_Shortcode
 * @package um_ext\um_notifications\core
 */
class Notifications_Shortcode {


	/**
	 * Notifications_Shortcode constructor.
	 */
	function __construct() {
		add_shortcode( 'ultimatemember_notifications', array( &$this, 'ultimatemember_notifications' ) );
		add_shortcode( 'ultimatemember_notifications_button', array( &$this, 'ultimatemember_notifications_button' ) );
		add_shortcode( 'ultimatemember_notification_count', array( &$this, 'ultimatemember_notification_count' ) );
		
		add_filter( 'wp_title', array( &$this, 'wp_title' ), 10, 2 );
		add_filter( 'wp_nav_menu_items', array( &$this, 'menu_patterns'), 10, 2 );
	}


	/**
	 * Replace patterns in nav menu
	 *
	 * @param string $items
	 * @param array $args
	 * 
	 * @return string
	 */
	function menu_patterns( $items, $args ) {

		$pattern_array = array(
				'{um_notifications_button}',
		);

		foreach( $pattern_array as $pattern ) {
			if( !preg_match( $pattern, $items ) ) {
				continue;
			}

			$value = '';

			if( $pattern == '{um_notifications_button}' && is_user_logged_in() ) {
				$value = $this->ultimatemember_notifications_button( array(
						'static' => '1'
				) );
			}

			if( $value ) {
				$value = preg_replace( array( '/^\s+/im', '/\\r\\n/im', '/\\n/im', '/\\t/im' ), '', $value );
			}
			$items = preg_replace( '/' . $pattern . '/', $value, $items );
		}

		return $items;
	}
	

	/**
	 * Custom title for page
	 *
	 * @param $title
	 * @param null $sep
	 *
	 * @return string
	 */
	function wp_title( $title, $sep=null ) {
		global $post;
		if ( isset( $post->ID ) && $post->ID == UM()->permalinks()->core['notifications'] ) {
			$unread = UM()->Notifications_API()->api()->get_notifications( 0, 'unread', true );
			if ( $unread ){
				$title = "($unread) $title";
			}
		}
		return $title;
	}


	/**
	 * Notifications list shortcode
	 *
	 * @param array $args
	 *
	 * @return string
	 */
	function ultimatemember_notifications( $args = array() ) {
		if ( ! is_user_logged_in() ) {
			exit( wp_redirect( home_url() ) );
		}

		wp_enqueue_script( 'um_notifications' );
		wp_enqueue_style( 'um_notifications' );

		$notifications = UM()->Notifications_API()->api()->get_notifications( 50 );
		$template = $notifications ? 'notifications.php' : 'no-notifications.php';

		$unread = (int)UM()->Notifications_API()->api()->get_notifications( 0, 'unread', true );
		$unread_count = ( absint( $unread ) > 9 ) ? '+9' : $unread;

		$t_args = array_merge( (array) $args, compact( 'notifications', 'unread', 'unread_count' ) );
		$content = UM()->get_template( $template, um_notifications_plugin, $t_args );

		$output = '<div class="um-notification-shortcode">' . $content . '</div>';
		
		return $output;
	}


	/**
	 * Shortcode "Notifications button"
	 *
	 * @param array $args
	 * @return string
	 */
	function ultimatemember_notifications_button( $args = array() ) {
		$args = wp_parse_args( $args, array(
			'static'            => 1,
			'hide_if_no_unread' => 0
		) );

		if ( ! is_user_logged_in() ) {
			return '';
		}

		$unread = ( int ) UM()->Notifications_API()->api()->get_notifications( 0, 'unread', true );
		$unread_count = ( absint( $unread ) > 9 ) ? '+9' : $unread;

		if ( ! $unread && $args[ 'hide_if_no_unread' ] ) {
			return '';
		}

		$t_args = array_merge( (array) $args, compact( 'unread', 'unread_count' ) );
		$output = UM()->get_template( 'notifications_button.php', um_notifications_plugin, $t_args );

		wp_enqueue_script( 'um_notifications' );
		wp_enqueue_style( 'um_notifications' );

		return $output;
	}
	

	/**
	 * Shortcode
	 *
	 * @param array $args
	 *
	 * @return int
	 */
	function ultimatemember_notification_count( $args = array() ) {
		wp_enqueue_script( 'um_notifications' );
		wp_enqueue_style( 'um_notifications' );

		$count = UM()->Notifications_API()->api()->unread_count( get_current_user_id() );
		return (int) $count;
	}

}