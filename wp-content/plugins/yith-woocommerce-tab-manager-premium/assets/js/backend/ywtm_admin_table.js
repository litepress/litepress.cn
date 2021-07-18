jQuery(document).ready(function ($) {


    //Handle multi-dependencies
    function multi_dependencies_handler(id, deps, values, first) {
        var result = true;

        for (var i = 0; i < deps.length; i++) {

            if (deps[i].substr(0, 6) == ':radio') {
                deps[i] = deps[i] + ':checked';
            }

            var val = $(deps[i]).val();

            if ($(deps[i]).attr('type') == 'checkbox') {
                var thisCheck = $(deps[i]);
                if (thisCheck.is(':checked')) {
                    val = 'yes';
                }
                else {
                    val = 'no';
                }
            }
            if (result && (val == values[i])) {
                result = true;
            }
            else {
                result = false;
                break;
            }
        }

        if (!result) {
            $(id + '-container').parent().hide();
        } else {
            $(id + '-container').parent().show();
        }
    }

    function isArray(myArray) {
        return myArray.constructor.toString().indexOf('Array') > -1;
    }


    //metaboxes
    $('.metaboxes-tab [data-dep-target]').each(function () {
        var t = $(this);

        var deps = t.data('dep-id').split(',');

        if (isArray(deps) && deps.length > 1) {
            var field = '#' + t.data('dep-target');
            var values = t.data('dep-value').split(',');
            multi_dependencies_handler(field, deps, values, true);
            for (var i = 0; i < deps.length; i++) {
                deps[i] = '#' + deps[i];
            }

            for (var i = 0; i < deps.length; i++)
                $(deps[i]).on('change', function () {
                    multi_dependencies_handler(field, deps, values, false);
                }).change();
        }
    });
    //show more/less


    $('.ywtm_description.show_more').each(function () {

        var content = $(this).html(),
            max_char = $(this).data('max_char'),
            show_more_txt = $(this).data('more_text');
        if (content.length > max_char) {


            var c = content.substr(0, max_char);
            var h = content.substr(max_char, content.length - max_char);

            var html = c + '<span class="ywtm_morecontent"><span style="display: none;">' + h + '</span>&nbsp;&nbsp;<a href="" class="ywtm_morelink">' + show_more_txt + '</a></span>';

            $(this).html(html);
        }
    });

    $(".ywtm_morelink").click(function () {

        var parent = $(this).parents('.ywtm_description'),
            show_more_txt = parent.data('more_text'),
            show_less_txt = parent.data('less_text');

        if ($(this).hasClass("less")) {
            $(this).removeClass("less");
            $(this).html(show_more_txt);
        } else {
            $(this).addClass("less");
            $(this).html(show_less_txt);
        }
        $(this).parent().prev().toggle('slow');
        $(this).prev().toggle();
        return false;
    });

    $('#_ywtm_icon_tab_select_icon_mode').on('change', function (e) {

        var select = $(this),
            value = select.val(),
            icon_wrapper = $("#yit-icons-manager-wrapper-_ywtm_icon_tab_icon"),
            custom_wrapper = $("#_ywtm_icon_tab_custom").parent();

        if ('icon' == value) {
            icon_wrapper.show();
            custom_wrapper.hide();
        } else if ('upload' == value) {
            icon_wrapper.hide();
            custom_wrapper.show();
        } else {
            icon_wrapper.hide();
            custom_wrapper.hide();
        }
    }).trigger('change');

    $('.ywtb-table').on('click', 'a.insert', function (e) {

        var table = $(this).closest('.ywtb-table'),
            add_table_row = '',
            metabox_id = '';

        e.preventDefault();
        if (table.length) {

            if (table.hasClass('ywtb-video-table')) {
                add_table_row = 'video_row';
                metabox_id = '#_ywtm_video-container';

            } else if (table.hasClass('ywtb-download-table')) {
                add_table_row = 'download_row';
                metabox_id = '#_ywtm_download-container';
            } else {
                add_table_row = 'faq_row';
                metabox_id = '#_ywtm_faqs-container';
            }


            var i = table.find('tbody tr').size(),
                data = {
                    'row': i,
                    'add_table_row': add_table_row,
                    'action': yith_tab_params.actions.add_table_row
                };

            $.ajax({
                type: 'POST',
                url: yith_tab_params.admin_url,
                data: data,
                dataType: 'json',
                success: function (response) {

                    var tbody = $(metabox_id).find('.ywtb-table tbody');

                    tbody.append(response.result);

                }

            });
        }
    });


    $('.ywtb-table tbody').sortable({
        items: 'tr',
        cursor: 'move',
        axis: 'y',
        handle: 'td.sort',
        scrollSensitivity: 40,
        forcePlaceholderSize: true,
        helper: 'clone',
        opacity: 0.65
    });

    $('.ywtb-table').on('click', 'a.delete', function (e) {
        e.preventDefault();

        $(this).closest('tr').remove();
    });

    // Uploading files.
    var downloadable_file_frame;
    var file_path_field;

    $(document.body).on('click', '.upload_file_button', function (event) {
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
                multiple: true,
                title: $el.data('choose'),
                priority: 20,
                filterable: 'uploaded'
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
                text: $el.data('update')
            },
            multiple: true,
            states: downloadable_file_states
        });

        // When an image is selected, run a callback.
        downloadable_file_frame.on('select', function () {
            var file_path = '';
            var selection = downloadable_file_frame.state().get('selection');

            selection.map(function (attachment) {
                attachment = attachment.toJSON();
                if (attachment.url) {
                    file_path = attachment.url;
                }
            });

            file_path_field.val(file_path).change();
        });

        // Set post to 0 and set our custom type.
        downloadable_file_frame.on('ready', function () {
            downloadable_file_frame.uploader.options.uploader.params = {
                type: 'downloadable_product'
            };
        });

        // Finally, open the modal.
        downloadable_file_frame.open();
    });

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

    $('#_ywtm_form_tab_name,#_ywtm_form_tab_website,#_ywtm_form_tab_subject').on('change', function (e) {

        var current_check_box = $(this),
            id = current_check_box.attr('id');

        if (current_check_box.is(':checked')) {
            enable_opt($('#' + id + '_req').closest('.ywtb-field'));
        } else {
            disable_opt($('#' + id + '_req').closest('.ywtb-field'));
        }

    }).trigger('change');

});