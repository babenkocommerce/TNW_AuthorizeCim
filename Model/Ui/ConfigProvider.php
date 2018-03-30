<?php
/**
 * Copyright © 2017 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */

namespace TNW\AuthorizeCim\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class ConfigProvider implements ConfigProviderInterface
{
    /** Payment code */
    const CODE = 'tnw_authorize_cim';
    /** Vault payment code */
    const VAULT_CODE = 'tnw_authorize_cim_vault';

    /** @var ScopeConfigInterface */
    private $config;

    /**
     * @param ScopeConfigInterface $config
     */
    public function __construct(
        ScopeConfigInterface $config
    ) {
        $this->config = $config;
    }

    /**
     * Get payment config array for payment method in checkout
     *
     * @return array
     */
    public function getConfig()
    {
        return [
            'payment' => [
                self::CODE => [
                    'clientKey'     => $this->getClientKey(),
                    'apiLoginId'    => $this->getApiLoginId(),
                    'useccv'        => $this->getUseCcv(),
                    'vaultCode'     => self::VAULT_CODE,
                    'test'          => $this->getIsTest(),
                ]
            ]
        ];
    }

    /**
     * Get client key config
     *
     * @return null|string
     */
    private function getClientKey()
    {
        return $this->getConfigByKey('client_key');
    }

    /**
     * Get API login ID config
     *
     * @return null|string
     */
    private function getApiLoginId()
    {
        return $this->getConfigByKey('login');
    }

    /**
     * Get use is CVV enabled config
     *
     * @return null|string
     */
    private function getUseCcv()
    {
        return $this->getConfigByKey('useccv');
    }

    /**
     * Get API in test mode
     *
     * @return null|string
     */
    private function getIsTest()
    {
        return $this->getConfigByKey('test');
    }

    /**
     * Retrieve config by key
     *
     * @param string $key
     * @return string|null
     */
    private function getConfigByKey($key)
    {
        $paymentCode = self::CODE;

        return $this->config->getValue(
            "payment/{$paymentCode}/{$key}",
            ScopeInterface::SCOPE_STORE
        );
    }
}
