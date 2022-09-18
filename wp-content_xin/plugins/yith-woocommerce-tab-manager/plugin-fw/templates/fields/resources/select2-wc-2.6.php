<?php
/**
 * The Template for displaying the select2 field, just for WooCommerce < 3.0.
 *
 * @var array  $args              Array of arguments.
 * @var string $custom_attributes Custom attributes.
 * @package YITH\PluginFramework\Templates\Fields\Resources
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

?>
<input
		type="hidden"
		id="<?php echo esc_attr( $args['id'] ); ?>"
		class="<?php echo esc_attr( $args['class'] ); ?>"
		name="<?php echo esc_attr( $args['name'] ); ?>"
		data-placeholder="<?php echo esc_attr( $args['data-placeholder'] ); ?>"
		data-allow_clear="<?php echo esc_attr( $args['data-allow_clear'] ); ?>"
		data-selected="<?php echo is_array( $args['data-selected'] ) ? esc_attr( wp_json_encode( $args['data-selected'] ) ) : esc_attr( $args['data-selected'] ); ?>"
		data-multiple="<?php echo ! ! $args['data-multiple'] ? 'true' : 'false'; ?>"
	<?php echo( ! empty( $args['data-action'] ) ? 'data-action="' . esc_attr( $args['data-action'] ) . '"' : '' ); ?>
		value="<?php echo esc_attr( $args['value'] ); ?>"
		style="<?php echo esc_attr( $args['style'] ); ?>"
	<?php yith_plugin_fw_html_attributes_to_string( $custom_attributes, true ); ?>
/>
