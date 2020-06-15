<?php
/**
 * Copyright © 2017 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */

namespace TNW\AuthorizeCim\Gateway\Response;

use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Payment\Model\InfoInterface;
use Magento\Sales\Api\Data\OrderPaymentExtensionInterface;
use Magento\Sales\Api\Data\OrderPaymentExtensionInterfaceFactory;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Model\CreditCardTokenFactory;
use TNW\AuthorizeCim\Gateway\Config\Config;
use TNW\AuthorizeCim\Gateway\Helper\SubjectReader;
use Magento\Vault\Model\PaymentTokenManagement;

class VaultDetailsHandler implements HandlerInterface
{
    /** @var CreditCardTokenFactory */
    private $paymentTokenFactory;

    /** @var OrderPaymentExtensionInterfaceFactory */
    private $paymentExtensionFactory;

    /** @var SubjectReader */
    private $subjectReader;

    /** @var Config */
    private $config;

    private $paymentTokenManagement;

    public function __construct(
        CreditCardTokenFactory $creditCardTokenFactory,
        OrderPaymentExtensionInterfaceFactory $paymentExtensionFactory,
        Config $config,
        SubjectReader $subjectReader,
        PaymentTokenManagement $paymentTokenManagement
    ) {
        $this->paymentTokenManagement = $paymentTokenManagement;
        $this->paymentTokenFactory = $creditCardTokenFactory;
        $this->paymentExtensionFactory = $paymentExtensionFactory;
        $this->subjectReader = $subjectReader;
        $this->config = $config;
    }

    /**
     * @inheritdoc
     */
    public function handle(array $subject, array $response)
    {
        $paymentDO = $this->subjectReader->readPayment($subject);

        /** @var \net\authorize\api\contract\v1\CreateCustomerProfileResponse $transaction */
        $transaction = $this->subjectReader->readTransaction($response);
        $payment = $paymentDO->getPayment();

        if (!$payment->getAdditionalInformation('is_active_payment_token_enabler')) {
            return;
        }

        $paymentToken = $this->getVaultPaymentToken($transaction, $payment);
        if (null !== $paymentToken) {
            $extensionAttributes = $this->_getExtensionAttributes($payment);
            $extensionAttributes->setVaultPaymentToken($paymentToken);
        }
    }

    /**
     * @param \net\authorize\api\contract\v1\CreateCustomerProfileResponse $transaction
     * @param $payment
     * @return PaymentTokenInterface|null
     */
    private function getVaultPaymentToken($transaction, $payment)
    {
        if (
            $payment->getAdditionalInformation('customer_id')
            && $payment->getAdditionalInformation('public_hash')
        ) {
            return $this->paymentTokenManagement->getByPublicHash(
                $payment->getAdditionalInformation('public_hash'),
                $payment->getAdditionalInformation('customer_id')
            );
        }
        $profileId = $transaction->getCustomerProfileId();
        $paymentProfileIdList = $transaction->getCustomerPaymentProfileIdList();

        /** @var PaymentTokenInterface $paymentToken */
        $paymentToken = $this->paymentTokenFactory->create()
            ->setExpiresAt($this->_getExpirationDate($payment))
            ->setGatewayToken(sprintf('%s/%s', $profileId, reset($paymentProfileIdList)));

        $paymentToken->setTokenDetails($this->_convertDetailsToJSON([
            'type' => $this->getCreditCardType($payment->getAdditionalInformation('cc_type')),
            'maskedCC' => $payment->getAdditionalInformation('cc_last4'),
            'expirationDate' => sprintf(
                '%s/%s',
                $payment->getAdditionalInformation('cc_exp_month'),
                $payment->getAdditionalInformation('cc_exp_year')
            )
        ]));

        return $paymentToken;
    }

    /**
     * @param $payment
     * @return string
     */
    private function _getExpirationDate($payment)
    {
        $time = sprintf(
            '%s-%s-01 00:00:00',
            trim($payment->getAdditionalInformation('cc_exp_year')),
            trim($payment->getAdditionalInformation('cc_exp_month'))
        );

        return date_create($time, timezone_open('UTC'))
            ->modify('+1 month')
            ->format('Y-m-d 00:00:00');
    }

    private function _convertDetailsToJSON($details)
    {
        $json = \Zend_Json::encode($details);
        return $json ? $json : '{}';
    }

    /**
     * Get type of credit card mapped from Braintree
     *
     * @param string $type
     * @return array
     */
    private function getCreditCardType($type)
    {
        $replaced = str_replace(' ', '-', strtolower($type));
        $mapper = $this->config->getCctypesMapper();

        return $mapper[$replaced];
    }

    /**
     * Get payment extension attributes
     * @param InfoInterface $payment
     * @return OrderPaymentExtensionInterface
     */
    private function _getExtensionAttributes(InfoInterface $payment)
    {
        $extensionAttributes = $payment->getExtensionAttributes();
        if (null === $extensionAttributes) {
            $extensionAttributes = $this->paymentExtensionFactory->create();
            $payment->setExtensionAttributes($extensionAttributes);
        }
        return $extensionAttributes;
    }
}
