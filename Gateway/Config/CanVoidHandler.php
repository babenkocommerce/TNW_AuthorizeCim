<?php
/**
 * Copyright © 2018 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */

namespace TNW\AuthorizeCim\Gateway\Config;

use Magento\Payment\Gateway\Config\ValueHandlerInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use TNW\AuthorizeCim\Gateway\Helper\SubjectReader;

/**
 * Check transaction status and order is paid
 */
class CanVoidHandler implements ValueHandlerInterface
{
    /** @var SubjectReader */
    private $_subjectReader;

    /**
     * @param SubjectReader $subjectReader
     */
    public function __construct(SubjectReader $subjectReader)
    {
        $this->_subjectReader = $subjectReader;
    }

    /**
     * Check has been paid amount
     *
     * @param array $subject
     * @param null|int $storeId
     * @return bool
     */
    public function handle(array $subject, $storeId = null)
    {
        $paymentDataObject = $this->_subjectReader->readPayment($subject);
        $payment = $paymentDataObject->getPayment();

        return $payment instanceof OrderPaymentInterface && !(bool)$payment->getAmountPaid();
    }
}
