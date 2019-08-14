/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define([
    'jquery',
    'uiComponent',
    'Magento_Ui/js/modal/alert',
    'Magento_Ui/js/lib/view/utils/dom-observer'
], function ($, Class, alert, domObserver) {
    'use strict';

    return Class.extend({

        defaults: {
            $selector: null,
            selector: 'edit_form',
            container: 'payment_form_tnw_authorize_cim',
            active: false,
            scriptLoaded: false,
            accept: null,
            imports: {
                onActiveChange: 'active'
            }
        },

        /**
         * Set list of observable attributes
         * @returns {exports.initObservable}
         */
        initObservable: function () {
            var self = this;

            self.$selector = $('#' + self.selector);
            this._super()
                .observe([
                    'active',
                    'scriptLoaded'
                ]);

            // re-init payment method events
            self.$selector.off('changePaymentMethod.' + this.code)
                .on('changePaymentMethod.' + this.code, this.changePaymentMethod.bind(this));

            // listen block changes
            domObserver.get('#' + self.container, function () {
                if (self.scriptLoaded()) {
                    self.$selector.off('submit');
                }
            });

            return this;
        },

        /**
         * Enable/disable current payment method
         * @param {Object} event
         * @param {String} method
         * @returns {exports.changePaymentMethod}
         */
        changePaymentMethod: function (event, method) {
            this.active(method === this.code);

            return this;
        },

        /**
         * Triggered when payment changed
         * @param {Boolean} isActive
         */
        onActiveChange: function (isActive) {
            if (!isActive) {
                this.$selector.off('submitOrder.' + this.code);

                return;
            }
            this.disableEventListeners();
            window.order.addExcludedPaymentMethod(this.code);

            if (!this.apiLoginID || !this.clientKey) {
                this.error($.mage.__('This payment is not available'));

                return;
            }

            this.enableEventListeners();

            if (!this.scriptLoaded()) {
                this.loadScript();
            }
        },

        /**
         * Load external Authorize SDK
         */
        loadScript: function () {
            var self = this,
                state = self.scriptLoaded;

                
            $('body').trigger('processStart');
            require([this.sdkUrl], function () {
                state(true);
                self.accept = window.Accept;
                $('body').trigger('processStop');
            });
        },

        /**
         * Show alert message
         * @param {String} message
         */
        error: function (message) {
            alert({
                content: message
            });
        },

        /**
         * Enable form event listeners
         */
        enableEventListeners: function () {
            this.$selector.on('submitOrder.'+ this.code, this.submitOrder.bind(this));
        },

        /**
         * Disable form event listeners
         */
        disableEventListeners: function () {
            this.$selector.off('submitOrder');
            this.$selector.off('submit');
        },

        /**
         * Store payment details
         * @param {String} nonce
         */
        setOpaqueDescriptor: function (nonce) {
            var $container = $('#' + this.container);

            $container.find('[name="payment[opaqueDescriptor]"]').val(nonce);
        },

        /**
         * Store payment details
         * @param {String} nonce
         */
        setOpaqueValue: function (nonce) {
            var $container = $('#' + this.container);

            $container.find('[name="payment[opaqueValue]"]').val(nonce);
        },

        /**
         * Trigger order submit
         */
        submitOrder: function () {
            this.$selector.validate().form();
            this.$selector.trigger('afterValidate.beforeSubmit');
            $('body').trigger('processStop');

            // validate parent form
            if (this.$selector.validate().errorList.length) {
                return false;
            }

            var self = this,
                paymentData = {
                cardData: {
                    cardNumber: $(this.getSelector('cc_number')).val().replace(/\D/g, ''),
                    month: $(this.getSelector('expiration')).val(),
                    year: $(this.getSelector('expiration_yr')).val(),
                    cardCode: $(this.getSelector('cc_cid')).val()
                },
                authData: {
                    clientKey: this.clientKey,
                    apiLoginID: this.apiLoginID
                }
            };

            this.accept.dispatchData(paymentData, function (response) {
                if (response.messages.resultCode === "Error") {
                    var i = 0;
                    while (i < response.messages.message.length) {
                        self.error(response.messages.message[i].code + ": " + response.messages.message[i].text);
                        i = i + 1;
                    }
                } else {
                    self.setOpaqueDescriptor(response.opaqueData.dataDescriptor);
                    self.setOpaqueValue(response.opaqueData.dataValue);
                    self.placeOrder();
                }
            });
        },

        /**
         * Place order
         */
        placeOrder: function () {
            $('#' + this.selector).trigger('realOrder');
        },

        /**
         * Get jQuery selector
         * @param {String} field
         * @returns {String}
         */
        getSelector: function (field) {
            return '#' + this.code + '_' + field;
        }
    });
});
