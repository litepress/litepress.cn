<?php
if (!defined('ABSPATH')) {
    exit;
}
global $product_object;
$product_id =  $product_object->get_id();
$tab_id = $tab->ID;
$editor_args = array(
    'wpautop'       => true, // use wpautop?
    'media_buttons' => true, // show insert/upload button(s)
    'textarea_name' => $field_name.'[default_editor]', // set the textarea name to something different, square brackets [] can be used here
    'textarea_rows' => 20, // rows="..."
    'tabindex'      => '',
    'editor_css'    => '', // intended for extra styles for both visual and HTML editors buttons, needs to include the <style> tags, can use "scoped".
    'editor_class'  => '', // add extra class(es) to the editor textarea
    'teeny'         => false, // output the minimal editor config used in Press This
    'dfw'           => false, // replace the default fullscreen with DFW (needs specific DOM elements and css)
    'tinymce'       => true, // load TinyMCE, can be used to pass settings directly to TinyMCE using an array()
    'quicktags'     => true // load Quicktags, can be used to pass settings directly to Quicktags using an array()
);
?>
<div id="<?php echo $tab_id;?>_tab" class="panel woocommerce_options_panel">
     <div class="custom_tab_options options_group" >
         <p class="form-field"><label for="<?php echo $tab_id.'_default_editor'?>"><?php _e( 'Tab Content', 'yith-woocommerce-tab-manager' ); ?></label></p>
         <div class="editor" style="margin:30px;">
             <?php
             $content =  get_post_meta( $product_id,$tab_id.'_default_editor',true);

             $content = wp_kses_post(  str_replace( '\\','', $content ),'UTF-8' );
             $editor_id = $tab_id.'_default_editor';
             wp_editor( $content, $editor_id , $editor_args );
             ?>
         </div>
     </div>

</div>