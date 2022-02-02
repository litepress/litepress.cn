<?php
/**
 * Template for displaying the title field
 *
 * @var array $field The field.
 * @package YITH\PluginFramework\Templates\Fields
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

list ( $field_id, $class, $name, $desc, $std, $custom_attributes, $data ) = yith_plugin_fw_extract( $field, 'id', 'class', 'name', 'desc', 'std', 'custom_attributes', 'data' );

$class = isset( $class ) ? $class : 'title';
?>
<h3 id="<?php echo esc_attr( $field_id ); ?>"
		class="<?php echo esc_attr( $class ); ?>"

	<?php yith_plugin_fw_html_attributes_to_string( $custom_attributes, true ); ?>
	<?php yith_plugin_fw_html_data_to_string( $data, true ); ?>
>
	<?php echo wp_kses_post( $desc ); ?>
</h3>
