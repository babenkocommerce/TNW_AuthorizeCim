<?php
/**
 * Copyright © 2018 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
namespace TNW\AuthorizeCim\Gateway\Validator;

use net\authorize\api\contract\v1\CreateTransactionResponse;
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
                if (!$transactionResponse) {
                    return [true, []];
                }

                $transactionResponse->getResponseCode();

                $errorMessages = array_map([$this, 'errorMap'], $transactionResponse->getErrors());

                return [
                    !count($errorMessages),
                    $errorMessages
                ];
            }
        ]);
    }

    /**
     * @param ErrorAType $message
     * @return string
     */
    private function errorMap(ErrorAType $message)
    {
        return $message->getErrorCode();
    }
}
