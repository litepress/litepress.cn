<?php

class QLWCDC_Controller_Suggestions {

  protected static $_instance;

  public function __construct() {
    add_action('qlwcdc_sections_header', array($this, 'add_header'));
    add_action('admin_menu', array($this, 'add_menu'));
    add_action('admin_init', array($this, 'add_redirect'));
    add_action('admin_head', array($this, 'remove_menu'));
    add_filter('network_admin_url', array($this, 'network_admin_url'), 10, 2);
  }

  public static function instance() {
    if (is_null(self::$_instance)) {
      self::$_instance = new self();
    }
    return self::$_instance;
  }

  // Admin
  // -------------------------------------------------------------------------

  public function add_page() {
    include_once( QLWCDC_PLUGIN_DIR . 'includes/suggestions.php' );
    include_once( QLWCDC_PLUGIN_DIR . 'includes/view/backend/pages/suggestions.php' );
  }

  public function add_menu() {
    add_submenu_page(QLWCDC_PREFIX, esc_html__('Suggestions', 'woocommerce-direct-checkout'), esc_html__('Suggestions', 'woocommerce-direct-checkout'), 'manage_woocommerce', QLWCDC_PREFIX . '_suggestions', array($this, 'add_page'));
  }

  // fix for activateUrl on install now button
  public function network_admin_url($url, $path) {

    if (wp_doing_ajax() && !is_network_admin()) {
      if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'install-plugin') {
        if (strpos($url, 'plugins.php') !== false) {
          $url = self_admin_url($path);
        }
      }
    }

    return $url;
  }

  public function add_redirect() {

    if (isset($_REQUEST['activate']) && $_REQUEST['activate'] == 'true') {
      if (wp_get_referer() == admin_url('admin.php?page=' . QLWCDC_PREFIX . '_suggestions')) {
        wp_redirect(admin_url('admin.php?page=' . QLWCDC_PREFIX . '_suggestions'));
      }
    }
  }
  function add_header() {
    ?>
    <li><a href="<?php echo admin_url('admin.php?page=qlwcdc_suggestions'); ?>"><?php echo esc_html__('Suggestions', 'woocommerce-direct-checkout'); ?></a></li> | 
    <?php
  }


  public function remove_menu() {
    ?>
    <style>

      li.toplevel_page_<?php echo QLWCDC_PREFIX; ?> {
        display:none;
      }

    </style>
    <?php
  }

}

QLWCDC_Controller_Suggestions::instance();

