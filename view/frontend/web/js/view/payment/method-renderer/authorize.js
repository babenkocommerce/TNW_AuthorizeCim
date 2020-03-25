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
    'TNW_AuthorizeCim/js/view/payment/validator-handler',
    'Magento_Checkout/js/model/full-screen-loader'
],
function ($, $t, Component, quote, VaultEnabler, validatorManager, fullScreenLoader) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'TNW_AuthorizeCim/payment/cc-form',
            ccCode: null,
            ccMessageContainer: null,
            validatorManager: validatorManager,
            code: 'tnw_authorize_cim',

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

            this.validatorManager.initialize();
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
            if (!isActive) {
                return;
            }

            this.restoreMessageContainer();
            this.restoreCode();

            fullScreenLoader.startLoader();
            this.validatorManager.load(function() {
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
         * Add data
         * @param key {String}
         * @param value {String}
         */
        addAdditionalData: function(key, value) {
            this.additionalData[key] = value;
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
            if (this.validateCardType()) {
                this.placeOrder();
            }
        },

        /**
         * @returns {String}
         */
        getVaultCode: function () {
            return window.checkoutConfig.payment[this.getCode()].vaultCode;
        },

        /**
         * Action to place order
         * @param {String} key
         */
        placeOrder: function (key) {
            var self = this;

            if (key) {
                return self._super();
            }

            // place order on success validation
            self.isPlaceOrderActionAllowed(false);
            self.validatorManager.validate(self)
                .done(function () {
                    self.isPlaceOrderActionAllowed(true);
                    self.placeOrder('parent');
                })
                .fail(function (error) {
                    self.isPlaceOrderActionAllowed(true);
                    self.messageContainer.addErrorMessage({
                        message: error
                    });
                });

            return false;
        }
    });
});
