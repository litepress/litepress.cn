<?php
if (!defined('ABSPATH')) {
    exit;
}

global $product_object;
$product_id = $product_object->get_id();
$tab_id = $tab->ID;
?>
<div id="<?php echo $tab_id;?>_tab" class="panel woocommerce_options_panel yith_tab_manager_product">
    <div class="custom_tab_options" >
        <div class="form-field downloadable_files" style="padding:10px;">
            <table class="widefat" data-tab_id="<?php echo $tab_id;?>">
                <thead>
                    <tr>
                        <th class="sort">&nbsp;</th>
                        <th style="text-align: center;"><?php _e( 'Question', 'yith-woocommerce-tab-manager' ); ?> <span class="tips" data-tip="<?php _e( 'This is the question shown to the customers.', 'yith-woocommerce-tab-manager' ); ?>">[?]</span></th>
                        <th colspan="2" style="text-align: center;"><?php _e( 'Answer', 'yith-woocommerce-tab-manager' ); ?> <span class="tips" data-tip="<?php _e( 'This is the answer shown to the customers.', 'yith-woocommerce-tab-manager' ); ?>">[?]</span></th>
                        <th>&nbsp;</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    $faqs = get_post_meta( $product_id, $tab_id. '_custom_list_faqs', true );
                    if ( $faqs ) {
                        foreach ( $faqs as $key => $faq ) {
                            include('html-tab-faq.php');
                        }
                    }
                    else{?>
                        <input type="hidden" name="<?php echo $field_name;?>" class="yith_tab_hidden_field">
	                    <?php
                    }
                    ?>
                    </tbody>
                    <tfoot>
                    <tr>
                        <th colspan="5">
                            <a href="#" class="button insert" data-row="<?php
                            $faq = array(
                                'question' => '',
                                'answer' => ''
                            );
                            ob_start();
                            include('html-tab-faq.php');
                            echo esc_attr( ob_get_clean() );
                            ?>"><?php _e( 'Add Question', 'yith-woocommerce-tab-manager' ); ?></a>
                        </th>
                    </tr>
                    </tfoot>

                </table>

        </div>
    </div>
</div>