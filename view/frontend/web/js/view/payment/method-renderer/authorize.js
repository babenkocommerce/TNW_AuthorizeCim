/*
 * Copyright © 2018 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */

define([
    'jquery',
    'mage/translate',
    'Magento_Payment/js/view/payment/cc-form',
    'Magento_Checkout/js/model/quote',
    'Magento_Vault/js/view/payment/vault-enabler',
    'Magento_Checkout/js/model/full-screen-loader'
],
function ($, $t, Component, quote, VaultEnabler, fullScreenLoader) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'TNW_AuthorizeCim/payment/cc-form',
            ccCode: null,
            ccMessageContainer: null,
            code: 'tnw_authorize_cim',
            accept: null,

            /**
             * Additional payment data
             *
             * {Object}
             */
            additionalData: {},

            imports: {
                onActiveChange: 'active'
            }
        },

        /**
         * @returns {exports.initialize}
         */
        initialize: function () {
            this._super();
            this.vaultEnabler = new VaultEnabler();
            this.vaultEnabler.setPaymentCode(this.getVaultCode());

            return this;
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
            var data = this._super();
            delete data['additional_data']['cc_cid'];
            delete data['additional_data']['cc_number'];
            data['additional_data'] = _.extend(data['additional_data'], this.additionalData);
            this.vaultEnabler.visitAdditionalData(data);

            return data;
        },

        /**
         * @returns {Boolean}
         */
        isVaultEnabled: function () {
            return this.vaultEnabler.isVaultEnabled();
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
                        self.additionalData['opaqueDescriptor'] = response.opaqueData.dataDescriptor;
                        self.additionalData['opaqueValue'] = response.opaqueData.dataValue;
                        self.placeOrder();
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
            return window.checkoutConfig.payment[this.getCode()].vaultCode;
        },

        /**
         * @returns {String}
         */
        getSdkUrl: function () {
            return window.checkoutConfig.payment[this.getCode()].sdkUrl;
        }
    });
});