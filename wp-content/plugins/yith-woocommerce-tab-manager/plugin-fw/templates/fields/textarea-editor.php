<?php
/**
 * Template for displaying the textarea-editor field
 *
 * @var array $field The field.
 * @package YITH\PluginFramework\Templates\Fields
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

list ( $field_id, $class, $name, $value, $custom_attributes, $data ) = yith_plugin_fw_extract( $field, 'id', 'class', 'name', 'value', 'custom_attributes', 'data' );

// Handle deprecated param 'classes' (since 3.5): use 'class' instead.
if ( isset( $field['classes'] ) && ! isset( $class ) ) {
	$class = $field['classes'];
}

if ( ! function_exists( 'wp_editor' ) ) {
	$field['type'] = 'textarea';
	yith_plugin_fw_get_field( $field, true, false );

	return;
}

$class = isset( $class ) ? $class : '';

$editor_args = wp_parse_args(
	$field,
	array(
		'wpautop'       => true, // Choose if you want to use wpautop.
		'media_buttons' => true, // Choose if showing media button(s).
		'textarea_name' => $name, // Set the textarea name to something different, square brackets [] can be used here.
		'textarea_rows' => 20, // Set the number of rows.
		'tabindex'      => '',
		'editor_css'    => '', // Intended for extra styles for both visual and HTML editors buttons, needs to include the <style> tags, can use "scoped".
		'editor_class'  => '', // Add extra class(es) to the editor textarea.
		'teeny'         => false, // Output the minimal editor config used in Press This.
		'dfw'           => false, // Replace the default fullscreen with DFW (needs specific DOM elements and css).
		'tinymce'       => true, // Load TinyMCE, can be used to pass settings directly to TinyMCE using an array().
		'quicktags'     => true, // Load Quicktags, can be used to pass settings directly to Quicktags using an array().
	)
);
?>
<div class="editor <?php echo esc_attr( $class ); ?>"
	<?php yith_plugin_fw_html_attributes_to_string( $custom_attributes, true ); ?>
	<?php yith_plugin_fw_html_data_to_string( $data, true ); ?>
><?php wp_editor( $value, $field_id, $editor_args ); ?></div>
