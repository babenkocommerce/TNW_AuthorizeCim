<?php
/**
 * Copyright © 2018 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
namespace TNW\AuthorizeCim\Gateway\Validator;

use TNW\AuthorizeCim\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;
use net\authorize\api\contract\v1\ANetApiResponseType;
use net\authorize\api\contract\v1\MessagesType\MessageAType;

/**
 * Validate response data
 */
class GeneralResponseValidator extends AbstractValidator
{
    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * Constructor
     *
     * @param ResultInterfaceFactory $resultFactory
     * @param SubjectReader $subjectReader
     */
    public function __construct(ResultInterfaceFactory $resultFactory, SubjectReader $subjectReader)
    {
        parent::__construct($resultFactory);
        $this->subjectReader = $subjectReader;
    }

    /**
     * @inheritdoc
     */
    public function validate(array $validationSubject)
    {
        /** @var \net\authorize\api\contract\v1\CreateTransactionResponse $response */
        $response = $this->subjectReader->readResponseObject($validationSubject);

        $isValid = true;
        $errorMessages = [];

        foreach ($this->getResponseValidators() as $validator) {
            $validationResult = $validator($response);

            if (!$validationResult[0]) {
                $isValid = $validationResult[0];
                $errorMessages = array_merge($errorMessages, $validationResult[1]);
            }
        }

        return $this->createResult($isValid, $errorMessages);
    }

    /**
     * @return array
     */
    protected function getResponseValidators()
    {
        return [
            function (ANetApiResponseType $response) {
                $messages = $response->getMessages();
                $errorMessages = array_map(function (MessageAType $message) {
                    return __('%1: %2', $message->getCode(), $message->getText());
                }, $messages->getMessage());

                return [
                    strcasecmp($messages->getResultCode(), 'Ok')  === 0,
                    $errorMessages
                ];
            }
        ];
    }
}
