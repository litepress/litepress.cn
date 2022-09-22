<?php
/**
 * Capability Manager Edit Posts Permission.
 * Edit Posts permission and visibility per roles.
 *
 *    Copyright 2021, PublishPress <help@publishpress.com>
 *
 *    This program is free software; you can redistribute it and/or
 *    modify it under the terms of the GNU General Public License
 *    version 2 as published by the Free Software Foundation.
 *
 *    This program is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

require_once (dirname(CME_FILE) . '/includes/features/restrict-editor-features.php');

global $capsman, $_wp_post_type_features;
$roles = $capsman->roles;

$default_role = $capsman->get_last_role();

$classic_editor = pp_capabilities_is_classic_editor_available();

$def_post_types = apply_filters('pp_capabilities_feature_post_types', []);
asort($def_post_types);
$def_post_types = array_unique(array_merge(['post', 'page'], $def_post_types));

//gutenberg element
$gutenberg_elements = PP_Capabilities_Post_Features::elementsLayout();
$gutenberg_post_disabled = [];
$ce_post_disabled = [];

//classic editor element
if ($classic_editor) {
    $ce_elements = PP_Capabilities_Post_Features::elementsLayoutClassic();
}

foreach($def_post_types as $type_name) {

    $_disabled = get_option("capsman_feature_restrict_{$type_name}", []);
    $gutenberg_post_disabled[$type_name] = !empty($_disabled[$default_role]) ? (array)$_disabled[$default_role] : [];

    //classic editor cpt disabled element
    if ($classic_editor) {
        $_disabled = get_option("capsman_feature_restrict_classic_{$type_name}", []);
        $ce_post_disabled[$type_name] = !empty($_disabled[$default_role]) ? (array)$_disabled[$default_role] : [];
    }
}

$active_tab_slug = (!empty($_REQUEST['pp_caps_tab'])) ? sanitize_key($_REQUEST['pp_caps_tab']) : 'post';

$active_tab_type_obj = get_post_type_object($active_tab_slug);

$active_tab_text = is_object($active_tab_type_obj) 
    && isset($active_tab_type_obj->labels) 
    && isset($active_tab_type_obj->labels->singular_name)
    ? 
    $active_tab_type_obj->labels->singular_name : '';
?>

<div class="wrap publishpress-caps-manage pressshack-admin-wrapper pp-capability-menus-wrapper editor-features">
    <div id="icon-capsman-admin" class="icon32"></div>
    <h2><?php esc_html_e('Editor Feature Restriction', 'capsman-enhanced'); ?></h2>

    <form method="post" id="ppc-editor-features-form"
            action="admin.php?page=pp-capabilities-editor-features">
        <?php wp_nonce_field('pp-capabilities-editor-features'); ?>
        <input type="hidden" name="pp_caps_tab" value="<?php echo esc_attr($active_tab_slug);?>" />
        <div class="pp-columns-wrapper<?php echo defined('CAPSMAN_PERMISSIONS_INSTALLED') && !CAPSMAN_PERMISSIONS_INSTALLED ? ' pp-enable-sidebar' : '' ?>">
            <div class="pp-column-left">
                <table id="akmin">
                    <tr>
                        <td class="content">

                            <div class="publishpress-headline">
                                <span class="cme-subtext">
                                <span class='pp-capability-role-caption'>
                                <?php
                                esc_html_e('Select editor features to remove. Note that this screen cannot be used to grant additional features to any role.', 'capsman-enhanced');
                                ?>
                                </span>
                                </span>
                            </div>

                            <div class="publishpress-filters">
                                <select name="ppc-editor-features-role" class="ppc-editor-features-role">
                                    <?php
                                    foreach ($roles as $role_name => $name) :
                                        $name = translate_user_role($name);
                                        ?>
                                        <option value="<?php echo esc_attr($role_name);?>" <?php selected($default_role, $role_name);?>><?php echo esc_html($name);?></option>
                                    <?php
                                    endforeach;
                                    ?>
                                </select> &nbsp;

                                <img class="loading" src="<?php echo esc_url_raw($capsman->mod_url); ?>/images/wpspin_light.gif" style="display: none">


                                <input type="submit" name="editor-features-all-submit"
                                    value="<?php esc_attr_e('Save for all Post Types', 'capsman-enhanced') ?>"
                                    class="button-secondary ppc-editor-features-submit" style="float:right" />
                                    
                                <input type="submit" name="editor-features-submit"
                                    value="<?php esc_attr_e(sprintf(esc_html__('Save %s Restrictions', 'capsman-enhanced'), esc_html($active_tab_text))); ?>"
                                    class="button-primary ppc-editor-features-submit" style="float:right"
                                    data-current_cpt="<?php esc_attr_e(sprintf(esc_html__('Save %s Restrictions', 'capsman-enhanced'), 'post_type')); ?>" />

                                <input type="hidden" name="ppc-tab" value="<?php echo (!empty($_REQUEST['ppc-tab'])) ? sanitize_key($_REQUEST['ppc-tab']) : 'gutenberg';?>" />
                            </div>

                            <script type="text/javascript">
                            /* <![CDATA[ */
                            jQuery(document).ready(function($) {
                                $('li.gutenberg-tab').click(function() {
                                    $('div.publishpress-filters input[name=ppc-tab]').val('gutenberg');
                                });

                                $('li.classic-tab').click(function() {
                                    $('div.publishpress-filters input[name=ppc-tab]').val('classic');
                                });
                            });
                            /* ]]> */
                            </script>

                            <?php if ($classic_editor) { ?>
                                <ul class="nav-tab-wrapper">
                                    <li class="editor-features-tab gutenberg-tab nav-tab <?php if (empty($_REQUEST['ppc-tab']) || ('gutenberg' == $_REQUEST['ppc-tab'])) echo 'nav-tab-active';?>"
                                        data-tab=".editor-features-gutenberg"><a href="#"><?php esc_html_e('Gutenberg', 'capsman-enhanced') ?></a></li>

                                    <li class="editor-features-tab classic-tab nav-tab <?php if (!empty($_REQUEST['ppc-tab']) && ('classic' == $_REQUEST['ppc-tab'])) echo 'nav-tab-active';?>"
                                        data-tab=".editor-features-classic"><a href="#"><?php esc_html_e('Classic', 'capsman-enhanced') ?></a></li>
                                </ul>
                            <?php } ?>

                            <div id="pp-capability-menu-wrapper" class="postbox">
                                <div class="pp-capability-menus">

                                    <div class="pp-capability-menus-wrap">
                                        <div id="pp-capability-menus-general"
                                                class="pp-capability-menus-content editable-role"
                                                style="display: block;">
                                                <div id="ppc-capabilities-wrapper" class="postbox">

                                                <div class="ppc-capabilities-tabs">
                                                    <ul>
                                                        <?php

                                                            foreach($def_post_types as $type_name) {
                                                                $type_obj = get_post_type_object($type_name);
                                                                $active_class = ($type_name === $active_tab_slug) ? 'ppc-capabilities-tab-active' : '';

                                                                $disabled_count  = 0;
                                                                $disabled_count += (is_array($gutenberg_post_disabled) && isset($gutenberg_post_disabled[$type_name])) ? count($gutenberg_post_disabled[$type_name]) : 0;
                                                                $disabled_count += (is_array($ce_post_disabled) && isset($ce_post_disabled[$type_name])) ? count($ce_post_disabled[$type_name]) : 0;

                                                                ?>
                                                                <li data-slug="<?php esc_attr_e($type_name); ?>" 
                                                                    data-content="cme-cap-type-tables-<?php esc_attr_e($type_name); ?>" 
                                                                    data-name="<?php esc_attr_e($type_obj->labels->singular_name); ?>"
                                                                    class="<?php esc_attr_e($active_class); ?>">
                                                                    <?php esc_html_e($type_obj->labels->singular_name); ?>
                                                                    <?php if ($disabled_count > 0) : ?>
                                                                        <span class="pp-capabilities-feature-count">
                                                                            <?php echo esc_html__('Restricted:', 'capsman-enhanced') . ' ' . esc_html($disabled_count); ?>
                                                                        </span>
                                                                    <?php endif; ?>
                                                                </li>
                                                                <?php
                                                            }
                                                        ?>
                                                    </ul>
                                                </div>

                                                <div class="ppc-capabilities-content">
                                                    <?php
                                                        //we want to remove empty header for row without feature for post typr
                                                        $empty_post_type_feature       = [];
                                                        $empty_post_type_feature_class = [];
                                                        foreach($def_post_types as $type_name) {
                                                            $type_obj = get_post_type_object($type_name);
                                                            $active_style = ($type_name === $active_tab_slug) ? '' : 'display:none;';
                                                            ?>
                                                            <div id="cme-cap-type-tables-<?php esc_attr_e($type_name); ?>" style="<?php esc_attr_e($active_style); ?>">
                                                                <?php
                                                                include(dirname(__FILE__) . '/editor-features-gutenberg.php');

                                                                if ($classic_editor) {
                                                                    include(dirname(__FILE__) . '/editor-features-classic.php');
                                                                }
                                                                ?>
                                                            </div>
                                                            <?php
                                                        }
                                                    ?>
                                                </div>
                                            </div>


                                        </div>
                                    </div>

                                </div>
                            </div>


                            <div class="editor-features-footer-meta">
                                <?php if (!$classic_editor) : ?>
                                <input type="submit" name="editor-features-classic-editor-toggle"
                                    value="<?php esc_attr_e('show Classic Editor controls', 'capsman-enhanced') ?>"
                                    class="button-secondary ppc-editor-classic-toggle-button" />
                                <?php endif; ?>

                                <input type="submit" name="editor-features-all-submit"
                                    value="<?php esc_attr_e('Save for all Post Types', 'capsman-enhanced') ?>"
                                    class="button-secondary ppc-editor-features-submit" style="float:right" />
                                
                                <input type="submit" name="editor-features-submit"
                                    value="<?php esc_attr_e(sprintf(esc_html__('Save %s Restrictions', 'capsman-enhanced'), esc_html($active_tab_text))); ?>"
                                    class="button-primary ppc-editor-features-submit" style="float:right"
                                    data-current_cpt="<?php esc_attr_e(sprintf(esc_html__('Save %s Restrictions', 'capsman-enhanced'), 'post_type')); ?>" />
                            </div

                        </td>
                    </tr>
                </table>
            </div><!-- .pp-column-left -->
            <?php if (defined('CAPSMAN_PERMISSIONS_INSTALLED') && !CAPSMAN_PERMISSIONS_INSTALLED) { ?>
                <div class="pp-column-right">
                    <?php
                    $banners = new PublishPress\WordPressBanners\BannersMain;
                    $banners->pp_display_banner(
                        esc_html__( 'Recommendations for you', 'capsman-enhanced' ),
                        esc_html__( 'Control permissions for individual posts and pages', 'capsman-enhanced' ),
                        array(
                            esc_html__( 'Choose who can read and edit each post.', 'capsman-enhanced' ),
                            esc_html__( 'Allow specific user roles or users to manage each post.', 'capsman-enhanced' ),
                            esc_html__( 'PublishPress Permissions is 100% free to install.', 'capsman-enhanced' )
                        ),
                        admin_url( 'plugin-install.php?s=publishpress-ppcore-install&tab=search&type=term' ),
                        esc_html__( 'Click here to install PublishPress Permissions', 'capsman-enhanced' ),
                        'install-permissions.jpg'
                    );
                    ?>
                </div><!-- .pp-column-right -->
            <?php } ?>
        </div><!-- .pp-columns-wrapper -->
    </form>

    <?php if (!defined('PUBLISHPRESS_CAPS_PRO_VERSION') || get_option('cme_display_branding')) {
        cme_publishpressFooter();
    }
    ?>
</div>

<style>
    <?php 
        if (!empty($empty_post_type_feature_class)) {
            echo esc_html(implode(', ', $empty_post_type_feature_class));
            echo esc_html('{display: none !important}');
        }
    ?>
    span.menu-item-link {
        webkit-user-select: none; /* Safari */
        -moz-user-select: none; /* Firefox */
        -ms-user-select: none; /* IE10+/Edge */
        user-select: none; /* Standard */
    }

    input.check-all-menu-item {margin-top: 5px !important;}

    .pp-promo-overlay-row .pp-promo-upgrade-notice {
        left: calc(50% - 125px) !important;
    }
    table#akmin .pp-capability-menus-select .restrict-column {
        text-align: right !important;
    }
    table#akmin .pp-capability-menus-select tr:first-of-type {
        border-right: 1px solid #c3c4c7;
    }
    table#akmin .pp-capability-menus-select tr:first-of-type th {
        border-top: 1px solid #c3c4c7;
    }
    input[name="editor-features-all-submit"].ppc-editor-features-submit {
        margin-left: 10px;
    }
    .pp-columns-wrapper .nav-tab-wrapper,
    .pp-columns-wrapper .postbox {
        border: unset;
    }
    .pp-capability-menus {
        overflow: initial;
    }
    .pp-capability-menus-wrapper.editor-features #pp-capability-menus-general #ppc-capabilities-wrapper {
        border: 1px solid #c3c4c7;
    }
    .pp-capability-menus-wrapper.editor-features #ppc-capabilities-wrapper .ppc-capabilities-content > div {
        padding-bottom: 0 !important;
    }
</style>

<script type="text/javascript">
    /* <![CDATA[ */
    jQuery(document).ready(function ($) {

         // Tabs and Content display
         $('.ppc-capabilities-tabs > ul > li').click( function() {
            var $pp_tab = $(this).attr('data-content');
            var $current_cpt = $('input[name="editor-features-submit"]').attr('data-current_cpt');
            var $button_text = $current_cpt.replace("post_type", $(this).attr('data-name'));

            $("[name='pp_caps_tab']").val($(this).attr('data-slug'));

            // Show current Content
            $('.ppc-capabilities-content > div').hide();
            $('#' + $pp_tab).show();

            // Active current Tab
            $('.ppc-capabilities-tabs > ul > li').removeClass('ppc-capabilities-tab-active');
            $(this).addClass('ppc-capabilities-tab-active');

            //Update button text
            $('input[name="editor-features-submit"]').val($button_text);
            
        });

        // -------------------------------------------------------------
        //   Set form action attribute to include role
        // -------------------------------------------------------------
        $('#ppc-editor-features-form').attr('action', '<?php echo esc_url_raw(admin_url('admin.php?page=pp-capabilities-editor-features&role=' . $default_role . '')); ?>');

        // -------------------------------------------------------------
        //   Instant restricted item class
        // -------------------------------------------------------------
        $(document).on('change', '.pp-capability-menus-wrapper .ppc-menu-row .check-item', function () {
            var current_tab;

            <?php if ($classic_editor) { ?>
                if ($('.nav-tab-wrapper .classic-tab').hasClass('nav-tab-active')) {
                    current_tab = 'classic';
                } else {
                    current_tab = 'gutenberg';
                }
            <?php } else { ?>
                current_tab = 'gutenberg';
            <?php } ?>

            //add class if feature is restricted for any post type
            var anyRestricted = $(this).closest('tr').find('input:checked').length > 0;
            $(this).closest('tr').find('.menu-item-link').toggleClass('restricted', anyRestricted);

            var isChecked = $(this).is(':checked');

            //toggle all checkbox
            if ($(this).hasClass('check-all-menu-item')) {
                var suffix = ('gutenberg' == current_tab) ? '' : current_tab + '_';
                $("input[type='checkbox'][name='capsman_feature_restrict_" + suffix + $(this).data('pp_type') + "[]']").prop('checked', isChecked);

                $('.' + current_tab + '.menu-item-link').each(function(i,e) {
                    $(this).toggleClass('restricted', $(this).closest('tr').find('input:checked').length > 0);
                });
            } else {
                $('.' + current_tab + '.check-all-menu-link').removeClass('restricted').prop('checked', false);
            }
        });

        $(document).on("click", "span.menu-item-link", function (e) {
            if($(e.target).parent().hasClass('ppc-custom-features-delete')){
                return;
            }
            var chks = $(this).closest('tr').find('input');
            $(chks).prop('checked', !$(this).hasClass('restricted'));
            $(this).toggleClass('restricted', $(chks).filter(':checked').length);
        });

        // -------------------------------------------------------------
        //   Load selected roles menu
        // -------------------------------------------------------------
        $(document).on('change', '.pp-capability-menus-wrapper .ppc-editor-features-role', function () {

            //disable select
            $('.pp-capability-menus-wrapper .ppc-editor-features-role').attr('disabled', true);

            //hide button
            $('.pp-capability-menus-wrapper .ppc-editor-features-submit').hide();

            //show loading
            $('#pp-capability-menu-wrapper').hide();
            $('div.publishpress-caps-manage img.loading').show();

            //go to url
            window.location = '<?php echo esc_url_raw(admin_url('admin.php?page=pp-capabilities-editor-features&role=')); ?>' + $(this).val() + '';

        });


        // -------------------------------------------------------------
        //   Editor features tab
        // -------------------------------------------------------------
        $('.editor-features-tab').click(function (e) {
            e.preventDefault();
            $('.editor-features-tab').removeClass('nav-tab-active');
            $(this).addClass('nav-tab-active');
            $('.pp-capability-menus-select').hide();
            $('.editor-features-classic-show').hide();
            $('.editor-features-gutenberg-show').hide();
            $($(this).attr('data-tab')).show();
            $($(this).attr('data-tab')+'-show').show();
        });

    });
    /* ]]> */
</script>
<?php
