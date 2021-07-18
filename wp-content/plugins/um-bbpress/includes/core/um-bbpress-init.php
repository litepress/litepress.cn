<?php if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class UM_bbPress_API
 */
class UM_bbPress_API {


	/**
	 * @var
	 */
	private static $instance;


	/**
	 * @return UM_bbPress_API
	 */
	static public function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}


	/**
	 * UM_bbPress_API constructor.
	 */
	function __construct() {
		// Global for backwards compatibility.
		$GLOBALS['um_bbpress'] = $this;

		$this->enqueue();
		add_filter( 'plugins_loaded', array( &$this, 'init' ) );

		add_action( 'um_admin_before_saving_role_meta', array( &$this, 'remove_this_meta' ), 5);

		add_filter( 'query_vars', array( &$this, 'add_query_vars' ) );

		add_filter( 'um_call_object_bbPress_API', array( &$this, 'get_this' ) );

		add_filter( 'um_settings_default_values', array( &$this, 'default_settings' ), 10, 1 );
	}


	/**
	 * @param $defaults
	 *
	 * @return array
	 */
	function default_settings( $defaults ) {
		$defaults = array_merge( $defaults, $this->setup()->settings_defaults );
		return $defaults;
	}


	/**
	 * @return $this
	 */
	function get_this() {
		return $this;
	}


	/**
	 * Init
	 */
	function init() {
		// Actions
		require_once um_bbpress_path . 'includes/core/actions/um-bbpress-content.php';

		require_once um_bbpress_path . 'includes/core/actions/um-bbpress-ajax.php';

		require_once um_bbpress_path . 'includes/core/actions/um-bbpress-redirect.php';
		require_once um_bbpress_path . 'includes/core/actions/um-bbpress-admin.php';
		require_once um_bbpress_path . 'includes/core/actions/um-bbpress-notices.php';
		
		// Filters
		require_once um_bbpress_path . 'includes/core/filters/um-bbpress-notification.php';
		require_once um_bbpress_path . 'includes/core/filters/um-bbpress-settings.php';
		require_once um_bbpress_path . 'includes/core/filters/um-bbpress-tabs.php';
		require_once um_bbpress_path . 'includes/core/filters/um-bbpress-access.php';
		require_once um_bbpress_path . 'includes/core/filters/um-bbpress-permissions.php';
		require_once um_bbpress_path . 'includes/core/filters/um-bbpress-caps.php';
		require_once um_bbpress_path . 'includes/core/filters/um-bbpress-admin.php';
	}


	/**
	 * @return um_ext\um_bbpress\core\bbPress_Setup()
	 */
	function setup() {
		if ( empty( UM()->classes['um_bbpress_setup'] ) ) {
			UM()->classes['um_bbpress_setup'] = new um_ext\um_bbpress\core\bbPress_Setup();
		}
		return UM()->classes['um_bbpress_setup'];
	}


	/**
	 * @return um_ext\um_bbpress\core\bbPress_Enqueue()
	 */
	function enqueue() {
		if ( empty( UM()->classes['um_bbpress_enqueue'] ) ) {
			UM()->classes['um_bbpress_enqueue'] = new um_ext\um_bbpress\core\bbPress_Enqueue();
		}
		return UM()->classes['um_bbpress_enqueue'];
	}


	/**
	 * Get count of subscriptions of user
	 *
	 * @param null $user_id
	 *
	 * @return int
	 */
	function user_subscriptions_count( $user_id = null ) {
		$topic_count = count( bbp_get_user_subscribed_topic_ids( $user_id ) );
		$forum_count = count( bbp_get_user_subscribed_forum_ids( $user_id ) );
		return $forum_count + $topic_count ;
	}


	/**
	 * Delete specific meta conditionally
	 *
	 * @param $post_id
	 */
	function remove_this_meta( $post_id ) {
		delete_post_meta( $post_id, '_um_lock_days' );
	}


	/**
	 * Get week days
	 *
	 * @return mixed
	 */
	function get_weekdays() {
		$array['sun'] = __( 'Sunday', 'um-bbpress' );
		$array['mon'] = __( 'Monday', 'um-bbpress' );
		$array['tue'] = __( 'Tuesday', 'um-bbpress' );
		$array['wed'] = __( 'Wednesday', 'um-bbpress' );
		$array['thu'] = __( 'Thursday', 'um-bbpress' );
		$array['fri'] = __( 'Friday', 'um-bbpress' );
		$array['sat'] = __( 'Saturday', 'um-bbpress' );
		return $array;
	}


	/**
	 * Check if user role allow creating topic
	 *
	 * @return bool
	 */
	function can_do_topic() {
		if ( is_admin() ) {
			return true;
		}

		$lock_days = um_user( 'lock_days' );
		if ( ! empty( $lock_days ) && is_serialized( $lock_days ) ) {
			$lock_days = unserialize( $lock_days ) ;
		}

		$check_day = strtolower( current_time('D') );
		if ( ! empty( $lock_days ) && in_array( $check_day, $lock_days ) )
			return false;

		return true;
	}


	/**
	 * @param $query_vars
	 *
	 * @return array
	 */
	function add_query_vars( $query_vars ) {
		$query_vars[] = 'bbp-subscription';
		return $query_vars;
	}
}

//create class var
add_action( 'plugins_loaded', 'um_init_bbpress', -10, 1 );
function um_init_bbpress() {
	if ( function_exists( 'UM' ) ) {
		UM()->set_class( 'bbPress_API', true );
	}
}