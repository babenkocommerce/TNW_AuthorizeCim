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
    const AVS_CODE = 'avs_code';
    const CVV_CODE = 'cvv_code';

    /**
     * This transaction has been approved.
     */
    const APPROVED_CODE = '1';

    /**
     * This transaction has been declined.
     */
    const DENIED_CODE = '2';

    /**
     * The code returned from the processor indicating that the card used needs to be picked up
     */
    const REVIEW_CODE = '4';

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * PaymentDetailsHandler constructor.
     * @param SubjectReader $subjectReader
     */
    public function __construct(
        SubjectReader $subjectReader
    ) {
        $this->subjectReader = $subjectReader;
    }

    /**
     * @inheritdoc
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function handle(array $handlingSubject, array $response)
    {
        $paymentDataObject = $this->subjectReader->readPayment($handlingSubject);

        /** @var \net\authorize\api\contract\v1\CreateTransactionResponse $transaction */
        $transaction = $this->subjectReader->readTransaction($response);
        $transaction = $transaction->getTransactionResponse();

        /** @var \Magento\Sales\Model\Order\Payment $payment */
        $payment = $paymentDataObject->getPayment();

        $payment->setCcTransId($transaction->getTransId());
        $payment->setLastTransId($transaction->getTransId());

        switch ($transaction->getResponseCode()) {
            case self::APPROVED_CODE:
                $payment->setIsTransactionApproved(true);
                break;

            case self::DENIED_CODE:
                $payment->setIsTransactionDenied(true);
                break;

            case self::REVIEW_CODE:
                $payment->setIsTransactionPending(true);
                break;
        }

        $additionalInformation = [
            'auth_code' => $transaction->getAuthCode(),
            self::AVS_CODE => $transaction->getAvsResultCode(),
            'cavv_code' => $transaction->getCavvResultCode(),
            self::CVV_CODE => $transaction->getCvvResultCode()
        ];

        $payment->unsAdditionalInformation('opaqueDescriptor');
        $payment->unsAdditionalInformation('opaqueValue');
        foreach ($additionalInformation as $key => $value) {
            $payment->setAdditionalInformation($key, $value);
        }
    }
}
