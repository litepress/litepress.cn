<?php
if (!defined('ABSPATH')) {
    exit;
}

global $product_object;
$product_id = $product_object->get_id();
$tab_id = $tab->ID;
$gallery_images = get_post_meta( $product_id, $tab_id."_custom_gallery", true );
$image_ids='';
$gallery_settings = isset ( $gallery_images['settings'] ) ? $gallery_images['settings'] : array() ;
//$height     =   isset( $gallery_settings['height'] )  ? $gallery_settings['height']   : 100;
$columns    =   isset( $gallery_settings['columns'] ) ? $gallery_settings['columns']  : 1

?>
<div id="<?php echo $tab_id;?>_tab" class="panel woocommerce_options_panel">
    <div class="custom_tab_options" >
        <div class="options_group">
            <p class="form-field">
                <label for="custom_columns_number"><?php _e('Images per row','yith-woocommerce-tab-manager');?></label>
                <input type="number" id="custom_columns_number" min="1" max="4" step="1" name="<?php echo $field_name;?>[columns_number]" value="<?php echo $columns;?>"/>
                <span class="description"><?php _e('Set how many columns to show', 'yith-woocommerce-tab-manager');?></span>
            </p>
        </div>
       <div class="options_group yith-plugin-fw">
        <div class="form-field image-gallery"  style="padding:10px;">
            <ul id="product-custom-extra-images" class="slides-wrapper extra-images ui-sortable clearfix">
            <?php
             if( isset( $gallery_images['images'] )  ) {

                 foreach ($gallery_images['images'] as $key   =>  $image) {
                     echo '<li class="image" data-attachment_id="'.esc_attr( $image['id'] ).'" >';
                        echo '<a href="#">';
                        if( function_exists( 'yit_image') )
                                yit_image( "id=".$image['id']."&size=admin-post-type-thumbnails" );
                        else
                                echo wp_get_attachment_image($image['id'], array(80, 80));
                        echo '<ul class="actions">';
                        echo   '<li><a href="#" class="delete" title="'.__( 'Delete image', 'yith-woocommerce-tab-manager' ).'">>x</a></li>';
                        echo   '</ul>';
                     echo   '</li>';

                     $image_ids.=$image['id'].',';
                 }
             }
            else
            {
                $image = array(
                    'id'=>'',
                    'url'   =>''
                );
            }

            if ( substr($image_ids, -1) == ',' )
                    $image_ids= substr($image_ids,0, -1);
            ?>
             </ul>
                <input type="button" data-choose="<?php _e( 'Add Images to Gallery', 'yith-woocommerce-tab-manager' ); ?>" data-update="<?php _e( 'Add to gallery', 'yith-woocommerce-tab-manager' ); ?>" value="<?php _e( 'Add images', 'yith-woocommerce-tab-manager' ) ?>" data-delete="<?php _e( 'Delete image', 'yith-woocommerce-tab-manager' ); ?>" data-text="<?php _e( 'Delete', 'yith-woocommerce-tab-manager' ); ?>" id="product-gallery-button" class="image-gallery-button button" />
                <input type="hidden" class="image_gallery_ids" id="image_gallery_ids" name="<?php echo $field_name;?>[custom_gallery_image_ids]" value="<?php echo esc_attr( $image_ids ); ?>" />
        </div>
       </div>
    </div>
</div>



