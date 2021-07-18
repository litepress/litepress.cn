<?php
/**
 * Created by PhpStorm.
 * User: Your Inspiration
 * Date: 18/03/2015
 * Time: 14:44
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

$desc_api_key = sprintf('%s <a href="https://developers.google.com/maps/documentation/javascript/get-api-key#key" target="_blank">%s</a>', __('Insert a valid API KEY or','yith-woocommerce-tab-manager'),__('Get a Key','yith-woocommerce-tab-manager') );

return array(

    'settings' => array(

        'section_general_settings'     => array(
            'name' => __( 'General settings', 'yith-woocommerce-tab-manager' ),
            'type' => 'title',
        ),

        'hide_wc_desc_tab_in_mobile' =>array(
            'name' =>__('Hide WooCommerce Description tab','yith-woocommerce-tab-manager' ),
            'desc' => __('If checked, description tab will not show up on products', 'yith-woocommerce-tab-manager'),
            'id' => 'ywtm_hide_wc_desc_tab_in_mobile',
            'default' => 'no',
            'type' => 'yith-field',
            'yith-type'    => 'checkbox'
        ),
        'hide_wc_reviews_tab' =>array(
            'name' =>__('Hide WooCommerce Reviews tab','yith-woocommerce-tab-manager' ),
            'desc' => __('If checked, Reviews tab will not show up on products', 'yith-woocommerce-tab-manager'),
            'id' => 'ywtm_hide_wc_reviews_tab',
            'type' => 'yith-field',
            'yith-type'    => 'checkbox'
        ),
        'hide_wc_addinfo_tab' =>array(
            'name' =>__('Hide WooCommerce Additional Information tab','yith-woocommerce-tab-manager' ),
            'desc' => __('If checked, Additional information tab will not show up on products', 'yith-woocommerce-tab-manager'),
            'id' => 'ywtm_hide_wc_addinfo_tab',
            'type' => 'yith-field',
            'yith-type'    => 'checkbox'
        ),

        'hide_wc_tab_in_mobile' =>array(
            'name' =>__('Hide WooCommerce tab on mobile','yith-woocommerce-tab-manager' ),
            'desc' => __('If checked this option hide woocommerce tab on mobile', 'yith-woocommerce-tab-manager'),
            'id' => 'ywtm_hide_wc_tab_mobile',
            'type' => 'yith-field',
            'yith-type'    => 'checkbox'
        ),

        'hide_tab_in_mobile' =>array(
         'name' =>__('Hide plugin tab on mobile','yith-woocommerce-tab-manager' ),
        'desc' => __('If checked, this option hide plugin tab on mobile', 'yith-woocommerce-tab-manager'),
        'id' => 'ywtm_hide_tab_mobile',
        'default' => 'no',
         'type' => 'yith-field',
         'yith-type'    => 'checkbox'
            ),



        'google_api_key' => array(
            'name' => __('Google API KEY', 'yith-woocommerce-tab-manager'),
            'id' => 'ywtm_google_api_key',
            'desc' => $desc_api_key,
            'type' => 'yith-field',
            'yith-type'    => 'text',
            'default' => ''
        ),
        'custom_css' => array(
          'name' => __( 'Custom Style', 'yith-woocommerce-tab-manager'),
          'type' => 'yith-field',
          'yith-type'    => 'textarea',
           'id' => 'ywtm_custom_style',
            'css' => 'width:100%;min-height:100px;',
            'desc'    => __( 'Insert here your custom CSS', 'yith-woocommerce-tab-manager' ),
            'default' => ''
        ),

         'section_general_settings_end' => array(
            'type' => 'sectionend',
        )
    )
);