<?php
/**
 * Template for displaying the inline-fields field
 *
 * @var array $field The field.
 * @package YITH\PluginFramework\Templates\Fields
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

list ( $field_id, $name, $class, $fields, $value, $data, $custom_attributes ) = yith_plugin_fw_extract( $field, 'id', 'name', 'class', 'fields', 'value', 'data', 'custom_attributes' );

$class         = ! ! $class ? $class : '';
$value         = maybe_unserialize( $value );
$allowed_types = apply_filters( 'yith_plugin_fw_inline_fields_allowed_types', array( 'select', 'select-buttons', 'number', 'text', 'slider', 'hidden', 'html', 'datepicker' ), $name, $field );
$default_args  = array( 'type' => 'select' );
?>
<?php if ( ! empty( $fields ) && is_array( $fields ) ) : ?>
	<div id="<?php echo esc_attr( $field_id ); ?>" class="<?php echo esc_attr( $class ); ?> yith-inline-fields"
		<?php yith_plugin_fw_html_attributes_to_string( $custom_attributes, true ); ?>
		<?php yith_plugin_fw_html_data_to_string( $data, true ); ?>
	>
		<?php foreach ( $fields as $key => $inline_field ) : ?>
			<?php
			if ( ! in_array( $inline_field['type'], $allowed_types, true ) ) {
				continue;
			}

			if ( ! isset( $inline_field['default'] ) && isset( $inline_field['std'] ) ) {
				$inline_field['default'] = $inline_field['std'];
			}
			$default = isset( $inline_field['default'] ) ? $inline_field['default'] : '';

			$inline_field['value'] = isset( $value[ $key ] ) ? maybe_unserialize( $value[ $key ] ) : $default;
			$inline_field['class'] = isset( $inline_field['class'] ) ? $inline_field['class'] : '';
			$inline_field['id']    = $field_id . '_' . $key;
			$inline_field['name']  = $name . '[' . $key . ']';

			if ( in_array( $inline_field['type'], array( 'select', 'select-buttons' ), true ) ) {
				$inline_field['class'] .= ' wc-enhanced-select';
			}
			?>
			<?php if ( ! empty( $inline_field['inline-label'] ) ) : ?>
				<div class="option-element">
					<span><?php echo esc_html( $inline_field['inline-label'] ); ?></span>
				</div>
			<?php endif; ?>
			<div class="option-element <?php echo esc_attr( $inline_field['type'] ); ?> <?php echo esc_attr( $inline_field['class'] ); ?>">
				<?php if ( isset( $inline_field['label'] ) && '' !== $inline_field['label'] ) : ?>
					<label for="<?php echo esc_attr( $inline_field['id'] ); ?>"><?php echo esc_html( $inline_field['label'] ); ?></label>
				<?php endif; ?>
				<?php yith_plugin_fw_get_field( $inline_field, true ); ?>
			</div>
		<?php endforeach; ?>
	</div>
<?php endif; ?>
