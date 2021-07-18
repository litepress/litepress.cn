<?php

class QLWCDC
{

  protected static $instance;

  public function __construct()
  {

    include_once(QLWCDC_PLUGIN_DIR . '/includes/install.php');
    include_once(QLWCDC_PLUGIN_DIR . '/includes/notices.php');

    add_action('plugins_loaded', array($this, 'includes'));

    load_plugin_textdomain('woocommerce-direct-checkout', false, QLWCDC_PLUGIN_DIR . '/languages/');
  }

  public static function instance()
  {
    if (!isset(self::$instance)) {
      self::$instance = new self();
    }
    return self::$instance;
  }

  public function includes()
  {
    include_once(QLWCDC_PLUGIN_DIR . 'includes/controller/backend.php');
    include_once(QLWCDC_PLUGIN_DIR . 'includes/controller/general.php');
    include_once(QLWCDC_PLUGIN_DIR . 'includes/controller/archives.php');
    include_once(QLWCDC_PLUGIN_DIR . 'includes/controller/products.php');
    include_once(QLWCDC_PLUGIN_DIR . 'includes/controller/checkout.php');
    include_once(QLWCDC_PLUGIN_DIR . 'includes/controller/premium.php');
    include_once(QLWCDC_PLUGIN_DIR . 'includes/controller/suggestions.php');
  }

  public function register_scripts()
  {
    wp_register_script('qlwcdc-admin', plugins_url('/assets/backend/qlwcdc-admin' . QLWCDC::instance()->is_min() . '.js', QLWCDC_PLUGIN_FILE), array('jquery'), QLWCDC_PLUGIN_VERSION, true);
  }

  public function is_min()
  {
    if (!defined('SCRIPT_DEBUG') || !SCRIPT_DEBUG) {
      return '.min';
    }
  }

  public function get_product_option($product_id = null, $meta_key = null, $default = null)
  {

    if (!$meta_key) {
      return null;
    }

    if ($product_id && metadata_exists('post', $product_id, $meta_key)) {

      if ($value = get_post_meta($product_id, $meta_key, true)) {
        return $value;
      }
    }

    return get_option($meta_key, $default);
  }
}

QLWCDC::instance();
