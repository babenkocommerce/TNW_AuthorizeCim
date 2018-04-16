/*
 * Copyright © 2018 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
define([
    'jquery',
    'TNW_AuthorizeCim/js/view/payment/accept',
    'TNW_AuthorizeCim/js/view/payment/3d-secure'
], function ($, accept, verify3DSecure) {
    'use strict';

    return {
        validators: [],

        /**
         * Get payment config
         * @returns {Object}
         */
        getConfig: function () {
            return window.checkoutConfig.payment;
        },

        /**
         * Init list of validators
         */
        initialize: function () {
            var config = this.getConfig();

            accept.setConfig(config[accept.getCode()]);
            this.add(accept);

            if (config[verify3DSecure.getCode()].enabled) {
                verify3DSecure.setConfig(config[verify3DSecure.getCode()]);
                this.add(verify3DSecure);
            }
        },

        /**
         * Load list of validators
         * @param {Function} callback
         */
        load: function(callback) {
            var self = this,
                deps = $.map(this.validators, function (validator) {
                    return validator.config.sdkUrl;
                });

            require(deps, function () {
                $.each(self.validators, function (key, validator) {
                    validator.load();
                });

                callback();
            });
        },

        /**
         * Add new validator
         * @param {Object} validator
         */
        add: function (validator) {
            this.validators.push(validator);
        },

        /**
         * Run pull of validators
         * @param {Object} context
         */
        validate: function (context) {
            var self = this,
                deferred;

            // no available validators
            if (!self.validators.length) {
                return $.when();
            }

            // get list of deferred validators
            deferred = $.map(self.validators, function (current) {
                return current.validate(context);
            });

            return $.when.apply($, deferred);
        }
    };
});
