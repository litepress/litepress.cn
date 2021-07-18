(function ($) {
    $(document).on('click', '.notice-dismiss', function () {
        var t = $(this),
            wrapper_id = t.parent().attr('id');

        if (wrapper_id === 'yith-system-alert') {
            var cname = 'hide_yith_system_alert',
                cvalue = 'yes';

            document.cookie = cname + "=" + cvalue + ";path=/";
        }
    });
})(jQuery);
