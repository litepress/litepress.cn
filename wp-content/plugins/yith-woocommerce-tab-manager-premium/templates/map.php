<?php
/*
 * This file belongs to the YIT Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */
//wp_enqueue_script('ywtm-google-map' );
$key = get_option('ywtm_google_api_key' );

$api_key = empty( $key ) ? '' : '?key='.$key;

wp_register_script('ywtm-google-map', '//maps.googleapis.com/maps/api/js'.$api_key, false, true );
wp_enqueue_script('yit-tabmap-script' );

if( isset( $map ) && !empty( $map ) && $map!="" && $map['addr']!=""){
$id = 'map_canvas_' .mt_rand();
extract($map);
$css_width = "";

if( !$show_width ){
    if( $wid != '' && $wid != 0 ){
        $css_width .= "width: " . $wid . "px;";
    } else{
        $css_width .= "width: auto;";
    }
}else{
    $wid = '';
}


$address = ( isset( $addr ) && $addr != '' ) ? $addr  : '';
$zoom = ( isset( $zoom ) && $zoom != '' ) ?  $zoom : 15;
$width_syle = ( $show_width ) ? "full-width section_fullwidth" : "" ;

?>

<div class="google-map ywtm_content_tab">
    <div class="gmap3 ywtm_map"   style="height:<?php echo $heig ?>px;<?php echo $css_width ?>" data-zoom="<?php echo $zoom;?>" data-address="<?php echo $address;?>"></div>
    <a class="link_google_map" target="_blank" href="<?php echo esc_url( add_query_arg( array( 'q' => urlencode( $addr ) ),  '//maps.google.com/' ) )?>"><?php _e('Show in Google Map', 'yith-woocommerce-tab-manager');?></a>
</div>
<?php }
else
    echo '<p>'.__('No map for this product', 'yith-woocommerce-tab-manager').'</p>';
?>