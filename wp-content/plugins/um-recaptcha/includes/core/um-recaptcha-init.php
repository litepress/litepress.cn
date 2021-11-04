<?php if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class UM_reCAPTCHA
 */
class UM_reCAPTCHA {


	/**
	 * @var
	 */
	private static $instance;


	/**
	 * @return UM_reCAPTCHA
	 */
	static public function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}


	/**
	 * UM_reCAPTCHA constructor.
	 */
	function __construct() {
		// Global for backwards compatibility.
		$GLOBALS['um_recaptcha'] = $this;
		add_filter( 'um_call_object_reCAPTCHA', array( &$this, 'get_this' ) );
		add_filter( 'um_settings_default_values', array( &$this, 'default_settings' ), 10, 1 );


		if ( UM()->is_request( 'admin' ) ) {
			$this->admin();
		}

		add_action( 'plugins_loaded', array( &$this, 'init' ), 0 );
	}

	/**
	 * @return $this
	 */
	function get_this() {
		return $this;
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
	 * @return um_ext\um_recaptcha\core\Recaptcha_Setup()
	 */
	function setup() {
		if ( empty( UM()->classes['um_recaptcha_setup'] ) ) {
			UM()->classes['um_recaptcha_setup'] = new um_ext\um_recaptcha\core\Recaptcha_Setup();
		}
		return UM()->classes['um_recaptcha_setup'];
	}


	/**
	 * @return um_ext\um_recaptcha\core\reCAPTCHA_Enqueue()
	 */
	function enqueue() {
		if ( empty( UM()->classes['um_recaptcha_enqueue'] ) ) {
			UM()->classes['um_recaptcha_enqueue'] = new um_ext\um_recaptcha\core\reCAPTCHA_Enqueue();
		}
		return UM()->classes['um_recaptcha_enqueue'];
	}


	/**
	 * @return um_ext\um_recaptcha\admin\reCAPTCHA_Admin()
	 */
	function admin() {
		if ( empty( UM()->classes['um_recaptcha_admin'] ) ) {
			UM()->classes['um_recaptcha_admin'] = new um_ext\um_recaptcha\admin\reCAPTCHA_Admin();
		}
		return UM()->classes['um_recaptcha_admin'];
	}


	/**
	 * Init
	 */
	function init() {
		// Actions
		require_once um_recaptcha_path . 'includes/core/actions/um-recaptcha-form.php';
	}


	/**
	 * Captcha allowed
	 *
	 * @param $args
	 *
	 * @return bool
	 */
	function captcha_allowed( $args ) {
		$enable = false;

		$recaptcha = UM()->options()->get( 'g_recaptcha_status' );
		$your_sitekey = UM()->options()->get( 'g_recaptcha_sitekey' ) || UM()->options()->get( 'g_reCAPTCHA_site_key' );
		$your_secret = UM()->options()->get( 'g_recaptcha_secretkey' ) || UM()->options()->get( 'g_reCAPTCHA_secret_key' );

		if ( $recaptcha ) {
			$enable = true;
		}

		if ( isset( $args['g_recaptcha_status'] ) && $args['g_recaptcha_status'] ) {
			$enable = true;
		}

		if ( isset( $args['g_recaptcha_status'] ) && ! $args['g_recaptcha_status'] ) {
			$enable = false;
		}

		if ( ! $your_sitekey || ! $your_secret ) {
			$enable = false;
		}

		if ( isset( $args['mode'] ) && $args['mode'] == 'password' && ! UM()->options()->get( 'g_recaptcha_password_reset' ) ) {
			$enable = false;
		}

		return ( $enable == false ) ? false : true;
	}
}

//create class var
add_action( 'plugins_loaded', 'um_init_recaptcha', -10, 1 );
function um_init_recaptcha() {
	if ( function_exists( 'UM' ) ) {
		UM()->set_class( 'reCAPTCHA', true );
	}
}