<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
$fields_name = array(
	'file_name' => 'name',
	'file_desc' => 'desc',
	'file_url' => 'file',

);
$metabox_name = 'yit_metaboxes['.$field_id.']';

?>
<tr>
    <td class="sort"></td>
    <?php foreach( $fields_name as $key=> $field_name ):?>

        <td class="<?php esc_attr_e( $key );?>">
	        <?php $field_value = isset( $value[$field_name]  ) ? $value[$field_name] : '';

	        if( 'file' == $field_name ){

	            $field_value = apply_filters( 'ywtm_change_url_path', $field_value );
            }

	        ?>


            <input type="text" name="<?php echo $metabox_name;?>[<?php echo $i;?>][<?php echo $field_name;?>]" value="<?php echo $field_value ;?>"/>

        </td>

    <?php endforeach;?>
    <td class="file_url_choose" width="1%"><a href="#" class="button upload_file_button" data-choose="<?php esc_attr_e( 'Choose file', 'yith-woocommerce-tab-manager' ); ?>" data-update="<?php esc_attr_e( 'Insert file URL', 'yith-woocommerce-tab-manager' ); ?>"><?php echo str_replace( ' ', '&nbsp;', __( 'Choose file', 'woocommerce' ) ); ?></a></td>
    <td width="1%"><a href="#" class="delete"><?php _e( 'Remove', 'yith-woocommerce-tab-manager' ); ?></a></td>
</tr>
