<?php
namespace um_ext\um_bbpress\core;


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


/**
 * Class bbPress_Setup
 * @package um_ext\um_bbpress\core
 */
class bbPress_Setup {


	/**
	 * @var array
	 */
	var $settings_defaults;


	/**
	 * bbPress_Setup constructor.
	 */
	function __construct() {
		//settings defaults
		$this->settings_defaults = array(
			'profile_tab_forums'           => 1,
			'profile_tab_forums_privacy'   => 0,
		);

		$notification_types_templates = array(
			'bbpress_user_reply'    => __( '<strong>{member}</strong> has <strong>replied</strong> to a topic you started on the forum.', 'um-bbpress' ),
			'bbpress_guest_reply'    => __( 'A guest has <strong>replied</strong> to a topic you started on the forum.', 'um-bbpress' ),
		);

		foreach ( $notification_types_templates as $k => $template ) {
			$this->settings_defaults[ 'log_' . $k ] = 1;
			$this->settings_defaults[ 'log_' . $k . '_template' ] = $template;
		}
	}


	/**
	 *
	 */
	function set_default_settings() {
		$options = get_option( 'um_options', array() );

		foreach ( $this->settings_defaults as $key => $value ) {
			//set new options to default
			if ( ! isset( $options[ $key ] ) ) {
				$options[ $key ] = $value;
			}

		}

		update_option( 'um_options', $options );
	}


	function set_restriction_settings() {
		$restricted_access_posts = UM()->options()->get( 'restricted_access_post_metabox' );
		if ( is_array( $restricted_access_posts ) ) {
			$restricted_access_posts = array_merge( $restricted_access_posts, array( 'forum', 'topic' ) );
			UM()->options()->update( 'restricted_access_post_metabox', $restricted_access_posts );
		}
	}


	/**
	 *
	 */
	function run_setup() {
		$this->set_default_settings();
	}
}