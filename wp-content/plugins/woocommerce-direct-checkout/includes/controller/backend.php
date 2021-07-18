<?php

class QLWCDC_Backend {

  protected static $_instance;

  public function __construct() {
    add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'), 99);
    add_filter('woocommerce_settings_tabs_array', array($this, 'add_tab'), 50);
    add_action('admin_menu', array(&$this, 'add_menu'));
    add_action('admin_footer', array($this, 'remove_premium'));
  }

  public static function instance() {
    if (is_null(self::$_instance)) {
      self::$_instance = new self();
    }
    return self::$_instance;
  }

  public function enqueue_scripts() {

    // 1326// filter by page stttings

    QLWCDC::instance()->register_scripts();

    wp_enqueue_script('qlwcdc-admin');
  }

  public function add_tab($settings_tabs) {
    $settings_tabs[QLWCDC_PREFIX] = esc_html__('Direct Checkout', 'woocommerce-direct-checkout');
    return $settings_tabs;
  }

  public function add_menu() {
    add_submenu_page('woocommerce', esc_html__('Direct Checkout', 'woocommerce-direct-checkout'), esc_html__('Direct Checkout', 'woocommerce-direct-checkout'), 'manage_woocommerce', admin_url('admin.php?page=wc-settings&tab=' . sanitize_title(QLWCDC_PREFIX)));
  }

  public function remove_premium() {
    ?>
    <script>
      (function ($) {
        'use strict';
        $(window).on('load', function (e) {
          $('#qlwcdc_options .options_group').css({'opacity': '0.5', 'pointer-events': 'none'});
          $('label[for=qlwcdc_add_product_ajax]').closest('tr').addClass('qlwcdc-premium-field').css({'opacity': '0.5', 'pointer-events': 'none'});
          $('label[for=qlwcdc_add_product_ajax_alert]').closest('tr').addClass('qlwcdc-premium-field').css({'opacity': '0.5', 'pointer-events': 'none'});
          $('label[for=qlwcdc_add_checkout_cart]').closest('tr').addClass('qlwcdc-premium-field').css({'opacity': '0.5', 'pointer-events': 'none'});
          $('label[for=qlwcdc_add_checkout_cart_fields]').closest('tr').addClass('qlwcdc-premium-field').css({'opacity': '0.5', 'pointer-events': 'none'});
          $('label[for=qlwcdc_add_checkout_cart_class]').closest('tr').addClass('qlwcdc-premium-field').css({'opacity': '0.5', 'pointer-events': 'none'});
          $('label[for=qlwcdc_remove_checkout_columns]').closest('tr').addClass('qlwcdc-premium-field').css({'opacity': '0.5', 'pointer-events': 'none'});
          $('label[for=qlwcdc_remove_checkout_coupon_form]').closest('tr').addClass('qlwcdc-premium-field').css({'opacity': '0.5', 'pointer-events': 'none'});
          $('label[for=qlwcdc_remove_order_details_address]').closest('tr').addClass('qlwcdc-premium-field').css({'opacity': '0.5', 'pointer-events': 'none'});
          $('label[for=qlwcdc_add_product_quick_purchase]').closest('tr').addClass('qlwcdc-premium-field').css({'opacity': '0.5', 'pointer-events': 'none'});
          $('label[for=qlwcdc_add_product_quick_purchase_to]').closest('tr').addClass('qlwcdc-premium-field').css({'opacity': '0.5', 'pointer-events': 'none'});
          $('label[for=qlwcdc_add_product_quick_purchase_qty]').closest('tr').addClass('qlwcdc-premium-field').css({'opacity': '0.5', 'pointer-events': 'none'});
          $('label[for=qlwcdc_add_product_quick_purchase_type]').closest('tr').addClass('qlwcdc-premium-field').css({'opacity': '0.5', 'pointer-events': 'none'});
          $('label[for=qlwcdc_add_product_quick_purchase_class]').closest('tr').addClass('qlwcdc-premium-field').css({'opacity': '0.5', 'pointer-events': 'none'});
          $('label[for=qlwcdc_add_product_quick_purchase_text]').closest('tr').addClass('qlwcdc-premium-field').css({'opacity': '0.5', 'pointer-events': 'none'});
          $('label[for=qlwcdc_add_product_default_attributes]').closest('tr').addClass('qlwcdc-premium-field').css({'opacity': '0.5', 'pointer-events': 'none'});
          $('label[for=qlwcdc_add_archive_quick_view]').closest('tr').addClass('qlwcdc-premium-field').css({'opacity': '0.5', 'pointer-events': 'none'});
        });
      }(jQuery));
    </script>
    <?php

  }

}

QLWCDC_Backend::instance();
