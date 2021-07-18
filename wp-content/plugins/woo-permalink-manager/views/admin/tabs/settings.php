<?php

if ( ! defined('WPINC')) {
    die;
}

?>

    <p>
        <?php esc_html_e('WooCommerce Permalink Manager offers you the ability to create a custom URL structure for your permalinks. Custom URL structures can improve the aesthetics, usability, and forward-compatibility of your links. A number of settings are available, and here are some examples to get you started.',
            'premmerce-url-manager'); ?>
    </p>
<?php $settings->show(); ?>