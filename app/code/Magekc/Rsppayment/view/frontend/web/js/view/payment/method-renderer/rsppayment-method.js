define([
    'Magento_Checkout/js/view/payment/default',
    'Magento_Checkout/js/action/redirect-on-success',
    'mage/url'
], function (Component, redirectOnSuccessAction, urlBuilder) {
    'use strict';
        
        return Component.extend({
            defaults: {
                template: 'Magekc_Rsppayment/payment/rsppayment-form'
            },

            getCode: function() {
                return 'magekc_rsppayment';
            },

            isActive: function() {
                return true;
            },

            getInstructions: function () {
                return window.checkoutConfig.payment.instructions[this.item.method];
            },
    
            afterPlaceOrder: function () {
                this.redirectAfterPlaceOrder = true;
                redirectOnSuccessAction.redirectUrl = urlBuilder.build('rsppayment/checkout/start');
            }
        });
    }
);
