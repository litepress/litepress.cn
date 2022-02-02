<?php
/**
 * Template for displaying the list-table-blank-state component
 *
 * @var array $component The component.
 * @package YITH\PluginFramework\Templates\Components
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

list ( $component_id, $class, $icon, $icon_class, $icon_url, $message, $cta, $attributes, $data ) = yith_plugin_fw_extract( $component, 'id', 'class', 'icon', 'icon_class', 'icon_url', 'message', 'cta', 'attributes', 'data' );
?>
<div id="<?php echo esc_attr( $component_id ); ?>"
		class="yith-plugin-fw__list-table-blank-state <?php echo esc_attr( $class ); ?>"
	<?php echo yith_plugin_fw_html_attributes_to_string( $attributes ); ?>
	<?php echo yith_plugin_fw_html_data_to_string( $data ); ?>
>
	<?php if ( $icon ) : ?>
		<i class="yith-plugin-fw__list-table-blank-state__icon yith-icon yith-icon-<?php echo esc_attr( $icon ); ?>"></i>
	<?php elseif ( $icon_class ) : ?>
		<i class="yith-plugin-fw__list-table-blank-state__icon <?php echo esc_attr( $icon_class ); ?>"></i>
	<?php elseif ( $icon_url ) : ?>
		<img class="yith-plugin-fw__list-table-blank-state__icon" src="<?php echo esc_url( $icon_url ); ?>"/>
	<?php endif; ?>
	<div class="yith-plugin-fw__list-table-blank-state__message"><?php echo wp_kses_post( $message ); ?></div>
	<?php if ( $cta && ! empty( $cta['title'] ) ) : ?>
		<?php
		$cta_url     = ! empty( $cta['url'] ) ? $cta['url'] : '';
		$cta_classes = array( 'yith-plugin-fw__list-table-blank-state__cta', 'yith-plugin-fw__button--primary', 'yith-plugin-fw__button--xxl' );
		if ( ! empty( $cta['class'] ) ) {
			$cta_classes[] = $cta['class'];
		}
		if ( ! empty( $cta['icon'] ) ) {
			$cta_classes[] = 'yith-plugin-fw__button--with-icon';
		}
		$cta_classes = implode( ' ', $cta_classes );
		?>
		<div class="yith-plugin-fw__list-table-blank-state__cta-wrapper">
			<a href="<?php echo esc_url( $cta_url ); ?>" class="<?php echo esc_attr( $cta_classes ); ?>">
				<?php if ( ! empty( $cta['icon'] ) ) : ?>
					<i class="yith-icon yith-icon-<?php echo esc_attr( $cta['icon'] ); ?>"></i>
				<?php endif; ?>
				<?php echo esc_html( $cta['title'] ); ?>
			</a>
		</div>
	<?php endif; ?>
</div>
