<?php
wp_enqueue_script( 'prettyPhoto' );
wp_enqueue_script( 'prettyPhoto-init' );
wp_enqueue_style( 'woocommerce_prettyPhoto_css' );
if(  isset( $images ) && !empty( $images )  && !empty( $images['gallery'] )) {
    if ( substr($images['gallery'], -1) == ',' )
        $images['gallery']= substr($images['gallery'],0, -1);

    $images_id= explode(',', $images['gallery'] );
    $column_w='ywtm_col_'.$images['columns'];


?>

<div class="ywtm_image_gallery_container ywtm_content_tab">
    <ul class="container_img  container_<?php echo $images['columns'];?>">
        <?php foreach( $images_id as $image):?>
            <?php $img_src_thumbn=wp_get_attachment_image_src($image,'medium');
                   $img_src_thumbn = is_array( $img_src_thumbn ) ? $img_src_thumbn[0] : $img_src_thumbn;
                  $img_src_full=wp_get_attachment_image_src($image,'full');
                  $img_src_full = is_array( $img_src_full ) ? $img_src_full[0] : $img_src_full;
            ?>

            <li class="<?php echo $column_w?>"><a href="<?php echo $img_src_full;?>" data-rel="prettyPhoto[gallery-<?php echo $tab_id?>]"><img src="<?php echo $img_src_thumbn;?>"></a></li>
        <?php endforeach;?>
    </ul>
</div>
<?php }?>
