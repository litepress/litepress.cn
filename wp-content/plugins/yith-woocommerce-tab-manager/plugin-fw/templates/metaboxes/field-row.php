<?php
/**
 * The Template for displaying field rows in meta-boxes.
 *
 * @var array $field The field.
 * @package YITH\PluginFramework\Templates
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

// Meta-box backward compatibility.
if ( isset( $field['label'] ) ) {
	$field['title'] = $field['label'];
}

$default_field = array(
	'id'    => '',
	'title' => isset( $field['name'] ) ? $field['name'] : '',
	'desc'  => '',
);
$field         = wp_parse_args( $field, $default_field );

$display_field_only = isset( $field['display-field-only'] ) ? $field['display-field-only'] : false;
$is_required        = ! empty( $field['required'] );

$extra_row_classes = $is_required ? array( 'yith-plugin-fw--required' ) : array();
$extra_row_classes = apply_filters( 'yith_plugin_fw_metabox_extra_row_classes', $extra_row_classes, $field );
$extra_row_classes = is_array( $extra_row_classes ) ? implode( ' ', $extra_row_classes ) : '';

?>
<div id="<?php echo esc_attr( $field['id'] ); ?>-container" <?php echo yith_field_deps_data( $field ); ?> class="yith-plugin-fw-metabox-field-row <?php echo esc_attr( $extra_row_classes ); ?>">
	<?php if ( $display_field_only ) : ?>
		<?php yith_plugin_fw_get_field( $field, true ); ?>
	<?php else : ?>
		<label for="<?php echo esc_attr( $field['id'] ); ?>"><?php echo wp_kses_post( $field['title'] ); ?></label>
		<?php yith_plugin_fw_get_field( $field, true ); ?>
		<div class="clear"></div>
		<span class="description"><?php echo wp_kses_post( $field['desc'] ); ?></span>
	<?php endif; ?>
</div>
