<?php
/**
 * Layout tab
 */

global $YIT_Tab_Manager;
$args = array(

	'layout' => array(
		'label'  => __( 'Layout', 'yith-woocommerce-tab-manager' ),
		'fields' => apply_filters( 'ywtm_layout_options_metabox', array(

				'ywtm_layout_type' => array(
					'label'   => __( 'Layout Type', 'yith-woocommerce-tab-manager' ),
					'desc'    => __( 'Choose the layout of the tab', 'yith-woocommerce-tab-manager' ),
					'type'    => 'select',
					'options' => ywtm_get_layout_types(),
					'std'     => 'default'
				),

				'ywtm_enable_custom_content' => array(
					'label' => __( 'Use the content for all products', 'yith-woocommerce-tab-manager' ),
					'desc'  => '',
					'type'  => 'checkbox',
					'std'   => 'no'

				),

				'sep' => array(
					'type' => 'sep'
				),

				/*FAQ*/

				'ywtm_faqs' => array(
					'label' => __( 'FAQ', 'yith-woocommerce-tab-manager' ),
					'type'  => 'ywtb-faqs-type',
					'deps'  => array(
						'ids'    => '_ywtm_layout_type,_ywtm_enable_custom_content',
						'values' => 'faq,yes',
					),
				),


				/*Image gallery*/

				'ywtm_gallery_columns' => array(
					'label' => __( 'Images per row', 'yith-woocommerce-tab-manager' ),
					'desc'  => __( 'Set how many columns to show', 'yith-woocommerce-tab-manager' ),
					'type'  => 'number',
					'std'   => 1,
					'min'   => 1,
					'max'   => 4,
					'deps'  => array(
						'ids'    => '_ywtm_layout_type,_ywtm_enable_custom_content',
						'values' => 'gallery,yes',
					),
				),


				'ywtm_gallery'               => array(
					'label' => __( 'Image Gallery', 'yith-woocommerce-tab-manager' ),
					'desc'  => __( 'Add images for the gallery', 'yith-woocommerce-tab-manager' ),
					'type'  => 'image-gallery',
					'deps'  => array(
						'ids'    => '_ywtm_layout_type,_ywtm_enable_custom_content',
						'values' => 'gallery,yes',
					),
				),


				/*Video gallery*/
				'ywtm_video'                 => array(
					'label' => __( 'Video Gallery', 'yith-woocommerce-tab-manager' ),
					'desc'  => '',
					'type'  => 'ywtb-video-gallery-type',
					'deps'  => array(
						'ids'    => '_ywtm_layout_type,_ywtm_enable_custom_content',
						'values' => 'video,yes',
					)

				),

				/*Download*/
				'ywtm_download'              => array(
					'label' => __( 'Attach File', 'yith-woocommerce-tab-manager' ),
					'desc'  => __( 'Upload your PDF file', 'yith-woocommerce-tab-manager' ),
					'type'  => 'ywtb-downloads-type',
					'deps'  => array(
						'ids'    => '_ywtm_layout_type,_ywtm_enable_custom_content',
						'values' => 'download,yes',
					),
				),

				/*Google map*/
				'ywtm_google_map_full_width' => array(
					'label' => __( 'Full Width', 'yith-woocommerce-tab-manager' ),
					'desc'  => '',
					'type'  => 'onoff',
					'std'   => 'yes',
					'deps'  => array(
						'ids'    => '_ywtm_layout_type,_ywtm_enable_custom_content',
						'values' => 'map,yes',
					),
				),
				'ywtm_google_map_width'      => array(
					'label' => __( 'Width', 'yith-woocommerce-tab-manager' ),
					'desc'  => __( 'Set the width for the Google map', 'yith-woocommerce-tab-manager' ),
					'type'  => 'number',
					'std'   => '100',
					'min'   => '0',
					'max'   => '999',
					'deps'  => array(
						'ids'    => '_ywtm_layout_type,_ywtm_enable_custom_content,_ywtm_google_map_full_width',
						'values' => 'map,yes,no'
					)
				),
				'ywtm_google_map_height'     => array(
					'label' => __( 'Height', 'yith-woocommerce-tab-manager' ),
					'desc'  => __( 'Set the height for the Google map', 'yith-woocommerce-tab-manager' ),
					'type'  => 'number',
					'std'   => '100',
					'min'   => '0',
					'max'   => '999',
					'deps'  => array(
						'ids'    => '_ywtm_layout_type,_ywtm_enable_custom_content',
						'values' => 'map,yes',
					),
				),


				'ywtm_google_map_overlay_address' => array(
					'label' => __( 'Address', 'yith-woocommerce-tab-manager' ),
					'desc'  => __( 'Set the address (like "1600 Amphitheatre Parkway, Mountain View, CA" )', 'yith-woocommerce-tab-manager' ),
					'type'  => 'text',
					'std'   => '',
					'deps'  => array(
						'ids'    => '_ywtm_layout_type,_ywtm_enable_custom_content',
						'values' => 'map,yes',
					),
				),

				'ywtm_google_map_overlay_zoom' => array(
					'label' => __( 'Zoom', 'yith-woocommerce-tab-manager' ),
					'desc'  => __( 'Set the zoom of the map (0-19)', 'yith-woocommerce-tab-manager' ),
					'type'  => 'number',
					'std'   => '15',
					'min'   => '0',
					'max'   => '19',
					'deps'  => array(
						'ids'    => '_ywtm_layout_type,_ywtm_enable_custom_content',
						'values' => 'map,yes',
					),
				),

				/*Editor*/
				'ywtm_text_tab'                => array(
					'label' => __( 'Tab Content', 'yith-woocommerce-tab-manager' ),
					'desc'  => '',
					'type'  => 'textarea-editor',
					'deps'  => array(
						'ids'    => '_ywtm_layout_type,_ywtm_enable_custom_content',
						'values' => 'default,yes'
					),

				),

				/*Contact Form*/
				'ywtm_form_tab'                => array(
					'label' => __( 'Contact Form', 'yith-woocommerce-tab-manager' ),
					'desc'  => '',
					'type'  => 'ywtb-forms-type',
					'deps'  => array(
						'ids'    => '_ywtm_layout_type,_ywtm_enable_custom_content',
						'values' => 'contact,yes',
					)
				),

				/*Shortcode*/
				'ywtm_shortcode_tab'           => array(
					'label' => __( 'Shortcode', 'yith-woocommerce-tab-manager' ),
					'desc'  => '',
					'type'  => 'textarea',
					'deps'  => array(
						'ids'    => '_ywtm_layout_type,_ywtm_enable_custom_content',
						'values' => 'shortcode,yes'
					)
				)
			)
		),
	),

);

return $args;