<?php
/**
 * Copyright © 2017 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */

namespace TNW\AuthorizeCim\Gateway\Command;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Payment\Gateway\CommandInterface;
use Magento\Payment\Gateway\Helper\ContextHelper;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Api\Data\TransactionInterface;
use Magento\Sales\Api\TransactionRepositoryInterface;
use TNW\AuthorizeCim\Gateway\Helper\SubjectReader;

class CaptureStrategyCommand implements CommandInterface
{
    const SALE = 'sale';
    const CAPTURE = 'settlement';

    /** @var SearchCriteriaBuilder */
    private $_searchCriteriaBuilder;

    /** @var TransactionRepositoryInterface */
    private $_transactionRepository;

    /** @var FilterBuilder */
    private $_filterBuilder;

    /** @var SubjectReader */
    private $_subjectReader;

    /** @var CommandPoolInterface */
    private $_commandPool;

    /**
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param TransactionRepositoryInterface $transactionRepository
     * @param FilterBuilder $filterBuilder
     * @param SubjectReader $subjectReader
     * @param CommandPoolInterface $commandPool
     */
    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        TransactionRepositoryInterface $transactionRepository,
        FilterBuilder $filterBuilder,
        SubjectReader $subjectReader,
        CommandPoolInterface $commandPool
    ) {
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->_transactionRepository = $transactionRepository;
        $this->_filterBuilder = $filterBuilder;
        $this->_subjectReader = $subjectReader;
        $this->_commandPool = $commandPool;
    }

    /**
     * @param array $commandSubject
     * @return void
     */
    public function execute(array $commandSubject)
    {
        $paymentDataObject = $this->_subjectReader->readPayment($commandSubject);
        $paymentInfo = $paymentDataObject->getPayment();
        ContextHelper::assertOrderPayment($paymentInfo);

        $command = $this->getCommand($paymentInfo);
        $this->_commandPool->get($command)->execute($commandSubject);
    }

    /**
     * @param OrderPaymentInterface $payment
     * @return string
     */
    private function getCommand(OrderPaymentInterface $payment)
    {
        $existsCapture = $this->isExistsCaptureTransaction($payment);
        if (!$payment->getAuthorizationTransaction() && !$existsCapture) {
            return self::SALE;
        }

        if (!$existsCapture) {
            return self::CAPTURE;
        }
    }

    /**
     * @param OrderPaymentInterface $payment
     * @return bool
     */
    private function isExistsCaptureTransaction(OrderPaymentInterface $payment)
    {
        $this->_searchCriteriaBuilder->addFilters(
            [
                $this->_filterBuilder
                    ->setField('payment_id')
                    ->setValue($payment->getId())
                    ->create()
            ]
        );

        $this->_searchCriteriaBuilder->addFilters(
            [
                $this->_filterBuilder
                    ->setField('txn_type')
                    ->setValue(TransactionInterface::TYPE_CAPTURE)
                    ->create()
            ]
        );

        $searchCriteria = $this->_searchCriteriaBuilder->create();

        $count = $this->_transactionRepository->getList($searchCriteria)->getTotalCount();
        return (boolean)$count;
    }
}
