<?php
/**
 * Copyright © 2017 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */

namespace TNW\AuthorizeCim\Model\Authorizenet;

use Magento\Framework\DataObject;

class Payment extends DataObject
{
    const PROFILE_ID = 'profile_id';

    /**
     * @return string
     */
    public function getProfileId()
    {
        return $this->getData(self::PROFILE_ID);
    }

    /**
     * @param string $profileId
     * @return $this
     */
    public function setProfileId($profileId)
    {
        $this->setData(self::PROFILE_ID, $profileId);
        return $this;
    }
}
