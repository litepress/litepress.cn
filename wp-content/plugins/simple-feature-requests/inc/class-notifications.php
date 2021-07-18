<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
    // Exit if accessed directly
}

/**
 * Notifications.
 */
class JCK_SFR_Notifications
{
    /**
     * @var string
     */
    private static  $process_queue_name = 'jck_sfr_process_email_queue' ;
    /**
     * @var string
     */
    private static  $queue_option_name = 'jck_sfr_email_queue' ;
    /**
     * Run.
     */
    public static function run()
    {
        add_action( self::$process_queue_name, array( __CLASS__, 'process_email_queue' ) );
    }
    
    /**
     * @return array
     */
    private static function get_email_queue()
    {
        $queue = (array) get_option( self::$queue_option_name, array() );
        $queue = array_filter( $queue );
        return $queue;
    }
    
    /**
     * Add an email tot he queue.
     *
     * @param $args
     *
     * @return bool
     */
    private static function add_to_email_queue( $args )
    {
        $queue = self::get_email_queue();
        $queue[] = $args;
        return self::set_email_queue( $queue );
    }
    
    /**
     * Set the email queue option.
     *
     * @param $queue
     *
     * @return bool
     */
    private static function set_email_queue( $queue )
    {
        return update_option( self::$queue_option_name, $queue );
    }
    
    /**
     * Schedule an email in the queue.
     *
     * @param array $args
     */
    public static function queue_wp_mail( $args )
    {
        self::add_to_email_queue( $args );
        // schedule event to process all queued emails
        
        if ( !wp_next_scheduled( self::$process_queue_name ) ) {
            // schedule event to be fired right away
            wp_schedule_single_event( time(), self::$process_queue_name );
            // send off a request to wp-cron on shutdown
            add_action( 'shutdown', 'spawn_cron' );
        }
    
    }
    
    /**
     * Processes the email queue.
     */
    public static function process_email_queue()
    {
        $queue = self::get_email_queue();
        if ( empty($queue) ) {
            return;
        }
        // send each queued email
        foreach ( $queue as $key => $args ) {
            unset( $queue[$key] );
            if ( empty($args['to']) ) {
                continue;
            }
            $defaults = array(
                'headers'     => '',
                'attachments' => array(),
            );
            $args = wp_parse_args( $args, $defaults );
            wp_mail(
                $args['to'],
                $args['subject'],
                $args['message'],
                $args['headers'],
                $args['attachments']
            );
        }
        // update queue with removed values
        self::set_email_queue( $queue );
    }
    
    /**
     * Get notification email address.
     */
    public static function get_notification_email_address( $type = 'to' )
    {
        global  $simple_feature_requests_licence ;
        $default_email = get_option( 'admin_email' );
        return $default_email;
    }
    
    /* Email Content */
    /**
     * Get status change email contents.
     *
     * @param JCK_SFR_Feature_Request $feature_request
     *
     * @return string
     */
    public static function get_post_created_email( $feature_request )
    {
        ob_start();
        do_action( 'jck_sfr_before_post_created_email_body', $feature_request );
        do_action( 'jck_sfr_before_email_body', $feature_request );
        ?>
		<p><?php 
        _e( 'Hello,', 'simple-feature-requests' );
        ?></p>
		<p><?php 
        printf( __( 'A new %s has been posted by "%s" to your site.', 'simple-feature-requests' ), __( 'feature request', 'simple-feature-requests' ), $feature_request->get_author_nicename() );
        ?></p>
		<p>
			<a href="<?php 
        echo  esc_url( $feature_request->get_permalink() ) ;
        ?>" title="<?php 
        echo  esc_attr( $feature_request->post->post_title ) ;
        ?>">
				<?php 
        echo  $feature_request->post->post_title ;
        ?>
			</a>
		</p>
		<?php 
        echo  self::get_signature() ;
        ?>
		<?php 
        do_action( 'jck_sfr_after_post_created_email_body', $feature_request );
        do_action( 'jck_sfr_after_email_body', $feature_request );
        return ob_get_clean();
    }
    
    /**
     * Get email signature.
     *
     * @return string
     */
    public static function get_signature()
    {
        $settings = JCK_SFR_Settings::get_settings();
        if ( empty($settings) ) {
            return '';
        }
        $signature = $settings['notifications_contents_signature'];
        if ( empty($signature) ) {
            return '';
        }
        $signature = apply_filters( 'jck_sfr_email_signature', $signature );
        $site_name = get_bloginfo( 'name' );
        $site_url = get_bloginfo( 'url' );
        $signature = str_replace( array( '%site_name%', '%site_url%' ), array( $site_name, $site_url ), $signature );
        return wpautop( $signature );
    }
    
    /**
     * Is this a comment for a feature request?
     *
     * @param int $comment_id
     *
     * @return bool
     */
    public static function is_comment_for_request( $comment_id )
    {
        $comment = get_comment( $comment_id );
        $post_id = absint( $comment->comment_post_ID );
        $post_type = get_post_type( $post_id );
        return $post_type === JCK_SFR_Post_Types::$key;
    }

}