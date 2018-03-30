/*
 * Copyright © 2018 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */

define(
    [
        'jquery',
        'ko',
        'Magento_Payment/js/view/payment/cc-form',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Magento_Checkout/js/action/redirect-on-success',
        'Magento_Payment/js/model/credit-card-validation/validator',
        'mage/validation',
        'TNW_authorize_cim_accept_js_sandbox'
    ],
    function (
        $,
        ko,
        Component,
        additionalValidators,
        redirectOnSuccess
    ) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'TNW_AuthorizeCim/payment/cc-form',
                payment_method_code: 'tnw_authorize_cim',
                opaqueToken: null
            },

            initialize: function () {
                this._super();
                window['acceptJs_' + this.getCode() + '_callback'] = function(response) {
                    this.handleAcceptResponse(response);
                }.bind(this);
            },

            isShowLegend: function() {
                return true;
            },

            isActive: function() {
                return true;
            },

            /**
             * Get payment method code
             *
             * @returns {string}
             */
            getCode: function () {
                return this.payment_method_code;
            },

            /**
             * Get data
             *
             * @returns {Object}
             */
            getData: function () {
                //TODO Удалить ненужную инфу
                return {
                    'method': this.item.method,
                    'additional_data': {
                        'cc_cid': this.creditCardVerificationNumber(),
                        'cc_ss_start_month': this.creditCardSsStartMonth(),
                        'cc_ss_start_year': this.creditCardSsStartYear(),
                        'cc_ss_issue': this.creditCardSsIssue(),
                        'cc_type': this.creditCardType(),
                        'cc_exp_year': this.creditCardExpYear(),
                        'cc_exp_month': this.creditCardExpMonth(),
                        'cc_number': this.creditCardNumber(),
                        'cc_token' : this.opaqueToken
                    }
                };
            },

            /**
             * Action to place order
             */
            placeOrder: function (data, event) {
                debugger;
                if (event) {
                    event.preventDefault();
                }

                this.isPlaceOrderActionAllowed(false);

                if (this.validate() && additionalValidators.validate()) {
                    this.createTokenAndSaveOrder();
                }
            },

            /**
             * Send payment info via Accept.js and call callback
             */
            createTokenAndSaveOrder: function () {
                debugger;
                var form = $('#' + this.getCode() + '-form'),
                    paymentData = {
                        cardData: {
                            cardNumber: form.find('#' + this.getCode() + '_cc_number').val().replace(/\D/g, ''),
                            month: form.find('#' + this.getCode() + '_expiration').val(),
                            year: form.find('#' + this.getCode() + '_expiration_yr').val(),
                            cardCode: ''
                        },
                        authData: {
                            clientKey: this.getUiData().clientKey,
                            apiLoginID: this.getUiData().apiLoginId
                        }
                    };

                if (form.find('#' + this.getCode() + '_cc_cid').length > 0) {
                    paymentData['cardData']['cardCode'] = form.find('#' + this.getCode() + '_cc_cid').val();
                }

                Accept.dispatchData(paymentData, 'acceptJs_' + this.getCode() + '_callback');
            },

            /**
             * Process accept.js response
             *
             * @param acceptResponse
             */
            handleAcceptResponse: function (acceptResponse) {
                debugger;
                if (acceptResponse.messages.resultCode === "Error") {
                    //TODO обработать ошибку
                } else {
                    if (typeof acceptResponse.opaqueData === 'object' &&
                        typeof acceptResponse.opaqueData.dataValue === 'string') {
                        this.opaqueToken = acceptResponse.opaqueData.dataValue;
                    }
                    this.saveOrder();
                }
            },

            /**
             * Save order in DB
             */
            saveOrder: function () {
                var self = this;
                this.getPlaceOrderDeferredObject()
                    .fail(function () {
                        self.isPlaceOrderActionAllowed(true);
                    }).done(function () {
                    self.isPlaceOrderActionAllowed(true);
                    redirectOnSuccess.execute();
                });

            },

            /**
             * Validate form fields
             *
             * @returns {boolean}
             */
            validate: function () {
                var form = $('#' + this.getCode() + '-form');

                return form.validation() && form.validation('isValid');
            },

            /**
             * Get config data from UI config provider
             *
             * @returns {object}
             */
            getUiData: function () {
                return window.checkoutConfig.payment[this.getCode()];
            }
        });
    }
);