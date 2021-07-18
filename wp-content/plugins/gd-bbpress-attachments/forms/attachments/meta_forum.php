<input type="hidden" name="gdbbatt_forum_meta" value="edit"/>
<p>
    <strong class="label" style="width: 160px;"><?php _e("Disable Attachments", "gd-bbpress-attachments"); ?>:</strong>
    <label for="gdbbatt_disable" class="screen-reader-text"><?php _e("Disable Attachments", "gd-bbpress-attachments"); ?>:</label>
    <input type="checkbox" <?php if ($meta["disable"] == 1) {
        echo " checked";
    } ?> name="gdbbatt[disable]" id="gdbbatt_disable"/>
</p>
<p>
    <strong class="label" style="width: 160px;"><?php _e("Override Defaults", "gd-bbpress-attachments"); ?>:</strong>
    <label for="gdbbatt_to_override" class="screen-reader-text"><?php _e("Override Defaults", "gd-bbpress-attachments"); ?>:</label>
    <input type="checkbox" <?php if ($meta["to_override"] == 1) {
        echo " checked";
    } ?> name="gdbbatt[to_override]" id="gdbbatt_to_override"/>
</p>
<h4 style="font-size: 14px; margin: 3px 0 5px;"><?php _e("Settings to override", "gd-bbpress-attachments"); ?>:</h4>
<p>
    <strong class="label" style="width: 160px;"><?php _e("Maximum file size", "gd-bbpress-attachments"); ?>:</strong>
    <label for="gdbbatt_max_file_size" class="screen-reader-text"><?php _e("Maximum file size", "gd-bbpress-attachments"); ?>:</label>
    <br/><input step="1" min="1" type="number" class="widefat small-text" value="<?php echo $meta["max_file_size"]; ?>" name="gdbbatt[max_file_size]" id="gdbbatt_max_file_size"/>
    <span class="description">KB</span>
</p>
<p>
    <strong class="label" style="width: 160px;"><?php _e("Maximum files to upload", "gd-bbpress-attachments"); ?>:</strong>
    <label for="gdbbatt_max_to_upload" class="screen-reader-text"><?php _e("Maximum files to upload", "gd-bbpress-attachments"); ?>:</label>
    <br/><input step="1" min="1" type="number" class="widefat small-text" value="<?php echo $meta["max_to_upload"]; ?>" name="gdbbatt[max_to_upload]" id="gdbbatt_max_to_upload"/>
    <span class="description"><?php _e("at once", "gd-bbpress-attachments"); ?></span>
</p>
<p>
    <strong class="label" style="width: 160px;"><?php _e("Hide list of attachments", "gd-bbpress-attachments"); ?>:</strong>
    <label for="gdbbatt_hide_from_visitors" class="screen-reader-text"><?php _e("Hide From Visitors", "gd-bbpress-attachments"); ?>:</label>
    <br/><input style="vertical-align: top; margin-top: 3px;" type="checkbox" <?php if ($meta["hide_from_visitors"] == 1) {
        echo " checked";
    } ?> name="gdbbatt[hide_from_visitors]" id="gdbbatt_hide_from_visitors"/>
    <?php _e("From visitors", "gd-bbpress-attachments"); ?>
</p>
