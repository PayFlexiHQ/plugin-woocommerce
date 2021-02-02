(function ($) {
    $('#woocommerce_payflexi-flexible-checkout_env').on('change', function () {
        var value = $(this).val(),
            test = $('h3.test'),
            live = $('h3.live');

        if ('live' == value) {
            test.hide().next().hide().next().hide();
            live.show().next().show().next().show();
        } else if ('test' == value) {
            test.show().next().show().next().show();
            live.hide().next().hide().next().hide();
        }

    }).change();
}(jQuery));