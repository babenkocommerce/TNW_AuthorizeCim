<?php
/**
 * Copyright © 2017 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
namespace TNW\AuthorizeCim\Controller\Jwt;

use Magento\Framework\App\Action;
use Magento\Framework\Controller\ResultFactory;
use TNW\AuthorizeCim\Gateway\Config\Config;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Hmac\Sha256;

/**
 * Additional save action
 * @package TNW\Subscriptions\Controller\Adminhtml\SubscriptionProfile
 */
class Encode extends Action\Action
{
    /**
     * @var Config
     */
    private $config;

    /**
     * AdditionalSave constructor.
     * @param Action\Context $context
     * @param Config $config
     */
    public function __construct(
        Action\Context $context,
        Config $config
    ) {
        parent::__construct($context);
        $this->config = $config;
    }

    /**
     * Execute action based on request and return result
     *
     * Note: Request will be added as operation argument in future
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $orderDetails = $this->_request->getParam('order_details');
        return $this->resultFactory->create(ResultFactory::TYPE_JSON)
            ->setData(['jwt' => $this->generateToken($orderDetails)]);
    }

    /**
     * @param array $orderDetails
     * @return string
     */
    private function generateToken($orderDetails)
    {
        $currentTime = time();
        $expireTime = 3600; // expiration in seconds - this equals 1hr

        if (!$this->config->isVerify3DSecure()) {
            return '';
        }

        return (string)(new Builder())
            ->setIssuer($this->config->getVerifyApiIdentifier())
            ->setId(\uniqid(), true)
            ->setIssuedAt($currentTime)
            ->setExpiration($currentTime + $expireTime)
            ->set('OrgUnitId', $this->config->getVerifyOrgUnitId())
            ->set('Payload', ['OrderDetails' => $orderDetails])
            ->set('ObjectifyPayload', true)
            ->sign(new Sha256(), $this->config->getVerifyApiKey())
            ->getToken();
    }
}
