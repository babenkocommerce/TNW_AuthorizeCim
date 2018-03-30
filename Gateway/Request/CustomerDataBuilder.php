<?php
/**
 * Copyright © 2018 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */

namespace TNW\AuthorizeCim\Gateway\Request;

use Magento\Customer\Model\Session;
use Magento\Payment\Gateway\Request\BuilderInterface;
use TNW\AuthorizeCim\Gateway\Helper\SubjectReader;
use TNW\AuthorizeCim\Model\Request\Data\CustomerDataFactory;

/**
 * Class for build request customer data
 */
class CustomerDataBuilder implements BuilderInterface
{
    /** key for build customer data */
    const CUSTOMER_BUILD_KEY = 'customer_data';

    /**
     * @var SubjectReader
     */
    protected $subjectReader;

    /**
     * @var Session
     */
    protected $session;
    /**
     * @var CustomerDataFactory
     */
    private $customerDataFactory;

    /**
     * @param SubjectReader $subjectReader
     * @param Session $session
     * @param CustomerDataFactory $customerDataFactory
     */
    public function __construct(
        SubjectReader $subjectReader,
        Session $session,
        CustomerDataFactory $customerDataFactory
    ) {
        $this->subjectReader = $subjectReader;
        $this->session = $session;
        $this->customerDataFactory = $customerDataFactory;
    }

    /**
     * Build customer data
     *
     * @param array $subject
     * @return array
     */
    public function build(array $subject)
    {
        $paymentDataObject = $this->subjectReader->readPayment($subject);
        $order = $paymentDataObject->getOrder();
        $email = $order->getBillingAddress()->getEmail();
        $customerId = $this->session->getCustomerId() ? : null;
        $customerDataObj = $this->customerDataFactory->create();
        $customerDataObj->setCustomerEmail($email)
            ->setCustomerId($customerId);

        return [
            self::CUSTOMER_BUILD_KEY => $customerDataObj
        ];
    }
}
