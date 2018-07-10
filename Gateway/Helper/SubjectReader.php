<?php
/**
 * Copyright © 2018 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
namespace TNW\AuthorizeCim\Gateway\Helper;

use Magento\Payment\Gateway\Helper;

/**
 * Subject Reader
 */
class SubjectReader
{
    /**
     * @param array $subject
     * @return mixed
     */
    public function readResponseObject(array $subject)
    {
        $response = Helper\SubjectReader::readResponse($subject);

        if (!isset($response['object']) || !\is_object($response['object'])) {
            throw new \InvalidArgumentException('Response object does not exist.');
        }

        return $response['object'];
    }

    /**
     * @param array $subject
     * @return \Magento\Payment\Gateway\Data\PaymentDataObjectInterface
     */
    public function readPayment(array $subject)
    {
        return Helper\SubjectReader::readPayment($subject);
    }

    /**
     * @param array $subject
     * @return \net\authorize\api\contract\v1\AnetApiResponseType
     */
    public function readTransaction(array $subject)
    {
        if (!isset($subject['object']) || !\is_object($subject['object'])) {
            throw new \InvalidArgumentException('Response object does not exist');
        }

        return $subject['object'];
    }

    /**
     * @param array $subject
     * @return mixed
     */
    public function readAmount(array $subject)
    {
        return Helper\SubjectReader::readAmount($subject);
    }
}
