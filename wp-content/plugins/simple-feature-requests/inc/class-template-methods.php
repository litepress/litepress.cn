<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
    // Exit if accessed directly
}

/**
 * Template Hooks.
 */
class JCK_SFR_Template_Methods
{
    /**
     * Get filters.
     *
     * @return array
     */
    public static function get_filters()
    {
        $archive_url = jck_sfr_get_archive_url_with_filters( array( 'filter', 'search' ) );
        $filters = array(
            'latest' => array(
            'type'  => 'text',
            'url'   => $archive_url,
            'class' => array( 'active' ),
            'label' => __( 'Latest', 'simple-feature-requests' ),
        ),
            'top'    => array(
            'type'  => 'text',
            'url'   => add_query_arg( array(
            'filter' => 'top',
        ), $archive_url ),
            'class' => array(),
            'label' => __( 'Top', 'simple-feature-requests' ),
        ),
        );
        if ( is_user_logged_in() ) {
            $filters['my-requests'] = array(
                'type'  => 'text',
                'url'   => add_query_arg( array(
                'filter' => 'my-requests',
            ), $archive_url ),
                'class' => array(),
                'label' => __( 'My Requests', 'simple-feature-requests' ),
            );
        }
        $status_excludes = ( is_user_logged_in() ? array() : array( 'pending' ) );
        $filters['status'] = array(
            'type'    => 'select',
            'class'   => array(),
            'label'   => __( 'Status', 'simple-feature-requests' ),
            'options' => jck_sfr_get_statuses( $status_excludes ),
        );
        
        if ( isset( $_GET['filter'] ) && isset( $filters[$_GET['filter']] ) ) {
            $filters['latest']['class'] = array();
            $filters[$_GET['filter']]['class'][] = 'active';
        }
        
        return apply_filters( 'jck_sfr_filters', $filters );
    }
    
    /**
     * Get filter HTML.
     *
     * @param array $filter
     *
     * @return string
     */
    public static function get_filter_html( $filter )
    {
        $method_name = sprintf( 'filter_html_%s', $filter['type'] );
        if ( !method_exists( __CLASS__, $method_name ) ) {
            return '';
        }
        ob_start();
        call_user_func_array( array( __CLASS__, $method_name ), array( $filter ) );
        return ob_get_clean();
    }
    
    /**
     * Get text filter HTML.
     *
     * @param $filter
     */
    public static function filter_html_text( $filter )
    {
        ?>
		<a href="<?php 
        esc_attr_e( $filter['url'] );
        ?>" class="jck-sfr-filters__filter-item-button <?php 
        echo  implode( ' ', $filter['class'] ) ;
        ?>">
			<?php 
        echo  $filter['label'] ;
        ?>
		</a>
		<?php 
    }
    
    /**
     * Get select filter HTML.
     *
     * @param $filter
     */
    public static function filter_html_select( $filter )
    {
        $base_url = jck_sfr_get_archive_url_with_filters( array( $filter['key'], 'search' ) );
        $selected = filter_input( INPUT_GET, $filter['key'] );
        $option_keys = array_keys( $filter['options'] );
        $selected = ( in_array( $selected, $option_keys, true ) ? $selected : false );
        ?>
		<span class="jck-sfr-filters__filter-item-button <?php 
        if ( $selected ) {
            echo  'active' ;
        }
        ?>">
			<select class="<?php 
        echo  implode( ' ', $filter['class'] ) ;
        ?>" onchange="location.href = this.value;">
				<option value="<?php 
        echo  esc_attr( $base_url ) ;
        ?>"><?php 
        printf( '%s %s', __( 'Any', 'simple-feature-requests' ), $filter['label'] );
        ?></option>
				<?php 
        foreach ( $filter['options'] as $value => $label ) {
            ?>
					<?php 
            $url = add_query_arg( $filter['key'], $value, $base_url );
            ?>
					<option value="<?php 
            echo  esc_attr( $url ) ;
            ?>" <?php 
            selected( $value, $selected );
            ?>><?php 
            echo  $label ;
            ?></option>
				<?php 
        }
        ?>
			</select>
		</span>
		<?php 
    }
    
    /**
     * Loop item title.
     *
     * @param JCK_SFR_Feature_Request $feature_request
     */
    public static function loop_item_title( $feature_request )
    {
        
        if ( $feature_request->is_single() ) {
            ?>
			<h1 class="jck-sfr-loop-item__title"><?php 
            echo  $feature_request->post->post_title ;
            ?></h1>
			<?php 
            return;
        }
        
        ?>
		<h2 class="jck-sfr-loop-item__title">
			<a href="<?php 
        echo  get_the_permalink( $feature_request->post->ID ) ;
        ?>"><?php 
        echo  $feature_request->post->post_title ;
        ?></a>
		</h2>
		<?php 
    }
    
    /**
     * Loop item vote badge.
     *
     * @param JCK_SFR_Feature_Request $feature_request
     */
    public static function loop_item_vote_badge( $feature_request )
    {
        $votes_count = $feature_request->get_votes_count();
        $page_type = JCK_SFR_Post_Types::get_page_type();
        ?>
		<div class="jck-sfr-vote-badge jck-sfr-vote-badge--<?php 
        echo  esc_attr( $feature_request->post->ID ) ;
        ?> jck-sfr-vote-badge--<?php 
        echo  esc_attr( $page_type ) ;
        ?>">
			<div class="jck-sfr-vote-badge__count">
				<strong><?php 
        echo  $votes_count ;
        ?></strong>
				<span><?php 
        echo  _n(
            'vote',
            'votes',
            $votes_count,
            'simple-feature-requests'
        ) ;
        ?></span>
			</div>
			<button class="jck-sfr-vote-badge__increment jck-sfr-vote-button <?php 
        if ( $feature_request->has_user_voted() ) {
            echo  'jck-sfr-vote-button--voted' ;
        }
        ?>" data-jck-sfr-vote="<?php 
        echo  esc_attr( $feature_request->post->ID ) ;
        ?>"><?php 
        echo  $feature_request->get_vote_button_text() ;
        ?></button>
		</div>
		<?php 
    }
    
    /**
     * Loop item vote badge (mini).
     *
     * @param JCK_SFR_Feature_Request $feature_request
     */
    public static function loop_item_vote_badge_mini( $feature_request )
    {
        $votes_count = $feature_request->get_votes_count();
        ?>
		<div class="jck-sfr-vote-badge jck-sfr-vote-badge--mini jck-sfr-vote-badge--<?php 
        echo  esc_attr( $feature_request->post->ID ) ;
        ?>">
			<button class="jck-sfr-vote-badge__increment jck-sfr-vote-button <?php 
        if ( $feature_request->has_user_voted() ) {
            echo  'jck-sfr-vote-button--voted' ;
        }
        ?>" data-jck-sfr-vote="<?php 
        echo  esc_attr( $feature_request->post->ID ) ;
        ?>"><?php 
        echo  $feature_request->get_vote_button_text() ;
        ?></button>
			<div class="jck-sfr-vote-badge__count">
				<strong><?php 
        echo  $votes_count ;
        ?></strong>
			</div>
		</div>
		<?php 
    }
    
    /**
     * Loop item status badge.
     *
     * @param JCK_SFR_Feature_Request $feature_request
     */
    public static function loop_item_status_badge( $feature_request )
    {
        $status = $feature_request->get_status();
        if ( $status === 'publish' ) {
            return;
        }
        $label = jck_sfr_get_status_label( $status );
        if ( empty($label) ) {
            return;
        }
        $status_colors = jck_sfr_get_status_colors( $status );
        ?>
		<span class="jck-sfr-status-badges">
			<span class="jck-sfr-status-badge jck-sfr-status-badge--<?php 
        echo  esc_attr( $status ) ;
        ?>" style="background: <?php 
        echo  esc_attr( $status_colors['background'] ) ;
        ?>; color: <?php 
        echo  esc_attr( $status_colors['color'] ) ;
        ?>;">
				<?php 
        echo  $label ;
        ?>
			</span>
		</span>
		<?php 
    }
    
    /**
     * Loop item author.
     *
     * @param JCK_SFR_Feature_Request $feature_request
     */
    public static function loop_item_author( $feature_request )
    {
        if ( !JCK_SFR_Post_Types::is_type( 'single' ) ) {
            return;
        }
        ?>
		<span class="jck-sfr-author">
			<?php 
        echo  get_avatar(
            $feature_request->post->post_author,
            40,
            '',
            false,
            array(
            'width'  => 20,
            'height' => 20,
        )
        ) ;
        ?>
			<?php 
        printf( '%s %s', $feature_request->get_author_display_name(), __( 'shared this idea', 'simple-feature-requests' ) );
        ?>
		</span>
		<?php 
    }
    
    /**
     * Loop item posted date.
     *
     * @param JCK_SFR_Feature_Request $feature_request
     */
    public static function loop_item_posted_date( $feature_request )
    {
        ?>
		<span class="jck-sfr-date">
			<?php 
        echo  get_the_date( '', $feature_request->post->id ) ;
        ?>
		</span>
		<?php 
    }
    
    /**
     * Loop item comment count.
     *
     * @param JCK_SFR_Feature_Request $feature_request
     */
    public static function loop_item_comment_count( $feature_request )
    {
        if ( JCK_SFR_Post_Types::is_type( 'single' ) || !jck_sfr_comments_enabled() ) {
            return;
        }
        $comment_count = get_comments_number( $feature_request->post->ID );
        ?>
		<span class="jck-sfr-comment-count jck-sfr-u-nowrap">
			<?php 
        printf( _n(
            '%d comment',
            '%d comments',
            $comment_count,
            'simple-feature-requests'
        ), $comment_count );
        ?>
		</span>
		<?php 
    }
    
    /**
     * Display comments.
     */
    public static function comments()
    {
        if ( !JCK_SFR_Post_Types::is_type( 'single' ) || !jck_sfr_comments_enabled() ) {
            return;
        }
        
        if ( comments_open() ) {
            ?>
			<div class="jck-sfr-comments">
				<?php 
            comments_template();
            ?>
			</div>
		<?php 
        }
    
    }
    
    /**
     * Back to archive link.
     */
    public static function back_to_archive_link()
    {
        $href = apply_filters( 'jck_sfr_archive_link', JCK_SFR_Post_Types::get_archive_url() );
        ?>
		<a href="<?php 
        echo  esc_attr( $href ) ;
        ?>" class="jck-sfr-back-to-archive-link">
			<?php 
        _e( '&larr; All Feature Requests', 'simple-feature-requests' );
        ?>
		</a>
		<?php 
    }

}