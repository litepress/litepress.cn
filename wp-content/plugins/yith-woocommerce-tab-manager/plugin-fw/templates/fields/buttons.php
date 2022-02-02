<?php
/**
 * Template for displaying the buttons field
 *
 * @var array $field The field.
 * @package YITH\PluginFramework\Templates\Fields
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

list ( $buttons ) = yith_plugin_fw_extract( $field, 'buttons' );
?>
<?php if ( ! empty( $buttons ) && is_array( $buttons ) ) : ?>
	<?php foreach ( $buttons as $button ) : ?>
		<?php
		$button_default_args = array(
			'name'  => '',
			'class' => '',
			'data'  => array(),
		);
		$button              = wp_parse_args( $button, $button_default_args );
		list ( $button_class, $button_name, $button_data ) = yith_plugin_fw_extract( $button, 'class', 'name', 'data' );
		?>
		<input type="button" class="<?php echo esc_attr( $button_class ); ?> button button-secondary"
				value="<?php echo esc_attr( $button_name ); ?>" <?php echo yith_plugin_fw_html_data_to_string( $button_data ); ?>/>
	<?php endforeach; ?>
<?php endif; ?>
