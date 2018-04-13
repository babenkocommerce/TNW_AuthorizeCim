/*
 * Copyright © 2018 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
define([
    'jquery',
    'Magento_Checkout/js/model/quote',
    'mage/translate'
], function ($, $t, quote) {
    'use strict';

    return {
        config: null,
        cardinal: null,

        /**
         * Set 3d secure config
         * @param {Object} config
         */
        setConfig: function (config) {
            this.config = config;
            this.config.thresholdAmount = parseFloat(config.thresholdAmount);
        },

        /**
         * Get code
         * @returns {String}
         */
        getCode: function () {
            return 'verify_authorize';
        },

        /**
         * Load sdk
         */
        load: function() {
            this.cardinal = window.Cardinal;
        },

        /**
         * Validate Braintree payment nonce
         * @param {Object} context
         * @returns {Object}
         */
        validate: function (context) {
            var state = $.Deferred(),
                totalAmount = quote.totals()['base_grand_total'],
                billingAddress = quote.billingAddress();

            if (!this.isAmountAvailable(totalAmount) || !this.isCountryAvailable(billingAddress.countryId)) {
                state.resolve();

                return state.promise();
            }

            //state.resolve();
            //state.reject(error.message);
            state.reject($t('Please try again with another form of payment.'));

            return state.promise();
        },

        /**
         * Check minimal amount for 3d secure activation
         * @param {Number} amount
         * @returns {Boolean}
         */
        isAmountAvailable: function (amount) {
            amount = parseFloat(amount);

            return amount >= this.config.thresholdAmount;
        },

        /**
         * Check if current country is available for 3d secure
         * @param {String} countryId
         * @returns {Boolean}
         */
        isCountryAvailable: function (countryId) {
            var key,
                specificCountries = this.config.specificCountries;

            // all countries are available
            if (!specificCountries.length) {
                return true;
            }

            for (key in specificCountries) {
                if (countryId === specificCountries[key]) {
                    return true;
                }
            }

            return false;
        }
    };
});
