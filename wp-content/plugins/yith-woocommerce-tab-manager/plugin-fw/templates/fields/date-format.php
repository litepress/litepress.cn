<?php
/**
 * Template for displaying the date-format field
 *
 * @var array $field The field.
 * @since   3.1.30
 * @package YITH\PluginFramework\Templates\Fields
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

list ( $field_id, $class, $name, $js, $format, $value, $data, $custom_attributes ) = yith_plugin_fw_extract( $field, 'id', 'class', 'name', 'js', 'format', 'value', 'data', 'custom_attributes' );

$class = isset( $class ) ? $class : '';
$class = 'yith-plugin-fw-radio yith-plugin-fw-date-format ' . $class;

$format  = isset( $format ) ? $format : 'date';
$options = 'time' === $format ? yith_get_time_formats() : yith_get_date_formats( $js );
$custom  = true;
$js      = isset( $js ) && 'date' === $format ? $js : false;

$data            = isset( $data ) ? $data : array();
$data['current'] = date_i18n( 'Y-m-d H:i:s' );
$data['js']      = ! ! $js ? 'yes' : 'no';
$data['format']  = $format;

$loop = 0;

wp_enqueue_script( 'yith-date-format' );
?>
<div class="<?php echo esc_attr( $class ); ?>"
		id="<?php echo esc_attr( $field_id ); ?>"
		value="<?php echo esc_attr( $value ); ?>"
	<?php yith_plugin_fw_html_attributes_to_string( $custom_attributes, true ); ?>
	<?php yith_plugin_fw_html_data_to_string( $data, true ); ?>
>
	<?php foreach ( $options as $key => $label ) : ?>
		<?php
		$loop ++;
		$checked  = '';
		$radio_id = $field_id . '-' . $loop . '-' . sanitize_key( $key );
		if ( $value === $key ) { // checked() doesn't use strict comparison.
			$checked = " checked='checked'";
			$custom  = false;
		}
		?>
		<div class="yith-plugin-fw-radio__row">
			<input type="radio" id="<?php echo esc_attr( $radio_id ); ?>" name="<?php echo esc_attr( $name ); ?>"
					class="yith-plugin-fw-date-format__option"
					value="<?php echo esc_attr( $key ); ?>" <?php echo esc_html( $checked ); ?>
					data-preview="<?php echo esc_attr( date_i18n( $label ) ); ?>"
			/>
			<label for="<?php echo esc_attr( $radio_id ); ?>">
				<?php echo esc_html( date_i18n( $label ) ); ?>
				<code><?php echo esc_html( $key ); ?></code>
			</label>
		</div>
	<?php endforeach; ?>
	<?php $radio_id = $field_id . '-custom'; ?>
	<div class="yith-plugin-fw-radio__row">
		<input type="radio" id="<?php echo esc_attr( $radio_id ); ?>" name="<?php echo esc_attr( $name ); ?>"
				value="\c\u\s\t\o\m" <?php checked( $custom ); ?>
				data-format-custom="<?php echo esc_attr( $value ); ?>"
		/>
		<label for="<?php echo esc_attr( $radio_id ); ?>"> <?php esc_html_e( 'Custom:', 'yith-plugin-fw' ); ?></label>
		<input type="text" name="<?php echo esc_attr( $name . '_text' ); ?>"
				id="<?php echo esc_attr( $radio_id ); ?>_text" value="<?php echo esc_attr( $value ); ?>"
				class="small-text yith-date-format-custom"/>
		<p>
			<strong><?php esc_html_e( 'Preview:', 'yith-plugin-fw' ); ?></strong>
			<span class="example"><?php echo ! $js ? esc_html( date_i18n( $value ) ) : ''; ?></span>
			<span class="spinner"></span>
		</p>
	</div>
</div>
