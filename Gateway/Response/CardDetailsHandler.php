<?php
/**
 * Copyright © 2017 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */

namespace TNW\AuthorizeCim\Gateway\Response;

use Magento\Payment\Gateway\Helper\ContextHelper;
use Magento\Payment\Gateway\Response\HandlerInterface;
use TNW\AuthorizeCim\Gateway\Helper\SubjectReader;
use Magento\Sales\Api\Data\OrderPaymentInterface;

class CardDetailsHandler implements HandlerInterface
{
    const CARD_NUMBER = 'cc_number';

    /** @var
     * SubjectReader
     */
    private $subjectReader;

    /**
     * CardDetailsHandler constructor.
     * @param SubjectReader $subjectReader
     */
    public function __construct(
        SubjectReader $subjectReader
    ) {
        $this->subjectReader = $subjectReader;
    }

    public function handle(array $subject, array $response)
    {
        $paymentDO = $this->subjectReader->readPayment($subject);

        /** @var \net\authorize\api\contract\v1\CreateTransactionResponse $transaction */
        $transaction = $this->subjectReader->readTransaction($response);

        /** @var \Magento\Sales\Model\Order\Payment $payment */
        $payment = $paymentDO->getPayment();
        ContextHelper::assertOrderPayment($payment);

        $transactionResponse = $transaction->getTransactionResponse();

        $payment->setCcLast4($transactionResponse->getAccountNumber());
        $payment->setCcType($transactionResponse->getAccountType());

        // set card details to additional info
        $payment->setAdditionalInformation(self::CARD_NUMBER, $transactionResponse->getAccountNumber());
        $payment->setAdditionalInformation(OrderPaymentInterface::CC_TYPE, $transactionResponse->getAccountType());
    }
}
