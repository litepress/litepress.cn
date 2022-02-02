<?php
/**
 * The Template for displaying the Error Log.
 *
 * @package YITH\PluginFramework\Templates\SysInfo
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

$debug_files = array(
	// WordPress' debug.log file.
	'debug.log' => array(
		'label' => __( 'WP debug.log file', 'yith-plugin-fw' ),
		'path'  => WP_CONTENT_DIR . '/debug.log',
	),
	// PHP error_log file.
	'error_log' => array(
		'label' => __( 'PHP error_log file', 'yith-plugin-fw' ),
		'path'  => ABSPATH . 'error_log',
	),
);

?>
<h2>
	<?php esc_html_e( 'Log Files', 'yith-plugin-fw' ); ?>
</h2>
<table class="form-table" role="presentation">
	<?php
	global $wp_filesystem;

	if ( empty( $wp_filesystem ) ) {
		require_once ABSPATH . '/wp-admin/includes/file.php';
		WP_Filesystem();
	}

	$max_file_size = 8388608; // 8 MB.
	$missing_files = 0;
	?>

	<?php foreach ( $debug_files as $key => $debug_file ) : ?>
		<?php
		if ( ! file_exists( $debug_file['path'] ) ) {
			$missing_files ++;
			continue;
		}

		$file_size = filesize( $debug_file['path'] );
		?>
		<tr>
			<th scope="row"><label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $debug_file['label'] ); ?></label></th>
			<td>
				<?php
				if ( $file_size > $max_file_size ) {
					yith_plugin_fw_get_field(
						array(
							'id'      => $key,
							'type'    => 'buttons',
							'buttons' => array(
								array(
									'name'  => __( 'Download', 'yith-plugin-fw' ),
									'class' => 'yith-download-log',
									'data'  => array(
										'action' => 'yith_create_log_file',
										'file'   => $key,
									),
								),
							),
						),
						true
					);

					echo '<span class="description">' . esc_html__( 'The file size exceeds 8 megabytes so it must be downloaded', 'yith-plugin-fw' ) . '</span>';

				} else {
					yith_plugin_fw_get_field(
						array(
							'id'                => $key,
							'type'              => 'textarea',
							'value'             => $wp_filesystem->get_contents( $debug_file['path'] ),
							'class'             => 'yith-system-info-debug',
							'custom_attributes' => 'readonly',
						),
						true
					);
				}
				?>
			</td>
		</tr>
	<?php endforeach; ?>

	<?php if ( 2 === $missing_files ) : ?>
		<tr>
			<td>
				<?php
				// translators: %s file name.
				echo sprintf( esc_html__( 'No Log file available. Enable the WordPress debug by adding this in the %s file of your installation', 'yith-plugin-fw' ), '<code>wp-config.php</code>' );
				?>
				<br/>
				<br/>
				<span class="debug-code">
					define( 'WP_DEBUG', true );<br/>
					define( 'WP_DEBUG_LOG', true );<br/>
					define( 'WP_DEBUG_DISPLAY', false );<br/>
				</span>
				<a href="#" data-tooltip="<?php esc_attr_e( 'Copied!', 'yith-plugin-fw' ); ?>" class="copy-link"><?php esc_html_e( 'Copy Code', 'yith-plugin-fw' ); ?></a>
			</td>
		</tr>
	<?php endif; ?>
</table>
