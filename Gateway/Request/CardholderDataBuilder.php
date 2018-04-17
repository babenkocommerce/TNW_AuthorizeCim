<?php
/**
 * Copyright © 2018 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
namespace TNW\AuthorizeCim\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;
use TNW\AuthorizeCim\Gateway\Helper\SubjectReader;

/**
 * Class for build request payment data
 */
class CardholderDataBuilder implements BuilderInterface
{
    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @param SubjectReader $subjectReader
     */
    public function __construct(
        SubjectReader $subjectReader
    ) {
        $this->subjectReader = $subjectReader;
    }

    /**
     * Build payment data
     *
     * @param array $subject
     * @return array
     */
    public function build(array $subject)
    {
        $paymentDO = $this->subjectReader->readPayment($subject);
        $payment = $paymentDO->getPayment();

        $indicator = $payment->getAdditionalInformation('ECIFlag');
        $value = $payment->getAdditionalInformation('CAVV');

        if (empty($indicator) || empty($value)) {
            return [];
        }

        return [
            'transaction_request' => [
                'cardholder_authentication' => [
                    'authentication_indicator' => $indicator,
                    'cardholder_authentication_value' => $value,
                ]
            ]
        ];
    }
}
