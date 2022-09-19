<?php
/**
 * Template for displaying the multi-colorpicker field
 *
 * @var array $field The field.
 * @package YITH\PluginFramework\Templates\Fields
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

list ( $field_id, $class, $name, $colorpickers, $value ) = yith_plugin_fw_extract( $field, 'id', 'class', 'name', 'colorpickers', 'value' );

wp_enqueue_style( 'wp-color-picker' );

if ( ! isset( $colorpickers ) ) {
	return;
}
$class               = ! empty( $class ) ? $class : 'yith-plugin-fw-multi-colorpicker';
$color_pickers_count = count( $colorpickers );
$items_to_process    = array();
?>
<div class="<?php echo esc_attr( $class ); ?>" id="<?php echo esc_attr( $field_id ); ?>">
	<?php for ( $i = 0; $i < $color_pickers_count; $i ++ ) : ?>
		<?php
		$items = $colorpickers[ $i ];

		// Make sure that we have at least one group.
		if ( isset( $items['id'] ) ) {
			$items_to_process[] = $items;
			$next               = isset( $colorpickers[ $i + 1 ] ) ? $colorpickers[ $i + 1 ] : false;

			if ( isset( $next['id'] ) ) {
				continue;
			}
		} else {
			$items_to_process = $items;
		}

		if ( isset( $items_to_process['desc'] ) ) {
			$group_desc = $items_to_process['desc'];
			unset( $items_to_process['desc'] );
		}
		?>
		<div class="yith-colorpicker-group">
			<?php foreach ( $items_to_process as $color_picker ) : ?>
				<?php
				if ( ! is_array( $color_picker ) ) {
					continue;
				}

				$color_picker['type']  = 'colorpicker';
				$color_picker['title'] = $color_picker['name'];
				$color_picker['name']  = $name . "[{$color_picker['id']}]";
				$color_picker['value'] = isset( $value[ $color_picker['id'] ] ) ? $value[ $color_picker['id'] ] : $color_picker['default'];
				$color_picker['id']    = $name . '_' . $color_picker['id'];
				?>
				<div class="yith-single-colorpicker colorpicker">
					<label for="<?php echo esc_attr( $color_picker['id'] ); ?>"><?php echo esc_html( $color_picker['title'] ); ?></label>
					<?php yith_plugin_fw_get_field( $color_picker, true, false ); ?>
				</div>
			<?php endforeach; ?>

			<?php if ( ! empty( $group_desc ) ) : ?>
				<span class="description"><?php echo wp_kses_post( $group_desc ); ?></span>
			<?php endif; ?>

		</div>
	<?php endfor; ?>
</div>
