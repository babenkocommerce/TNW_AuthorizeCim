<?php
/**
 * Copyright © 2018 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */

namespace TNW\AuthorizeCim\Gateway\Http\Client;

use Magento\Framework\DataObjectFactory;
use Magento\Framework\Exception\PaymentException;
use Magento\Payment\Model\Method\Logger;
use Psr\Log\LoggerInterface;
use TNW\AuthorizeCim\Gateway\Request\PaymentDataBuilder;
use TNW\AuthorizeCim\Model\Adapter\AuthorizeAdapter;
use TNW\AuthorizeCim\Model\Request\Data\PaymentData;
use TNW\AuthorizeCim\Model\Request\Request\CustomerProfile as CustomerProfileRequest;
use TNW\AuthorizeCim\Model\Request\Request\PaymentProfile as PaymentProfileRequest;
use TNW\AuthorizeCim\Gateway\Request\CustomerDataBuilder;
use TNW\AuthorizeCim\Model\Request\Data\CustomerData;
use Magento\Payment\Model\CcConfig;
use TNW\AuthorizeCim\Model\Request\Data\PaymentData\TransactionRequest\TransactionTypesResource;
use TNW\AuthorizeCim\Model\Request\Request\Transaction;

/**
 * Create payment and customer profile in Authorize CIM
 */
class AuthorizeTransaction extends AbstractTransaction
{
    /**
     * @var CustomerProfileRequest
     */
    private $customerProfileRequest;
    /**
     * @var PaymentProfileRequest
     */
    private $paymentProfileRequest;
    /**
     * @var CcConfig
     */
    private $ccConfig;
    /**
     * @var Transaction
     */
    private $transaction;

    /**
     * @param CcConfig $ccConfig
     * @param CustomerProfileRequest $customerProfileRequest
     * @param PaymentProfileRequest $paymentProfileRequest
     * @param Transaction $transaction
     * @param DataObjectFactory $dataObjectFactory
     * @param LoggerInterface $logger
     * @param Logger $customLogger
     */
    public function __construct(
        CcConfig $ccConfig,
        CustomerProfileRequest $customerProfileRequest,
        PaymentProfileRequest $paymentProfileRequest,
        Transaction $transaction,
        DataObjectFactory $dataObjectFactory,
        LoggerInterface $logger,
        Logger $customLogger
    ) {
        $this->ccConfig = $ccConfig;
        $this->customerProfileRequest = $customerProfileRequest;
        $this->paymentProfileRequest = $paymentProfileRequest;
        $this->transaction = $transaction;
        parent::__construct($dataObjectFactory, $logger, $customLogger);
    }


    /**
     * Process authorize credit card.
     *
     * @param array $data
     * @return array|\Magento\Framework\DataObject
     */
    protected function process(array $data)
    {
//        $customerProfile = $this->customerProfileRequest->getCustomerProfile($data);
//        $customerProfileId = null;
//        /** @var CustomerData $customer */
//        $customer = $data[CustomerDataBuilder::CUSTOMER_BUILD_KEY];
//        //Process get customer profile ID
//        if ($customerProfile !== null) {
//            $customerProfileId = $customerProfile['profile']['customerProfileId'];
//        } else {
//            $createProfile = $this->customerProfileRequest->createCustomerProfile($data);
//            $customerProfileId = $createProfile['customerProfileId'];
//        }
//
//        $customer->setCustomerProfileId($customerProfileId);
//        $paymentProfileId = $this->getCustomerPaymentProfileId($data);
//
//        if (!$paymentProfileId) {
//            $paymentProfileId = $this->paymentProfileRequest
//                ->createPaymentProfile($data)['customerPaymentProfileId'];
//        }
//
//        /** @var PaymentData $payment */
//        $payment = $data[PaymentDataBuilder::PAYMENT_BUILD_KEY];
//        $payment->getTransactionRequest()
//            ->setTransactionType(TransactionTypesResource::TYPE_AUTH_CAPTURE)
//            ->setCustomerProfileId($customerProfileId)
//            ->setCustomerPaymentProfileId($paymentProfileId);
        /** @var PaymentData $payment */
        $payment = $data[PaymentDataBuilder::PAYMENT_BUILD_KEY];
        $payment->getTransactionRequest()->setTransactionType(TransactionTypesResource::TYPE_AUTH_ONLY);
        $result = $this->transaction->beginTransaction($data);

        return $this->createDataObject($result);
    }

    /**
     * Search payment profile ID
     *
     * @param array $data
     * @return null|string payment profile id
     * @throws PaymentException
     */
    private function getCustomerPaymentProfileId(array $data)
    {
        $result = null;
        /** @var PaymentData $paymentData */
        $paymentData = $data[PaymentDataBuilder::PAYMENT_BUILD_KEY];
        $ccTypes = $this->ccConfig->getCcAvailableTypes();
        $paymentCcType = $paymentData->getPaymentInfo()->getCcType();
        $ccStringType = $ccTypes[$paymentCcType];
        $last4 = $paymentData->getPaymentInfo()->getLast4();
        $paymentProfileList = $this->paymentProfileRequest
            ->getCustomerPaymentProfileList($data);

        if (isset($paymentProfileList['paymentProfiles'])) {
            $paymentProfileIds = [];

            foreach ($paymentProfileList['paymentProfiles'] as $item) {
                $card = $item['payment']['creditCard'];

                if ($card['cardNumber'] === $last4 &&
                    $card['cardType'] === $ccStringType
                ) {
                    $paymentProfileIds[] = $item['customerPaymentProfileId'];
                    $result = $item['customerPaymentProfileId'];
                }
            }

            if (count($paymentProfileIds) > 1) {
                throw new PaymentException(__('Many cards founded'));
            }
        }

        return $result;
    }
}
