<?php
/**
 * Template for displaying the customtabs field
 *
 * @var array $field The field.
 * @package YITH\PluginFramework\Templates\Fields
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

list ( $name, $value ) = yith_plugin_fw_extract( $field, 'name', 'value' );

$value = ! ! $value && is_array( $value ) ? $value : array();
?>
<div id="yit_custom_tabs" class="panel wc-metaboxes-wrapper" style="display: block;">
	<p class="toolbar">
		<a href="#" class="close_all"><?php esc_html_e( 'Close all', 'yith-plugin-fw' ); ?></a><a href="#" class="expand_all"><?php esc_html_e( 'Expand all', 'yith-plugin-fw' ); ?></a>
	</p>

	<div class="yit_custom_tabs wc-metaboxes ui-sortable">

		<?php foreach ( $value as $i => $the_tab ) : ?>
			<div class="yit_custom_tab wc-metabox closed" rel="0">
				<h3>
					<button type="button" class="remove_row button"><?php esc_html_e( 'Remove', 'yith-plugin-fw' ); ?></button>
					<div class="handlediv" title="Click to toggle"></div>
					<strong class="attribute_name"><?php echo esc_html( $the_tab['name'] ); ?></strong>
				</h3>

				<table cellpadding="0" cellspacing="0" class="woocommerce_attribute_data wc-metabox-content" style="display: table;">
					<tbody>
					<tr>
						<td class="attribute_name">
							<label><?php esc_html_e( 'Name', 'yith-plugin-fw' ); ?>:</label>
							<input type="text" class="attribute_name" name="<?php echo esc_attr( $name ); ?>[<?php echo esc_attr( $i ); ?>][name]" value="<?php echo esc_attr( $the_tab['name'] ); ?>">
							<input type="hidden" name="<?php echo esc_attr( $name ); ?>[<?php echo esc_attr( $i ); ?>][position]" class="attribute_position" value="<?php echo esc_attr( $i ); ?>">
						</td>

						<td rowspan="3">
							<label><?php esc_html_e( 'Value', 'yith-plugin-fw' ); ?>:</label>
							<textarea name="<?php echo esc_attr( $name ); ?>[<?php echo esc_attr( $i ); ?>][value]" cols="5" rows="5" placeholder="<?php esc_attr_e( 'Content of the tab. (HTML is supported)', 'yith-plugin-fw' ); ?>"><?php echo wp_kses_post( $the_tab['value'] ); ?></textarea>
						</td>
					</tr>
					</tbody>
				</table>

			</div>
		<?php endforeach ?>
	</div>

	<p class="toolbar">
		<button type="button" class="button button-primary add_custom_tab"><?php esc_html_e( 'Add custom product tab', 'yith-plugin-fw' ); ?></button>
	</p>

	<div class="clear"></div>
</div>

<script>
	jQuery( document ).ready( function ( $ ) {
		// Add rows
		$( 'button.add_custom_tab' ).on( 'click', function () {

			var size        = $( '.yit_custom_tabs .yit_custom_tab' ).size() + 1;

			// Add custom attribute row
			$( '.yit_custom_tabs' ).append( '<div class="yit_custom_tab wc-metabox">\
						<h3>\
							<button type="button" class="remove_row button"><?php esc_html_e( 'Remove', 'yith-plugin-fw' ); ?></button>\
							<div class="handlediv" title="Click to toggle"></div>\
							<strong class="attribute_name"></strong>\
						</h3>\
						<table cellpadding="0" cellspacing="0" class="woocommerce_attribute_data">\
							<tbody>\
								<tr>\
									<td class="attribute_name">\
										<label><?php esc_html_e( 'Name', 'yith-plugin-fw' ); ?>:</label>\
										<input type="text" class="attribute_name" name="<?php echo esc_attr( $name ); ?>[' + size + '][name]" />\
										<input type="hidden" name="<?php echo esc_attr( $name ); ?>[' + size + '][position]" class="attribute_position" value="' + size + '" />\
									</td>\
									<td rowspan="3">\
										<label><?php esc_html_e( 'Value', 'yith-plugin-fw' ); ?>:</label>\
										<textarea name="<?php echo esc_attr( $name ); ?>[' + size + '][value]" cols="5" rows="5" placeholder="<?php echo esc_attr( addslashes( __( 'Content of the tab. (HTML is supported)', 'yith-plugin-fw' ) ) ); ?>"></textarea>\
									</td>\
								</tr>\
							</tbody>\
						</table>\
					</div>' );

		} );


		$( '.yit_custom_tabs' ).on( 'click', 'button.remove_row', function () {
			var answer = confirm( "<?php esc_attr_e( 'Do you want to remove the custom tab?', 'yith-plugin-fw' ); ?>" );
			if ( answer ) {
				var $parent = $( this ).parent().parent();

				$parent.remove();
				attribute_row_indexes();
			}
			return false;
		} );

		// Attribute ordering
		$( '.yit_custom_tabs' ).sortable(
			{
				items               : '.yit_custom_tab',
				cursor              : 'move',
				axis                : 'y',
				handle              : 'h3',
				scrollSensitivity   : 40,
				forcePlaceholderSize: true,
				helper              : 'clone',
				opacity             : 0.65,
				placeholder         : 'wc-metabox-sortable-placeholder',
				start               : function ( event, ui ) {
					ui.item.css( 'background-color', '#f6f6f6' );
				},
				stop                : function ( event, ui ) {
					ui.item.removeAttr( 'style' );
					attribute_row_indexes();
				}
			}
		);

		function attribute_row_indexes() {
			$( '.yit_custom_tabs .yit_custom_tab' ).each( function ( index, el ) {
				var newVal = '[' + $( el ).index( '.yit_custom_tabs .yit_custom_tab' ) + ']';
				var oldVal = '[' + $( '.attribute_position', el ).val() + ']';

				$( ':input:not(button)', el ).each( function () {
					var name = $( this ).attr( 'name' );
					$( this ).attr( 'name', name.replace( oldVal, newVal ) );
				} );

				$( '.attribute_position', el ).val( $( el ).index( '.yit_custom_tabs .yit_custom_tab' ) );
			} );
		}

	} );
</script>
