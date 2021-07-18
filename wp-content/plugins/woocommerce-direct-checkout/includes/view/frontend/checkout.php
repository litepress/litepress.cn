<?php

class QLWCDC_Checkout
{

  protected static $instance;

  public function __construct()
  {
    add_filter('woocommerce_checkout_fields', array($this, 'remove_checkout_fields'));

    //add_filter('woocommerce_form_field_args', array($this, 'country_hidden_field_args'), 10, 4);

    //add_filter('woocommerce_form_field_country_hidden', array($this, 'country_hidden_field'), 10, 4);

    add_filter('woocommerce_countries_allowed_countries', array($this, 'remove_allowed_countries'));

    add_action('woocommerce_before_checkout_form', array($this, 'remove_country_css'));

    add_filter('woocommerce_enable_order_notes_field', array($this, 'remove_checkout_order_commens'));
    add_filter('option_woocommerce_ship_to_destination', array($this, 'remove_checkout_shipping_address'), 10, 3);

    if ('yes' === get_option('qlwcdc_remove_checkout_privacy_policy_text')) {
      remove_action('woocommerce_checkout_terms_and_conditions', 'wc_checkout_privacy_policy_text', 20);
    }

    if ('yes' === get_option('qlwcdc_remove_checkout_terms_and_conditions')) {
      add_filter('woocommerce_checkout_show_terms', '__return_false');
      remove_action('woocommerce_checkout_terms_and_conditions', 'wc_terms_and_conditions_page_content', 30);
    }
  }

  public static function instance()
  {
    if (!isset(self::$instance)) {
      self::$instance = new self();
    }
    return self::$instance;
  }

  function remove_checkout_fields($fields)
  {

    if ($remove = get_option('qlwcdc_remove_checkout_fields', array())) {

      foreach ($remove as $id => $key) {
        // We need to remove both fields otherwise will be required
        if ($key == 'country') {
          continue;
        }
        unset($fields['billing']['billing_' . $key]);
        unset($fields['shipping']['shipping_' . $key]);
      }
    }

    return $fields;
  }

  /* function country_hidden_field_args($args, $key, $value = null)
  {

    $remove = get_option('qlwcdc_remove_checkout_fields', array());

    if (in_array('country', (array) $remove)) {
      if ($key == 'billing_country' || $key == 'shipping') {
        $args['default'] = 'AR';
        $args['required'] = false;
        $args['type'] = 'country_hidden';
      }
    }

    return $args;
  }*/

  /*   function country_hidden_field($field = '', $key, $args, $value)
  {

    static $instance = 0;

    if ($instance) {
      return $field;
    }

    $instance++;

    $value = WC()->countries->get_base_country();
    //$countries = WC()->countries->get_allowed_countries();

    //if (count($countries = WC()->countries->get_allowed_countries())) {
    //  $value = key($countries);
    //} 

    $field .= '<input type="text" class="country_to_state" name="' . esc_attr($key) . '" id="' . esc_attr($args['id']) . '" value="' . esc_html($value) . '" ' . implode(' ', $args['custom_attributes']) . ' readonly="readonly" />';

    return $field;
  } */

  function remove_allowed_countries($countries)
  {

    $remove = get_option('qlwcdc_remove_checkout_fields', array());

    if (in_array('country', (array) $remove)) {

      $base = WC()->countries->get_base_country();

      if (isset($countries[$base])) {

        $countries = array(
          $base => $countries[$base]
        );
      }
    }

    return $countries;
  }

  public function remove_country_css()
  {

    $remove = get_option('qlwcdc_remove_checkout_fields', array());

    if (in_array('country', (array) $remove)) {
?>
      <style>
        #billing_country_field {
          display: none !important;
        }
      </style>
<?php
    }
  }

  function remove_checkout_order_commens($return)
  {

    if ('yes' === get_option('qlwcdc_remove_checkout_order_comments')) {
      $return = false;
    }

    return $return;
  }

  function remove_checkout_shipping_address($val)
  {

    if ('yes' === get_option('qlwcdc_remove_checkout_shipping_address')) {
      $val = 'billing_only';
    }

    return $val;
  }
}

QLWCDC_Checkout::instance();
