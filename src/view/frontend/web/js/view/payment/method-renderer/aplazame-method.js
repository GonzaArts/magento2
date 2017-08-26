define(
    [
        'jquery',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/action/redirect-on-success',
        'Magento_Checkout/js/action/set-payment-information',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Magento_Checkout/js/model/quote'
    ],
    function (
        $,
        Component,
        redirectOnSuccessAction,
        setPaymentInformationAction,
        additionalValidators,
        quote
    ) {
        'use strict';

        var config = window.checkoutConfig.payment.aplazame_payment;

        return Component.extend({
            defaults: {
              template: 'Aplazame_Payment/payment/form'
            },

            redirectAfterPlaceOrder: false,

            /**
             * @override
             */
            placeOrder: function () {
                if (additionalValidators.validate()) {
                    this.isPlaceOrderActionAllowed(false);

                    $.when(
                        setPaymentInformationAction(
                            this.messageContainer,
                            {
                                method: this.getCode()
                            }
                        )
                    )
                    .done(this.launchAplazameCheckout().bind(this))
                    .fail(this.fail.bind(this));
                }
            },

            launchAplazameCheckout: function () {
                var payload = config.checkout;

                payload.merchant.onDismiss = function () {};
                payload.merchant.onError = function () {};
                payload.merchant.onSuccess = function () {
                    redirectOnSuccessAction.execute()
                };

                var totals = quote.totals();
                payload.order.currency = totals.quote_currency_code;
                payload.order.total_amount = aplazame._.parsePrice("" + totals.base_grand_total);
                payload.order.discount = aplazame._.parsePrice("" + totals.discount_amount);

                if (quote.guestEmail) {
                    payload.customer.email = quote.guestEmail ;
                }

                var billingAddress = quote.billingAddress();
                payload.billing.city = billingAddress.city;
                payload.billing.country = billingAddress.countryId;
                payload.billing.first_name = billingAddress.firstname;
                payload.billing.last_name = billingAddress.lastname;
                payload.billing.phone = billingAddress.telephone;
                payload.billing.postcode = billingAddress.postcode;
                payload.billing.state = billingAddress.region;
                payload.billing.street = billingAddress.street[0];

                var shippingAddress = quote.shippingAddress();
                payload.shipping.city = shippingAddress.city;
                payload.shipping.country = shippingAddress.countryId;
                payload.shipping.first_name = shippingAddress.firstname;
                payload.shipping.last_name = shippingAddress.lastname;
                payload.shipping.phone = shippingAddress.telephone;
                payload.shipping.postcode = shippingAddress.postcode;
                payload.shipping.state = shippingAddress.region;
                payload.shipping.street = shippingAddress.street[0];

                var shippingMethod = quote.shippingMethod();
                payload.shipping.name = shippingMethod.carrier_code + "_" + shippingMethod.method_code;
                payload.shipping.price = aplazame._.parsePrice("" + totals.shipping_amount);
                payload.shipping.discount = aplazame._.parsePrice("" + totals.shipping_discount_amount);

                aplazame.checkout(payload);
            },
        });
    }
);
