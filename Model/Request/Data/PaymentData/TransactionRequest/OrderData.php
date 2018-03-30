<?php
/**
 * Copyright © 2018 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */

namespace TNW\AuthorizeCim\Model\Request\Data\PaymentData\TransactionRequest;

use Magento\Framework\DataObject;

/**
 * Contains order data
 */
class OrderData extends DataObject
{
    /** Order invoice number key */
    const INVOICE_NUMBER_KEY = 'invoice_number';

    /**
     * Set invoice number
     *
     * @param string $invoiceNumber
     * @return $this
     */
    public function setInvoiceNumber(string $invoiceNumber)
    {
        $this->setData(self::INVOICE_NUMBER_KEY, $invoiceNumber);

        return $this;
    }

    /**
     * Return invoice number
     *
     * @return string|null
     */
    public function getInvoiceNumber()
    {
        return $this->getData(self::INVOICE_NUMBER_KEY);
    }
}
