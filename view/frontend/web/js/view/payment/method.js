/*
 * Copyright © 2018 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
define([
    'uiComponent',
    'Magento_Checkout/js/model/payment/renderer-list'
], function (Component, rendererList) {
    'use strict';

    var config = window.checkoutConfig.payment,
        type = 'tnw_authorize_cim';

    if (config[type].isActive) {
        rendererList.push(
            {
                type: type,
                component: 'TNW_AuthorizeCim/js/view/payment/method-renderer/authorize'
            }
        );
    }

    /** Add view logic here if needed */
    return Component.extend({});
});