<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
    // Exit if accessed directly
}

/**
 * JCK_SFR_Settings.
 *
 * @class    JCK_SFR_Settings
 * @version  1.0.0
 * @category Class
 * @author   Iconic
 */
class JCK_SFR_Settings
{
    /**
     * Run.
     */
    public static function run()
    {
        add_action( 'admin_menu', array( __CLASS__, 'add_menu_items' ), 10 );
        add_action( 'admin_head', array( __CLASS__, 'menu_highlight' ) );
        add_filter( 'wpsf_menu_icon_url_jck_sfr', array( __CLASS__, 'menu_icon' ) );
        add_filter( 'wpsf_menu_position_jck_sfr', array( __CLASS__, 'menu_position' ) );
        add_filter( 'wpsf_register_settings_jck_sfr', array( __CLASS__, 'add_settings' ), 10 );
        add_filter( 'wpsf_register_settings_jck_sfr', array( __CLASS__, 'add_free_settings' ), 20 );
        add_action( 'update_option_jck_sfr_settings', array( __CLASS__, 'after_save_settings' ), 10 );
        add_action( 'add_option_jck_sfr_settings', array( __CLASS__, 'after_save_settings' ), 10 );
        add_action( 'admin_notices', array( __CLASS__, 'archive_page_notice' ) );
    }
    
    /**
     * Rename dashboard page.
     */
    public static function add_menu_items()
    {
        global  $submenu ;
        $settings_framework = JCK_SFR_Core_Settings::$settings_framework;
        add_submenu_page(
            'jck-sfr-settings',
            'Add New',
            'Add New',
            'manage_options',
            'post-new.php?post_type=cpt_feature_requests',
            null
        );
        add_submenu_page(
            'jck-sfr-settings',
            __( 'Simple Feature Requests', 'simple-feature-requests' ),
            __( 'Settings', 'simple-feature-requests' ),
            'manage_options',
            'jck-sfr-settings',
            array( $settings_framework, 'settings_page_content' )
        );
        $submenu['jck-sfr-settings'][] = array( __( 'Documentation', 'simple-feature-requests' ), 'manage_options', 'https://docs.simplefeaturerequests.com/?utm_source=JCK&utm_medium=insideplugin' );
        $submenu['jck-sfr-settings'][] = array( __( 'Support', 'simple-feature-requests' ), 'manage_options', 'https://simplefeaturerequests.com/contact/?utm_source=JCK&utm_medium=insideplugin' );
    }
    
    /**
     * Keep menu open.
     *
     * Highlights the wanted admin (sub-) menu items for the CPT.
     */
    public static function menu_highlight()
    {
        global  $parent_file, $submenu_file, $post_type ;
        if ( $post_type !== 'cpt_feature_requests' ) {
            return;
        }
        $screen = get_current_screen();
        if ( $screen->base !== 'post' ) {
            return;
        }
        $parent_file = 'edit.php?post_type=cpt_feature_requests';
        $submenu_file = 'post-new.php?post_type=cpt_feature_requests';
    }
    
    /**
     * Add menu icon.
     *
     * @param string $icon_url
     *
     * @return string
     */
    public static function menu_icon( $icon_url )
    {
        return 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiIHN0YW5kYWxvbmU9Im5vIj8+PCFET0NUWVBFIHN2ZyBQVUJMSUMgIi0vL1czQy8vRFREIFNWRyAxLjEvL0VOIiAiaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkIj48c3ZnIHdpZHRoPSIxMDAlIiBoZWlnaHQ9IjEwMCUiIHZpZXdCb3g9IjAgMCAyMCAyMCIgdmVyc2lvbj0iMS4xIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB4bWw6c3BhY2U9InByZXNlcnZlIiB4bWxuczpzZXJpZj0iaHR0cDovL3d3dy5zZXJpZi5jb20vIiBzdHlsZT0iZmlsbC1ydWxlOmV2ZW5vZGQ7Y2xpcC1ydWxlOmV2ZW5vZGQ7c3Ryb2tlLWxpbmVqb2luOnJvdW5kO3N0cm9rZS1taXRlcmxpbWl0OjI7Ij48cGF0aCBkPSJNMTEuNjA3LDIuOTUzYzAuMDgsLTAuMjIzIDAuMTc0LC0wLjQ0NCAwLjI4MywtMC42NjFjMC4yNzEsLTAuNTM5IDAuNjgzLC0xLjAzOSAxLjI5NiwtMS4yMjZjMC4wMjIsLTAuMDA2IDAuMDQ0LC0wLjAxMiAwLjA2NiwtMC4wMThjMC45NDcsLTAuMjMzIDEuOTAxLDAuNDI1IDIuNTA5LDEuMDI4YzEuNTg2LDEuNTczIDIuNDc2LDMuODA5IDIuOTc2LDYuMDM2YzAuNDYxLDIuMDU2IDAuNzA1LDQuMjc3IC0wLjIwMiw2LjIxNmMtMC4wMzIsMC4wNjggLTAuMDY1LDAuMTM1IC0wLjEwMSwwLjIwMmMtMC4zNDYsMC42MzggLTAuOTQ5LDEuMjEgLTEuNzU4LDEuMTk1Yy0wLjcyMywtMC4wMTQgLTEuMzQyLC0wLjM5IC0xLjg2MSwtMC44NzFjLTEuMjEsLTAuMzEzIC0yLjc4NSwtMC41NDUgLTQuMDM3LC0wLjU0MWMtMC4wMzksMC4zNDggLTAuNDk0LDMuMDgxIC0yLjEzOCw0LjUzM2MtMC4xNjMsMC4xNDQgLTAuODExLDAuNDk1IC0wLjY3NywtMC43OTVjMC4xMzksLTEuMzM3IC0wLjU2LC0zLjA4MSAtMi4xNTQsLTMuMDA0Yy0wLjU3NiwwLjAyOCAtMS4wOTgsMC4yNDYgLTEuNDI2LDAuMzUzYzAsMCAtMC4wMjEsMC4wMDMgLTAuMDU1LDAuMDA0Yy0wLjAzNywwLjAxNyAtMC4wNzUsMC4wMjkgLTAuMTE0LDAuMDM2Yy0yLjEzOSwwLjM3IC0zLjk5NSwtMy41MiAtMy4xNTcsLTYuMTMxYzAuMTk4LC0wLjYxNyAwLjU4LC0xLjI5OSAxLjI1MiwtMS40MDdjMC4zNjUsLTAuMTg3IDEuMDg0LC0wLjI0NSAxLjY1OSwtMC40MjRjMi44MjksLTAuODc5IDUuNTc2LC0yLjM5NCA3LjYzOSwtNC41MjVabTIuMjM4LDAuNDA4Yy0wLjUyOSwxLjE4NiAtMC40MSwyLjYwMyAtMC4xODgsMy45NjljMC4zMTIsMS45MjMgMC45NTksMy44NSAyLjE4LDUuMzUzYzAuMjA0LDAuMjUyIDAuNDI3LDAuNDkgMC42ODgsMC42OGMwLDAgMC4xMDMsLTAuMjQyIDAuMTUsLTAuNDFjMC40NDcsLTEuNjA5IDAuMTc5LC0zLjM4MiAtMC4yODMsLTUuMDQ2Yy0wLjQzMiwtMS41NTcgLTEuMDU3LC0zLjExOSAtMi4xOTgsLTQuMjUyYy0wLjEzOCwtMC4xMzcgLTAuMzM2LC0wLjMyNCAtMC4zNDksLTAuMjk0WiIgc3R5bGU9ImZpbGw6IzllYTNhODsiLz48L3N2Zz4=';
    }
    
    /**
     * Change menu position.
     *
     * @param int|null $position
     *
     * @return int|null
     */
    public static function menu_position( $position )
    {
        return 30;
    }
    
    /**
     * Get support button.
     *
     * @return string
     */
    public static function support_link()
    {
        return sprintf( '<a href="%s" class="button button-secondary" target="_blank">%s</a>', 'http://www.simplefeaturerequests.com/contact/?utm_source=JCK&utm_medium=insideplugin', __( 'Submit Ticket', 'simple-feature-requests' ) );
    }
    
    /**
     * Get documentation button.
     *
     * @return string
     */
    public static function documentation_link()
    {
        return sprintf( '<a href="%s" class="button button-secondary" target="_blank">%s</a>', 'https://docs.simplefeaturerequests.com/?utm_source=JCK&utm_medium=insideplugin', __( 'Read Documentation', 'simple-feature-requests' ) );
    }
    
    /**
     * Get settings.
     *
     * @return array|bool
     */
    public static function get_settings()
    {
        global  $simple_feature_requests_class ;
        if ( empty($simple_feature_requests_class) ) {
            return false;
        }
        $settings = $simple_feature_requests_class->settings;
        if ( empty($settings) ) {
            return false;
        }
        return $settings::$settings;
    }
    
    /**
     * Add settings.
     *
     * @param $settings
     *
     * @return mixed
     */
    public static function add_settings( $settings )
    {
        $settings['tabs'][10] = array(
            'id'    => 'general',
            'title' => __( 'General', 'simple-feature-requests' ),
        );
        $settings['sections']['general_setup'] = array(
            'tab_id'              => 'general',
            'section_id'          => 'setup',
            'section_title'       => __( 'Setup', 'simple-feature-requests' ),
            'section_description' => '',
            'section_order'       => 10,
            'fields'              => array(
            'archive_page_id' => array(
            'id'       => 'archive_page_id',
            'title'    => __( 'Archive Page', 'simple-feature-requests' ),
            'subtitle' => __( 'Select the archive page for your feature requests. Make sure you add the [simple-feature-requests] shortcode to the selected page.', 'simple-feature-requests' ),
            'type'     => 'select',
            'choices'  => array(),
            'default'  => '',
        ),
        ),
        );
        $settings['sections']['general_comments'] = array(
            'tab_id'              => 'general',
            'section_id'          => 'comments',
            'section_title'       => __( 'Comments', 'simple-feature-requests' ),
            'section_description' => '',
            'section_order'       => 20,
            'fields'              => array( array(
            'id'       => 'enable',
            'title'    => __( 'Enable Comments', 'simple-feature-requests' ),
            'subtitle' => __( 'When enabled, users will be able to comment on feature requests.', 'simple-feature-requests' ),
            'type'     => 'checkbox',
            'default'  => 1,
        ) ),
        );
        $settings['sections']['general_credit'] = array(
            'tab_id'              => 'general',
            'section_id'          => 'credit',
            'section_title'       => __( 'Credit', 'simple-feature-requests' ),
            'section_description' => '',
            'section_order'       => 40,
            'fields'              => array( array(
            'id'       => 'enable',
            'title'    => __( 'Enable Credit', 'simple-feature-requests' ),
            'subtitle' => __( 'When enabled, a "powered by" link will be displayed in the footer of the feature requests templates. Help us spread the word!', 'simple-feature-requests' ),
            'type'     => 'checkbox',
            'default'  => 1,
        ) ),
        );
        $settings['tabs'][20] = array(
            'id'    => 'notifications',
            'title' => __( 'Notifications', 'simple-feature-requests' ),
        );
        $settings['sections']['notifications_contents'] = array(
            'tab_id'              => 'notifications',
            'section_id'          => 'contents',
            'section_title'       => __( 'Email Contents', 'simple-feature-requests' ),
            'section_description' => '',
            'section_order'       => 20,
            'fields'              => array( array(
            'id'       => 'signature',
            'title'    => __( 'Signature', 'simple-feature-requests' ),
            'subtitle' => __( 'Used to sign off notification emails. You can use %site_name% and %site_url%.', 'simple-feature-requests' ),
            'type'     => 'textarea',
            'default'  => sprintf( "%s, \n%s", __( 'Thank you', 'simple-feature-requests' ), '<a href="%site_url%">%site_name%</a>' ),
        ) ),
        );
        $settings['tabs'][30] = array(
            'id'    => 'votes',
            'title' => __( 'Votes', 'simple-feature-requests' ),
        );
        $settings['sections']['votes_general'] = array(
            'tab_id'              => 'votes',
            'section_id'          => 'general',
            'section_title'       => __( 'Voting Settings', 'simple-feature-requests' ),
            'section_description' => '',
            'section_order'       => 10,
            'fields'              => array( array(
            'id'       => 'allow_own_vote_removal',
            'title'    => __( 'Allow Own Vote Removal?', 'simple-feature-requests' ),
            'subtitle' => __( 'Do you want to allow your users to remove their votes on their own requests?', 'simple-feature-requests' ),
            'type'     => 'checkbox',
            'default'  => 0,
        ) ),
        );
        return $settings;
    }
    
    /**
     * Add settings for free plugin.
     *
     * @param $settings
     *
     * @return mixed
     */
    public static function add_free_settings( $settings )
    {
        global  $simple_feature_requests_class ;
        if ( !is_admin() || $simple_feature_requests_class->freemius->can_use_premium_code() ) {
            return $settings;
        }
        $settings['sections']['general_setup']['fields']['default_status'] = array(
            'id'       => 'default_status',
            'title'    => __( 'Default Request Status', 'simple-feature-requests' ),
            'subtitle' => __( 'Set the default request status.', 'simple-feature-requests' ),
            'type'     => 'custom',
            'default'  => JCK_Simple_Feature_Requests::get_pro_button(),
        );
        $settings['sections']['general_setup']['fields']['ppp'] = array(
            'id'       => 'ppp',
            'title'    => __( 'Requests Per Page', 'simple-feature-requests' ),
            'subtitle' => __( 'How many requests to show per page in the archive.', 'simple-feature-requests' ),
            'type'     => 'custom',
            'default'  => JCK_Simple_Feature_Requests::get_pro_button(),
        );
        $settings['sections']['general_setup']['fields']['single_title_tag'] = array(
            'id'       => 'single_title_tag',
            'title'    => __( 'Single Request Title Tag', 'simple-feature-requests' ),
            'subtitle' => __( 'Tag to use for single request title.', 'simple-feature-requests' ),
            'type'     => 'select',
            'choices'  => array(
            'h1' => 'h1',
            'h2' => 'h2',
            'h3' => 'h3',
            'h4' => 'h4',
            'h5' => 'h5',
            'h6' => 'h6',
            'p'  => 'p',
        ),
            'default'  => 'h1',
        );
        $settings['sections']['general_setup']['fields']['archive_title_tag'] = array(
            'id'       => 'archive_title_tag',
            'title'    => __( 'Archive Request Title Tag', 'simple-feature-requests' ),
            'subtitle' => __( 'Tag to use for request titles in the request archive.', 'simple-feature-requests' ),
            'type'     => 'select',
            'choices'  => array(
            'h1' => 'h1',
            'h2' => 'h2',
            'h3' => 'h3',
            'h4' => 'h4',
            'h5' => 'h5',
            'h6' => 'h6',
            'p'  => 'p',
        ),
            'default'  => 'h2',
        );
        $settings['sections']['general_setup']['fields']['hide_entry_title'] = array(
            'id'       => 'hide_entry_title',
            'title'    => __( 'Hide Entry Title On Single Request View', 'simple-feature-requests' ),
            'subtitle' => __( 'Hide the default entry title output by your theme when viewing single feature requests.', 'simple-feature-requests' ),
            'type'     => 'checkbox',
            'default'  => 0,
        );
        $settings['sections']['notifications_admin'] = array(
            'tab_id'              => 'notifications',
            'section_id'          => 'admin',
            'section_title'       => __( 'Admin', 'simple-feature-requests' ),
            'section_description' => '',
            'section_order'       => 10,
            'fields'              => array( array(
            'id'       => 'emails',
            'title'    => __( 'Email Addresses', 'simple-feature-requests' ),
            'subtitle' => __( 'Set the admin email addresses for sending and receiving email notifications.', 'simple-feature-requests' ),
            'type'     => 'custom',
            'default'  => JCK_Simple_Feature_Requests::get_pro_button(),
        ) ),
        );
        $settings['sections']['notifications_events'] = array(
            'tab_id'              => 'notifications',
            'section_id'          => 'events',
            'section_title'       => __( 'Events', 'simple-feature-requests' ),
            'section_description' => '',
            'section_order'       => 10,
            'fields'              => array(
            array(
            'id'       => 'admin_upgrade',
            'title'    => __( 'New Request', 'simple-feature-requests' ),
            'subtitle' => __( 'Send a notification to the admin email address when a new request is added.', 'simple-feature-requests' ),
            'type'     => 'custom',
            'default'  => JCK_Simple_Feature_Requests::get_pro_button(),
        ),
            array(
            'id'       => 'status_change_upgrade',
            'title'    => __( 'Status Change', 'simple-feature-requests' ),
            'subtitle' => __( 'Send a notification when the request status changes.', 'simple-feature-requests' ),
            'type'     => 'custom',
            'default'  => JCK_Simple_Feature_Requests::get_pro_button(),
        ),
            array(
            'id'       => 'merge_upgrade',
            'title'    => __( 'Merge Request', 'simple-feature-requests' ),
            'subtitle' => __( 'Send a notification to the author/voters of merged requests.', 'simple-feature-requests' ),
            'type'     => 'custom',
            'default'  => JCK_Simple_Feature_Requests::get_pro_button(),
        ),
            array(
            'id'       => 'comment_upgrade',
            'title'    => __( 'Comment', 'simple-feature-requests' ),
            'subtitle' => __( 'Send a notification when a new comment is added to the request.', 'simple-feature-requests' ),
            'type'     => 'custom',
            'default'  => JCK_Simple_Feature_Requests::get_pro_button(),
        )
        ),
        );
        $settings['sections']['votes_limits'] = array(
            'tab_id'              => 'votes',
            'section_id'          => 'limits',
            'section_title'       => __( 'Voting Limits', 'simple-feature-requests' ),
            'section_description' => '',
            'section_order'       => 20,
            'fields'              => array( array(
            'id'       => 'upgrade',
            'title'    => __( 'Votes Limit', 'simple-feature-requests' ),
            'subtitle' => __( 'How many votes should each user get? Enter 0 or leave empty for no limit.', 'simple-feature-requests' ),
            'type'     => 'custom',
            'default'  => JCK_Simple_Feature_Requests::get_pro_button(),
        ) ),
        );
        return $settings;
    }
    
    /**
     * List pages on site.
     *
     * @return array
     */
    public static function get_page_options()
    {
        $return = array( __( 'Select a page...', 'iconic-ww' ) );
        $pages = get_pages( array(
            'post_status' => 'publish,private,draft',
        ) );
        if ( empty($pages) ) {
            return $return;
        }
        foreach ( $pages as $page ) {
            $path = get_page_uri( $page->ID );
            $return[$page->ID] = sprintf( '%s (/%s)', $page->post_title, $path );
        }
        return $return;
    }
    
    /**
     * Populate status slug tracker if empty.
     */
    public static function populate_slug_tracker( $statuses )
    {
        if ( empty($statuses) ) {
            return false;
        }
        $statuses = maybe_unserialize( $statuses );
        if ( !is_array( $statuses ) ) {
            return false;
        }
        $tracker = array();
        foreach ( $statuses as $key => $status ) {
            $tracker[$status['row_id']] = jck_sfr_get_status_slug( $status['status_title'] );
        }
        $tracker = array_filter( $tracker );
        update_option( 'jck_sfr_status_slug_tracker', $tracker );
        return $tracker;
    }
    
    public static function compare_slug_tracker( $statuses, $tracker )
    {
        if ( empty($statuses) ) {
            return false;
        }
        $statuses = maybe_unserialize( $statuses );
        if ( !is_array( $statuses ) ) {
            return false;
        }
        if ( empty($tracker) ) {
            return false;
        }
        $tracker = maybe_unserialize( $tracker );
        if ( !is_array( $tracker ) ) {
            return false;
        }
        $update_tracker = false;
        // Convert to matching array for comparison.
        $existing = array();
        foreach ( $statuses as $status ) {
            $existing[$status['row_id']] = jck_sfr_get_status_slug( $status['status_title'] );
        }
        $leftovers = $tracker;
        foreach ( $existing as $row_id => $slug ) {
            // An existing status needs to be added to the tracker
            
            if ( !array_key_exists( $row_id, $tracker ) ) {
                $tracker[$row_id] = $slug;
                $update_tracker = true;
            } else {
                
                if ( $slug != $tracker[$row_id] ) {
                    // It exists, but the slug changed. Update posts.
                    jck_sfr_fix_post_statuses( $tracker[$row_id], $slug );
                    $tracker[$row_id] = $slug;
                    $update_tracker = true;
                }
            
            }
            
            unset( $leftovers[$row_id] );
        }
        // Change posts with deleted status to pending.
        if ( !empty($leftovers) ) {
            foreach ( $leftovers as $leftover_row_id => $left_over_slug ) {
                jck_sfr_fix_post_statuses( $left_over_slug, apply_filters( 'jck_sfr_default_missing_status', 'pending' ) );
                unset( $tracker[$leftover_row_id] );
                $update_tracker = true;
            }
        }
        if ( $update_tracker === true ) {
            update_option( 'jck_sfr_status_slug_tracker', $tracker );
        }
    }
    
    /**
     * After save settings.
     */
    public static function after_save_settings()
    {
        $slug_tracker = get_option( 'jck_sfr_status_slug_tracker', array() );
        $custom_statuses = jck_sfr_get_custom_statuses();
        
        if ( empty($slug_tracker) ) {
            $slug_tracker = self::populate_slug_tracker( $custom_statuses );
        } else {
            $slug_tracker = self::compare_slug_tracker( $custom_statuses, $slug_tracker );
        }
        
        JCK_SFR_Post_Types::flush_permalinks();
    }
    
    /**
     * Add notice if no archive page is set.
     */
    public static function archive_page_notice()
    {
        $archive_url = JCK_SFR_Post_Types::get_archive_url();
        if ( !empty($archive_url) ) {
            return;
        }
        ?>
		<div class="notice notice-warning is-dismissible">
			<p>
				<strong><?php 
        _e( 'Important!', 'simple-feature-requests' );
        ?></strong>
				<?php 
        printf( __( 'You need to <a href="%s">set an archive page</a> for Simple Feature Requests, under the "General" tab.', 'simple-feature-requests' ), admin_url( 'admin.php?page=jck-sfr-settings' ) );
        ?>
			</p>
		</div>
		<?php 
    }

}