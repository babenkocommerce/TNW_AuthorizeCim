<?php
/**
 * Copyright © 2018 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */

namespace TNW\AuthorizeCim\Model\Request\Data\AddressData;

use Magento\Framework\DataObject;
use Magento\Payment\Gateway\Data\AddressAdapterInterface;

/**
 * Contain shipping address request data
 */
class ShippingAddressData extends DataObject
{
    /** Customer first name field key*/
    const FIRST_NAME_KEY = 'first_name';

    /** Customer last name field key */
    const LAST_NAME_KEY = 'last_name';

    /** Customer company field key */
    const COMPANY_KEY = 'company';

    /** Customer address field key */
    const ADDRESS_KEY = 'address';

    /** Customer city field key */
    const CITY_KEY = 'city';

    /** Customer state field key */
    const STATE_KEY = 'state';

    /** Customer ZIP field key */
    const ZIP_KEY = 'zip';

    /** Customer country field key */
    const COUNTRY_KEY = 'country';

    /** Customer phone number field key */
    const PHONE_NUMBER_KEY = 'phone_number';

    /**
     * Return shipping address first name
     *
     * @return null|string
     */
    public function getFirstName()
    {
        return $this->getData(self::FIRST_NAME_KEY);
    }

    /**
     * Set shipping address first name
     *
     * @param string $firstName
     * @return $this
     */
    public function setFirstName($firstName)
    {
        $this->setData(self::FIRST_NAME_KEY, $firstName);

        return $this;
    }

    /**
     * Return shipping address last name
     *
     * @return null|string
     */
    public function getLastName()
    {
        return $this->getData(self::LAST_NAME_KEY);
    }

    /**
     * Set shipping address last name
     *
     * @param string $lastName
     * @return $this
     */
    public function setLastName($lastName)
    {
        $this->setData(self::LAST_NAME_KEY, $lastName);

        return $this;
    }

    /**
     * Return shipping address company
     *
     * @return null|string
     */
    public function getCompany()
    {
        return $this->getData(self::COMPANY_KEY);
    }

    /**
     * Set shipping address company
     *
     * @param string $company
     * @return $this
     */
    public function setCompany($company)
    {
        $this->setData(self::COMPANY_KEY, $company);

        return $this;
    }

    /**
     * Return address from shipping address
     *
     * @return null|string
     */
    public function getAddress()
    {
        return $this->getData(self::ADDRESS_KEY);
    }

    /**
     * Set address to shipping address
     *
     * @param string $address
     * @return $this
     */
    public function setAddress($address)
    {
        $this->setData(self::ADDRESS_KEY, $address);

        return $this;
    }

    /**
     * Return shipping address city
     *
     * @return null|string
     */
    public function getCity()
    {
        return $this->getData(self::CITY_KEY);
    }

    /**
     * Set shipping address city
     *
     * @param string $city
     * @return $this
     */
    public function setCity($city)
    {
        $this->setData(self::CITY_KEY, $city);

        return $this;
    }

    /**
     * Return shipping address state
     *
     * @return null|string
     */
    public function getState()
    {
        return $this->getData(self::STATE_KEY);
    }

    /**
     * Set shipping address state
     *
     * @param string $state
     * @return $this
     */
    public function setState($state)
    {
        $this->setData(self::STATE_KEY, $state);

        return $this;
    }

    /**
     * Return shipping address ZIP code
     *
     * @return null|string
     */
    public function getZip()
    {
        return $this->getData(self::ZIP_KEY);
    }

    /**
     * Set shipping address ZIP code
     *
     * @param string $zip
     * @return $this
     */
    public function setZip($zip)
    {
        $this->setData(self::ZIP_KEY, $zip);

        return $this;
    }

    /**
     * Return shipping address country
     *
     * @return null|string
     */
    public function getCountry()
    {
        return $this->getData(self::COUNTRY_KEY);
    }

    /**
     * Set shipping address country
     *
     * @param string $country
     * @return $this
     */
    public function setCountry($country)
    {
        $this->setData(self::COUNTRY_KEY, $country);

        return $this;
    }

    /**
     * Get shipping address phone number
     *
     * @return null|string
     */
    public function getPhoneNumber()
    {
        return $this->getData(self::PHONE_NUMBER_KEY);
    }

    /**
     * Set shipping address phone number
     *
     * @param string $phoneNumber
     * @return $this
     */
    public function setPhoneNumber($phoneNumber)
    {
        $this->setData(self::PHONE_NUMBER_KEY, $phoneNumber);

        return $this;
    }

    /**
     * Set shipping address data to request data
     *
     * @param AddressAdapterInterface $shippingAddress
     * @return $this
     */
    public function copyDataFromShippingAddress(AddressAdapterInterface $shippingAddress)
    {
        $this->setFirstName($shippingAddress->getFirstname())
            ->setLastName($shippingAddress->getLastname())
            ->setCompany($shippingAddress->getCompany())
            ->setAddress($shippingAddress->getStreetLine1())
            ->setCity($shippingAddress->getCity())
            ->setState($shippingAddress->getRegionCode())
            ->setZip($shippingAddress->getPostcode())
            ->setCountry($shippingAddress->getCountryId())
            ->setPhoneNumber($shippingAddress->getTelephone());

        return $this;
    }
}
