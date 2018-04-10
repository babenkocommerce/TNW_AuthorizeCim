<?php
/**
 * Copyright © 2018 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */

namespace TNW\AuthorizeCim\Gateway\Config;

use Magento\Payment\Gateway\Config\Config as MagentoGatewayConfig;
use TNW\AuthorizeCim\Model\Adminhtml\Source\Environment;

/**
 * Config for payment config values
 */
class Config extends MagentoGatewayConfig
{
    /** is method active field name */
    const ACTIVE = 'active';
    /** is need use CCV field name */
    const USE_CCV = 'useccv';
    /** API login id field name */
    const LOGIN = 'login';
    /** API transaction key field name */
    const TRANSACTION_KEY = 'trans_key';
    /** API client key field name */
    const CLIENT_KEY = 'client_key';
    /** payment mode field name */
    const ENVIRONMENT = 'environment';
    /** currency code field name */
    const CURRENCY = 'currency';
    /** validation mode field name */
    const VALIDATION_MODE = 'validation_mode';
    /** js sdk url */
    const SDK_URL = 'sdk_url';
    /** js sdk url */
    const SDK_URL_TEST = 'sdk_url_test_mode';
    /** cc types mapper */
    const CC_TYPES_MAPPER = 'cctypes_mapper';

    /**
     * Can method is active
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isActive($storeId = null)
    {
        return (bool)$this->getValue(self::ACTIVE, $storeId);
    }

    /**
     * Is need enter CVV code (for vault)
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isCcvEnabled($storeId = null)
    {
        return (bool)$this->getValue(self::USE_CCV, $storeId);
    }

    /**
     * Get API login
     *
     * @param int|null $storeId
     * @return string|null
     */
    public function getApiLoginId($storeId = null)
    {
        return $this->getValue(self::LOGIN, $storeId);
    }

    /**
     * Get API transaction key
     *
     * @param int|null $storeId
     * @return string|null
     */
    public function getTransactionKey($storeId = null)
    {
        return $this->getValue(self::TRANSACTION_KEY, $storeId);
    }

    /**
     * Get API client key
     *
     * @param int|null $storeId
     * @return null|string
     */
    public function getClientKey($storeId = null)
    {
        return $this->getValue(self::CLIENT_KEY, $storeId);
    }

    /**
     * @param int|null $storeId
     * @return string
     */
    public function getEnvironment($storeId = null)
    {
        return $this->getValue(self::ENVIRONMENT, $storeId);
    }

    /**
     * Get in what mode is the payment method (test or live modes)
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isSandboxMode($storeId = null)
    {
        return $this->getEnvironment($storeId) == Environment::ENVIRONMENT_SANDBOX;
    }

    /**
     * Get validation mode
     *
     * @param int|null $storeId
     * @return string
     */
    public function getValidationMode($storeId = null)
    {
        return $this->getValue(self::VALIDATION_MODE, $storeId);
    }

    /**
     * @param int|null $storeId
     * @return string
     */
    public function getSdkUrl($storeId = null)
    {
        if ($this->isSandboxMode($storeId)) {
            return $this->getValue(self::SDK_URL_TEST, $storeId);
        }

        return $this->getValue(self::SDK_URL, $storeId);
    }

    /**
     * Retrieve mapper between Magento and Authorize.Net card types
     *
     * @return array
     */
    public function getCcTypesMapper()
    {
        $result = json_decode(
            $this->getValue(self::CC_TYPES_MAPPER),
            true
        );

        return is_array($result) ? $result : [];
    }
}
