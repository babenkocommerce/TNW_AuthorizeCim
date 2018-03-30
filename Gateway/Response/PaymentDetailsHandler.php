<?php
/**
 * Copyright © 2017 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */

namespace TNW\AuthorizeCim\Gateway\Response;

use Magento\Payment\Gateway\Response\HandlerInterface;
use TNW\AuthorizeCim\Gateway\Helper\SubjectReader;

class PaymentDetailsHandler implements HandlerInterface
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
        $transaction = $this->_subjectReader->readTransaction($response);
        $transaction = $transaction->getData('transactionResponse');
        $payment = $paymentDataObject->getPayment();

        $payment->setCcTransId($transaction->getData('transId'));
        $payment->setLastTransId($transaction->getData('transId'));

        $additionalInformation = [
            'auth_code' => $transaction->getData('authCode'),
            'avs_code' => $transaction->getData('avsResultCode'),
            'cavv_code' => $transaction->getData('cavvResultCode'),
            'cvv_code' => $transaction->getData('cvvResultCode')
        ];

        foreach ($additionalInformation as $key => $value) {
            $payment->setAdditionalInformation($key, $value);
        }
    }
}
