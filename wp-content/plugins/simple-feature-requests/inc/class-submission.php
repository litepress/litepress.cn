<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
    // Exit if accessed directly
}

/**
 * Setup submission methods.
 */
class JCK_SFR_Submission
{
    /**
     * Run class.
     */
    public static function run()
    {
        add_action( 'template_redirect', array( __CLASS__, 'handle_submission' ), 10 );
    }
    
    /**
     * Handle feature request submission.
     */
    public static function handle_submission()
    {
        if ( !isset( $_POST['jck-sfr-submission'] ) ) {
            return;
        }
        $notices = JCK_SFR_Notices::instance();
        $nonce = filter_input( INPUT_POST, 'jck-sfr-submission-nonce', FILTER_SANITIZE_STRING );
        $title = trim( filter_input( INPUT_POST, 'jck-sfr-submission-title', FILTER_SANITIZE_STRING ) );
        $description = trim( filter_input( INPUT_POST, 'jck-sfr-submission-description', FILTER_SANITIZE_STRING ) );
        $username = trim( filter_input( INPUT_POST, 'jck-sfr-login-username', FILTER_SANITIZE_STRING ) );
        $email = trim( filter_input( INPUT_POST, 'jck-sfr-login-email', FILTER_SANITIZE_STRING ) );
        $password = trim( filter_input( INPUT_POST, 'jck-sfr-login-password', FILTER_SANITIZE_STRING ) );
        $repeat_password = trim( filter_input( INPUT_POST, 'jck-sfr-login-repeat-password', FILTER_SANITIZE_STRING ) );
        $user_type = trim( filter_input( INPUT_POST, 'jck-sfr-login-user-type', FILTER_SANITIZE_STRING ) );
        if ( !wp_verify_nonce( $nonce, 'jck-sfr-submission' ) ) {
            $notices->add( __( 'There was an error submitting your request.', 'simple-feature-requests' ), 'error' );
        }
        if ( empty($title) ) {
            $notices->add( __( 'Please enter a request title.', 'simple-feature-requests' ), 'error' );
        }
        if ( empty($description) ) {
            $notices->add( __( 'Please enter a request description.', 'simple-feature-requests' ), 'error' );
        }
        
        if ( $user_type === 'register' ) {
            $user = JCK_SFR_User::register(
                $username,
                $email,
                $password,
                $repeat_password
            );
        } else {
            $user = JCK_SFR_User::login( $email, $password );
        }
        
        do_action( 'jck_sfr_submission_notices', $notices );
        if ( $notices->has_notices() ) {
            return;
        }
        $args = apply_filters( 'jck_sfr_submission_args', array(
            'title'       => $title,
            'description' => $description,
            'user'        => $user,
        ) );
        $request_id = JCK_SFR_Factory::create( $args );
        
        if ( !$request_id ) {
            $notices->add( __( 'Sorry, there was an issue adding that request. Please try again.', 'simple-feature-requests' ), 'error' );
            return;
        }
        
        wp_safe_redirect( get_permalink( $request_id ), 302 );
        exit;
    }

}