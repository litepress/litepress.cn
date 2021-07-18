<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
    // Exit if accessed directly
}

/**
 * User methods.
 */
class JCK_SFR_User
{
    /**
     * User ID.
     *
     * @var int
     */
    public  $user_id = 0 ;
    /**
     * Array of votes.
     *
     * @var array
     */
    public  $votes = array() ;
    /**
     * Votes count.
     *
     * @var int
     */
    public  $votes_count = 0 ;
    /**
     * Votes meta key.
     *
     * @var string
     */
    public static  $votes_meta_key = '_jck_sfr_votes' ;
    /**
     * Main notices instance.
     *
     * @param int $user_id User ID.
     */
    public function __construct( $user_id = null )
    {
        $this->user_id = self::get_user_id( $user_id );
    }
    
    /**
     * Run.
     */
    public static function run()
    {
        add_action( 'template_redirect', array( __CLASS__, 'handle_login' ), 10 );
    }
    
    /**
     * Handle login from form.
     */
    public static function handle_login()
    {
        if ( !isset( $_POST['jck-sfr-login'] ) ) {
            return;
        }
        $notices = JCK_SFR_Notices::instance();
        $nonce = filter_input( INPUT_POST, 'jck-sfr-login-nonce', FILTER_SANITIZE_STRING );
        $username = trim( filter_input( INPUT_POST, 'jck-sfr-login-username', FILTER_SANITIZE_STRING ) );
        $email = trim( filter_input( INPUT_POST, 'jck-sfr-login-email', FILTER_SANITIZE_STRING ) );
        $password = trim( filter_input( INPUT_POST, 'jck-sfr-login-password', FILTER_SANITIZE_STRING ) );
        $repeat_password = trim( filter_input( INPUT_POST, 'jck-sfr-login-repeat-password', FILTER_SANITIZE_STRING ) );
        $user_type = trim( filter_input( INPUT_POST, 'jck-sfr-login-user-type', FILTER_SANITIZE_STRING ) );
        if ( !wp_verify_nonce( $nonce, 'jck-sfr-login' ) ) {
            $notices->add( __( 'There was an error logging you in.', 'simple-feature-requests' ), 'error' );
        }
        
        if ( $user_type === 'register' ) {
            JCK_SFR_User::register(
                $username,
                $email,
                $password,
                $repeat_password
            );
        } else {
            JCK_SFR_User::login( $email, $password );
        }
        
        if ( $notices->has_notices() ) {
            return;
        }
        wp_safe_redirect( JCK_SFR_Post_Types::get_archive_url(), 302 );
        exit;
    }
    
    /**
     * Get username.
     *
     * @return string
     */
    public function get_username()
    {
        $current_user = ( is_null( $this->user_id ) ? wp_get_current_user() : get_user_by( 'id', $this->user_id ) );
        return $current_user->user_login;
    }
    
    /**
     * Get user votes.
     *
     * @return array
     */
    public function get_votes()
    {
        if ( !$this->user_id ) {
            return array();
        }
        if ( !empty($this->votes) ) {
            return $this->votes;
        }
        $this->votes = array();
        $votes = get_user_meta( $this->user_id, self::$votes_meta_key, true );
        if ( !$votes ) {
            return $this->votes;
        }
        // Remove trashed requests.
        if ( !empty($votes) ) {
            foreach ( $votes as $request_id => $vote ) {
                $status = get_post_status( $request_id );
                if ( !$status || 'trash' === $status ) {
                    unset( $votes[$request_id] );
                }
            }
        }
        $this->votes = $votes;
        return $this->votes;
    }
    
    /**
     * Get votes count.
     *
     * @return int|mixed
     */
    public function get_votes_count()
    {
        if ( !$this->user_id ) {
            return $this->votes_count;
        }
        if ( !empty($this->votes_count) ) {
            return $this->votes_count;
        }
        $votes = $this->get_votes();
        if ( empty($votes) ) {
            return $this->votes_count;
        }
        $settings = JCK_SFR_Settings::get_settings();
        $reimburse_statuses = $settings['votes_limits_reimburse_votes'];
        foreach ( $votes as $request_id => $vote ) {
            $request = new JCK_SFR_Feature_Request( $request_id );
            if ( in_array( $request->get_status(), $reimburse_statuses, true ) ) {
                continue;
            }
            $this->votes_count += $vote;
        }
        return $this->votes_count;
    }
    
    /**
     * Has voted?
     *
     * @param int $post_id
     *
     * @return bool
     */
    public function has_voted( $post_id )
    {
        $votes = $this->get_votes();
        return apply_filters(
            'jck_sfr_has_voted',
            isset( $votes[$post_id] ),
            $post_id,
            $votes
        );
    }
    
    /**
     * Add vote.
     *
     * @param          $post_id
     *
     * @return array
     */
    public function add_vote( $post_id )
    {
        $return = array(
            'success' => false,
            'reason'  => null,
        );
        
        if ( 0 === $this->user_id ) {
            $return['reason'] = __( 'You need to login to vote for a feature.', 'simple-feature-requests' );
            return $return;
        }
        
        
        if ( $this->has_voted( $post_id ) ) {
            $return['reason'] = __( 'You have already voted for this feature.', 'simple-feature-requests' );
            return $return;
        }
        
        $votes = $this->get_votes();
        $votes_count = $this->get_votes_count();
        $votes_limit = self::get_votes_limit();
        
        if ( $votes_limit && $votes_count >= $votes_limit ) {
            $return['reason'] = __( 'Sorry, you do not have any votes remaining.', 'simple-feature-requests' );
            return $return;
        }
        
        $votes[$post_id] = 1;
        $this->votes = $votes;
        $update = update_user_meta( $this->user_id, self::$votes_meta_key, $votes );
        
        if ( !$update ) {
            $return['reason'] = __( 'There was an error adding your vote.', 'simple-feature-requests' );
            return $return;
        }
        
        $return['success'] = true;
        return $return;
    }
    
    /**
     * Remove vote.
     *
     * @param int $post_id
     *
     * @return array
     */
    public function remove_vote( $post_id )
    {
        $return = array(
            'success' => false,
            'reason'  => null,
        );
        
        if ( 0 === $this->user_id ) {
            $return['reason'] = __( 'You need to login to remove a vote from a feature.', 'simple-feature-requests' );
            return $return;
        }
        
        
        if ( !$this->has_voted( $post_id ) ) {
            $return['reason'] = __( 'You have not voted for this feature yet.', 'simple-feature-requests' );
            return $return;
        }
        
        $post_author_id = (int) get_post_field( 'post_author', $post_id );
        $settings = JCK_SFR_Settings::get_settings();
        
        if ( empty($settings['votes_general_allow_own_vote_removal']) && $post_author_id === $this->user_id ) {
            $return['reason'] = __( 'You cannot remove a vote for your own request.', 'simple-feature-requests' );
            return $return;
        }
        
        $votes = $this->get_votes();
        unset( $votes[$post_id] );
        $this->votes = $votes;
        $update = update_user_meta( $this->user_id, self::$votes_meta_key, $votes );
        
        if ( !$update ) {
            $return['reason'] = __( 'There was an error removing your vote.', 'simple-feature-requests' );
            return $return;
        }
        
        $return['success'] = true;
        return $return;
    }
    
    /**
     * Get votes limit for user.
     *
     * @return bool|int If false, then no limit.
     */
    public static function get_votes_limit()
    {
        global  $simple_feature_requests_licence ;
        $settings = JCK_SFR_Settings::get_settings();
        $limit = ( empty($settings['votes_limits_limit']) ? 0 : $settings['votes_limits_limit'] );
        return ( empty($limit) ? false : $limit );
    }
    
    /**
     * Validate user logging in.
     *
     * @param string|null $email
     * @param string|null $password
     *
     * @return bool|WP_User|WP_Error
     */
    public static function login( $email, $password )
    {
        if ( is_user_logged_in() ) {
            return wp_get_current_user();
        }
        $error = false;
        $notices = JCK_SFR_Notices::instance();
        
        if ( empty($email) ) {
            $notices->add( __( 'Please enter an email.', 'simple-feature-requests' ), 'error' );
            $error = true;
        }
        
        
        if ( empty($password) ) {
            $notices->add( __( 'Please enter a password.', 'simple-feature-requests' ), 'error' );
            $error = true;
        }
        
        if ( $error ) {
            return false;
        }
        $credentials = array(
            'user_login'    => $email,
            'user_password' => $password,
            'remember'      => true,
        );
        $user = wp_signon( $credentials, is_ssl() );
        if ( is_wp_error( $user ) ) {
            $notices->add( $user->get_error_message(), 'error' );
        }
        return $user;
    }
    
    /**
     * Register user.
     *
     * @param string|null $username
     * @param string|null $email
     * @param string|null $password
     * @param string|null $repeat_password
     *
     * @return bool|WP_User|WP_Error
     */
    public static function register(
        $username,
        $email,
        $password,
        $repeat_password
    )
    {
        $error = false;
        $notices = JCK_SFR_Notices::instance();
        
        if ( empty($username) ) {
            $notices->add( __( 'Please enter a username.', 'simple-feature-requests' ), 'error' );
            $error = true;
        }
        
        
        if ( empty($email) ) {
            $notices->add( __( 'Please enter an email.', 'simple-feature-requests' ), 'error' );
            $error = true;
        }
        
        
        if ( empty($password) ) {
            $notices->add( __( 'Please enter a password.', 'simple-feature-requests' ), 'error' );
            $error = true;
        }
        
        
        if ( empty($repeat_password) ) {
            $notices->add( __( 'Please enter a repeat password.', 'simple-feature-requests' ), 'error' );
            $error = true;
        }
        
        
        if ( $password !== $repeat_password ) {
            $notices->add( __( 'Your passwords did not match.', 'simple-feature-requests' ), 'error' );
            $error = true;
        }
        
        if ( $error ) {
            return false;
        }
        $username_exists = username_exists( $username );
        $email_exists = email_exists( $email );
        
        if ( $username_exists ) {
            $notices->add( __( 'That username has already been registered.', 'simple-feature-requests' ), 'error' );
            $error = true;
        }
        
        
        if ( $email_exists ) {
            $notices->add( __( 'That email has already been registered.', 'simple-feature-requests' ) );
            $error = true;
        }
        
        if ( $error ) {
            return false;
        }
        $user_id = wp_insert_user( array(
            'user_login' => wp_slash( $username ),
            'user_email' => wp_slash( $email ),
            'user_pass'  => $password,
            'role'       => 'subscriber',
        ) );
        
        if ( !$user_id || is_wp_error( $user_id ) ) {
            $notices->add( __( 'There was an issue registering your account. Please try again.', 'simple-feature-requests' ), 'error' );
            return false;
        }
        
        return self::login( $email, $password );
    }
    
    /**
     * Get user_id.
     *
     * @param int|null $user_id
     *
     * @return int
     */
    public static function get_user_id( $user_id = null )
    {
        return ( !is_null( $user_id ) ? $user_id : get_current_user_id() );
    }

}