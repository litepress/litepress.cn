<?php
/**
 * Capability Manager Backup Tool.
 * Provides backup and restore functionality to Capability Manager.
 *
 * @version		$Rev: 198515 $
 * @author		Jordi Canals
 * @copyright   Copyright (C) 2009, 2010 Jordi Canals
 * @license		GNU General Public License version 2
 * @link		http://alkivia.org
 * @package		Alkivia
 * @subpackage	CapsMan
 *
 *
 *	Copyright 2009, 2010 Jordi Canals <devel@jcanals.cat>
 *
 *	Modifications Copyright 2020, PublishPress <help@publishpress.com>
 *
 *	This program is free software; you can redistribute it and/or
 *	modify it under the terms of the GNU General Public License
 *	version 2 as published by the Free Software Foundation.
 *
 *	This program is distributed in the hope that it will be useful,
 *	but WITHOUT ANY WARRANTY; without even the implied warranty of
 *	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *	GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

global $wpdb;

$auto_backups = $wpdb->get_results("SELECT option_name, option_value FROM $wpdb->options WHERE option_name LIKE 'cme_backup_auto_%' ORDER BY option_id DESC");
?>

<div class="wrap publishpress-caps-manage publishpress-caps-backup pressshack-admin-wrapper">
    <div id="icon-capsman-admin" class="icon32"></div>
    <h2><?php esc_html_e('Backup Tool for PublishPress Capabilities', 'capsman-enhanced');?></h2>


    <form method="post" action="admin.php?page=pp-capabilities-backup" enctype="multipart/form-data">
        <?php wp_nonce_field('pp-capabilities-backup'); ?>

        <div class="pp-columns-wrapper<?php echo defined('CAPSMAN_PERMISSIONS_INSTALLED') && !CAPSMAN_PERMISSIONS_INSTALLED ? ' pp-enable-sidebar' : '' ?>">
            <div class="pp-column-left">
                <ul id="publishpress-capability-backup-tabs" class="nav-tab-wrapper">
                    <li class="nav-tab nav-tab-active"><a href="#ppcb-tab-restore"><?php esc_html_e('Restore', 'capsman-enhanced');?></a></li>
                    <li class="nav-tab"><a href="#ppcb-tab-backup"><?php esc_html_e('Backup', 'capsman-enhanced');?></a></li>
                    <li class="nav-tab"><a href="#ppcb-tab-reset"><?php esc_html_e('Reset Roles', 'capsman-enhanced');?></a></li>
                    <li class="nav-tab"><a href="#ppcb-tab-import-export"><?php esc_html_e('Export / Import', 'capsman-enhanced');?></a></li>
                </ul>

                <fieldset>
                    <table id="akmin">
                        <tr>
                            <td class="content">

                                <div id="ppcb-tab-backup" class="postbox ppc-postbox" style="display:none;">
                                    <h2><?php esc_html_e('Backup Roles and Capabilities', 'capsman-enhanced'); ?></h2>
                                    <div>
                                        <p class="description">
                                        <?php
                                        $max_auto_backups = (defined('CME_AUTOBACKUPS')) ? (int) CME_AUTOBACKUPS : 20;
                                        printf(esc_html__('PublishPress Capabilities automatically creates a backup on installation and whenever you save changes. The initial backup and last %d auto-backups are kept.', 'capsman-enhanced'), esc_attr($max_auto_backups));
                                        ?>
                                        <?php esc_html_e('A backup created on this screen replaces any previous manual backups, but is never automatically replaced.', 'capsman-enhanced');?>
                                        </p>

                                        <div class="pp-caps-backup-button">
                                            <input type="submit" name="save_backup"
                                                    value="<?php esc_attr_e('Manual Backup', 'capsman-enhanced') ?>"
                                                    class="button-primary"/>
                                        </div>
                                    </div>

                                </div>

                                <?php
                                $listed_manual_backup = false;
                                $backup_datestamp = get_option('capsman_backup_datestamp');
                                $last_caption = ($backup_datestamp) ? sprintf(esc_html__('Last Manual Backup - %s', 'capsman-enhanced'), date('j M Y, g:i a', $backup_datestamp)) : esc_html__('Last Backup', 'capsman-enhanced');
                                ?>

                                <div id="ppcb-tab-restore" class="postbox ppc-postbox">
                                    <h2><?php esc_html_e('Restore Previous Roles and Capabilities', 'capsman-enhanced'); ?></h2>
                                    <div>
                                        <p class="description">
                                        <?php esc_html_e('PublishPress Capabilities automatically creates a backup on installation and whenever you save changes.', 'capsman-enhanced');?>
                                        <?php esc_html_e('On this screen, you can restore an earlier version of your roles and capabilities.', 'capsman-enhanced');?>
                                        </p>

                                        <p style="margin-top:15px;"><strong><?php esc_html_e('Available Backups:', 'capsman-enhanced'); ?></strong></p>

                                        <table width='100%' class="form-table">
                                            <tr>
                                                <td class="cme-backup-list">
                                                    <div id="cme_select_restore_div">
                                                    <ul id="cme_select_restore">
                                                        <?php foreach ($auto_backups as $row):
                                                            $arr = explode('_', str_replace('cme_backup_auto_', '', $row->option_name));
                                                            $arr[1] = str_replace('-', ':', $arr[1]);
                                                            $date_caption = implode(' ', $arr);

                                                            if (!$listed_manual_backup && ($backup_datestamp > strtotime($date_caption))) :
                                                                $manual_date_caption = date('Y-m-d, g:i a', $backup_datestamp);
                                                                $last_backup = get_option('capsman_last_backup');
                                                                if(!$last_backup){
                                                                    $last_backup = __('all roles', 'capsman-enhanced');
                                                                }
                                                            ?>
                                                                <li>
                                                                <input type="radio" name="select_restore" value="restore" id="cme_restore_manual">
                                                                <label for="cme_restore_manual"><?php printf(esc_html__('Manual backup of %s (%s)', 'capsman-enhanced'), esc_html($last_backup), esc_html($manual_date_caption)); ?></label>
                                                                </li>
                                                                <?php
                                                                $listed_manual_backup = true;
                                                            endif;
                                                            ?>

                                                            <?php
                                                            $date_caption = str_replace(' ', ', ', $date_caption);
                                                            $date_caption = str_replace(', am', ' am', $date_caption);
                                                            $date_caption = str_replace(', pm', ' pm', $date_caption);
                                                            ?>

                                                            <li>
                                                            <input type="radio" name="select_restore" value="<?php echo esc_attr($row->option_name);?>" id="<?php echo esc_attr($row->option_name);?>">
                                                            <label for="<?php echo esc_attr($row->option_name);?>"><?php printf(esc_html__('Auto-backup of all roles (%s)', 'capsman-enhanced'), esc_html($date_caption)); ?></label>
                                                            </li>
                                                        <?php endforeach; ?>

                                                        <?php
                                                        if ($initial = get_option('capsman_backup_initial')):?>
                                                            <li>
                                                            <input type="radio" name="select_restore" value="restore_initial" id="cme_restore_initial">
                                                            <label for="cme_restore_initial"><?php esc_html_e('Initial backup of all roles', 'capsman-enhanced'); ?></label>
                                                            </li>
                                                        <?php endif; ?>
                                                    <!-- </select> -->
                                                    </ul>
                                                    </div>

                                                    <div class="cme-restore-button">
                                                    <input type="submit" name="restore_backup"
                                                           value="<?php esc_attr_e('Restore Selected Roles', 'capsman-enhanced') ?>"
                                                           class="button-primary"/>

                                                    <div class="cme-selected-backup-caption"></div>
                                                    </div>
                                                </td>

                                                <td class="cme-backup-info">
                                                    <div class="cme_backup_info_changes_only" style="display:none">
                                                    <input type="checkbox" class="cme_backup_info_changes_only" autocomplete="off" checked="checked"> <?php esc_html_e('Show changes from current roles only', 'capsman-enhanced');?>
                                                    </div>

                                                <?php
                                                    global $wp_roles;

                                                    $backup_datestamp = get_option('capsman_backup_initial_datestamp');
                                                    $initial_caption = ($backup_datestamp) ? sprintf(esc_html__('Initial Backup - %s', 'capsman-enhanced'), date('j M Y, g:i a', $backup_datestamp)) : esc_html__('Initial Backup', 'capsman-enhanced');

                                                    $backups = array(
                                                        'capsman_backup_initial' => $initial_caption,
                                                    );

                                                    if (empty($capsman_backup)) {
                                                        $backups['capsman_backup'] = $last_caption;
                                                    }

                                                    foreach ($auto_backups as $row) {
                                                        $arr = explode('_', str_replace('cme_backup_auto_', '', $row->option_name));
                                                        $arr[1] = str_replace('-', ':', $arr[1]);

                                                        $date_caption = implode(' ', $arr);
                                                        $date_caption = str_replace(' ', ', ', $date_caption);
                                                        $date_caption = str_replace(', am', ' am', $date_caption);
                                                        $date_caption = str_replace(', pm', ' pm', $date_caption);

                                                        $option_name = sanitize_key($row->option_name);
                                                        $backups[$option_name] = "Auto-backup from " . $date_caption;
                                                    }

                                                    foreach ($backups as $name => $caption) {
                                                        if ($backup_data = get_option($name)) :?>
                                                            <div id="cme_display_<?php echo esc_attr($name); ?>" style="display:none;"
                                                                class="cme-show-backup">
                                                                <h3><?php printf(esc_html__("%s (%s roles)", 'capsman-enhanded'), esc_html($caption), count($backup_data)); ?></h3>

                                                                <?php
                                                                foreach ($wp_roles->role_objects as $role_name => $role_object) {
                                                                    if (empty($backup_data[$role_name])) {
                                                                        $role_caption = $role_object->name;
                                                                        ?>
                                                                        <h4><span class="cme-change cme-minus"><?php echo (esc_html(translate_user_role($role_caption)));?></span> <?php esc_html_e('(this role will be removed if you restore backup)', 'capsman-enhanced');?></h4>
                                                                        <?php
                                                                    }
                                                                }
                                                                ?>

                                                                <?php foreach ($backup_data as $role_name => $props) :
                                                                    if (isset($wp_roles->role_objects[$role_name]->capabilities)) {
                                                                        $props['capabilities'] = array_merge(
                                                                            array_fill_keys(array_keys($wp_roles->role_objects[$role_name]->capabilities), 0),
                                                                            $props['capabilities']
                                                                        );
                                                                    }
                                                                ?>
                                                                    <?php if (!isset($props['name'])) continue; ?>
                                                                    <?php
                                                                    $level = 0;
                                                                    for ($i = 10; $i >= 0; $i--) {
                                                                        if (!empty($props['capabilities']["level_{$i}"])) {
                                                                            $level = $i;
                                                                            break;
                                                                        }
                                                                    }
                                                                    ?>
                                                                    <?php
                                                                    $role_caption = $props['name'];
                                                                    $role_class = (empty($wp_roles->role_objects[$role_name])) ? 'cme-change cme-plus' : '';
                                                                    ?>

                                                                    <h4 class="<?php echo esc_attr($role_class);?>"><?php printf(esc_html__('%s (level %s)', 'capsman-enhanced'), esc_html(translate_user_role($role_caption)), esc_html($level)); ?></h4>

                                                                    <?php
                                                                    $items = [];
                                                                    $any_changes = false;

                                                                    ksort($props['capabilities']);
                                                                    foreach ($props['capabilities'] as $cap_name => $val) :
                                                                        if (0 === strpos($cap_name, 'level_')) continue;
                                                                        ?>
                                                                        <?php
                                                                        if ($val && (empty($wp_roles->role_objects[$role_name]) || empty($wp_roles->role_objects[$role_name]->capabilities[$cap_name]))) {
                                                                            $class = 'cme-change cme-plus';

                                                                        } elseif ((false === $props['capabilities'][$cap_name]) && (!isset($wp_roles->role_objects[$role_name]->capabilities[$cap_name]) || false !== $wp_roles->role_objects[$role_name]->capabilities[$cap_name])) {
                                                                            $class = 'cme-change cme-negate';

                                                                        } elseif (!$val && !empty($wp_roles->role_objects[$role_name]->capabilities[$cap_name])) {
                                                                            $class = 'cme-change cme-minus';
                                                                            $cap_name = "&nbsp;&nbsp;" . esc_attr($cap_name) . "&nbsp;&nbsp;";
                                                                        } else {
                                                                            $class = '';
                                                                        }

                                                                        $items[$cap_name] = $class;

                                                                        $any_changes = $any_changes || $class;
                                                                        ?>
                                                                    <?php endforeach; ?>

                                                                    <?php if ($items) :?>
                                                                        <ul class="pp-restore-caps">
                                                                        <?php foreach($items as $cap_name => $class) :?>
                                                                            <li class="<?php echo esc_attr($class);?>"><?php echo esc_html($cap_name);?></li>
                                                                        <?php endforeach; ?>
                                                                        </ul>
                                                                    <?php endif;?>

                                                                    <?php if (!$any_changes):?>
                                                                        <span class="pp-restore-caps-no-change">
                                                                        <?php esc_html_e('No changes', 'capsman-enhanced');?>
                                                                        </span>
                                                                    <?php endif;?>
                                                                <?php endforeach; ?>
                                                            </div>
                                                        <?php endif;
                                                    }
                                                ?>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>


                                <div id="ppcb-tab-reset" class="postbox ppc-postbox" style="display:none;">
                                    <h2><?php if (!in_array(get_locale(), ['en_EN', 'en_US'])) esc_html_e('Reset WordPress Defaults', 'capsman-enhanced'); else echo 'Reset Roles to WordPress Defaults'; ?></h2>
                                    <div>
                                        <p><span class="pp-caps-warning"><?php esc_html_e('Warning:', 'capsman-enhanced'); ?></span> <?php if (!in_array(get_locale(), ['en_EN', 'en_US'])) esc_html_e('Reseting default Roles and Capabilities will set them to the WordPress install defaults.', 'capsman-enhanced'); else echo 'This will delete and/or modify stored role definitions.'; ?>
                                            <?php
                                            esc_html_e('If you have installed any plugin that adds new roles or capabilities, these will be lost.', 'capsman-enhanced') ?>
                                            <?php if (!in_array(get_locale(), ['en_EN', 'en_US'])) esc_html_e('It is recommended to use this only as a last resource!', 'capsman-enhanced'); else echo('It is recommended to use this only as a last resort!'); ?>
                                        </p>
                                        <p><a class="ak-delete button-primary"
                                                                         title="<?php echo esc_attr__('Reset Roles and Capabilities to WordPress defaults', 'capsman-enhanced') ?>"
                                                                         href="<?php echo esc_url_raw(wp_nonce_url("admin.php?page=pp-capabilities-backup&amp;action=reset-defaults", 'capsman-reset-defaults')); ?>"
                                                                         onclick="if ( confirm('<?php echo esc_js(__("You are about to reset Roles and Capabilities to WordPress defaults.\n 'Cancel' to stop, 'OK' to reset.", 'capsman-enhanced')); ?>') ) { return true;}return false;"><?php esc_html_e('Reset to WordPress defaults', 'capsman-enhanced') ?></a>

                                    </div>
                                </div>


                                <div id="ppcb-tab-import-export" style="display:none;">
                                    <div class="postbox ppc-postbox">
                                        <h2><?php esc_html_e('Export Settings', 'capsman-enhanced'); ?></h2>
                                        <div>
                                            <p><?php esc_html_e('Export the plugin settings for this site as a .json file. This allows you to easily import the configuration into another site.', 'capsman-enhanced'); ?></p>
                                            <ul>
                                                <li>
                                                    <input id="pp_capabilities_export_roles" name="pp_capabilities_export_section[]" type="checkbox" value="user_roles" checked />
                                                    <label for="pp_capabilities_export_roles">
                                                        <?php esc_html_e('Roles and Capabilities', 'capsman-enhanced'); ?>
                                                    </label>
                                                </li>
                                                <?php
                                                    $backup_sections = pp_capabilities_backup_sections();
                                                    foreach($backup_sections as $backup_key => $backup_section){
                                                        ?>
                                                        <li>
                                                            <input id="pp_capabilities_export_<?php echo esc_attr($backup_key); ?>" name="pp_capabilities_export_section[]" type="checkbox" value="<?php echo esc_attr($backup_key); ?>" checked />
                                                            <label for="pp_capabilities_export_<?php echo esc_attr($backup_key); ?>"> <?php esc_html_e($backup_section['label']); ?> </label>
                                                        </li>
                                                        <?php
                                                    }
                                                ?>
                                            </ul>
                                                <p>
                                                <input type="submit" name="export_backup"
                                                        value="<?php esc_attr_e('Export', 'capsman-enhanced') ?>"
                                                        class="button-primary"/>
                                                </p>
                                        </div>
                                    </div>
                                    <div class="postbox ppc-postbox">
                                        <h2><?php esc_html_e('Import Settings', 'capsman-enhanced'); ?></h2>
                                        <p><span class="pp-caps-warning"><?php esc_html_e('Warning:', 'capsman-enhanced'); ?></span> <?php esc_html_e('Please make a \'Manual Backup\' in the backup tab to enable backup restore in case anything goes wrong.', 'capsman-enhanced'); ?>
                                        <?php esc_html_e('Import the plugin settings from a .json file. This file can be obtained by exporting the settings on another site using the form above.', 'capsman-enhanced'); ?>
                                        <?php esc_html_e('Before importing, we recommend using the "Backup" tab to create a backup of your current settings.', 'capsman-enhanced'); ?></p>
                                        <p>
                                                <input type="file" name="import_file"/>
                                        </p>
                                        <p>
                                            <input type="submit" name="import_backup"
                                                    value="<?php esc_attr_e('Import', 'capsman-enhanced') ?>"
                                                    class="button-primary"/>
                                        </p>
                                    </div>
                                </div>

                            </td>
                        </tr>
                    </table>
                </fieldset>
            </div><!-- .pp-column-left -->
            <?php if (defined('CAPSMAN_PERMISSIONS_INSTALLED') && !CAPSMAN_PERMISSIONS_INSTALLED ) { ?>
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

    <script type="text/javascript">
        /* <![CDATA[ */
        jQuery(document).ready(function ($) {

            $('#cme_select_restore input[name="select_restore"]').on('change click', function () {
                $('div.cme-show-backup').hide();

                $('div.cme-selected-backup-caption').html($(this).next('label').html());
                $('div.cme-selected-backup-caption').css('margin-top','30px');

                var selected_val = $(this).val();

                $('td.cme-backup-info div').hide();

                switch (selected_val) {
                    case 'restore_initial':
                        $('#cme_display_capsman_backup_initial').addClass('current-display').show();
                        break;
                    case 'restore':
                        $('#cme_display_capsman_backup').addClass('current-display').show();
                        break;
                    default:
                        $('#cme_display_' + selected_val).addClass('current-display').show();
                }

                $('input.cme_backup_info_changes_only').click();
                $('div.cme_backup_info_changes_only').show();
            });

            $('input.cme_backup_info_changes_only').click(function() {
                $(this).attr('disabled', true);
                $('td.cme-backup-info div.current-display li:not(.cme-change)').toggle(!$(this).prop('checked'));
                $('span.pp-restore-caps-no-change').toggle($(this).prop('checked'));
                $(this).removeAttr('disabled');
            });

            $('#publishpress-capability-backup-tabs').find('li').click(function (e) {
                e.preventDefault();
                $('#publishpress-capability-backup-tabs').children('li').filter('.nav-tab-active').removeClass('nav-tab-active');
                $(this).addClass('nav-tab-active');

                $('div[id^="ppcb-"]').hide();
                $($(this).find('a').first().attr('href')).show();
            });

        });
        /* ]]> */
    </script>


	<?php if (!defined('PUBLISHPRESS_CAPS_PRO_VERSION') || get_option('cme_display_branding')) {
		cme_publishpressFooter();
	}
	?>
</div>
