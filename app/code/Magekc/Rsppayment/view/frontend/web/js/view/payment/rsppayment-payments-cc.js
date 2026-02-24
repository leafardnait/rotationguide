
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'magekc_rsppayment_cc',
                component: 'Magekc_Rsppayment/js/view/payment/method-renderer/rsppayment-method-cc'
            }
        );
        /** Add view logic here if needed */
        return Component.extend({});
    }
);