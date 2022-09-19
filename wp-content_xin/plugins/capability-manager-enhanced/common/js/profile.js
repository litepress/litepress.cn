jQuery(function ($) {
    // Check if the Role field is available. If not, abort.
    if (!$('.user-role-wrap select#role, #createuser select#role').length) {
        return;
    }

    var $field = $('.user-role-wrap select#role, #createuser select#role'),
        $newField = $field.clone();

    $newField.attr('name', 'pp_roles[]');
    $newField.attr('id', 'pp_roles');
    $field.after($newField);
    $field.hide();

    // Convert the roles field into multiselect
  $newField.prop('multiple', true);
  $newField.after('<p class="description">' + ppCapabilitiesProfileData.role_description + '</p>');

    // $newField.attr('name', 'role[]');

    // Select additional roles
    $newField.find('option').each(function (i, option) {
        $option = $(option);

        $.each(ppCapabilitiesProfileData.selected_roles, function (i, role) {
            if ($option.val() === role) {
                $option.prop('selected', true);
            }
        });
    });
  
  /**
   * loop role options and change selected role position
   */
   $.each(ppCapabilitiesProfileData.selected_roles.reverse(), function (i, role) {
     var options = $('#pp_roles option');
     var position = $("#pp_roles option[value='" + role + "']").index();
     $(options[position]).insertBefore(options.eq(0));
   });

    //add hidden option as first option to enable sorting selection
  $("#pp_roles").prepend('<option style="display:none;"></option>');
  
  //init chosen.js
    $newField.chosen({
        'width': '25em'
    });
  
  /**
   * Make role sortable
   */
  $(".user-role-wrap .chosen-choices, #createuser .chosen-choices").sortable();
  
  /**
   * Force role option re-order before profile form submission
   */
  $('form#your-profile, form#createuser').submit(function () {
    var options = $('#pp_roles option');
    $(".user-role-wrap .chosen-choices .search-choice .search-choice-close, #createuser .chosen-choices .search-choice .search-choice-close").each(function () {
      var select_position = $(this).attr('data-option-array-index');
      $(options[select_position]).insertBefore(options.eq(0));
    });
  });

  /**
   * Add class to chosen container on choice click
   */
	$(document).on( 'mousedown', '.user-role-wrap .chosen-choices .search-choice, #createuser .chosen-choices .search-choice', function() {
    $(this).closest('.chosen-container').addClass('chosen-choice-click');
   });
  
  /**
   * Remove chosen container class on click inside input
   */
  
	$(document).on( 'mousedown', '.user-role-wrap .chosen-choices, #createuser .chosen-choices', function(e) {
    if (!e.target.parentElement.classList.contains('search-choice')) {
      $(this).closest('.chosen-container').removeClass('chosen-choice-click');
    }
   });
    
});
