<?php
/**
 * "Welcome" modal view.
 *
 * @var array  $plugin    The plugin info.
 * @var array  $modal     The modal info.
 * @var string $close_url The URL for closing the modal.
 *
 * @package YITH\PluginFramework
 */

defined( 'ABSPATH' ) || exit;

$classes = array(
	'yith-plugin-fw-welcome',
	'yith-plugin-fw-welcome--welcome',
);
$classes = implode( ' ', $classes );

$description = $modal['description'] ?? '';
$items       = $modal['items'] ?? array();
?>
<div class="<?php echo esc_attr( $classes ); ?>">
	<div class="yith-plugin-fw-welcome__head">
		<?php if ( $plugin['icon'] ) : ?>
			<img class="yith-plugin-fw-welcome__icon" src="<?php echo esc_url( $plugin['icon'] ); ?>"/>
		<?php endif; ?>

		<div class="yith-plugin-fw-welcome__title">
			<div><?php esc_html_e( 'Thank you for using our plugin', 'yith-plugin-fw' ); ?></div>
			<div class="yith-plugin-fw-welcome__title__plugin-name"><?php echo esc_html( $plugin['name'] ); ?></div>
		</div>

		<?php if ( $description ) : ?>
			<div class="yith-plugin-fw-welcome__description">
				<?php echo wp_kses_post( $description ); ?>
			</div>
		<?php endif; ?>
	</div>

	<div class="yith-plugin-fw-welcome__list-head">
		<div class="yith-plugin-fw-welcome__list-head__title">
			<?php
			// translators: %s is the number of steps.
			echo esc_html( sprintf( __( 'Start with these %s steps:', 'yith-plugin-fw' ), count( $items ) ) );
			?>
		</div>
	</div>
	<?php
	yith_plugin_fw_get_component(
		array(
			'type'    => 'list-items',
			'variant' => 'steps',
			'items'   => $items,
		)
	);
	?>

	<div class="yith-plugin-fw-welcome__footer">
		<a class="yith-plugin-fw-welcome__close" href="<?php echo esc_url( $close_url ); ?>"><?php esc_html_e( 'Got it, close this window', 'yith-plugin-fw' ); ?></a>
	</div>
</div>
