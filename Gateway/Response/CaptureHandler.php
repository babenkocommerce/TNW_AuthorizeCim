<?php
/**
 * Copyright © 2018 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */

namespace TNW\AuthorizeCim\Gateway\Response;

use Magento\Sales\Model\Order\Payment;
use Magento\Payment\Gateway\Response\HandlerInterface;
use TNW\AuthorizeCim\Gateway\Helper\SubjectReader;

class CaptureHandler implements HandlerInterface
{
    /** @var SubjectReader  */
    protected $subjectReader;

    /**
     * @param SubjectReader $subjectReader
     */
    public function __construct(
        SubjectReader $subjectReader
    ) {
        $this->subjectReader = $subjectReader;
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
        /** @var Payment $payment */
        $payment = $this->subjectReader->readPayment($subject)->getPayment();
        $transaction = $this->subjectReader->readTransaction($response);

        return $this;
    }
}
