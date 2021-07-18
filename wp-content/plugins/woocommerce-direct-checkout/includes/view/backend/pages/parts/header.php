<div class="wrap about-wrap full-width-layout">

  <h1><?php esc_html_e('Suggestions', 'woocommerce-direct-checkout'); ?></h1>

  <p class="about-text"><?php printf(esc_html__('Thanks for using our product! We recommend these extensions that will add new features to stand out your business and improve your sales.', 'woocommerce-direct-checkout'), QLWCDC_PLUGIN_NAME); ?></p>

  <p class="about-text">
    <?php printf('<a href="%s" target="_blank">%s</a>', QLWCDC_PURCHASE_URL, esc_html__('Purchase', 'woocommerce-direct-checkout')); ?></a> |  
    <?php printf('<a href="%s" target="_blank">%s</a>', QLWCDC_DOCUMENTATION_URL, esc_html__('Documentation', 'woocommerce-direct-checkout')); ?></a>
  </p>

  <?php printf('<a href="%s" target="_blank"><div style="
               background: #006bff url(%s) no-repeat;
               background-position: top center;
               background-size: 130px 130px;
               color: #fff;
               font-size: 14px;
               text-align: center;
               font-weight: 600;
               margin: 5px 0 0;
               padding-top: 120px;
               height: 40px;
               display: inline-block;
               width: 140px;
               " class="wp-badge">%s</div></a>', 'https://quadlayers.com/?utm_source=qlwcdc_admin', plugins_url('/assets/backend/img/quadlayers.jpg', QLWCDC_PLUGIN_FILE), esc_html__('QuadLayers', 'woocommerce-direct-checkout')); ?>

</div>

<?php
if (isset($GLOBALS['submenu'][QLWCDC_PREFIX])) {
  if (is_array($GLOBALS['submenu'][QLWCDC_PREFIX])) {
    ?>
    <div class="wrap about-wrap full-width-layout qlwrap">
      <h2 class="nav-tab-wrapper">
        <?php
        foreach ($GLOBALS['submenu'][QLWCDC_PREFIX] as $tab) {
          if (strpos($tab[2], '.php') !== false)
            continue;
          ?>
          <a href="<?php echo admin_url('admin.php?page=' . esc_attr($tab[2])); ?>" class="nav-tab<?php echo (isset($_GET['page']) && $_GET['page'] == $tab[2]) ? ' nav-tab-active' : ''; ?>"><?php echo $tab[0]; ?></a>
          <?php
        }
        ?>
      </h2>
    </div>
    <?php
  }
}