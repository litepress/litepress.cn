<?php if (isset($_GET["settings-updated"]) && $_GET["settings-updated"] == "true") { ?>
    <div class="updated settings-error" id="setting-error-settings_updated">
        <p><strong><?php _e("Settings saved.", "gd-bbpress-attachments"); ?></strong></p>
    </div>
<?php } ?>

<form action="" method="post">
    <?php wp_nonce_field("gd-bbpress-attachments"); ?>
    <div class="d4p-settings">
        <fieldset>
            <h3><?php _e("Global Attachments Settings", "gd-bbpress-attachments"); ?></h3>
            <p><?php _e("These settings can be overridden for individual forums.", "gd-bbpress-attachments"); ?></p>
            <table class="form-table">
                <tbody>
                <tr valign="top">
                    <th scope="row">
                        <label for="max_file_size"><?php _e("Maximum file size", "gd-bbpress-attachments"); ?></label>
                    </th>
                    <td>
                        <input step="1" min="1" type="number" class="widefat small-text" value="<?php echo $options["max_file_size"]; ?>" id="max_file_size" name="max_file_size"/>
                        <span class="description">KB</span>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">
                        <label for="max_to_upload"><?php _e("Maximum files to upload", "gd-bbpress-attachments"); ?></label>
                    </th>
                    <td>
                        <input step="1" min="1" type="number" class="widefat small-text" value="<?php echo $options["max_to_upload"]; ?>" id="max_to_upload" name="max_to_upload"/>
                        <span class="description"><?php _e("at once", "gd-bbpress-attachments"); ?></span>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">
                        <label for="hide_from_visitors"><?php _e("Hide attachments", "gd-bbpress-attachments"); ?></label>
                    </th>
                    <td>
                        <input class="widefat" type="checkbox" <?php if ($options["hide_from_visitors"] == 1) {
                            echo " checked";
                        } ?> name="hide_from_visitors"/>
                        <?php _e("From visitors", "gd-bbpress-attachments"); ?>
                    </td>
                </tr>
                </tbody>
            </table>
        </fieldset>

        <fieldset>
            <h3><?php _e("Users Upload Restrictions", "gd-bbpress-attachments"); ?></h3>
            <p><?php _e("Only users having one of the selected roles will be able to attach files.", "gd-bbpress-attachments"); ?></p>
            <table class="form-table">
                <tbody>
                <tr valign="top">
                    <th scope="row"><?php _e("Allow upload to", "gd-bbpress-attachments") ?></th>
                    <td>
                        <fieldset>
                            <legend class="screen-reader-text">
                                <span><?php _e("Allow upload to", "gd-bbpress-attachments"); ?></span></legend>
                            <?php foreach ($_user_roles as $role => $title) { ?>
                                <label for="roles_to_upload_<?php echo $role; ?>">
                                    <input type="checkbox" <?php if (!isset($options["roles_to_upload"]) || is_null($options["roles_to_upload"]) || in_array($role, $options["roles_to_upload"])) {
                                        echo " checked";
                                    } ?> value="<?php echo $role; ?>" id="roles_to_upload_<?php echo $role; ?>" name="roles_to_upload[]"/>
                                    <?php echo $title; ?>
                                </label><br/>
                            <?php } ?>
                        </fieldset>
                    </td>
                </tr>
                </tbody>
            </table>
        </fieldset>

        <fieldset>
            <h3><?php _e("Topic and Reply Deleting", "gd-bbpress-attachments"); ?></h3>
            <p><?php _e("Select what to do with attachments when topic or reply with attachments is deleted.", "gd-bbpress-attachments"); ?></p>
            <table class="form-table">
                <tbody>
                <tr valign="top">
                    <th scope="row"><label><?php _e("Attachments Action", "gd-bbpress-attachments"); ?></label></th>
                    <td>
                        <select name="delete_attachments" class="widefat">
                            <option value="detach"<?php if ($options["delete_attachments"] == "detach") {
                                echo ' selected="selected"';
                            } ?>><?php _e("Leave in media library", "gd-bbpress-attachments"); ?></option>
                            <option value="delete"<?php if ($options["delete_attachments"] == "delete") {
                                echo ' selected="selected"';
                            } ?>><?php _e("Delete", "gd-bbpress-attachments"); ?></option>
                            <option value="nohing"<?php if ($options["delete_attachments"] == "nohing") {
                                echo ' selected="selected"';
                            } ?>><?php _e("Do nothing", "gd-bbpress-attachments"); ?></option>
                        </select>
                    </td>
                </tr>
                </tbody>
            </table>
        </fieldset>

        <fieldset>
            <h3><?php _e("JavaScript and CSS Settings", "gd-bbpress-attachments"); ?></h3>
            <p><?php _e("If you use shortcodes to embed forums, and you rely on plugin to add JS and CSS, you also need to enable this option to skip checking for bbPress specific pages.", "gd-bbpress-attachments"); ?></p>
            <p><?php _e("Plugin will attempt to load files automatically when needed. If that fails, try using this option.", "gd-bbpress-attachments"); ?></p>
            <table class="form-table">
                <tbody>
                <tr valign="top">
                    <th scope="row">
                        <label for="include_always"><?php _e("Always Include", "gd-bbpress-attachments"); ?></label>
                    </th>
                    <td>
                        <input type="checkbox" <?php if ($options["include_always"] == 1) {
                            echo " checked";
                        } ?> name="include_always"/>
                    </td>
                </tr>
                </tbody>
            </table>
        </fieldset>

        <fieldset>
            <h3><?php _e("Forums Integration", "gd-bbpress-attachments"); ?></h3>
            <p><?php _e("With these options you can modify the forums to include attachment elements.", "gd-bbpress-attachments"); ?></p>
            <table class="form-table">
                <tbody>
                <tr valign="top">
                    <th scope="row">
                        <label for="attachment_icon"><?php _e("Attachment Icon", "gd-bbpress-attachments"); ?></label>
                    </th>
                    <td>
                        <input type="checkbox" <?php if ($options["attachment_icon"] == 1) {
                            echo " checked";
                        } ?> name="attachment_icon"/>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">
                        <label for="attachment_icons"><?php _e("File Type Icons", "gd-bbpress-attachments"); ?></label>
                    </th>
                    <td>
                        <input type="checkbox" <?php if ($options["attachment_icons"] == 1) {
                            echo " checked";
                        } ?> name="attachment_icons"/>
                    </td>
                </tr>
                </tbody>
            </table>
        </fieldset>

        <p class="submit">
            <input type="submit" value="<?php _e("Save Changes", "gd-bbpress-attachments"); ?>" class="button-primary gdbb-tools-submit" id="gdbb-attach-submit" name="gdbb-attach-submit"/>
        </p>
    </div>
    <div class="d4p-settings-second">
        <?php include(GDBBPRESSATTACHMENTS_PATH.'forms/more/toolbox.php'); ?>
    </div>

    <div class="d4p-clear"></div>
</form>
