<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<tr>
	<td class="sort"></td>
	<td class="file_name"><input type="text" class="input_text" placeholder="<?php _e( 'File Name', 'yith-woocommerce-tab-manager' ); ?>" name="<?php echo $field_name.'[file_names][]'?>" value="<?php echo esc_attr( $file['name'] ); ?>" /></td>
    <td class="file_desc"><input type="text" class="input_text" placeholder="<?php _e('Insert a short description of the file', 'yith-woocommerce-tab-manager');?>" name="<?php echo $field_name.'[file_desc][]'?>" value="<?php echo wp_unslash( $file['desc'] ); ?>" /></td>
	<td class="file_url">
        <?php
            $file_url = apply_filters( 'ywtm_change_url_path', $file['file'] );
        ?>
        <input type="text" class="input_text" placeholder="<?php _e( "http://", 'yith-woocommerce-tab-manager' ); ?>" name="<?php echo $field_name.'[file_urls][]'?>" value="<?php echo esc_attr( $file_url ); ?>" />
    </td>
    <td class="file_url_choose" width="1%"><a href="#" class="button tab_upload_file_button" data-choose="<?php _e( 'Choose file', 'yith-woocommerce-tab-manager' ); ?>" data-update="<?php _e( 'Insert file URL', 'yith-woocommerce-tab-manager' ); ?>"><?php echo str_replace( ' ', '&nbsp;', __( 'Choose file', 'yith-woocommerce-tab-manager' ) ); ?></a></td>
	<td width="1%"><a href="#" class="delete"><?php _e( 'Delete', 'yith-woocommerce-tab-manager' ); ?></a></td>
</tr>