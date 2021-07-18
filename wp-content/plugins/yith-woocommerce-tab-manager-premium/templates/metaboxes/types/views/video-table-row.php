<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
$fields_name = array(
	'video_name' => 'video_name',
	'video_type' => 'host',
	'video_id' => 'id',
    'video_url' => 'url'
);
$metabox_name = 'yit_metaboxes['.$field_id.'][video_info]';

?>
<tr>
    <td class="sort"></td>
    <?php foreach( $fields_name as $key=> $field_name ):?>

        <td class="<?php esc_attr_e( $key );?>">
	        <?php $field_value = isset( $value[$field_name]  ) ? $value[$field_name] : '';?>
            <?php if( 'video_type' == $key ):?>
                <select name="<?php echo $metabox_name;?>[<?php echo $i;?>][<?php echo $field_name;?>]">
                    <option value="youtube" <?php selected('youtube', esc_attr( $field_value ) );?>><?php _e('YouTube', 'yith-woocommerce-tab-manager')?></option>
                    <option value="vimeo" <?php selected('vimeo', esc_attr( $field_value ) );?>><?php _e('Vimeo', 'yith-woocommerce-tab-manager')?></option>
                </select>
            <?php else:?>

            <input type="text" name="<?php echo $metabox_name;?>[<?php echo $i;?>][<?php echo $field_name;?>]" value="<?php echo $field_value ;?>"/>
            <?php endif;?>
        </td>

    <?php endforeach;?>
    <td width="1%"><a href="#" class="delete"><?php _e( 'Remove', 'yith-woocommerce-tab-manager' ); ?></a></td>
</tr>
