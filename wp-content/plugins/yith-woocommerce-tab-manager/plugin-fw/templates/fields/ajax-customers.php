<?php
/**
 * Template for displaying the ajax-customers field
 * Note: the stored value is an array if WooCommerce >= 3.0; string otherwise
 *
 * @var array $field The field.
 * @package YITH\PluginFramework\Templates\Fields
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

yith_plugin_fw_enqueue_enhanced_select();

$default_field = array(
	'id'       => '',
	'name'     => '',
	'class'    => 'yith-customer-search',
	'no_value' => false,
	'multiple' => false,
	'data'     => array(),
	'style'    => 'width:400px',
	'value'    => '',
);

foreach ( $default_field as $field_key => $field_value ) {
	if ( empty( $field[ $field_key ] ) ) {
		$field[ $field_key ] = $field_value;
	}
}
unset( $field_key );
unset( $field_value );

list ( $field_id, $class, $no_value, $multiple, $data, $name, $style, $value ) = yith_plugin_fw_extract( $field, 'id', 'class', 'no_value', 'multiple', 'data', 'name', 'style', 'value' );

if ( $no_value ) {
	$value = array();
}

$default_data = array(
	'action'      => 'woocommerce_json_search_customers',
	'placeholder' => __( 'Search Customers', 'yith-plugin-fw' ),
	'allow_clear' => false,
);
$data         = wp_parse_args( $data, $default_data );
$show_id      = isset( $data['show_id'] ) && $data['show_id'];

// Separate select2 needed data and other data.
$select2_custom_attributes = array();
$select2_data              = array();
$select2_data_keys         = array( 'placeholder', 'allow_clear', 'action' );
foreach ( $data as $d_key => $d_value ) {
	if ( in_array( $d_key, $select2_data_keys, true ) ) {
		$select2_data[ $d_key ] = $d_value;
	} else {
		$select2_custom_attributes[ 'data-' . $d_key ] = $d_value;
	}
}

// Populate data-selected by value.
$data_selected = array();
if ( ! empty( $value ) ) {
	if ( $multiple ) {
		$value = is_array( $value ) ? $value : explode( ',', $value );
		foreach ( $value as $user_id ) {
			$user                      = get_user_by( 'id', $user_id );
			$data_selected[ $user_id ] = sprintf(
			// translators: 1. user display name; 2. user ID; 3. user email.
				esc_html__( '%1$s (#%2$s &ndash; %3$s)', 'woocommerce' ),
				$user->display_name,
				absint( $user->ID ),
				$user->user_email
			);
		}
	} else {
		$user_id                   = absint( $value );
		$user                      = get_user_by( 'id', $user_id );
		$data_selected[ $user_id ] = sprintf(
		// translators: 1. user display name; 2. user ID; 3. user email.
			esc_html__( '%1$s (#%2$s &ndash; %3$s)', 'woocommerce' ),
			$user->display_name,
			absint( $user->ID ),
			$user->user_email
		);
	}
}

// Parse $value to string to prevent issue with wc2.6.
$value = is_array( $value ) ? implode( ',', $value ) : $value;
?>
<div class="yith-plugin-fw-select2-wrapper">
	<?php
	if ( function_exists( 'yit_add_select2_fields' ) ) {
		yit_add_select2_fields(
			array(
				'id'                => $field_id,
				'name'              => $name,
				'class'             => $class,
				'data-multiple'     => $multiple,
				'data-placeholder'  => $select2_data['placeholder'],
				'data-allow_clear'  => $select2_data['allow_clear'],
				'data-action'       => $select2_data['action'],
				'custom-attributes' => $select2_custom_attributes,
				'style'             => $style,
				'value'             => $value,
				'data-selected'     => $data_selected,
			)
		);
	}
	?>
</div>
