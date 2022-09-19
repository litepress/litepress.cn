<?php
/**
 * Template for displaying the multi-select field
 *
 * @var array $field The field.
 * @package YITH\PluginFramework\Templates\Fields
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

list ( $field_id, $class, $name, $selects, $value ) = yith_plugin_fw_extract( $field, 'id', 'class', 'name', 'selects', 'value' );

if ( empty( $selects ) ) {
	return;
}

$selects_count = count( $selects );
?>
<div class="yith-plugin-fw-multi-select" id="<?php echo esc_attr( $field_id ); ?>">
	<?php for ( $i = 0; $i < $selects_count; $i ++ ) : ?>
		<?php if ( ! ( $i % 2 ) ) : ?>
			<div class="yith-select-group">
		<?php endif; ?>

		<div class="yith-single-select">
			<?php
			$select          = $selects[ $i ];
			$select['type']  = 'select';
			$select['title'] = isset( $select['title'] ) ? $select['title'] : $select['name'];
			$select['name']  = $name . "[{$select['id']}]";
			$select['value'] = isset( $value[ $select['id'] ] ) ? $value[ $select['id'] ] : $select['default'];
			$select['id']    = $name . '_' . $select['id'];
			$select['class'] = $class
			?>
			<label for="<?php echo esc_attr( $select['id'] ); ?>"><?php echo esc_html( $select['title'] ); ?></label>
			<?php yith_plugin_fw_get_field( $select, true, false ); ?>
		</div>

		<?php if ( ( $i % 2 ) !== 0 || ! isset( $selects[ $i + 1 ] ) ) : ?>
			</div>
		<?php endif; ?>
	<?php endfor; ?>
</div>
