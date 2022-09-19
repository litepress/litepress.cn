<?php
if( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly
if( !class_exists('YITH_WCTM_Admin_Premium')){

    class YITH_WCTM_Admin_Premium extends  YITH_WCTM_Admin {
        /**
         * @var YITH_WCTM_Admin_Premium $instance
         */
        protected static $instance;





        public function __construct()
        {
            parent::__construct();
            add_action( 'wp_loaded', array( $this, 'register_plugin_for_activation' ), 99 );
            add_action( 'admin_init', array( $this, 'register_plugin_for_updates' ) );
            add_action( 'admin_init', array( $this, 'add_layout_tab_metabox' ), 15 );
            add_action( 'admin_enqueue_scripts', array( $this, 'admin_premium_scripts' ), 20 );
            add_action( 'admin_enqueue_scripts', array( $this, 'admin_premium_styles' ), 100 );

	        add_filter( 'yith_plugin_fw_get_field_template_path', array( $this, 'add_custom_metaboxes_type' ), 10, 2 );




	        //add a new row in video table and in download table
	        add_action( 'wp_ajax_add_table_row', array( $this, 'add_table_row' ) );


	        YWTM_Product_Tab();

        }

        /**
         * Returns single instance of the class
         * @author Salvatore Strano
         * @return YITH_WCTM_Admin_Premium
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
         * Register plugins for activation tab
         *
         * @return void
         * @since    2.0.0
         * @author   Andrea Grillo <andrea.grillo@yithemes.com>
         */
        public function register_plugin_for_activation()
        {
            if( !class_exists( 'YIT_Plugin_Licence' ) ) {
                require_once YWTM_DIR . 'plugin-fw/licence/lib/yit-licence.php';
                require_once YWTM_DIR . 'plugin-fw/licence/lib/yit-plugin-licence.php';
            }
            YIT_Plugin_Licence()->register( YWTM_INIT, YWTM_SECRET_KEY, YWTM_SLUG );
        }

        /**
         * Register plugins for update tab
         *
         * @return void
         * @since    1.0.0
         * @author   Andrea Grillo <andrea.grillo@yithemes.com>
         */
        public function register_plugin_for_updates()
        {
            if( !class_exists( 'YIT_Upgrade' ) ) {
                require_once( YWTM_DIR . 'plugin-fw/lib/yit-upgrade.php' );
            }
            YIT_Upgrade()->register( YWTM_SLUG, YWTM_INIT );
        }

        /**
         * add_tab_metabox
         * Register metabox for global tab
         * @author Salvatore Strano
         * @since 1.0.0
         */
        public function add_layout_tab_metabox()
        {
            $args = include_once( YWTM_INC . '/metabox/tab-layout-metabox.php' );

            if( !function_exists( 'YIT_Metabox' ) ) {
                require_once( YWTM_DIR . 'plugin-fw/yit-plugin.php' );
            }
            $metabox = YIT_Metabox( 'yit-tab-manager-setting' );
            $metabox->add_tab( $args, 'after', 'settings' );

        }

        /**Include admin script
         * @author Salvatore Strano
         * @since 2.0.0
         * @use admin_enqueue_scripts
         */
        public function admin_premium_scripts()
        {
            global $post;

            if( isset( $_GET['page'] ) && 'master-slider' === $_GET['page'] ) {
                wp_deregister_script( 'yit-spinner' );
            }

            $current_screen = get_current_screen();
            wp_register_script( 'ywtm_admin_post_type', YWTM_ASSETS_URL . 'js/backend/' . yit_load_js_file( 'ywtm_admin_table.js' ), array( 'jquery', 'jquery-ui-sortable' ), YWTM_VERSION, true );
            wp_register_script( 'ywtm_admin_product', YWTM_ASSETS_URL . 'js/backend/' . yit_load_js_file( 'admin_tab_product.js' ), array( 'jquery' ), YWTM_VERSION, true );
	        $params = array(
		        'admin_url' => admin_url( 'admin-ajax.php', is_ssl() ? 'https' : 'http' ),
		        'actions'   => array(

			        'add_table_row' => 'add_table_row'
		        ),
	        );
	        wp_localize_script( 'ywtm_admin_post_type', 'yith_tab_params',  $params );
	        wp_localize_script( 'ywtm_admin_table', 'yith_tab_params',  $params );

            if( isset( $current_screen->post_type ) && ( 'ywtm_tab' == $current_screen->post_type ) ) {

	            wp_enqueue_script( 'ywtm_admin_post_type' );

            }


            if( isset( $current_screen->post_type ) && ( 'product' == $current_screen->post_type ) ){

	            wp_enqueue_script( 'ywtm_admin_product' );

	            if( !wp_script_is('yith-plugin-fw-fields') ){
		            wp_enqueue_script( 'yith-plugin-fw-fields' );
		            wp_enqueue_script( 'codemirror' );
		            wp_enqueue_script( 'codemirror-javascript' );

	            }

            }
        }


	    /**
	     * include premium styles
	     * @author Salvatore Strano
	     * @since 1.2.0
	     */
        public function admin_premium_styles(){


	        $current_screen = get_current_screen();

	        wp_register_style( 'font-retina', YWTM_ASSETS_URL.'fonts/retinaicon-font/style.css', array(), YWTM_VERSION );

	        if( isset( $current_screen->post_type ) && ( ( 'ywtm_tab' == $current_screen->post_type )  || 'product' == $current_screen->post_type ) ) {

	        	if( !wp_style_is('font-awesome' ) ){
			        wp_enqueue_style( 'font-awesome' );
		        }

		        wp_enqueue_style( 'font-retina' );
	        }
        }
	    /**
	     * add custom type in the plugin-fw
	     * @author Salvatore Strano
	     * @since 1.2.0
	     * @param string $field_template
	     * @param array $field
	     *
	     * @return string
	     */
	    public function add_custom_metaboxes_type( $field_template, $field ) {
		    $custom_types = array(
			    'ywtb-faqs-type',
			    'ywtb-forms-type',
			    'ywtb-iconlist-type',
			    'ywtb-video-gallery-type',
			    'ywtb-downloads-type',
			    'ywtb-table-type'

		    );

		    if ( in_array( $field['type'], $custom_types ) ) {
			    $field_template = YWTM_TEMPLATE_PATH . '/metaboxes/types/' . $field['type'] . '.php';
		    }


		    return $field_template;
	    }

	    public function add_table_row(){

		    $template = '';

		    if( isset($_REQUEST['add_table_row'] ) ){

			    $add_table_row = $_REQUEST['add_table_row'];
			    $i = isset( $_REQUEST['row'] ) ? $_REQUEST['row'] : 0;
			    $field_id = '';
			    $type = 'video';
			    if( 'video_row' == $add_table_row ){
				    $field_id = '_ywtm_video';
			    }elseif( 'download_row' == $add_table_row ){
				    $field_id = '_ywtm_download';
				    $type = 'download';
			    }elseif( 'faq_row' == $add_table_row  ){
				    $field_id = '_ywtm_faqs';
				    $type = 'faq';
			    }

			    $args = array(
				    'i' => $i,
				    'field_id' => $field_id,
				    'value' => array()
			    );

			    ob_start();
			    wc_get_template( '/metaboxes/types/views/'.$type.'-table-row.php', $args, YWTM_TEMPLATE_PATH, YWTM_TEMPLATE_PATH );

			    $template = ob_get_contents();
			    ob_end_clean();

			    wp_send_json( array( 'result' => $template ) );
		    }
	    }

	    /**
	     * plugin_row_meta
	     *
	     * add the action links to plugin admin page
	     *
	     * @param $new_row_meta_args
	     * @param $plugin_meta
	     * @param $plugin_file
	     * @param $plugin_data
	     * @param $status
	     *
	     * @return   array
	     * @since    1.0
	     * @author   Andrea Grillo <andrea.grillo@yithemes.com>
	     * @use plugin_row_meta
	     */
	    public function plugin_row_meta( $new_row_meta_args, $plugin_meta, $plugin_file, $plugin_data, $status, $init_file = 'YWTM_INIT' ) {

	    	$new_row_meta_args = parent::plugin_row_meta( $new_row_meta_args, $plugin_meta, $plugin_file, $plugin_data, $status, $init_file );
		    if ( defined( $init_file ) && constant( $init_file ) == $plugin_file ) {
			    $new_row_meta_args['is_premium'] = true;

		    }

		    return $new_row_meta_args;

	    }

    }
}