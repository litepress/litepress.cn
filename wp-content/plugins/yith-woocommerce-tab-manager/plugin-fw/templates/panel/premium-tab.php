<?php
/**
 * The Template for displaying the Premium tab.
 *
 * @var array  $options     The premium tab  options array.
 * @var string $premium_url The premium landing page URL.
 * @var string $plugin_slug The plugin slug.
 *
 * @package YITH\PluginFramework\Templates
 * @author  Giuseppe Arcifa <giuseppe.arcifa@yithemes.com>
 * @since   3.9.0
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

list( $premium_features, $main_image_url, $show_free_vs_premium ) = yith_plugin_fw_extract( $options, 'premium_features', 'main_image_url', 'show_free_vs_premium_link' );

$get_premium_url = yith_plugin_fw_add_utm_data( $premium_url, $plugin_slug, 'button-upgrade', 'wp-free-dashboard' );

if ( $show_free_vs_premium ) {
	$free_vs_premium_url = yith_plugin_fw_add_utm_data( $premium_url, $plugin_slug, 'button-compare', 'wp-free-dashboard' );
}

?>

<div id="yith_plugin_fw_panel_premium_tab" class="yith-plugin-fw-panel-premium-tab-container">
	<div class="yith-plugin-fw-panel-premium-tab">
		<div class="yith-plugin-fw-panel-premium-tab__header">
		<span class="yith-plugin-fw-panel-premium-tab__header-title">
			<?php echo esc_html_x( 'Get the premium version to unlock advanced features', 'Premium Tab', 'yith-plugin-fw' ); ?>
		</span>
			<span class="yith-plugin-fw-panel-premium-tab__header-cta-arrow"></span>
			<a href="<?php echo esc_url( $get_premium_url ); ?>" target="_blank" class="yith-plugin-fw-panel-premium-tab__header-cta yith-plugin-fw-panel-premium-tab__cta-button">
				<?php echo esc_html_x( 'Get premium', 'Premium Tab', 'yith-plugin-fw' ); ?>
			</a>
		</div>
		<div class="yith-plugin-fw-panel-premium-tab__content">
			<?php if ( $main_image_url ) : ?>
				<img class="yith-plugin-fw-panel-premium-tab__main-image" src="<?php echo esc_attr( $main_image_url ); ?>" alt="<?php esc_html_x( 'Plugin premium features images', 'Premium Tab', 'yith-plugin-fw' ); // translators: alt attribute of main image tag. ?>">
			<?php endif; ?>
			<div class="yith-plugin-fw-panel-premium-tab__features">
				<?php foreach ( $premium_features as $premium_feature ) : ?>
					<div class="yith-plugin-fw-panel-premium-tab__feature">
						<span class="yith-plugin-fw-panel-premium-tab__feature-content">
							<?php echo wp_kses_post( $premium_feature ); ?>
						</span>
					</div>
				<?php endforeach; ?>
				<?php if ( $show_free_vs_premium ) : ?>
					<span class="yith-plugin-fw-panel-premium-tab__free-vs-premium">
						<?php echo esc_html_x( 'And so much more!', 'Premium Tab', 'yith-plugin-fw' ); ?>
						<a href="<?php echo esc_url( $free_vs_premium_url . '#tab-free_vs_premium_tab' ); ?>" target="_blank">
							<?php echo esc_html_x( 'Check the free vs premium features >', 'Premium Tab', 'yith-plugin-fw' ); ?>
						</a>
					</span>
				<?php endif; ?>
				<a href="<?php echo esc_url( $get_premium_url ); ?>" target="_blank" class="yith-plugin-fw-panel-premium-tab__content-cta yith-plugin-fw-panel-premium-tab__cta-button">
					<?php echo esc_html_x( 'Get the premium version', 'Premium Tab', 'yith-plugin-fw' ); ?>
				</a>
			</div>
		</div>
	</div>
</div>
