<?php
/**
 * Define constants and include Plugin Framework files.
 *
 * @package YITH\PluginFramework
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.


! defined( 'YIT_CORE_PLUGIN' ) && define( 'YIT_CORE_PLUGIN', true );
! defined( 'YIT_CORE_PLUGIN_PATH' ) && define( 'YIT_CORE_PLUGIN_PATH', dirname( __FILE__ ) );
! defined( 'YIT_CORE_PLUGIN_URL' ) && define( 'YIT_CORE_PLUGIN_URL', untrailingslashit( plugins_url( '/', __FILE__ ) ) );
! defined( 'YIT_CORE_PLUGIN_TEMPLATE_PATH' ) && define( 'YIT_CORE_PLUGIN_TEMPLATE_PATH', YIT_CORE_PLUGIN_PATH . '/templates' );

require_once 'yit-functions.php';
require_once 'yit-woocommerce-compatibility.php';
require_once 'yit-plugin-registration-hook.php';
require_once 'includes/class-yit-metabox.php';
require_once 'includes/class-yit-plugin-panel.php';
require_once 'includes/class-yit-plugin-panel-woocommerce.php';
require_once 'includes/class-yit-ajax.php';
require_once 'includes/class-yit-plugin-subpanel.php';
require_once 'includes/class-yit-plugin-common.php';
require_once 'includes/class-yit-gradients.php';
require_once 'includes/class-yit-plugin-licence.php';
require_once 'includes/class-yit-theme-licence.php';
require_once 'includes/class-yit-help-desk.php';
require_once 'includes/class-yit-video.php';
require_once 'includes/class-yit-upgrade.php';
require_once 'includes/class-yit-pointers.php';
require_once 'includes/class-yit-icons.php';
require_once 'includes/class-yit-assets.php';
require_once 'includes/class-yith-debug.php';
require_once 'includes/class-yith-dashboard.php';
require_once 'includes/privacy/class-yith-privacy.php';
require_once 'includes/privacy/class-yith-privacy-plugin-abstract.php';
require_once 'includes/class-yith-system-status.php';
require_once 'includes/class-yith-post-type-admin.php';
require_once 'includes/class-yith-bh-onboarding.php';

// Gutenberg Support.
if ( class_exists( 'WP_Block_Type_Registry' ) ) {
	require_once 'includes/builders/gutenberg/class-yith-gutenberg.php';
}

require_once 'includes/builders/elementor/class-yith-elementor.php';

// load from theme folder...
load_textdomain( 'yith-plugin-fw', get_template_directory() . '/core/plugin-fw/yith-plugin-fw-' . apply_filters( 'plugin_locale', determine_locale(), 'yith-plugin-fw' ) . '.mo' ) ||
// ...or from plugin folder.
load_textdomain( 'yith-plugin-fw', dirname( __FILE__ ) . '/languages/yith-plugin-fw-' . apply_filters( 'plugin_locale', determine_locale(), 'yith-plugin-fw' ) . '.mo' );

add_filter( 'plugin_row_meta', 'yit_plugin_fw_row_meta', 20, 4 );

if ( ! function_exists( 'yit_plugin_fw_row_meta' ) ) {
	/**
	 * Show the plugin row meta.
	 *
	 * @param string[] $plugin_meta An array of the plugin's metadata, including the version, author, author URI, and plugin URI.
	 * @param string   $plugin_file Path to the plugin file relative to the plugins directory.
	 * @param array    $plugin_data An array of plugin data.
	 * @param string   $status      Status filter currently applied to the plugin list.
	 *
	 * @return string[] array of the plugin's metadata.
	 * @author Andrea Grillo <andrea.grillo@yithemes.com>
	 * @since  3.0.17
	 */
	function yit_plugin_fw_row_meta( $plugin_meta, $plugin_file, $plugin_data, $status ) {
		if ( false === strstr( $plugin_file, 'yith' ) ) {
			// Not an YITH plugin.
			return $plugin_meta;
		}

		$base_uri = array(
			'live_demo'       => 'https://plugins.yithemes.com/',
			'documentation'   => 'https://docs.yithemes.com/',
			'premium_support' => 'https://yithemes.com/my-account/support/dashboard/',
			'free_support'    => 'https://wordpress.org/support/plugin/',
			'premium_version' => 'https://yithemes.com/themes/plugins/',
		);

		$default = array(
			'live_demo'       => array(
				'label' => _x( 'Live Demo', 'Plugin Row Meta', 'yith-plugin-fw' ),
				'icon'  => 'dashicons  dashicons-laptop',
			),
			'documentation'   => array(
				'label' => _x( 'Documentation', 'Plugin Row Meta', 'yith-plugin-fw' ),
				'icon'  => 'dashicons  dashicons-search',
			),
			'support'         => array(
				'label' => _x( 'Support', 'Plugin Row Meta', 'yith-plugin-fw' ),
				'icon'  => 'dashicons  dashicons-admin-users',
			),
			'premium_version' => array(
				'label' => _x( 'Premium version', 'Plugin Row Meta', 'yith-plugin-fw' ),
				'icon'  => 'dashicons  dashicons-cart',
			),
		);

		$to_show           = array( 'live_demo', 'documentation', 'support', 'premium_version' );
		$new_row_meta_args = apply_filters(
			'yith_show_plugin_row_meta',
			array(
				'to_show' => $to_show,
				'slug'    => '',
			),
			$plugin_meta,
			$plugin_file,
			$plugin_data,
			$status
		);
		$fields            = $new_row_meta_args['to_show'] ?? array();
		$slug              = $new_row_meta_args['slug'] ?? '';
		$is_extended       = $new_row_meta_args['is_extended'] ?? ( false !== strpos( $plugin_file, '-extended' ) );
		$is_premium        = $new_row_meta_args['is_premium'] ?? ( false !== strpos( $plugin_file, '-premium' ) );
		$utm_campaign      = 'plugin-version-author-uri';

		if ( $is_premium ) {
			$utm_source = 'wp-premium-dashboard';
			$to_remove  = array( 'premium_version' );
		} elseif ( $is_extended ) {
			$utm_source = 'wp-extended-dashboard';
			$to_remove  = array( 'live_demo' );

			// set custom base uri.
			$base_uri['documentation'] = 'https://www.bluehost.com/help/article/';
			$base_uri['free_support']  = add_query_arg( array( 'page' => 'bluehost' ), admin_url( 'admin.php' ) ) . '#/help';
		} else {
			$utm_source = 'wp-free-dashboard';
		}

		// remove meta not required for current plugin.
		if ( ! empty( $to_remove ) ) {
			foreach ( $to_remove as $item_to_remove ) {
				$item_to_remove_pos = array_search( $item_to_remove, $fields, true );

				if ( false === $item_to_remove_pos ) {
					continue;
				}

				unset( $fields[ $item_to_remove_pos ] );
			}
		}

		foreach ( $fields as $field ) {
			$row_meta = isset( $new_row_meta_args[ $field ] ) ? wp_parse_args( $new_row_meta_args[ $field ], $default[ $field ] ) : $default[ $field ];
			$url      = '';
			$icon     = '';
			$label    = '';

			// Check for Label.
			if ( isset( $row_meta['label'] ) ) {
				$label = $row_meta['label'];
			}

			// Check for Icon.
			if ( isset( $row_meta['icon'] ) ) {
				$icon = $row_meta['icon'];
			}

			// Check for URL.
			if ( isset( $row_meta['url'] ) ) {
				$url = $row_meta['url'];
			} else {
				if ( ! empty( $slug ) ) {
					if ( 'support' === $field ) {
						$support_field = true === $is_premium ? 'premium_support' : 'free_support';

						if ( ! empty( $base_uri[ $support_field ] ) ) {
							$url = $base_uri[ $support_field ];
						}

						if ( 'free_support' === $support_field && ! $is_extended ) {
							$url = $url . $slug;
						}
					} else {
						if ( isset( $base_uri[ $field ] ) ) {
							$url = apply_filters( "yith_plugin_row_meta_{$field}_url", $base_uri[ $field ] . $slug, $field, $slug, $base_uri );
						}
					}
				}
			}

			if ( ! empty( $url ) && ! empty( $label ) ) {
				if ( ! ( $is_extended && in_array( $field, array( 'support', 'documentation' ), true ) ) ) {
					$url = yith_plugin_fw_add_utm_data( $url, $slug, $utm_campaign, $utm_source );
				}

				$plugin_meta[] = sprintf( '<a href="%s" target="_blank"><span class="%s"></span>%s</a>', $url, $icon, $label );
			}
		}

		if ( isset( $plugin_meta[1] ) ) {
			$utm_author_uri = yith_plugin_fw_add_utm_data( $plugin_data['AuthorURI'], $slug, $utm_campaign, $utm_source );
			$plugin_meta[1] = str_replace( $plugin_data['AuthorURI'], $utm_author_uri, $plugin_meta[1] );
		}

		if ( isset( $plugin_meta[2] ) ) {
			if ( $is_extended ) {
				unset( $plugin_meta[2] );
			} else {
				$utm_plugin_uri = yith_plugin_fw_add_utm_data( $plugin_data['PluginURI'], $slug, $utm_campaign, $utm_source );
				$plugin_meta[2] = str_replace( $plugin_data['PluginURI'], $utm_plugin_uri, $plugin_meta[2] );
			}
		}

		return $plugin_meta;
	}
}

if ( ! function_exists( 'yith_add_action_links' ) ) {
	/**
	 * Add the action links to plugin admin page
	 *
	 * @param array  $links       The plugin links.
	 * @param string $panel_page  The panel page.
	 * @param bool   $is_premium  Is this plugin premium? True if the plugin is premium. False otherwise.
	 * @param string $plugin_slug The plugin slug.
	 *
	 * @return   array
	 * @author   Andrea Grillo <andrea.grillo@yithemes.com>
	 * @since    1.6.5
	 */
	function yith_add_action_links( $links, $panel_page = '', $is_premium = false, $plugin_slug = '' ) {
		$links = is_array( $links ) ? $links : array();
		if ( ! empty( $panel_page ) ) {
			$links[] = sprintf( '<a href="%s">%s</a>', admin_url( "admin.php?page={$panel_page}" ), _x( 'Settings', 'Action links', 'yith-plugin-fw' ) );
		}

		if ( $is_premium && class_exists( 'YIT_Plugin_Licence' ) ) {
			$links[] = sprintf( '<a href="%s">%s</a>', YIT_Plugin_Licence()->get_license_activation_url( $plugin_slug ), __( 'License', 'yith-plugin-fw' ) );
		}

		return $links;
	}
}

if ( ! function_exists( 'yith_plugin_fw_print_deactivation_message' ) ) {
	/**
	 * Prints message about plugins deactivation, due to multiple versions active of the same software active at the same time
	 *
	 * @return void
	 * @since 3.9.8
	 */
	function yith_plugin_fw_print_deactivation_message() {
		global $pagenow;

		// phpcs:disable WordPress.Security.NonceVerification
		if ( 'plugins.php' !== $pagenow || ! isset( $_GET['yith_deactivated_plugins'] ) ) {
			return;
		}

		$names = sanitize_text_field( wp_unslash( $_GET['yith_deactivated_plugins'] ) );
		$names = explode( ',', $names );
		$names = array_map(
			function ( $init_file ) {
				$name = get_plugin_data( WP_PLUGIN_DIR . '/' . $init_file )['Name'] ?? '';
				if ( ! $name ) {
					$name = str_replace( array( '-', 'init.php', '/' ), ' ', $init_file );
					$name = str_replace( array( 'yith', 'woocommerce', 'wordpress' ), array( 'YITH', 'WooCommerce', 'WordPress' ), $name );
					$name = trim( ucwords( $name ) );
				}

				return $name;
			},
			$names
		);

		// translators: 1. Plugin(s) name(s).
		$message = _n(
			'%s was deactivated as you\'re running an higher tier version of the same plugin.',
			'%s were deactivated as you\'re running higher tier versions of the same plugins.',
			count( $names ),
			'yit-plugin-fw'
		);
		$message = sprintf( $message, implode( ', ', $names ) );
		?>
		<div class="notice">
			<p><?php echo esc_html( $message ); ?></p>
		</div>
		<?php
		// phpcs:enable
	}
}

add_action( 'admin_notices', 'yith_plugin_fw_print_deactivation_message' );
