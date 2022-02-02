<?php
/**
 * Template for displaying the radio field
 *
 * @var array $field The field.
 * @since   3.0.13
 * @package YITH\PluginFramework\Templates\Fields
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

list ( $field_id, $class, $name, $value, $options, $custom_attributes, $data ) = yith_plugin_fw_extract( $field, 'id', 'class', 'name', 'value', 'options', 'custom_attributes', 'data' );

$class = isset( $class ) ? $class : '';
$class = 'yith-plugin-fw-radio ' . $class;

$label_extra_allowed_tags = array(
	'input'  => array(
		'checked'     => true,
		'disabled'    => true,
		'max'         => true,
		'min'         => true,
		'name'        => true,
		'placeholder' => true,
		'type'        => true,
		'value'       => true,
	),
	'option' => array(
		'disabled' => true,
		'selected' => true,
		'value'    => true,
	),
	'select' => array(
		'disabled' => true,
		'name'     => true,
		'value'    => true,
	),
);

$label_extra_allowed_tags = array_map( 'yith_plugin_fw_add_kses_global_attributes', $label_extra_allowed_tags );

$label_allowed_tags = array_merge( wp_kses_allowed_html( 'post' ), $label_extra_allowed_tags );
$label_allowed_tags = apply_filters( 'yith_plugin_fw_radio_field_label_allowed_tags', $label_allowed_tags, $field );
?>
<div id="<?php echo esc_attr( $field_id ); ?>"
		class="<?php echo esc_attr( $class ); ?>"
		data-value="<?php echo esc_attr( $value ); ?>"
		data-type="radio"
	<?php yith_plugin_fw_html_attributes_to_string( $custom_attributes, true ); ?>
	<?php yith_plugin_fw_html_data_to_string( $data, true ); ?>
>
	<?php foreach ( $options as $key => $label ) : ?>
		<?php
		$radio_id = $field_id . '-' . sanitize_key( $key );
		?>
		<div class="yith-plugin-fw-radio__row">
			<input type="radio" id="<?php echo esc_attr( $radio_id ); ?>"
					name="<?php echo esc_attr( $name ); ?>"
					value="<?php echo esc_attr( $key ); ?>"
				<?php checked( $key, $value ); ?>
			/>
			<label for="<?php echo esc_attr( $radio_id ); ?>">
				<?php echo wp_kses( $label, $label_allowed_tags ); ?>
			</label>
		</div>
	<?php endforeach; ?>
</div>
