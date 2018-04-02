<?php
/**
 * Copyright © 2018 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */

namespace TNW\AuthorizeCim\Model\Request\Data\PaymentData;

use Magento\Framework\DataObject;
use Magento\Framework\Exception\PaymentException;
use TNW\AuthorizeCim\Model\Request\Data\PaymentData\TransactionRequest\OrderData;
use TNW\AuthorizeCim\Model\Request\Data\PaymentData\TransactionRequest\OrderDataFactory;
use TNW\AuthorizeCim\Model\Request\Data\PaymentData\TransactionRequest\TransactionTypesResource;

/**
 * Contain transaction data
 */
class TransactionRequestData extends DataObject
{
    /** Order data key */
    const ORDER_KEY = 'order';

    /** Transaction type key */
    const TRANSACTION_TYPE_KEY = 'transaction_type';

    /** order amount key */
    const AMOUNT_KEY = 'amount';

    /** customer payment profile id */
    const PAYMENT_PROFILE_ID = 'payment_profile_id';

    /** customer profile id */
    const CUSTOMER_PROFILE_ID = 'customer_profile_id';

    /**
     * @var OrderDataFactory
     */
    private $orderDataFactory;

    /**
     * @param OrderDataFactory $orderDataFactory
     * @param array $data
     */
    public function __construct(
        OrderDataFactory $orderDataFactory,
        array $data = []
    ) {
        $this->orderDataFactory = $orderDataFactory;
        parent::__construct($data);
    }

    /**
     * Set transaction type
     *
     * @param string $transactionType
     * @return $this
     * @throws PaymentException
     */
    public function setTransactionType($transactionType) {
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
     * Create order object and return him
     *
     * @return null|OrderData
     */
    public function createOrder()
    {
        if ($this->getOrder() === null) {
            $order = $this->orderDataFactory->create();
            $this->setData(self::ORDER_KEY, $order);
        }

        return $this->getOrder();
    }

    /**
     * Return order object
     *
     * @return OrderData|null
     */
    public function getOrder()
    {
        return $this->getData(self::ORDER_KEY);
    }

    /**
     * Set transaction amount
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
     * Return amount
     *
     * @return string|null
     */
    public function getAmount()
    {
        return $this->getData(self::AMOUNT_KEY);
    }

    /**
     * Set customer payment profile ID for transaction
     *
     * @param string $paymentProfileId
     * @return $this
     */
    public function setCustomerPaymentProfileId($paymentProfileId)
    {
        $this->setData(self::PAYMENT_PROFILE_ID, $paymentProfileId);

        return $this;
    }

    /**
     * Return customer payment profile ID
     *
     * @return string|null
     */
    public function getCustomerPaymentProfileId()
    {
        return $this->getData(self::PAYMENT_PROFILE_ID);
    }

    /**
     * Set customer profile for transaction
     *
     * @param string $customerProfileId
     * @return $this
     */
    public function setCustomerProfileId($customerProfileId)
    {
        $this->setData(self::CUSTOMER_PROFILE_ID, $customerProfileId);

        return $this;
    }

    /**
     * Return customer profile ID
     *
     * @return string|null
     */
    public function getCustomerProfileId()
    {
        return $this->getData(self::CUSTOMER_PROFILE_ID);
    }

    /**
     * Set is need create profile
     *
     * @param bool $createProfile
     * @return $this
     */
    public function setNeedCreateProfile(bool $createProfile)
    {
        $this->setData(self::CUSTOMER_PROFILE_ID, $createProfile);

        return $this;
    }

    /**
     * Return is need create profile
     *
     * @return string|null
     */
    public function getNeedCreateProfile()
    {
        return $this->getData(self::CUSTOMER_PROFILE_ID);
    }
}
