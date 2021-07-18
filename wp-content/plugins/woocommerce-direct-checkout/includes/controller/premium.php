<?php

class QLWCDC_Controller_Premium {

  protected static $_instance;

  public function __construct() {
    add_action('qlwcdc_sections_header', array($this, 'add_header'));
    add_action('admin_menu', array($this, 'add_menu'));
  }

  public static function instance() {
    if (is_null(self::$_instance)) {
      self::$_instance = new self();
    }
    return self::$_instance;
  }

  function add_header() {
    ?>
    <li><a href="<?php echo admin_url('admin.php?page=' . QLWCDC_PREFIX); ?>"><?php echo esc_html__('Premium', 'woocommerce-direct-checkout'); ?></a></li> | 
    <?php
  }

  // Admin    
  // -------------------------------------------------------------------------

  public function add_page() {
    include_once( QLWCDC_PLUGIN_DIR . 'includes/view/backend/pages/premium.php' );
  }

  function add_menu() {
    add_menu_page(QLWCDC_PLUGIN_NAME, QLWCDC_PLUGIN_NAME, 'manage_woocommerce', QLWCDC_PREFIX, array($this, 'add_page'));
    add_submenu_page(QLWCDC_PREFIX, esc_html__('Premium', 'woocommerce-direct-checkout'), esc_html__('Premium', 'woocommerce-direct-checkout'), 'manage_woocommerce', QLWCDC_PREFIX, array($this, 'add_page'));
  }

}

QLWCDC_Controller_Premium::instance();
