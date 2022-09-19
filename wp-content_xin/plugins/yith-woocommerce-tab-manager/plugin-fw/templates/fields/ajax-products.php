<?php
/**
 * Template for displaying the ajax-products field
 *
 * @var array $field The field.
 * @package YITH\PluginFramework\Templates\Fields
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

list ( $data ) = yith_plugin_fw_extract( $field, 'data' );

$field['type'] = 'ajax-posts';
$field_data    = array(
	'post_type'   => 'product',
	'placeholder' => __( 'Search for a product...', 'yith-plugin-fw' ),
	'action'      => 'yith_plugin_fw_json_search_products',
);
if ( ! ! $data ) {
	$field_data = wp_parse_args( $data, $field_data );
}

$field['data'] = $field_data;

yith_plugin_fw_get_field( $field, true );
