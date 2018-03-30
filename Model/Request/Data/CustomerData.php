<?php
/**
 * Copyright © 2018 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */

namespace TNW\AuthorizeCim\Model\Request\Data;

use Magento\Framework\DataObject;

/**
 * Data by customer for request
 */
class CustomerData extends DataObject
{
    /** magento customer email field key */
    const CUSTOMER_EMAIL_KEY = 'customer_email';

    /** magento customer id field key */
    const CUSTOMER_ID_KEY = 'customer_id';

    /** customer profile id field key */
    const CUSTOMER_PROFILE_ID_KEY = 'customer_profile_id';

    /**
     * Set magento customer email
     *
     * @param string $customerEmail
     * @return $this
     */
    public function setCustomerEmail($customerEmail)
    {
        $this->setData(self::CUSTOMER_EMAIL_KEY, $customerEmail);

        return $this;
    }

    /**
     * Return magento customer email
     *
     * @return null|string
     */
    public function getCustomerEmail()
    {
        return $this->getData(self::CUSTOMER_EMAIL_KEY);
    }

    /**
     * Set magento customer entity id
     *
     * @param string|null $customerId
     * @return $this
     */
    public function setCustomerId($customerId)
    {
        $this->setData(self::CUSTOMER_ID_KEY, $customerId);

        return $this;
    }

    /**
     * Return magento customer entity id
     *
     * @return null|string
     */
    public function getCustomerId()
    {
        return $this->getData(self::CUSTOMER_ID_KEY);
    }

    /**
     * Set customer profile ID
     *
     * @param string $customerProfileId
     * @return $this
     */
    public function setCustomerProfileId($customerProfileId)
    {
        $this->setData(self::CUSTOMER_PROFILE_ID_KEY, $customerProfileId);

        return $this;
    }

    /**
     * Return customer profile ID
     *
     * @return null|string
     */
    public function getCustomerProfileId()
    {
        return $this->getData(self::CUSTOMER_PROFILE_ID_KEY);
    }
}
