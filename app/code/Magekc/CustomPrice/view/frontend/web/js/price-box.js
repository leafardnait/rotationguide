define([
    'jquery',
    'Magento_Catalog/js/price-box'
], function ($, priceBox) {
    'use strict';

    return $.extend(true, priceBox, {
        getFormattedPrice: function (price) {
            // Round to nearest whole number
            var rounded = Math.round(price);
            return this.formatPrice(rounded, 0);
        }
    });
});
