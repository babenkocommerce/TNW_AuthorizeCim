/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'mage/template',
    'Magento_Ui/js/modal/alert',
    'jquery/ui',
    'mage/translate'
], function ($, mageTemplate, alert) {
    'use strict';

    $.widget('authorizecim.payment', {
        options: {
            continueSelector: '#payment-continue',
            methodsContainer: '#payment-methods',
            minBalance: 0,
            tmpl: '<input id="hidden-free" type="hidden" name="payment[method]" value="free">',
            config:'',
            paymentconfig:''
        },


        /** @inheritdoc */
        _create: function () {

              
           
            this.element.find('dd [name^="payment["]').prop('disabled', true).end()
                .on('click', this.options.continueSelector, $.proxy(this._submitHandler, this))
                .on('updateCheckoutPrice', $.proxy(function (event, data) {
                    //updating the checkoutPrice
                    if (data.price) {
                        this.options.checkoutPrice += data.price;
                    }

                    //updating total price
                    if (data.totalPrice) {
                        data.totalPrice = this.options.checkoutPrice;
                    }

                    if (this.options.checkoutPrice <= this.options.minBalance) {
                        // Add free input field, hide and disable unchecked
                        // checkbox payment method and all radio button payment methods
                        this._disablePaymentMethods();
                    } else {
                        // Remove free input field, show all payment method
                        this._enablePaymentMethods();
                    }
                }, this))
                .on('click', 'dt input:radio', $.proxy(this._paymentMethodHandler, this));

            if (this.options.checkoutPrice < this.options.minBalance) {
                this._disablePaymentMethods();
            } else {
                this._enablePaymentMethods();
                
            }

            
            if(this.options.paymentconfig){
               
                this.options.config = JSON.stringify(this.options.paymentconfig);
                //this.options.config = this.options.paymentconfig;
                this._loadScript(this.options.paymentconfig);
                $('#tnw_authorize_cim_config').val(this.options.config);
            }
            
        },

        /**
         * Display payment details when payment method radio button is checked
         * @private
         * @param {EventObject} e
         */
        _paymentMethodHandler: function (e) {
          
            var element = $(e.target),
                parentsDl = element.closest('dl');

            parentsDl.find('dt input:radio').prop('checked', false);
            parentsDl.find('dd').addClass('no-display').end()
                .find('.items').hide()
                .find('[name^="payment["]').prop('disabled', true);
            element.prop('checked', true).parent()
                .next('dd').removeClass('no-display')
                .find('.items').show().find('[name^="payment["]').prop('disabled', false);
        },

        /**
         * make sure one payment method is selected
         * @private
         * @return {Boolean}
         */
        _validatePaymentMethod: function () {
           
            var methods = this.element.find('[name^="payment["]'),
                isValid = false;

            if (methods.length === 0) {
                alert({
                    content: $.mage.__('We can\'t complete your order because you don\'t have a payment method set up.')
                });
            } else if (this.options.checkoutPrice <= this.options.minBalance) {
                isValid = true;
            } else if (methods.filter('input:radio:checked').length) {
                isValid = true;
            } else {
                alert({
                    content: $.mage.__('Please choose a payment method.')
                });
            }

            return isValid;
        },

        /**
         * Disable and enable payment methods
         * @private
         */
        _disablePaymentMethods: function () {
            
            var tmpl = mageTemplate(this.options.tmpl, {
                data: {}
            });

            this.element.find('input[name="payment[method]"]').prop('disabled', true).end()
                .find('input[id^="use"][name^="payment[use"]:not(:checked)').prop('disabled', true).parent().hide();
            this.element.find('[name="payment[method]"][value="free"]').parent('dt').remove();
            this.element.find(this.options.methodsContainer).hide().find('[name^="payment["]').prop('disabled', true);

            $(tmpl).appendTo(this.element);
        },

        /**
         * Enable and enable payment methods
         * @private
         */
        _enablePaymentMethods: function () {
           
            this.element.find('input[name="payment[method]"]').prop('disabled', false).end()
                .find('dt input:radio:checked').trigger('click').end()
                .find('input[id^="use"][name^="payment[use"]:not(:checked)').prop('disabled', false).parent().show();
            this.element.find(this.options.methodsContainer).show();
        },

        /**
         * Returns checked payment method.
         *
         * @private
         */
        _getSelectedPaymentMethod: function () {
          
            return this.element.find('input[name=\'payment[method]\']:checked');
        },

         /**
         * Load external Authorize SDK
         */
        _loadScript: function (config) {
            var self = this,
                state = self.scriptLoaded;

            console.log(config.sdkUrl);
            $('body').trigger('processStart');
            require([config.sdkUrl], function () {
                
                self.accept = window.Accept;
                $('body').trigger('processStop');
            });
        },

        getSelector: function (field) {
            this.code = 'tnw_authorize_cim';
            return '#' + this.code + '_' + field;
        },

        /**
         * Validate  before form submit
         * @private
         * @param {EventObject} e
         */
        _submitHandler: function (e) {
            var currentMethod,
                submitButton;

                e.preventDefault();
           
            var paymentconfig = this.element.find('#tnw_authorize_cim_config').val()

            this.options.config = JSON.parse(paymentconfig);

            

            this.$selector = $(this.options.methodsContainer);
            this.$selector.validate().form();
            this.$selector.trigger('afterValidate.beforeSubmit');
            $('body').trigger('processStop');

            // validate parent form
            if (this.$selector.validate().errorList.length) {
                this.element.submit();
                return false;
            }else{
                var paymentData = {
                    cardData: {
                        cardNumber: $(this.getSelector('cc_number')).val().replace(/\D/g, ''),
                        month: $(this.getSelector('expiration')).val(),
                        year: $(this.getSelector('expiration_yr')).val(),
                        cardCode: $(this.getSelector('cc_cid')).val()
                    },
                    authData: {
                        clientKey:  this.options.config.clientKey,
                        apiLoginID:  this.options.config.apiLoginID
                    }
                };

                console.log(paymentData);

                window.Accept.dispatchData(paymentData, function (response) {
                    console.log(response);
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
    
            }

           
           
            

            // if (this._validatePaymentMethod()) {
            //     currentMethod = this._getSelectedPaymentMethod();
            //     submitButton = currentMethod.parent().next('dd').find('button[type=submit]');

               
            //     console.log(paymentData);

            //     if (submitButton.length) {
            //         submitButton.first().trigger('click');
            //     } else {
                    
            //         return false;

            //         this.element.submit();


                    


            //         console.log("test");
            //     }
            // }
        }
    });

    return $.authorizecim.payment;
});
