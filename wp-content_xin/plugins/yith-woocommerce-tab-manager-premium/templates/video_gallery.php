<?php
if( isset( $videos) && !empty($videos) && $videos!="" ){
$column_w='ywtm_col_'.$videos['columns'];
?>

<div class="ywtm_video_gallery_container">
    <ul class="container_list_video container_<?php echo $videos['columns'];?>">
        <?php foreach($videos['video'] as $key=>$video):?>
            <?php if ( $video['id']!="" || $video['url']!=""  ):?>
                <li class="<?php echo $column_w?>">
                    <?php
                        $video_host =   $video['host'];

                        $args   =   array(
                            'id'    =>  $video['id'],
                            'url'   =>  $video['url'],
                            'width' => '100%',
                            'echo'  => true
                        );
                        if( $video_host=='youtube' )
                            YIT_Video::youtube($args);
                        else if( $video_host=='vimeo' )
                            YIT_Video::vimeo($args);
                    ?>
                </li>
        <?php endif;endforeach;?>
    </ul>
</div>
<?php }
else
    _e('No video found for this product', 'yith-woocommerce-tab-manager');?>