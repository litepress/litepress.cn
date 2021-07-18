<?php

class QLWCDC_Controller_General
{

  protected static $instance;

  public function __construct()
  {

    include_once(QLWCDC_PLUGIN_DIR . '/includes/view/frontend/general.php');

    add_action('qlwcdc_sections_header', array($this, 'add_header'));
    add_action('woocommerce_sections_' . QLWCDC_PREFIX, array($this, 'add_section'), 99);
    add_action('woocommerce_settings_save_' . QLWCDC_PREFIX, array($this, 'save_settings'));
  }

  public static function instance()
  {
    if (!isset(self::$instance)) {
      self::$instance = new self();
    }
    return self::$instance;
  }

  function add_header()
  {
    global $current_section;
?>
    <li><a href="<?php echo admin_url('admin.php?page=wc-settings&tab=' . QLWCDC_PREFIX . '&section'); ?>" class="<?php echo ($current_section == '' ? 'current' : ''); ?>"><?php esc_html_e('General', 'woocommerce-direct-checkout'); ?></a> | </li>
<?php
  }

  function get_settings()
  {

    return array(
      array(
        'name' => esc_html__('General', 'woocommerce-direct-checkout'),
        'type' => 'title',
        'desc' => esc_html__('Simplifies the checkout process.', 'woocommerce-direct-checkout'),
        'id' => 'qlwcdc_section_title'
      ),
      array(
        'name' => esc_html__('Added to cart alert', 'woocommerce-direct-checkout'),
        'desc_tip' => esc_html__('Replace "View Cart" alert with direct checkout.', 'woocommerce-direct-checkout'),
        'id' => 'qlwcdc_add_to_cart_message',
        'type' => 'select',
        'class' => 'chosen_select',
        'options' => array(
          'yes' => esc_html__('Yes', 'woocommerce-direct-checkout'),
          'no' => esc_html__('No', 'woocommerce-direct-checkout'),
        ),
        'default' => 'no',
      ),
      array(
        'name' => esc_html__('Added to cart link', 'woocommerce-direct-checkout'),
        'desc_tip' => esc_html__('Replace "View Cart" link with direct checkout.', 'woocommerce-direct-checkout'),
        'id' => 'qlwcdc_add_to_cart_link',
        'type' => 'select',
        'class' => 'chosen_select',
        'options' => array(
          'yes' => esc_html__('Yes', 'woocommerce-direct-checkout'),
          'no' => esc_html__('No', 'woocommerce-direct-checkout'),
        ),
        'default' => 'no',
      ),
      array(
        'name' => esc_html__('Added to cart redirect', 'woocommerce-direct-checkout'),
        'desc_tip' => esc_html__('Add to cart button behaviour.', 'woocommerce-direct-checkout'),
        'id' => 'qlwcdc_add_to_cart',
        'type' => 'select',
        'class' => 'chosen_select',
        'options' => array(
          'no' => esc_html__('No', 'woocommerce-direct-checkout'),
          //'ajax' => esc_html__('Ajax', 'woocommerce-direct-checkout'),
          'redirect' => esc_html__('Yes', 'woocommerce-direct-checkout'),
        ),
        'default' => 'no',
      ),
      array(
        'name' => esc_html__('Added to cart redirect to', 'woocommerce-direct-checkout'),
        'desc_tip' => esc_html__('Redirect to the cart or checkout page after successful addition.', 'woocommerce-direct-checkout'),
        'id' => 'qlwcdc_add_to_cart_redirect_page',
        'type' => 'select',
        'class' => 'chosen_select',
        'options' => array(
          'cart' => esc_html__('Cart', 'woocommerce-direct-checkout'),
          'checkout' => esc_html__('Checkout', 'woocommerce-direct-checkout'),
          'url' => esc_html__('Custom URL', 'woocommerce-direct-checkout'),
        ),
        'default' => 'cart',
      ),
      array(
        'name' => esc_html__('Added to cart redirect to custom url', 'woocommerce-direct-checkout'),
        'desc_tip' => esc_html__('Redirect to the cart or checkout page after successful addition.', 'woocommerce-direct-checkout'),
        'id' => 'qlwcdc_add_to_cart_redirect_url',
        'type' => 'text',
        'placeholder' => wc_get_checkout_url(),
      ),
      array(
        'name' => esc_html__('Replace cart url', 'woocommerce-direct-checkout'),
        'desc_tip' => esc_html__('Replace cart url', 'woocommerce-direct-checkout'),
        'id' => 'qlwcdc_replace_cart_url',
        'type' => 'select',
        'class' => 'chosen_select',
        'options' => array(
          'no' => esc_html__('No', 'woocommerce-direct-checkout'),
          'checkout' => esc_html__('Checkout', 'woocommerce-direct-checkout'),
          'custom' => esc_html__('Custom URL', 'woocommerce-direct-checkout'),
        ),
        'default' => 'no',
      ),
      array(
        'name' => esc_html__('Replace cart url with custom url', 'woocommerce-direct-checkout'),
        'desc_tip' => esc_html__('Replace cart url with custom url', 'woocommerce-direct-checkout'),
        'id' => 'qlwcdc_replace_cart_url_custom',
        'type' => 'text',
        'placeholder' => wc_get_checkout_url(),
      ),
      array(
        'type' => 'sectionend',
        'id' => 'qlwcdc_section_end'
      )
    );
  }

  function add_section()
  {

    global $current_section;

    if ('' == $current_section) {

      $settings = $this->get_settings();

      include_once(QLWCDC_PLUGIN_DIR . 'includes/view/backend/pages/general.php');
    }
  }

  function save_settings()
  {

    global $current_section;

    if ('' == $current_section) {

      woocommerce_update_options($this->get_settings());
    }
  }
}

QLWCDC_Controller_General::instance();
