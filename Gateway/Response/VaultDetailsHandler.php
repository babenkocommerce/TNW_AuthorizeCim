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

    public function __construct(
        CreditCardTokenFactory $creditCardTokenFactory,
        OrderPaymentExtensionInterfaceFactory $paymentExtensionFactory,
        Config $config,
        SubjectReader $subjectReader
    ) {
        $this->paymentTokenFactory = $creditCardTokenFactory;
        $this->paymentExtensionFactory = $paymentExtensionFactory;
        $this->subjectReader = $subjectReader;
        $this->config = $config;
    }

    public function handle(array $subject, array $response)
    {
        $paymentDO = $this->subjectReader->readPayment($subject);

        /** @var \net\authorize\api\contract\v1\CreateTransactionResponse $transaction */
        $transaction = $this->subjectReader->readTransaction($response);
        $transaction = $transaction->getTransactionResponse();
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
     * @param \net\authorize\api\contract\v1\CreateTransactionResponse $transaction
     * @param $payment
     * @return PaymentTokenInterface|null
     */
    private function getVaultPaymentToken($transaction, $payment)
    {
        // Check token existing in gateway response
        $paymentProfileId = $transaction->getProfileResponse()->getCustomerProfileId();
        if (!isset($paymentProfileId)) {
            return null;
        }

        /** @var PaymentTokenInterface $paymentToken */
        $paymentToken = $this->paymentTokenFactory->create();
        $paymentToken->setGatewayToken($paymentProfileId);
        $paymentToken->setExpiresAt($this->_getExpirationDate($payment));

        $paymentToken->setTokenDetails($this->_convertDetailsToJSON([
            'type' => $payment->getAdditionalInformation('cc_type'),
            'maskedCC' => $payment->getAdditionalInformation('cc_last4'),
            'expirationDate' => $payment->getAdditionalInformation('cc_exp_month') . '/' . $payment->getAdditionalInformation('cc_exp_year')
        ]));

        return $paymentToken;
    }

    private function _getExpirationDate($payment)
    {
        $expDate = new \DateTime(
            trim($payment->getAdditionalInformation('cc_exp_year'))
            . '-'
            . trim($payment->getAdditionalInformation('cc_exp_month'))
            . '-'
            . '01'
            . ' '
            . '00:00:00',
            new \DateTimeZone('UTC')
        );
        $expDate->add(new \DateInterval('P1M'));
        return $expDate->format('Y-m-d 00:00:00');
    }

    private function _convertDetailsToJSON($details)
    {
        $json = \Zend_Json::encode($details);
        return $json ? $json : '{}';
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
