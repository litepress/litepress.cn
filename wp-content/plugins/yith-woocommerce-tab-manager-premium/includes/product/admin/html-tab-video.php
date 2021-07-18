<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<tr>
    <td class="sort"></td>
    <td width="25%">
        <select name="<?php echo $field_name.'[video_hosts][]'?>" style="width: 100%;">
            <option value="youtube" <?php selected('youtube', esc_attr($video['host']));?>><?php _e('YouTube', 'yith-woocommerce-tab-manager')?></option>
            <option value="vimeo" <?php selected('vimeo', esc_attr($video['host']));?>><?php _e('Vimeo', 'yith-woocommerce-tab-manager')?></option>
        </select>
    </td>
    <td class="file_name"><input type="text" class="input_text" placeholder="<?php _e( 'Video ID', 'yith-woocommerce-tab-manager' ); ?>" name="<?php echo $field_name.'[video_ids][]';?>" value="<?php echo esc_attr( $video['id'] ); ?>" /></td>
    <td class="file_url"><input type="text" class="input_text" placeholder="<?php _e( "Add the video URL here", 'yith-woocommerce-tab-manager' ); ?>" name="<?php echo $field_name.'[video_urls][]'?>" value="<?php echo esc_attr( $video['url'] ); ?>" /></td>
    <td width="1%"><a href="#" class="delete"><?php _e( 'Delete', 'yith-woocommerce-tab-manager' ); ?></a></td>
</tr>