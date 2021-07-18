<?php

class QLWCDC_Controller_Archives {

  protected static $instance;

  public function __construct() {
    include_once(QLWCDC_PLUGIN_DIR . '/includes/view/frontend/archives.php');
    add_action('qlwcdc_sections_header', array($this, 'add_header'));
    add_action('woocommerce_sections_' . QLWCDC_PREFIX, array($this, 'add_section'), 99);
    add_action('woocommerce_settings_save_' . QLWCDC_PREFIX, array($this, 'save_settings'));
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

  function get_settings() {

    return array(
        array(
            'name' => esc_html__('Archives', 'woocommerce-direct-checkout'),
            'type' => 'title',
            'id' => 'qlwcdc_archives_section_title'
        ),
        array(
            'name' => esc_html__('Replace Add to cart text', 'woocommerce-direct-checkout'),
            'desc_tip' => esc_html__('Replace Add to cart text', 'woocommerce-direct-checkout'),
            'id' => 'qlwcdc_add_archive_text',
            'type' => 'select',
            'class' => 'chosen_select',
            'options' => array(
                'yes' => esc_html__('Yes', 'woocommerce-direct-checkout'),
                'no' => esc_html__('No', 'woocommerce-direct-checkout'),
            ),
            'default' => 'no',
        ),
        array(
            'name' => esc_html__('Replace Add to cart text in', 'woocommerce-direct-checkout'),
            'desc_tip' => esc_html__('Replace "Add to cart" text in product types.', 'woocommerce-direct-checkout'),
            'id' => 'qlwcdc_add_archive_text_in',
            'type' => 'multiselect',
            'class' => 'chosen_select',
            'options' => array(
                'simple' => esc_html__('Simple Products', 'woocommerce-direct-checkout'),
                'grouped' => esc_html__('Grouped Products', 'woocommerce-direct-checkout'),
                'virtual' => esc_html__('Virtual Products', 'woocommerce-direct-checkout'),
                'variable' => esc_html__('Variable Products', 'woocommerce-direct-checkout'),
                'downloadable' => esc_html__('Downloadable Products', 'woocommerce-direct-checkout'),
            ),
            'default' => array('simple'),
        ),
        array(
            'name' => esc_html__('Replace Add to cart text content', 'woocommerce-direct-checkout'),
            'desc_tip' => esc_html__('Replace "Add to cart" text with this text.', 'woocommerce-direct-checkout'),
            'id' => 'qlwcdc_add_archive_text_content',
            'type' => 'text',
            'default' => esc_html__('Purchase', 'woocommerce-direct-checkout'),
        ),
        array(
            'name' => esc_html__('Add quick view button', 'woocommerce-direct-checkout'),
            'desc_tip' => esc_html__('Add product quick view modal button.', 'woocommerce-direct-checkout'),
            'id' => 'qlwcdc_add_archive_quick_view',
            'type' => 'select',
            'class' => 'chosen_select qlwcdc-premium-field',
            'options' => array(
                'yes' => esc_html__('Yes', 'woocommerce-direct-checkout'),
                'no' => esc_html__('No', 'woocommerce-direct-checkout'),
            ),
            'default' => 'no',
        ),
        array(
            'type' => 'sectionend',
            'id' => 'qlwcdc_archives_section_end'
        )
    );
  }

  function add_section() {

    global $current_section;

    if ('archives' == $current_section) {

      $settings = $this->get_settings();

      include_once( QLWCDC_PLUGIN_DIR . 'includes/view/backend/pages/archives.php' );
    }
  }

  function save_settings() {

    global $current_section;

    if ('archives' == $current_section) {

      woocommerce_update_options($this->get_settings());
    }
  }

}

QLWCDC_Controller_Archives::instance();
