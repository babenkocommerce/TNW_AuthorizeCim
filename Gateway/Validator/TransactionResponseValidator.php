<?php
/**
 * Copyright © 2018 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
namespace TNW\AuthorizeCim\Gateway\Validator;

use net\authorize\api\contract\v1\CreateTransactionResponse;
use net\authorize\api\contract\v1\TransactionResponseType\MessagesAType\MessageAType;
use net\authorize\api\contract\v1\TransactionResponseType\ErrorsAType\ErrorAType;

/**
 * Validate response data
 */
class TransactionResponseValidator extends GeneralResponseValidator
{
    /**
     * @inheritdoc
     */
    protected function getResponseValidators()
    {
        return array_merge(parent::getResponseValidators(), [
            function (CreateTransactionResponse $response) {
                $transactionResponse = $response->getTransactionResponse();
                $result = [true, []];
                if (!$transactionResponse) {
                    return $result;
                }

                $messages = $transactionResponse->getMessages();
                $errorMessages = [];
                if ($messages) {
                    $errorMessages = array_map([$this, 'map'], array_filter($messages, [$this, 'filter']));
                }
                if ($errorMessages) {
                    $messages = $transactionResponse->getErrors();
                    $errorMessages = array_map([$this, 'errorMap'], array_filter($messages, [$this, 'errorFilter']));
                } else {
                    return $result;
                }

                return [
                    !count($errorMessages),
                    $errorMessages
                ];
            }
        ]);
    }

    /**
     * @param MessageAType $message
     * @return bool
     */
    private function filter(MessageAType $message)
    {
        return $message->getCode() != 1;
    }

    /**
     * @param MessageAType $message
     * @return string
     */
    private function map(MessageAType $message)
    {
        return __($message->getDescription());
    }

    /**
     * @param MessageAType $message
     * @return bool
     */
    private function errorFilter(ErrorAType $message)
    {
        return $message->getErrorCode() != 1;
    }

    /**
     * @param MessageAType $message
     * @return string
     */
    private function errorMap(ErrorAType $message)
    {
        return __($message->getErrorText());
    }
}
