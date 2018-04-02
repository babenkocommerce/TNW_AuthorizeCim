<?php
/**
 * Copyright © 2018 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */

namespace TNW\AuthorizeCim\Gateway\Response;

use Magento\Sales\Model\Order\Payment;
use Magento\Payment\Gateway\Response\HandlerInterface;
use TNW\AuthorizeCim\Gateway\Helper\SubjectReader;

class AuthorizeHandler implements HandlerInterface
{
    /** @var SubjectReader  */
    protected $_subjectReader;

    /**
     * @param SubjectReader $subjectReader
     */
    public function __construct(
        SubjectReader $subjectReader
    ) {
        $this->_subjectReader = $subjectReader;
    }

    /**
     * Handle data after authorize card
     *
     * @param array $subject
     * @param array $response
     * @return $this
     */
    public function handle(array $subject, array $response)
    {
        $apiResponse = $response['object']->getData('transactionResponse');
        /** @var Payment $payment */
        $payment = $this->_subjectReader->readPayment($subject)->getPayment();

        if ($payment instanceof Payment) {
            $payment->setTransactionId($apiResponse['transId']);
            $payment->setCcTransId($apiResponse['transId']);
            $payment->setLastTransId($apiResponse['transId']);
            $payment->setTransactionAdditionalInfo('cc_number', $apiResponse['accountNumber']);
            $payment->setTransactionAdditionalInfo('cc_type', $apiResponse['accountType']);
            $payment->setIsTransactionClosed(false);
            $payment->setShouldCloseParentTransaction(false);
        }

        return $this;
    }
}
