<?php
/**
 * Template for displaying the toggle-element field
 *
 * @var array $field The field.
 * @package YITH\PluginFramework\Templates\Fields
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

$defaults = array(
	'id'                => '',
	'class'             => '',
	'name'              => '',
	'add_button'        => '',
	'elements'          => array(),
	'title'             => '',
	'subtitle'          => '',
	'onoff_field'       => array(),
	'sortable'          => false,
	'save_button'       => array(),
	'delete_button'     => array(),
	'custom_attributes' => '',
);
$field    = wp_parse_args( $field, $defaults );

list ( $field_id, $class, $name, $value, $add_button, $elements, $the_title, $subtitle, $onoff_field, $sortable, $save_button, $delete_button, $custom_attributes ) = yith_plugin_fw_extract( $field, 'id', 'class', 'name', 'value', 'add_button', 'elements', 'title', 'subtitle', 'onoff_field', 'sortable', 'save_button', 'delete_button', 'custom_attributes' );

$show_add_button   = isset( $add_button ) && $add_button;
$add_button_closed = isset( $add_button_closed ) ? $add_button_closed : '';
$values            = isset( $value ) ? $value : get_option( $name, array() );
$values            = maybe_unserialize( $values );
$sortable          = isset( $sortable ) ? $sortable : false;
$class_wrapper     = $sortable ? 'ui-sortable' : '';
$onoff_id          = isset( $onoff_field['id'] ) ? $onoff_field['id'] : '';
$ajax_nonce        = wp_create_nonce( 'save-toggle-element' );

if ( empty( $values ) && ! $show_add_button && $elements ) {
	$values = array();
	// Populate toggle element with the default values.
	foreach ( $elements as $element ) {
		$values[0][ $element['id'] ] = $element['default'];
	}
}

?>
<div class="yith-toggle_wrapper <?php echo esc_attr( $class_wrapper ); ?>" id="<?php echo esc_attr( $field_id ); ?>" data-nonce="<?php echo esc_attr( $ajax_nonce ); ?>">
	<?php if ( ! empty( $label ) ) : ?>
		<label for="<?php esc_attr( $field_id ); ?>"><?php echo esc_html( $label ); ?></label>
	<?php endif; ?>
	<?php if ( $show_add_button ) : ?>
		<button class="yith-add-button yith-add-box-button"
				data-box_id="<?php echo esc_attr( $field_id ); ?>_add_box"
				data-closed_label="<?php echo esc_attr( $add_button_closed ); ?>"
				data-opened_label="<?php echo esc_attr( $add_button ); ?>"><?php echo esc_html( $add_button ); ?></button>
		<div id="<?php echo esc_attr( $field_id ); ?>_add_box" class="yith-add-box"></div>
		<script type="text/template" id="tmpl-yith-toggle-element-add-box-content-<?php echo esc_attr( $field_id ); ?>">
			<?php foreach ( $elements as $element ) : ?>
				<?php
				$element['title'] = $element['name'];
				$element['type']  = isset( $element['yith-type'] ) ? $element['yith-type'] : $element['type'];

				unset( $element['yith-type'] );

				$element['value'] = isset( $element['default'] ) ? $element['default'] : '';
				$element['id']    = 'new_' . $element['id'];
				$element['name']  = $name . '[{{{data.index}}}][' . $element['id'] . ']';
				$class_element    = isset( $element['class_row'] ) ? $element['class_row'] : '';
				if ( ! empty( $element['deps']['id'] ) ) {
					$element['deps']['id'] = 'new_' . $element['deps']['id'];
				}
				if ( ! empty( $element['deps']['target-id'] ) ) {
					$element['deps']['target-id'] = 'new_' . $element['deps']['target-id'];
				}

				if ( ! empty( $element['required'] ) ) {
					$class_element .= ' yith-plugin-fw--required';
				}
				?>
				<div class="yith-add-box-row yith-toggle-content-row <?php echo esc_attr( $class_element ); ?> <?php echo '{{{data.index}}}'; ?>" <?php echo yith_field_deps_data( $element ); ?>>

					<label for="<?php echo esc_attr( $element['id'] ); ?>"><?php echo esc_html( $element['title'] ); ?></label>
					<div class="yith-plugin-fw-option-with-description">
						<?php yith_plugin_fw_get_field( $element, true ); ?>
						<span class="description"><?php echo ! empty( $element['desc'] ) ? wp_kses_post( $element['desc'] ) : ''; ?></span>
					</div>
				</div>
			<?php endforeach; ?>

			<?php if ( ! empty( $save_button ) ) : ?>
				<div class="yith-add-box-buttons">
					<button class="button-primary yith-save-button">
						<?php echo esc_html( $save_button['name'] ); ?>
					</button>
				</div>
			<?php endif; ?>
		</script>
	<?php endif; ?>

	<div class="yith-toggle-elements">
		<?php if ( $values ) : ?>
			<?php foreach ( $values as $i => $value ) : ?>
				<?php
				$title_element    = yith_format_toggle_title( $the_title, $value );
				$title_element    = apply_filters( 'yith_plugin_fw_toggle_element_title_' . $field_id, $title_element, $elements, $value );
				$subtitle_element = yith_format_toggle_title( $subtitle, $value );
				$subtitle_element = apply_filters( 'yith_plugin_fw_toggle_element_subtitle_' . $field_id, $subtitle_element, $elements, $value );
				?>
				<div id="<?php echo esc_attr( $field_id ); ?>_<?php echo esc_attr( $i ); ?>"
						class="yith-toggle-row <?php echo ! empty( $subtitle ) ? 'with-subtitle' : ''; ?> <?php echo esc_attr( $class ); ?>"
						data-item_key="<?php echo esc_attr( $i ); ?>"
					<?php yith_plugin_fw_html_attributes_to_string( $custom_attributes, true ); ?>
				>
					<div class="yith-toggle-title">
						<h3>
							<span class="title" data-title_format="<?php echo esc_attr( $the_title ); ?>"><?php echo wp_kses_post( $title_element ); ?></span>
							<?php if ( ! empty( $subtitle_element ) ) : ?>
								<div class="subtitle" data-subtitle_format="<?php echo esc_attr( $subtitle ); ?>"><?php echo wp_kses_post( $subtitle_element ); ?></div>
							<?php endif; ?>
						</h3>
						<span class="yith-toggle"><span class="yith-icon yith-icon-arrow_right ui-sortable-handle"></span></span>
						<?php if ( ! empty( $onoff_field ) && is_array( $onoff_field ) ) : ?>
							<?php
							$onoff_field['value'] = isset( $value[ $onoff_id ] ) ? $value[ $onoff_id ] : ( isset( $onoff_field['default'] ) ? $onoff_field['default'] : '' );
							$onoff_field['type']  = 'onoff';
							$onoff_field['name']  = "{$name}[{$i}][{$onoff_id}]";
							$onoff_field['id']    = $onoff_id . '_' . $i;
							unset( $onoff_field['yith-type'] );
							?>
							<span class="yith-toggle-onoff"
								<?php if ( ! empty( $onoff_field['ajax_action'] ) ) : ?>
									data-ajax_action="<?php echo esc_attr( $onoff_field['ajax_action'] ); ?>"
								<?php endif ?>
							>
								<?php yith_plugin_fw_get_field( $onoff_field, true ); ?>
							</span>

							<?php if ( $sortable ) : ?>
								<span class="yith-icon yith-icon-drag"></span>
							<?php endif ?>
						<?php endif; ?>
					</div>
					<div class="yith-toggle-content">
						<?php if ( $elements && count( $elements ) > 0 ) : ?>
							<?php foreach ( $elements as $element ) : ?>
								<?php
								$element['type'] = isset( $element['yith-type'] ) ? $element['yith-type'] : $element['type'];
								unset( $element['yith-type'] );
								$element['title']     = $element['name'];
								$element['name']      = $name . "[$i][" . $element['id'] . ']';
								$element['value']     = isset( $value[ $element['id'] ] ) ? $value[ $element['id'] ] : ( isset( $element['default'] ) ? $element['default'] : '' );
								$element['id']        = $element['id'] . '_' . $i;
								$element['class_row'] = isset( $element['class_row'] ) ? $element['class_row'] : '';

								if ( ! empty( $element['deps']['id'] ) ) {
									$element['deps']['id'] = $element['deps']['id'] . '_' . $i;
								}
								if ( ! empty( $element['deps']['target-id'] ) ) {
									$element['deps']['target-id'] = $element['deps']['target-id'] . '_' . $i;
								}

								if ( ! empty( $element['required'] ) ) {
									$element['class_row'] .= ' yith-plugin-fw--required';
								}
								?>
								<div class="yith-toggle-content-row <?php echo esc_attr( $element['class_row'] . ' ' . $element['type'] ); ?>" <?php echo yith_field_deps_data( $element ); ?>>
									<label for="<?php echo esc_attr( $element['id'] ); ?>"><?php echo esc_html( $element['title'] ); ?></label>
									<div class="yith-plugin-fw-option-with-description">
										<?php yith_plugin_fw_get_field( $element, true ); ?>
										<span class="description"><?php echo ! empty( $element['desc'] ) ? wp_kses_post( $element['desc'] ) : ''; ?></span>
									</div>
								</div>
							<?php endforeach; ?>
						<?php endif; ?>
						<div class="yith-toggle-content-buttons">
							<div class="spinner"></div>
							<?php if ( $save_button && ! empty( $save_button['id'] ) ) : ?>
								<?php
								$save_button_class = isset( $save_button['class'] ) ? $save_button['class'] : '';
								$save_button_name  = isset( $save_button['name'] ) ? $save_button['name'] : '';
								?>
								<button id="<?php echo esc_attr( $save_button['id'] ); ?>" class="button-primary yith-save-button <?php echo esc_attr( $save_button_class ); ?>">
									<?php echo esc_html( $save_button_name ); ?>
								</button>
							<?php endif; ?>
							<?php if ( $delete_button && ! empty( $delete_button['id'] ) ) : ?>
								<?php
								$delete_button_class = isset( $delete_button['class'] ) ? $delete_button['class'] : '';
								$delete_button_name  = isset( $delete_button['name'] ) ? $delete_button['name'] : '';
								?>
								<button id="<?php echo esc_attr( $delete_button['id'] ); ?>"
										class="button-secondary yith-delete-button <?php echo esc_attr( $delete_button_class ); ?>">
									<?php echo esc_html( $delete_button_name ); ?>
								</button>
							<?php endif; ?>
						</div>
					</div>

				</div>
			<?php endforeach; ?>
		<?php endif; ?>
	</div>
	<script type="text/template" id="tmpl-yith-toggle-element-item-<?php echo esc_attr( $field_id ); ?>">
		<div id="<?php echo esc_attr( $field_id ); ?>_{{{data.index}}}"
				class="yith-toggle-row highlight <?php echo ! empty( $subtitle ) ? 'with-subtitle' : ''; ?> <?php echo esc_attr( $class ); ?>"
				data-item_key="{{{data.index}}}"
			<?php yith_plugin_fw_html_attributes_to_string( $custom_attributes, true ); ?>
		>
			<div class="yith-toggle-title">
				<h3>
					<span class="title" data-title_format="<?php echo esc_attr( $the_title ); ?>"><?php echo wp_kses_post( $the_title ); ?></span>
					<div class="subtitle" data-subtitle_format="<?php echo esc_attr( $subtitle ); ?>"><?php echo wp_kses_post( $subtitle ); ?></div>
				</h3>
				<span class="yith-toggle"><span class="yith-icon yith-icon-arrow_right"></span></span>
				<?php if ( ! empty( $onoff_field ) && is_array( $onoff_field ) ) : ?>
					<?php
					$onoff_field['value'] = isset( $onoff_field['default'] ) ? $onoff_field['default'] : '';
					$onoff_field['type']  = 'onoff';
					$onoff_field['name']  = $name . '[{{{data.index}}}][' . $onoff_id . ']';
					$onoff_field['id']    = $onoff_id;
					unset( $onoff_field['yith-type'] );
					?>
					<span class="yith-toggle-onoff"
						<?php if ( ! empty( $onoff_field['ajax_action'] ) ) : ?>
							data-ajax_action="<?php echo esc_attr( $onoff_field['ajax_action'] ); ?>"
						<?php endif ?>
					>
						<?php yith_plugin_fw_get_field( $onoff_field, true ); ?>
					</span>
				<?php endif; ?>
				<?php if ( $sortable ) : ?>
					<span class="yith-icon yith-icon-drag ui-sortable-handle"></span>
				<?php endif ?>
			</div>
			<div class="yith-toggle-content">
				<?php if ( $elements && count( $elements ) > 0 ) : ?>
					<?php foreach ( $elements as $element ) : ?>
						<?php
						$element['type'] = isset( $element['yith-type'] ) ? $element['yith-type'] : $element['type'];
						unset( $element['yith-type'] );
						$element['title'] = $element['name'];
						$element['name']  = $name . '[{{{data.index}}}][' . $element['id'] . ']';
						$element['id']    = $element['id'] . '_{{{data.index}}}';
						$class_element    = isset( $element['class_row'] ) ? $element['class_row'] : '';

						if ( ! empty( $element['deps']['id'] ) ) {
							$element['deps']['id'] = $element['deps']['id'] . '_{{{data.index}}}';
						}
						if ( ! empty( $element['deps']['target-id'] ) ) {
							$element['deps']['target-id'] = $element['deps']['target-id'] . '_{{{data.index}}}';
						}

						if ( ! empty( $element['required'] ) ) {
							$class_element .= ' yith-plugin-fw--required';
						}

						?>
						<div class="yith-toggle-content-row <?php echo esc_attr( $class_element . ' ' . $element['type'] ); ?>" <?php echo yith_field_deps_data( $element ); ?>>
							<label for="<?php echo esc_attr( $element['id'] ); ?>"><?php echo esc_html( $element['title'] ); ?></label>
							<div class="yith-plugin-fw-option-with-description">
								<?php yith_plugin_fw_get_field( $element, true ); ?>
								<span class="description"><?php echo ! empty( $element['desc'] ) ? wp_kses_post( $element['desc'] ) : ''; ?></span>
							</div>
						</div>
					<?php endforeach; ?>
				<?php endif; ?>
				<div class="yith-toggle-content-buttons">
					<div class="spinner"></div>
					<?php if ( $save_button && ! empty( $save_button['id'] ) ) : ?>
						<?php
						$save_button_class = isset( $save_button['class'] ) ? $save_button['class'] : '';
						$save_button_name  = isset( $save_button['name'] ) ? $save_button['name'] : '';
						?>
						<button id="<?php echo esc_attr( $save_button['id'] ); ?>" class="yith-save-button <?php echo esc_attr( $save_button_class ); ?>">
							<?php echo esc_html( $save_button_name ); ?>
						</button>
					<?php endif; ?>
					<?php if ( $delete_button && ! empty( $delete_button['id'] ) ) : ?>
						<?php
						$delete_button_class = isset( $delete_button['class'] ) ? $delete_button['class'] : '';
						$delete_button_name  = isset( $delete_button['name'] ) ? $delete_button['name'] : '';
						?>
						<button id="<?php echo esc_attr( $delete_button['id'] ); ?>" class="button-secondary yith-delete-button <?php echo esc_attr( $delete_button_class ); ?>">
							<?php echo esc_html( $delete_button_name ); ?>
						</button>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</script>
</div>
