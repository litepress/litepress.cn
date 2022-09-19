<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
    // Exit if accessed directly
}

/**
 * Modify front-end post queries and loops.
 */
class JCK_SFR_Query
{
    /**
     * @var string
     */
    private static  $post_type = 'cpt_feature_requests' ;
    /**
     * Top requests transient name.
     *
     * @var string
     */
    public static  $top_requests_transient_name = 'jck_sfr_top_requests' ;
    /**
     * Run class.
     */
    public static function run()
    {
        add_action(
            'pre_get_posts',
            array( __CLASS__, 'search' ),
            100,
            1
        );
        add_action(
            'pre_get_posts',
            array( __CLASS__, 'prepare_main_query' ),
            100,
            1
        );
        add_action(
            'pre_get_posts',
            array( __CLASS__, 'filter' ),
            100,
            1
        );
        add_action(
            'jck_sfr_status_updated',
            array( __CLASS__, 'status_updated' ),
            10,
            2
        );
    }
    
    /**
     * Get feature requests.
     *
     * @param array $args Array or WP_Query args.
     *
     * @return WP_Query
     */
    public static function get_requests( $args = array() )
    {
        $defaults = array(
            'post_type'      => self::$post_type,
            'jck_sfr_query'  => true,
            'posts_per_page' => JCK_SFR_Post_Types::get_posts_per_page(),
            'paged'          => ( get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1 ),
        );
        $args = apply_filters( 'jck_sfr_get_requests', wp_parse_args( $args, $defaults ) );
        return new WP_Query( $args );
    }
    
    /**
     * Count pending requests.
     *
     * @return int
     */
    public static function count_pending_requests()
    {
        global  $wpdb ;
        static  $count = null ;
        if ( !is_null( $count ) ) {
            return apply_filters( 'jck_sfr_pending_count', $count );
        }
        $count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->posts} AS posts\n\t\t\t\tLEFT JOIN {$wpdb->postmeta} AS postmeta ON postmeta.post_id = posts.ID\n\t\t\t\tWHERE posts.post_type = %s\n\t\t\t\tAND postmeta.meta_key = 'jck_sfr_status'\n\t\t\t\tAND postmeta.meta_value = 'pending'", self::$post_type ) );
        if ( empty($count) || is_wp_error( $count ) ) {
            return 0;
        }
        $count = absint( $count );
        return apply_filters( 'jck_sfr_pending_count', $count );
    }
    
    /**
     * Get requests ordered by status.
     *
     * @param array $status
     * @param array $taxonomies tax_query formatted array of taxonomies.
     *
     * @return array
     */
    public static function get_grouped_requests( $statuses = array(), $taxonomies = array() )
    {
        $grouped_requests = array_fill_keys( $statuses, array() );
        $query_args = array(
            'jck_sfr_query'  => false,
            'posts_per_page' => -1,
            'meta_query'     => array(
            'status' => array(
            'key'     => 'jck_sfr_status',
            'value'   => $statuses,
            'compare' => 'IN',
        ),
        ),
            'tax_query'      => array(),
        );
        if ( !empty($taxonomies) ) {
            $query_args['tax_query'][] = $taxonomies;
        }
        $jck_sfr_requests = self::get_requests( $query_args );
        if ( $jck_sfr_requests->have_posts() ) {
            foreach ( $jck_sfr_requests->posts as $jck_sfr_request ) {
                $jck_sfr_request = new JCK_SFR_Feature_Request( $jck_sfr_request );
                $status = $jck_sfr_request->get_status();
                $grouped_requests[$status][] = $jck_sfr_request;
            }
        }
        $grouped_requests = array_filter( $grouped_requests );
        return $grouped_requests;
    }
    
    /**
     * @param WP_Query $query
     *
     * @return bool
     */
    public static function is_query_modification_allowed( $query )
    {
        if ( $query->get( 'post_type' ) !== "cpt_feature_requests" ) {
            return false;
        }
        $is_ajax = $query->get( 'jck_sfr_ajax' );
        if ( $is_ajax ) {
            return true;
        }
        if ( is_admin() && empty($is_ajax) ) {
            return false;
        }
        if ( is_admin() || !$query->get( 'jck_sfr_query' ) ) {
            return false;
        }
        return true;
    }
    
    /**
     * Add current user's pending posts.
     *
     * @param WP_Query $query
     */
    public static function prepare_main_query( $query )
    {
        if ( !self::is_query_modification_allowed( $query ) ) {
            return;
        }
        $other_user_pending_ids = self::get_other_user_pending_ids();
        $query->set( 'post__not_in', $other_user_pending_ids );
        $query->set( 'post_status', self::get_post_stati_to_query() );
        self::set_status_query( $query );
    }
    
    /**
     * Get status query.
     *
     * @param WP_Query $query
     */
    public static function set_status_query( $query )
    {
        $is_ajax = $query->get( 'jck_sfr_ajax' );
        $search = $query->get( 's' );
        // If ajax or search, allow all statuses.
        if ( $is_ajax && !empty($search) || !empty($search) ) {
            return;
        }
        $status = ( isset( $_REQUEST['status'] ) ? sanitize_text_field( $_REQUEST['status'] ) : false );
        $meta_query = (array) $query->get( 'meta_query' );
        $meta_query['status'] = array(
            'key'     => 'jck_sfr_status',
            'value'   => array( 'completed', 'declined' ),
            'compare' => 'NOT IN',
        );
        if ( !is_user_logged_in() ) {
            $meta_query['status']['value'][] = 'pending';
        }
        $viewable_statuses = jck_sfr_get_viewable_statuses();
        
        if ( $status && in_array( $status, $viewable_statuses, true ) ) {
            $meta_query['status']['value'] = array( $status );
            $meta_query['status']['compare'] = 'IN';
        }
        
        $query->set( 'meta_query', $meta_query );
    }
    
    /**
     * Modify query based on filters.
     *
     * @param WP_Query $query
     */
    public static function filter( $query )
    {
        if ( !self::is_query_modification_allowed( $query ) ) {
            return;
        }
        if ( empty($_REQUEST['filter']) ) {
            return;
        }
        switch ( $_REQUEST['filter'] ) {
            case 'top':
                $query->set( 'meta_key', 'jck_sfr_votes' );
                $query->set( 'orderby', 'meta_value_num post_date' );
                $query->set( 'order', 'DESC' );
                break;
            case 'my-requests':
                $query->set( 'author', get_current_user_id() );
                break;
        }
    }
    
    /**
     * Modify query based on search.
     *
     * @param WP_Query $query
     */
    public static function search( $query )
    {
        if ( !self::is_query_modification_allowed( $query ) ) {
            return;
        }
        if ( empty($_REQUEST['search']) ) {
            return;
        }
        $search = sanitize_text_field( $_REQUEST['search'] );
        $query->set( 's', $search );
    }
    
    /**
     * Delete top requests transient when statuses are updated.
     */
    public static function status_updated()
    {
        delete_transient( self::$top_requests_transient_name );
    }
    
    /**
     * Get post stati to query based on current user.
     *
     * @return array|string
     */
    public static function get_post_stati_to_query()
    {
        if ( !is_user_logged_in() ) {
            return 'publish';
        }
        return array( 'publish', 'pending' );
    }
    
    /**
     * Get pending post IDs of all other users.
     *
     * @return array
     */
    public static function get_other_user_pending_ids()
    {
        global  $wpdb ;
        static  $ids = null ;
        if ( !is_null( $ids ) ) {
            return $ids;
        }
        
        if ( !is_user_logged_in() ) {
            $ids = array();
            return $ids;
        }
        
        $current_user_id = get_current_user_id();
        $ids = $wpdb->get_col( $wpdb->prepare( "\n\t\t\tSELECT DISTINCT ID \n\t\t\tFROM {$wpdb->posts} as posts\n\t\t\tINNER JOIN {$wpdb->postmeta} as meta ON posts.ID = meta.post_id\n\t\t\tWHERE posts.post_author != %d\n\t\t\t\tAND posts.post_type = 'cpt_feature_requests'\n\t\t\t\tAND ( posts.post_status = 'pending' OR ( meta.meta_key = 'jck_sfr_status' && meta.meta_value = 'pending' ) )\n\t\t\t", $current_user_id ) );
        return $ids;
    }
    
    /**
     * Get top requests.
     *
     * @return array
     */
    public static function get_top_requests()
    {
        $top_requests = get_transient( self::$top_requests_transient_name );
        if ( !empty($top_requests) ) {
            return $top_requests;
        }
        $top_requests = array();
        $args = array(
            'post_type'      => JCK_SFR_Post_Types::$key,
            'posts_per_page' => 5,
            'meta_query'     => array(
            'votes' => array(
            'key'  => 'jck_sfr_votes',
            'type' => 'numeric',
        ),
            array(
            'key'     => 'jck_sfr_status',
            'value'   => apply_filters( 'jck_sfr_ignore_statuses_in_top_requests', array( 'completed', 'pending' ) ),
            'compare' => 'NOT IN',
        ),
        ),
            'orderby'        => 'votes post_date',
            'order'          => 'DESC',
        );
        $query = new WP_Query( $args );
        if ( !$query->have_posts() ) {
            return $top_requests;
        }
        foreach ( $query->posts as $request ) {
            $top_requests[] = new JCK_SFR_Feature_Request( $request );
        }
        set_transient( self::$top_requests_transient_name, $top_requests, 12 * HOUR_IN_SECONDS );
        return $top_requests;
    }

}