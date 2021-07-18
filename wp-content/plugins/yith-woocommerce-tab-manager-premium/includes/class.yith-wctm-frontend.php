<?php
if( !defined('ABSPATH')){
    exit;
}
if( !class_exists('YITH_WCTM_Frontend')){
    class YITH_WCTM_Frontend{

        protected static $instance;

        public function __construct()
        {
        	//add tabs to woocommerce
            add_filter( 'woocommerce_product_tabs', array( $this, 'add_tabs_woocommerce' ), 20 );

        }

        /**
         * @return YITH_WCTM_Frontend
         */
        public static function get_instance(){
            if( is_null( self::$instance)){
                self::$instance = new self();
            }
            return self::$instance;
        }

        /**
         * add_global_tabs_woocommerce
         *
         * @author YITHEMES
         * @since 1.0.0
         * @param $tabs
         * @return mixed
         * @use woocommerce_product_tabs filter
         */
        public function add_tabs_woocommerce( $tabs )
        {

            $yith_tabs = YITH_WCTM_Post_Type()->get_tabs();
            $priority = apply_filters( 'ywctm_priority_tab', 30 );

            foreach ( $yith_tabs as $tab ) {

                $tabs[$tab["id"]] = array(
                    'title' => __( $tab['title'], 'yith-woocommerce-tab-manager' ),
                    'priority' => $tab['priority']+$priority,
                    'callback' => array( $this, 'put_content_tabs' )
                );

            }

            return $tabs;
        }

        /**
         * put_content_tabs
         * Put the content at the tabs
         * @param $key
         * @param $tab
         */
        public function put_content_tabs( $key, $tab )
        {

            $args['content'] = get_post_meta( $key, '_ywtm_text_tab', true );

            wc_get_template( 'default.php', $args, YWTM_TEMPLATE_PATH, YWTM_TEMPLATE_PATH );
        }
    }
}

/**
 * @return YITH_WCTM_Frontend_Frontend
 */
function YITH_Tab_Manager_Frontend(){

    if( defined('YWTM_PREMIUM' ) && class_exists('YITH_WCTM_Frontend_Premium' ) ){
        return  YITH_WCTM_Frontend_Premium::get_instance();
    }else{
        return YITH_WCTM_Frontend::get_instance();
    }
}