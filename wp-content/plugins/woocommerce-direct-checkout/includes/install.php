<?php

class QLWCDC_Install {

  protected static $_instance;

  public function __construct() {
    register_activation_hook(QLWCDC_PLUGIN_FILE, array(__CLASS__, 'activation'));
    register_deactivation_hook(QLWCDC_PLUGIN_FILE, array(__CLASS__, 'deactivation'));
    self::import_old_settings();
  }

  public static function instance() {
    if (is_null(self::$_instance)) {
      self::$_instance = new self();
    }
    return self::$_instance;
  }

  public static function activation() {
    self::add_transient();
    self::add_settings();
  }

  public static function deactivation() {
    
  }

  private static function add_transient() {
    set_transient('qlwcdc-first-rating', true, MONTH_IN_SECONDS);
  }

  private static function add_settings() {
    if ('yes' === get_option('woocommerce_cart_redirect_after_add')) {
      add_option('qlwcdc_add_to_cart', 'redirect');
      add_option('qlwcdc_add_to_cart_redirect_page', 'cart');
    }
  }

  public static function import_old_settings() {

    global $wpdb;

    if (!get_option('qlwcdc_wcd_imported2')) {

      if (get_option('direct_checkout_pro_enabled', get_option('direct_checkout_enabled'))) {

        $url = get_option('direct_checkout_pro_cart_redirect_url', get_option('direct_checkout_cart_redirect_url'));

        if ($url === wc_get_cart_url()) {
          $val = 'cart';
        } elseif (filter_var($url, FILTER_VALIDATE_URL) !== false && $url != wc_get_checkout_url()) {
          $val = 'url';
        } else {
          $val = 'checkout';
        }

        /* add_option('qlwcdc_add_product_cart', 'redirect');
          add_option('qlwcdc_add_product_cart_redirect_page', $val);
          add_option('qlwcdc_add_product_cart_redirect_url', $url);

          add_option('qlwcdc_add_archive_cart', 'redirect');
          add_option('qlwcdc_add_archive_cart_redirect_page', $val);
          add_option('qlwcdc_add_archive_cart_redirect_url', $url); */

        add_option('qlwcdc_add_to_cart', 'redirect');
        add_option('qlwcdc_add_to_cart_redirect_page', $val);
        add_option('qlwcdc_add_to_cart_redirect_url', $url);
      }

      if ($text = get_option('direct_checkout_cart_button_text', get_option('direct_checkout_cart_button_text'))) {
        add_option('qlwcdc_add_product_text', 'yes');
        add_option('qlwcdc_add_product_text_content', $text);
        add_option('qlwcdc_add_archive_text', 'yes');
        add_option('qlwcdc_add_archive_text_content', $text);
        add_option('qlwcdc_add_archive_text_in', array(
            'simple',
            'grouped',
            'virtual',
            'variable',
            'downloadable'
        ));
      }

      if (count($keys = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->postmeta} WHERE meta_key = %s", '_direct_checkout_pro_enabled')))) {
        foreach ($keys as $key) {
          if ($key->meta_value == 'yes') {
            if ($text = get_post_meta($key->post_id, '_direct_checkout_pro_cart_button_text', true)) {
              add_post_meta($key->post_id, 'qlwcdc_add_product_text', 'yes', true);
              add_post_meta($key->post_id, 'qlwcdc_add_product_text_content', $text, true);
            }
          }
        }
      }

      delete_option('qlwcdc_wcd_imported');
      update_option('qlwcdc_wcd_imported2', true);
    }
  }

}

QLWCDC_Install::instance();
