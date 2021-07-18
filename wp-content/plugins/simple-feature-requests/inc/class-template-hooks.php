<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
    // Exit if accessed directly
}

/**
 * Template Hooks.
 */
class JCK_SFR_Template_Hooks
{
    /**
     * Run class.
     */
    public static function run()
    {
        $action_hooks = array(
            'jck_sfr_before_main_content'  => array( array(
            'function' => array( 'JCK_SFR_Notices', 'print_notices' ),
            'priority' => 10,
        ), array(
            'function' => array( __CLASS__, 'submission_form' ),
            'priority' => 20,
        ), array(
            'function' => array( __CLASS__, 'filters' ),
            'priority' => 30,
        ) ),
            'jck_sfr_before_single_loop'   => array( array(
            'function' => array( 'JCK_SFR_Notices', 'print_notices' ),
            'priority' => 10,
        ) ),
            'jck_sfr_loop'                 => array( array(
            'function' => array( __CLASS__, 'loop_content' ),
            'priority' => 10,
        ) ),
            'jck_sfr_loop_item_vote_badge' => array( array(
            'function' => array( 'JCK_SFR_Template_Methods', 'loop_item_vote_badge' ),
            'priority' => 10,
        ) ),
            'jck_sfr_loop_item_title'      => array( array(
            'function' => array( 'JCK_SFR_Template_Methods', 'loop_item_title' ),
            'priority' => 10,
        ) ),
            'jck_sfr_loop_item_text'       => array( array(
            'function' => array( __CLASS__, 'loop_item_text' ),
            'priority' => 10,
        ) ),
            'jck_sfr_loop_item_meta'       => array( array(
            'function' => array( 'JCK_SFR_Template_Methods', 'loop_item_status_badge' ),
            'priority' => 10,
        ), array(
            'function' => array( 'JCK_SFR_Template_Methods', 'loop_item_author' ),
            'priority' => 20,
        ), array(
            'function' => array( 'JCK_SFR_Template_Methods', 'loop_item_comment_count' ),
            'priority' => 30,
        ) ),
            'jck_sfr_loop_item_after_meta' => array( array(
            'function' => array( 'JCK_SFR_Template_Methods', 'comments' ),
            'priority' => 10,
        ), array(
            'function' => array( __CLASS__, 'disable_theme_comments' ),
            'priority' => 10,
        ) ),
            'jck_sfr_no_requests_found'    => array( array(
            'function' => array( __CLASS__, 'no_requests_found' ),
            'priority' => 10,
        ) ),
            'jck_sfr_after_main_content'   => array( array(
            'function' => array( __CLASS__, 'pagination' ),
            'priority' => 10,
        ) ),
            'jck_sfr_sidebar'              => array(
            array(
            'function' => array( __CLASS__, 'back_to_archive_link' ),
            'priority' => 10,
        ),
            array(
            'function' => array( __CLASS__, 'login' ),
            'priority' => 20,
        ),
            array(
            'function' => array( __CLASS__, 'top_requests__premium_only' ),
            'priority' => 30,
        ),
            array(
            'function' => array( __CLASS__, 'taxonomies__premium_only' ),
            'priority' => 40,
        )
        ),
            'jck_sfr_login_form'           => array( array(
            'function' => array( __CLASS__, 'login_form_fields' ),
            'priority' => 10,
        ) ),
            'jck_sfr_submission_form'      => array( array(
            'function' => array( __CLASS__, 'login_form_fields' ),
            'priority' => 20,
        ) ),
            'jck_sfr_after_columns'        => array( array(
            'function' => array( __CLASS__, 'credit' ),
            'priority' => 10,
        ) ),
        );
        foreach ( $action_hooks as $hook => $actions ) {
            foreach ( $actions as $action ) {
                $defaults = array(
                    'priority' => 10,
                    'args'     => 1,
                );
                $action = wp_parse_args( $action, $defaults );
                if ( !method_exists( $action['function'][0], $action['function'][1] ) ) {
                    continue;
                }
                add_action(
                    $hook,
                    $action['function'],
                    $action['priority'],
                    $action['args']
                );
            }
        }
    }
    
    /**
     * Include template.
     *
     * @param string $name
     * @param array  $args
     */
    public static function include_template( $name, $args = array() )
    {
        $path = sprintf( '%s%s.php', JCK_SFR_TEMPLATES_PATH, $name );
        if ( !file_exists( $path ) ) {
            return;
        }
        extract( $args );
        include $path;
    }
    
    /**
     * Submission form.
     */
    public static function submission_form( $args = array() )
    {
        self::include_template( 'archive/submission-form', $args );
    }
    
    /**
     * Filters.
     */
    public static function filters()
    {
        self::include_template( 'archive/filters' );
    }
    
    /**
     * Loop content.
     */
    public static function loop_content()
    {
        self::include_template( 'loop/content' );
    }
    
    /**
     * Roadmap loop content.
     */
    public static function roadmap_loop_content()
    {
        self::include_template( 'loop/roadmap-content' );
    }
    
    /**
     * Loop item text.
     *
     * @param JCK_SFR_Feature_Request $feature_request
     */
    public static function loop_item_text( $feature_request )
    {
        
        if ( $feature_request->is_single() ) {
            the_content();
        } else {
            the_excerpt();
        }
    
    }
    
    /**
     * No requests found.
     */
    public static function no_requests_found()
    {
        self::include_template( 'loop/no-requests-found' );
    }
    
    /**
     * Pagination.
     */
    public static function pagination()
    {
        self::include_template( 'loop/pagination' );
    }
    
    /**
     * Login.
     */
    public static function login()
    {
        self::include_template( 'sidebar/login' );
    }
    
    /**
     * Login form fields.
     */
    public static function login_form_fields()
    {
        if ( is_user_logged_in() ) {
            return;
        }
        self::include_template( 'components/login-form-fields' );
    }
    
    /**
     * Back to archive link.
     */
    public static function back_to_archive_link()
    {
        self::include_template( 'sidebar/back-to-archive-link' );
    }
    
    /**
     * Credit.
     */
    public static function credit()
    {
        $settings = JCK_SFR_Settings::get_settings();
        if ( empty($settings['general_credit_enable']) ) {
            return;
        }
        ?>
		<p class="jck-sfr-credit">
			<?php 
        _e( 'Powered by', 'simple-feature-requests' );
        ?>
			<img src="data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiIHN0YW5kYWxvbmU9Im5vIj8+PCFET0NUWVBFIHN2ZyBQVUJMSUMgIi0vL1czQy8vRFREIFNWRyAxLjEvL0VOIiAiaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkIj48c3ZnIHdpZHRoPSIxMDAlIiBoZWlnaHQ9IjEwMCUiIHZpZXdCb3g9IjAgMCAyMCAyMCIgdmVyc2lvbj0iMS4xIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB4bWw6c3BhY2U9InByZXNlcnZlIiB4bWxuczpzZXJpZj0iaHR0cDovL3d3dy5zZXJpZi5jb20vIiBzdHlsZT0iZmlsbC1ydWxlOmV2ZW5vZGQ7Y2xpcC1ydWxlOmV2ZW5vZGQ7c3Ryb2tlLWxpbmVqb2luOnJvdW5kO3N0cm9rZS1taXRlcmxpbWl0OjI7Ij48cGF0aCBkPSJNMTEuNjA3LDIuOTUzYzAuMDgsLTAuMjIzIDAuMTc0LC0wLjQ0NCAwLjI4MywtMC42NjFjMC4yNzEsLTAuNTM5IDAuNjgzLC0xLjAzOSAxLjI5NiwtMS4yMjZjMC4wMjIsLTAuMDA2IDAuMDQ0LC0wLjAxMiAwLjA2NiwtMC4wMThjMC45NDcsLTAuMjMzIDEuOTAxLDAuNDI1IDIuNTA5LDEuMDI4YzEuNTg2LDEuNTczIDIuNDc2LDMuODA5IDIuOTc2LDYuMDM2YzAuNDYxLDIuMDU2IDAuNzA1LDQuMjc3IC0wLjIwMiw2LjIxNmMtMC4wMzIsMC4wNjggLTAuMDY1LDAuMTM1IC0wLjEwMSwwLjIwMmMtMC4zNDYsMC42MzggLTAuOTQ5LDEuMjEgLTEuNzU4LDEuMTk1Yy0wLjcyMywtMC4wMTQgLTEuMzQyLC0wLjM5IC0xLjg2MSwtMC44NzFjLTEuMjEsLTAuMzEzIC0yLjc4NSwtMC41NDUgLTQuMDM3LC0wLjU0MWMtMC4wMzksMC4zNDggLTAuNDk0LDMuMDgxIC0yLjEzOCw0LjUzM2MtMC4xNjMsMC4xNDQgLTAuODExLDAuNDk1IC0wLjY3NywtMC43OTVjMC4xMzksLTEuMzM3IC0wLjU2LC0zLjA4MSAtMi4xNTQsLTMuMDA0Yy0wLjU3NiwwLjAyOCAtMS4wOTgsMC4yNDYgLTEuNDI2LDAuMzUzYzAsMCAtMC4wMjEsMC4wMDMgLTAuMDU1LDAuMDA0Yy0wLjAzNywwLjAxNyAtMC4wNzUsMC4wMjkgLTAuMTE0LDAuMDM2Yy0yLjEzOSwwLjM3IC0zLjk5NSwtMy41MiAtMy4xNTcsLTYuMTMxYzAuMTk4LC0wLjYxNyAwLjU4LC0xLjI5OSAxLjI1MiwtMS40MDdjMC4zNjUsLTAuMTg3IDEuMDg0LC0wLjI0NSAxLjY1OSwtMC40MjRjMi44MjksLTAuODc5IDUuNTc2LC0yLjM5NCA3LjYzOSwtNC41MjVabTIuMjM4LDAuNDA4Yy0wLjUyOSwxLjE4NiAtMC40MSwyLjYwMyAtMC4xODgsMy45NjljMC4zMTIsMS45MjMgMC45NTksMy44NSAyLjE4LDUuMzUzYzAuMjA0LDAuMjUyIDAuNDI3LDAuNDkgMC42ODgsMC42OGMwLDAgMC4xMDMsLTAuMjQyIDAuMTUsLTAuNDFjMC40NDcsLTEuNjA5IDAuMTc5LC0zLjM4MiAtMC4yODMsLTUuMDQ2Yy0wLjQzMiwtMS41NTcgLTEuMDU3LC0zLjExOSAtMi4xOTgsLTQuMjUyYy0wLjEzOCwtMC4xMzcgLTAuMzM2LC0wLjMyNCAtMC4zNDksLTAuMjk0WiIvPjwvc3ZnPg==" alt="" width="20" height="20">
			<a href="https://simplefeaturerequests.com/" target="_blank"><?php 
        _e( 'Simple feature Requests', 'simple-feature-requests' );
        ?></a>
		</p>
		<?php 
    }
    
    /**
     * Disable default theme comments.
     */
    public static function disable_theme_comments()
    {
        add_filter(
            'comments_open',
            function ( $open, $post_id ) {
            if ( JCK_SFR_Post_Types::$key === get_post_type( $post_id ) ) {
                return false;
            }
            return $open;
        },
            20,
            2
        );
    }

}