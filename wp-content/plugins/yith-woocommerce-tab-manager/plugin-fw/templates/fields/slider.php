<?php
/**
 * Template for displaying the slider field
 *
 * @var array $field The field.
 * @package YITH\PluginFramework\Templates\Fields
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

list ( $field_id, $class, $name, $value,
	/**
	 * Array of option containing min and max value
	 * This is deprecated since 3.5 | use 'min' and 'max' instead.
	 */
	$option,
	$min, $max, $step, $custom_attributes, $data ) = yith_plugin_fw_extract( $field, 'id', 'class', 'name', 'value', 'option', 'min', 'max', 'step', 'custom_attributes', 'data' );

// Handle the deprecated attribute 'option': use 'min' and 'max' instead.
if ( ! isset( $min ) && isset( $option, $option['min'] ) ) {
	$min = $option['min'];
}

if ( ! isset( $max ) && isset( $option, $option['max'] ) ) {
	$max = $option['max'];
}

$min  = isset( $min ) ? $min : 0;
$max  = isset( $max ) ? $max : 100;
$step = isset( $step ) ? $step : 1;
?>
<div class="yith-plugin-fw-slider-container <?php echo ! empty( $class ) ? esc_attr( $class ) : ''; ?>">
	<div class="ui-slider">
		<span class="minCaption"><?php echo esc_html( $min ); ?></span>
		<div id="<?php echo esc_attr( $field_id ); ?>-div"
				class="ui-slider ui-slider-horizontal ui-widget ui-widget-content ui-corner-all"
				data-step="<?php echo esc_attr( $step ); ?>"
				data-min="<?php echo esc_attr( $min ); ?>"
				data-max="<?php echo esc_attr( $max ); ?>"
				data-val="<?php echo esc_attr( $value ); ?>"

			<?php yith_plugin_fw_html_attributes_to_string( $custom_attributes, true ); ?>
			<?php yith_plugin_fw_html_data_to_string( $data, true ); ?>
		>
			<input id="<?php echo esc_attr( $field_id ); ?>"
					type="hidden"
					name="<?php echo esc_attr( $name ); ?>"
					value="<?php echo esc_attr( $value ); ?>"
			/>
		</div>
		<span class="maxCaption"><?php echo esc_html( $max ); ?></span>
	</div>
</div>
