<?php
/**
 * TNW_AuthorizeCim extension
 * NOTICE OF LICENSE
 *
 * This source file is subject to the OSL 3.0 License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/osl-3.0.php
 *
 * @category  Pmclain
 * @package   TNW_AuthorizeCim
 * @copyright Copyright (c) 2017-2018
 * @license   Open Software License (OSL 3.0)
 */

namespace TNW\AuthorizeCim\Gateway\Http\Client;

use TNW\AuthorizeCim\Gateway\Request\PaymentDataBuilder;

class TransactionRefund extends AbstractTransaction
{
    /**
     * @param array $data
     * @return array
     */
    protected function process(array $data)
    {
        return $this->_adapter->refund($data[PaymentDataBuilder::TRANSACTION_REQUEST]);
    }
}
