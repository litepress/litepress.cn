/*jslint regexp: true, confusion: true, undef: true, sloppy: true, eqeq: true, vars: true, white: true, plusplus: true, maxerr: 50, indent: 4 */
/*global gdbbPressAttachmentsInit*/

;(function($, window, document, undefined) {
    window.wp = window.wp || {};
    window.wp.gdatt = window.wp.gdatt || {};

    window.wp.gdatt.attachments = {
        init: function() {
            $("form#new-post").attr("enctype", "multipart/form-data");

            $(document).on("click", ".d4p-bba-actions a", function(e){
                return confirm(gdbbPressAttachmentsInit.are_you_sure);
            });

            $(document).on("click", ".d4p-attachment-addfile", function(e){
                e.preventDefault();

                var now = $(".bbp-attachments-form input[type=file]").length,
                    max = parseInt(gdbbPressAttachmentsInit.max_files);

                if (now < max) {
                    $(this).before('<input type="file" size="40" name="d4p_attachment[]"><br/>');
                }

                if (now + 1 >= max) {
                    $(this).remove();
                }
            });
        }
    };

    $(document).ready(function() {
        wp.gdatt.attachments.init();
    });
})(jQuery, window, document);
