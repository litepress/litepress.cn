<?php
/**
 * Functions for deactivating plugins.
 *
 * @package YITH\PluginFramework
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

if ( ! function_exists( 'yit_deactive_free_version' ) ) {
	/**
	 * Deactivate the free version of the plugin.
	 *
	 * @param string $to_deactivate The constant name of the plugin to deactivate.
	 * @param string $to_activate   The path of the File of the plugin to activate.
	 */
	function yit_deactive_free_version( $to_deactivate, $to_activate ) {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		if ( defined( $to_deactivate ) && is_plugin_active( constant( $to_deactivate ) ) ) {
			deactivate_plugins( constant( $to_deactivate ) );

			if ( ! function_exists( 'wp_create_nonce' ) ) {
				header( 'Location: plugins.php' );
				exit();
			}

			global $status, $page, $s;
			$redirect = 'plugins.php?action=activate&plugin=' . $to_activate . '&plugin_status=' . $status . '&paged=' . $page . '&s=' . $s;
			$redirect = esc_url_raw( add_query_arg( '_wpnonce', wp_create_nonce( 'activate-plugin_' . $to_activate ), $redirect ) );

			header( 'Location: ' . $redirect );
			exit();
		}
	}
}
