<?php
/**
 * Template for displaying the hidden field
 *
 * @var array $field The field.
 * @package YITH\PluginFramework\Templates\Fields
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

list ( $field_id, $class, $name, $value, $data, $custom_attributes ) = yith_plugin_fw_extract( $field, 'id', 'class', 'name', 'value', 'data', 'custom_attributes' );

$class = ! ! $class ? $class : '';

// Backward compatibility.
if ( ! isset( $value ) ) {
	if ( isset( $field['val'] ) ) {
		$value = $field['val'];
	} else {
		$value = '';
	}
}
?>
<input type="hidden" id="<?php echo esc_attr( $field_id ); ?>"
		name="<?php echo esc_attr( $name ); ?>"
		class="<?php echo esc_attr( $class ); ?>"
		value="<?php echo esc_attr( $value ); ?>"
	<?php if ( isset( $std ) ) : ?>
		data-std="<?php echo esc_attr( $std ); ?>"
	<?php endif; ?>
	<?php yith_plugin_fw_html_attributes_to_string( $custom_attributes, true ); ?>
	<?php yith_plugin_fw_html_data_to_string( $data, true ); ?>
/>
