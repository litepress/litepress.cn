<?php
/**
 * Template for displaying the textarea field
 *
 * @var array $field The field.
 * @package YITH\PluginFramework\Templates\Fields
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

list ( $field_id, $class, $name, $value, $std, $rows, $cols, $custom_attributes, $data ) = yith_plugin_fw_extract( $field, 'id', 'class', 'name', 'value', 'std', 'rows', 'cols', 'custom_attributes', 'data' );

$class = isset( $class ) ? $class : 'yith-plugin-fw-textarea';
$rows  = isset( $rows ) ? $rows : 5;
$cols  = isset( $cols ) ? $cols : 50;
?>
<textarea id="<?php echo esc_attr( $field_id ); ?>"
		name="<?php echo esc_attr( $name ); ?>"
		class="<?php echo esc_attr( $class ); ?>"
		rows="<?php echo esc_attr( $rows ); ?>"
		cols="<?php echo esc_attr( $cols ); ?>"

	<?php if ( isset( $std ) ) : ?>
		data-std="<?php echo esc_attr( $std ); ?>"
	<?php endif; ?>

	<?php yith_plugin_fw_html_attributes_to_string( $custom_attributes, true ); ?>
	<?php yith_plugin_fw_html_data_to_string( $data, true ); ?>
><?php echo esc_textarea( $value ); ?></textarea>
