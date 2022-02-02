<?php
/**
 * Template for displaying the icons field
 *
 * @var array $field The field.
 * @package YITH\PluginFramework\Templates\Fields
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

list ( $field_id, $name, $filter_icons, $std, $value ) = yith_plugin_fw_extract( $field, 'id', 'name', 'filter_icons', 'std', 'value' );

wp_enqueue_style( 'font-awesome' );

$filter_icons      = ! ! $filter_icons ? $filter_icons : '';
$default_icon_text = isset( $std ) ? $std : false;
$default_icon_data = YIT_Icons()->get_icon_data_array( $default_icon_text, $filter_icons );
$default_icon      = '';
if ( isset( $default_icon_data['icon'] ) ) {
	$default_icon = $default_icon_data['icon'];
	$default_icon = str_replace( '&#x', '', $default_icon );
	unset( $default_icon_data['icon'] );
}

$current_icon_data = YIT_Icons()->get_icon_data_array( $value, $filter_icons );
$current_icon_text = $value;
$current_icon      = '';
if ( isset( $current_icon_data['icon'] ) ) {
	$current_icon = $current_icon_data['icon'];
	$current_icon = str_replace( '&#x', '', $current_icon );
	unset( $current_icon_data['icon'] );
}

$yit_icons = YIT_Icons()->get_icons( $filter_icons );
?>

<div id="yit-icons-manager-wrapper-<?php echo esc_attr( $field_id ); ?>" class="yit-icons-manager-wrapper">

	<div class="yit-icons-manager-text">
		<div class="yit-icons-manager-icon-preview"
			<?php echo yith_plugin_fw_html_data_to_string( $current_icon_data ); ?>
			<?php if ( $current_icon ) : ?>
				data-icon="&#x<?php echo esc_attr( $current_icon ); ?>"
			<?php endif; ?>
		></div>
		<input class="yit-icons-manager-icon-text" type="text"
				id="<?php echo esc_attr( $field_id ); ?>"
				name="<?php echo esc_attr( $name ); ?>"
				value="<?php echo esc_attr( $current_icon_text ); ?>"
		/>
		<div class="clear"></div>
	</div>


	<div class="yit-icons-manager-list-wrapper">
		<ul class="yit-icons-manager-list">
			<?php foreach ( $yit_icons as $font => $icons ) : ?>
				<?php foreach ( $icons as $key => $icon_name ) : ?>
					<?php
					$data_icon  = str_replace( '\\', '', $key );
					$icon_text  = $font . ':' . $icon_name;
					$icon_class = $icon_text === $current_icon_text ? 'active' : '';

					$icon_class .= $icon_text === $default_icon_text ? ' default' : '';
					?>
					<li class="<?php echo esc_attr( $icon_class ); ?>"
							data-font="<?php echo esc_attr( $font ); ?>"
							data-icon="&#x<?php echo esc_attr( $data_icon ); ?>"
							data-key="<?php echo esc_attr( $key ); ?>"
							data-name="<?php echo esc_attr( $icon_name ); ?>"></li>
				<?php endforeach; ?>
			<?php endforeach; ?>
		</ul>
	</div>

	<div class="yit-icons-manager-actions">
		<?php if ( $default_icon_text ) : ?>
			<div class="yit-icons-manager-action-set-default button"><?php esc_html_e( 'Set Default', 'yith-plugin-fw' ); ?>
				<i class="yit-icons-manager-default-icon-preview"
					<?php echo yith_plugin_fw_html_data_to_string( $default_icon_data ); ?>
					<?php if ( $default_icon ) : ?>
						data-icon="&#x<?php echo esc_attr( $default_icon ); ?>"
					<?php endif; ?>
				></i>
			</div>
		<?php endif ?>
	</div>
</div>
