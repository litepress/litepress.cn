<?php
/**
 * Template for displaying the action-button component
 *
 * @var array $component The component.
 * @package YITH\PluginFramework\Templates\Components
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

list ( $component_id, $class, $the_title, $attributes, $data, $button_action, $icon, $icon_class, $url, $action_button_menu, $confirm_data ) = yith_plugin_fw_extract( $component, 'id', 'class', 'title', 'attributes', 'data', 'action', 'icon', 'icon_class', 'url', 'menu', 'confirm_data' );

$button_action      = isset( $button_action ) ? $button_action : '';
$icon               = isset( $icon ) ? $icon : $button_action;
$icon_class         = isset( $icon_class ) ? $icon_class : "yith-icon yith-icon-{$icon}";
$url                = isset( $url ) ? $url : '#';
$class              = isset( $class ) ? $class : '';
$the_title          = isset( $the_title ) ? $the_title : '';
$action_button_menu = isset( $action_button_menu ) ? $action_button_menu : array();
$confirm_data       = isset( $confirm_data ) ? $confirm_data : array();

$classes = array( 'yith-plugin-fw__action-button', "yith-plugin-fw__action-button--{$button_action}-action", $class );

if ( ! ! $action_button_menu ) {
	$classes[] = 'yith-plugin-fw__action-button--has-menu';
}

$link_classes = array( 'yith-plugin-fw__action-button__link' );
$link_data    = array();
if ( isset( $confirm_data['title'], $confirm_data['message'] ) && '#' !== $url ) {
	$link_classes[] = 'yith-plugin-fw__require-confirmation-link';
	$link_data      = $confirm_data;
}

if ( $the_title ) {
	$link_classes[] = 'yith-plugin-fw__tips';
}

$class      = implode( ' ', $classes );
$link_class = implode( ' ', array_filter( $link_classes ) );
?>
<span
		id="<?php echo esc_attr( $component_id ); ?>"
		class="<?php echo esc_attr( $class ); ?>"
	<?php echo yith_plugin_fw_html_attributes_to_string( $attributes ); ?>
	<?php echo yith_plugin_fw_html_data_to_string( $data ); ?>
	>
<a class="<?php echo esc_attr( $link_class ); ?>"

		href="<?php echo esc_url( $url ); ?>"
	<?php if ( $the_title ) : ?>
		data-tip="<?php echo esc_attr( $the_title ); ?>"
	<?php endif; ?>

	<?php yith_plugin_fw_html_data_to_string( $link_data, true ); ?>
>
	<?php if ( $icon ) : ?>
		<i class="yith-plugin-fw__action-button__icon <?php echo esc_attr( $icon_class ); ?>"></i>
	<?php endif; ?>
</a>
	<?php if ( $action_button_menu ) : ?>
		<?php yith_plugin_fw_include_fw_template( '/components/resources/action-button-menu.php', compact( 'action_button_menu' ) ); ?>
	<?php endif; ?>
</span>
