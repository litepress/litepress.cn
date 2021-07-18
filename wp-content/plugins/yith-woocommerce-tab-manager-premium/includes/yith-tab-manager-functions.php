<?php

if( !function_exists( 'get_html_icon' )  ) {
    /**Print the html code for admin
     * @param int $tab
     * @author YITHEMES
     * @since 1.0.0
     * @return string
     */
    function get_html_icon( $tab )
    {


        $icon = get_post_meta( $tab, '_ywtm_icon_tab', true );


        $tab_icon = '';
        if( !empty( $icon ) ) {

            switch ( $icon['select'] ) {
                case 'icon' :


                	$icon = ywtm_map_old_icon_with_new( $icon['icon'] );
                	$icon_data = explode( ':', $icon );

                	if( 'FontAwesome' == $icon_data[0] ){
                		$class = 'fas fa-'.$icon_data[1];
	                }elseif( 'Dashicons' == $icon_data[0] ){
                		$class = 'dashicons dashicons-'.$icon_data[1];
	                }else{
                		$class ='retinaicon-font '.$icon_data[1];
	                }
                    $tab_icon = sprintf( '<span class="ywtm_icon %s" style="padding-right:10px;"></span>', $class);

                    break;
                case 'upload' :
                    $tab_icon = '<span class="ywtm_custom_icon" style="padding-right:10px;" ><img src="' . $icon['custom'] . '" style="max-width :27px;max-height: 25px;display: inline-block"/></span>';
                    break;
            }
        }


        return $tab_icon;
    }
}

if( !function_exists( 'ywtm_get_default_tab' ) ) {

    function ywtm_get_default_tab( $product_id )
    {

        global $post;

        $tabs = array();
        $product = wc_get_product( $product_id );
        // Description tab - shows product content
        if( apply_filters( 'ywtm_override_tab_desc',$post->post_content, $post) ) {
            $tabs['description'] = array(
                'title' => __( 'Description', 'woocommerce' ),
                'priority' => 10,
                'callback' => 'woocommerce_product_description_tab'
            );
        }

        // Additional information tab - shows attributes
        // Additional information tab - shows attributes
        if ( $product && ( $product->has_attributes() || apply_filters( 'wc_product_enable_dimensions_display', $product->has_weight() || $product->has_dimensions() ) ) ) {
            $tabs['additional_information'] = array(
                'title'    => __( 'Additional information', 'woocommerce' ),
                'priority' => 20,
                'callback' => 'woocommerce_product_additional_information_tab',
            );
        }
        // Reviews tab - shows comments
        if( comments_open() ) {
            $tabs['reviews'] = array(
                'title' => __( 'Reviews', 'woocommerce' ),
                'priority' => 30,
                'callback' => 'comments_template'
            );
        }
        return $tabs;
    }
}

if( !function_exists( 'ywtm_get_tab_ppl_language' ) ) {
    function ywtm_get_tab_ppl_language( $args )
    {
        
            global $post;

            if( isset( $post ) ) {
                $lang = pll_get_post_language( $post->ID );
                $args['lang'] = $lang;

            }
            return $args;
             
    }
}

 function ywtm_get_meta( $tab_id, $meta_key ){

    $value = get_post_meta( $tab_id, $meta_key, true );

    if( !empty( $value ) && !is_array( $value ) ){
        $value = explode(',',$value);
    }

    return $value;
}


/**get_tab_types
 *
 * return type tabs
 *
 * @author Salvatore Strano
 * @since 1.0.0
 * @return array
 */
 function ywtm_get_tab_types()
{

    $tab_type = array(
        'global' => __( 'Global Tab', 'yith-woocommerce-tab-manager' ),
        'category' => __( 'Category Tab', 'yith-woocommerce-tab-manager' ),
        'product' => __( 'Product Tab', 'yith-woocommerce-tab-manager' )
    );

    return $tab_type;

}

/**return layout type of tabs
 * @author Salvatore Strano
 * @since 1.0.0
 * @return array
 */
 function ywtm_get_layout_types()
{

    $tab_layout_types = apply_filters( 'yith_add_layout_tab', array(

            'default' => __( 'Editor', 'yith-woocommerce-tab-manager' ),
            'video' => __( 'Video Gallery', 'yith-woocommerce-tab-manager' ),
            'gallery' => __( 'Image Gallery', 'yith-woocommerce-tab-manager' ),
            'faq' => __( 'FAQ', 'yith-woocommerce-tab-manager' ),
            'download' => __( 'Download', 'yith-woocommerce-tab-manager' ),
            'map' => __( 'Map', 'yith-woocommerce-tab-manager' ),
            'contact' => __( 'Contact', 'yith-woocommerce-tab-manager' ),
            'shortcode' => __( 'Shortcode', 'yith-woocommerce-tab-manager' )

        )
    );

    return $tab_layout_types;
}

/**
 * map the old icon with last font awesome
 * @author Salvatore Strano
 * @since 1.2.0
 * @param $icon_name
 *
 * @return string
 */
function ywtm_map_old_icon_with_new( $icon_name ){


	if( strpos( $icon_name,'FontAwesome:fa-' )!== false ){

		$icon_name = str_replace( 'FontAwesome:fa-','FontAwesome:', $icon_name );
	}

	return $icon_name;
}


add_filter('ywtm_show_single_tab', 'ywtm_check_if_show_tab', 10,1 );

if( !function_exists('ywtm_check_if_show_tab' ) ) {
	function ywtm_check_if_show_tab( $show ) {

		$is_elementor = isset( $_REQUEST['action']  ) && 'elementor' == $_REQUEST['action'];
		if( $is_elementor ){
			$show = true;
		}
		return $show;
	}
}