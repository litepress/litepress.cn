<?php
/**
 * Template for displaying the colorpicker field
 *
 * @var array $field The field.
 * @package YITH\PluginFramework\Templates\Fields
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

wp_enqueue_style( 'wp-color-picker' );

list ( $field_id, $name, $class, $default, $alpha_enabled, $value, $data, $custom_attributes ) = yith_plugin_fw_extract( $field, 'id', 'name', 'class', 'default', 'alpha_enabled', 'value', 'data', 'custom_attributes' );

$class         = ! empty( $class ) ? $class : 'yith-plugin-fw-colorpicker color-picker';
$alpha_enabled = isset( $alpha_enabled ) ? $alpha_enabled : true;
$default       = isset( $default ) ? $default : '';
?>
<input type="text"
		name="<?php echo esc_attr( $name ); ?>"
		id="<?php echo esc_attr( $field_id ); ?>"
		class="<?php echo esc_attr( $class ); ?>"
		value="<?php echo esc_attr( $value ); ?>"
		data-alpha-enabled="<?php echo $alpha_enabled ? 'true' : 'false'; ?>"
	<?php if ( $default ) : ?>
		data-default-color="<?php echo esc_attr( $default ); ?>"
	<?php endif ?>
	<?php yith_plugin_fw_html_attributes_to_string( $custom_attributes, true ); ?>
	<?php yith_plugin_fw_html_data_to_string( $data, true ); ?>
/>
