<?php
/**
 * Copyright © 2018 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */

namespace TNW\AuthorizeCim\Model\Request\Data;

use Magento\Framework\DataObject;
use TNW\AuthorizeCim\Model\Request\Data\AddressData\BillingAddressData;
use TNW\AuthorizeCim\Model\Request\Data\AddressData\BillingAddressDataFactory;
use TNW\AuthorizeCim\Model\Request\Data\AddressData\ShippingAddressData;
use TNW\AuthorizeCim\Model\Request\Data\AddressData\ShippingAddressDataFactory;

/**
 * Data by address for request
 */
class AddressData extends DataObject
{
    /** billing address data field key */
    const BILLING_ADDRESS_KEY = 'billing_address';

    /** shipping address data field key */
    const SHIPPING_ADDRESS_KEY = 'shipping_address';

    /**
     * @var BillingAddressDataFactory
     */
    private $billingAddressDataFactory;

    /**
     * @var ShippingAddressDataFactory
     */
    private $shippingAddressDataFactory;

    /**
     * @param BillingAddressDataFactory $billingAddressDataFactory
     * @param ShippingAddressDataFactory $shippingAddressDataFactory
     * @param array $data
     */
    public function __construct(
        BillingAddressDataFactory $billingAddressDataFactory,
        ShippingAddressDataFactory $shippingAddressDataFactory,
        array $data = []
    ) {
        $this->billingAddressDataFactory = $billingAddressDataFactory;
        $this->shippingAddressDataFactory = $shippingAddressDataFactory;
        parent::__construct($data);
    }

    /**
     * If billing address is not created create him and return
     *
     * @return null|BillingAddressData
     */
    public function createBillingAddress()
    {
        if ($this->getBillingAddress() === null) {
            $billingAddress = $this->billingAddressDataFactory->create();
            $this->setData(self::BILLING_ADDRESS_KEY, $billingAddress);
        }

        return $this->getBillingAddress();
    }

    /**
     * Return billing address
     *
     * @return null|BillingAddressData
     */
    public function getBillingAddress()
    {
        return $this->getData(self::BILLING_ADDRESS_KEY);
    }

    /**
     * If shipping address is not created create him and return
     *
     * @return null|ShippingAddressData
     */
    public function createShippingAddress()
    {
        if ($this->getShippingAddress() === null) {
            $shippingAddress = $this->shippingAddressDataFactory->create();
            $this->setData(self::SHIPPING_ADDRESS_KEY, $shippingAddress);
        }

        return $this->getShippingAddress();
    }

    /**
     * Return shipping address
     *
     * @return null|ShippingAddressData
     */
    public function getShippingAddress()
    {
        return $this->getData(self::SHIPPING_ADDRESS_KEY);
    }
}
