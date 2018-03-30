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
use TNW\AuthorizeCim\Model\Request\Data\AddressData;
use TNW\AuthorizeCim\Model\Request\Data\AddressData\BillingAddressData;
use TNW\AuthorizeCim\Model\Request\Data\CustomerData;
use TNW\AuthorizeCim\Model\Request\Data\PaymentData;
use TNW\AuthorizeCim\Model\Request\RequestAbstract;
use TNW\AuthorizeCim\Model\Response\ResponseData;

/**
 * Payment profile API methods
 */
class PaymentProfile extends RequestAbstract
{
    /** Name for method create customer payment profile */
    const METHOD_NAME_CREATE_PAYMENT_PROFILE = 'createCustomerPaymentProfileRequest';

    /** Name for method get customer payment profile list */
    const METHOD_NAME_GET_CUSTOMER_PAYMENT_LIST = 'getCustomerPaymentProfileListRequest';

    /** Name for method create transaction request */
    const METHOD_NAME_CREATE_TRANSACTION_REQUEST = 'createTransactionRequest';

    /** Name for method get customer profile */
    const METHOD_NAME_GET_CUSTOMER_PAYMENT = 'getCustomerPaymentProfileRequest';

    /** Payment information key */
    const PAYMENT_PROFILE_FIELD = 'paymentProfile';

    /** Payment transaction request key */
    const PAYMENT_TRANSACTION_REQUEST_FIELD = 'transactionRequest';

    /** Payment transaction type key */
    const PAYMENT_TRANSACTION_TYPE_FIELD = 'transactionType';

    /** Payment transaction amount key */
    const PAYMENT_AMOUNT_FIELD = 'amount';

    /** Customer payment profile ID key */
    const PAYMENT_CUSTOMER_PAYMENT_PROFILE_ID_FIELD = 'customerPaymentProfileId';

    /** Payment unmask expiration key */
    const PAYMENT_UNMASK_EXPIRATION_FIELD = 'unmaskExpirationDate';

    /** Payment key */
    const PAYMENT_PAYMENT_FIELD = 'payment';

    /** Payment profile ID key */
    const PAYMENT_PAYMENT_PROFILE_ID_FIELD = 'paymentProfileId';

    /** Payment opaque data key */
    const PAYMENT_OPAQUE_FIELD = 'opaqueData';

    /** Payment opaque data descriptor key */
    const PAYMENT_OPAQUE_DATA_DESCRIPTOR_FIELD = 'dataDescriptor';

    /** Payment opaque data value key */
    const PAYMENT_OPAQUE_DATA_VALUE_FIELD = 'dataValue';

    /** Payment customer type key */
    const PAYMENT_CUSTOMER_TYPE_FIELD = 'customerType';

    /** Payment is ned create profile key */
    const PAYMENT_CREATE_PROFILE_FIELD = 'createProfile';

    /** Payment solution key */
    const PAYMENT_SOLUTION_FIELD = 'solution';

    /** Payment solution ID key */
    const PAYMENT_SOLUTION_ID_FIELD = 'id';

    /** Is payment default key */
    const PAYMENT_DEFAULT_PAYMENT_FIELD = 'defaultPaymentProfile';

    /** Payment order key */
    const PAYMENT_ORDER_FIELD = 'order';

    /** Payment bill to key */
    const PAYMENT_BILL_TO_FIELD = 'billTo';

    /** Payment billing first name key */
    const PAYMENT_BILL_TO_FIRSTNAME_FIELD_FIELD = 'firstName';

    /** Payment billing last name key */
    const PAYMENT_BILL_TO_LASTNAME_FIELD = 'lastName';

    /** Payment billing company key */
    const PAYMENT_BILL_TO_COMPANY_FIELD = 'company';

    /** Payment billing address key */
    const PAYMENT_BILL_TO_ADDRESS_FIELD = 'address';

    /** Payment billing city key */
    const PAYMENT_BILL_TO_CITY_FIELD = 'city';

    /** Payment billing state key */
    const PAYMENT_BILL_TO_STATE_FIELD = 'state';

    /** Payment billing ZIP key */
    const PAYMENT_BILL_TO_ZIP_FIELD = 'zip';

    /** Payment billing country key */
    const PAYMENT_BILL_TO_COUNTRY_FIELD = 'country';

    /** Payment billing phone number key */
    const PAYMENT_BILL_TO_PHONE_NUMBER_FIELD = 'phoneNumber';

    /** Payment search type key */
    const PAYMENT_SEARCH_TYPE_FIELD = 'searchType';

    /** Payment expiration month key */
    const PAYMENT_MONTH_FIELD = 'month';

    /** Payment order invoice number fkey */
    const PAYMENT_INVOICE_NUMBER_FIELD = 'invoiceNumber';

    /** Payment tax field */
    const PAYMENT_TAX_FIELD = 'tax';

    /** Payment order number key */
    const PAYMENT_PO_NUMBER_FIELD = 'poNumber';

    /**
     * Create customer payment profile
     *
     * @param array $buildData
     * @return array|null
     * @throws PaymentException
     */
    public function createPaymentProfile(array $buildData)
    {
        $result = null;
        /** @var CustomerData $customerData */
        $customerData = $buildData[CustomerDataBuilder::CUSTOMER_BUILD_KEY];
        /** @var PaymentData $paymentData */
        $paymentData = $buildData[PaymentDataBuilder::PAYMENT_BUILD_KEY];
        /** @var AddressData $address */
        $address = $buildData[AddressDataBuilder::ADDRESS_BUILD_KEY];

        if (!$customerData->getCustomerProfileId()) {
            throw new PaymentException(__('Build data is\'t valid: customer profile ID incorrect.'));
        }

        $requestArray = [
            self::METHOD_NAME_CREATE_PAYMENT_PROFILE => array_merge($this->getMerchantAuthentication(), [
                CustomerProfile::CUSTOMER_PROFILE_ID_FIELD => $customerData->getCustomerProfileId(),
                self::PAYMENT_PROFILE_FIELD => [
                    self::PAYMENT_CUSTOMER_TYPE_FIELD => $paymentData->getPaymentProfile()->getCustomerType(),
                    self::PAYMENT_BILL_TO_FIELD => $this->addressToArray($address->getBillingAddress()),
                    self::PAYMENT_PAYMENT_FIELD => [
                        self::PAYMENT_OPAQUE_FIELD => [
                            self::PAYMENT_OPAQUE_DATA_DESCRIPTOR_FIELD => $paymentData->getPaymentProfile()
                                ->getOpaque()->getDataDescriptor(),
                            self::PAYMENT_OPAQUE_DATA_VALUE_FIELD => $paymentData->getPaymentProfile()
                                ->getOpaque()->getDataValue()
                        ]
                    ],
                    self::PAYMENT_DEFAULT_PAYMENT_FIELD => $paymentData->getPaymentProfile()
                        ->getIsDefaultPaymentProfile()
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
     * Return payment profile list
     *
     * @param array $buildData
     * @return array
     * @throws PaymentException
     */
    public function getCustomerPaymentProfileList(array $buildData)
    {
        /** @var PaymentData $paymentData */
        $paymentData = $buildData[PaymentDataBuilder::PAYMENT_BUILD_KEY];
        $result = null;

        if (!$paymentData->getPaymentInfo()->getExpMonth() ||
            !$paymentData->getPaymentInfo()->getExpYear()
        ) {
            throw new PaymentException(__('Build data is\'t valid: CC data is not set.'));
        }
        $requestArray = [
            self::METHOD_NAME_GET_CUSTOMER_PAYMENT_LIST => array_merge($this->getMerchantAuthentication(), [
                self::PAYMENT_SEARCH_TYPE_FIELD => 'cardsExpiringInMonth',
                self::PAYMENT_MONTH_FIELD => $this->getSearchExpirationDate($paymentData)
            ])
        ];

        $requestResult = $this->postRequest($requestArray);

        if ($requestResult->getResponseCode() !== ResponseData::SUCCESS_CODE) {
            throw new PaymentException(__('Exception: %1', $requestResult->getResponseText()));
        }

        return $requestResult->getResponseData();
    }

    /**
     * Return payment profile by payment and profile ids
     *
     * @param string $profileId
     * @param string $paymentProfileId
     * @return null|array
     * @throws PaymentException
     */
    public function getCustomerPaymentProfile($profileId, $paymentProfileId)
    {
        if (!$profileId || !$paymentProfileId) {
            throw new PaymentException(__('Build data is\'t valid: payment or profile ID is\'t set'));
        }

        $requestArray = [
            self::METHOD_NAME_GET_CUSTOMER_PAYMENT => array_merge($this->getMerchantAuthentication(), [
                CustomerProfile::CUSTOMER_PROFILE_ID_FIELD => $profileId,
                self::PAYMENT_CUSTOMER_PAYMENT_PROFILE_ID_FIELD => $paymentProfileId,
                self::PAYMENT_UNMASK_EXPIRATION_FIELD => true
            ])
        ];
        $requestResult = $this->postRequest($requestArray);

        if ($requestResult->getResponseCode() === ResponseData::SUCCESS_CODE) {
            $result = $requestResult->getResponseData();
        } else {
            if ($requestResult->getResponseCode() === ResponseData::RECORD_NOT_FOUND_CODE) {
                $result = null;
            } else {
                throw new PaymentException(__('Exception: %1', $requestResult->getResponseText()));
            }
        }

        return $result;
    }

    /**
     * Convert billing address data to array
     *
     * @param BillingAddressData $address
     * @return array
     */
    protected function addressToArray(BillingAddressData $address)
    {
        $result = [];

        if ($address->getFirstName()) {
            $result[self::PAYMENT_BILL_TO_FIRSTNAME_FIELD_FIELD] = $address->getFirstName();
        }

        if ($address->getLastName()) {
            $result[self::PAYMENT_BILL_TO_LASTNAME_FIELD] = $address->getLastName();
        }

        if ($address->getCompany()) {
            $result[self::PAYMENT_BILL_TO_COMPANY_FIELD] = $address->getCompany();
        }

        if ($address->getAddress()) {
            $result[self::PAYMENT_BILL_TO_ADDRESS_FIELD] = $address->getAddress();
        }

        if ($address->getCity()) {
            $result[self::PAYMENT_BILL_TO_CITY_FIELD] = $address->getCity();
        }

        if ($address->getState()) {
            $result[self::PAYMENT_BILL_TO_STATE_FIELD] = $address->getState();
        }

        if ($address->getZip()) {
            $result[self::PAYMENT_BILL_TO_ZIP_FIELD] = $address->getZip();
        }

        if ($address->getCountry()) {
            $result[self::PAYMENT_BILL_TO_COUNTRY_FIELD] = $address->getCountry();
        }

        if ($address->getPhoneNumber()) {
            $result[self::PAYMENT_BILL_TO_PHONE_NUMBER_FIELD] = $address->getPhoneNumber();
        }

        return $result;
    }

    /**
     * Get expiration date for search
     *
     * @param PaymentData $paymentData
     * @return string
     */
    private function getSearchExpirationDate(PaymentData $paymentData)
    {
        $expirationMonth = $paymentData->getPaymentInfo()->getExpMonth();

        if ($expirationMonth < 10) {
            $expirationMonth = 0 . $expirationMonth;
        }

        return $paymentData->getPaymentInfo()->getExpYear() . '-' . $expirationMonth;
    }
}
