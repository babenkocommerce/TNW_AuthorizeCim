<?php
/**
 * Copyright © 2018 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */

namespace TNW\AuthorizeCim\Gateway\Http\Client;

use Magento\Framework\DataObjectFactory;
use Magento\Payment\Model\Method\Logger;
use Psr\Log\LoggerInterface;
use TNW\AuthorizeCim\Gateway\Request\CaptureDataBuilder;
use TNW\AuthorizeCim\Model\Request\Data\CaptureData;
use TNW\AuthorizeCim\Model\Request\Request\Transaction;
use TNW\AuthorizeCim\Model\Request\Data\PaymentData\TransactionRequest\TransactionTypesResource;

class CaptureTransaction extends AbstractTransaction
{
    /**
     * @var Transaction
     */
    private $transaction;

    /**
     * @param Transaction $transaction
     * @param DataObjectFactory $dataObjectFactory
     * @param LoggerInterface $logger
     * @param Logger $customLogger
     */
    public function __construct(
        Transaction $transaction,
        DataObjectFactory $dataObjectFactory,
        LoggerInterface $logger,
        Logger $customLogger
    ) {
        $this->transaction = $transaction;
        parent::__construct($dataObjectFactory, $logger, $customLogger);
    }


    /**
     * Process capture payment.
     *
     * @param array $data
     * @return array|\Magento\Framework\DataObject
     */
    protected function process(array $data)
    {
        /** @var CaptureData $capture */
        $capture = $data[CaptureDataBuilder::CAPTURE_BUILD_KEY];
        $capture->setTransactionType(TransactionTypesResource::TYPE_PRIOR_AUTH_CAPTURE);
        $result = $this->transaction->captureTransaction($data);

        return $this->createDataObject($result);
    }
}
