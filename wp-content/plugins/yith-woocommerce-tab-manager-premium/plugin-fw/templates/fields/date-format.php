<?php
/**
 * This file belongs to the YIT Plugin Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 *
 * @var array $field
 */

/** @since 3.1.30 */

! defined( 'ABSPATH' ) && exit; // Exit if accessed directly

extract( $field );

$class = isset( $class ) ? $class : '';
$js    = isset( $js ) ? $js : false;
$class = 'yith-plugin-fw-radio ' . $class;

$options = yith_get_date_format( $js );
$custom  = true;
?>
<div class="<?php echo $class ?> yith-plugin-fw-date-format" id="<?php echo $id ?>"
	<?php echo $custom_attributes ?>
	<?php if ( isset( $data ) ) {
		echo yith_plugin_fw_html_data_to_string( $data );
	} ?> value="<?php echo $value ?>">
	<?php foreach ( $options as $key => $label ) :
		$checked = '';
		$radio_id = sanitize_key( $id . '-' . $key );
		if ( $value === $key ) { // checked() uses "==" rather than "==="
			$checked = " checked='checked'";
			$custom  = false;
		}
		?>
        <div class="yith-plugin-fw-radio__row">
            <input type="radio" id="<?php echo esc_attr( $radio_id ) ?>" name="<?php echo $name ?>"
                   value="<?php echo esc_attr( $key ) ?>" <?php echo $checked ?> />
            <label for="<?php echo esc_attr( $radio_id ) ?>"><?php echo date_i18n( $label ) ?>
                <code><?php echo esc_html( $key ) ?></code></label>
        </div>
	<?php endforeach; ?>
	<?php $radio_id = sanitize_key( $id . '-custom' ); ?>
    <div class="yith-plugin-fw-radio__row">
        <input type="radio" id="<?php echo esc_attr( $radio_id ) ?>" name="<?php echo esc_attr( $name ) ?>"
               value="\c\u\s\t\o\m" <?php checked( $custom ); ?> />
        <label for="<?php echo esc_attr( $radio_id ) ?>"> <?php _e( 'Custom:', 'yith-plugin-fw' ) ?></label>
        <input type="text" name="<?php echo esc_attr( $name . '_text' ) ?>"
               id="<?php echo esc_attr( $radio_id ) ?>_text" value="<?php echo esc_attr( $value ) ?>"
               class="small-text"/>
    </div>

</div>