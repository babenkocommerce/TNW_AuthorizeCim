<?php
/**
 * Copyright © 2018 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */

namespace TNW\AuthorizeCim\Observer;

use Magento\Framework\Event\Observer;
use Magento\Payment\Observer\AbstractDataAssignObserver;

/**
 * Observer for set additional data
 */
class DataAssignObserver extends AbstractDataAssignObserver
{
    /** additional information key */
    const KEY_ADDITIONAL_DATA = 'additional_data';

    /**
     * Set additional information from additional data
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $data = $this->readDataArgument($observer);

        $additionalData = $data->getData(self::KEY_ADDITIONAL_DATA);

        if (is_array($additionalData)) {
            $paymentInfo = $this->readPaymentModelArgument($observer);

            foreach ($additionalData as $key => $value) {
                $paymentInfo->setAdditionalInformation($key, $value);
            }
        }
    }
}
