<?php
/**
 * Copyright © 2017 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
namespace TNW\AuthorizeCim\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Session\SessionManagerInterface;
use TNW\AuthorizeCim\Gateway\Config\Config;
use Magento\Framework\UrlInterface;

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
     * @var UrlInterface
     */
    private $url;

    /**
     * @param Config $config
     * @param SessionManagerInterface $session
     * @param UrlInterface $url
     */
    public function __construct(
        Config $config,
        SessionManagerInterface $session,
        UrlInterface $url
    ) {
        $this->config = $config;
        $this->session = $session;
        $this->url = $url;
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
                    'clientKey' => $this->config->getClientKey($storeId),
                    'apiLoginID' => $this->config->getApiLoginId($storeId),
                    'sdkUrl' => $this->config->getSdkUrl($storeId),
                    'vaultCode' => self::VAULT_CODE,
                ],
                'verify_authorize' => [
                    'enabled' => $this->config->isVerify3DSecure($storeId),
                    'thresholdAmount' => $this->config->getThresholdAmount($storeId),
                    'specificCountries' => $this->config->get3DSecureSpecificCountries($storeId),
                    'sdkUrl' => $this->config->getVerifySdkUrl($storeId),
                    'jwtUrl' => $this->url->getUrl('tnw_authorizecim/jwt/encode'),
                ],
            ]
        ];
    }
}
