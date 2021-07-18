(function ($) {

    /**
     * Ignore banner
     */
    $(document).on('click', '[data-get-woo-permalink-manager-banner--ignore]', function (e) {
        e.preventDefault();

        var $this = $(this);
        var notice = $this.closest('[data-get-woo-permalink-manager-banner]');

        notice.slideUp();

        $.get($this.attr('href'));

    });

}(jQuery));