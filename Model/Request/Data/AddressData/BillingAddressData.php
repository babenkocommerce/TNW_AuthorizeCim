<?php
/**
 * Copyright © 2018 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */

namespace TNW\AuthorizeCim\Model\Request\Data\AddressData;

use Magento\Framework\DataObject;
use Magento\Payment\Gateway\Data\AddressAdapterInterface;

/**
 * Contain billing address request data
 */
class BillingAddressData extends DataObject
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
     * Return billing address first name
     *
     * @return null|string
     */
    public function getFirstName()
    {
        return $this->getData(self::FIRST_NAME_KEY);
    }

    /**
     * Set billing address first name
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
     * Return billing address last name
     *
     * @return null|string
     */
    public function getLastName()
    {
        return $this->getData(self::LAST_NAME_KEY);
    }

    /**
     * Set billing address last name
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
     * Return billing address company
     *
     * @return null|string
     */
    public function getCompany()
    {
        return $this->getData(self::COMPANY_KEY);
    }

    /**
     * Set billing address company
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
     * Return address from billing address
     *
     * @return null|string
     */
    public function getAddress()
    {
        return $this->getData(self::ADDRESS_KEY);
    }

    /**
     * Set address to billing address
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
     * Return billing address city
     *
     * @return null|string
     */
    public function getCity()
    {
        return $this->getData(self::CITY_KEY);
    }

    /**
     * Set billing address city
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
     * Return billing address state
     *
     * @return null|string
     */
    public function getState()
    {
        return $this->getData(self::STATE_KEY);
    }

    /**
     * Set billing address state
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
     * Return billing address ZIP code
     *
     * @return null|string
     */
    public function getZip()
    {
        return $this->getData(self::ZIP_KEY);
    }

    /**
     * Set billing address ZIP code
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
     * Return billing address country
     *
     * @return null|string
     */
    public function getCountry()
    {
        return $this->getData(self::COUNTRY_KEY);
    }

    /**
     * Set billing address country
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
     * Get billing address phone number
     *
     * @return null|string
     */
    public function getPhoneNumber()
    {
        return $this->getData(self::PHONE_NUMBER_KEY);
    }

    /**
     * Set billing address phone number
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
     * Set billing address data to request data
     *
     * @param AddressAdapterInterface $billingAddress
     * @return $this
     */
    public function copyDataFromBillingAddress(AddressAdapterInterface $billingAddress)
    {
        $this->setFirstName($billingAddress->getFirstname())
            ->setLastName($billingAddress->getLastname())
            ->setCompany($billingAddress->getCompany())
            ->setAddress($billingAddress->getStreetLine1())
            ->setCity($billingAddress->getCity())
            ->setState($billingAddress->getRegionCode())
            ->setZip($billingAddress->getPostcode())
            ->setCountry($billingAddress->getCountryId())
            ->setPhoneNumber($billingAddress->getTelephone());

        return $this;
    }
}
