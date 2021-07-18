<?php
/**
 * This file belongs to the YIT Plugin Framework.
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 *
 * @var array $field
 */

/** @since 3.4.0 */

!defined( 'ABSPATH' ) && exit; // Exit if accessed directly

extract( $field );

$class = isset( $class ) ? $class : '';
$class = 'yith-plugin-fw-checkbox-array ' . $class;

$value = is_array( $value ) ? $value : array();
?>
<div class="<?php echo $class ?>" id="<?php echo $id ?>"
    <?php echo $custom_attributes ?>
    <?php if ( isset( $data ) ) echo yith_plugin_fw_html_data_to_string( $data ); ?> >
    <?php foreach ( $options as $key => $label ) :
        $checkbox_id = sanitize_key( $id . '-' . $key );
        ?>
        <div class="yith-plugin-fw-checkbox-array__row">
            <input type="checkbox" id="<?php echo $checkbox_id ?>" name="<?php echo $name ?>[]" value="<?php echo esc_attr( $key ) ?>" <?php checked( in_array( $key, $value ) ); ?> />
            <label for="<?php echo $checkbox_id ?>"><?php echo $label ?></label>
        </div>
    <?php endforeach; ?>
</div>

