// General
// -----------------------------------------------------------------

(function ($) {
  'use strict';

  $('select#qlwcdc_add_to_cart_redirect_page').on('change qlwcdc-load', (function () {
    if ('url' === $(this).val() && 'yes' == $('select#qlwcdc_add_to_cart_redirect').val()) {
      $('input#qlwcdc_add_to_cart_redirect_url').closest('tr, p').fadeIn();
    } else {
      $('input#qlwcdc_add_to_cart_redirect_url').closest('tr:not(.qlwcdc-premium-field), p').fadeOut();
    }
  }));
  $('select#qlwcdc_add_to_cart_redirect').on('change qlwcdc-load', (function () {
    if ('yes' === $(this).val()) {
      $('select#qlwcdc_add_to_cart_redirect_page').change();
      $('select#qlwcdc_add_to_cart_redirect_page').closest('tr, p').fadeIn();
    } else {
      $('select#qlwcdc_add_to_cart_redirect_page').change();
      $('select#qlwcdc_add_to_cart_redirect_page').closest('tr:not(.qlwcdc-premium-field), p').fadeOut();
    }
  }))
  $('select#qlwcdc_add_to_cart_redirect_page').on('change qlwcdc-load', (function () {
    if ('url' === $(this).val() && 'redirect' == $('select#qlwcdc_add_to_cart').val()) {
      $('input#qlwcdc_add_to_cart_redirect_url').closest('tr, p').fadeIn();
    } else {
      $('input#qlwcdc_add_to_cart_redirect_url').closest('tr:not(.qlwcdc-premium-field), p').fadeOut();
    }
  }));
  $('select#qlwcdc_add_to_cart').on('change qlwcdc-load', (function () {
    if ('ajax' === $(this).val()) {
      $('select#qlwcdc_add_to_cart_ajax_button').closest('tr, p').fadeIn();
      $('select#qlwcdc_add_to_cart_ajax_message').closest('tr, p').fadeIn();
    } else {
      $('select#qlwcdc_add_to_cart_ajax_button').closest('tr:not(.qlwcdc-premium-field), p').fadeOut();
      $('select#qlwcdc_add_to_cart_ajax_message').closest('tr:not(.qlwcdc-premium-field), p').fadeOut();
    }
    if ('redirect' === $(this).val()) {
      $('select#qlwcdc_add_to_cart_redirect_page').closest('tr, p').fadeIn();
    } else {
      $('select#qlwcdc_add_to_cart_redirect_page').closest('tr:not(.qlwcdc-premium-field), p').fadeOut();
    }
    $('select#qlwcdc_add_to_cart_redirect_page').change();
  }))

  $('select#qlwcdc_replace_cart_url').on('change qlwcdc-load', (function () {
    if ('custom' === $(this).val()) {
      $('input#qlwcdc_replace_cart_url_custom').closest('tr, p').fadeIn();
    } else {
      $('input#qlwcdc_replace_cart_url_custom').closest('tr:not(.qlwcdc-premium-field), p').fadeOut();
    }
  }));
  // Archive
  // -----------------------------------------------------------------
  $('select#qlwcdc_add_archive_text').on('change qlwcdc-load', (function () {
    if ('yes' === $(this).val()) {
      $('select#qlwcdc_add_archive_text_in').closest('tr, p').fadeIn();
      $('input#qlwcdc_add_archive_text_content').closest('tr, p').fadeIn();
    } else {
      $('select#qlwcdc_add_archive_text_in').closest('tr:not(.qlwcdc-premium-field), p').fadeOut();
      $('input#qlwcdc_add_archive_text_content').closest('tr:not(.qlwcdc-premium-field), p').fadeOut();
    }
  }))
  // Product
  // -----------------------------------------------------------------
  $('select#qlwcdc_add_product_ajax').on('change qlwcdc-load', (function () {
    if ('yes' === $(this).val()) {
      $('select#qlwcdc_add_product_ajax_alert').closest('tr, p').fadeIn();
    } else {
      $('select#qlwcdc_add_product_ajax_alert').closest('tr:not(.qlwcdc-premium-field), p').fadeOut();
    }
  }))
  $('select#qlwcdc_add_product_quick_purchase').on('change qlwcdc-load', (function () {
    if ('yes' === $(this).val()) {
      $('select#qlwcdc_add_product_quick_purchase_to').closest('tr, p').fadeIn();
      $('select#qlwcdc_add_product_quick_purchase_type').closest('tr, p').fadeIn();
      $('input#qlwcdc_add_product_quick_purchase_class').closest('tr, p').fadeIn();
      $('input#qlwcdc_add_product_quick_purchase_text').closest('tr, p').fadeIn();
    } else {
      $('select#qlwcdc_add_product_quick_purchase_to').closest('tr:not(.qlwcdc-premium-field), p').fadeOut();
      $('select#qlwcdc_add_product_quick_purchase_type').closest('tr:not(.qlwcdc-premium-field), p').fadeOut();
      $('input#qlwcdc_add_product_quick_purchase_class').closest('tr:not(.qlwcdc-premium-field), p').fadeOut();
      $('input#qlwcdc_add_product_quick_purchase_text').closest('tr:not(.qlwcdc-premium-field), p').fadeOut();
    }
  }))
  $('select#qlwcdc_add_product_text').on('change qlwcdc-load', (function () {
    if ('yes' === $(this).val()) {
      $('input#qlwcdc_add_product_text_content').closest('tr, p').fadeIn();
    } else {
      $('input#qlwcdc_add_product_text_content').closest('tr:not(.qlwcdc-premium-field), p').fadeOut();
    }
  }))
  // Checkout
  // -----------------------------------------------------------------
  $('select#qlwcdc_add_checkout_cart').on('change qlwcdc-load', (function () {
    if ('yes' === $(this).val()) {
      $('select#qlwcdc_add_checkout_cart_fields').closest('tr, p').fadeIn();
      $('input#qlwcdc_add_checkout_cart_class').closest('tr, p').fadeIn();
    } else {
      $('select#qlwcdc_add_checkout_cart_fields').closest('tr:not(.qlwcdc-premium-field), p').fadeOut();
      $('input#qlwcdc_add_checkout_cart_class').closest('tr:not(.qlwcdc-premium-field), p').fadeOut();
    }
  }))

  $(document).on('ready', function (e) {
    $('select#qlwcdc_add_to_cart_redirect_page').trigger('qlwcdc-load');
    $('select#qlwcdc_add_to_cart_redirect').trigger('qlwcdc-load');
    $('select#qlwcdc_add_to_cart_redirect_page').trigger('qlwcdc-load');
    $('select#qlwcdc_add_to_cart').trigger('qlwcdc-load');
    $('select#qlwcdc_replace_cart_url').trigger('qlwcdc-load');
    $('select#qlwcdc_add_archive_text').trigger('qlwcdc-load');
    $('select#qlwcdc_add_product_ajax').trigger('qlwcdc-load');
    $('select#qlwcdc_add_product_quick_purchase').trigger('qlwcdc-load');
    $('select#qlwcdc_add_product_text').trigger('qlwcdc-load');
    $('select#qlwcdc_add_checkout_cart').trigger('qlwcdc-load');
  });

}(jQuery));
