<?php
/**
 * Double Opt-In Helper module
 *
 * @link https://contactform7.com/doi-helper/
 */


add_action( 'wpcf7_doi', 'wpcf7_doihelper_start_session', 10, 3 );

/**
 * Starts a double opt-in session.
 */
function wpcf7_doihelper_start_session( $agent_name, $args, &$token ) {
	if ( isset( $token ) ) {
		return;
	}

	if ( ! function_exists( 'doihelper_start_session' ) ) {
		return;
	}

	$submission = WPCF7_Submission::get_instance();

	if ( ! $submission ) {
		return;
	}

	$contact_form = $submission->get_contact_form();

	$do_doi = ! array_filter(
		$contact_form->additional_setting( 'doi', false ),
		function ( $setting ) {
			return in_array( $setting, array( 'off', 'false', '0' ), true );
		}
	);

	$do_doi = apply_filters( 'wpcf7_do_doi', $do_doi, $agent_name, $args );

	if ( ! $do_doi ) {
		return;
	}

	$token = doihelper_start_session( $agent_name, $args );
}
