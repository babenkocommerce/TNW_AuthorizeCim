<?php
/**
 * Copyright © 2017 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */

namespace TNW\AuthorizeCim\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class ValidationMode implements ArrayInterface
{
    public function toOptionArray()
    {
        return [
            [
                'value' => 'none',
                'label' => __('None')
            ],
            [
                'value' => 'testMode',
                'label' => __('Test Mode')
            ],
            [
                'value' => 'liveMode',
                'label' => __('Live Mode')
            ],
        ];
    }
}
