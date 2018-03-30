<?php
/**
 * Copyright © 2018 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */

namespace TNW\AuthorizeCim\Gateway\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Payment\Gateway\Config\Config as MagentoGatewayConfig;

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
    const TEST = 'test';
    /** currency code field name */
    const CURRENCY = 'currency';
    /** validation mode field name */
    const VALIDATION_MODE = 'validation_mode';

    /**
     * Can method is active
     *
     * @return bool
     */
    public function isActive()
    {
        return (bool)$this->getValue(self::ACTIVE);
    }


    /**
     * Is need enter CVV code (for vault)
     *
     * @return bool
     */
    public function isCcvEnabled()
    {
        return (bool)$this->getValue(self::USE_CCV);
    }

    /**
     * Get API login
     *
     * @return string|null
     */
    public function getApiLoginId()
    {
        return $this->getValue(self::LOGIN);
    }

    /**
     * Get API transaction key
     *
     * @return string|null
     */
    public function getTransactionKey()
    {
        return $this->getValue(self::TRANSACTION_KEY);
    }

    /**
     * Get API client key
     *
     * @return null|string
     */
    public function getClientKey()
    {
        return $this->getValue(self::CLIENT_KEY);
    }

    /**
     * Get in what mode is the payment method (test or live modes)
     *
     * @return bool
     */
    public function isTest()
    {
        return (bool)$this->getValue(self::TEST);
    }

    /**
     * Get currency code
     *
     * @return string
     */
    public function getCurrency()
    {
        return $this->getValue(self::CURRENCY);
    }

    /**
     * Get validation mode
     *
     * @return string
     */
    public function getValidationMode()
    {
        return $this->getValue(self::VALIDATION_MODE);
    }
}
