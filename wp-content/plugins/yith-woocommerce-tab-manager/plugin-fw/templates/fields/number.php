<?php
/**
 * Template for displaying the number field
 *
 * @var array $field The field.
 * @package YITH\PluginFramework\Templates\Fields
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

list ( $field_id, $class, $name, $std, $value, $min, $max, $step, $custom_attributes, $data ) = yith_plugin_fw_extract( $field, 'id', 'class', 'name', 'std', 'value', 'min', 'max', 'step', 'custom_attributes', 'data' );

?>
<input type="number" id="<?php echo esc_attr( $field_id ); ?>"
		name="<?php echo esc_attr( $name ); ?>"
		class="<?php echo ! empty( $class ) ? esc_attr( $class ) : ''; ?>"
		value="<?php echo esc_attr( $value ); ?>"
	<?php if ( isset( $min ) ) : ?>
		min="<?php echo esc_attr( $min ); ?>"
	<?php endif; ?>
	<?php if ( isset( $max ) ) : ?>
		max="<?php echo esc_attr( $max ); ?>"
	<?php endif; ?>
	<?php if ( isset( $step ) ) : ?>
		step="<?php echo esc_attr( $step ); ?>"
	<?php endif; ?>
	<?php if ( isset( $std ) ) : ?>
		data-std="<?php echo esc_attr( $std ); ?>"
	<?php endif; ?>
	<?php yith_plugin_fw_html_attributes_to_string( $custom_attributes, true ); ?>
	<?php yith_plugin_fw_html_data_to_string( $data, true ); ?>
/>
