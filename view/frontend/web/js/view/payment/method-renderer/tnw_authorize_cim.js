/*
 * Copyright © 2018 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */

define([
    'jquery',
    'Magento_Payment/js/view/payment/cc-form',
    'Magento_Checkout/js/model/full-screen-loader'
],
function ($, Component, fullScreenLoader) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'TNW_AuthorizeCim/payment/cc-form',
            ccCode: null,
            ccMessageContainer: null,
            code: 'tnw_authorize_cim',
            accept: null,
            imports: {
                onActiveChange: 'active'
            }
        },

        /**
         * Set list of observable attributes
         *
         * @returns {exports.initObservable}
         */
        initObservable: function () {
            this._super()
                .observe(['active']);

            return this;
        },

        /**
         * Check if payment is active
         *
         * @returns {Boolean}
         */
        isActive: function() {
            var active = this.getCode() === this.isChecked();

            this.active(active);

            return active;
        },

        /**
         * Triggers when payment method change
         * @param {Boolean} isActive
         */
        onActiveChange: function (isActive) {
            var self = this;

            if (!isActive) {
                return;
            }

            this.restoreMessageContainer();
            this.restoreCode();

            fullScreenLoader.startLoader();
            require([this.getSdkUrl()], function () {
                self.accept = window.Accept;
                fullScreenLoader.stopLoader();
            });
        },

        /**
         * Get full selector name
         *
         * @param {String} field
         * @returns {String}
         */
        getSelector: function (field) {
            return '#' + this.getCode() + '_' + field;
        },

        /**
         * Restore original message container for cc-form component
         */
        restoreMessageContainer: function () {
            this.messageContainer = this.ccMessageContainer;
        },

        /**
         * Restore original code for cc-form component
         */
        restoreCode: function () {
            this.code = this.ccCode;
        },

        /** @inheritdoc */
        initChildren: function () {
            this._super();
            this.ccMessageContainer = this.messageContainer;
            this.ccCode = this.code;

            return this;
        },

        /**
         * Get payment method code
         *
         * @returns {string}
         */
        getCode: function () {
            return this.code;
        },

        /**
         * Get data
         *
         * @returns {Object}
         */
        getData: function () {
            return this._super();
        },

        /**
         * Returns state of place order button
         * @returns {Boolean}
         */
        isButtonActive: function () {
            return this.isActive() && this.isPlaceOrderActionAllowed();
        },

        /**
         * Validate current credit card type
         * @returns {Boolean}
         */
        validateCardType: function () {
            return this.selectedCardType() !== null;
        },

        /**
         * Triggers order placing
         */
        placeOrderClick: function () {
            var self = this;

            if (this.validateCardType()) {
                this.isPlaceOrderActionAllowed(false);
                var paymentData = {
                        cardData: {
                            cardNumber: $(this.getSelector('cc_number')).val().replace(/\D/g, ''),
                            month: $(this.getSelector('expiration')).val(),
                            year: $(this.getSelector('expiration_yr')).val(),
                            cardCode: $(this.getSelector('cc_cid')).val()
                        },
                        authData: {
                            clientKey: this.getClientKey(),
                            apiLoginID: this.getApiLoginId()
                        }
                    };

                this.accept.dispatchData(paymentData, function (response) {
                    if (response.messages.resultCode === "Error") {
                        self.isPlaceOrderActionAllowed(true);

                        var i = 0;
                        while (i < response.messages.message.length) {
                            self.messageContainer.addErrorMessage({
                                message:response.messages.message[i].code + ": " + response.messages.message[i].text
                            });
                            i = i + 1;
                        }
                    } else {
                        response.opaqueData.dataValue;
                    }
                });
            }
        },

        /**
         * @returns {String}
         */
        getClientKey: function () {
            return window.checkoutConfig.payment[this.getCode()].clientKey;
        },

        /**
         * @returns {String}
         */
        getApiLoginId: function () {
            return window.checkoutConfig.payment[this.getCode()].apiLoginId;
        },

        /**
         * @returns {String}
         */
        getVaultCode: function () {
            return window.checkoutConfig.payment[this.getCode()].ccVaultCode;
        },

        /**
         * @returns {String}
         */
        getSdkUrl: function () {
            return window.checkoutConfig.payment[this.getCode()].sdkUrl;
        }
    });
});