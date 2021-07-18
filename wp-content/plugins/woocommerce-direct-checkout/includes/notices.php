<?php

class QLWCDC_Notices {

  protected static $_instance;

  public function __construct() {
    add_action('wp_ajax_qlwcdc_dismiss_notice', array($this, 'ajax_dismiss_notice'));
    add_action('admin_notices', array($this, 'add_notices'));
    add_filter('plugin_action_links_' . plugin_basename(QLWCDC_PLUGIN_FILE), array($this, 'add_action_links'));
  }

  public static function instance() {
    if (is_null(self::$_instance)) {
      self::$_instance = new self();
    }
    return self::$_instance;
  }
  
  public function ajax_dismiss_notice() {

    if (check_admin_referer('qlwcdc_dismiss_notice', 'nonce') && isset($_REQUEST['notice_id'])) {

      $notice_id = sanitize_key($_REQUEST['notice_id']);

      update_user_meta(get_current_user_id(), $notice_id, true);

      wp_send_json($notice_id);
    }

    wp_die();
  }

  public function add_notices() {

    if (!get_transient('qlwcdc-first-rating') && !get_user_meta(get_current_user_id(), 'qlwcdc-user-rating', true)) {
      ?>
      <div id="qlwcdc-admin-rating" class="qlwcdc-notice notice is-dismissible" data-notice_id="qlwcdc-user-rating">
        <div class="notice-container" style="padding-top: 10px; padding-bottom: 10px; display: flex; justify-content: left; align-items: center;">
          <div class="notice-image">
            <img style="border-radius:50%;max-width: 90px;" src="<?php echo plugins_url('/assets/backend/img/logo.jpg', QLWCDC_PLUGIN_FILE); ?>" alt="<?php echo esc_html(QLWCDC_PLUGIN_NAME); ?>>">
          </div>
          <div class="notice-content" style="margin-left: 15px;">
            <p>
              <?php printf(esc_html__('Hello! Thank you for choosing the %s plugin!', 'woocommerce-direct-checkout'), QLWCDC_PLUGIN_NAME); ?>
              <br/>
      <?php esc_html_e('Could you please give it a 5-star rating on WordPress? We know its a big favor, but we\'ve worked very much and very hard to release this great product. Your feedback will boost our motivation and help us promote and continue to improve this product.', 'woocommerce-direct-checkout'); ?>
            </p>
            <a href="<?php echo esc_url(QLWCDC_REVIEW_URL); ?>" class="button-primary" target="_blank">
      <?php esc_html_e('Yes, of course!', 'woocommerce-direct-checkout'); ?>
            </a>
            <a href="<?php echo esc_url(QLWCDC_SUPPORT_URL); ?>" class="button-secondary" target="_blank">
      <?php esc_html_e('Report a bug', 'woocommerce-direct-checkout'); ?>
            </a>
          </div>
        </div>
      </div>
      <script>
        (function ($) {
          $('.qlwcdc-notice').on('click', '.notice-dismiss', function (e) {
            e.preventDefault();
            var notice_id = $(e.delegateTarget).data('notice_id');
            $.ajax({
              type: 'POST',
              url: ajaxurl,
              data: {
                notice_id: notice_id,
                action: 'qlwcdc_dismiss_notice',
                nonce: '<?php echo wp_create_nonce('qlwcdc_dismiss_notice'); ?>'
              },
              success: function (response) {
                console.log(response);
              },
            });
          });
        })(jQuery);
      </script>
      <?php
    }
  }

  public function add_action_links($links) {

    $links[] = '<a target="_blank" href="' . QLWCDC_PURCHASE_URL . '">' . esc_html__('Premium', 'woocommerce-direct-checkout') . '</a>';
    $links[] = '<a href="' . admin_url('admin.php?page=wc-settings&tab=' . sanitize_title(QLWCDC_PREFIX)) . '">' . esc_html__('Settings', 'woocommerce-direct-checkout') . '</a>';

    return $links;
  }

}

QLWCDC_Notices::instance();
