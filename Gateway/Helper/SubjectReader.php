<?php
/**
 * Copyright © 2018 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */

namespace TNW\AuthorizeCim\Gateway\Helper;

use Magento\Payment\Gateway\Helper;

//TODO Нужны коменты
class SubjectReader
{
    public function readResponseObject(array $subject)
    {
        $response = Helper\SubjectReader::readResponse($subject);

        if (!is_object($response['object'])) {
            throw new \InvalidArgumentException('Response object does not exist.');
        }

        return $response['object'];
    }

    public function readPayment(array $subject)
    {
        return Helper\SubjectReader::readPayment($subject);
    }

    public function readTransaction(array $subject)
    {
        if (!is_object($subject['object'])) {
            throw new \InvalidArgumentException('Response object does not exist');
        }

        return $subject['object'];
    }

    public function readAmount(array $subject)
    {
        return Helper\SubjectReader::readAmount($subject);
    }
}
