<?php

function qlwcdc_wcd_udpdate() {

  if ($active_plugins = get_option('active_plugins', array())) {

    global $wpdb;

    foreach ($active_plugins as $key => $active_plugin) {

      if (strstr($active_plugin, '/wc-direct-checkout.php')) {

        $active_plugins[$key] = str_replace('/wc-direct-checkout.php', '/woocommerce-direct-checkout.php', $active_plugin);

      }
    }

    update_option('active_plugins', $active_plugins);
  }
}

add_action('wp_loaded', 'qlwcdc_wcd_udpdate');
