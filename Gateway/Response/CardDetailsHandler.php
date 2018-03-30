<?php
/**
 * Copyright © 2017 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */

namespace TNW\AuthorizeCim\Gateway\Response;

use Magento\Payment\Gateway\Helper\ContextHelper;
use Magento\Payment\Gateway\Response\HandlerInterface;
use TNW\AuthorizeCim\Gateway\Helper\SubjectReader;

class CardDetailsHandler implements HandlerInterface
{
    /** @var SubjectReader */
    protected $_subjectReader;

    public function __construct(
        SubjectReader $subjectReader
    ) {
        $this->_subjectReader = $subjectReader;
    }

    public function handle(array $subject, array $response)
    {
        $paymentDataObject = $this->_subjectReader->readPayment($subject);
        $transaction = $this->_subjectReader->readTransaction($response);
        $transaction = $transaction->getData('transactionResponse');
        $payment = $paymentDataObject->getPayment();
        ContextHelper::assertOrderPayment($payment);

        $payment->setCcLast4($this->_getLast4($transaction->getData('accountNumber')));
        $payment->setCcType($transaction->getData('accountType'));
    }

    protected function _getLast4($string)
    {
        return substr($string, strlen($string) - 4, strlen($string));
    }
}
