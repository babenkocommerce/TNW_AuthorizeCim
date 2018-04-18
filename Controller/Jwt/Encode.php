<?php
/**
 * Copyright © 2017 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
namespace TNW\AuthorizeCim\Controller\Jwt;

use Magento\Framework\App\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\QuoteRepository;
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
     * @var QuoteRepository
     */
    private $quoteRepository;

    /**
     * AdditionalSave constructor.
     * @param Action\Context $context
     * @param Config $config
     * @param QuoteRepository $quoteRepository
     */
    public function __construct(
        Action\Context $context,
        Config $config,
        QuoteRepository $quoteRepository
    ) {
        parent::__construct($context);
        $this->config = $config;
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * Execute action based on request and return result
     *
     * Note: Request will be added as operation argument in future
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $quote_id = $this->_request->getParam('quote_id');

        try {
            $quote = $this->quoteRepository->get($quote_id);
        } catch (NoSuchEntityException $e) {
            return $this->resultFactory->create(ResultFactory::TYPE_JSON)
                ->setHttpResponseCode(404)
                ->setData(['error' => $e->getMessage()]);
        }

        return $this->resultFactory->create(ResultFactory::TYPE_JSON)
            ->setData([
                'jwt' => $this->generateToken($quote),
                'number' => 'ORDER-' . $quote->getId()
            ]);
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @return string
     */
    private function generateToken($quote)
    {
        $currentTime = time();
        $expireTime = 3600; // expiration in seconds - this equals 1hr
        $storeId = $quote->getStoreId();

        if (!$this->config->isVerify3DSecure($storeId)) {
            return '';
        }

        return (string)(new Builder())
            ->setIssuer($this->config->getVerifyApiIdentifier($storeId))
            ->setId(\uniqid(), true)
            ->setIssuedAt($currentTime)
            ->setExpiration($currentTime + $expireTime)
            ->set('OrgUnitId', $this->config->getVerifyOrgUnitId($storeId))
            ->set('Payload', [
                "OrderDetails" => [
                    "OrderNumber" =>  'ORDER-' . $quote->getId(),
                    "Amount" => $quote->getBaseGrandTotal(),
                    "CurrencyCode" => $quote->getBaseCurrencyCode()
                ]
            ])
            ->set('ObjectifyPayload', true)
            ->sign(new Sha256(), $this->config->getVerifyApiKey($storeId))
            ->getToken();
    }
}
