<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
    // Exit if accessed directly.
}

/**
 * Shortcodes.
 */
class JCK_SFR_Shortcodes
{
    /**
     * Init shortcodes.
     */
    public static function run()
    {
        if ( is_admin() && !wp_doing_ajax() ) {
            return;
        }
        add_shortcode( 'simple-feature-requests', array( __CLASS__, 'output' ) );
        add_shortcode( 'simple-feature-requests-sidebar', array( __CLASS__, 'sidebar_output' ) );
    }
    
    /**
     * Output archive and single templates.
     *
     * @param array $args Shortcode args.
     *
     * @return string
     */
    public static function output( $args = array() )
    {
        $queried_object = get_queried_object();
        if ( !$queried_object ) {
            return '';
        }
        $queried_object_id = ( isset( $queried_object->page_id ) ? $queried_object->page_id : $queried_object->ID );
        if ( $queried_object_id !== JCK_SFR_Post_Types::get_archive_page_id() ) {
            return '<p>' . sprintf( __( 'Please select this page (%s) as the <strong>Archive Page</strong> in <a href="%s">the settings</a>, under the "General" tab.', 'simple-feature-requests' ), get_the_title(), admin_url( 'admin.php?page=jck-sfr-settings' ) ) . '</p>';
        }
        $defaults = array(
            'sidebar'    => true,
            'submission' => true,
        );
        $args = wp_parse_args( $args, $defaults );
        $args['sidebar'] = filter_var( $args['sidebar'], FILTER_VALIDATE_BOOLEAN );
        $args['submission'] = filter_var( $args['submission'], FILTER_VALIDATE_BOOLEAN );
        $page_type = JCK_SFR_Post_Types::get_page_type();
        // If the page was not found.
        
        if ( '404' === $page_type ) {
            wp_safe_redirect( get_home_url() );
            // redirect to archive.
            die;
        }
        
        ob_start();
        
        if ( 'single' === $page_type ) {
            $args['request_query'] = JCK_SFR_Post_Types::get_current_request_query();
            JCK_SFR_Template_Hooks::include_template( 'single-feature-request', $args );
        } else {
            global  $jck_sfr_requests ;
            $jck_sfr_requests = JCK_SFR_Query::get_requests();
            JCK_SFR_Template_Hooks::include_template( 'archive-feature-requests', $args );
        }
        
        return ob_get_clean();
    }
    
    /**
     * Output sidebar.
     */
    public static function sidebar_output()
    {
        ob_start();
        /**
         * jck_sfr_sidebar hook.
         */
        do_action( 'jck_sfr_sidebar' );
        return ob_get_clean();
    }

}