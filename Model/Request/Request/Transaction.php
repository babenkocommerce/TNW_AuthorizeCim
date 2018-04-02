<?php
/**
 * Copyright © 2018 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */

namespace TNW\AuthorizeCim\Model\Request\Request;

use Magento\Framework\Exception\PaymentException;
use TNW\AuthorizeCim\Gateway\Request\AddressDataBuilder;
use TNW\AuthorizeCim\Gateway\Request\CustomerDataBuilder;
use TNW\AuthorizeCim\Gateway\Request\PaymentDataBuilder;
use TNW\AuthorizeCim\Gateway\Request\CaptureDataBuilder;
use TNW\AuthorizeCim\Model\Request\RequestAbstract;
use TNW\AuthorizeCim\Model\Request\Data\AddressData;
use TNW\AuthorizeCim\Model\Request\Data\AddressData\BillingAddressData;
use TNW\AuthorizeCim\Model\Request\Data\CustomerData;
use TNW\AuthorizeCim\Model\Request\Data\PaymentData;
use TNW\AuthorizeCim\Model\Request\Data\CaptureData;
use TNW\AuthorizeCim\Model\Response\ResponseData;

class Transaction extends RequestAbstract
{
    /** Name for method create transaction request */
    const METHOD_NAME_CREATE_TRANSACTION_REQUEST = 'createTransactionRequest';

    /** Payment transaction request key */
    const PAYMENT_TRANSACTION_REQUEST_FIELD = 'transactionRequest';

    /** Payment transaction type key */
    const PAYMENT_TRANSACTION_TYPE_FIELD = 'transactionType';

    /** Payment transaction amount key */
    const PAYMENT_AMOUNT_FIELD = 'amount';

    /** Payment information key */
    const PAYMENT_PROFILE_FIELD = 'paymentProfile';

    /** Payment profile ID key */
    const PAYMENT_PAYMENT_PROFILE_ID_FIELD = 'paymentProfileId';

    /** Payment order key */
    const PAYMENT_ORDER_FIELD = 'order';

    /** Payment order invoice number fkey */
    const PAYMENT_INVOICE_NUMBER_FIELD = 'invoiceNumber';

    /** Payment key */
    const PAYMENT_PAYMENT_FIELD = 'payment';

    /** Payment credit card (array) field */
    const PAYMENT_CREDIT_CARD_FIELD = 'creditCard';

    /** Payment credit card number */
    const PAYMENT_CREDIT_CARD_NUMBER = 'cardNumber';

    /** Payment credit card expiration date */
    const PAYMENT_CREDIT_CARD_EXPIRATION = 'expirationDate';

    /** Payment credit card code */
    const PAYMENT_CREDIT_CARD_CODE = 'cardCode';

    /** Payment customer key */
    const PAYMENT_CUSTOMER_FIELD = 'customer';

    /** Payment customer email key */
    const PAYMENT_CUSTOMER_EMAIL_FIELD = 'email';

    /** Payment billing address key */
    const PAYMENT_BILLING_FIELD = 'billTo';

    /** Payment shipping address key */
    const PAYMENT_SHIPPING_FIELD = 'shipTo';

    /** Payment authorize transaction ID key */
    const PAYMENT_TRANSACTION_ID = 'refTransId';

    /**
     * Sale order transaction request
     *
     * @param array $buildData
     * @return array
     * @throws PaymentException
     */
    public function createTransactionRequest(array $buildData)
    {
        $result = null;
        /** @var PaymentData $paymentData */
        $paymentData = $buildData[PaymentDataBuilder::PAYMENT_BUILD_KEY];

        if (!$paymentData->getTransactionRequest()->getCustomerProfileId()) {
            throw new PaymentException(__('Build data is\'t valid: customer profile ID incorrect.'));
        }

        $requestArray = [
            self::METHOD_NAME_CREATE_TRANSACTION_REQUEST => array_merge($this->getMerchantAuthentication(), [
                self::PAYMENT_TRANSACTION_REQUEST_FIELD => [
                    self::PAYMENT_TRANSACTION_TYPE_FIELD => $paymentData->getTransactionRequest()->getTransactionType(),
                    self::PAYMENT_AMOUNT_FIELD => $paymentData->getTransactionRequest()->getAmount(),
                    CustomerProfile::CUSTOMER_PROFILE_FIELD => [
                        CustomerProfile::CUSTOMER_PROFILE_ID_FIELD => $paymentData->getTransactionRequest()
                            ->getCustomerProfileId(),
                        self::PAYMENT_PROFILE_FIELD => [
                            self::PAYMENT_PAYMENT_PROFILE_ID_FIELD => $paymentData->getTransactionRequest()
                                ->getCustomerPaymentProfileId()
                        ]
                    ],
                    self::PAYMENT_ORDER_FIELD => [
                        self::PAYMENT_INVOICE_NUMBER_FIELD => $paymentData->getTransactionRequest()
                            ->getOrder()->getInvoiceNumber(),
                    ],
                ]
            ])
        ];
        $requestResult = $this->postRequest($requestArray);

        if ($requestResult->getResponseCode() !== ResponseData::SUCCESS_CODE) {
            throw new PaymentException(__('Exception: %1', $requestResult->getResponseText()));
        }

        return $requestResult->getResponseData();
    }

    /**
     * Capture transaction request
     *
     * @param array $buildData
     * @return array
     * @throws PaymentException
     */
    public function captureTransaction(array $buildData)
    {
        /** @var CaptureData $captureData */
        $captureData = $buildData[CaptureDataBuilder::CAPTURE_BUILD_KEY];
        $requestArray = [
            self::METHOD_NAME_CREATE_TRANSACTION_REQUEST => array_merge($this->getMerchantAuthentication(), [
                self::PAYMENT_TRANSACTION_REQUEST_FIELD => [
                    self::PAYMENT_TRANSACTION_TYPE_FIELD => $captureData->getTransactionType(),
                    self::PAYMENT_AMOUNT_FIELD => $captureData->getAmount(),
                    self::PAYMENT_TRANSACTION_ID => $captureData->getTransactionId()
                ]
            ])
        ];
        $requestResult = $this->postRequest($requestArray);

        if ($requestResult->getResponseCode() !== ResponseData::SUCCESS_CODE) {
            throw new PaymentException(__('Exception: %1', $requestResult->getResponseText()));
        }

        return $requestResult->getResponseData();
    }

    /**
     * Use this method to authorize a credit card payment.
     *
     * @param array $buildData
     * @return array
     * @throws PaymentException
     */
    public function beginTransaction(array $buildData)
    {
        $result = null;
        $requestArray = $this->getStandardTransactionBody($buildData);
        $requestResult = $this->postRequest($requestArray);

        if ($requestResult->getResponseCode() !== ResponseData::SUCCESS_CODE) {
            throw new PaymentException(__('Exception: %1', $requestResult->getResponseText()));
        }

        return $requestResult->getResponseData();
    }

    /**
     * Standard transaction request array
     *
     * @param array $buildData
     * @return array
     */
    private function getStandardTransactionBody(array $buildData)
    {
        /** @var PaymentData $paymentData */
        $paymentData = $buildData[PaymentDataBuilder::PAYMENT_BUILD_KEY];
        /** @var CustomerData $customerData */
        $customerData = $buildData[CustomerDataBuilder::CUSTOMER_BUILD_KEY];
        /** @var AddressData $addressData */
        $addressData = $buildData[AddressDataBuilder::ADDRESS_BUILD_KEY];

        return [
            self::METHOD_NAME_CREATE_TRANSACTION_REQUEST => array_merge($this->getMerchantAuthentication(), [
                self::PAYMENT_TRANSACTION_REQUEST_FIELD => [
                    self::PAYMENT_TRANSACTION_TYPE_FIELD => $paymentData->getTransactionRequest()->getTransactionType(),
                    self::PAYMENT_AMOUNT_FIELD => $paymentData->getTransactionRequest()->getAmount(),
                    self::PAYMENT_PAYMENT_FIELD => [
                        self::PAYMENT_CREDIT_CARD_FIELD => [
                            self::PAYMENT_CREDIT_CARD_NUMBER => $paymentData->getPaymentInfo()->getCardNumber(),
                            self::PAYMENT_CREDIT_CARD_EXPIRATION => $this->getTransactionExpirationDate($paymentData),
                            self::PAYMENT_CREDIT_CARD_CODE => $paymentData->getPaymentInfo()->getCardCode(),
                        ]
                    ],
                    self::PAYMENT_ORDER_FIELD => [
                        self::PAYMENT_INVOICE_NUMBER_FIELD => $paymentData->getTransactionRequest()
                            ->getOrder()->getInvoiceNumber(),
                    ],
                    self::PAYMENT_CUSTOMER_FIELD => [
                        self::PAYMENT_CUSTOMER_EMAIL_FIELD => $customerData->getCustomerEmail(),
                    ],
                    self::PAYMENT_BILLING_FIELD => $addressData->getBillingAddress()->toTransactionArray(),
                    self::PAYMENT_SHIPPING_FIELD => $addressData->getShippingAddress()->toTransactionArray()
                ]
            ])
        ];
    }

    /**
     * Get expiration date for transaction
     *
     * @param PaymentData $paymentData
     * @return string
     */
    private function getTransactionExpirationDate(PaymentData $paymentData)
    {
        $expirationMonth = $paymentData->getPaymentInfo()->getExpMonth();

        if ($expirationMonth < 10) {
            $expirationMonth = 0 . $expirationMonth;
        }

        return $paymentData->getPaymentInfo()->getExpYear() . '-' . $expirationMonth;
    }
}
