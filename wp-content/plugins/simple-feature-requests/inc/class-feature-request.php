<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
    // Exit if accessed directly
}

/**
 * Feature Request Class.
 */
class JCK_SFR_Feature_Request
{
    /**
     * @var WP_Post
     */
    public  $post ;
    /**
     * Voters.
     *
     * @var null|array
     */
    protected  $voters = null ;
    /**
     * Construct post.
     *
     * @param int|WP_Post $post Post ID or object.
     *
     * @throws Exception Throws exception if post passed is not a feature request.
     */
    public function __construct( $post )
    {
        if ( is_numeric( $post ) ) {
            $post = get_post( $post );
        }
        if ( JCK_SFR_Post_Types::$key !== $post->post_type ) {
            throw new Exception( __( 'This post is not a feature request.', 'simple-feature-requests' ) );
        }
        $this->post = $post;
    }
    
    /**
     * Get votes count.
     *
     * @return int
     */
    public function get_votes_count()
    {
        $votes = absint( $this->get_meta( 'votes' ) );
        return apply_filters( 'jck_sfr_votes_count', $votes, $this );
    }
    
    /**
     * Get voters.
     *
     * @param bool $include_athor
     *
     * @return array
     */
    public function get_voters( $include_athor = true )
    {
        if ( !is_null( $this->voters ) ) {
            return $this->voters;
        }
        $args = array(
            'meta_query' => array( array(
            'key'     => JCK_SFR_User::$votes_meta_key,
            'value'   => sprintf( 'i:%d;', $this->post->ID ),
            'compare' => 'LIKE',
        ) ),
        );
        if ( !$include_athor ) {
            $args['exlcude'] = array( $this->post->post_author );
        }
        $this->voters = get_users( $args );
        return $this->voters;
    }
    
    /**
     * Get status.
     *
     * @return string
     */
    public function get_status()
    {
        $status = $this->get_meta( 'status' );
        if ( !$status ) {
            $status = jck_sfr_get_default_post_status();
        }
        return $status;
    }
    
    /**
     * Get author nicename.
     *
     * @return string
     */
    public function get_author_nicename()
    {
        return get_the_author_meta( 'user_nicename', $this->post->post_author );
    }
    
    /**
     * Get author display name.
     *
     * @return string
     */
    public function get_author_display_name()
    {
        return get_the_author_meta( 'display_name', $this->post->post_author );
    }
    
    /**
     * Get author email.
     *
     * @return string
     */
    public function get_author_email()
    {
        return get_the_author_meta( 'email', $this->post->post_author );
    }
    
    /**
     * Get permalink.
     *
     * @return string
     */
    public function get_permalink()
    {
        static  $permalink = null ;
        if ( !is_null( $permalink ) ) {
            return $permalink;
        }
        $permalink = get_permalink( $this->post->ID );
        return $permalink;
    }
    
    /**
     * Update meta.
     *
     * @param $key
     * @param $value
     */
    public function update_meta( $key, $value )
    {
        $value = apply_filters(
            'jck_sfr_update_meta_value',
            $value,
            $key,
            $this
        );
        $meta_key = sprintf( 'jck_sfr_%s', $key );
        update_post_meta( $this->post->ID, $meta_key, $value );
        do_action(
            'jck_sfr_meta_updated',
            $key,
            $value,
            $this
        );
        do_action( 'jck_sfr_' . $key . '_updated', $value, $this );
    }
    
    /**
     * Get meta.
     *
     * @param $key
     *
     * @return mixed
     */
    public function get_meta( $key )
    {
        $meta_key = sprintf( 'jck_sfr_%s', $key );
        $meta = get_post_meta( $this->post->ID, $meta_key, true );
        return apply_filters(
            'jck_sfr_get_meta',
            $meta,
            $key,
            $this
        );
    }
    
    /**
     * Increment votes count.
     *
     * @param string   $type add|remove
     * @param int      $inc
     * @param null|int $user_id
     *
     * @return array
     */
    public function set_votes_count( $type = 'add', $inc = 1, $user_id = null )
    {
        $return = array(
            'success'             => false,
            'reason'              => null,
            'updated_votes_count' => null,
        );
        $user = new JCK_SFR_User( $user_id );
        $vote = ( 'add' === $type ? $user->add_vote( $this->post->ID ) : $user->remove_vote( $this->post->ID ) );
        if ( !$vote['success'] ) {
            return $vote;
        }
        $votes_count = $this->get_votes_count();
        $votes_count = ( $type === 'add' ? $votes_count + $inc : $votes_count - $inc );
        $this->set_votes( $votes_count );
        $return['success'] = true;
        $return['updated_votes_count'] = $votes_count;
        return $return;
    }
    
    /**
     * Set votes.
     *
     * @param int $votes
     */
    public function set_votes( $votes )
    {
        $votes = absint( $votes );
        $this->update_meta( 'votes', $votes );
    }
    
    /**
     * Set status.
     *
     * @param string $status
     */
    public function set_status( $status, $force = false )
    {
        if ( empty($status) ) {
            return;
        }
        if ( !$force && $status === $this->get_status() ) {
            return;
        }
        $statuses = jck_sfr_get_statuses();
        if ( !isset( $statuses[$status] ) ) {
            $status = 'pending';
        }
        $this->update_meta( 'status', $status );
    }
    
    /**
     * Add voters to a request.
     *
     * @param array $users Array of WP_User objects.
     */
    public function add_voters( $users = array() )
    {
        if ( empty($users) ) {
            return;
        }
        foreach ( $users as $user ) {
            if ( !is_a( $user, 'WP_User' ) ) {
                continue;
            }
            if ( $this->has_user_voted( $user->ID ) ) {
                continue;
            }
            $this->set_votes_count( 'add', 1, $user->ID );
        }
    }
    
    /**
     * Update taxonomies.
     *
     * @param array|bool $taxonomies
     * @param bool       $append
     */
    public function update_taxonomies( $taxonomies, $append = false )
    {
        if ( empty($taxonomies) ) {
            return;
        }
        foreach ( $taxonomies as $taxonomy => $terms ) {
            wp_set_post_terms(
                $this->post->ID,
                $terms,
                $taxonomy,
                $append
            );
            do_action(
                'jck_sfr_taxonomy_updated',
                $taxonomy,
                $terms,
                $this
            );
            do_action( 'jck_sfr_' . $taxonomy . '_updated', $terms, $this );
        }
    }
    
    /**
     * Set attachments.
     *
     * @param array|bool $taxonomies
     * @param bool       $append
     */
    public function set_attachments( $attachment_ids )
    {
        if ( empty($attachment_ids) ) {
            return;
        }
        $attachments = array_filter( array_map( 'intval', $attachment_ids ) );
        // ensure attachment ids
        foreach ( $attachments as $attachment_id ) {
            // attach post as attachment parent
            wp_update_post( array(
                'ID'          => $attachment_id,
                'post_parent' => $this->post->ID,
            ) );
            update_post_meta( $attachment_id, '_via', 'jck_sfr' );
            // needed for cron job to clean unused items
        }
        update_post_meta( $this->post->ID, '_attachments', $attachments );
    }
    
    /**
     * Has user voted?
     *
     * @param null|int $user_id User ID to check.
     *
     * @return bool
     */
    public function has_user_voted( $user_id = null )
    {
        $user = new JCK_SFR_User( $user_id );
        return $user->has_voted( $this->post->ID );
    }
    
    /**
     * Get vote button text.
     *
     * @return string
     */
    public function get_vote_button_text()
    {
        $has_voted = $this->has_user_voted();
        $text = ( $has_voted ? __( 'Voted', 'simple-feature-requests' ) : __( 'Vote', 'simple-feature-requests' ) );
        return apply_filters(
            'jck_sfr_vote_button_text',
            $text,
            $has_voted,
            $this
        );
    }
    
    /**
     * Is pending?
     *
     * @return bool
     */
    public function is_pending()
    {
        return 'pending' === $this->post->post_status;
    }
    
    /**
     * Is single?
     *
     * @return bool
     */
    public function is_single()
    {
        return JCK_SFR_Post_Types::is_type( 'single' );
    }
    
    /**
     * Loop item wrapper class.
     */
    public function wrapper_class()
    {
        $classes = array( 'jck-sfr-loop-item-wrapper' );
        $page_type = JCK_SFR_Post_Types::get_page_type();
        $classes[] = 'jck-sfr-loop-item-wrapper--' . $page_type;
        $classes = apply_filters( 'jck_sfr_loop_item_wrapper_class', $classes );
        printf( 'class="%s"', implode( ' ', array_map( 'esc_attr', $classes ) ) );
    }
    
    /**
     * Loop item wrapper class.
     */
    public function item_class()
    {
        $classes = apply_filters( 'jck_sfr_loop_item_class', array( 'jck-sfr-loop-item' ) );
        printf( 'class="%s"', implode( ' ', array_map( 'esc_attr', $classes ) ) );
    }

}