<?php
/**
 * Template for displaying the image-dimensions field
 *
 * @var array $field The field.
 * @package YITH\PluginFramework\Templates\Fields
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

list ( $field_id, $class, $name, $value ) = yith_plugin_fw_extract( $field, 'id', 'class', 'name', 'value' );

$value = isset( $value ) && is_array( $value ) ? $value : array();

?>
<div class="yith-plugin-fw-image-dimensions" id="<?php echo esc_attr( $field_id ); ?>">
	<div class="yith-image-width">
		<label for="<?php echo esc_attr( $name . '_width' ); ?>"><?php echo esc_html_x( 'Width', 'Image width field label', 'yith-plugin-fw' ); ?></label>
		<input type="number" id="<?php echo esc_attr( $name . '_width' ); ?>" name="<?php echo esc_attr( $name . '[width]' ); ?>"
				value="<?php echo isset( $value['width'] ) ? absint( $value['width'] ) : 0; ?>" step="1" min="0" />
	</div>
	<div class="yith-image-height">
		<label for="<?php echo esc_attr( $name . '_height' ); ?>"><?php echo esc_html_x( 'Height', 'Image height field label', 'yith-plugin-fw' ); ?></label>
		<input type="number" id="<?php echo esc_attr( $name . '_height' ); ?>" name="<?php echo esc_attr( $name . '[height]' ); ?>"
				value="<?php echo isset( $value['height'] ) ? absint( $value['height'] ) : 0; ?>" step="1" min="0" />
	</div>
</div>
