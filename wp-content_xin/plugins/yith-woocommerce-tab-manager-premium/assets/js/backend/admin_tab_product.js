/**
 * Created by Your Inspiration on 10/04/2015.
 */
jQuery(document).ready(function ($) {
    $('#custom_check_map').on('click', function () {

        if (!$('#custom_check_map').is(":checked")) {

            $('#custom_width_enable').css('display', 'block');
        }
        else
            $('#custom_width_enable').css('display', 'none');
    });

    $('.add_field_check').on('change', function () {


        var t = $(this),
            id_sub_cont = '#' + t.attr('id') + '_req';

        if (t.is(':checked')) {

            $(id_sub_cont).show();
        }
        else {
            $(id_sub_cont).hide();
        }

    });


    /*Downloadable tab*/
    // Uploading files
    var downloadable_file_frame;
    var file_path_field;

    $(document).on('click', '.tab_upload_file_button', function (event) {

        var $el = $(this);

        file_path_field = $el.closest('tr').find('td.file_url input');

        event.preventDefault();

        // If the media frame already exists, reopen it.
        if (downloadable_file_frame) {
            downloadable_file_frame.open();
            return;
        }

        var downloadable_file_states = [
            // Main states.
            new wp.media.controller.Library({
                library: wp.media.query(),
                multiple: false,
                title: $el.data('choose'),
                priority: 20,
                filterable: 'uploaded',
            })
        ];

        // Create the media frame.
        downloadable_file_frame = wp.media.frames.downloadable_file = wp.media({
            // Set the title of the modal.
            title: $el.data('choose'),
            library: {
                type: ''
            },
            button: {
                text: $el.data('update'),
            },
            multiple: false,
            states: downloadable_file_states,
        });

        // When an image is selected, run a callback.
        downloadable_file_frame.on('select', function () {

            var file_path = '';
            var selection = downloadable_file_frame.state().get('selection');

            selection.map(function (attachment) {

                attachment = attachment.toJSON();

                if (attachment.url)
                    file_path = attachment.url

            });

            file_path_field.val(file_path);
        });


        // Finally, open the modal.
        downloadable_file_frame.open();
    });

    $('.ywtm_override_cb').on('change', function () {

        var t = $(this),
            tab_type = t.data('tab_type'),
            container_id = '#ywtm_wc_tab_content_';

        switch (tab_type) {
            case 'reviews':
                container_id += 'reviews';
                break;
            case 'description':
                container_id += 'description';
                break;
            case 'additional_information':
                container_id += 'additional_information';
                break;
        }

        if (t.is(':checked'))
            $(container_id).show();
        else
            $(container_id).hide();
    }).change();

    /*FORM CHECK BOX

     */

    var disable_opt = function (disable) {
            disable.css('opacity', '0.3');
            disable.css('pointer-events', 'none');
        },

        enable_opt = function (disable) {
            disable.css('opacity', '1');
            disable.css('pointer-events', 'auto');
        };

    $('#field_name,#field_webaddr,#field_subj').on('change', function (e) {

        var current_check_box = $(this),
            id = current_check_box.attr('id');

        if (current_check_box.is(':checked')) {
            enable_opt($('#' + id + '_req').closest('.form-field'));
        } else {
            disable_opt($('#' + id + '_req').closest('.form-field'));
        }

    }).trigger('change');

    $('.yith_tab_manager_product').on( 'click', 'a.insert', function(e){


        var table = $(this).parents('table.widefat'),
            hidden_field  = table.find('.yith_tab_hidden_field');

        if( hidden_field.length ) {
            hidden_field.remove();
        }


    });


    $('.yith_tab_manager_product').on( 'click', 'a.delete',function(e){
        var table = $(this).parents('table.widefat'),
            table_rows = table.find( 'tbody tr');


            if( ( table_rows.length -1 <= 0 ) && !table.find('.yith_tab_hidden_field').length ){
           
                var tab_id = table.data('tab_id'),
                    field_name = 'yith_product_tabs['+tab_id+']',
                    field_hidden = "<input type='hidden' name='"+field_name+"' class='yith_tab_hidden_field'>";

                table.find('tbody').append( field_hidden);
            }
    });
});

