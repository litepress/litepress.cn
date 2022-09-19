(function($) {

  var init = function() {
     var currentTab = $('.yith-bh-onboarding-tabs__nav li.selected').data('tab');
    $('#' + currentTab).fadeIn();
  };

  var block = function(element) {
    var blockArgs = {
      message: '',
      overlayCSS: {
        backgroundColor: '#FFFFFF',
        opacity: 0.8,
        cursor: 'wait',
      },
    };
    element.block(blockArgs);
  };

  var unblock = function(element) {
    element.unblock();
  };

  $(document).on('click', '.yith-bh-onboarding-tabs__nav li', function(e) {
    var $t = $(this);
    //nav
    $('.yith-bh-onboarding-tabs__nav li').removeClass('selected');
    $t.addClass('selected');
    //tab content
    $('.yith-bh-onboarding-tabs__tab').hide();
    $('#' + $t.data('tab')).fadeIn();
  });

  $(document).on('submit', 'form', function(e) {
    e.preventDefault();
    var form = $(this);

    if (true ===
        $(document).triggerHandler('yith_onboarding_form_submit_validation')) {
      $(document).trigger('yith_onboarding_validate_form_submit', [form]);
      return false;
    }
    block($('.yith-bh-onboarding-tabs__content'));
    $.ajax(
        {
          type: 'POST',
          data: form.serialize(),
          url: yith_bh_onboarding.ajax_url,
          success: function(response) {
            if (response.success) {
              var button = parent.document.querySelector(
                  '.components-modal__frame button');
              button.click();
            }
          },
          complete: function() {
            unblock($('.yith-bh-onboarding-tabs__content'));
          },
        },
    );

  });
  init();

})(jQuery);