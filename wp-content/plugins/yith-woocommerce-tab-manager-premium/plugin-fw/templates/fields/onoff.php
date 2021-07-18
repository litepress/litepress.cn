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

!defined( 'ABSPATH' ) && exit; // Exit if accessed directly

extract( $field );
?>

<div class="yith-plugin-fw-onoff-container <?php echo !empty( $class ) ? $class : ''; ?>"
    <?php if ( isset( $data ) ) echo yith_plugin_fw_html_data_to_string( $data ); ?>>
    <input type="checkbox" id="<?php echo $id ?>" name="<?php echo $name ?>" value="<?php echo esc_attr( $value ) ?>"
        <?php checked( yith_plugin_fw_is_true( $value ) ) ?> class="on_off" <?php if ( isset( $std ) ) : ?>data-std="<?php echo $std ?>"<?php endif ?>
        <?php echo $custom_attributes ?>
    />
    <span class="yith-plugin-fw-onoff"
          data-text-on="<?php echo esc_attr_x( 'ON', 'ON/OFF button: use MAX 3 characters!', 'yith-plugin-fw' ); ?>"
          data-text-off="<?php echo esc_attr_x( 'OFF', 'ON/OFF button: use MAX 3 characters!', 'yith-plugin-fw' ); ?>"></span>
</div>
<?php
if ( isset( $field[ 'desc-inline' ] ) ) {
    echo "<span class='description inline'>" . $field[ 'desc-inline' ] . "</span>";
}
?>
