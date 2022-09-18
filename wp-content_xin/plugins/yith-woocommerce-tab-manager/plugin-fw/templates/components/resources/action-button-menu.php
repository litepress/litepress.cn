<?php
/**
 * Template for displaying the action-button-submenu
 *
 * @var array $action_button_menu The menu.
 * @package YITH\PluginFramework\Templates\Components\Resources
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.
?>
<span class="yith-plugin-fw__action-button__menu">
	<?php foreach ( $action_button_menu as $menu_key => $menu_item ) : ?>
		<?php
		$item_name            = isset( $menu_item['name'] ) ? $menu_item['name'] : '';
		$item_url             = isset( $menu_item['url'] ) ? $menu_item['url'] : '';
		$item_class           = isset( $menu_item['class'] ) ? $menu_item['class'] : '';
		$item_attributes      = isset( $menu_item['attributes'] ) ? $menu_item['attributes'] : array();
		$item_data            = isset( $menu_item['data'] ) ? $menu_item['data'] : array();
		$item_open_in_new_tab = isset( $menu_item['open_in_new_tab'] ) ? ! ! $menu_item['open_in_new_tab'] : false;
		$item_confirm_data    = isset( $menu_item['confirm_data'] ) ? $menu_item['confirm_data'] : array();

		$item_classes = array( 'yith-plugin-fw__action-button__menu__item', "yith-plugin-fw__action-button__menu__item--{$menu_key}-key", $item_class );

		if ( isset( $item_confirm_data['title'], $item_confirm_data['message'] ) && ! ! $item_url ) {
			$item_classes[] = 'yith-plugin-fw__require-confirmation-link';
			$item_data      = array_merge( $item_data, $item_confirm_data );
		}

		$item_classes = implode( ' ', array_filter( $item_classes ) );
		?>
		<?php if ( ! ! $item_url ) : ?>
			<a
					class="<?php echo esc_attr( $item_classes ); ?>"
					href="<?php echo esc_url( $item_url ); ?>"
				<?php if ( ! ! $item_open_in_new_tab ) : ?>
					target="_blank"
				<?php endif; ?>
				<?php
				yith_plugin_fw_html_attributes_to_string( $item_attributes, true );
				yith_plugin_fw_html_data_to_string( $item_data, true );
				?>
			><?php echo esc_html( $item_name ); ?></a>
		<?php else : ?>
			<span
					class="<?php echo esc_attr( $item_classes ); ?>"
					<?php
					yith_plugin_fw_html_attributes_to_string( $item_attributes, true );
					yith_plugin_fw_html_data_to_string( $item_data, true );
					?>
			><?php echo esc_html( $item_name ); ?></span>
		<?php endif; ?>

	<?php endforeach; ?>
</span>
