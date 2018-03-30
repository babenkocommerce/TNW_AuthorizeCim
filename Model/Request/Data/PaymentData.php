<?php
/**
 * Copyright © 2018 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */

namespace TNW\AuthorizeCim\Model\Request\Data;

use Magento\Framework\DataObject;
use TNW\AuthorizeCim\Model\Request\Data\PaymentData\PaymentInfoData;
use TNW\AuthorizeCim\Model\Request\Data\PaymentData\PaymentInfoDataFactory;
use TNW\AuthorizeCim\Model\Request\Data\PaymentData\PaymentProfileData;
use TNW\AuthorizeCim\Model\Request\Data\PaymentData\PaymentProfileDataFactory;
use TNW\AuthorizeCim\Model\Request\Data\PaymentData\TransactionRequestData;
use TNW\AuthorizeCim\Model\Request\Data\PaymentData\TransactionRequestDataFactory;

/**
 * Data by payment for request
 */
class PaymentData extends DataObject
{
    /** Can capture field key */
    const CAPTURE_KEY = 'capture';

    /** Transaction request data field key */
    const TRANSACTION_REQUEST_KEY = 'transaction_request';

    /** Payment profile data field key */
    const PAYMENT_PROFILE_KEY = 'payment_profile';

    /** Payment info field key */
    const PAYMENT_INFO_KEY = 'payment_info';

    /** Save in vault field key key */
    const SAVE_IN_VAULT_KEY = 'save_in_vault';

    /**
     * @var PaymentDataFactory
     */
    private $paymentProfileDataFactory;

    /**
     * @var TransactionRequestDataFactory
     */
    private $transactionRequestDataFactory;

    /**
     * @var PaymentInfoDataFactory
     */
    private $paymentInfoDataFactory;

    /**
     * @param PaymentProfileDataFactory $paymentProfileDataFactory
     * @param TransactionRequestDataFactory $transactionRequestDataFactory
     * @param PaymentInfoDataFactory $paymentInfoDataFactory
     * @param array $data
     */
    public function __construct(
        PaymentProfileDataFactory $paymentProfileDataFactory,
        TransactionRequestDataFactory $transactionRequestDataFactory,
        PaymentInfoDataFactory $paymentInfoDataFactory,
        array $data = []
    ) {
        $this->paymentProfileDataFactory = $paymentProfileDataFactory;
        $this->transactionRequestDataFactory = $transactionRequestDataFactory;
        $this->paymentInfoDataFactory = $paymentInfoDataFactory;
        parent::__construct($data);
    }

    /**
     * Set can capture
     *
     * @param bool $capture
     * @return $this
     */
    public function setCapture(bool $capture = false)
    {
        $this->setData(self::CAPTURE_KEY, $capture);

        return $this;
    }

    /**
     * Create transaction request object and
     * return him
     *
     * @return null|TransactionRequestData
     */
    public function createTransactionRequest()
    {
        if ($this->getTransactionRequest() === null) {
            $transactionRequest = $this->transactionRequestDataFactory->create();
            $this->setData(self::TRANSACTION_REQUEST_KEY, $transactionRequest);
        }

        return $this->getTransactionRequest();
    }

    /**
     * Return transaction request
     *
     * @return TransactionRequestData|null
     */
    public function getTransactionRequest()
    {
        return $this->getData(self::TRANSACTION_REQUEST_KEY);
    }

    /**
     * Create payment profile object and
     * return him
     *
     * @return null|PaymentProfileData
     */
    public function createPaymentProfile()
    {
        if ($this->getPaymentProfile() === null) {
            $paymentProfile = $this->paymentProfileDataFactory->create();
            $this->setData(self::PAYMENT_PROFILE_KEY, $paymentProfile);
        }

        return $this->getPaymentProfile();
    }

    /**
     * Return payment profile object
     *
     * @return PaymentProfileData|null
     */
    public function getPaymentProfile()
    {
        return $this->getData(self::PAYMENT_PROFILE_KEY);
    }

    /**
     * Set is need save in vault
     *
     * @param bool $saveInVault
     * @return $this
     */
    public function setSaveInVault(bool $saveInVault)
    {
        $this->setData(self::SAVE_IN_VAULT_KEY, $saveInVault);

        return $this;
    }

    /**
     * Return is need save in vault
     *
     * @return bool
     */
    public function getSaveInVault()
    {
        return (bool)$this->getData(self::SAVE_IN_VAULT_KEY);
    }

    /**
     * Create payment information object and
     * return him
     *
     * @return null|PaymentInfoData
     */
    public function createPaymentInfo()
    {
        if ($this->getPaymentInfo() === null) {
            $paymentInfo = $this->paymentInfoDataFactory->create();
            $this->setData(self::PAYMENT_INFO_KEY, $paymentInfo);
        }

        return $this->getPaymentInfo();
    }

    /**
     * Return payment information object
     *
     * @return PaymentInfoData|null
     */
    public function getPaymentInfo()
    {
        return $this->getData(self::PAYMENT_INFO_KEY);
    }
}
