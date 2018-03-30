<?php
/**
 * Copyright © 2018 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */

namespace TNW\AuthorizeCim\Gateway\Validator;

use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterface;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;
use TNW\AuthorizeCim\Gateway\Helper\SubjectReader;
use TNW\AuthorizeCim\Model\Request\Request\CustomerProfile;
use TNW\AuthorizeCim\Model\Request\Request\PaymentProfile;

/**
 * Validate response data
 */
class ResponseValidator extends AbstractValidator
{
    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @param ResultInterfaceFactory $resultInterfaceFactory
     * @param SubjectReader $subjectReader
     */
    public function __construct(
        ResultInterfaceFactory $resultInterfaceFactory,
        SubjectReader $subjectReader
    ) {
        parent::__construct($resultInterfaceFactory);
        $this->subjectReader = $subjectReader;
    }

    /**
     * Validate customer profile and payment is created
     *
     * @param array $subject
     * @return ResultInterface
     */
    public function validate(array $subject)
    {
        $response = $this->subjectReader->readResponseObject($subject)['transactionResponse'];
        $isValid = true;
        $errorMessages = [];

        foreach ($response['messages'] as $message) {
            if ($message['code'] != 1) {
                $isValid = false;
                $errorMessages[] = $message['description'];
                break;
            }
        }

        return $this->createResult($isValid, $errorMessages);
    }
}
