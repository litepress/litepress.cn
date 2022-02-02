<?php
/**
 * The Template for displaying the System Information Panel.
 *
 * @package YITH\PluginFramework\Templates\SysInfo
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

$section_tabs = array(
	'main'      => esc_html__( 'System Status', 'yith-plugin-fw' ),
	'php-info'  => esc_html__( 'PHPInfo', 'yith-plugin-fw' ),
	'error-log' => esc_html__( 'Log Files', 'yith-plugin-fw' ),
);

$current_tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'main'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$tab_path    = defined( 'YIT_CORE_PLUGIN_PATH' ) ? YIT_CORE_PLUGIN_PATH : get_template_directory() . '/core/plugin-fw/';

?>
<div id="yith-sysinfo" class="wrap yith-system-info yith-plugin-ui">
	<h2 class="yith-sysinfo-title">
		<span class="yith-logo"><img src="<?php echo esc_url( yith_plugin_fw_get_default_logo() ); ?>"/></span> <?php esc_html_e( 'YITH System Information', 'yith-plugin-fw' ); ?>
	</h2>

	<h2 class="nav-tab-wrapper">
		<ul class="yith-plugin-fw-tabs">
			<?php foreach ( $section_tabs as $key => $tab_value ) : ?>
				<?php
				$active_class = ( $current_tab === $key ) ? ' nav-tab-active' : '';
				$url          = add_query_arg( array( 'tab' => $key ) );
				?>
				<li class="yith-plugin-fw-tab-element">
					<a class="nav-tab <?php echo esc_attr( $active_class ); ?>" href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $tab_value ); ?></a>
				</li>
			<?php endforeach; ?>
		</ul>
	</h2>
	<div id="wrap" class="yith-plugin-fw plugin-option yit-admin-panel-container">
		<div class="yith-system-info-wrap">
			<?php require_once $tab_path . "/templates/sysinfo/tabs/$current_tab.php"; ?>
		</div>
	</div>
</div>
