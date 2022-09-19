jQuery(document).ready( function($) {
	$('a.neg-cap').attr('title',cmeAdmin.negationCaption);
	$('a.neg-type-caps').attr('title',cmeAdmin.typeCapsNegationCaption);
	$('td.cap-unreg').attr('title',cmeAdmin.typeCapUnregistered);
	$('a.normal-cap').attr('title',cmeAdmin.switchableCaption);
	$('span.cap-x').attr('title',cmeAdmin.capNegated);
	$('table.cme-checklist input[class!="cme-check-all"]').not(':disabled').attr('title',cmeAdmin.chkCaption);

	$('table.cme-checklist a.neg-cap').click( function(e) {
		$(this).closest('td').removeClass('cap-yes').removeClass('cap-no').addClass('cap-neg');

		var cap_name_attr = $(this).parent().find('input[type="checkbox"]').attr('name');
		$(this).after('<input type="hidden" class="cme-negation-input" name="'+cap_name_attr+'" value="" />');

		$('input[name="' + cap_name_attr + '"]').closest('td').removeClass('cap-yes').removeClass('cap-no').addClass('cap-neg');
    
    if ($(this).closest('tr').hasClass('unfiltered_upload')) { 
      $('input[name="caps[upload_files]"]').closest('td').addClass('cap-neg');
      $('input[name="caps[upload_files]"]').closest('td').append('<input type="hidden" class="cme-negation-input" name="caps[upload_files]" value="" />');
      $('input[name="caps[upload_files]"]').parent().next('a.neg-cap:visible').click();
    }

		return false;
	});

	//$('table.cme-typecaps span.cap-x,table.cme-checklist span.cap-x,table.cme-checklist td.cap-neg span').live( 'click', function(e) {
	$(document).on( 'click', 'table.cme-typecaps span.cap-x,table.cme-checklist span.cap-x,table.cme-checklist td.cap-neg span', function(e) {
		$(this).closest('td').removeClass('cap-neg').removeClass('cap-yes').addClass('cap-no');
		$(this).parent().find('input[type="checkbox"]').prop('checked',false);
		$(this).parent().find('input.cme-negation-input').remove();

		// Also apply for any other checkboxes with the same name
		var cap_name_attr = $(this).next('input[type="checkbox"]').attr('name');

		if (!cap_name_attr) {
			cap_name_attr = $(this).next('label').find('input[type="checkbox"]').attr('name');
		}

		$('input[name="' + cap_name_attr + '"]').parent().closest('td').removeClass('cap-neg').removeClass('cap-yes').addClass('cap-no');
		$('input[name="' + cap_name_attr + '"]').prop('checked',false).parent().find('input.cme-negation-input').remove();

    if ($(this).closest('td').hasClass('capability-checkbox-rotate')) {
      $(this).closest('td').find('input[type="checkbox"]').prop('checked', true);

      if ($(this).closest('td').hasClass('upload_files')) {
        $('tr.unfiltered_upload').find('td').removeClass('cap-neg').removeClass('cap-yes').addClass('cap-no');
		    $('tr.unfiltered_upload').find('input[type="checkbox"]').prop('checked',false);
        $('tr.unfiltered_upload').find('input.cme-negation-input').remove();
        $('input[name="caps[unfiltered_upload]"]').parent().closest('td').removeClass('cap-neg').removeClass('cap-yes').addClass('cap-no');
		    $('input[name="caps[unfiltered_upload]"]').prop('checked', true).parent().find('input.cme-negation-input').remove();
      }
    } 

    if ($(this).closest('td').find('input[type="checkbox"]').hasClass('pp-single-action-rotate')) {
      $(this).closest('td').find('input[type="checkbox"]').prop('checked', true);
    }

    if ($(this).closest('tr').hasClass('unfiltered_upload')) {
      $('input[name="caps[upload_files]"]').parent().closest('td').removeClass('cap-neg').removeClass('cap-yes').addClass('cap-no');
      $('input[name="caps[upload_files]"]').prop('checked', true).parent().find('input.cme-negation-input').remove();
    }
		return false;
	});

	$("#publishpress_caps_form").bind("keypress", function(e) {
		if (e.keyCode == 13) {
		   $(document.activeElement).parent().find('input[type="submit"]').first().click();
		   return false;
		}
	});

	$('input.cme-check-all').click( function(e) {
		$(this).closest('table').find('input[type="checkbox"][disabled!="disabled"]:visible').prop('checked', $(this).is(":checked") );
	});

	$('a.cme-neg-all').click( function(e) {
		$(this).closest('table').find('a.neg-cap:visible').click();
		return false;
	});

	$('a.cme-switch-all').click( function(e) {
		$(this).closest('table').find('td.cap-neg span').click();
		return false;
	});

	$('table.cme-typecaps a.neg-type-caps').click( function(e) {
		$(this).closest('tr').find('td[class!="cap-neg"]').filter('td[class!="cap-unreg"]').each( function() {
			$(this).addClass('cap-neg');

			var cap_name_attr = $(this).find('input[type="checkbox"]').attr('name');
			$(this).append('<input type="hidden" class="cme-negation-input" name="'+cap_name_attr+'" value="" />');

			$('input[name="' + cap_name_attr + '"]').parent().next('a.neg-cap:visible').click();
		});

		return false;
	});

	//http://stackoverflow.com/users/803925/nbrooks
	$('table.cme-typecaps th').click(function(){
		var columnNo = $(this).index();

		var check_val = ! $(this).prop('checked_all');

		if ( $(this).hasClass('term-cap') )
			var class_sel = '[class*="term-cap"]';
		else
			var class_sel = '[class*="post-cap"]';

		var chks = $(this).closest("table")
			.find("tr td" + class_sel + ":nth-child(" + (columnNo+1) + ') input[type="checkbox"]:visible');

		$(chks).each(function(i,e) {
			$('input[name="' + $(this).attr('name') + '"]').prop('checked', check_val);
		});

		$(this).prop('checked_all',check_val);
	});

	$('a.cme-fix-read-cap').click(function(){
		$('input[name="caps[read]"]').prop('checked', true);
		$('input[name="SaveRole"]').trigger('click');
		return false;
	});

	/* Filter Edit, Delete and Read capabilities */

	// Fill the <select> extracting the values and labels from the tables
	$('.ppc-filter-select').each(function(){
	    var filter = $(this)
	    var options = new Array();
	    $(this).parent().siblings('table').find('tbody').find('tr').each(function(){
	        options.push({
	            value : $(this).attr('class'),
	            text : $(this).find('.cap_type').text()
	        });
	    });
	    options.forEach(function(option, index){
	        filter.append($('<option>', {
	            value: option.value,
	            text: option.text
	        }));
	    });
	});

	// Reset select filters on load
	$('.ppc-filter-select').prop('selectedIndex', 0);

	$('.ppc-filter-select-reset').click(function(){
	    $(this).prev('.ppc-filter-select').prop('selectedIndex', 0);
	    $(this).parent().siblings('table').find('tr').show(); // Show all the table rows
	});
	$('.ppc-filter-select').change(function(){
        if($(this).val()){
			$(this).parent().siblings('table').find('tr').hide();
    	    $(this).parent().siblings('table').find('thead tr:first-child').show(); // Show the table heading
    	    $(this).parent().siblings('table').find('tr.' + $(this).val()).show(); // Show only the filtered row
        } else {
            $(this).parent().siblings('table').find('tr').show(); // No value selected; show all the table rows
        }
	});

	/* Filter WordPress core, WooCommerce, Additional capabilities */

	// Reset text filters on load
	$('.ppc-filter-text').val('');

	$('.ppc-filter-text-reset').click(function(){
	    $(this).prev('.ppc-filter-text').val('');
	    $(this).parent().siblings('table').find('tr').show(); // Show all the table rows
		$(this).parent().siblings('.ppc-filter-no-results').hide(); // Hide "no results" message
	});

	$('.ppc-filter-text').keyup(function(){
      	var search_text = $(this).val();
      	var search_class = search_text.trim().replace(/\s+/g, '_');
	    $(this).parent().siblings('table').find('tr').hide();
	    $(this).parent().siblings('table').find('tr[class*="' + search_class + '"]').show(); // Show only the filtered row
	    $(this).parent().siblings('table').find('tr.cme-bulk-select').hide(); // Hide bulk row
	    if($(this).val().length === 0){
	        $(this).parent().siblings('table').find('tr').show(); // Show all the table rows
	    }
	    // Show / Hide the no-results message
	    if($(this).parent().siblings('table').find('tr:visible').length === 0) {
	        $(this).parent().siblings('.ppc-filter-no-results').show(); // Show "no results" message
	    } else {
	        $(this).parent().siblings('.ppc-filter-no-results').hide(); // Hide "no results" message
	    }
  });

  /**
     * Roles tab toggle
     */
   $(document).on('click', '.ppc-roles-tab li', function (event) {
      event.preventDefault();

      var clicked_tab = $(this).attr('data-tab');

      //remove active class from all tabs
      $('.ppc-roles-tab li').removeClass('active');
      //add active class to current tab
      $(this).addClass('active');

      //hide all tabs contents
      $('.pp-roles-tab-tr').hide();
      //show this current tab contents
     $('.pp-roles-' + clicked_tab + '-tab').show();
   });
  
   /**
    * Roles capabilities load more button
    */
    $(document).on('click', '.roles-capabilities-load-more', function (event) {
       event.preventDefault();
 
      $('.roles-capabilities-load-more').hide();
     
      $('.roles-capabilities-load-less').show();
    
      $('ul.pp-roles-capabilities li').show();
   });
  
   /**
    * Capabilities single box click
    */
    $(document).on('change', '.capability-checkbox-rotate input[type="checkbox"]', function (event) {
     
      let clicked_box           = $(this);
      let mark_box_as_x         = false;
      let mark_box_as_checked   = false;
      let mark_box_as_unchecked = false;

      if (!clicked_box.prop('checked')) {
        mark_box_as_unchecked   = true;
      } else if (clicked_box.prop('checked')) {
        mark_box_as_checked   = true;
      }

      if (mark_box_as_checked && clicked_box.hasClass('interacted')) {
        mark_box_as_checked   = false;
        mark_box_as_unchecked = false;
        mark_box_as_x         = true;
      }

      if (mark_box_as_unchecked) {
        clicked_box.prop('checked', false);
        if (clicked_box.closest('td').hasClass('upload_files')) {
          $('tr.unfiltered_upload').find('input[name="caps[unfiltered_upload]"]').prop('checked', false);
        }
      } else if (mark_box_as_checked) {
        clicked_box.prop('checked', true);
        if (clicked_box.closest('td').hasClass('upload_files')) {
          $('tr.unfiltered_upload').find('td').removeClass('cap-neg').removeClass('cap-yes').addClass('cap-no');
          $('tr.unfiltered_upload').find('input[type="checkbox"]').prop('checked',false);
          $('tr.unfiltered_upload').find('input.cme-negation-input').remove();
          $('input[name="caps[unfiltered_upload]"]').parent().closest('td').removeClass('cap-neg').removeClass('cap-yes').addClass('cap-no');
          $('input[name="caps[unfiltered_upload]"]').prop('checked', true).parent().find('input.cme-negation-input').remove();
          $('tr.unfiltered_upload').find('input[name="caps[unfiltered_upload]"]').prop('checked', true);
        }
      } else if (mark_box_as_x) {
        if (clicked_box.closest('td').hasClass('upload_files')) {
          $('tr.unfiltered_upload').find('a.neg-cap').trigger('click');
        }
        clicked_box.prop('checked', false);
        //perform X action if state is blank
        var box_parent = clicked_box.closest('td');
        box_parent.addClass('cap-neg');
        var cap_name_attr = box_parent.find('input[type="checkbox"]').attr('name');
        box_parent.append('<input type="hidden" class="cme-negation-input" name="'+cap_name_attr+'" value="" />');
        $('input[name="' + cap_name_attr + '"]').parent().next('a.neg-cap:visible').click();
      }
      clicked_box.addClass('interacted');
   });

   /**
    * Capabilities checkmark rotate
    */
    $(document).on('click', '.pp-row-action-rotate', function (event) {
      event.preventDefault();
      let clicked_box       = $(this);
      var checked_fields     = false;
      var unchecked_fields   = false;
      var all_checkbox      = 0;
      var negative_checkbox = 0;

      //determine if we should check or uncheck based on current input state
      clicked_box.closest('tr').find('input[type="checkbox"]').each(function () {
        if (!$(this).hasClass('excluded-input') && !$(this).prop('checked')) {
          all_checkbox++;
          unchecked_fields = true;
        } else if (!$(this).hasClass('excluded-input') && $(this).prop('checked')) {
          all_checkbox++;
          checked_fields = true;
        }
        if ($(this).closest('td').hasClass('cap-neg')) {
          negative_checkbox++;
        }
      });

      if ((checked_fields && unchecked_fields) || (negative_checkbox >= all_checkbox)) {
        checked_fields   = true;
        unchecked_fields = false;
      } else if (!checked_fields && unchecked_fields && !clicked_box.hasClass('interacted')) {
        checked_fields   = true;
        unchecked_fields = false;
      } else if (checked_fields && !unchecked_fields) {
        checked_fields   = false;
        unchecked_fields = true;
      } else {
        checked_fields   = false;
        unchecked_fields = false;
      }


      if (checked_fields) {
        //perform checked action
        clicked_box.closest('tr').find('td').filter('td[class!="cap-unreg"]').each(function () {
          $(this).closest('td').removeClass('cap-neg').removeClass('cap-yes').addClass('cap-no');
          $(this).parent().find('input[type="checkbox"]').prop('checked',true);
          $(this).parent().find('input.cme-negation-input').remove();
          // Also apply for any other checkboxes with the same name
          var cap_name_attr = $(this).next('input[type="checkbox"]').attr('name');
      
          if (!cap_name_attr) {
            cap_name_attr = $(this).next('label').find('input[type="checkbox"]').attr('name');
          }
      
          $('input[name="' + cap_name_attr + '"]').parent().closest('td').removeClass('cap-neg').removeClass('cap-yes').addClass('cap-no');
          $('input[name="' + cap_name_attr + '"]').prop('checked', true).parent().find('input.cme-negation-input').remove();
          if ($(this).closest('td').hasClass('upload_files')) {
            $('tr.unfiltered_upload').find('td').removeClass('cap-neg').removeClass('cap-yes').addClass('cap-no');
            $('tr.unfiltered_upload').find('input[type="checkbox"]').prop('checked',false);
            $('tr.unfiltered_upload').find('input.cme-negation-input').remove();
            $('input[name="caps[unfiltered_upload]"]').parent().closest('td').removeClass('cap-neg').removeClass('cap-yes').addClass('cap-no');
            $('input[name="caps[unfiltered_upload]"]').prop('checked', true).parent().find('input.cme-negation-input').remove();
            $('tr.unfiltered_upload').find('input[name="caps[unfiltered_upload]"]').prop('checked', true);
          }
        });
      } else if (unchecked_fields) {
        //perform blank action if state is checked
        clicked_box.closest('tr').find('td').filter('td[class!="cap-unreg"]').each(function () {
          $(this).closest('td').removeClass('cap-neg').removeClass('cap-yes').addClass('cap-no');
          $(this).parent().find('input[type="checkbox"]').prop('checked',false);
          $(this).parent().find('input.cme-negation-input').remove();
          // Also apply for any other checkboxes with the same name
          var cap_name_attr = $(this).next('input[type="checkbox"]').attr('name');
      
          if (!cap_name_attr) {
            cap_name_attr = $(this).next('label').find('input[type="checkbox"]').attr('name');
          }
      
          $('input[name="' + cap_name_attr + '"]').parent().closest('td').removeClass('cap-neg').removeClass('cap-yes').addClass('cap-no');
          $('input[name="' + cap_name_attr + '"]').prop('checked', false).parent().find('input.cme-negation-input').remove();
          if ($(this).closest('td').hasClass('upload_files')) {
            $('tr.unfiltered_upload').find('input[name="caps[unfiltered_upload]"]').prop('checked', false);
          }
        });
      } else {
        //perform X action if state is blank
        clicked_box.closest('tr').find('td[class!="cap-neg"]').filter('td[class!="cap-unreg"]').each(function () {
          $(this).addClass('cap-neg');
    
          var cap_name_attr = $(this).find('input[type="checkbox"]').attr('name');
          $(this).append('<input type="hidden" class="cme-negation-input" name="'+cap_name_attr+'" value="" />');
    
          $('input[name="' + cap_name_attr + '"]').parent().next('a.neg-cap:visible').click();
          if ($(this).closest('td').hasClass('upload_files')) {
            $('tr.unfiltered_upload').find('a.neg-cap').trigger('click');
          }
        });
      }

      clicked_box.addClass('interacted');

   });
  
  
   /**
    * unfiltered_upload change sync
    */
    $(document).on('change', 'tr.unfiltered_upload input[name="caps[unfiltered_upload]"]', function (event) {
     
      let clicked_box           = $(this);

      if (clicked_box.prop('checked')) {
        $('input[name="caps[upload_files]"]').parent().closest('td').removeClass('cap-neg').removeClass('cap-yes').addClass('cap-no');
        $('input[name="caps[upload_files]"]').prop('checked', true).parent().find('input.cme-negation-input').remove();
      } else if (!clicked_box.prop('checked')) {
        $('input[name="caps[upload_files]"]').parent().closest('td').removeClass('cap-neg').removeClass('cap-yes').addClass('cap-no');
        $('input[name="caps[upload_files]"]').prop('checked', false).parent().find('input.cme-negation-input').remove();
      }
      
    });

    /**
     * Other capabilities checkmark rotate
     */
     $(document).on('click', '.pp-single-action-rotate', function (event) {
       
       let clicked_input     = $(this);
       var checked_fields     = false;
       var unchecked_fields   = false;
 
       //determine if we should check or uncheck based on current input state
        if (clicked_input.prop('checked')) {
           unchecked_fields = true;
        } else if (!clicked_input.prop('checked')) {
          checked_fields = true;
        }
 
       if ((checked_fields && unchecked_fields)) {
         checked_fields   = true;
         unchecked_fields = false;
       } else if (!checked_fields && unchecked_fields && !clicked_input.hasClass('interacted')) {
         checked_fields   = true;
         unchecked_fields = false;
       } else if (checked_fields && !unchecked_fields) {
         checked_fields   = false;
         unchecked_fields = true;
       } else {
         checked_fields   = false;
         unchecked_fields = false;
       }
 
 
       if (!checked_fields && !unchecked_fields) {
         //perform X action if state is blank
         event.preventDefault();
         clicked_input.closest('td').find('a.neg-cap').click();
       }
 
       clicked_input.addClass('interacted');
 
       if (navigator.userAgent.toLowerCase().indexOf('firefox') > -1) {
         document.getSelection().empty();
       }
    });

});
