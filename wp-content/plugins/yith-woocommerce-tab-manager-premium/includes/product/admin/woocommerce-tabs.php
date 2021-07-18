<?php

/** template for manage woocommerce tabs
 * @author YITHEMES
 * @since 1.1.0
 *
 */
if( !defined( 'ABSPATH' ) )
    exit;
/**
 * @var WC_Product $product_object
 */
global $product_object;
$product_id = $product_object->get_id();

$wc_tabs = ywtm_get_default_tab( $product_id );
$content = $product_object->get_description();

?>

<div id="ywtm_wc_tab" class="panel woocommerce_options_panel">
    <?php
    foreach( $wc_tabs as $key => $tab ):
        $is_override = get_post_meta( $product_id, '_ywtm_override_'.$key, true );
        $is_hide     = get_post_meta( $product_id, '_ywtm_hide_'.$key, true );
        $count_review = $key === 'reviews' ? ' (%d)' : '';
        $tab_title = get_post_meta( $product_id, '_ywtm_title_tab_'.$key, true );
        $tab_title = $tab_title ? $tab_title : $tab['title'].$count_review;
        $tab_content = get_post_meta( $product_id, '_ywtm_content_tab_'.$key , true);
        $tab_content = $tab_content ? $tab_content : apply_filters( 'the_content',$content );
        $tab_priority = get_post_meta( $product_id, '_ywtm_priority_tab_'.$key, true );
        $tab_priority = $tab_priority ? $tab_priority : $tab['priority'];

        $tab_content =  wp_kses_post(  str_replace('\\','', $tab_content  ), 'UTF-8'  );


        ?>
        <div id="ywtm_wc_tab_container_<?php echo esc_attr( $key );?>" class="options_group ywtm_wc_tab_container">
            <div class="ywtm_wc_tab_option">

                <span class="ywtm_title"><label><?php echo esc_attr( $tab['title'] );?></label></span>
                  <span class="ywtm_hide">
                    <label><?php _e( 'Hide','yith-wc-tab-manager' );?></label>
                    <input type="checkbox" class="checkbox ywtm_hide_cb" name="ywtm_hide_<?php echo esc_attr( $key );?>" <?php checked( 'yes', $is_hide );?> data-tab_type="<?php esc_attr_e( $key );?>"/>
                    <img class="help_tip" data-tip="<?php esc_attr_e( 'Check for hide this tab', 'yith-wc-tab-manager' ); ?>" src="<?php echo esc_url( WC()->plugin_url() ); ?>/assets/images/help.png" height="16" width="16" />
                </span>

                <span class="ywtm_override">
                    <label><?php _e( 'Override','yith-wc-tab-manager' );?></label>
                        <input type="checkbox" class="checkbox ywtm_override_cb" name="ywtm_override_<?php echo esc_attr( $key );?>" <?php checked( 'yes', $is_override );?>  data-tab_type="<?php esc_attr_e( $key );?>"/>
						<img class="help_tip" data-tip="<?php esc_attr_e( 'Check for override this tab', 'yith-wc-tab-manager' ); ?>" src="<?php echo esc_url( WC()->plugin_url() ); ?>/assets/images/help.png" height="16" width="16" />
                </span>
            </div>
            <div class="ywtm_wc_tab_content" id="ywtm_wc_tab_content_<?php echo esc_attr( $key );?>">
                <p class="form-field">
                    <label><?php _e('Tab Title','yith-woocommerce-tab-manager');?></label>
                    <input type="text" name="ywtm_title_tab_<?php esc_attr_e( $key );?>" class="ywtm_title_tab_<?php esc_attr_e( $key );?>" value="<?php echo $tab_title;?>" />
                    <?php if( $key === 'reviews' ):?>
                        <img class="help_tip" data-tip="<?php esc_attr_e( 'If you want to display the number of the available reviews use %d as a placeholder', 'yith-wc-tab-manager' ); ?>" src="<?php echo esc_url( WC()->plugin_url() ); ?>/assets/images/help.png" height="16" width="16" />
                    <?php endif;?>
        </p>
                <?php
                    switch( $key ){
                        case 'description' :
                        ?>
                             <p class="form-field">
                                 <label><?php _e( 'Tab Content','yith-woocommerce-tab-manager');?></label>
                                 <?php

                                 $editor_args = array(
	                                 'wpautop'       => true, // use wpautop?
	                                 'media_buttons' => true, // show insert/upload button(s)
	                                 'textarea_name' => 'ywtm_content_tab_'.$key, // set the textarea name to something different, square brackets [] can be used here
	                                 'textarea_rows' => 40, // rows="..."
	                                 'tabindex'      => '',
	                                 'editor_css'    => '', // intended for extra styles for both visual and HTML editors buttons, needs to include the <style> tags, can use "scoped".
	                                 'editor_class'  => '', // add extra class(es) to the editor textarea
	                                 'teeny'         => false, // output the minimal editor config used in Press This
	                                 'dfw'           => false, // replace the default fullscreen with DFW (needs specific DOM elements and css)
	                                 'tinymce'       => true, // load TinyMCE, can be used to pass settings directly to TinyMCE using an array()
	                                 'quicktags'     => true // load Quicktags, can be used to pass settings directly to Quicktags using an array()
                                 );
                                 ?>
                                 <div class="editor" style="margin:30px;">
                                  <?php wp_editor( $tab_content, 'ywtm_content_tab_'.$key , $editor_args );?>
                                </div>
                             </p>
                        <?php
                            break;
                        case 'additional_information':
                            break;
                        case 'reviews':
                            break;

                    }
                ?>
                <p class="form-field">
                    <label><?php _e('Priority', 'yith-woocommerce-tab-manager');?></label>
                    <input type="number" min="0" name="ywtm_priority_tab_<?php esc_attr_e( $key );?>" value="<?php esc_attr_e( $tab_priority );?>">
                </p>
            </div>
        </div>
   <?php endforeach;?>
</div>
