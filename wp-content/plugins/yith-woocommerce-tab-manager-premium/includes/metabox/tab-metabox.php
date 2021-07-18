<?php
/**
 * Created by PhpStorm.
 * User: Your Inspiration
 * Date: 18/03/2015
 * Time: 13:53
 */
global $YIT_Tab_Manager;

$desc_priority = sprintf( '%s: %s: 10, %s: 20, %s: 30',
	__( 'WooCommerce Tabs are this priority', 'yith-woocommerce-tab-manager' ),
	__( 'Description', 'yith-woocommerce-tab-manager' ),
	__( 'Additional Information', 'yith-woocommerce-tab-manager' ),
	__( 'Reviews', 'yith-woocommerce-tab-manager' ) );
$args          = array(
	'label'    => __( 'Tab Settings', 'yith-woocommerce-tab-manager' ),
	'pages'    => 'ywtm_tab', //or array( 'post-type1', 'post-type2')
	'context'  => 'normal', //('normal', 'advanced', or 'side')
	'priority' => 'default',
	'class' => yith_set_wrapper_class(),
	'tabs'     => array(
		'settings' => array(
			'label'  => __( 'Settings', 'yith-woocommerce-tab-manager' ),
			'fields' => apply_filters( 'ywtm_options_metabox', array(

					'ywtm_tab_type'     => array(
						'label'   => __( 'Tab Type', 'yith-woocommerce-tab-manager' ),
						'desc'    => __( 'Choose the type of the tab', 'yith-woocommerce-tab-manager' ),
						'type'    => 'select',
						'options' => ywtm_get_tab_types(),
						'std'     => 'global'
					),

					/*Option "chosen" if  tab_type=category*/
					'ywtm_tab_category' => array(
						'label'    => __( 'Choose Product Category', 'yith-woocommerce-tab-manager' ),
						'desc'     => __( 'Choose the product categories in which you want to show the tab', 'yith-woocommerce-tab-manager' ),
						'type'     => 'ajax-terms',
						'data'     => array(
							'taxonomy'    => 'product_cat',
							'placeholder' => __( 'Search for a category', 'yith-woocommerce-tab-manager' ),
						),
						'multiple' => true,
						'deps'     => array(
							'ids'    => '_ywtm_tab_type',
							'values' => 'category',
						),
					),

					/*Option "chosen" if tab_type=product*/
					'ywtm_tab_product'  => array(
						'label'    => __( 'Choose Product', 'yith-woocommerce-tab-manager' ),
						'desc'     => __( 'Choose the Products in which you want to show the tab', 'yith-woocommerce-tab-manager' ),
						'type'     => 'ajax-products',
						'multiple' => true,
						'std'      => array(),
						'data'     => array(
							'placeholder' => __( 'Search for a product', 'yith-woocommerce-tab-manager' ),
                            'show_id' => true
						),
						'deps'     => array(
							'ids'    => '_ywtm_tab_type',
							'values' => 'product',
						),

					),

					'ywtm_show_tab' => array(
						'label' => __( 'Enable Tab', 'yith-woocommerce-tab-manager' ),
						'desc'  => __( 'Show the tab in the front end', 'yith-woocommerce-tab-manager' ),
						'type'  => 'checkbox',
						'std'   => 1
					),

					'ywtm_order_tab' => array(
						'label' => __( 'Priority Tab', 'yith-woocommerce-tab-manager' ),
						'desc'  => $desc_priority,
						'type'  => 'number',
						'std'   => 1,
						'min'   => 1,
						'max'   => 99
					),

					'ywtm_icon_tab' => array(
						'label'   => __( 'Icon Tab', 'yith-woocommerce-tab-manager' ),
						'desc'    => '',
						'type'    => 'ywtb-iconlist-type',
						'options' => array(
							'select' => array(
								'icon'   => __( 'Theme Icon', 'yit' ),
								'custom' => __( 'Custom Icon', 'yit' ),
								'none'   => __( 'None', 'yit' )
							),
							'icon'   => ''
						),
						'std'     => array(
							'select' => 'icon',
							'icon'   => 'FontAwesome:envelope-o',
							'custom' => ''
						)
					),
				)

			),

		),


	)

);


return $args;