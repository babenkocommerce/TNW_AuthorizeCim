<?php
/**
 * Copyright © 2018 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */

namespace TNW\AuthorizeCim\Model\Request\Request;

use Magento\Framework\Exception\PaymentException;
use TNW\AuthorizeCim\Gateway\Request\CustomerDataBuilder;
use TNW\AuthorizeCim\Model\Request\Data\CustomerData;
use TNW\AuthorizeCim\Model\Request\RequestAbstract;
use TNW\AuthorizeCim\Model\Response\ResponseData;

/**
 * Customer profile API methods
 */
class CustomerProfile extends RequestAbstract
{
    /** Name for method get customer profile */
    const METHOD_NAME_GET_CUSTOMER_PROFILE = 'getCustomerProfileRequest';

    /** Name for method create customer profile */
    const METHOD_NAME_CREATE_CUSTOMER_PROFILE = 'createCustomerProfileRequest';

    /** Magento customer email field */
    const EMAIL_FIELD = 'email';

    /** Customer profile field */
    const CUSTOMER_PROFILE_FIELD = 'profile';

    /** Customer profile ID field */
    const CUSTOMER_PROFILE_ID_FIELD = 'customerProfileId';

    /**
     * Return existing customer profile or null if not exist
     *
     * @param array $buildData
     * @return array|null
     * @throws PaymentException
     */
    public function getCustomerProfile(array $buildData)
    {
        /** @var CustomerData $customerData */
        $customerData = $buildData[CustomerDataBuilder::CUSTOMER_BUILD_KEY];
        $result = null;
        $requestArray = [
            self::METHOD_NAME_GET_CUSTOMER_PROFILE => array_merge($this->getMerchantAuthentication(), [
                self::EMAIL_FIELD => $customerData->getCustomerEmail(),
            ])
        ];

        $requestResult = $this->postRequest($requestArray);

        if ($requestResult->getResponseCode() !== ResponseData::SUCCESS_CODE &&
            $requestResult->getResponseCode() !== ResponseData::RECORD_NOT_FOUND_CODE
        ) {
            throw new PaymentException(__('Exception: %1', $requestResult->getResponseText()));
        } else {
            $result = $requestResult->getResponseData();
        }

        return $result;
    }

    /**
     * Create customer profile
     *
     * @param array $buildData
     * @return array
     * @throws PaymentException
     */
    public function createCustomerProfile(array $buildData)
    {
        /** @var CustomerData $customerData */
        $customerData = $buildData[CustomerDataBuilder::CUSTOMER_BUILD_KEY];
        $requestArray = [
            self::METHOD_NAME_GET_CUSTOMER_PROFILE => array_merge($this->getMerchantAuthentication(), [
                self::EMAIL_FIELD => $customerData->getCustomerEmail(),
            ])
        ];

        $requestResult = $this->postRequest($requestArray);

        if ($requestResult->getResponseCode() !== ResponseData::SUCCESS_CODE) {
            throw new PaymentException(__('Exception: %1', $requestResult->getResponseText()));
        }

        return $requestResult->getResponseData();
    }
}
