<?php
/**
 * Template for displaying the onoff field
 *
 * @var array $field The field.
 * @package YITH\PluginFramework\Templates\Fields
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

list ( $field_id, $class, $name, $std, $value, $custom_attributes, $data, $desc_inline ) = yith_plugin_fw_extract( $field, 'id', 'class', 'name', 'std', 'value', 'custom_attributes', 'data', 'desc-inline' );

?>
<div class="yith-plugin-fw-onoff-container <?php echo ! empty( $class ) ? esc_attr( $class ) : ''; ?>"
	<?php yith_plugin_fw_html_data_to_string( $data, true ); ?>
>
	<input type="checkbox" id="<?php echo esc_attr( $field_id ); ?>"
			class="on_off"
			name="<?php echo esc_attr( $name ); ?>"
			value="<?php echo esc_attr( $value ); ?>"
		<?php if ( isset( $std ) ) : ?>
			data-std="<?php echo esc_attr( $std ); ?>"
		<?php endif; ?>
		<?php checked( true, yith_plugin_fw_is_true( $value ) ); ?>
		<?php yith_plugin_fw_html_attributes_to_string( $custom_attributes, true ); ?>
	/>
	<span class="yith-plugin-fw-onoff"
			data-text-on="<?php echo esc_attr_x( 'YES', 'YES/NO button: use MAX 4 characters!', 'yith-plugin-fw' ); ?>"
			data-text-off="<?php echo esc_attr_x( 'NO', 'YES/NO button: use MAX 4 characters!', 'yith-plugin-fw' ); ?>"></span>
</div>

<?php if ( isset( $desc_inline ) ) : ?>
	<span class='description inline'><?php echo wp_kses_post( $desc_inline ); ?></span>
<?php endif; ?>
