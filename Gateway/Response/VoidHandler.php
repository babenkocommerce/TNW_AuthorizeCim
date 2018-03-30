<?php
/**
 * Copyright © 2017 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */

namespace TNW\AuthorizeCim\Gateway\Response;

use Magento\Sales\Model\Order\Payment;

class VoidHandler extends TransactionIdHandler
{
    protected function _setTransactionId(Payment $orderPayment, $transaction)
    {
        return;
    }

    protected function _shouldCloseTransaction()
    {
        return true;
    }

    protected function _shouldCloseParentTransaction(Payment $orderPayment)
    {
        return true;
    }
}
