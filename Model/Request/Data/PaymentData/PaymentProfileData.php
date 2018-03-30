<?php
/**
 * Copyright © 2018 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */

namespace TNW\AuthorizeCim\Model\Request\Data\PaymentData;

use Magento\Framework\DataObject;
use Magento\Framework\Exception\PaymentException;
use TNW\AuthorizeCim\Model\Request\Data\PaymentData\PaymentProfileData\OpaqueData;
use TNW\AuthorizeCim\Model\Request\Data\PaymentData\PaymentProfileData\OpaqueDataFactory;

/**
 * Payment profile data for request
 */
class PaymentProfileData extends DataObject
{
    /** Opaque data key */
    const OPAQUE_KEY = 'opaque';

    /** Is payment profile is default field key */
    const DEFAULT_PAYMENT_PROFILE_KEY = 'default_payment_profile';

    /** Type of customer key */
    const CUSTOMER_TYPE_KEY = 'customer_type';

    /** Individual customer type */
    const CUSTOMER_TYPE_INDIVIDUAL = 'individual';

    /** Business customer type */
    const CUSTOMER_TYPE_BUSINESS = 'business';

    /**
     * @var OpaqueDataFactory
     */
    private $opaqueDataFactory;

    /**
     * @param OpaqueDataFactory $opaqueDataFactory
     * @param array $data
     */
    public function __construct(
        OpaqueDataFactory $opaqueDataFactory,
        array $data = []
    ) {
        $this->opaqueDataFactory = $opaqueDataFactory;
        parent::__construct($data);
    }

    /**
     * Set is payment profile is default
     * When set to true, this field designates the payment profile as the default payment profile.
     * When a default payment profile has been designated, you can use "getCustomerPaymentProfileRequest"
     * with customerProfileId as the only parameter
     *
     * @param bool $isDefault
     * @return $this
     */
    public function setIsDefaultPaymentProfile(bool $isDefault = false)
    {
        $this->setData(self::DEFAULT_PAYMENT_PROFILE_KEY, $isDefault);

        return $this;
    }

    /**
     * Get is payment profile is default
     *
     * @return bool|null
     */
    public function getIsDefaultPaymentProfile()
    {
        return $this->getData(self::DEFAULT_PAYMENT_PROFILE_KEY);
    }

    /**
     * Create opaque data and return him
     *
     * @return null|OpaqueData
     */
    public function createOpaque()
    {
        if ($this->getOpaque() === null) {
            $opaque = $this->opaqueDataFactory->create();
            $this->setData(self::OPAQUE_KEY, $opaque);
        }

        return $this->getOpaque();
    }

    /**
     * Return opaque object
     *
     * @return OpaqueData|null
     */
    public function getOpaque()
    {
        return $this->getData(self::OPAQUE_KEY);
    }

    /**
     * Set customer type.
     * If this field is not submitted in the request,
     * or is submitted with a blank value, the original value will
     * be removed from the profile.
     *
     * @param string $customerType individual or business
     * @return $this
     * @throws PaymentException
     */
    public function setCustomerType($customerType = self::CUSTOMER_TYPE_INDIVIDUAL)
    {
        if (!in_array($customerType, self::getAvailableCustomerTypes(), true)) {
            throw new PaymentException(__('Customer type %1 is not available', $customerType));
        }

        $this->setData(self::CUSTOMER_TYPE_KEY, $customerType);

        return $this;
    }

    /**
     * Return customer type
     *
     * @return string|null
     */
    public function getCustomerType()
    {
        return $this->getData(self::CUSTOMER_TYPE_KEY);
    }

    /**
     * Return available customer types
     *
     * @return array
     */
    public static function getAvailableCustomerTypes()
    {
        return [
            self::CUSTOMER_TYPE_INDIVIDUAL,
            self::CUSTOMER_TYPE_BUSINESS
        ];
    }
}
