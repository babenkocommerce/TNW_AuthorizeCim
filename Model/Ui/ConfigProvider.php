<?php
/**
 * Copyright © 2017 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
namespace TNW\AuthorizeCim\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Session\SessionManagerInterface;
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
                    'clientKey' => $this->config->getClientKey(),
                    'apiLoginId' => $this->config->getApiLoginId(),
                    'sdkUrl' => $this->config->getSdkUrl(),
                    'vaultCode' => self::VAULT_CODE,
                ]
            ]
        ];
    }
}
