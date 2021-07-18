<?php

if( !defined('ABSPATH')){
    exit;
}
if( !class_exists('YITH_WCTM_Post_Type')){
    class YITH_WCTM_Post_Type{

        protected static $instance;
        /**
         * Post type name
         *
         * @var string
         * @since 1.0.0
         */
        public $post_type_name = 'ywtm_tab';


        public function __construct()
        {
            //Add action register post type
            add_action( 'init', array( $this, 'tabs_post_type' ), 10 );

            add_filter( 'manage_edit-' . $this->post_type_name . '_columns', array( $this, 'edit_columns' ) );
            add_action( 'manage_' . $this->post_type_name . '_posts_custom_column', array( $this, 'custom_columns' ), 10, 2 );
            //Custom Tab Message
            add_filter( 'post_updated_messages', array( $this, 'custom_tab_messages' ) );


        }

        /**
         * Returns single instance of the class
         * @author Salvatore Strano
         * @return YITH_WCTM_Post_Type
         * @since 2.0.0
         */
        public static function get_instance()
        {
            if (is_null(self::$instance)) {
                self::$instance = new self();
            }

            return self::$instance;
        }

        /**
         * tabs_post_type
         *
         * Register a Global Tab post type
         *
         * @author Salvatore Strano
         * @since 1.0.0
         */
        public function tabs_post_type()
        {
            $args = apply_filters( 'yith_wctm_post_type', array(
                    'label' => __( 'ywtm_tab', 'yith-woocommerce-tab-manager' ),
                    'description' => __( 'Yith Tab Manager Description', 'yith-woocommerce-tab-manager' ),
                    'labels' => $this->get_tab_taxonomy_label(),
                    'supports' => array( 'title' ),
                    'hierarchical' => true,
                    'public' => false,
                    'show_ui' => true,
                    'show_in_menu' => true,
                    'show_in_nav_menus' => false,
                    'show_in_admin_bar' => false,
                    'menu_position' => 57,
                    'menu_icon' => 'dashicons-feedback',
                    'can_export' => true,
                    'has_archive' => false,
                    'exclude_from_search' => true,
                    'publicly_queryable' => false,
                    'capability_type' => 'post',
                )
            );


            register_post_type( $this->post_type_name, $args );

        }

        /**
         * Get the tab taxonomy label
         *
         * @param   $arg string The string to return. Defaul empty. If is empty return all taxonomy labels
         *
         * @author Andrea Grillo <andrea.grillo@yithemes.com>
         * @since  1.0.0
         *
         * @return Array taxonomy label
         * @fire yith_tab_manager_taxonomy_label hooks
         */
        protected function get_tab_taxonomy_label( $arg = '' )
        {

            $label = apply_filters( 'yith_tab_manager_taxonomy_label', array(
                    'name' => _x( 'YITH WooCommerce Tab Manager', 'Post Type General Name', 'yith-woocommerce-tab-manager' ),
                    'singular_name' => _x( 'Tab', 'Post Type Singular Name', 'yith-woocommerce-tab-manager' ),
                    'menu_name' => __( 'Tab Manager', 'yith-woocommerce-tab-manager' ),
                    'parent_item_colon' => __( 'Parent Item:', 'yith-woocommerce-tab-manager' ),
                    'all_items' => __( 'All Tabs', 'yith-woocommerce-tab-manager' ),
                    'view_item' => __( 'View Tabs', 'yith-woocommerce-tab-manager' ),
                    'add_new_item' => __( 'Add New Tab', 'yith-woocommerce-tab-manager' ),
                    'add_new' => __( 'Add New Tab', 'yith-woocommerce-tab-manager' ),
                    'edit_item' => __( 'Edit Tab', 'yith-woocommerce-tab-manager' ),
                    'update_item' => __( 'Update Tab', 'yith-woocommerce-tab-manager' ),
                    'search_items' => __( 'Search Tab', 'yith-woocommerce-tab-manager' ),
                    'not_found' => __( 'Not found', 'yith-woocommerce-tab-manager' ),
                    'not_found_in_trash' => __( 'Not found in Trash', 'yith-woocommerce-tab-manager' ),
                )
            );
            return !empty( $arg ) ? $label[$arg] : $label;
        }

        /**
         * Customize the messages for Tabs
         * @param $messages
         * @author Salvatore Strano
         *
         * @return array
         * @fire post_updated_messages filter
         */
        public function custom_tab_messages( $messages )
        {

            $singular_name = $this->get_tab_taxonomy_label( 'singular_name' );
            $messages[$this->post_type_name] = array(

                0 => '',
                1 => sprintf( __( '%s updated', 'yith-woocommerce-tab-manager' ), $singular_name ),
                2 => __( 'Custom field updated', 'yith-woocommerce-tab-manager' ),
                3 => __( 'Custom field deleted', 'yith-woocommerce-tab-manager' ),
                4 => sprintf( __( '%s updated', 'yith-woocommerce-tab-manager' ), $singular_name ),
                5 => isset( $_GET['revision'] ) ? sprintf( __( 'Tab restored to version %s', 'yith-woocommerce-tab-manager' ), wp_post_revision_title( (int)$_GET['revision'], false ) ) : false,
                6 => sprintf( __( '%s published', 'yith-woocommerce-tab-manager' ), $singular_name ),
                7 => sprintf( __( '%s saved', 'yith-woocommerce-tab-manager' ), $singular_name ),
                8 => sprintf( __( '%s submitted', 'yith-woocommerce-tab-manager' ), $singular_name ),
                9 => sprintf( __( '%s', 'yith-woocommerce-tab-manager' ), $singular_name ),
                10 => sprintf( __( '%s draft updated', 'yith-woocommerce-tab-manager' ), $singular_name )
            );


            return $messages;
        }




        /** Edit Columns Table
         * @param $columns
         * @return mixed
         */
        function edit_columns( $columns )
        {

            $columns = apply_filters( 'yith_add_column_tab', array(
                    'cb' => '<input type="checkbox" />',
                    'title' => __( 'Title', 'yith-woocommerce-tab-manager' ),
                    'is_show' => __( 'Is Visible', 'yith-woocommerce-tab-manager' ),
                    'tab_position' => __( 'Tab Position', 'yith-woocommerce-tab-manager' ),
                    'date' => __( 'Date', 'yith-woocommerce-tab-manager' ),
                )
            );

            return $columns;
        }

        /**
         * Print the content columns
         * @param $column
         * @param $post_id
         */
        public function custom_columns( $column, $post_id )
        {
            switch ( $column ) {
                case 'is_show' :
                    $show = get_post_meta( $post_id, '_ywtm_show_tab', true );

                    if( $show ) {
                        echo '<mark class="show tips" data-tip="yes">yes</mark>';
                    }
                    else {
                        echo '<mark class="hide tips" data-tip="no">no</mark>';
                    }
                    break;

                case 'tab_position' :
                    $tab_position = get_post_meta( $post_id, '_ywtm_order_tab', true );
                    echo $tab_position;
                    break;
            }

            do_action( 'ywtm_show_custom_columns',$column, $post_id );

        }
        /**
         * get_tabs
         * build the global tab
         *
         * @author YITHEMES
         * @return mixed
         */
        public function get_tabs()
        {

            /*Custom query for gets all post 'Tab'*/

            $args = array(
                'post_type' => 'ywtm_tab',
                'post_status' => 'publish',
                'posts_per_page' => -1,
                'suppress_filters' => false,
                'meta_query' => array(
                    array(
                        'key' =>  '_ywtm_show_tab',
                        'value' => true
                    )
                )

            );

            if( function_exists( 'pll_current_language' ) ) {
                $args['lang'] = pll_current_language();
            }
            $q_tabs = get_posts( $args );
            $tabs = array();


            foreach ( $q_tabs as $tab ) {

                $attr_tab = array();
                    $attr_tab['title'] = $tab->post_title;
                    $attr_tab['priority'] = get_post_meta( $tab->ID, '_ywtm_order_tab', true );
                    $attr_tab['id'] = $tab->ID;
                    $tabs[$tab->post_title . '_' . $tab->ID] = $attr_tab;

            }
            return $tabs;

        }

    }

}
/**
 * @return YITH_WCTM_Post_Type|YITH_WCTM_Post_Type_Premium
 */
function YITH_WCTM_Post_Type(){

    if( defined('YWTM_PREMIUM' ) && class_exists('YITH_WCTM_Post_Type_Premium' ) ){
        return  YITH_WCTM_Post_Type_Premium::get_instance();
    }else{
        return YITH_WCTM_Post_Type::get_instance();
    }

}