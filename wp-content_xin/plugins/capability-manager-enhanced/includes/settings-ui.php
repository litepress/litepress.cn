<?php
/*
 * PublishPress Capabilities
 *
 * Plugin settings UI
 *
 */

class Capabilities_Settings_UI {
    public function __construct() {
        $this->settingsUI();
    }

    public function settingsUI() {
        $all_options        = pp_capabilities_settings_options();
        if (defined('PUBLISHPRESS_CAPS_PRO_VERSION')) {
            $first_table_display = 'display:none;';
            $first_active_tab    = '';
        } else {
            $first_table_display = '';
            $first_active_tab    = 'nav-tab-active';
        }
        ?>

        <ul id="publishpress-capability-settings-tabs" class="nav-tab-wrapper">
            <?php do_action('pp_capabilities_settings_before_menu_list'); ?>
            <li class="<?php echo esc_attr('nav-tab ' . $first_active_tab); ?>"><a href="#ppcs-tab-roles"><?php esc_html_e('Roles', 'capsman-enhanced');?></a></li>
            <li class="nav-tab"><a href="#ppcs-tab-capabilities"><?php esc_html_e('Capabilities', 'capsman-enhanced');?></a></li>
            <li class="nav-tab"><a href="#ppcs-tab-editor-features"><?php esc_html_e('Editor Features', 'capsman-enhanced');?></a></li>
            <?php do_action('pp_capabilities_settings_after_menu_list'); ?>
        </ul>

        <fieldset>
            <table id="akmin">
                <tr>
                    <td class="content">

                    <?php do_action('pp_capabilities_settings_before_menu_content'); ?>

                    <table class="form-table" role="presentation" id="ppcs-tab-roles" style="<?php echo esc_attr($first_table_display); ?>">
                        <tbody>

                            <tr>
                            <?php
                                $checked = checked(!empty(get_option('cme_capabilities_add_user_multi_roles', 0)), true, false);
                            ?>
                            <th scope="row"> <?php esc_html_e('Enable Multiple Roles When Creating Users', 'capsman-enhanced'); ?></th>
                            <td>
                                <label> 
                                <input type="checkbox" name="cme_capabilities_add_user_multi_roles" id="cme_capabilities_add_user_multi_roles" autocomplete="off" value="1" <?php echo esc_attr($checked);?>>
                                </label>
                                <br>
                            </td>
                        </tr>
                        </tbody>
                    </table>

                    <table class="form-table" role="presentation" id="ppcs-tab-capabilities" style="display:none;">
                        <tbody>

                        <tr>
                            <?php
                                $checked = checked(!empty(get_option('cme_capabilities_show_private_taxonomies', 0)), true, false);
                            ?>
                            <th scope="row"> <?php esc_html_e('Show Private Taxonomies', 'capsman-enhanced'); ?></th>
                            <td>
                                <label> 
                                <input type="checkbox" name="cme_capabilities_show_private_taxonomies" id="cme_capabilities_show_private_taxonomies" autocomplete="off" value="1" <?php echo esc_attr($checked);?>>
                                </label>
                                <br>
                            </td>
                        </tr>

                        <?php do_action('pp_capabilities_settings_after_capabilities_content'); ?>
                        
                        </tbody>
                    </table>

                    <table class="form-table" role="presentation" id="ppcs-tab-editor-features" style="display:none;">
                        <tbody>

                            <tr>
                            <?php
                                $checked = checked(!empty(get_option('cme_editor_features_private_post_type', 0)), true, false);
                            ?>
                            <th scope="row"> <?php esc_html_e('Support for Private Post Types', 'capsman-enhanced'); ?></th>
                            <td>
                                <label> 
                                <input type="checkbox" name="cme_editor_features_private_post_type" id="cme_editor_features_private_post_type" autocomplete="off" value="1" <?php echo esc_attr($checked);?>>
                                </label>
                                <br>
                            </td>
                        </tr>

                            <tr>
                            <?php
                                $checked = checked(!empty(get_option('cme_editor_features_classic_editor_tab', 0)), true, false);
                            ?>
                            <th scope="row"> <?php esc_html_e('Enable Classic Editor Tab', 'capsman-enhanced'); ?></th>
                            <td>
                                <label> 
                                <input type="checkbox" name="cme_editor_features_classic_editor_tab" id="cme_editor_features_classic_editor_tab" autocomplete="off" value="1" <?php echo esc_attr($checked);?>>
                                </label>
                                <br>
                            </td>
                        </tr>

                        </tbody>
                    </table>

                    </td>
                </tr>
            </table>
        </fieldset>

        <script>
        jQuery(document).ready(function ($) {

            $('#publishpress-capability-settings-tabs').find('li').click(function (e) {
                e.preventDefault();
                $('#publishpress-capability-settings-tabs').children('li').filter('.nav-tab-active').removeClass('nav-tab-active');
                $(this).addClass('nav-tab-active');

                $('[id^="ppcs-"]').hide();
                $($(this).find('a').first().attr('href')).show();
            });

        });
        </script>

    <?php
        echo "<input type='hidden' name='all_options' value='" . implode(',', array_map('esc_attr', $all_options)) . "' />";
    }
} // end class
