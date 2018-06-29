<?php
/**
 * Copyright © 2018 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
namespace TNW\AuthorizeCim\Gateway\Validator;

use net\authorize\api\contract\v1\CreateTransactionResponse;
use net\authorize\api\contract\v1\TransactionResponseType\MessagesAType\MessageAType;

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
                if (!$transactionResponse) {
                    return [true, []];
                }

                $messages = $transactionResponse->getMessages();
                $errorMessages = array_map([$this, 'map'], array_filter($messages, [$this, 'filter']));

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
        return $message->getDescription();
    }
}
