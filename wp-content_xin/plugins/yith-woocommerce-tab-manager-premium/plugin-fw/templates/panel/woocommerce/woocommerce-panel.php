<?php add_thickbox(); ?>
<div class="wrap <?php echo $wrap_class ?>">
    <div id="icon-users" class="icon32"><br/></div>
    <?php do_action( 'yith_plugin_fw_before_woocommerce_panel', $page ) ?>
    <?php if ( !empty( $available_tabs ) ) {
        $this->print_panel_content();
    }
    ?>
</div>