/*
 * Copyright © 2018 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
define([
    'jquery',
    'mage/translate'
], function ($, $t) {
    'use strict';

    return {
        config: null,
        accept: null,

        /**
         * Set 3d secure config
         * @param {Object} config
         */
        setConfig: function (config) {
            this.config = config;
        },

        /**
         * Get code
         * @returns {String}
         */
        getCode: function () {
            return 'tnw_authorize_cim';
        },

        /**
         * Load sdk
         */
        load: function() {
            this.accept = window.Accept;
        },

        /**
         * Validate Braintree payment nonce
         * @param {Object} context
         * @returns {Object}
         */
        validate: function (context) {
            var state = $.Deferred();

            var paymentData = {
                cardData: {
                    cardNumber: $(context.getSelector('cc_number')).val().replace(/\D/g, ''),
                    month: $(context.getSelector('expiration')).val(),
                    year: $(context.getSelector('expiration_yr')).val(),
                    cardCode: $(context.getSelector('cc_cid')).val()
                },
                authData: {
                    clientKey: this.config.clientKey,
                    apiLoginID: this.config.apiLoginID
                }
            };

            this.accept.dispatchData(paymentData, function (response) {
                if (response.messages.resultCode === "Error") {
                    var messages = $.map(response.messages.message, function() {
                        return this.code + ": " + this.text;
                    });

                    state.reject(messages.join('. '));
                } else {
                    context.addAdditionalData('opaqueDescriptor', response.opaqueData.dataDescriptor);
                    context.addAdditionalData('opaqueValue', response.opaqueData.dataValue);
                    state.resolve();
                }
            });

            return state.promise();
        }
    };
});