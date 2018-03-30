/*
 * Copyright © 2018 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */

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
                type: 'tnw_authorize_cim',
                component: 'TNW_AuthorizeCim/js/view/payment/method-renderer/tnw_authorize_cim'
            }
        );
        /** Add view logic here if needed */
        return Component.extend({});
    }
);