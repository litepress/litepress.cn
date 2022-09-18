<?php
/**
 * Template for displaying the datepicker field
 *
 * @var array $field The field.
 * @package YITH\PluginFramework\Templates\Fields
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

list ( $field_id, $class, $name, $value, $data, $custom_attributes ) = yith_plugin_fw_extract( $field, 'id', 'class', 'name', 'value', 'data', 'custom_attributes' );

$class = ! empty( $class ) ? $class : 'yith-plugin-fw-datepicker';
?>
<input type="text"
		name="<?php echo esc_attr( $name ); ?>"
		id="<?php echo esc_attr( $field_id ); ?>"
		value="<?php echo esc_attr( $value ); ?>"
		class="<?php echo esc_attr( $class ); ?>"
		autocomplete="off"
	<?php yith_plugin_fw_html_attributes_to_string( $custom_attributes, true ); ?>
	<?php yith_plugin_fw_html_data_to_string( $data, true ); ?>
/>
