<?php
/**
 * Copyright © 2017 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
namespace TNW\AuthorizeCim\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Session\SessionManagerInterface;
use TNW\AuthorizeCim\Gateway\Config\Config;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Hmac\Sha256;

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
                    'clientKey' => $this->config->getClientKey($storeId),
                    'apiLoginId' => $this->config->getApiLoginId($storeId),
                    'sdkUrl' => $this->config->getSdkUrl($storeId),
                    //'verifySdkUrl' => $this->config->getVerifySdkUrl($storeId),
                    //'cardinalRequestJwt' => (string)$this->generateJwtToken($storeId),
                    'vaultCode' => self::VAULT_CODE,
                ]
            ]
        ];
    }

    /**
     * @param int|null $storeId
     * @return \Lcobucci\JWT\Token
     */
    private function generateJwtToken($storeId = null)
    {
        $currentTime = time();
        $expireTime = 3600; // expiration in seconds - this equals 1hr

        return (new Builder())
            ->setIssuer($this->config->getVerifyApiIdentifier($storeId))
            ->setId(\uniqid(), true)
            ->setIssuedAt($currentTime)
            ->setExpiration($currentTime + $expireTime)
            ->set('OrgUnitId', $this->config->getVerifyOrgUnitId($storeId))
            ->set('Payload', ["OrderDetails" => ["OrderNumber" =>  'ORDER-' . \strval(mt_rand(1000, 10000))]])
            ->set('ObjectifyPayload', true)
            ->sign(new Sha256(), $this->config->getVerifyApiKey($storeId))
            ->getToken();
    }
}
