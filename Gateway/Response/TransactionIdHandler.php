<?php
/**
 * Copyright © 2017 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */

namespace TNW\AuthorizeCim\Gateway\Response;

use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Model\Order\Payment;
use TNW\AuthorizeCim\Gateway\Helper\SubjectReader;

class TransactionIdHandler implements HandlerInterface
{
    /** @var SubjectReader */
    protected $_subjectReader;

    public function __construct(
        SubjectReader $subjectReader
    ) {
        $this->_subjectReader = $subjectReader;
    }

    public function handle(array $handlingSubject, array $response)
    {
        $paymentDataObject = $this->_subjectReader->readPayment($handlingSubject);

        if ($paymentDataObject->getPayment() instanceof Payment) {
            $transaction = $this->_subjectReader->readTransaction($response);
            $transaction = $transaction->getData('transactionResponse');
            $orderPayment = $paymentDataObject->getPayment();

            $this->_setTransactionId(
                $orderPayment,
                $transaction
            );

            $orderPayment->setIsTransactionClosed($this->_shouldCloseTransaction());
            $closed = $this->_shouldCloseParentTransaction($orderPayment);
            $orderPayment->setShouldCloseParentTransaction($closed);
        }
    }

    protected function _setTransactionId(Payment $orderPayment, $transaction)
    {
        $orderPayment->setTransactionId($transaction->getData('transId'));
    }

    protected function _shouldCloseTransaction()
    {
        return false;
    }

    protected function _shouldCloseParentTransaction(Payment $orderPayment)
    {
        return false;
    }
}
