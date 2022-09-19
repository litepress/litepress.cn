<?php
/**
 * Template for displaying the list-items component
 *
 * @var array $component The component.
 * @package YITH\PluginFramework\Templates\Components
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

list ( $component_id, $class, $the_title, $attributes, $data, $items, $variant ) = yith_plugin_fw_extract( $component, 'id', 'class', 'title', 'attributes', 'data', 'items', 'variant' );

$variant        = sanitize_key( $variant ?? 'list' );
$classes        = array(
	'yith-plugin-fw__list-items',
	"yith-plugin-fw__list-items--{$variant}",
	$class,
);
$classes        = implode( ' ', $classes );
$current_locale = substr( get_user_locale(), 0, 2 );
$loop           = 1;
?>
<ul
		id="<?php echo esc_attr( $component_id ); ?>"
		class="<?php echo esc_attr( $classes ); ?>"
	<?php echo yith_plugin_fw_html_attributes_to_string( $attributes ); ?>
	<?php echo yith_plugin_fw_html_data_to_string( $data ); ?>
>

	<?php foreach ( $items as $item ) : ?>
		<?php
		$item_url         = $item['url'] ?? '';
		$item_title       = $item['title'] ?? '';
		$item_description = $item['description'] ?? '';
		$item_cta         = $item['cta'] ?? '';
		$item_classes     = array( 'yith-plugin-fw__list-item' );

		if ( is_array( $item_url ) ) {
			$item_url = $item_url[ $current_locale ] ?? $item_url['en'] ?? current( $item_url );
		}

		if ( ! $item_url ) {
			$item_classes[] = 'yith-plugin-fw__list-item--no-link';
		}

		$item_classes = implode( ' ', $item_classes );
		?>
		<li class="<?php echo esc_attr( $item_classes ); ?>">
			<a
					class="yith-plugin-fw__list-item__wrap"
					target="_blank"
				<?php if ( $item_url ) : ?>
					href="<?php echo esc_url( $item_url ); ?>"
				<?php endif; ?>
			>
				<?php if ( 'steps' === $variant ) : ?>
					<div class="yith-plugin-fw__list-item__step">
						<?php echo esc_html( $loop ); ?>
					</div>
				<?php endif; ?>
				<div class="yith-plugin-fw__list-item__content">
					<div class="yith-plugin-fw__list-item__title">
						<?php echo wp_kses_post( $item_title ); ?>
					</div>
					<div class="yith-plugin-fw__list-item__description">
						<?php echo wp_kses_post( $item_description ); ?>
					</div>
					<?php if ( $item_cta ) : ?>
						<div class="yith-plugin-fw__list-item__cta">
							<?php echo esc_html( $item_cta ); ?>
						</div>
					<?php endif; ?>
				</div>

				<?php if ( $item_url && ! $item_cta ) : ?>
					<i class="yith-plugin-fw__list-item__arrow yith-icon yith-icon-arrow-right-alt"></i>
				<?php endif; ?>
			</a>
		</li>

		<?php $loop ++; ?>
	<?php endforeach; ?>
</ul>
