<?php include_once('parts/header.php'); ?>
<div class="wrap about-wrap full-width-layout">
  <div class="has-2-columns is-wider-left" style="max-width: 100%">
    <div class="column">
      <div class="welcome-header">
        <h1><?php esc_html_e('Premium', 'woocommerce-direct-checkout'); ?></h1>
        <div class="about-description">
          <?php printf(esc_html__('%s allows you to simplifies the checkout process by skipping the shopping cart page. This plugin allows you to redirect your customers directly to the checkout page and includes the cart inside the checkout page.', 'woocommerce-direct-checkout'), QLWCDC_PLUGIN_NAME); ?>
        </div>
        <br />
        <a class="button button-primary" target="_blank" href="<?php echo esc_url(QLWCDC_PURCHASE_URL); ?>"><?php esc_html_e('Purchase Now', 'woocommerce-direct-checkout'); ?></a>
        <a class="button button-secondary" target="_blank" href="<?php echo esc_url(QLWCDC_SUPPORT_URL); ?>"><?php esc_html_e('Get Support', 'woocommerce-direct-checkout'); ?></a>
      </div>
      <hr />
      <div class="feature-section" style="padding: 10px 0;">
        <h3><?php esc_html_e('One page checkout', 'woocommerce-direct-checkout'); ?></h3>
        <p>
          <?php printf(esc_html__('%s allows you to include the cart form in the checkout page allowing your users to edit the cart and confirm the order on the same page.', 'woocommerce-direct-checkout'), QLWCDC_PLUGIN_NAME); ?>
        </p>
      </div>
      <div class="feature-section" style="padding: 10px 0;">
        <h3><?php esc_html_e('Remove checkout fields', 'woocommerce-direct-checkout'); ?></h3>
        <p>
          <?php esc_html_e('Our checkout settings allow you to easily remove the unnecessary fields and reduce the user spend completing those fields like the order comments, shipping address, coupon form, policy text, and terms and conditions.', 'woocommerce-direct-checkout'); ?>
        </p>
      </div>
      <div class="feature-section" style="padding: 10px 0;">
        <h3><?php esc_html_e('Remove checkout columns', 'woocommerce-direct-checkout'); ?></h3>
        <p>
          <?php esc_html_e('This option allows you to simplify the checkout page by removing the two columns in the checkout page.', 'woocommerce-direct-checkout'); ?>
        </p>
      </div>
    </div>
    <div class="column">
      <img src="<?php echo plugins_url('/assets/backend/img/checkout.png', QLWCDC_PLUGIN_FILE); ?>">
    </div>
  </div>
  <hr />
  <div class="has-2-columns is-wider-left" style="max-width: 100%">
    <div class="column">
      <div class="feature-section" style="padding: 10px 0;">
        <h3><?php esc_html_e('Checkout redirect', 'woocommerce-direct-checkout'); ?></h3>
        <p>
          <?php esc_html_e('This option allows you to redirect your users directly to the checkout page reducing the total checkout process in one step.', 'woocommerce-direct-checkout'); ?>
        </p>
      </div>
      <div class="feature-section" style="padding: 10px 0;">
        <h3><?php esc_html_e('Quick purchase on single products', 'woocommerce-direct-checkout'); ?></h3>
        <p>
          <?php esc_html_e('If you want to give the option to the user to make a direct purchase or the default add to cart product you can include a direct purchase button to the products page.', 'woocommerce-direct-checkout'); ?>
        </p>
      </div>
      <div class="feature-section" style="padding: 10px 0;">
        <h3><?php esc_html_e('Quick view in product archives', 'woocommerce-direct-checkout'); ?></h3>
        <p>
          <?php esc_html_e('This option allows you to include a button which displays a quick view in the WooCommerce shop page and products categories.', 'woocommerce-direct-checkout'); ?>
        </p>
        <p>
          <?php esc_html_e('This is especially useful for the variable products because it allows users to select the products attributes and include directly into the cart.', 'woocommerce-direct-checkout'); ?>
        </p>
      </div>
      <div class="feature-section" style="padding: 10px 0;">
        <hr />
        <h3><?php esc_html_e('Quick purchase', 'woocommerce-direct-checkout'); ?></h3>
        <p>
          <?php esc_html_e('The Quick purchase button allows you to include a direct button in the single products, variable, grouped and virtual products wich redirects user to the checkout page.', 'woocommerce-direct-checkout'); ?>
        </p>
      </div>
    </div>
    <div class="column">
      <br />
      <img src="<?php echo plugins_url('/assets/backend/img/modal.png', QLWCDC_PLUGIN_FILE); ?>">
    </div>
  </div>
</div>