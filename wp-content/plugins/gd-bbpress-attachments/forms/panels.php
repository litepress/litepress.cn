<?php

$current = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'attachments';

$tabs = array(
    'attachments' => '<span class="dashicons dashicons-admin-settings" title="'.__("Settings", "gd-bbpress-attachments").'"></span><span class="tab-title"> '.__("Settings", "gd-bbpress-attachments").'</span>',
    'images' => '<span class="dashicons dashicons-images-alt" title="'.__("Images", "gd-bbpress-attachments").'"></span><span class="tab-title"> '.__("Images", "gd-bbpress-attachments").'</span>',
    'advanced' => '<span class="dashicons dashicons-admin-tools" title="'.__("Advanced", "gd-bbpress-attachments").'"></span><span class="tab-title"> '.__("Advanced", "gd-bbpress-attachments").'</span>',
    'd4p' => '<span class="dashicons dashicons-flag" title="'.__("Dev4Press", "gd-bbpress-attachments").'"></span><span class="tab-title"> '.__("Dev4Press", "gd-bbpress-attachments").'</span>',
    'about' => '<span class="dashicons dashicons-info" title="'.__("About", "gd-bbpress-attachments").'"></span><span class="tab-title"> '.__("About", "gd-bbpress-attachments").'</span>'
);

if (!isset($tabs[$current])) {
    $current = 'attachments';
}

?>
<div class="wrap">
    <h2>GD bbPress Attachments</h2>
    <div id="icon-upload" class="icon32"><br></div>
    <h2 class="nav-tab-wrapper d4p-tabber-ctrl">
        <?php

        foreach ($tabs as $tab => $name) {
            $class = ($tab == $current) ? ' nav-tab-active' : '';

            if ($tab == 'toolbox') {
                $class .= ' d4p-tab-toolbox';
            }

            echo '<a class="nav-tab'.$class.'" href="edit.php?post_type=forum&page=gdbbpress_attachments&tab='.$tab.'">'.$name.'</a>';
        }

        ?>
    </h2>
    <div id="d4p-panel" class="d4p-panel-<?php echo $current; ?>">
        <?php include(GDBBPRESSATTACHMENTS_PATH."forms/tabs/".$current.".php"); ?>
    </div>
</div>
