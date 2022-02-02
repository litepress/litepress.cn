<?php
/**
 * Template for displaying the country-select field
 *
 * @var array $field The field.
 * @package YITH\PluginFramework\Templates\Fields
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

if ( ! function_exists( 'WC' ) ) {
	return;
}

list ( $field_id, $name, $class, $placeholder, $std, $value, $data, $custom_attributes ) = yith_plugin_fw_extract( $field, 'id', 'name', 'class', 'placeholder', 'std', 'value', 'data', 'custom_attributes' );

$placeholder     = isset( $placeholder ) ? ' data-placeholder = "' . $placeholder . '" ' : '';
$country_setting = (string) $value;

if ( strstr( $country_setting, ':' ) ) {
	$country_setting  = explode( ':', $country_setting );
	$selected_country = current( $country_setting );
	$selected_state   = end( $country_setting );
} else {
	$selected_country = $country_setting;
	$selected_state   = '*';
}
$countries = WC()->countries->get_countries();
$class     = isset( $class ) ? $class : 'yith-plugin-fw-select';
?>
<select id="<?php echo esc_attr( $field_id ); ?>"
		name="<?php echo esc_attr( $name ); ?>"
		class="wc-enhanced-select <?php echo esc_attr( $class ); ?>"
	<?php if ( isset( $std ) ) : ?>
		data-std="<?php echo esc_attr( $std ); ?>"
	<?php endif; ?>
	<?php if ( isset( $placeholder ) ) : ?>
		data-placeholder="<?php echo esc_attr( $placeholder ); ?>"
	<?php endif; ?>
	<?php yith_plugin_fw_html_attributes_to_string( $custom_attributes, true ); ?>
	<?php yith_plugin_fw_html_data_to_string( $data, true ); ?>
>
	<?php
	if ( $countries ) {
		foreach ( $countries as $key => $value ) {
			$states = WC()->countries->get_states( $key );
			if ( $states ) {
				echo '<optgroup label="' . esc_attr( $value ) . '">';
				foreach ( $states as $state_key => $state_value ) {
					echo '<option value="' . esc_attr( $key ) . ':' . esc_attr( $state_key ) . '"';

					if ( $selected_country === $key && $selected_state === $state_key ) {
						echo ' selected="selected"';
					}

					echo '>' . esc_html( $value ) . ' &mdash; ' . esc_html( $state_value ) . '</option>';
				}
				echo '</optgroup>';
			} else {
				echo '<option';
				if ( $selected_country === $key && '*' === $selected_state ) {
					echo ' selected="selected"';
				}
				echo ' value="' . esc_attr( $key ) . '">' . esc_html( $value ) . '</option>';
			}
		}
	}
	?>
</select>
