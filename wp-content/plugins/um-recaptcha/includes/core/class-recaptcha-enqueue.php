<?php
namespace um_ext\um_recaptcha\core;

if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class reCAPTCHA_Enqueue
 * @package um_ext\um_recaptcha\core
 */
class reCAPTCHA_Enqueue {


	/**
	 * reCAPTCHA_Enqueue constructor.
	 */
	function __construct() {
	}


	/**
	 * reCAPTCHA scripts/styles enqueue
	 */
	function wp_enqueue_scripts() {
		wp_register_style( 'um_recaptcha', um_recaptcha_url . 'assets/css/um-recaptcha.css' );
		wp_enqueue_style( 'um_recaptcha' );

		$version = UM()->options()->get( 'g_recaptcha_version' );
		switch( $version ) {
			case 'v3':

				$site_key = UM()->options()->get( 'g_reCAPTCHA_site_key' );

				wp_register_script( 'google-recapthca-api-v3', "https://www.recaptcha.net/recaptcha/api.js?render=$site_key" );
				wp_register_script( 'um-recaptcha', um_recaptcha_url . 'assets/js/um-recaptcha.js', array( 'jquery', 'google-recapthca-api-v3' ), um_recaptcha_version, true );

				break;

			case 'v2':
			default:

				$language_code = UM()->options()->get( 'g_recaptcha_language_code' );
				$language_code = apply_filters( 'um_recaptcha_language_code', $language_code );

				$site_key = UM()->options()->get( 'g_recaptcha_sitekey' );

				wp_register_script( 'google-recapthca-api-v2', "https://www.recaptcha.net/recaptcha/api.js?onload=onloadCallback&render=explicit&hl=$language_code" );
				wp_register_script( 'um-recaptcha', um_recaptcha_url . 'assets/js/um-recaptcha.js', array( 'jquery', 'google-recapthca-api-v2' ), um_recaptcha_version, true );

				break;
		}

		wp_enqueue_script( 'um-recaptcha' );

		wp_localize_script( 'um-recaptcha', 'umRecaptchaData', array(
			'version'   => $version,
			'site_key'  => $site_key,
		) );
	}

}