<?php

class QLWCDC_Products {

  protected static $instance;

  public function __construct() {
    add_filter('woocommerce_product_single_add_to_cart_text', array($this, 'add_product_text'), 10, 2);
    // WooCommerce Product Addon Compatibility
    add_filter('woocommerce_add_to_cart_validation', array($this, 'validate_add_cart_item'), -10, 4);
  }

  public static function instance() {
    if (!isset(self::$instance)) {
      self::$instance = new self();
    }
    return self::$instance;
  }

  function add_product_text($text, $product) {

    if ('yes' === QLWCDC::instance()->get_product_option($product->get_id(), 'qlwcdc_add_product_text')) {
      $text = esc_html__(QLWCDC::instance()->get_product_option($product->get_id(), 'qlwcdc_add_product_text_content'), $text);
    }

    return $text;
  }

  function validate_add_cart_item($passed, $product_id, $qty, $post_data = null) {

    if (class_exists('WC_Product_Addons_Helper')) {

      if (isset($_GET['add-to-cart']) && absint($_GET['add-to-cart']) > 0) {

        $product_addons = WC_Product_Addons_Helper::get_product_addons($product_id);

        if (is_array($product_addons) && !empty($product_addons)) {

          foreach ($product_addons as $addon) {

            if (isset($_GET['addon-' . $addon['field_name']])) {
              $_POST['addon-' . $addon['field_name']] = sanitize_text_field($_GET['addon-' . $addon['field_name']]);
            }
          }
        }
      }
    }

    return $passed;
  }

}

QLWCDC_Products::instance();
