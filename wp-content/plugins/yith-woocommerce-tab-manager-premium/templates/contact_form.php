<?php
if ( ! is_array( $form ) ) {
	$form = array();
}
global  $product;
$show_name     = ( isset( $form['name']['show'] ) && ( 'yes' == $form['name']['show'] || 'on' == $form['name']['show'] ) ) ? true : false;
$show_req_name = ( isset( $form['name']['req'] ) && ( 'yes' == $form['name']['req'] || 'on' == $form['name']['req'] ) ) ? true : false;

$show_webaddr     = ( isset( $form['webaddr']['show'] ) && ( 'yes' == $form['webaddr']['show'] || 'on' == $form['webaddr']['show'] ) ) ? true : false;
$show_req_webaddr = ( isset( $form['webaddr']['req'] ) && ( 'yes' == $form['webaddr']['req'] || 'on' == $form['webaddr']['req'] ) ) ? true : false;

$show_subject     = ( isset( $form['subj']['show'] ) && ( 'yes' == $form['subj']['show'] || 'on' == $form['subj']['show'] ) ) ? true : false;
$show_req_subject = ( isset( $form['subj']['req'] ) && ( 'yes' == $form['subj']['req'] || 'on' == $form['subj']['req'] ) ) ? true : false;

$col        = $show_subject ? count( $form ) : count( $form )+1;
$col        = 'ywtm_col_' . $col;
$star_name  = $show_req_name ? '*' : '';
$star_addr  = $show_req_webaddr ? '*' : '';
$star_subj  = $show_subject ? '*' : '';
$product_id =$product->get_id();
?>
<div class="yit_wc_tab_manager_contact_form_container ywtm_content_tab">
    <div class="error_messages"></div>
    <form class="ywtm_contact_form" method="post">
        <fieldset>
            <div class="primary_contact_information">
				<?php if ( $show_name ): ?>
                    <div class="contact_name_field <?php echo $col; ?> contact_field">
                        <input type="text" name="ywtm_name_contact_field"
                               placeholder="<?php _e( 'Your name', 'yith-woocommerce-tab-manager' ); ?><?php echo $star_name; ?>"/>
						<?php if ( $show_req_name ): ?>
                            <input type="hidden" name="ywtm_req_name" value="req"/>
						<?php endif; ?>
                    </div>
				<?php endif; ?>

                <div class="contact_email_field <?php echo $col; ?> contact_field">
                    <input type="text" name="ywtm_email_contact_field"
                           placeholder="<?php _e( 'Email', 'yith-woocommerce-tab-manager' ); ?>*"/>
                    <input type="hidden" name="ywtm_req_email" value="req"/>
                </div>

				<?php if ( $show_webaddr ): ?>
                    <div class="contact_webaddr_field <?php echo $col; ?> contact_field">
                        <input type="text" name="ywtm_webaddr_contact_field"
                               placeholder="<?php _e( 'Website', 'yith-woocommerce-tab-manager' ); ?><?php echo $star_addr; ?>"/>
						<?php if ( $show_req_webaddr ): ?>
                            <input type="hidden" name="ywtm_req_webaddr" value="req"/>
						<?php endif; ?>
                    </div>
				<?php endif; ?>
            </div>
            <div class="secondary_contact_information">
				<?php if ( $show_subject ): ?>
                    <div class="contact_subj_field ywtm_col_1">
                        <input type="text" name="ywtm_subj_contact_field"
                               placeholder="<?php _e( 'Subject', 'yith-woocommerce-tab-manager' ); ?><?php echo $star_subj; ?>"/>
						<?php if ( $show_req_subject ): ?>
                            <input type="hidden" name="ywtm_req_subj" value="req"/>
						<?php endif; ?>
                    </div>
				<?php endif; ?>
                <div class="contact_textarea_field ywtm_col_1">
                    <textarea name="ywtm_info_contact_field"
                              placeholder="<?php _e( 'Your Message', 'yith-woocommerce-tab-manager' ); ?>*"></textarea>
                    <input type="hidden" name="ywtm_req_info" value="req"/>
                    <input type="hidden" name="ywtm_product_id" value="<?php echo $product_id; ?>"/>
                    <input type="hidden" name="ywtm_action" value="ywtm_sendermail"/>
                    <?php
                    if ( defined( 'ICL_LANGUAGE_CODE' ) ) {
	                    echo '<input type="hidden" name ="ywtm_language" value="'.ICL_LANGUAGE_CODE.'">';
                    }
                    ?>

                    <div style="position:absolute; z-index:-1; <?php echo( is_rtl() ? "margin-right:-9999999px;" : "margin-left:-9999999px;" ); ?> ">
                        <input type="text" name="ywtm_bot" class="ywtm_bot"/></div>
                    <span id="ywtm_btn_container"><input type="submit" class="ywtm_btn_sendmail"
                                                         value="<?php _e( 'Send', 'yith-woocommerce-tab-manager' ); ?>"/></span>
					<?php wp_nonce_field( 'ywtm-sendmail' ); ?>
                </div>

            </div>
        </fieldset>
    </form>
    <span>*<?php _e( 'required', 'yith-woocommerce-tab-manager' ); ?></span>
</div>
