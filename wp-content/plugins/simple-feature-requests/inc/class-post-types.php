<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
    // Exit if accessed directly
}

/**
 * Setup post types.
 */
class JCK_SFR_Post_Types
{
    /**
     * Post type key.
     *
     * @var string
     */
    public static  $key = 'cpt_feature_requests' ;
    /**
     * Query var.
     *
     * @var string
     */
    public static  $query_var = 'jck_sfr_slug' ;
    /**
     * Archive page id.
     *
     * @var int
     */
    public static  $archive_page_id = null ;
    /**
     * Run class.
     *
     * @param null|JCK_SFR_Core_Settings $settings
     */
    public static function run()
    {
        add_action( 'init', array( __CLASS__, 'add_post_types' ) );
        add_action( 'init', array( __CLASS__, 'add_rewrite_rule' ) );
        add_action( 'template_redirect', array( __CLASS__, 'add_pending_notice' ) );
        add_filter( 'query_vars', array( __CLASS__, 'add_query_var' ) );
        add_filter( 'manage_cpt_feature_requests_posts_columns', array( __CLASS__, 'admin_columns' ), 1000 );
        add_action(
            'manage_cpt_feature_requests_posts_custom_column',
            array( __CLASS__, 'admin_columns_content' ),
            10,
            2
        );
        add_filter( 'manage_edit-cpt_feature_requests_sortable_columns', array( __CLASS__, 'admin_sortable_columns' ) );
        add_action( 'pre_get_posts', array( __CLASS__, 'admin_columns_orderby' ) );
        add_action( 'add_meta_boxes', array( __CLASS__, 'add_meta_boxes' ) );
        add_filter(
            'post_type_link',
            array( __CLASS__, 'post_type_link' ),
            10,
            4
        );
        add_filter( 'body_class', array( __CLASS__, 'body_class' ) );
        
        if ( !is_admin() ) {
            add_action( 'template_redirect', array( __CLASS__, 'redirect_pending_request' ) );
            add_filter(
                'the_posts',
                array( __CLASS__, 'the_posts' ),
                1,
                2
            );
            add_filter(
                'the_post',
                array( __CLASS__, 'the_post' ),
                1,
                2
            );
            add_action( 'admin_bar_menu', array( __CLASS__, 'admin_bar_menu_item' ), 2000 );
            add_filter(
                'get_post_metadata',
                array( __CLASS__, 'set_single_page_template' ),
                10,
                4
            );
        }
        
        if ( is_admin() ) {
            add_filter(
                'register_post_type_args',
                array( __CLASS__, 'make_post_type_viewable' ),
                99,
                2
            );
        }
    }
    
    /**
     * Get archive page ID.
     *
     * @return bool|int
     */
    public static function get_archive_page_id()
    {
        static  $archive_page_id = null ;
        if ( !is_null( $archive_page_id ) ) {
            return $archive_page_id;
        }
        global  $simple_feature_requests_class ;
        
        if ( empty($simple_feature_requests_class) ) {
            $archive_page_id = null;
        } else {
            $archive_page_id = absint( $simple_feature_requests_class->settings->get_setting( 'general_setup_archive_page_id' ) );
        }
        
        $archive_page_id = ( empty($archive_page_id) ? null : $archive_page_id );
        $archive_page_id = apply_filters( 'jck_sfr_archive_page_id', $archive_page_id );
        return $archive_page_id;
    }
    
    /**
     * Add post types.
     */
    public static function add_post_types()
    {
        self::add( array(
            'plural'       => __( 'Feature Requests', 'simple-feature-requests' ),
            'singular'     => __( 'Feature Request', 'simple-feature-requests' ),
            'menu_name'    => __( 'All Requests', 'simple-feature-requests' ),
            'key'          => self::$key,
            'show_in_rest' => true,
            'supports'     => array(
            'title',
            'editor',
            'comments',
            'author'
        ),
            'menu_icon'    => 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiIHN0YW5kYWxvbmU9Im5vIj8+PCFET0NUWVBFIHN2ZyBQVUJMSUMgIi0vL1czQy8vRFREIFNWRyAxLjEvL0VOIiAiaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkIj48c3ZnIHdpZHRoPSIxMDAlIiBoZWlnaHQ9IjEwMCUiIHZpZXdCb3g9IjAgMCAyMCAyMCIgdmVyc2lvbj0iMS4xIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB4bWw6c3BhY2U9InByZXNlcnZlIiB4bWxuczpzZXJpZj0iaHR0cDovL3d3dy5zZXJpZi5jb20vIiBzdHlsZT0iZmlsbC1ydWxlOmV2ZW5vZGQ7Y2xpcC1ydWxlOmV2ZW5vZGQ7c3Ryb2tlLWxpbmVqb2luOnJvdW5kO3N0cm9rZS1taXRlcmxpbWl0OjI7Ij48cGF0aCBkPSJNMTEuNjA3LDIuOTUzYzAuMDgsLTAuMjIzIDAuMTc0LC0wLjQ0NCAwLjI4MywtMC42NjFjMC4yNzEsLTAuNTM5IDAuNjgzLC0xLjAzOSAxLjI5NiwtMS4yMjZjMC4wMjIsLTAuMDA2IDAuMDQ0LC0wLjAxMiAwLjA2NiwtMC4wMThjMC45NDcsLTAuMjMzIDEuOTAxLDAuNDI1IDIuNTA5LDEuMDI4YzEuNTg2LDEuNTczIDIuNDc2LDMuODA5IDIuOTc2LDYuMDM2YzAuNDYxLDIuMDU2IDAuNzA1LDQuMjc3IC0wLjIwMiw2LjIxNmMtMC4wMzIsMC4wNjggLTAuMDY1LDAuMTM1IC0wLjEwMSwwLjIwMmMtMC4zNDYsMC42MzggLTAuOTQ5LDEuMjEgLTEuNzU4LDEuMTk1Yy0wLjcyMywtMC4wMTQgLTEuMzQyLC0wLjM5IC0xLjg2MSwtMC44NzFjLTEuMjEsLTAuMzEzIC0yLjc4NSwtMC41NDUgLTQuMDM3LC0wLjU0MWMtMC4wMzksMC4zNDggLTAuNDk0LDMuMDgxIC0yLjEzOCw0LjUzM2MtMC4xNjMsMC4xNDQgLTAuODExLDAuNDk1IC0wLjY3NywtMC43OTVjMC4xMzksLTEuMzM3IC0wLjU2LC0zLjA4MSAtMi4xNTQsLTMuMDA0Yy0wLjU3NiwwLjAyOCAtMS4wOTgsMC4yNDYgLTEuNDI2LDAuMzUzYzAsMCAtMC4wMjEsMC4wMDMgLTAuMDU1LDAuMDA0Yy0wLjAzNywwLjAxNyAtMC4wNzUsMC4wMjkgLTAuMTE0LDAuMDM2Yy0yLjEzOSwwLjM3IC0zLjk5NSwtMy41MiAtMy4xNTcsLTYuMTMxYzAuMTk4LC0wLjYxNyAwLjU4LC0xLjI5OSAxLjI1MiwtMS40MDdjMC4zNjUsLTAuMTg3IDEuMDg0LC0wLjI0NSAxLjY1OSwtMC40MjRjMi44MjksLTAuODc5IDUuNTc2LC0yLjM5NCA3LjYzOSwtNC41MjVabTIuMjM4LDAuNDA4Yy0wLjUyOSwxLjE4NiAtMC40MSwyLjYwMyAtMC4xODgsMy45NjljMC4zMTIsMS45MjMgMC45NTksMy44NSAyLjE4LDUuMzUzYzAuMjA0LDAuMjUyIDAuNDI3LDAuNDkgMC42ODgsMC42OGMwLDAgMC4xMDMsLTAuMjQyIDAuMTUsLTAuNDFjMC40NDcsLTEuNjA5IDAuMTc5LC0zLjM4MiAtMC4yODMsLTUuMDQ2Yy0wLjQzMiwtMS41NTcgLTEuMDU3LC0zLjExOSAtMi4xOTgsLTQuMjUyYy0wLjEzOCwtMC4xMzcgLTAuMzM2LC0wLjMyNCAtMC4zNDksLTAuMjk0WiIgc3R5bGU9ImZpbGw6IzllYTNhODsiLz48L3N2Zz4=',
            'show_in_menu' => 'jck-sfr-settings',
        ) );
    }
    
    /**
     * Method: Add
     *
     * @param array $options
     *
     * @since 1.0.0
     *
     */
    public static function add( $options )
    {
        $defaults = array(
            "plural"              => "",
            "singular"            => "",
            "key"                 => false,
            "rewrite_slug"        => false,
            "rewrite_with_front"  => false,
            "rewrite_feeds"       => true,
            "rewrite_pages"       => true,
            "menu_icon"           => "dashicons-admin-post",
            'hierarchical'        => false,
            'supports'            => array( 'title' ),
            'public'              => false,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'show_in_nav_menus'   => true,
            'show_in_rest'        => false,
            'publicly_queryable'  => true,
            'exclude_from_search' => true,
            'has_archive'         => false,
            'query_var'           => true,
            'can_export'          => true,
            'capability_type'     => 'post',
            'menu_name'           => false,
        );
        $options = wp_parse_args( $options, $defaults );
        if ( empty($options['key']) ) {
            return;
        }
        $labels = array(
            'name'               => $options['plural'],
            'singular_name'      => $options['singular'],
            'add_new'            => _x( 'Add New', 'iconic-advanced-layered-nav' ),
            'add_new_item'       => _x( sprintf( 'Add New %s', $options['singular'] ), 'iconic-advanced-layered-nav' ),
            'edit_item'          => _x( sprintf( 'Edit %s', $options['singular'] ), 'iconic-advanced-layered-nav' ),
            'new_item'           => _x( sprintf( 'New %s', $options['singular'] ), 'iconic-advanced-layered-nav' ),
            'view_item'          => _x( sprintf( 'View %s', $options['singular'] ), 'iconic-advanced-layered-nav' ),
            'search_items'       => _x( sprintf( 'Search %s', $options['plural'] ), 'iconic-advanced-layered-nav' ),
            'not_found'          => _x( sprintf( 'No %s found', strtolower( $options['plural'] ) ), 'iconic-advanced-layered-nav' ),
            'not_found_in_trash' => _x( sprintf( 'No %s found in Trash', strtolower( $options['plural'] ) ), 'iconic-advanced-layered-nav' ),
            'parent_item_colon'  => _x( sprintf( 'Parent %s:', $options['singular'] ), 'iconic-advanced-layered-nav' ),
            'menu_name'          => ( $options['menu_name'] ? $options['menu_name'] : $options['plural'] ),
        );
        $args = array(
            'labels'              => $labels,
            'hierarchical'        => $options['hierarchical'],
            'supports'            => $options['supports'],
            'public'              => $options['public'],
            'show_ui'             => $options['show_ui'],
            'show_in_menu'        => $options['show_in_menu'],
            'show_in_rest'        => $options['show_in_rest'],
            'menu_icon'           => $options['menu_icon'],
            'show_in_nav_menus'   => $options['show_in_nav_menus'],
            'publicly_queryable'  => $options['publicly_queryable'],
            'exclude_from_search' => $options['exclude_from_search'],
            'has_archive'         => $options['has_archive'],
            'query_var'           => $options['query_var'],
            'can_export'          => $options['can_export'],
            'capability_type'     => $options['capability_type'],
            'rewrite'             => false,
        );
        if ( $options['rewrite_slug'] ) {
            $args['rewrite'] = array(
                "slug"       => $options['rewrite_slug'],
                "with_front" => $options['rewrite_with_front'],
                "feeds"      => $options['rewrite_feeds'],
                "pages"      => $options['rewrite_pages'],
            );
        }
        register_post_type( $options['key'], $args );
        
        if ( get_option( 'jck_sfr_flush_rewrite_rules_flag' ) ) {
            flush_rewrite_rules();
            delete_option( 'jck_sfr_flush_rewrite_rules_flag' );
        }
    
    }
    
    /**
     * Add rewrite rule for requests.
     */
    public static function add_rewrite_rule()
    {
        $archive_page_id = self::get_archive_page_id();
        if ( empty($archive_page_id) ) {
            return;
        }
        $archive_slug = self::get_archive_slug();
        add_rewrite_rule( '^' . $archive_slug . '/page/([0-9]{1,})/?', 'index.php?page_id=' . self::get_archive_page_id() . '&paged=$matches[1]', 'top' );
        add_rewrite_rule( '^' . $archive_slug . '/?(?!page$)([^/]*)/?', 'index.php?page_id=' . self::get_archive_page_id() . '&' . self::$query_var . '=$matches[1]', 'top' );
    }
    
    /**
     * Add query var for rewrite rule.
     *
     * @param array $vars Array of query vars.
     *
     * @return array
     */
    public static function add_query_var( $vars )
    {
        $vars[] = self::$query_var;
        return $vars;
    }
    
    /**
     * Get current request slug.
     *
     * @return mixed
     */
    public static function get_current_request_slug()
    {
        global  $wp_query ;
        if ( !$wp_query ) {
            return false;
        }
        return get_query_var( self::$query_var );
    }
    
    /**
     * Get current request.
     *
     * @return bool|JCK_SFR_Feature_Request
     */
    public static function get_current_request()
    {
        static  $request = null ;
        if ( $request ) {
            return $request;
        }
        $slug = self::get_current_request_slug();
        if ( empty($slug) ) {
            return false;
        }
        $request_post = get_page_by_path( $slug, OBJECT, self::$key );
        if ( !$request_post ) {
            return false;
        }
        $request = new JCK_SFR_Feature_Request( $request_post );
        return $request;
    }
    
    /**
     * Get current request query.
     *
     * @return bool|WP_Query
     */
    public static function get_current_request_query()
    {
        $slug = self::get_current_request_slug();
        if ( empty($slug) ) {
            return false;
        }
        $args = apply_filters( 'jck_sfr_current_request_args', array(
            'post_type'        => self::$key,
            'posts_per_page'   => 1,
            'name'             => $slug,
            'suppress_filters' => true,
        ) );
        return new WP_Query( $args );
    }
    
    /**
     * Check if the page is a certain type.
     *
     * @param $type
     *
     * @return bool
     */
    public static function is_type( $type )
    {
        return self::get_page_type() === $type;
    }
    
    /**
     * Get request by the slug.
     *
     * @param string $slug
     *
     * @return array|bool|WP_Post|null
     */
    public static function get_request_by_slug( $slug )
    {
        static  $requests = array() ;
        if ( isset( $requests[$slug] ) ) {
            return $requests[$slug];
        }
        $requests[$slug] = get_page_by_path( $slug, OBJECT, self::$key );
        return ( empty($requests[$slug]) ? false : $requests[$slug] );
    }
    
    /**
     * Get page type.
     *
     * @return string
     */
    public static function get_page_type()
    {
        global  $jck_sfr_page_type ;
        if ( !empty($jck_sfr_page_type) ) {
            return (string) $jck_sfr_page_type;
        }
        $request_slug = self::get_current_request_slug();
        
        if ( empty($request_slug) || 'page' === $request_slug ) {
            $type = 'archive';
            return $type;
        }
        
        $request = self::get_request_by_slug( $request_slug );
        $type = ( !$request ? '404' : 'single' );
        return $type;
    }
    
    /**
     * Get archive post type.
     *
     * @return string|bool
     */
    public static function get_archive_post_type()
    {
        if ( !is_archive() ) {
            return false;
        }
        $queried_object = get_queried_object();
        if ( !isset( $queried_object->name ) ) {
            return false;
        }
        return $queried_object->name;
    }
    
    /**
     * Modify admin columns.
     *
     * @param array $columns
     *
     * @return array
     */
    public static function admin_columns( $columns )
    {
        foreach ( $columns as $key => $column ) {
            if ( strpos( $key, 'wpseo-' ) !== 0 ) {
                continue;
            }
            unset( $columns[$key] );
        }
        $date = $columns['date'];
        $comments = $columns['comments'];
        unset( $columns['post_type'], $columns['date'], $columns['comments'] );
        $columns['author'] = __( 'Author', 'simple-feature-requests' );
        $columns['status'] = __( 'Status', 'simple-feature-requests' );
        $columns['votes'] = __( 'Votes', 'simple-feature-requests' );
        $columns['comments'] = $comments;
        $columns['date'] = $date;
        return $columns;
    }
    
    /**
     * Add custom column content.
     *
     * @param string $column
     * @param int    $post_id
     */
    public static function admin_columns_content( $column, $post_id )
    {
        if ( in_array( $column, array(
            'cb',
            'title',
            'author',
            'date'
        ) ) ) {
            return;
        }
        $feature_request = new JCK_SFR_Feature_Request( $post_id );
        
        if ( $column === 'status' ) {
            echo  self::get_inline_status_badge( $feature_request ) ;
        } elseif ( $column === 'votes' ) {
            $votes_count = $feature_request->get_votes_count();
            $votes_count_text = sprintf( '%d %s', $votes_count, _n(
                'Vote',
                'Votes',
                $votes_count,
                'simple-feature-requests'
            ) );
            echo  apply_filters( 'jck_sfr_votes_count_column', $votes_count_text, $feature_request ) ;
        }
    
    }
    
    /**
     * Set sortable admin columns.
     *
     * @param array $columns
     *
     * @return array
     */
    public static function admin_sortable_columns( $columns )
    {
        $columns['votes'] = 'votes';
        return $columns;
    }
    
    /**
     * Set orderby for admin columns.
     *
     * @param WP_Query $query
     */
    public static function admin_columns_orderby( $query )
    {
        if ( !is_admin() || !$query->is_main_query() ) {
            return;
        }
        
        if ( $query->get( 'orderby' ) === 'votes' ) {
            $query->set( 'orderby', 'meta_value' );
            $query->set( 'meta_key', 'jck_sfr_votes' );
            $query->set( 'meta_type', 'numeric' );
        }
    
    }
    
    /**
     * Add meta boxes.
     */
    public static function add_meta_boxes()
    {
        global  $simple_feature_requests_class ;
        if ( !$simple_feature_requests_class->freemius->can_use_premium_code() ) {
            add_meta_box(
                'jck-sfr-taxonomies',
                __( 'Categories', 'simple-feature-requests' ),
                array( __CLASS__, 'categories_meta_box' ),
                self::$key,
                'side',
                'default'
            );
        }
        add_meta_box(
            'jck-sfr-meta',
            esc_html__( 'Information', 'simple-feature-requests' ),
            array( __CLASS__, 'information_meta_box' ),
            'cpt_feature_requests',
            'side',
            'default'
        );
    }
    
    /**
     * Display categories meta box.
     */
    public static function categories_meta_box()
    {
        global  $post ;
        if ( !$post ) {
            return;
        }
        echo  JCK_Simple_Feature_Requests::get_pro_button() ;
    }
    
    /**
     * Display request meta box.
     */
    public static function information_meta_box()
    {
        global  $post ;
        if ( !$post ) {
            return;
        }
        $feature_request = new JCK_SFR_Feature_Request( $post );
        $author = get_userdata( $post->post_author );
        $author_url = $url = add_query_arg( array(
            'author'    => $author->ID,
            'post_type' => 'cpt_feature_requests',
        ), 'edit.php' );
        $meta = apply_filters( 'jck_sfr_meta_information', array(
            'status' => array(
            'label'   => sprintf( '<label for="jck_sfr_status">%s</label>', __( 'Status', 'simple-feature-requests' ) ),
            'content' => self::get_meta_content_status_select( $feature_request ),
        ),
            'votes'  => array(
            'label'   => __( 'Votes', 'simple-feature-requests' ),
            'content' => $feature_request->get_votes_count(),
        ),
            'author' => array(
            'label'   => __( 'Author', 'simple-feature-requests' ),
            'content' => sprintf( '<a href="%s">%s</a>', $author_url, $author->user_login ),
        ),
        ), $feature_request );
        if ( empty($meta) ) {
            return;
        }
        ?>
		<style>
			.jck-sfr-meta-table {
				border: none !important;
			}

			.jck-sfr-meta-table th,
			.jck-sfr-meta-table td {
				padding-left: 0;
			}

			.jck-sfr-meta-table th {
				width: 60px;
			}
		</style>
		<table class="jck-sfr-meta-table widefat fixed">
			<?php 
        foreach ( $meta as $key => $meta_content ) {
            ?>
				<tr class="jck-sfr-meta-table__row jck-sfr-meta-table__row--<?php 
            echo  esc_attr( $key ) ;
            ?>">
					<th><?php 
            echo  $meta_content['label'] ;
            ?></th>
					<td><?php 
            echo  $meta_content['content'] ;
            ?></td>
				</tr>
			<?php 
        }
        ?>
		</table>
		<?php 
    }
    
    /**
     * Get meta content for status selection.
     *
     * @param $feature_request
     *
     * @return false|string
     */
    public static function get_meta_content_status_select( $feature_request )
    {
        $statuses = jck_sfr_get_statuses();
        $status = $feature_request->get_status();
        ob_start();
        ?>
		<select id="jck_sfr_status" name="jck_sfr_status">
			<?php 
        foreach ( $statuses as $key => $label ) {
            ?>
				<option value="<?php 
            echo  esc_attr( $key ) ;
            ?>" <?php 
            selected( $key, $status );
            ?>><?php 
            echo  $label ;
            ?></option>
			<?php 
        }
        ?>
		</select>
		<?php 
        return ob_get_clean();
    }
    
    /**
     * @param JCK_SFR_Feature_Request $feature_request
     *
     * @return string
     */
    public static function get_inline_status_badge( $feature_request )
    {
        $status = $feature_request->get_status();
        $status_label = jck_sfr_get_status_label( $status );
        $status_colors = jck_sfr_get_status_colors( $status );
        return sprintf(
            '<span style="
				background-color: %s;
				height: 24px;
				line-height: 24px;
				text-transform: uppercase;
				font-size: 11px;
				padding: 0 8px;
				display: inline-block;
				vertical-align: middle;
				letter-spacing: .5px;
				font-family: Arial,sans-serif;
				border-radius: 3px;
				color: %s;
            ">%s</span>',
            $status_colors['background'],
            $status_colors['color'],
            $status_label
        );
    }
    
    /**
     * Redirect to archive if request is pending and
     * you're not the author.
     */
    public static function redirect_pending_request()
    {
        global  $post ;
        // If the post object is empty.
        if ( !self::is_type( 'single' ) ) {
            return;
        }
        $request = self::get_current_request();
        // If this isn't a feature request.
        if ( !$request ) {
            return;
        }
        $user_id = get_current_user_id();
        $author_id = absint( $request->post->post_author );
        // If you are the author of this request, or an admin.
        if ( $user_id === $author_id || current_user_can( 'administrator' ) ) {
            return;
        }
        $status = $request->get_status();
        if ( $status !== 'pending' ) {
            return;
        }
        $archive_url = self::get_archive_url();
        wp_safe_redirect( $archive_url );
        exit;
    }
    
    /**
     * Get archive URL.
     *
     * @return string
     */
    public static function get_archive_url()
    {
        $archive_slug = self::get_archive_slug();
        if ( empty($archive_slug) ) {
            return '';
        }
        return trailingslashit( home_url( $archive_slug ) );
    }
    
    /**
     * Get archive slug.
     *
     * @return string
     */
    public static function get_archive_slug()
    {
        static  $archive_slug = null ;
        if ( $archive_slug ) {
            return $archive_slug;
        }
        $archive_page_id = self::get_archive_page_id();
        $archive_slug = get_page_uri( $archive_page_id );
        return $archive_slug;
    }
    
    /**
     * Modify permalink for requests.
     *
     * @param $link
     * @param $post
     * @param $leavename
     * @param $sample
     *
     * @return mixed
     */
    public static function post_type_link(
        $link,
        $post,
        $leavename,
        $sample
    )
    {
        if ( self::$key !== $post->post_type ) {
            return $link;
        }
        return self::get_archive_url() . trailingslashit( $post->post_name );
    }
    
    /**
     * Modify post object on single request page.
     *
     * @param array    $the_posts
     * @param WP_Query $wp_query
     *
     * @return mixed
     */
    public static function the_posts( $the_posts, $wp_query )
    {
        if ( empty($wp_query->query_vars[self::$query_var]) ) {
            return $the_posts;
        }
        $request = self::get_current_request();
        $the_posts[0]->post_title = $request->post->post_title;
        $the_posts[0]->post_excerpt = $request->post->post_excerpt;
        $the_posts[0]->post_name = $request->post->post_name;
        $the_posts[0]->page_id = $the_posts[0]->ID;
        $the_posts[0]->ID = $request->post->ID;
        $the_posts[0]->post_parent = self::get_archive_page_id();
        return $the_posts;
    }
    
    /**
     * Set post type of archive page.
     *
     * @param WP_Post  $the_post
     * @param WP_Query $wp_query
     *
     * @return mixed
     */
    public static function the_post( $the_post, $wp_query )
    {
        $archive_id = self::get_archive_page_id();
        if ( empty($the_post) || $the_post->ID !== $archive_id ) {
            return $the_post;
        }
        $the_post->post_type = self::$key;
        return $the_post;
    }
    
    /**
     * Get posts per page.
     *
     * @return int
     */
    public static function get_posts_per_page()
    {
        $settings = JCK_SFR_Settings::get_settings();
        if ( empty($settings) || empty($settings['general_setup_ppp']) ) {
            return apply_filters( 'jck_sfr_posts_per_page', 10 );
        }
        return apply_filters( 'jck_sfr_posts_per_page', $settings['general_setup_ppp'] );
    }
    
    /**
     * Get single request title tag
     * 
     * @return string
     */
    public static function get_single_title_tag()
    {
        $settings = JCK_SFR_Settings::get_settings();
        if ( empty($settings) || empty($settings['general_setup_single_title_tag']) ) {
            return apply_filters( 'jck_sfr_single_title_tag', 'h1' );
        }
        return apply_filters( 'jck_sfr_single_title_tag', $settings['general_setup_single_title_tag'] );
    }
    
    /**
     * Get archive request title tag
     * 
     * @return string
     */
    public static function get_archive_title_tag()
    {
        $settings = JCK_SFR_Settings::get_settings();
        if ( empty($settings) || empty($settings['general_setup_archive_title_tag']) ) {
            return apply_filters( 'jck_sfr_archive_title_tag', 'h1' );
        }
        return apply_filters( 'jck_sfr_archive_title_tag', $settings['general_setup_archive_title_tag'] );
    }
    
    /**
     * Check if we should hide default theme entry title on single request view
     * 
     * @return bool
     */
    public static function hide_entry_title()
    {
        $settings = JCK_SFR_Settings::get_settings();
        if ( empty($settings) || empty($settings['general_setup_hide_entry_title']) ) {
            return apply_filters( 'jck_sfr_hide_entry_title', 0 );
        }
        return apply_filters( 'jck_sfr_hide_entry_title', $settings['general_setup_hide_entry_title'] );
    }
    
    /**
     * Flush permalinks.
     */
    public static function flush_permalinks()
    {
        // Flush permalinks.
        if ( !get_option( 'jck_sfr_flush_rewrite_rules_flag' ) ) {
            add_option( 'jck_sfr_flush_rewrite_rules_flag', true );
        }
    }
    
    /**
     * Add admin bar menu items.
     */
    public static function admin_bar_menu_item()
    {
        global  $wp_admin_bar ;
        $menu_id = 'jck-sfr';
        $wp_admin_bar->add_menu( array(
            'id'    => $menu_id,
            'title' => __( apply_filters( 'jck_sfr_plural_request_name', 'Requests', true ), 'simple-feature-requests' ),
            'href'  => '',
        ) );
        if ( self::is_type( 'single' ) ) {
            $wp_admin_bar->add_menu( array(
                'parent' => $menu_id,
                'title'  => __( 'Edit ' . apply_filters( 'jck_sfr_single_request_name', 'Feature Request', true ), 'simple-feature-requests' ),
                'id'     => 'jck-sfr-edit',
                'href'   => get_edit_post_link(),
            ) );
        }
        $wp_admin_bar->add_menu( array(
            'parent' => $menu_id,
            'title'  => __( 'Manage ' . apply_filters( 'jck_sfr_plural_request_name', 'Feature Requests', true ), 'simple-feature-requests' ),
            'id'     => 'jck-sfr-manage',
            'href'   => admin_url( 'edit.php?post_type=cpt_feature_requests' ),
        ) );
    }
    
    /**
     * Get main requests menu name with count.
     *
     * @return string
     */
    public static function get_menu_title()
    {
        $title = __( apply_filters( 'jck_sfr_plural_request_name', 'Requests', true ), 'simple-feature-requests' );
        $pending = JCK_SFR_Query::count_pending_requests();
        if ( empty($pending) ) {
            return $title;
        }
        return sprintf(
            '%s <span class="update-plugins count-%d"><span class="plugin-count">%d</span></span>',
            $title,
            esc_attr( $pending ),
            $pending
        );
    }
    
    /**
     * Set single page template to same as archive page.
     *
     * @param $metadata
     * @param $object_id
     * @param $meta_key
     * @param $single
     *
     * @return mixed
     */
    public static function set_single_page_template(
        $metadata,
        $object_id,
        $meta_key,
        $single
    )
    {
        if ( !self::is_type( 'single' ) || '_wp_page_template' !== $meta_key ) {
            return $metadata;
        }
        $archive_page_id = self::get_archive_page_id();
        if ( $archive_page_id === $object_id ) {
            return $metadata;
        }
        return get_post_meta( $archive_page_id, '_wp_page_template', true );
    }
    
    /**
     * Make sure "View Post" link is present in the admin area.
     *
     * @param array  $args
     * @param string $post_type
     *
     * @return array
     */
    public static function make_post_type_viewable( $args, $post_type )
    {
        if ( self::$key !== $post_type || !is_array( $args ) ) {
            return $args;
        }
        $args['public'] = true;
        return $args;
    }
    
    /**
     * Add body classes.
     *
     * @param array $classes
     *
     * @return array
     */
    public static function body_class( $classes )
    {
        global  $post ;
        if ( !$post || self::$key !== $post->post_type && strpos( $post->post_content, '[simple-feature-requests]' ) === false ) {
            return $classes;
        }
        $classes[] = 'jck-sfr';
        $classes[] = 'jck-sfr--' . self::get_page_type();
        return $classes;
    }
    
    /**
     * Add a notice to pending requests.
     *
     * @throws Exception
     */
    public static function add_pending_notice()
    {
        global  $post ;
        if ( !self::is_type( 'single' ) ) {
            return;
        }
        $feature_request = new JCK_SFR_Feature_Request( $post->ID );
        $status = $feature_request->get_status();
        if ( 'pending' !== $status ) {
            return;
        }
        $status_description = jck_sfr_get_status_description( $status );
        if ( !$status_description ) {
            return;
        }
        $notices = JCK_SFR_Notices::instance();
        $notices->add( $status_description, 'error' );
    }
    
    /**
     * Attachments metabox
     */
    public static function attachments_meta_box( $post )
    {
        $attachment_ids = array_filter( array_map( 'intval', (array) get_post_meta( $post->ID, '_attachments', true ) ) );
        ?>
		<div class="jck-sfr-attachments-container">
			<ul class="jck-sfr-attachments">
				<?php 
        foreach ( $attachment_ids as $attachment_id ) {
            ?>
				<li class="jck-sfr-attachment" data-attachment_id="<?php 
            echo  $attachment_id ;
            ?>">
					<?php 
            echo  wp_get_attachment_image( $attachment_id, 'thumbnail' ) ;
            ?>
					<a hre="#" class="jck-sfr-remove_attachment"></a>
				</li>
				<?php 
        }
        ?>
			</ul>
			<input type="hidden" name="jck_sfr_attachments" value="<?php 
        echo  implode( ',', $attachment_ids ) ;
        ?>">

			<a href="#" class="jck-sfr-add_attachments"><?php 
        _e( 'Add attachments', 'simple-feature-requests' );
        ?></a>
		</div>
		<?php 
    }

}