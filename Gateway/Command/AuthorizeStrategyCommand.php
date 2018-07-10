<?php
/**
 * Copyright © 2017 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
namespace TNW\AuthorizeCim\Gateway\Command;

use TNW\AuthorizeCim\Gateway\Helper\SubjectReader;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Payment\Gateway\Command;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Payment\Gateway\CommandInterface;
use Magento\Payment\Gateway\Helper\ContextHelper;
use Magento\Sales\Api\TransactionRepositoryInterface;

class AuthorizeStrategyCommand implements CommandInterface
{
    /**
     * Stripe authorize command
     */
    const AUTHORIZE = 'authorize';

    /**
     * Stripe customer command
     */
    const CUSTOMER = 'customer';

    /**
     * @var CommandPoolInterface
     */
    private $commandPool;

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * Constructor.
     * @param CommandPoolInterface $commandPool
     * @param SubjectReader $subjectReader
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        CommandPoolInterface $commandPool,
        SubjectReader $subjectReader,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->commandPool = $commandPool;
        $this->subjectReader = $subjectReader;
        $this->logger = $logger;
    }

    /**
     * @param array $commandSubject
     * @return Command\ResultInterface|null|void
     * @throws Command\CommandException
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute(array $commandSubject)
    {
        /** @var \Magento\Payment\Gateway\Data\PaymentDataObjectInterface $paymentDO */
        $paymentDO = $this->subjectReader->readPayment($commandSubject);
        $payment = $paymentDO->getPayment();
        ContextHelper::assertOrderPayment($payment);

        $this->commandPool->get(self::AUTHORIZE)->execute($commandSubject);

        if ($payment->getAdditionalInformation('is_active_payment_token_enabler')) {
            try {
                $this->commandPool->get(self::CUSTOMER)->execute($commandSubject);
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
            }
        }
    }
}
