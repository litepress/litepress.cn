<?php
/**
 * FormatValidator class.
 */

namespace Automattic\WooCommerce\Internal\Settings;

defined( 'ABSPATH' ) || exit;

/**
 * This class handles sanitization of core options that need to conform to certain format.
 *
 * @since 6.6.0
 */
class OptionSanitizer {

	/**
	 * OptionSanitizer constructor.
	 */
	public function __construct() {
		// Sanitize color options.
		$color_options = array(
			'woocommerce_email_base_color',
			'woocommerce_email_background_color',
			'woocommerce_email_body_background_color',
			'woocommerce_email_text_color',
		);

		foreach ( $color_options as $option_name ) {
			add_filter(
				"woocommerce_admin_settings_sanitize_option_{$option_name}",
				function( $value, $option ) {
					return $this->sanitize_color_option( $value, $option );
				},
				10,
				2
			);
		}
	}

	/**
	 * Sanitizes values for options of type 'color' before persisting to the database.
	 * Falls back to previous/default value for the option if given an invalid value.
	 *
	 * @since 6.6.0
	 * @param string $value Option value.
	 * @param array  $option Option data.
	 * @return string Color in hex format.
	 */
	private function sanitize_color_option( $value, $option ) {
		$value = sanitize_hex_color( $value );

		// If invalid, try the current value.
		if ( ! $value && ! empty( $option['id'] ) ) {
			$value = sanitize_hex_color( get_option( $option['id'] ) );
		}

		// If still invalid, try the default.
		if ( ! $value && ! empty( $option['default'] ) ) {
			$value = sanitize_hex_color( $option['default'] );
		}

		return (string) $value;
	}

}
