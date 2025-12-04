define([
    'jquery',
    'breeze'
], function ($, breeze) {
    'use strict';

    return function (BasePrice) {
        return BasePrice.extend({
            format: function (amount) {
                var rounded = Math.round(amount);
                return this.currencySymbol + rounded;
            }
        });
    };
});
