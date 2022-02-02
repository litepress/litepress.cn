<?php
/**
 * Template for displaying the text-button field
 *
 * @var array $field The field.
 * @package YITH\PluginFramework\Templates\Fields
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

list ( $field_id, $class, $name, $value, $std, $buttons, $custom_attributes, $data ) = yith_plugin_fw_extract( $field, 'id', 'class', 'name', 'value', 'std', 'buttons', 'custom_attributes', 'data' );

$backward_compatibility = false;
if ( ! isset( $buttons ) ) {
	$backward_compatibility = true;
	$button_data            = array();

	if ( isset( $field['button-class'] ) ) {
		$button_data['class'] = $field['button-class'];
	}
	if ( isset( $field['button-name'] ) ) {
		$button_data['name'] = $field['button-name'];
	}
	if ( isset( $field['data'] ) ) {
		$button_data['data'] = $field['data'];
	}

	$buttons = array( $button_data );
}
$class = isset( $class ) ? $class : 'yith-plugin-fw-text-input';
?>
<input type="text"
		id="<?php echo esc_attr( $field_id ); ?>"
		name="<?php echo esc_attr( $name ); ?>"
		class="<?php echo esc_attr( $class ); ?>"
		value="<?php echo esc_attr( $value ); ?>"

	<?php if ( isset( $std ) ) : ?>
		data-std="<?php echo esc_attr( $std ); ?>"
	<?php endif; ?>

	<?php yith_plugin_fw_html_attributes_to_string( $custom_attributes, true ); ?>
	<?php
	if ( ! $backward_compatibility ) {
		yith_plugin_fw_html_data_to_string( $data, true );
	}
	?>
/>
<?php
if ( isset( $buttons ) ) {
	$button_field = array(
		'type'    => 'buttons',
		'buttons' => $buttons,
	);
	yith_plugin_fw_get_field( $button_field, true );
}
?>
