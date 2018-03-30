<?php
/**
 * Copyright © 2017 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */

namespace TNW\AuthorizeCim\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;
use TNW\AuthorizeCim\Gateway\Helper\SubjectReader;
use TNW\AuthorizeCim\Model\Request\Data\AddressDataFactory;

/**
 * Class for build request address data
 */
class AddressDataBuilder implements BuilderInterface
{
    /** key for build address data */
    const ADDRESS_BUILD_KEY = 'address_data';

    /**
     * @var SubjectReader
     */
    protected $subjectReader;

    /**
     * @var AddressDataFactory
     */
    private $addressDataFactory;

    /**
     * @param SubjectReader $subjectReader
     * @param AddressDataFactory $addressDataFactory
     */
    public function __construct(
        SubjectReader $subjectReader,
        AddressDataFactory $addressDataFactory
    ) {
        $this->subjectReader = $subjectReader;
        $this->addressDataFactory = $addressDataFactory;
    }

    /**
     * Build address data
     *
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {
        $paymentDataObject = $this->subjectReader->readPayment($buildSubject);
        $order = $paymentDataObject->getOrder();
        $billingAddress = $order->getBillingAddress();
        $shippingAddress = $order->getShippingAddress();
        $addressDataObj = $this->addressDataFactory->create();
        $addressDataObj->createBillingAddress()->copyDataFromBillingAddress($billingAddress);

        if ($shippingAddress) {
            $addressDataObj->createShippingAddress()->copyDataFromShippingAddress($shippingAddress);
        }

        return [
            self::ADDRESS_BUILD_KEY => $addressDataObj
        ];
    }
}
