/*
 * Copyright © 2018 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
define([
    'jquery',
    'mage/translate',
    'Magento_Checkout/js/model/quote'
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
            this.cardinal.configure({ logging: { level: 'verbose' } });
        },

        /**
         * @param {Object} context
         * @returns {Object}
         */
        validate: function (context) {
            var self = this,
                state = $.Deferred(),
                orderNumber = 'ORDER-' + quote.getQuoteId(),
                totalAmount = parseFloat(quote.totals()['base_grand_total']).toFixed(2).replace('.', ''),
                currencyCode = quote.totals()['base_currency_code'],
                billingAddress = quote.billingAddress();

            if (!this.isAmountAvailable(totalAmount) || !this.isCountryAvailable(billingAddress.countryId)) {
                state.resolve();
                return state.promise();
            }

            $.ajax({
                method: 'get',
                url: this.config.jwtUrl,
                data: {
                    'order_details': {
                        'OrderNumber': orderNumber,
                        'Amount': totalAmount,
                        'CurrencyCode': currencyCode
                    }
                },
                dataType: 'json'
            }).done(function (responseData) {
                try {
                    self.cardinal.setup('init', {jwt: responseData.jwt});

                    self.cardinal.on('payments.validated', function (data, jwt) {
                        switch(data.ActionCode) {
                            case "SUCCESS":
                                context.addAdditionalData('ECIFlag', data.Payment.ExtendedData.ECIFlag);
                                context.addAdditionalData('CAVV', data.Payment.ExtendedData.CAVV);
                                state.resolve();
                                break;

                            case "NOACTION":
                                state.resolve();
                                break;

                            case "FAILURE":
                            case "ERROR":
                                state.reject(data.ErrorDescription);
                                break;
                        }
                    });

                    self.cardinal.start('cca', {
                        OrderDetails: {
                            OrderNumber: orderNumber
                        },
                        Consumer: {
                            Account: {
                                AccountNumber: $(context.getSelector('cc_number')).val().replace(/\D/g, ''),
                                ExpirationMonth: $(context.getSelector('expiration')).val(),
                                ExpirationYear: $(context.getSelector('expiration_yr')).val(),
                                //CardCode: $(context.getSelector('cc_cid')).val()
                            }
                        }
                    });
                } catch (error) {
                    state.reject(error);
                }
            }).fail(function (xhr, ajaxError) {
                state.reject($t('Error getting JWT'));
            });

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
