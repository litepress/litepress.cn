/**
 * Contains logic for deleting and adding roles.
 *
 * For deleting roles it makes a request to the server to delete the tag.
 * For adding roles it makes a request to the server to add the tag.
 *
 */

/* global ajaxurl, validateForm */

jQuery(document).ready(function ($) {
    $('input[name="role_name"]').keyup(function(k){
        // Indicate how default role slug will be generated from Role Name: PHP save handler will convert certain special characters to underscore, strip out others.
        var role_slug = $('input[name="role_name"]').val().toLowerCase().replace(/[ \(\)\&\#\@\+\,\-]/gi, "_").replace(/[^0-9a-zA-Z\_]/g, '');
        $('input[name="role_slug"]').attr('placeholder', role_slug);
    });

    $('input[name="role_slug"]').keypress(function (e) {
        // Don't allow forbidden characters to be entered. Note that dash is not normally allowed, but ban be allowed if constant is defined.

        //   underscore,                numeric,                         lowercase
        if (95 != e.which && (e.which < 48 || e.which > 57) && (e.which < 97 || e.which > 122)) {
            return false;
        }
    });

    /**
     * Adds an event handler to the delete role link on the role overview page.
     *
     * Cancels default event handling and event bubbling.
     *
     *
     * @returns boolean Always returns false to cancel the default event handling.
     */
    $('#the-list').on('click', '.delete-role', function () {

        if (confirm(pp_roles_i18n.confirm_delete)) {
            var t = $(this), tr = t.parents('tr'), r = true, data;

            data = t.attr('href').replace(/[^?]*\?/, '');

            /**
             * Makes a request to the server to delete the role that corresponds to the delete role button.
             *
             * @param {string} r The response from the server.
             *
             * @returns {void}
             */
            $.post(ajaxurl, data, function (r) {
                if (r) {
                    if (r.success === true) {
                        $('#ajax-response').empty();
                        tr.fadeOut('normal', function () {
                            tr.remove();
                        });
                    } else {
                        $('#ajax-response').empty().append(r.data);
                        tr.children().css('backgroundColor', '');
                    }
                }
            });

            tr.children().css('backgroundColor', '#f33');
        }

        return false;
    });

    /**
     * Adds an event handler to the form submit on the role overview page.
     *
     * Cancels default event handling and event bubbling.
     *
     *
     * @returns boolean Always returns false to cancel the default event handling.
     */
    $('#submit').click(function () {
        var form = $(this).parents('form');

        if (!validateForm(form))
            return false;

        /**
         * Does a request to the server to add a new role to the system
         *
         * @param {string} r The response from the server.
         *
         * @returns {void}
         */

        $.post(ajaxurl, $('#addrole').serialize(), function (r) {
            var res, parent, role, indent, i;

            $('#ajax-response').empty();
            res = typeof r !== 'undefined' ? r : null;
            if (res) {
                if (res.success === false) {
                    $('#ajax-response').append(res.data);

                } else if (res.success === true) {

                    $('.roles').prepend(res.data); // add to the table

                    $('.roles .no-items').remove();

                    $('input[type="text"]:visible, textarea:visible', form).val('');
                }
            }
        });

        return false;
    });

  
  /**
   * Capabilities role slug validation
   */
   $('.ppc-roles-tab-content input[name="role_slug"]').on('keyup', function (e) {
    is_role_slug_exist();
  });

  if ($('#pp-role-slug-exists').length > 0) {
    is_role_slug_exist();
  }

  /**
   * Role active tab
   */
  $(document).on('click', 'ul.ppc-roles-tab li', function () {
      $('.ppc-roles-active-tab').val($(this).attr('data-tab'));
  });
  
  /**
   * Role screen select input
   */
  if ($('.pp-capabilities-role-choosen').length > 0) {
    $('.pp-capabilities-role-choosen').chosen({
      'width': '25em'
    });
  }
  
  /**
   * Roles capabilities load less button
   */
   $(document).on('click', '.roles-capabilities-load-less', function (event) {
      event.preventDefault();

     $('.roles-capabilities-load-less').hide();
    
     $('.roles-capabilities-load-more').show();
   
     $('ul.pp-roles-capabilities li').hide();

     $('ul.pp-roles-capabilities').children().slice(0, 6).show();

     window.scrollTo({ top: 0, behavior: 'smooth' });
  });
 
  /**
   * Roles login redirect options
   */
   $(document).on('change', '.login-redirect-option #referer_redirect', function () {
     $('.login-redirect-option .custom-url-wrapper').hide();
     $('.login-redirect-option #custom_redirect').prop('checked', false);
  });
 
  /**
   * Roles login redirect options
   */
   $(document).on('change', '.login-redirect-option #custom_redirect', function (event) {
     if ($(this).prop('checked')) {
       $('.login-redirect-option .custom-url-wrapper').show();
     } else {
       $('.login-redirect-option .custom-url-wrapper').hide();
     }
     $('.login-redirect-option #referer_redirect').prop('checked', false);
  });
 
  /**
   * Roles allowed editor manage toggle
   */
   $(document).on('change', '.allowed-editor-toggle', function () {
     if ($(this).prop('checked')) {
       $('.role-editor-select-box').show();
     } else {
       $('.role-editor-select-box').hide();
       $('#role_editor-select option').prop('selected', false);
       $('#role_editor-select').trigger('chosen:updated');
     }
  });
  
   /**
   * Role submit required field validation
   */
  $('.pp-capability-roles-wrapper .submit-role-form').on('click', function (e) {

    let error_message = '';
    let error_report  = false;
    $('.role-submit-response').html('');

    //add required custom redirect link error message
    if ($('#custom_redirect').prop('checked') && isEmptyOrSpaces($('#login_redirect').val())) {
      error_report = true;
      error_message += '- ' + $('#login_redirect').attr('data-required_message') + '<br />';
    }

    //add custom url validation warning
    $('.pp-roles-internal-links-wrapper .base-input input').each(function () {
      var base_url = $(this).attr('data-base');
      if (!isEmptyOrSpaces(base_url) && base_url.includes('://')) {
        error_report = true;
        error_message += '- ' + $(this).attr('data-message') + '<br />';
      }
    });
    
    //add allowed editor option validation
    if ($('.allowed-editor-toggle').prop('checked') && $('#role_editor-select').val().length === 0) {
      error_report = true;
      error_message += '- ' + $('#role_editor-select').attr('data-message') + '<br />';
    }

    if (error_report) {
      e.preventDefault();
      $('.role-submit-response').html(error_message);
    }

  });
 
  /**
   * Role custom url change syc
   */
   $('.pp-roles-internal-links-wrapper .base-input input').on('keyup', function (e) {
    var current_input   = $(this);
    var current_wrapper = current_input.closest('.pp-roles-internal-links-wrapper');
    var current_entry   = current_input.val();
    
     current_wrapper.find('.base-input input')
       .attr('data-base', current_entry)
       .attr('data-entry', current_wrapper.find('.base-input input').attr('data-home_url') + current_entry);
  });
  /**
   * Prevent click on custom url base link
   */
   $('.pp-roles-internal-links-wrapper .base-url a').on('click', function (e) {
     e.preventDefault();
     return false;
   });

  function isEmptyOrSpaces(str) {
    return str === null || str.match(/^ *$/) !== null;
  }


  function is_role_slug_exist() {
    if ($('.ppc-roles-tab-content input[name="role_slug"]').attr('readonly') !== 'readonly') {
      var value = $('.ppc-roles-tab-content input[name="role_slug"]').val();
      var slugexists = $('#pp-role-slug-exists')
      var all_roles = $('.ppc-roles-all-roles').val();
      var role_array = all_roles.split(',');
      if (role_array.includes(value)) {
        slugexists.show();
      } else {
        slugexists.hide();
      }
    }
  }

});
