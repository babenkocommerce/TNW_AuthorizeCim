<?php
/**
 * Copyright © 2018 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */

namespace TNW\AuthorizeCim\Model\Request\Data;

use Magento\Framework\DataObject;
use Magento\Framework\Exception\PaymentException;
use TNW\AuthorizeCim\Model\Request\Data\PaymentData\TransactionRequest\TransactionTypesResource;

class CaptureData extends DataObject
{
    /** captured transaction ID key */
    const TRANSACTION_ID_KEY = 'transaction_id';

    /** captured amount key */
    const AMOUNT_KEY = 'amount';

    /** Transaction type key */
    const TRANSACTION_TYPE_KEY = 'transaction_type';

    /**
     * Set transaction type
     *
     * @param string $transactionType
     * @return $this
     * @throws PaymentException
     */
    public function setTransactionType($transactionType)
    {
        if (!in_array($transactionType, TransactionTypesResource::getAvailableTypes(), true)) {
            throw new PaymentException(__('Transaction type %1 is not available', $transactionType));
        }

        $this->setData(self::TRANSACTION_TYPE_KEY, $transactionType);

        return $this;
    }

    /**
     * Return transaction type
     *
     * @return string|null
     */
    public function getTransactionType()
    {
        return $this->getData(self::TRANSACTION_TYPE_KEY);
    }

    /**
     * Set transaction ID for capture
     *
     * @param string $transactionKey
     * @return $this
     */
    public function setTransactionId($transactionKey)
    {
        $this->setData(self::TRANSACTION_ID_KEY, $transactionKey);

        return $this;
    }

    /**
     * Return capture transaction ID
     *
     * @return string|null
     */
    public function getTransactionId()
    {
        return $this->getData(self::TRANSACTION_ID_KEY);
    }

    /**
     * Set capture amount
     *
     * @param string $amount
     * @return $this
     */
    public function setAmount($amount)
    {
        $this->setData(self::AMOUNT_KEY, $amount);

        return $this;
    }

    /**
     * Return capture amount
     *
     * @return string|null
     */
    public function getAmount()
    {
        return $this->getData(self::AMOUNT_KEY);
    }
}
