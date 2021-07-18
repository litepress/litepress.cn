<?php

/**
 * bbPress BuddyPress Members Class
 *
 * @package bbPress
 * @subpackage BuddyPress
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'BBP_Forums_Members' ) ) :
/**
 * Member profile modifications
 *
 * @since 2.2.0 bbPress (r4395)
 * @since 2.6.0 bbPress (r6320) Add engagements support
 *
 * @package bbPress
 * @subpackage BuddyPress
 */
class BBP_BuddyPress_Members {

	/**
	 * Main constructor for modifying bbPress profile links
	 *
	 * @since 2.2.0 bbPress (r4395)
	 */
	public function __construct() {
		$this->setup_actions();
		$this->setup_filters();
		$this->fully_loaded();
	}

	/**
	 * Setup the actions
	 *
	 * @since 2.2.0 bbPress (r4395)
	 *
	 * @access private
	 */
	private function setup_actions() {

		// Allow unsubscribe/unfavorite links to work
		add_action( 'bp_template_redirect', array( $this, 'set_member_forum_query_vars' ) );

		/** Favorites *********************************************************/

		// Move handler to 'bp_actions' - BuddyPress bypasses template_loader
		remove_action( 'bbp_get_request', 'bbp_favorites_handler', 1 );
		add_action(    'bp_actions',      'bbp_favorites_handler', 1 );

		/** Subscriptions *****************************************************/

		// Move handler to 'bp_actions' - BuddyPress bypasses template_loader
		remove_action( 'bbp_get_request', 'bbp_subscriptions_handler', 1 );
		add_action(    'bp_actions',      'bbp_subscriptions_handler', 1 );
	}

	/**
	 * Setup the filters
	 *
	 * @since 2.2.0 bbPress (r4395)
	 * @since 2.6.0 bbPress (r6320) Add engagements support
	 *
	 * @access private
	 */
	private function setup_filters() {
		add_filter( 'bbp_pre_get_user_profile_url',         array( $this, 'get_user_profile_url'        ) );
		add_filter( 'bbp_pre_get_user_topics_created_url',  array( $this, 'get_topics_created_url'      ) );
		add_filter( 'bbp_pre_get_user_replies_created_url', array( $this, 'get_replies_created_url'     ) );
		add_filter( 'bbp_pre_get_user_engagements_url',     array( $this, 'get_engagements_permalink'   ) );
		add_filter( 'bbp_pre_get_favorites_permalink',      array( $this, 'get_favorites_permalink'     ) );
		add_filter( 'bbp_pre_get_subscriptions_permalink',  array( $this, 'get_subscriptions_permalink' ) );
	}

	/**
	 * Allow the variables, actions, and filters to be modified by third party
	 * plugins and themes.
	 *
	 * @since 2.6.0 bbPress (r6808)
	 */
	private function fully_loaded() {
		do_action_ref_array( 'bbp_buddypress_members_loaded', array( $this ) );
	}

	/** Filters ***************************************************************/

	/**
	 * Override bbPress profile URL with BuddyPress profile URL
	 *
	 * @since 2.0.0 bbPress (r3401)
	 * @since 2.6.0 bbPress (r6320) Add engagements support
	 *
	 * @param int $user_id
	 * @return string
	 */
	public function get_user_profile_url( $user_id = 0 ) {
		return $this->get_profile_url( $user_id );
	}

	/**
	 * Override bbPress topics created URL with BuddyPress profile URL
	 *
	 * @since 2.6.0 bbPress (r3721)
	 * @since 2.6.0 bbPress (r6803) Use private method
	 *
	 * @param int $user_id
	 * @return string
	 */
	public function get_topics_created_url( $user_id = 0 ) {
		return $this->get_profile_url( $user_id, bbp_get_topic_archive_slug() );
	}

	/**
	 * Override bbPress replies created URL with BuddyPress profile URL
	 *
	 * @since 2.6.0 bbPress (r3721)
	 * @since 2.6.0 bbPress (r6803) Use private method
	 *
	 * @param int $user_id
	 * @return string
	 */
	public function get_replies_created_url( $user_id = 0 ) {
		return $this->get_profile_url( $user_id, bbp_get_reply_archive_slug() );
	}

	/**
	 * Override bbPress favorites URL with BuddyPress profile URL
	 *
	 * @since 2.1.0 bbPress (r3721)
	 * @since 2.6.0 bbPress (r6803) Use private method
	 *
	 * @param int $user_id
	 * @return string
	 */
	public function get_favorites_permalink( $user_id = 0 ) {
		return $this->get_profile_url( $user_id, bbp_get_user_favorites_slug() );
	}

	/**
	 * Override bbPress subscriptions URL with BuddyPress profile URL
	 *
	 * @since 2.1.0 bbPress (r3721)
	 * @since 2.6.0 bbPress (r6803) Use private method
	 *
	 * @param int $user_id
	 * @return string
	 */
	public function get_subscriptions_permalink( $user_id = 0 ) {
		return $this->get_profile_url( $user_id, bbp_get_user_subscriptions_slug() );
	}

	/**
	 * Override bbPress engagements URL with BuddyPress profile URL
	 *
	 * @since 2.6.0 bbPress (r6320)
	 *
	 * @param int $user_id
	 * @return string
	 */
	public function get_engagements_permalink( $user_id = 0 ) {
		return $this->get_profile_url( $user_id, bbp_get_user_engagements_slug() );
	}

	/**
	 * Set favorites and subscriptions query variables if viewing member profile
	 * pages.
	 *
	 * @since 2.3.0 bbPress (r4615)
	 * @since 2.6.0 bbPress (r6320) Support all profile sections
	 *
	 * @global WP_Query $wp_query
	 * @return If not viewing your own profile
	 */
	public function set_member_forum_query_vars() {

		// Special handling for forum component
		if ( ! bp_is_my_profile() ) {
			return;
		}

		// Get the main query object
		$wp_query = bbp_get_wp_query();

		// 'topics' action
		if ( bp_is_current_action( bbp_get_topic_archive_slug() ) ) {
			$wp_query->bbp_is_single_user_topics = true;

		// 'replies' action
		} elseif ( bp_is_current_action( bbp_get_reply_archive_slug() ) ) {
			$wp_query->bbp_is_single_user_replies = true;

		// 'favorites' action
		} elseif ( bbp_is_favorites_active() && bp_is_current_action( bbp_get_user_favorites_slug() ) ) {
			$wp_query->bbp_is_single_user_favs = true;

		// 'subscriptions' action
		} elseif ( bbp_is_subscriptions_active() && bp_is_current_action( bbp_get_user_subscriptions_slug() ) ) {
			$wp_query->bbp_is_single_user_subs = true;

		// 'engagements' action
		} elseif ( bbp_is_engagements_active() && bp_is_current_action( bbp_get_user_engagements_slug() ) ) {
			$wp_query->bbp_is_single_user_engagements = true;
		}
	}

	/** Private Methods *******************************************************/

	/**
	 * Private method used to concatenate user IDs and slugs into URLs
	 *
	 * @since 2.6.0 bbPress (r6803)
	 *
	 * @param int    $user_id
	 * @param string $slug
	 *
	 * @return string
	 */
	private function get_profile_url( $user_id = 0, $slug = '' ) {

		// Do not filter if not on BuddyPress root blog
		if ( empty( $user_id ) || ! bp_is_root_blog() ) {
			return false;
		}

		// Setup profile URL
		$url = array( bp_core_get_user_domain( $user_id ) );

		// Maybe push slug to end of URL array
		if ( ! empty( $slug ) ) {
			array_push( $url, bbpress()->extend->buddypress->slug );
			array_push( $url, $slug );
		}

		// Return
		return implode( '', array_map( 'trailingslashit', $url ) );
	}
}
endif;
