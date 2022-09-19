<?php
/**
 * Template for displaying the select-buttons field
 *
 * @var array $field The field.
 * @package YITH\PluginFramework\Templates\Fields
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

wp_enqueue_script( 'wc-enhanced-select' );

$field['type'] = 'select';

if ( empty( $field['class'] ) ) {
	unset( $field['class'] );
}
// 'add_all_button_label' is deprecated, use 'add_all_label' instead.
$add_all_label    = $field['add_all_label'] ?? $field['add_all_button_label'] ?? __( 'Add All', 'yith-plugin-fw' );
$remove_all_label = $field['remove_all_label'] ?? __( 'Remove All', 'yith-plugin-fw' );
$default_args     = array(
	'multiple' => true,
	'class'    => 'wc-enhanced-select',
	'buttons'  => array(
		array(
			'name'  => $add_all_label,
			'class' => 'yith-plugin-fw-select-all',
			'data'  => array(
				'select-id' => $field['id'],
			),
		),
		array(
			'name'  => $remove_all_label,
			'class' => 'yith-plugin-fw-deselect-all',
			'data'  => array(
				'select-id' => $field['id'],
			),
		),
	),
);

$field = wp_parse_args( $field, $default_args );

yith_plugin_fw_get_field( $field, true );
