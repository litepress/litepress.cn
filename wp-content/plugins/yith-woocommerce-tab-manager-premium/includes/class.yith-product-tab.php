<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly


if ( ! class_exists( 'YWTM_Product_Tab' ) ) {

	class YWTM_Product_Tab {
		/**
		 * @var Single instance of the class
		 * @since 1.0.0
		 */
		protected static $instance;
		/**
		 * @var array of tabs
		 */
		protected $tabs = array();


		public function __construct() {

            add_filter( 'woocommerce_product_write_panel_tabs', array( $this, 'add_custom_tab_product_edit' ), 15 );
            add_filter( 'woocommerce_product_write_panel_tabs', array( $this, 'add_woocommerce_tabs_edit' ), 20 );
            add_action( 'woocommerce_process_product_meta', array( $this, 'save_product_tab_metabox' ), 30, 2 );




     	}



        /** Add tabs in product data section
         *
         * @author YITHEMES
         * @since 1.0.0
         * @use woocommerce_product_write_panel_tabs filter
         */
        public function add_custom_tab_product_edit() {
            global $product_object;


            if( $product_object instanceof  WC_Product ){

                $this->tabs = $this->get_current_product_tabs( $product_object, false );


                foreach (  $this->tabs as $tab ):?>
                    <li class="my-tabs <?php echo $tab->ID; ?>_tab">
                        <a href="#<?php echo $tab->ID; ?>_tab"><?php echo get_html_icon( $tab->ID ); ?><?php echo $tab->post_title; ?></a>
                    </li>
                <?php endforeach;

                add_action( 'woocommerce_product_data_panels', array( $this, 'write_tab_options' ) );
            }
        }

        /**
         * add woocommerce tabs in product edit
         * @author Salvatore Strano
         * @since 1.1.0
         */
        public function add_woocommerce_tabs_edit() {
            ?>

            <li class="my-tabs_ywtm_wc_tab">
                <a href="#ywtm_wc_tab"><span><?php _e( 'WooCommerce Tab', 'yith-woocommerce-tab-manager' ); ?></span></a>
            </li>

            <?php
            add_action( 'woocommerce_product_data_panels', array( $this, 'edit_woocommerce_tabs' ) );
        }

        /**
         * print tab option tab
         * @author YITHEMES
         * @since 1.0.0
         * @use woocommerce_product_write_panels
         */
        public function write_tab_options() {

            foreach ( $this->tabs as $tab ) {
                $field_name ="yith_product_tabs[$tab->ID]";
                $layout_tab = get_post_meta( $tab->ID, '_ywtm_layout_type', true );

                switch ( $layout_tab ) {

                    case 'gallery' :
                        include( YWTM_INC . 'product/admin/gallery.php' );
                        break;

                    case 'download' :
                        include( YWTM_INC . 'product/admin/download.php' );
                        break;

                    case 'map' :
                        include( YWTM_INC . 'product/admin/map.php' );
                        break;

                    case 'faq' :
                        include( YWTM_INC . 'product/admin/faq.php' );
                        break;

                    case 'video' :
                        include( YWTM_INC . 'product/admin/video.php' );

                        break;

                    case 'shortcode' :
                        include( YWTM_INC . 'product/admin/shortcode.php' );
                        break;

                    case 'contact' :
                        include( YWTM_INC . 'product/admin/contact.php' );
                        break;
                    default :
                        include( YWTM_INC . 'product/admin/default.php' );
                }
            }
        }

        /**
         * include template for woocommerce tabs
         * @author Salvatore Strano
         * @since 1.1.0
         */
        public function edit_woocommerce_tabs() {

            include( YWTM_INC . 'product/admin/woocommerce-tabs.php' );
        }

        /**
         * get all product tabs
         * @author Salvatore Strano
         * @since 2.0.0
         * @param WC_Product $product
         * @param boolean $get_all_tabs
         * @return array
         */
        public function get_current_product_tabs( $product, $get_all_tabs  ){

            $global_tabs = YITH_WCTM_Post_Type()->get_product_tabs( 'global', $get_all_tabs );
            $category_tabs = YITH_WCTM_Post_Type()->get_product_tabs( 'category', $get_all_tabs );
            $product_tabs = YITH_WCTM_Post_Type()->get_product_tabs( 'product',$get_all_tabs );

	        $global_tabs = apply_filters('yith_tab_manager_current_product_tabs', $global_tabs );
	        $category_tabs = apply_filters('yith_tab_manager_current_product_tabs', $category_tabs );
	        $product_tabs = apply_filters('yith_tab_manager_current_product_tabs', $product_tabs );

            $filtered_category_tabs = $this->filter_tabs_by_category( $product, $category_tabs );

            $filtered_product_tabs = $this->filter_tabs_by_product( $product, $product_tabs );

            $all_tabs = array_merge( $global_tabs,$filtered_category_tabs,$filtered_product_tabs );

            return $all_tabs ;
        }

        /**
     * return a filtered tabs list by product category
     * @author Salvatore Strano
     * @since 2.0.0
     * @param WC_Product $product
     * @param array $tabs
     * @return array
     */
        public function filter_tabs_by_category( $product, $tabs ){

            $filtered_tabs = array();
            $product_id =  $product ->get_id();

            $categories   = wp_get_post_terms( $product_id, 'product_cat', array( "fields" => "ids" ) );

            foreach ( $tabs as $tab ) {
                $cats = ywtm_get_meta( $tab->ID, '_ywtm_tab_category' );
                if (!empty($cats) && is_array( $cats ) ) {
                    foreach ($cats as $cat) {

                        $cat_id = yit_wpml_object_id($cat, 'product_cat');

                        if (in_array($cat_id, $categories)) {

                            $filtered_tabs[] = $tab;
                        }
                    }
                }
            }
            return $filtered_tabs;
        }

        /**
         * return a filtered tabs list by product
         * @author Salvatore Strano
         * @since 2.0.0
         * @param WC_Product $product
         * @param array $tabs
         * @return array
         */
        public function filter_tabs_by_product( $product, $tabs ){

            $filtered_tabs = array();
            $product_id =  $product->get_id();
            foreach ( $tabs as $tab ) {
                $prods = ywtm_get_meta( $tab->ID, '_ywtm_tab_product' );

                if ( ! empty( $prods ) && is_array( $prods ) ) {
                    foreach ( $prods as $prod ) {
                        $prod_id = yit_wpml_object_id( $prod, 'product' );
                        if ( $prod_id == $product_id ) {
                            $filtered_tabs[] = $tab;
                        }
                    }
                }
            }
            return $filtered_tabs;
        }

        /**
         * save custom metabox
         * @author Salvatore Strano
         * @since 2.0.0
         * @param  int $post_id
         * @param WP_Post $post
         */
        public function save_product_tab_metabox( $post_id, $post ){

            $product = wc_get_product( $post_id );

            if( isset( $_POST['yith_product_tabs'] ) ){

                $tabs = $_POST['yith_product_tabs'];

                foreach( $tabs as $tab_id => $values ){

                    $layout_tab = get_post_meta( $tab_id, '_ywtm_layout_type', true );

                    switch ( $layout_tab ) {

                        case 'download' :
                            $this->save_download_tab( $product, $tab_id, $values );
                            break;
                        case 'faq':
                            $this->save_faq_tab( $product, $tab_id, $values );
                            break;
                        case 'gallery':
                            $this->save_gallery_tab( $product, $tab_id, $values );
                            break;
                        case 'map':
                            $this->save_map_tab( $product, $tab_id, $values );
                            break;
                        case 'video':
                            $this->save_video_tab( $product, $tab_id, $values );
                            break;
                        case 'shortcode':
                            $this->save_shortcode_tab( $product, $tab_id, $values );
                            break;
                        case 'contact':
                            $this->save_contact_tab( $product, $tab_id, $values );
                            break;
                        default:
                            $this->save_editor_tab( $product, $tab_id, $values );
                            break;
                    }
                }
            }

            $this->save_product_wc_tabs_metabox( $post_id, $product );

        }

        /**
         * save download tab
         * @author Salvatore Strano
         * @since 2.0.0
         * @param WC_Product $product
         * @param int $tab_id
         * @param  array $values
         */
        public function save_download_tab( $product, $tab_id, $values ){

            if ( isset( $values['file_urls' ] ) ) {
                $files         = array();
                $file_names    = isset( $values['file_names' ] ) ? array_map( 'wc_clean', $values['file_names' ] ) : array();
                $file_urls     = isset( $values['file_urls' ] ) ? array_map( 'wc_clean', $values['file_urls' ] ) : array();
                $file_desc     = isset( $values['file_desc' ] ) ? array_map( 'wc_clean', $values['file_desc' ] ) : array();
                $file_url_size = sizeof( $file_urls );

                for ( $i = 0; $i < $file_url_size; $i ++ ) {
                    if ( ! empty( $file_urls[ $i ] ) ) {
                        $files[ md5( $file_urls[ $i ] ) ] = array(
                            'name' => wp_kses_post( wp_unslash( $file_names[ $i ] ) ),
                            'file' => $file_urls[ $i ],
                            'desc' => wp_kses_post( wp_unslash($file_desc[ $i ] ) )
                        );
                    }
                }

                 $product->update_meta_data( $tab_id.'_custom_list_file', $files );
	            $product->save();

            } else {
                $product->delete_meta_data( $tab_id.'_custom_list_file' );
	            $product->save();
            }



        }


        /**
         * save faq tab
         * @author Salvatore Strano
         * @since 2.0.0
         * @param WC_Product $product
         * @param int $tab_id
         * @param  array $values
         */
        public function save_faq_tab( $product, $tab_id, $values ){


            if ( isset( $values['faq_questions' ] ) ) {
                $faqs             = array();
                $faqs_question    = isset( $values['faq_questions' ] ) ? array_map( 'wc_clean', $values['faq_questions' ] ) : array();
                $faqs_answer      = isset( $values['faq_answers' ] ) ? array_map( 'wc_clean', $values['faq_answers' ] ) : array();
                $faqs_answer_size = sizeof( $faqs_answer );

                for ( $i = 0; $i < $faqs_answer_size; $i ++ ) {
                    if ( ! empty( $faqs_answer[ $i ] ) ) {
                        $faqs[ $i ] = array(
                            'question' => wp_kses_post( wp_unslash($faqs_question[ $i ] ) ),
                            'answer'   => wp_kses_post( wp_unslash( $faqs_answer[ $i ] ) )
                        );
                    }
                }

                 $product->update_meta_data( $tab_id.'_custom_list_faqs', $faqs );
                $product->save();

            } else {
                 $product->delete_meta_data( $tab_id.'_custom_list_faqs' );
                 $product->save();
            }
        }

        /**
         * save gallery tab
         * @author Salvatore Strano
         * @since 2.0.0
         * @param WC_Product $product
         * @param int $tab_id
         * @param array $values
         */
        public function save_gallery_tab( $product, $tab_id, $values ){
            if ( isset ( $values['custom_gallery_image_ids' ] ) ) {

                $gallery = explode( ",", $values['custom_gallery_image_ids' ] );
                $images  = array();
                $i       = 0;

                foreach ( $gallery as $image ) {
                    if ( ! empty( $image ) ) {
                        $images[ $i ] = array(
                            'id' => $image
                        );
                        $i ++;
                    }
                }

                $gallery_setting['columns'] = isset( $values['columns_number' ] ) ? $values['columns_number' ] : 1;

                $args = array(
                    'settings' => $gallery_setting,
                    'images'   => $images
                );
                 $product->update_meta_data( $tab_id.'_custom_gallery', $args );
                 $product->save();

            } else {
                $product->delete_meta_data( $tab_id.'_custom_gallery' );
	            $product->save();
            }
        }

        /**
         * save video tab
         * @author Salvatore Strano
         * @since 2.0.0
         * @param WC_Product $product
         * @param int $tab_id
         * @param array $values
         */
        public function save_video_tab( $product, $tab_id, $values ){

            $video_urls  = isset( $values['video_urls' ] ) ? $values['video_urls' ] : array();
            $video_ids   = isset( $values['video_ids' ] ) ? $values['video_ids' ] : array();
            $video_hosts = isset( $values['video_hosts' ] ) ? $values['video_hosts' ] : array();

            if ( ! empty( $video_urls ) || ! empty( $video_ids ) ) {

                $video_url_size = empty( $video_urls ) ? sizeof( $video_ids ) : sizeof( $video_urls );

                $videos = array();

                for ( $i = 0; $i < $video_url_size; $i ++ ) {
                    {
                        $videos[ $i ] = array(
                            'id'   => $video_ids[ $i ],
                            'url'  => $video_urls[ $i ],
                            'host' => $video_hosts[ $i ]
                        );
                    }
                }
                $gallery_setting['columns'] = isset($values['columns_number_video' ] ) ?$values['columns_number_video' ] : 1;

                $args = array(
                    'settings' => $gallery_setting,
                    'video'    => $videos
                );

                 $product->update_meta_data( $tab_id.'_custom_video', $args );
                 $product->save();
            } else {
                $product->delete_meta_data( $tab_id.'_custom_video' );
	            $product->save();
            }
        }

        /**
         * save map tab
         * @author Salvatore Strano
         * @since 2.0.0
         * @param WC_Product $product
         * @param int $tab_id
         * @param  array $values
         */
        public function save_map_tab( $product, $tab_id, $values ){
            $address = isset( $values['custom_map_addr' ] ) ? $values['custom_map_addr' ] : "";
            $width   = isset( $values['custom_map_width' ] ) ? $values['custom_map_width' ] : "";
            $height  = isset( $values['custom_map_height' ] ) ? $values['custom_map_height' ] : "";
            $zoom    = isset( $values['custom_map_zoom' ] ) ? $values['custom_map_zoom' ] : 15;
            $show_w  = isset( $values['enable_width' ] ) ? $values['enable_width' ] : 0;

            if ( ! empty( $address ) ) {
                $map_setting = array(

                    'addr'       => $address,
                    'wid'        => $width,
                    'heig'       => $height,
                    'zoom'       => $zoom,
                    'show_width' => $show_w
                );

                 $product->update_meta_data( $tab_id.'_custom_map', $map_setting );
                 $product->save();
            } else {
               $product->delete_meta_data( $tab_id.'_custom_map' );
               $product->save();
            }
        }

        /**
         * save shortcode tab
         * @author Salvatore Strano
         * @since 2.0.0
         * @param WC_Product $product
         * @param int $tab_id
         * @parm array $values
         */
        public function save_shortcode_tab( $product, $tab_id, $values ){
            $shortcode = isset( $values['shortcode' ] ) ?  $values['shortcode' ] : "";

            if ( ! empty( $shortcode ) ) {
                 $product->update_meta_data( $tab_id.'_custom_shortcode', $shortcode );
                 $product->save();
            } else {
                $product->delete_meta_data( $tab_id.'_custom_shortcode' );
                $product->save();
            }

        }

        /**
         * save contact tab
         * @author Salvatore Strano
         * @since 2.0.0
         * @param WC_Product $product
         * @param int $tab_id
         * @param array $values
         */
        public function save_contact_tab( $product, $tab_id, $values ){

            $fields['name']['show']     = isset( $values['name_show' ] ) ? $values['name_show' ] : 'off';
            $fields['webaddr']['show']  = isset(  $values['webaddr_show' ] ) ?  $values['webaddr_show' ] : 'off';
            $fields['subj']['show']     = isset(  $values['subj_show' ] ) ?  $values['subj_show' ] : 'off';
            $fields['name']['req']      = isset(  $values['name_req' ] ) ?  $values['name_req' ] : 'off';
            $fields['webaddr']['req']   = isset(  $values['webaddr_req' ] ) ?  $values['webaddr_req' ] : 'off';
            $fields['subj']['req']      = isset(  $values['subj_req' ] ) ?  $values['subj_req' ] : 'off';

             $product->update_meta_data( $tab_id . '_custom_form', $fields );
             $product->save();
        }

        /**
         * save editor tab
         * @author Salvatore Strano
         * @since 2.0.0
         * @param WC_Product $product
         * @param int $tab_id
         * @param array $values
         */
        public function save_editor_tab( $product, $tab_id, $values ){
            if ( isset( $values[ 'default_editor' ] ) ) {
                $content = wp_unslash( $values[ 'default_editor' ] );
                 $product->update_meta_data( $tab_id . '_default_editor', $content );
                 $product->save();

            }
        }

        /**
         * save product default tabs meta
         * @author Salvatore Strano
         * @since 1.1.0
         *
         * @param $product_id
         * @param WC_Product $product
         */
        public function save_product_wc_tabs_metabox( $product_id, $product ) {

            $tabs    = ywtm_get_default_tab( $product_id );

            foreach ( $tabs as $key => $tab ) {

                $is_hide_key  = '_ywtm_hide_' . $key;
                $is_hide_val  = isset( $_REQUEST[ 'ywtm_hide_' . $key ] ) ? 'yes' : 'no';
                $is_over_key  = '_ywtm_override_' . $key;
                $is_over_val  = isset( $_REQUEST[ 'ywtm_override_' . $key ] ) ? 'yes' : 'no';
                $priority_key = '_ywtm_priority_tab_' . $key;
                $priority_val = isset( $_REQUEST[ 'ywtm_priority_tab_' . $key ] ) ? $_REQUEST[ 'ywtm_priority_tab_' . $key ] : '1';
                $title_key    = '_ywtm_title_tab_' . $key;
                $title_val    = isset( $_REQUEST[ 'ywtm_title_tab_' . $key ] ) ? $_REQUEST[ 'ywtm_title_tab_' . $key ] : '';

                $product->update_meta_data( $is_hide_key, $is_hide_val );
	            $product->update_meta_data( $is_over_key, $is_over_val );
	            $product->update_meta_data( $priority_key, $priority_val );
	            $product->update_meta_data( $title_key, $title_val );

                if ( $key === 'description' ) {

                    $desc_key = '_ywtm_content_tab_' . $key;
                    $desc_val = isset( $_REQUEST[ 'ywtm_content_tab_' . $key ] ) ? $_REQUEST[ 'ywtm_content_tab_' . $key ] : '';
                    $desc_val = wp_unslash( $desc_val );

	                $product->update_meta_data( $desc_key, $desc_val );
                }

                $product->save();


            }
        }


        /**
		 * @author YITHEMES
		 * @since 1.0.0
		 * @return YWTM_Product_Tab
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}
	}
}
/**
 * @author YITHEMES
 * @since 1.0.0
 * @return YWTM_Product_Tab
 */

function YWTM_Product_Tab() {
	return YWTM_Product_Tab::get_instance();
}


