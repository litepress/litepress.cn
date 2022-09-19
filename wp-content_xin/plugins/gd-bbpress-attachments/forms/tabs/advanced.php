<?php if (isset($_GET["settings-updated"]) && $_GET["settings-updated"] == "true") { ?>
    <div class="updated settings-error" id="setting-error-settings_updated">
        <p><strong><?php _e("Settings saved.", "gd-bbpress-attachments"); ?></strong></p>
    </div>
<?php } ?>

<form action="" method="post">
    <?php wp_nonce_field("gd-bbpress-attachments"); ?>
    <div class="d4p-settings">
        <fieldset>
            <h3><?php _e("Error logging", "gd-bbpress-attachments"); ?></h3>
            <p><?php _e("Each failed upload will be logged in postmeta table. Administrators and topic/reply authors can see the log.", "gd-bbpress-attachments"); ?></p>
            <table class="form-table">
                <tbody>
                <tr valign="top">
                    <th scope="row">
                        <label for="log_upload_errors"><?php _e("Activated", "gd-bbpress-attachments"); ?></label></th>
                    <td>
                        <input type="checkbox" <?php if ($options["log_upload_errors"] == 1) {
                            echo " checked";
                        } ?> name="log_upload_errors"/>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">
                        <label for="errors_visible_to_admins"><?php _e("Visible to administrators", "gd-bbpress-attachments"); ?></label>
                    </th>
                    <td>
                        <input type="checkbox" <?php if ($options["errors_visible_to_admins"] == 1) {
                            echo " checked";
                        } ?> name="errors_visible_to_admins"/>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">
                        <label for="errors_visible_to_moderators"><?php _e("Visible to moderators", "gd-bbpress-attachments"); ?></label>
                    </th>
                    <td>
                        <input type="checkbox" <?php if ($options["errors_visible_to_moderators"] == 1) {
                            echo " checked";
                        } ?> name="errors_visible_to_moderators"/>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">
                        <label for="errors_visible_to_author"><?php _e("Visible to author", "gd-bbpress-attachments"); ?></label>
                    </th>
                    <td>
                        <input type="checkbox" <?php if ($options["errors_visible_to_author"] == 1) {
                            echo " checked";
                        } ?> name="errors_visible_to_author"/>
                    </td>
                </tr>
                </tbody>
            </table>
        </fieldset>

        <fieldset>
            <h3><?php _e("Deleting attachments", "gd-bbpress-attachments"); ?></h3>
            <p><?php _e("Once uploaded and attached, attachments can be deleted. Only administrators and authors can do this.", "gd-bbpress-attachments"); ?></p>
            <table class="form-table">
                <tbody>
                <tr valign="top">
                    <th scope="row"><label><?php _e("Administrators", "gd-bbpress-attachments"); ?></label></th>
                    <td>
                        <select name="delete_visible_to_admins" class="widefat">
                            <option value="no"<?php if ($options["delete_visible_to_admins"] == "no") {
                                echo ' selected="selected"';
                            } ?>><?php _e("Don't allow to delete", "gd-bbpress-attachments"); ?></option>
                            <option value="delete"<?php if ($options["delete_visible_to_admins"] == "delete") {
                                echo ' selected="selected"';
                            } ?>><?php _e("Delete from Media Library", "gd-bbpress-attachments"); ?></option>
                            <option value="detach"<?php if ($options["delete_visible_to_admins"] == "detach") {
                                echo ' selected="selected"';
                            } ?>><?php _e("Only detach from topic/reply", "gd-bbpress-attachments"); ?></option>
                            <option value="both"<?php if ($options["delete_visible_to_admins"] == "both") {
                                echo ' selected="selected"';
                            } ?>><?php _e("Allow both delete and detach", "gd-bbpress-attachments"); ?></option>
                        </select>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label><?php _e("Moderators", "gd-bbpress-attachments"); ?></label></th>
                    <td>
                        <select name="delete_visible_to_moderators" class="widefat">
                            <option value="no"<?php if ($options["delete_visible_to_moderators"] == "no") {
                                echo ' selected="selected"';
                            } ?>><?php _e("Don't allow to delete", "gd-bbpress-attachments"); ?></option>
                            <option value="delete"<?php if ($options["delete_visible_to_moderators"] == "delete") {
                                echo ' selected="selected"';
                            } ?>><?php _e("Delete from Media Library", "gd-bbpress-attachments"); ?></option>
                            <option value="detach"<?php if ($options["delete_visible_to_moderators"] == "detach") {
                                echo ' selected="selected"';
                            } ?>><?php _e("Only detach from topic/reply", "gd-bbpress-attachments"); ?></option>
                            <option value="both"<?php if ($options["delete_visible_to_moderators"] == "both") {
                                echo ' selected="selected"';
                            } ?>><?php _e("Allow both delete and detach", "gd-bbpress-attachments"); ?></option>
                        </select>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label><?php _e("Author", "gd-bbpress-attachments"); ?></label></th>
                    <td>
                        <select name="delete_visible_to_author" class="widefat">
                            <option value="no"<?php if ($options["delete_visible_to_author"] == "no") {
                                echo ' selected="selected"';
                            } ?>><?php _e("Don't allow to delete", "gd-bbpress-attachments"); ?></option>
                            <option value="delete"<?php if ($options["delete_visible_to_author"] == "delete") {
                                echo ' selected="selected"';
                            } ?>><?php _e("Delete from Media Library", "gd-bbpress-attachments"); ?></option>
                            <option value="detach"<?php if ($options["delete_visible_to_author"] == "detach") {
                                echo ' selected="selected"';
                            } ?>><?php _e("Only detach from topic/reply", "gd-bbpress-attachments"); ?></option>
                            <option value="both"<?php if ($options["delete_visible_to_author"] == "both") {
                                echo ' selected="selected"';
                            } ?>><?php _e("Allow both delete and detach", "gd-bbpress-attachments"); ?></option>
                        </select>
                    </td>
                </tr>
                </tbody>
            </table>
        </fieldset>

        <p class="submit">
            <input type="submit" value="<?php _e("Save Changes", "gd-bbpress-attachments"); ?>" class="button-primary gdbb-tools-submit" id="gdbb-att-advanced-submit" name="gdbb-att-advanced-submit"/>
        </p>
    </div>
    <div class="d4p-settings-second">
        <?php include(GDBBPRESSATTACHMENTS_PATH.'forms/more/toolbox.php'); ?>
    </div>

    <div class="d4p-clear"></div>
</form>
