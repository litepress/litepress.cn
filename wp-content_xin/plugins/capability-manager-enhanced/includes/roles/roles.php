<div class="wrap publishpress-caps-manage pressshack-admin-wrapper pp-capability-roles-wrapper">

    <?php
    if (isset($_GET['add']) && $_GET['add'] === 'new_item') {
        pp_capabilities_roles()->admin->get_roles_edit_ui();
     }else{ ?>
    <div class="wrap">
        <h1 class="wp-heading-inline"><?php esc_html_e('Roles', 'capsman-enhanced') ?> </h1>
        <a href="<?php echo esc_url(admin_url('admin.php?page=pp-capabilities-roles&add=new_item')); ?>" class="page-title-action">
            <?php esc_html_e('Add New', 'capsman-enhanced'); ?>
        </a>
        <?php
        if (isset($_REQUEST['s']) && $search_str = esc_attr(wp_unslash(sanitize_text_field($_REQUEST['s'])))) {
            /* translators: %s: search keywords */
            printf(' <span class="subtitle">' . esc_html__('Search results for &#8220;%s&#8221;', 'capsman-enhanced') . '</span>', esc_html($search_str));
        }

        //the roles table instance
        $table = pp_capabilities_roles()->admin->get_roles_list_table();
        $table->prepare_items();
        pp_capabilities_roles()->notify->display();
        ?>
        <hr class="wp-header-end">
        <div id="ajax-response"></div>
        <form class="search-form wp-clearfix" method="get">
            <?php $table->search_box(esc_html__('Search Roles', 'capsman-enhanced'), 'roles'); ?>
        </form>
        <div id="col-container" class="wp-clearfix">
            <div class="col-wrap">
                <form action="" method="post">
                    <?php $table->display(); //Display the table ?>
                </form>
                <div class="form-wrap edit-term-notes">
                    <p><?php esc_html__('Description here.', 'capsman-enhanced') ?></p>
                </div>
            </div>
        </div>
        <form method="get">
            <?php $table->inline_edit() ?>
        </form>

    </div>
    <?php } ?>


    <?php if (!defined('PUBLISHPRESS_CAPS_PRO_VERSION') || get_option('cme_display_branding')) {
        cme_publishpressFooter();
    }
    ?>
</div>
<?php
