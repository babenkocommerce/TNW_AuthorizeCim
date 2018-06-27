<?php
/**
 * Copyright © 2018 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
namespace TNW\AuthorizeCim\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;
use TNW\AuthorizeCim\Gateway\Helper\SubjectReader;

/**
 * Class for build request address data
 */
class AddressDataBuilder implements BuilderInterface
{
    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @param SubjectReader $subjectReader
     */
    public function __construct(
        SubjectReader $subjectReader
    ) {
        $this->subjectReader = $subjectReader;
    }

    /**
     * Build address data
     *
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {
        $paymentDO = $this->subjectReader->readPayment($buildSubject);

        $order = $paymentDO->getOrder();
        $result = [];

        $billingAddress = $order->getBillingAddress();
        if ($billingAddress) {
            $result['transaction_request']['bill_to'] = [
                'first_name' => $billingAddress->getFirstname(),
                'last_name' => $billingAddress->getLastname(),
                'company' => $billingAddress->getCompany(),
                'address' => $billingAddress->getStreetLine1(),
                'city' => $billingAddress->getCity(),
                'state' => $billingAddress->getRegionCode(),
                'zip' => $billingAddress->getPostcode(),
                'country' => $billingAddress->getCountryId(),
                'phone_number' => $billingAddress->getTelephone(),
                'email' => $billingAddress->getEmail(),
            ];
        }

        $shippingAddress = $order->getShippingAddress();
        if ($shippingAddress) {
            $result['transaction_request']['ship_to'] = [
                'first_name' => $shippingAddress->getFirstname(),
                'last_name' => $shippingAddress->getLastname(),
                'company' => $shippingAddress->getCompany(),
                'address' => $shippingAddress->getStreetLine1(),
                'city' => $shippingAddress->getCity(),
                'state' => $shippingAddress->getRegionCode(),
                'zip' => $shippingAddress->getPostcode(),
                'country' => $shippingAddress->getCountryId(),
            ];
        }

        return $result;
    }
}
