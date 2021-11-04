<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * add recaptcha
 *
 * @param $args
 */
function um_recaptcha_add_captcha( $args ) {
	if ( ! UM()->reCAPTCHA()->captcha_allowed( $args ) ) {
		return;
	}

	$version = UM()->options()->get( 'g_recaptcha_version' );
	switch( $version ) {
		case 'v3':

			$t_args = compact( 'args' );
			UM()->get_template( 'captcha_v3.php', um_recaptcha_plugin, $t_args, true );

			break;

		case 'v2':
		default:

			$options = array(
				'data-type'    => UM()->options()->get( 'g_recaptcha_type' ),
				'data-size'    => UM()->options()->get( 'g_recaptcha_size' ),
				'data-theme'   => UM()->options()->get( 'g_recaptcha_theme' ),
				'data-sitekey' => UM()->options()->get( 'g_recaptcha_sitekey' ),
			);

			$attrs = array();
			foreach ( $options as $att => $value ) {
				if ( $value ) {
					$att = esc_html( $att );
					$value = esc_attr( $value );
					$attrs[] = "{$att}=\"{$value}\"";
				}
			}

			if ( ! empty( $attrs ) ) {
				$attrs = implode( ' ', $attrs );
			} else {
				$attrs = '';
			}

			$t_args = compact( 'args', 'attrs', 'options' );
			UM()->get_template( 'captcha.php', um_recaptcha_plugin, $t_args, true );

			break;
	}
	wp_enqueue_script( 'um-recaptcha' );
}
add_action( 'um_after_register_fields', 'um_recaptcha_add_captcha', 500 );
add_action( 'um_after_login_fields', 'um_recaptcha_add_captcha', 500 );
add_action( 'um_after_password_reset_fields', 'um_recaptcha_add_captcha', 500 );


/**
 * form error handling
 *
 * @link https://developers.google.com/recaptcha/docs/verify#api_request
 * @link https://developers.google.com/recaptcha/docs/v3#interpreting_the_score
 *
 * @param $args
 */
function um_recaptcha_validate( $args ) {
	if ( isset( $args['mode'] ) && ! in_array( $args['mode'], array( 'login', 'register', 'password' ), true ) && ! isset( $args['_social_login_form'] ) ) {
		return;
	}

	if ( ! UM()->reCAPTCHA()->captcha_allowed( $args ) ) {
		return;
	}

	$version = UM()->options()->get( 'g_recaptcha_version' );
	switch( $version ) {
		case 'v3':
			$your_secret = trim( UM()->options()->get( 'g_reCAPTCHA_secret_key' ) );
			break;

		case 'v2':
		default:
			$your_secret = trim( UM()->options()->get( 'g_recaptcha_secretkey' ) );
			break;
	}

	if ( empty( $_POST['g-recaptcha-response'] ) ) {
		UM()->form()->add_error( 'recaptcha', __( 'Please confirm you are not a robot', 'um-recaptcha' ) );
		return;
	} else {
		$client_captcha_response = sanitize_textarea_field( $_POST['g-recaptcha-response'] );
	}

	$user_ip  = sanitize_text_field( $_SERVER['REMOTE_ADDR'] );
	$response = wp_remote_get( "https://www.google.com/recaptcha/api/siteverify?secret=$your_secret&response=$client_captcha_response&remoteip=$user_ip" );

	if ( is_array( $response ) ) {

		$result = json_decode( $response['body'] );

		$score = UM()->options()->get( 'g_reCAPTCHA_score' );
		if ( ! empty( $args['g_recaptcha_score'] ) ) {
			// use form setting for score
			$score = $args['g_recaptcha_score'];
		}

		if ( empty( $score ) ) {
			// set default 0.6 because Google recommend by default set 0.5 score
			// https://developers.google.com/recaptcha/docs/v3#interpreting_the_score
			$score = 0.6;
		}
		// available to change score based on form $args
		$validate_score = apply_filters( 'um_recaptcha_score_validation', $score, $args );

		if ( isset( $result->score ) && $result->score < $validate_score ) {
			UM()->form()->add_error( 'recaptcha', __( 'reCAPTCHA: it is very likely a bot.', 'um-recaptcha' ) );
		} elseif ( isset( $result->{'error-codes'} ) && ! $result->success ) {
			$error_codes = array(
				'missing-input-secret'   => __( 'The secret parameter is missing.', 'um-recaptcha' ),
				'invalid-input-secret'   => __( 'The secret parameter is invalid or malformed.', 'um-recaptcha' ),
				'missing-input-response' => __( 'The response parameter is missing.', 'um-recaptcha' ),
				'invalid-input-response' => __( 'The response parameter is invalid or malformed.', 'um-recaptcha' ),
				'bad-request'            => __( 'The request is invalid or malformed.', 'um-recaptcha' ),
				'timeout-or-duplicate'   => __( 'The response is no longer valid: either is too old or has been used previously.', 'um-recaptcha' ),
			);

			foreach ( $result->{'error-codes'} as $key => $error_code ) {
				UM()->form()->add_error( 'recaptcha', $error_codes[ $error_code ] );
			}
		}

	}
}
add_action( 'um_submit_form_errors_hook', 'um_recaptcha_validate', 20 );
add_action( 'um_reset_password_errors_hook', 'um_recaptcha_validate', 20 );


/**
 * reCAPTCHA scripts/styles enqueue in the page with a form
 */
function um_recaptcha_enqueue_scripts( $args ) {
	if ( ! UM()->reCAPTCHA()->captcha_allowed( $args ) ) {
		return;
	}

	UM()->reCAPTCHA()->enqueue()->wp_enqueue_scripts();
}
add_action( 'um_pre_register_shortcode', 'um_recaptcha_enqueue_scripts' );
add_action( 'um_pre_login_shortcode', 'um_recaptcha_enqueue_scripts' );
add_action( 'um_pre_password_shortcode', 'um_recaptcha_enqueue_scripts' );


/**
 * reCAPTCHA scripts/styles enqueue in member directory
 *
 * @param array $args
 */
function um_recaptcha_directory_enqueue_scripts( $args ) {
	if ( ! UM()->reCAPTCHA()->captcha_allowed( $args ) ) {
		return;
	}

	if ( is_user_logged_in() || empty( $args['show_pm_button'] ) ) {
		return;
	}

	UM()->reCAPTCHA()->enqueue()->wp_enqueue_scripts();
}
add_action( 'um_pre_directory_shortcode', 'um_recaptcha_directory_enqueue_scripts', 10, 1 );
