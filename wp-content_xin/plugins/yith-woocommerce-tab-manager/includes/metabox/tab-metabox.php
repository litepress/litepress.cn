<?php
/**
 * This is the tab metabox configuration
 *
 * @package YITH WooCommerce Tab Manager\Admin
 * @since 1.0.0
 */

$args = array(
	'label'    => __( 'Tab Settings', 'yith-woocommerce-tab-manager' ),
	'pages'    => 'ywtm_tab',
	'context'  => 'normal',
	'priority' => 'default',
	'tabs'     => array(
		'settings' => array(
			'label'  => __( 'Settings', 'yith-woocommerce-tab-manager' ),
			'fields' => apply_filters(
				'ywtm_options_metabox',
				array(
					'ywtm_text_tab'  => array(
						'label' => __( 'Content Tab', 'yith-woocommerce-tab-manager' ),
						'desc'  => '',
						'type'  => 'textarea-editor',
					),
					'ywtm_show_tab'  => array(
						'label' => __( 'Enable Tab', 'yith-woocommerce-tab-manager' ),
						'desc'  => __( 'Show Tab in frontend', 'yith-woocommerce-tab-manager' ),
						'type'  => 'checkbox',
						'std'   => 1,
					),

					'ywtm_order_tab' => array(
						'label' => __( 'Tab Priority', 'yith-woocommerce-tab-manager' ),
						'desc'  => __( 'The plugin tabs are displayed after the WooCommerce Tabs', 'yith-woocommerce-tab-manager' ),
						'type'  => 'number',
						'std'   => 1,
						'min'   => 1,
						'max'   => 99,
					),
				)
			),

		),

	),

);

return $args;
