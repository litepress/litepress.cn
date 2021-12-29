<div class="d4p-information">
    <fieldset>
        <h3>GD bbPress Attachments <?php echo $options["version"]; ?></h3>
        <?php

        $status = ucfirst($options["status"]);
        if ($options["revision"] > 0) {
            $status .= " #".$options["revision"];
        }

        _e("Release Date: ", "gd-bbpress-attachments");
        echo '<strong>'.$options["date"].'</strong><br/>';
        _e("Status: ", "gd-bbpress-attachments");
        echo '<strong>'.$status.'</strong><br/>';
        _e("Build: ", "gd-bbpress-attachments");
        echo '<strong>'.$options["build"].'</strong>';

        ?>
    </fieldset>

    <fieldset>
        <h3><?php _e("System Requirements", "gd-bbpress-attachments"); ?></h3>
        <?php

        _e("PHP: ", "gd-bbpress-attachments");
        echo '<strong>7.0 or newer</strong><br/>';
        _e("WordPress: ", "gd-bbpress-attachments");
        echo '<strong>5.1 or newer</strong><br/>';
        _e("bbPress: ", "gd-bbpress-attachments");
        echo '<strong>2.6.2 or newer</strong>';

        ?>
    </fieldset>

    <fieldset>
        <h3><?php _e("Important Plugin Links", "gd-bbpress-attachments"); ?></h3>
        <a target="_blank" href="https://plugins.dev4press.com/gd-bbpress-attachments/">GD bbPress Attachments <?php _e("Home Page", "gd-bbpress-attachments"); ?></a><br/>
        <a target="_blank" href="https://wordpress.org/plugins/gd-bbpress-attachments/">GD bbPress Attachments <?php _e("on", "gd-bbpress-attachments"); ?> WordPress.org</a>
        <h3><?php _e("Plugin Support", "gd-bbpress-attachments"); ?></h3>
        <a target="_blank" href="https://support.dev4press.com/forums/forum/plugins-free/gd-bbpress-attachments/"><?php _e("Plugin Support Forum on Dev4Press", "gd-bbpress-attachments"); ?></a><br/>
        <h3><?php _e("Dev4Press Important Links", "gd-bbpress-attachments"); ?></h3>
        <a target="_blank" href="https://twitter.com/milangd">Dev4Press <?php _e("on", "gd-bbpress-attachments"); ?> Twitter</a><br/>
        <a target="_blank" href="https://www.facebook.com/dev4press">Dev4Press Facebook <?php _e("Page", "gd-bbpress-attachments"); ?></a><br/>
    </fieldset>
</div>
<div class="d4p-information-second">
    <?php include(GDBBPRESSATTACHMENTS_PATH.'forms/more/toolbox.php'); ?>
</div>
<div class="d4p-clear"></div>
<div class="d4p-copyright">
    Dev4Press &copy; 2008 - 2021
    <a target="_blank" href="https://www.dev4press.com/">www.dev4press.com</a> | Golden Dragon WebStudio
    <a target="_blank" href="https://www.gdragon.info">www.gdragon.info</a>
</div>
