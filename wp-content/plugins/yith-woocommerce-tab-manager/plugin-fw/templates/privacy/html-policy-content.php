<?php
/**
 * The Template for displaying privacy content policy.
 *
 * @var array $sections The sections.
 * @package YITH\PluginFramework\Templates
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

?>
<div class="wp-suggested-text">
	<?php do_action( 'yith_plugin_fw_privacy_guide_content_before' ); ?>

	<?php
	foreach ( $sections as $key => $section ) {
		$privacy_action = "yith_plugin_fw_privacy_guide_content_{$key}";
		$content        = apply_filters( 'yith_plugin_fw_privacy_guide_content', '', $key );

		if ( has_action( $privacy_action ) || ! empty( $section['tutorial'] ) || ! empty( $section['description'] ) || $content ) {
			if ( ! empty( $section['title'] ) ) {
				echo '<h2>' . esc_html( $section['title'] ) . '</h2>';
			}

			if ( ! empty( $section['tutorial'] ) ) {
				echo '<p class="privacy-policy-tutorial">' . wp_kses_post( $section['tutorial'] ) . '</p>';
			}

			if ( ! empty( $section['description'] ) ) {
				echo '<p >' . wp_kses_post( $section['description'] ) . '</p>';
			}

			if ( ! empty( $content ) ) {
				echo wp_kses_post( $content );
			}
		}

		do_action( $privacy_action );
	}
	?>

	<?php do_action( 'yith_plugin_fw_privacy_guide_content_after' ); ?>
</div>
