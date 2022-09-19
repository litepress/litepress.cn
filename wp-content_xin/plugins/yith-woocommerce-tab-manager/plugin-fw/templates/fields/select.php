<?php
/**
 * Template for displaying the select field
 *
 * @var array $field The field.
 * @package YITH\PluginFramework\Templates\Fields
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

list ( $field_id, $class, $name, $value, $options, $disabled_options, $multiple, $placeholder, $buttons, $custom_attributes, $data ) = yith_plugin_fw_extract( $field, 'id', 'class', 'name', 'value', 'options', 'disabled_options', 'multiple', 'placeholder', 'buttons', 'custom_attributes', 'data' );

$multiple         = ! empty( $multiple );
$class            = $class ?? 'yith-plugin-fw-select';
$name             = $name ?? '';
$name             = ! ! $name && $multiple ? $name . '[]' : $name;
$disabled_options = $disabled_options ?? array();

if ( $multiple && ! is_array( $value ) ) {
	$value = array();
}

?>
<select id="<?php echo esc_attr( $field_id ); ?>"
		name="<?php echo esc_attr( $name ); ?>"
		class="<?php echo esc_attr( $class ); ?>"
		data-value="<?php echo $multiple ? esc_attr( implode( ',', $value ) ) : esc_attr( $value ); ?>"

	<?php if ( $multiple ) : ?>
		multiple
	<?php endif; ?>

	<?php if ( isset( $std ) ) : ?>
		data-std="<?php echo $multiple && is_array( $std ) ? esc_attr( implode( ',', $std ) ) : esc_attr( $std ); ?>"
	<?php endif; ?>

	<?php if ( isset( $placeholder ) ) : ?>
		data-placeholder="<?php echo esc_attr( $placeholder ); ?>"
	<?php endif; ?>

	<?php yith_plugin_fw_html_attributes_to_string( $custom_attributes, true ); ?>
	<?php yith_plugin_fw_html_data_to_string( $data, true ); ?>
>
	<?php foreach ( $options as $key => $item ) : ?>
		<?php if ( is_array( $item ) ) : ?>
			<optgroup label="<?php echo esc_attr( $item['label'] ); ?>">
				<?php foreach ( $item['options'] as $option_key => $option ) : ?>
					<option value="<?php echo esc_attr( $option_key ); ?>"
						<?php
						if ( $multiple ) {
							selected( true, in_array( $option_key, $value ) ); // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
						} else {
							selected( $option_key, $value );
						}

						disabled( true, in_array( $option_key, $disabled_options, true ) );
						?>
					><?php echo esc_html( $option ); ?></option>
				<?php endforeach; ?>
			</optgroup>
		<?php else : ?>
			<option value="<?php echo esc_attr( $key ); ?>"
				<?php
				if ( $multiple ) {
					selected( true, in_array( $key, $value ) ); // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
				} else {
					selected( $key, $value );
				}

				disabled( true, in_array( $key, $disabled_options, true ) );
				?>
			><?php echo esc_html( $item ); ?></option>
		<?php endif; ?>
	<?php endforeach; ?>
</select>

<?php
// Let's add buttons if they are set.
if ( isset( $buttons ) ) {
	$button_field = array(
		'type'    => 'buttons',
		'buttons' => $buttons,
	);
	yith_plugin_fw_get_field( $button_field, true );
}
?>
