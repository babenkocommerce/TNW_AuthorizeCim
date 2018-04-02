<?php
/**
 * Copyright © 2018 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */

namespace TNW\AuthorizeCim\Gateway\Request;

use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Request\BuilderInterface;
use TNW\AuthorizeCim\Model\Request\Data\CaptureDataFactory;
use TNW\AuthorizeCim\Gateway\Helper\SubjectReader;

/**
 * Class for build request payment data
 */
class CaptureDataBuilder implements BuilderInterface
{
    /** key for build capture data */
    const CAPTURE_BUILD_KEY = 'capture_data';

    /**
     * @var CaptureDataFactory
     */
    private $captureDataFactory;

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @param CaptureDataFactory $captureDataFactory
     * @param SubjectReader $subjectReader
     */
    public function __construct(
        CaptureDataFactory $captureDataFactory,
        SubjectReader $subjectReader
    ) {
        $this->captureDataFactory = $captureDataFactory;
        $this->subjectReader = $subjectReader;
    }

    /**
     * Build capture data
     *
     * @param array $subject
     * @return array
     * @throws LocalizedException
     */
    public function build(array $subject)
    {
        $paymentDO = $this->subjectReader->readPayment($subject);
        $payment = $paymentDO->getPayment();
        $transactionId = $payment->getCcTransId();

        if (!$transactionId) {
            throw new LocalizedException(__('No authorization transaction to proceed capture.'));
        }

        $captureDataObj = $this->captureDataFactory->create();
        $captureDataObj->setTransactionId($transactionId)
            ->setAmount(sprintf('%.2F', $this->subjectReader->readAmount($subject)));

        return [self::CAPTURE_BUILD_KEY => $captureDataObj];
    }
}
