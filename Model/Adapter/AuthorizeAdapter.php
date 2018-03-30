<?php
/**
 * Copyright © 2018 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */

namespace TNW\AuthorizeCim\Model\Adapter;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Exception\PaymentException;
use Magento\Framework\Webapi\Exception;
use Pmclain\Authnet\CustomerProfile;
use Pmclain\Authnet\MerchantAuthentication;
use Pmclain\Authnet\Request\CreateCustomerPaymentProfile;
use Pmclain\Authnet\Request\CreateCustomerPaymentProfileFactory;
use Pmclain\Authnet\Request\CreateCustomerProfile;
use Pmclain\Authnet\Request\CreateCustomerProfileFactory;
use Pmclain\Authnet\Request\UpdateCustomerProfile;
use Pmclain\Authnet\Request\UpdateCustomerProfileFactory;
use Pmclain\Authnet\Request\CreateTransaction;
use Pmclain\Authnet\Request\CreateTransactionFactory;
use Pmclain\Authnet\TransactionRequest;
use Pmclain\Authnet\ValidationMode;
use Pmclain\Authnet\ValidationModeFactory;
use TNW\AuthorizeCim\Gateway\Config\Config;
use TNW\AuthorizeCim\Gateway\Request\AddressDataBuilder;
use TNW\AuthorizeCim\Gateway\Request\CustomerDataBuilder;
use TNW\AuthorizeCim\Gateway\Request\PaymentDataBuilder;
use TNW\AuthorizeCim\Model\Authorizenet\Payment;

class AuthorizeAdapter
{
    const ERROR_CODE_DUPLICATE = 'E00039';

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var MerchantAuthentication
     */
    protected $merchantAuth;

    /**
     * @var CreateCustomerProfileFactory
     */
    protected $createCustomerProfileFactory;

    /**
     * @var ValidationModeFactory
     */
    protected $validationModeFactory;

    /**
     * @var DataObjectFactory
     */
    protected $dataObjectFactory;

    /**
     * @var Payment
     */
    protected $paymentProfile;

    /**
     * @var CreateTransactionFactory
     */
    protected $createTransactionFactory;

    /**
     * @var CreateCustomerPaymentProfileFactory
     */
    protected $createPaymentProfileFactory;

    /**
     * @var CustomerDataBuilder
     */
    private $customerDataBuilder;
    /**
     * @var UpdateCustomerProfileFactory
     */
    private $updateCustomerProfileFactory;

    /**
     * @param CustomerRepositoryInterface $customerRepository
     * @param Config $config
     * @param MerchantAuthentication $merchantAuthentication
     * @param CreateCustomerProfileFactory $createCustomerProfileFactory
     * @param UpdateCustomerProfileFactory $updateCustomerProfileFactory
     * @param ValidationModeFactory $validationModeFactory
     * @param DataObjectFactory $dataObjectFactory
     * @param Payment $paymentProfile
     * @param CreateTransactionFactory $createTransactionFactory
     * @param CreateCustomerPaymentProfileFactory $createPaymentProfileFactory
     * @param CustomerDataBuilder $customerDataBuilder
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        Config $config,
        MerchantAuthentication $merchantAuthentication,
        CreateCustomerProfileFactory $createCustomerProfileFactory,
        UpdateCustomerProfileFactory $updateCustomerProfileFactory,
        ValidationModeFactory $validationModeFactory,
        DataObjectFactory $dataObjectFactory,
        Payment $paymentProfile,
        CreateTransactionFactory $createTransactionFactory,
        CreateCustomerPaymentProfileFactory $createPaymentProfileFactory,
        CustomerDataBuilder $customerDataBuilder
    ) {
        $this->customerRepository = $customerRepository;
        $this->config = $config;
        $this->merchantAuth = $merchantAuthentication;
        $this->createCustomerProfileFactory = $createCustomerProfileFactory;
        $this->validationModeFactory = $validationModeFactory;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->paymentProfile = $paymentProfile;
        $this->createTransactionFactory = $createTransactionFactory;
        $this->createPaymentProfileFactory = $createPaymentProfileFactory;
        $this->initMerchantAuthentication();
        $this->customerDataBuilder = $customerDataBuilder;
        $this->updateCustomerProfileFactory = $updateCustomerProfileFactory;
    }

    /**
     * @return $this
     */
    protected function initMerchantAuthentication()
    {
        $this->merchantAuth->setLoginId($this->config->getApiLoginId());
        $this->merchantAuth->setTransactionKey($this->config->getTransactionKey());

        return $this;
    }

    /**
     * @param TransactionRequest $transaction
     * @return array
     */
    public function refund($transaction)
    {
        return $this->submitTransactionRequest($transaction);
    }

    /**
     * @param TransactionRequest $transaction
     * @return array
     */
    protected function submitTransactionRequest($transaction)
    {
        /**
         * @var CreateTransaction $createTransaction
         */
        $createTransaction = $this->createTransactionFactory->create(['sandbox' => $this->getIsSandbox()]);
        $createTransaction->setMerchantAuthentication($this->merchantAuth);
        $createTransaction->setTransactionRequest($transaction);

        return $this->createDataObject($createTransaction->submit());
    }

    /**
     * @return bool
     */
    protected function getIsSandbox()
    {
        return $this->config->isTest();
    }

    /**
     * @param array $data
     * @return array|\Magento\Framework\DataObject
     */
    protected function createDataObject($data)
    {
        $convert = false;
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->createDataObject($value);
            }
            if (!is_numeric($key)) {
                $convert = true;
            }
        }
        return $convert ? $this->dataObjectFactory->create(['data' => $data]) : $data;
    }

    /**
     * @param TransactionRequest $transaction
     * @return array
     */
    public function void($transaction)
    {
        return $this->submitTransactionRequest($transaction);
    }

    /**
     * @param TransactionRequest $transaction
     * @return array
     */
    public function submitForSettlement($transaction)
    {
        return $this->submitTransactionRequest($transaction);
    }

    /**
     * @param array $data
     * @return array
     */
    public function saleForNewProfile(array $data)
    {
        $data[PaymentDataBuilderOld::PAYMENT]->setBillTo($data[AddressDataBuilder::BILL_TO]);
        $data[CustomerDataBuilder::CUSTOMER_PROFILE]->setPaymentProfile($data[PaymentDataBuilderOld::PAYMENT]);
        $customerProfileResponse = $this->createCustomerProfile($data[CustomerDataBuilder::CUSTOMER_PROFILE]);

        $data[PaymentDataBuilderOld::PAYMENT_PROFILE] = $customerProfileResponse->getData('customerPaymentProfileIdList')[0];
        $this->paymentProfile->setProfileId($data[PaymentDataBuilderOld::PAYMENT_PROFILE]);
        $data[CustomerDataBuilder::PROFILE_ID] = $customerProfileResponse->getData('customerProfileId');

        if ($data[CustomerDataBuilder::CUSTOMER_ID] !== null) {
            //$this->saveCard($data);
        }

        return $this->sale($data);
    }

    /**
     * @param CustomerProfile $customerProfile
     * @return \Magento\Framework\DataObject
     * @throws PaymentException
     */
    protected function createCustomerProfile($customerProfile)
    {
        /**
         * @var CreateCustomerProfile $customerProfileRequest
         */
        $customerProfileRequest = $this->createCustomerProfileFactory->create(['sandbox' => $this->getIsSandbox()]);
        $customerProfileRequest->setProfile($customerProfile);
        $customerProfileRequest->setMerchantAuthentication($this->merchantAuth);
        $customerProfileRequest->setValidationMode($this->getValidationMode());

        $result = $this->createDataObject($customerProfileRequest->submit());

        if ($result->getMessages()->getData('resultCode') === 'Error') {
            if ($result->getMessages()->getMessage()[0]->getCode() !== self::ERROR_CODE_DUPLICATE) {
                throw new PaymentException(
                    __('Profile could not be created.')
                );
            }
        }

        return $result;
    }

    /**
     * Update customer ID in Authorize CIM profile
     *
     * @param int $userId
     * @param string $userEmail
     * @param int $profileId
     * @return $this
     */
    public function updateAuthorizeCimProfile($userId, $userEmail, $profileId)
    {
        /**
         * @var UpdateCustomerProfile $updateProfileRequest
         */
        $updateProfileRequest = $this->updateCustomerProfileFactory->create(['sandbox' => $this->getIsSandbox()]);
        $updateProfileRequest->setMerchantAuthentication($this->merchantAuth);
        $customerData = $this->customerDataBuilder->generateCustomerProfileData($userId, $userEmail, $profileId);
        $updateProfileRequest->setProfile($customerData[CustomerDataBuilder::CUSTOMER_PROFILE])
            ->setCustomerId($userId)
            ->setProfileId($profileId);
        $result = $updateProfileRequest->submit();

        //throw new PaymentException(__('Error update authorize CIM profile'));


        return $this;
    }

    /**
     * @return ValidationMode
     */
    protected function getValidationMode()
    {
        /**
         * @var ValidationMode $validationMode
         */
        $validationMode = $this->validationModeFactory->create();

        try {
            $validationMode->set($this->config->getValidationMode());
            return $validationMode;
        } catch (\Pmclain\Authnet\Exception\InputException $e) {
            return $validationMode;
        }
    }

//    /**
//     * Save card data
//     *
//     * @param array $paymentData
//     * @return $this
//     */
//    private function saveCard($paymentData)
//    {
//        $customerId = $paymentData[CustomerDataBuilder::CUSTOMER_ID] ?: null;
//        $email = $paymentData[CustomerDataBuilder::CUSTOMER_PROFILE]->toArray()['email'];
//        $profileId = $paymentData[CustomerDataBuilder::PROFILE_ID];
//        $paymentId = $paymentData[PaymentDataBuilderOld::PAYMENT_PROFILE];
//        $paymentInfo = $paymentData[PaymentDataBuilderOld::PAYMENT_INFO];
//        $card = $this->card->setCustomerId($customerId)
//            ->setCustomerEmail($email)
//            ->setProfileId($profileId)
//            ->setPaymentId($paymentId)
//            ->setEncodedAdditionalInfo($paymentInfo)
//            ->updateHash();
//
//
//        //TODO test update
//        //$this->updateAuthorizeCimProfile($customerId, $email, $profileId);
//
//        $this->cardRepository->save($card);
//
//        return $this;
//    }

    /**
     * @param array $data
     * @return array
     */
    protected function sale(array $data)
    {
        $data[PaymentDataBuilderOld::TRANSACTION_REQUEST]->setCustomerProfileId($data[CustomerDataBuilder::PROFILE_ID]);
        $data[PaymentDataBuilderOld::TRANSACTION_REQUEST]->setPaymentProfileId($data[PaymentDataBuilderOld::PAYMENT_PROFILE]);

        if ($data[PaymentDataBuilderOld::CAPTURE]) {
            $data[PaymentDataBuilderOld::TRANSACTION_REQUEST]->setTransactionType(TransactionRequest\TransactionType::TYPE_AUTH_CAPTURE);
        }

        return $this->submitTransactionRequest($data[PaymentDataBuilder::TRANSACTION_REQUEST]);
    }

    /**
     * @param array $data
     * @return array
     */
    public function saleForExistingProfile(array $data)
    {
        $data[PaymentDataBuilderOld::PAYMENT]->setBillTo($data[AddressDataBuilder::BILL_TO]);
        $customerPaymentProfileResponse = $this->createCustomerPaymentProfile($data);
        //TODO: if this has an error it should throw an exception. invalid authnet
        // profile_id really mess this up
        $data[PaymentDataBuilderOld::PAYMENT_PROFILE] = $customerPaymentProfileResponse->getData('customerPaymentProfileId');
        $this->paymentProfile->setProfileId($data[PaymentDataBuilderOld::PAYMENT_PROFILE]);
        //$this->saveCard($data);

        return $this->sale($data);
    }

    /**
     * @param array $data
     * @return \Magento\Framework\DataObject
     * @throws PaymentException
     */
    protected function createCustomerPaymentProfile(array $data)
    {
        /** @var CreateCustomerPaymentProfile $createPaymentProfileRequest */
        $createPaymentProfileRequest = $this->createPaymentProfileFactory->create(['sandbox' => $this->getIsSandbox()]);
        $createPaymentProfileRequest->setMerchantAuthentication($this->merchantAuth);
        $createPaymentProfileRequest->setCustomerProfileId($data[CustomerDataBuilder::PROFILE_ID]);
        $createPaymentProfileRequest->setPaymentProfile($data[PaymentDataBuilderOld::PAYMENT]);
        $createPaymentProfileRequest->setValidationMode($this->getValidationMode());

        $result = $this->createDataObject($createPaymentProfileRequest->submit());

        if ($result->getMessages()->getData('resultCode') === 'Error') {
            if ($result->getMessages()->getMessage()[0]->getCode() !== self::ERROR_CODE_DUPLICATE) {
                throw new PaymentException(
                    __('Profile could not be created.')
                );
            }
        }

        return $result;
    }

    /**
     * @param array $data
     * @return array
     */
    public function saleForVault(array $data)
    {
        return $this->sale($data);
    }
}
