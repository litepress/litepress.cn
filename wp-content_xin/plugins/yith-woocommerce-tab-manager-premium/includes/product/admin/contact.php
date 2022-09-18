<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<?php
global $product_object;
$product_id = $product_object->get_id();
$tab_id = $tab->ID;
$contact_info = get_post_meta($product_id, $tab_id."_custom_form", true);
if ( !$contact_info )
    $contact_info   =   array();


$fields['name']['show']  = isset ($contact_info['name']['show'])? $contact_info['name']['show'] : 'off';
$fields['webaddr']['show']  = isset ($contact_info['webaddr']['show'])? $contact_info['webaddr']['show'] : 'off';
$fields['subj']['show']  = isset ($contact_info['subj']['show'])? $contact_info['subj']['show'] : 'off';

$fields['name']['req']  = isset ($contact_info['name']['req'])? $contact_info['name']['req'] : 'off';
$fields['webaddr']['req']  = isset ($contact_info['webaddr']['req'])? $contact_info['webaddr']['req'] : 'off';
$fields['subj']['req']  = isset ($contact_info['subj']['req'])? $contact_info['subj']['req'] : 'off';

$show_req_name      =   $fields['name']['show']=='off'? 'display:none' :'display:block';
$show_req_webaddr   =   $fields['webaddr']['show']=='off'? 'display:none' :'display:block';
$show_req_subj      =   $fields['subj']['show']=='off'? 'display:none' :'display:block';

?>
<div id="<?php echo $tab_id;?>_tab" class="panel woocommerce_options_panel">
    <div class="custom_tab_options">
        <div class="options_group">
            <p class="form-field">
                <label for="field_name"><?php _e('Name','yith-woocommerce-tab-manager');?></label>
                <input type="checkbox" id="field_name" class="add_field_check" name="<?php echo $field_name;?>[name_show]" <?php checked($fields['name']['show'],'on');?> />
                <p class="sub_form_video_row form-field" id="field_name_req" style="<?php echo $show_req_name?>">
                    <label class="req">Required</label>
                    <input type="checkbox" name="<?php echo $field_name;?>[name_req]" <?php checked($fields['name']['req'],'on');?>/>
                    <span class="description"><?php _e( 'Check this option to make it required.', 'yith-woocommerce-tab-manager' ) ?></span>
                </p>
            </p>
            <p class="form-field">
                <label for="field_webaddr"><?php _e('Website','yith-woocommerce-tab-manager');?></label>
                <input type="checkbox"  id="field_webaddr" class="add_field_check" name="<?php echo $field_name;?>[webaddr_show]" <?php checked($fields['webaddr']['show'],'on');?>/>
                <p class="sub_form_video_row form-field" id="field_webaddr_req" style="<?php echo $show_req_webaddr?>">
                    <label class="req">Required</label>
                    <input type="checkbox"  name="<?php echo $field_name;?>[webaddr_req]"  <?php checked($fields['webaddr']['req'],'on');?>/>
                    <span class="descprition"><?php _e( 'Check this option to make it required.', 'yith-woocommerce-tab-manager' ) ?></span>
                </p>
            </p>

            <p class="form-field">
                <label for="field_subj"><?php _e('Subject','yith-woocommerce-tab-manager');?></label>
                <input type="checkbox"  id="field_subj" class="add_field_check" name="<?php echo $field_name;?>[subj_show]" <?php checked($fields['subj']['show'],'on');?>/>
                <p class="sub_form_video_row form-field" id="field_subj_req" style="<?php echo $show_req_subj?>">
                    <label class="req">Required</label>
                    <input type="checkbox"  name="<?php echo $field_name;?>[subj_req]" <?php checked($fields['subj']['req'],'on');?>/>
                    <span class="description"><?php _e( 'Check this option to make it required.', 'yith-woocommerce-tab-manager' ) ?></span>
                </p>
            </p>
        </div>
    </div>
</div>