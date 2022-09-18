<?php
/**
 * The Template for displaying the Main page of the System Information.
 *
 * @package YITH\PluginFramework\Templates\SysInfo
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

$system_info    = get_option( 'yith_system_info' );
$output_ip      = YITH_System_Status()->get_output_ip();
$labels         = YITH_System_Status()->requirement_labels;
$plugin_fw_info = YITH_System_Status()->get_plugin_fw_info();
$database_info  = YITH_System_Status()->get_database_info();
?>
<h2>
	<?php esc_html_e( 'Site Info', 'yith-plugin-fw' ); ?>
</h2>
<table class="form-table" role="presentation">
	<tr>
		<th scope="row">
			<?php esc_html_e( 'Site URL', 'yith-plugin-fw' ); ?>
		</th>
		<td class="info-value">
			<?php echo esc_html( get_site_url() ); ?>
		</td>

	</tr>
	<tr>
		<th scope="row">
			<?php esc_html_e( 'Output IP Address', 'yith-plugin-fw' ); ?>
		</th>
		<td class="info-value">
			<?php echo esc_html( $output_ip ); ?>
		</td>
	</tr>
	<tr>
		<th scope="row">
			<?php esc_html_e( 'Defined WP_CACHE', 'yith-plugin-fw' ); ?>
		</th>
		<td class="info-value">
			<?php echo( defined( 'WP_CACHE' ) && WP_CACHE ? esc_html__( 'Yes', 'yith-plugin-fw' ) : esc_html__( 'No', 'yith-plugin-fw' ) ); ?>
		</td>
	</tr>
	<tr>
		<th scope="row">
			<?php esc_html_e( 'External object cache', 'yith-plugin-fw' ); ?>
		</th>
		<td class="info-value">
			<?php echo( wp_using_ext_object_cache() ? esc_html__( 'Yes', 'yith-plugin-fw' ) : esc_html__( 'No', 'yith-plugin-fw' ) ); ?>
		</td>
	</tr>
	<tr>
		<th scope="row">
			<?php esc_html_e( 'YITH Plugin Framework Version', 'yith-plugin-fw' ); ?>
		</th>
		<td class="info-value">
			<?php
			echo esc_html(
				sprintf(
					'%s (%s)',
					$plugin_fw_info['version'],
					// translators: %s is the name of the plugin that is loading the framework.
					sprintf( __( 'loaded by %s', 'yith-plugin-fw' ), $plugin_fw_info['loaded_by'] )
				)
			);
			?>
		</td>
	</tr>
</table>

<h2>
	<?php esc_html_e( 'Plugins Requirements', 'yith-plugin-fw' ); ?>
</h2>
<table class="form-table" role="presentation">
	<?php foreach ( $system_info['system_info'] as $key => $item ) : ?>
		<?php
		$has_errors   = isset( $item['errors'] );
		$has_warnings = isset( $item['warnings'] );
		?>
		<tr>
			<th scope="row">
				<?php echo esc_html( $labels[ $key ] ); ?>
			</th>
			<td class="requirement-value <?php echo( $has_errors ? 'has-errors' : '' ); ?> <?php echo( $has_warnings ? 'has-warnings' : '' ); ?>">
				<span class="dashicons dashicons-<?php echo( $has_errors || $has_warnings ? 'warning' : 'yes' ); ?>"></span>
				<?php
				YITH_System_Status()->format_requirement_value( $key, $item['value'] );
				?>
			</td>
			<td class="requirement-messages">
				<?php
				if ( $has_errors ) {
					YITH_System_Status()->print_error_messages( $key, $item, $labels[ $key ] );
					YITH_System_Status()->print_solution_suggestion( $key, $item, $labels[ $key ] );
				} elseif ( $has_warnings ) {
					YITH_System_Status()->print_warning_messages( $key );
				}
				?>
			</td>
		</tr>
	<?php endforeach; ?>
</table>
<?php
$db_error = version_compare( $database_info['mysql_version'], '5.6', '<' ) && ! strstr( $database_info['mysql_version_string'], 'MariaDB' );
?>
<h2>
	<?php esc_html_e( 'Database Info', 'yith-plugin-fw' ); ?>
</h2>
<table class="form-table" role="presentation">
	<tr>
		<th scope="row">
			<?php esc_html_e( 'MySQL version', 'yith-plugin-fw' ); ?>
		</th>
		<td class="requirement-value <?php echo( $db_error ? 'has-errors' : '' ); ?>" style="width:auto!important">
			<span class="dashicons dashicons-<?php echo( $db_error ? 'warning' : 'yes' ); ?>"></span>
			<?php echo esc_attr( $database_info['mysql_version'] . ' - ' . $database_info['mysql_version_string'] ); ?>
		</td>
		<td class="requirement-messages">
			<?php
			if ( $db_error ) {
				/* Translators: %s: Codex link. */
				echo sprintf( esc_html__( 'WordPress recommends a minimum MySQL version of 5.6. See: %s', 'yith-plugin-fw' ), '<a href="https://wordpress.org/about/requirements/" target="_blank">' . esc_html__( 'WordPress requirements', 'yith-plugin-fw' ) . '</a>' );
			}
			?>
		</td>
	</tr>
	<tr>
		<th scope="row">
			<?php esc_html_e( 'Total Database Size', 'yith-plugin-fw' ); ?>
		</th>
		<td colspan="2">
			<?php printf( '%.2fMB', esc_html( $database_info['database_size']['data'] + $database_info['database_size']['index'] + $database_info['database_size']['free'] ) ); ?>
		</td>
	</tr>
	<tr>
		<th scope="row">
			<?php esc_html_e( 'Database Data Size', 'yith-plugin-fw' ); ?>
		</th>
		<td colspan="2">
			<?php printf( '%.2fMB', esc_html( $database_info['database_size']['data'] ) ); ?>
		</td>
	</tr>
	<tr>
		<th scope="row">
			<?php esc_html_e( 'Database Index Size', 'yith-plugin-fw' ); ?>
		</th>
		<td colspan="2">
			<?php printf( '%.2fMB', esc_html( $database_info['database_size']['index'] ) ); ?>
		</td>
	</tr>
	<tr>
		<th scope="row">
			<?php esc_html_e( 'Database Free Size', 'yith-plugin-fw' ); ?>
		</th>
		<td colspan="2">
			<?php printf( '%.2fMB', esc_html( $database_info['database_size']['free'] ) ); ?>
		</td>
	</tr>
	<?php foreach ( $database_info['database_tables'] as $table => $table_data ) : ?>
		<tr>
			<th scope="row">
				<?php echo esc_html( $table ); ?>
			</th>
			<td colspan="2">
				<?php
				/* Translators: %1$f: Table size, %2$f: Index size, %3$f: Free size, %4$s Engine. */
				printf( esc_html__( 'Data: %1$.2fMB | Index: %2$.2fMB | Free: %3$.2fMB | Engine: %4$s', 'yith-plugin-fw' ), esc_html( number_format( $table_data['data'], 2 ) ), esc_html( number_format( $table_data['index'], 2 ) ), esc_html( number_format( $table_data['free'], 2 ) ), esc_html( $table_data['engine'] ) );
				?>
			</td>
		</tr>
	<?php endforeach; ?>
</table>
