<?php
/**
 * Template for displaying the list-table field
 *
 * @var array $field The field.
 * @package YITH\PluginFramework\Templates\Fields
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

list ( $field_id, $class, $the_title, $the_post_type, $args, $add_new_button, $add_new_url, $list_table_class, $list_table_class_dir, $search_form, $desc ) = yith_plugin_fw_extract( $field, 'id', 'class', 'title', 'post_type', 'args', 'add_new_button', 'add_new_url', 'list_table_class', 'list_table_class_dir', 'search_form', 'desc' );

$show_button = false;
if ( isset( $add_new_button ) && ( isset( $the_post_type ) || ( isset( $add_new_url ) ) ) ) {
	$show_button         = true;
	$admin_url           = admin_url( 'post-new.php' );
	$params['post_type'] = $the_post_type;
	$add_new_url         = $add_new_url ? $add_new_url : apply_filters( 'yith_plugin_fw_add_new_post_url', esc_url( add_query_arg( $params, $admin_url ) ), $params, isset( $args ) ? $args : false );
}

if ( isset( $list_table_class, $list_table_class_dir ) && ! class_exists( $list_table_class ) && file_exists( $list_table_class_dir ) ) {
	include_once $list_table_class_dir;
}
?>
<?php if ( class_exists( $list_table_class ) ) : ?>
	<?php
	$list_table = isset( $args ) ? new $list_table_class( $args ) : new $list_table_class();
	?>
	<div id="<?php echo esc_attr( $field_id ); ?>" class="yith-plugin-fw-list-table <?php echo esc_attr( $class ); ?>">
		<div class="yith-plugin-fw-list-table-container">
			<div class="list-table-title">
				<h2>
					<?php echo isset( $the_title ) ? wp_kses_post( $the_title ) : ''; ?>
				</h2>
				<?php if ( $show_button ) : ?>
					<a href="<?php echo esc_url( $add_new_url ); ?>" class="yith-add-button"><?php echo esc_html( $add_new_button ); ?></a>
				<?php endif ?>
			</div>

			<?php if ( isset( $desc ) && ! empty( $desc ) ) : ?>
				<p class="yith-section-description"><?php echo wp_kses_post( $desc ); ?></p>
			<?php endif; ?>

			<?php
			$list_table->prepare_items();
			$list_table->views();
			?>

			<form method="post">
				<?php
				if ( isset( $search_form ) ) {
					$list_table->search_box( $search_form['text'], $search_form['input_id'] );
				}
				$list_table->display();
				?>
			</form>
		</div>
	</div>
<?php endif; ?>
