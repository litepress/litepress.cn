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
$placeholder = isset( $placeholder ) ? ' data-placeholder = "' . $placeholder . '" ' : '';


$country_setting = (string) $value;

if ( strstr( $country_setting, ':' ) ) {
    $country_setting  = explode( ':', $country_setting );
    $selected_country = current( $country_setting );
    $selected_state   = end( $country_setting );
} else {
    $selected_country = $country_setting;
    $selected_state   = '*';
}
$countries = WC()->countries->get_countries();
$class     = isset( $class ) ? $class : 'yith-plugin-fw-select';
?>
<select id="<?php echo $id ?>"
        name="<?php echo $name ?>" <?php echo isset( $std ) ? " data-std='{$std}'" : '' ?>
        class="wc-enhanced-select <?php echo $class ?>"
    <?php echo $placeholder ?>
    <?php echo $custom_attributes ?>
    <?php if ( isset( $data ) ) echo yith_plugin_fw_html_data_to_string( $data ); ?>>
    <?php
    if ( $countries ) {
        foreach ( $countries as $key => $value ) {
            $states = WC()->countries->get_states( $key );
            if ( $states ) {
                echo '<optgroup label="' . esc_attr( $value ) . '">';
                foreach ( $states as $state_key => $state_value ) {
                    echo '<option value="' . esc_attr( $key ) . ':' . esc_attr( $state_key ) . '"';

                    if ( $selected_country === $key && $selected_state === $state_key ) {
                        echo ' selected="selected"';
                    }

                    echo '>' . esc_html( $value ) . ' &mdash; ' . $state_value . '</option>'; // WPCS: XSS ok.
                }
                echo '</optgroup>';
            } else {
                echo '<option';
                if ( $selected_country === $key && '*' === $selected_state ) {
                    echo ' selected="selected"';
                }
                echo ' value="' . esc_attr( $key ) . '">' . $value . '</option>'; // WPCS: XSS ok.
            }
        }
    }
    ?>
</select>