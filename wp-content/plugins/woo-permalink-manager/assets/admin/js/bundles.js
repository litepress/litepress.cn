/**
 * Tabs for variable product
 */
jQuery(document).ready(function ($) {

    $(document).on('click', '[data-variation-tab-target]', function (e) {
        e.preventDefault();

        var $this = $(this);
        var target = $this.attr('data-variation-tab-target');
        var container = $('[data-variation-tab-content="' + target + '"]');
        $('[data-variation-tab-target]').removeClass('is-active');
        $this.addClass('is-active');

        $('[data-variation-tab-content]').addClass('hidden');
        container.removeClass('hidden');
        container.find('[data-variation-control]').first().prop("checked", true);

    });

    $(document).on('click', '[data-qa-toggle]', function () {
        console.log('togle');
        $(this).closest('[data-qa-scope]').toggleClass('is-open').find('[data-qa-content]').slideToggle('fast');

    });

    //Smooth scrolling with links
    $('a[href*=\\#].c-license__plugin-link').on('click', function (event) {
        event.preventDefault();
        $('html,body').animate({ scrollTop: $(this.hash).offset().top }, 500);
    });


    //Freemius checkout
    $(document).ready(function () {
        $('[data-freemius-bundle]').on('click', function (e) {
            var premmerce_img = $('[data-freemius-image]');

            var billing_cycle = $('[data-variation-tab-target]').filter('.is-active').attr('data-variation-tab-target');

            var handler = FS.Checkout.configure({
                plugin_id: '2057',
                plan_id: '3071',
                public_key: 'pk_b9c33adf75c1f4c5b3b21216a9c27',
                billing_cycle: billing_cycle,
                image: premmerce_img
            });

            handler.open({
                name: 'Premmerce Bundle',
                licenses: $(this).attr("data-licence"),
                // You can consume the response for after purchase logic.
                purchaseCompleted: function (response) {
                    // The logic here will be executed immediately after the purchase confirmation.                                // alert(response.user.email);
                },
                success: function (response) {
                    // The logic here will be executed after the customer closes the checkout, after a successful purchase.                                // alert(response.user.email);
                }
            });
            e.preventDefault();
        });
    });

});
