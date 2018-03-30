<?php
/**
 * Copyright © 2018 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */

namespace TNW\AuthorizeCim\Model\Request\Data\PaymentData\TransactionRequest;

/**
 * Transaction types resource
 */
class TransactionTypesResource
{
    //TODO Дописать описание типов транзакций
    const TYPE_REFUND = 'refundTransaction';
    const TYPE_AUTH_CAPTURE = 'authCaptureTransaction';
    const TYPE_AUTH_ONLY = 'authOnlyTransaction';
    const TYPE_GET_DETAILS = 'getDetailsTransaction';
    const TYPE_AUTH_ONLY_CONTINUE = 'authOnlyContinueTransaction';
    const TYPE_PRIOR_AUTH_CAPTURE = 'priorAuthCaptureTransaction';
    const TYPE_AUTH_CAPTURE_CONTINUE = 'authCaptureContinueTransaction';
    const TYPE_VOID = 'voidTransaction';
    const TYPE_CAPTURE_ONLY = 'captureOnlyTransaction';

    /**
     * Return available transaction types
     *
     * @return array
     */
    public static function getAvailableTypes()
    {
        return [
            self::TYPE_REFUND,
            self::TYPE_AUTH_CAPTURE,
            self::TYPE_AUTH_ONLY,
            self::TYPE_GET_DETAILS,
            self::TYPE_AUTH_ONLY_CONTINUE,
            self::TYPE_PRIOR_AUTH_CAPTURE,
            self::TYPE_AUTH_CAPTURE_CONTINUE,
            self::TYPE_VOID,
            self::TYPE_CAPTURE_ONLY,
        ];
    }
}