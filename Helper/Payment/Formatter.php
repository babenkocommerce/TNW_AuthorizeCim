<?php
/**
 * Copyright © 2018 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
namespace TNW\AuthorizeCim\Helper\Payment;

trait Formatter
{
    /**
     * Format Price
     * @param $price
     * @return mixed
     */
    public function formatPrice($price)
    {
        return sprintf('%.2F', $price);
    }
}
