<?php
/**
 * Copyright © 2018 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
namespace TNW\AuthorizeCim\Model\Config;

class Dom extends \Magento\Framework\Config\Dom
{
    protected function _getMatchedNode($nodePath)
    {
        if (preg_match('/^\/config\/payment_method_list\/payment_method?$/i', $nodePath)) {
            return null;
        }

        return parent::_getMatchedNode($nodePath);
    }
}
