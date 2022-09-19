<?php
/**
 * "Update" modal view.
 *
 * @var array  $plugin        The plugin info.
 * @var array  $modal         The modal info.
 * @var string $close_url     The URL for closing the modal.
 *
 * @package YITH\PluginFramework
 */

defined( 'ABSPATH' ) || exit;

$classes = array(
	'yith-plugin-fw-welcome',
	'yith-plugin-fw-welcome--update',
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
			<div class="yith-plugin-fw-welcome__title__plugin-name"><?php echo esc_html( $plugin['name'] ); ?></div>
			<div>
				<?php
				// translators: %s is the plugin version.
				echo esc_html( sprintf( __( 'is successfully updated to version %s.', 'yith-plugin-fw' ), $plugin['version'] ) );
				?>
			</div>
		</div>
	</div>

	<div class="yith-plugin-fw-welcome__list-head">
		<div class="yith-plugin-fw-welcome__list-head__title">
			<?php
			// translators: %s is the plugin version.
			echo esc_html( sprintf( __( 'What\'s new in version %s', 'yith-plugin-fw' ), $modal['since'] ?? $plugin['version'] ) );
			?>
		</div>
		<?php if ( isset( $modal['changelog_url'] ) ) : ?>
			<a class="yith-plugin-fw-welcome__list-head__changelog" target="_blank" href="<?php echo esc_url( $modal['changelog_url'] ); ?>">
				<?php esc_html_e( 'Check the changelog >', 'yith-plugin-fw' ); ?>
			</a>
		<?php endif; ?>
	</div>
	<?php
	yith_plugin_fw_get_component(
		array(
			'type'    => 'list-items',
			'variant' => 'list',
			'items'   => $items,
		)
	);
	?>

	<div class="yith-plugin-fw-welcome__footer">
		<a class="yith-plugin-fw-welcome__close" href="<?php echo esc_url( $close_url ); ?>"><?php esc_html_e( 'Got it, close this window', 'yith-plugin-fw' ); ?></a>
	</div>
</div>
