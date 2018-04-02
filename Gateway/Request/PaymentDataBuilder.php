<?php
/**
 * Copyright © 2018 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */

namespace TNW\AuthorizeCim\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;
use TNW\AuthorizeCim\Model\Request\Data\PaymentDataFactory;
use TNW\AuthorizeCim\Gateway\Helper\SubjectReader;

/**
 * Class for build request payment data
 */
class PaymentDataBuilder implements BuilderInterface
{
    /** key for build payment data */
    const PAYMENT_BUILD_KEY = 'payment_data';

    /**
     * @var PaymentDataFactory
     */
    private $paymentDataFactory;

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @param PaymentDataFactory $paymentDataFactory
     * @param SubjectReader $subjectReader
     */
    public function __construct(
        PaymentDataFactory $paymentDataFactory,
        SubjectReader $subjectReader
    ) {
        $this->paymentDataFactory = $paymentDataFactory;
        $this->subjectReader = $subjectReader;
    }

    /**
     * Build payment data
     *
     * @param array $subject
     * @return array
     */
    public function build(array $subject)
    {
        $paymentDataObject = $this->subjectReader->readPayment($subject);
        $payment = $paymentDataObject->getPayment();
        $order = $paymentDataObject->getOrder();
        $paymentDataObj = $this->paymentDataFactory->create();
        $paymentProfile = $paymentDataObj->createPaymentProfile();
        $transactionRequest = $paymentDataObj->createTransactionRequest();
        $paymentInfo = $paymentDataObj->createPaymentInfo();
        $paymentProfile->setIsDefaultPaymentProfile()
            ->setCustomerType()
            ->createOpaque()->setDataDescriptor()->setDataValue(
                $payment->getAdditionalInformation('cc_token')
            );
        $transactionRequest->setAmount(sprintf('%.2F', $this->subjectReader->readAmount($subject)))
            ->createOrder()->setInvoiceNumber($order->getOrderIncrementId());
        $paymentInfo->setCcType($payment->getAdditionalInformation('cc_type'))
            ->setLast4('XXXX' . substr($payment->getAdditionalInformation('cc_number'), -4))
            ->setExpMonth($payment->getAdditionalInformation('cc_exp_month'))
            ->setExpYear($payment->getAdditionalInformation('cc_exp_year'))
            ->setCardNumber($payment->getAdditionalInformation('cc_number'))
            ->setCardCode($payment->getAdditionalInformation('cc_cid'));
        $payment->unsAdditionalInformation('cc_number')
            ->unsAdditionalInformation('cc_cid');
        $paymentDataObj->setCapture()
            ->setSaveInVault((bool)$payment->getAdditionalInformation('is_active_payment_token_enabler'));

        return [self::PAYMENT_BUILD_KEY => $paymentDataObj];
    }
}
