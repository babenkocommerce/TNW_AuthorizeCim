<?php
/**
 * Copyright © 2018 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
namespace TNW\AuthorizeCim\Model\Source;

use Magento\Payment\Model\Source\Cctype as PaymentCctype;

/**
 * Credit card types source model
 */
class Cctype extends PaymentCctype
{
    /**
     * @inheritdoc
     */
    public function getAllowedTypes()
    {
        return ['VI', 'MC', 'AE', 'DI', 'JCB', 'DN'];
    }
}
