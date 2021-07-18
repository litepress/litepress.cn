<?php
if (!defined('ABSPATH')) {
    exit;
}
global $product_object;
$product_id =  $product_object->get_id();
$tab_id = $tab->ID;
  $map_info = get_post_meta($product_id, $tab_id."_custom_map", true);
if ( !$map_info )
    $map_info   =   array();

extract( $map_info );/*lat, long, addr, heig,wid,zoom, mark,style*/



$address    =   isset( $addr )?   $addr         :   "";
$width      =   isset( $wid )?    $wid          :   100;
$height     =   isset( $heig )?   $heig         :   100;
$zoom       =   isset( $zoom )?   $zoom         :   15;
$style_map  =   isset( $style )?  $style        :   "";
$show_w     =   isset( $show_width )? $show_width   :   0;
$display_input = ($show_w == 1) ? 'display:none;' : 'display:block';
?>
<div id="<?php echo $tab_id;?>_tab" class="panel woocommerce_options_panel">
    <div class="custom_tab_options">
        <div class="options_group">
            <p class="form-field">
                <label for="custom_check_map"><?php _e('Full width','yith-woocommerce-tab-manager');?></label>
                <input type="checkbox" name="<?php echo $field_name;?>[enable_width]" id="custom_check_map" value="1" <?php checked( $show_w, 1 );?> />
            </p>
        </div>
        <div class="options_group">
             <p id="custom_width_enable" class="form-field" style="<?php echo $display_input; ?>">
                <label for="custom_map_width"><?php _e('Width','yith-woocommerce-tab-manager');?></label>
                <input type="number" name="<?php echo $field_name;?>[custom_map_width]" id="custom_map_width" min="100" step="1" value="<?php echo esc_attr($width);?>" />
            </p>
            <p class="form-field">
                <label for="custom_map_height"><?php _e('Height', 'yith-woocommerce-tab-manager');?></label>
                <input type="number" name="<?php echo $field_name;?>[custom_map_height]" id="custom_map_height" min="100" step="1" value="<?php echo esc_attr($height);?>" />
            </p>

        </div>
        <div class="options_group">
            <p class="form-field">
                <label for="custom_address_map"><?php _e('Address','yith-woocommerce-tab-manager');?></label>
                <input type="text" name="<?php echo $field_name;?>[custom_map_addr]" id="custom_address_map" value="<?php echo esc_attr($address);?>" />
            </p>
            <p class="form-field">
                <label for="custom_map_zoom"><?php _e('Zoom', 'yith-woocommerce-tab-manager');?></label>
                <input  type="number" id="custom_map_zoom" name="<?php echo $field_name;?>[custom_map_zoom]" value="<?php echo esc_attr( $zoom ) ?>" min="0" max="19" step="1" />
            </p>
        </div>
    </div>
</div>
