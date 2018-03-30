<?php
/**
 * Copyright © 2018 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */

namespace TNW\AuthorizeCim\Model\Request\Data\PaymentData\PaymentProfileData;

use Magento\Framework\DataObject;

/**
 * Contains data descriptor and payment token.
 */
class OpaqueData extends DataObject
{
    /** Specifies how the request should be processed  */
    const STANDARD_DATA_DESCRIPTOR = 'COMMON.ACCEPT.INAPP.PAYMENT';

    /** Data descriptor key */
    const DATA_DESCRIPTOR_KEY = 'data_descriptor';

    /** Data value key */
    const DATA_VALUE_KEY = 'data_value';

    /**
     * Set opaque descriptor
     *
     * @param string $descriptor
     * @return $this
     */
    public function setDataDescriptor($descriptor = self::STANDARD_DATA_DESCRIPTOR)
    {
        $this->setData(self::DATA_DESCRIPTOR_KEY, $descriptor);

        return $this;
    }

    /**
     * Return opaque descriptor
     *
     * @return string|null
     */
    public function getDataDescriptor()
    {
        return $this->getData(self::DATA_DESCRIPTOR_KEY);
    }

    /**
     * Set base64 encoded token that contains encrypted payment data.
     *
     * @param string $dataValue
     * @return $this
     */
    public function setDataValue($dataValue)
    {
        $this->setData(self::DATA_VALUE_KEY, $dataValue);

        return $this;
    }

    /**
     * Return data value(token)
     *
     * @return string|null
     */
    public function getDataValue()
    {
        return $this->getData(self::DATA_VALUE_KEY);
    }
}
