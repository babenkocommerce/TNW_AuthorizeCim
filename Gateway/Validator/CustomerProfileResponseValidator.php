<?php
/**
 * Copyright © 2018 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
namespace TNW\AuthorizeCim\Gateway\Validator;

use net\authorize\api\contract\v1\CreateCustomerProfileResponse;
use net\authorize\api\contract\v1\MessagesType\MessageAType;

/**
 * Validate response data
 */
class CustomerProfileResponseValidator extends GeneralResponseValidator
{
    /**
     * @inheritdoc
     */
    protected function getResponseValidators()
    {
        return [
            function (CreateCustomerProfileResponse $response) {
                $messages = $response->getMessages()->getMessage();
                $errorMessages = array_map([$this, 'map'], array_filter($messages, [$this, 'filter']));

                return [
                    !count($errorMessages),
                    $errorMessages
                ];
            }
        ];
    }

    /**
     * @param MessageAType $message
     * @return bool
     */
    private function filter(MessageAType $message)
    {
        return $message->getCode() != 'I00001';
    }

    /**
     * @param MessageAType $message
     * @return string
     */
    private function map(MessageAType $message)
    {
        return $message->getText();
    }
}
