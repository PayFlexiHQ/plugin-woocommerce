/* global payflexi_flexible_checkout_frontend, paypal*/
(function ($) {

    'use strict';

    jQuery('#payflexi_popup_info_link').on('click', function() {
        try {
            let payflexi = PayFlexi.info();
            payflexi.show();
            } catch (error) {
            console.log(error.message);
        }
    });

    var payflexi_flexible_checkout_payment_submit = false;

    jQuery('#payflexi-flexible-checkout-payment-button' ).on('click', function() {
        return PayflexiFlexibleCheckoutPaymentFormHandler();
    });

    jQuery('#payflexi_flexible_checkout_payment_form form#order_review' ).submit( function() {
        return PayflexiFlexibleCheckoutPaymentFormHandler();
    });

    function PayflexiFlexibleCheckoutPaymentCustomFields() {
        var meta = {
            title: payflexi_flexible_checkout_params.products
        };
        if (payflexi_flexible_checkout_params.meta_order_id){
            meta['order_id'] = payflexi_flexible_checkout_params.meta_order_id;
        }
        if(payflexi_flexible_checkout_params.meta_name){
          meta['name'] = payflexi_flexible_checkout_params.meta_name;
        }
        if(payflexi_flexible_checkout_params.meta_email){
            meta['email'] = payflexi_flexible_checkout_params.meta_email;
        }
        if(payflexi_flexible_checkout_params.meta_phone){
            meta['phone'] = payflexi_flexible_checkout_params.meta_phone;
        }
        if(payflexi_flexible_checkout_params.meta_billing_address){
           meta['billing_address'] = payflexi_flexible_checkout_params.meta_billing_address;
        }
        if(payflexi_flexible_checkout_params.meta_shipping_address){
            meta['shipping_address'] = payflexi_flexible_checkout_params.meta_shipping_address;
        }
        return meta;
    }

    function PayflexiFlexibleCheckoutPaymentFormHandler() {

        if ( payflexi_flexible_checkout_payment_submit ) {
            payflexi_flexible_checkout_payment_submit = false;
            return true;
        }

        var $form = $('form#payment-form, form#order_review' ),
        payflexi_txnref = $form.find('input.payflexi_txnref' );

        payflexi_txnref.val( '' );

        var amount = Number( payflexi_flexible_checkout_params.amount );

        var payflexi_callback = function( response ) {
            $form.append( '<input type="hidden" class="payflexi_txnref" name="payflexi_txnref" value="' + response.reference + '"/>' );
            payflexi_flexible_checkout_payment_submit = true;
            $form.submit();

            $( 'body' ).block({
                message: null,
                overlayCSS: {
                    background: '#fff',
                    opacity: 0.6
                },
                css: {
                    cursor: "wait"
                }
            });
        };

        var handler = PayFlexi.checkout({
            key: payflexi_flexible_checkout_params.key,
            gateway: payflexi_flexible_checkout_params.gateway,
            amount:amount,
            email: payflexi_flexible_checkout_params.email,
            name: payflexi_flexible_checkout_params.name,
            currency: payflexi_flexible_checkout_params.currency,
            reference: payflexi_flexible_checkout_params.txnref,
            meta: PayflexiFlexibleCheckoutPaymentCustomFields(),
            onSuccess: payflexi_callback,
            onExit: function() {
                window.location.reload();
            },
            onDecline: function (response) {
                console.log(response);
                window.location.reload();
            }
        });

        handler.renderCheckout();

        return false;
    }
    

}(jQuery));


