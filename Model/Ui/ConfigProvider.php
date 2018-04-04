<?php
/**
 * Copyright © 2017 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
namespace TNW\AuthorizeCim\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Store\Model\ScopeInterface;
use TNW\AuthorizeCim\Gateway\Config\Config;

class ConfigProvider implements ConfigProviderInterface
{
    /** Payment code */
    const CODE = 'tnw_authorize_cim';
    /** Vault payment code */
    const VAULT_CODE = 'tnw_authorize_cim_vault';

    /** @var Config */
    private $config;

    /** @var SessionManagerInterface */
    private $session;

    /**
     * @param Config $config
     * @param SessionManagerInterface $session
     */
    public function __construct(
        Config $config,
        SessionManagerInterface $session
    ) {
        $this->config = $config;
        $this->session = $session;
    }

    /**
     * Get payment config array for payment method in checkout
     *
     * @return array
     */
    public function getConfig()
    {
        $storeId = $this->session->getStoreId();
        return [
            'payment' => [
                self::CODE => [
                    'isActive' => $this->config->isActive($storeId),
                    'clientKey' => $this->getClientKey(),
                    'apiLoginId' => $this->getApiLoginId(),
                    'countrySpecificCardTypes' => $this->config->getCountrySpecificCardTypeConfig($storeId),
                    'availableCardTypes' => $this->config->getAvailableCardTypes($storeId),
                    'useccv' => $this->getUseCcv(),
                    'vaultCode' => self::VAULT_CODE,
                    'test' => $this->getIsTest(),
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
