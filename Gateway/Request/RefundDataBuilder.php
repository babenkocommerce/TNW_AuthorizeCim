<?php
/**
 * Copyright © 2018 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
namespace TNW\AuthorizeCim\Gateway\Request;

use TNW\AuthorizeCim\Gateway\Helper\SubjectReader;
use TNW\AuthorizeCim\Helper\Payment\Formatter;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Refund Data Builder
 */
class RefundDataBuilder implements BuilderInterface
{
    use Formatter;

    const TRANSACTION_ID = 'transaction_id';

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * RefundDataBuilder constructor.
     * @param SubjectReader $subjectReader
     */
    public function __construct(
        SubjectReader $subjectReader
    ) {
        $this->subjectReader = $subjectReader;
    }

    /**
     * {@inheritdoc}
     * @throws LocalizedException
     */
    public function build(array $subject)
    {
        $paymentDO = $this->subjectReader->readPayment($subject);

        /** @var \Magento\Sales\Model\Order\Payment $payment */
        $payment = $paymentDO->getPayment();

        return [
            'transaction_request' => [
                'amount' => $this->formatPrice($this->subjectReader->readAmount($subject)),
                'ref_trans_id' => $payment->getParentTransactionId()
            ]
        ];
    }
}
