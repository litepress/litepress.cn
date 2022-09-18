(function ($) {
    // bind a button or a link to open the dialog
    $('.yith-last-changelog').click(function(e) {
        e.preventDefault();
        var inlineId = $( this ).data( 'changelogid' ),
            inlineModal = $('#' + inlineId),
            plugininfo = $( this ).data( 'plugininfo' ),
            b = {},
            close_function = function() { $( this ).dialog( "close" ); };

        b[yith_dashboard.buttons.close] = close_function;

        // initalise the dialog
        inlineModal.dialog({
            title: plugininfo,
            dialogClass: 'wp-dialog',
            autoOpen: false,
            draggable: false,
            width: 'auto',
            modal: true,
            resizable: false,
            closeOnEscape: true,
            position: {
                my: "center",
                at: "center",
                of: window
            },
            buttons: b,
            show: {
                effect: "blind",
                duration: 1000
            },
            open: function () {
                // close dialog by clicking the overlay behind it
                $('.ui-widget-overlay').bind('click', function(){
                    inlineModal.dialog('close');
                })
            },
            create: function () {
                // style fix for WordPress admin
                $('.ui-dialog-titlebar-close').addClass('ui-button');
            },
        });

        inlineModal.dialog('open');
    });
})(jQuery);