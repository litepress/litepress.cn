<?php

class QLWCDC_Archives {

  protected static $instance;

  public function __construct() {
    add_filter('woocommerce_product_add_to_cart_text', array($this, 'add_archive_text'), 10, 2);
  }

  public static function instance() {
    if (!isset(self::$instance)) {
      self::$instance = new self();
    }
    return self::$instance;
  }

  function add_header() {
    global $current_section;
    ?>
    <li><a href="<?php echo admin_url('admin.php?page=wc-settings&tab=' . QLWCDC_PREFIX . '&section=archives'); ?>" class="<?php echo ( $current_section == 'archives' ? 'current' : '' ); ?>"><?php esc_html_e('Archives', 'woocommerce-direct-checkout'); ?></a> | </li>
    <?php
  }

  function add_archive_text($text, $product) {

    if ('yes' === get_option('qlwcdc_add_archive_text')) {
      if ($product->is_type(get_option('qlwcdc_add_archive_text_in', array()))) {
        $text = esc_html__(get_option('qlwcdc_add_archive_text_content'));
      }
    }

    return $text;
  }

}

QLWCDC_Archives::instance();
