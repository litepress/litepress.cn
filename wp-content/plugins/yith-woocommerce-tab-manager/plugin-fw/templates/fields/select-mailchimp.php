<?php
/**
 * Template for displaying the select-mailchimp field
 *
 * @var array $field The field.
 * @package YITH\PluginFramework\Templates\Fields
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

list ( $field_id, $class, $name, $multiple, $std, $value, $options, $button_name, $custom_attributes, $data ) = yith_plugin_fw_extract( $field, 'id', 'class', 'name', 'multiple', 'std', 'value', 'options', 'button_name', 'custom_attributes', 'data' );

$multiple = ! empty( $multiple );
?>

<select id="<?php echo esc_attr( $field_id ); ?>"
		name="<?php echo esc_attr( $name ); ?>"
		class="yith-plugin-fw-select"

	<?php if ( $multiple ) : ?>
		multiple
	<?php endif; ?>

	<?php if ( isset( $std ) ) : ?>
		data-std="<?php echo $multiple && is_array( $std ) ? esc_attr( implode( ',', $std ) ) : esc_attr( $std ); ?>"
	<?php endif; ?>

	<?php yith_plugin_fw_html_attributes_to_string( $custom_attributes, true ); ?>
	<?php yith_plugin_fw_html_data_to_string( $data, true ); ?>
>
	<?php foreach ( $options as $key => $item ) : ?>
		<option value="<?php echo esc_attr( $key ); ?>"<?php selected( $key, $value ); ?>><?php echo esc_html( $item ); ?></option>
	<?php endforeach; ?>
</select>
<input type="button" class="button-secondary <?php echo isset( $class ) ? esc_attr( $class ) : ''; ?>" value="<?php echo esc_attr( $button_name ); ?>"/>
<span class="spinner"></span>
