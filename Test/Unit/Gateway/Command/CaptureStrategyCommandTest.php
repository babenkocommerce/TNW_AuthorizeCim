<?php
/**
 * Copyright © 2018 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
namespace TNW\AuthorizeCim\Test\Unit\Gateway\Command;

use TNW\AuthorizeCim\Gateway\Command\CaptureStrategyCommand;
use TNW\AuthorizeCim\Gateway\Helper\SubjectReader;
use TNW\AuthorizeCim\Model\Adapter\AuthorizeAdapter;
use TNW\AuthorizeCim\Model\Adapter\AuthorizeAdapterFactory;
use Magento\Framework\Api\Search\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Payment\Gateway\Command\GatewayCommand;
use Magento\Payment\Gateway\Data\OrderAdapterInterface;
use Magento\Payment\Gateway\Data\PaymentDataObject;
use Magento\Sales\Api\TransactionRepositoryInterface;
use Magento\Sales\Model\Order\Payment;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Test CaptureStrategyCommand
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CaptureStrategyCommandTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CommandPoolInterface|MockObject
     */
    private $commandPool;

    /**
     * @var TransactionRepositoryInterface|MockObject
     */
    private $transactionRepository;

    /**
     * @var SearchCriteriaBuilder|MockObject
     */
    private $searchCriteriaBuilder;

    /**
     * @var PaymentDataObject|MockObject
     */
    private $paymentDO;

    /**
     * @var Payment|MockObject
     */
    private $payment;

    /**
     * @var GatewayCommand|MockObject
     */
    private $command;

    /**
     * @var AuthorizeAdapter|MockObject
     */
    private $authorizeAdapter;

    /**
     * @var CaptureStrategyCommand
     */
    private $strategyCommand;

    protected function setUp()
    {
        $this->commandPool = $this->getMockBuilder(CommandPoolInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['get', '__wakeup'])
            ->getMock();

        $this->initCommandMock();
        $this->initTransactionRepositoryMock();
        $this->initSearchCriteriaBuilderMock();

        $this->payment = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId', 'getAuthorizationTransaction', 'getAdditionalInformation'])
            ->getMock();

        $this->paymentDO = $this->getMockBuilder(PaymentDataObject::class)
            ->setMethods(['getPayment', 'getOrder'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->paymentDO->method('getPayment')
            ->willReturn($this->payment);

        $order = $this->getMockBuilder(OrderAdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->paymentDO->method('getOrder')
            ->willReturn($order);

        $this->authorizeAdapter = $this->getMockBuilder(AuthorizeAdapter::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var AuthorizeAdapterFactory|MockObject $adapterFactory */
        $adapterFactory = $this->getMockBuilder(AuthorizeAdapterFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $adapterFactory->method('create')
            ->willReturn($this->authorizeAdapter);

        /** @var \Psr\Log\LoggerInterface|MockObject $logger */
        $logger = $this->createMock(\Psr\Log\LoggerInterface::class);

        $this->strategyCommand = new CaptureStrategyCommand(
            $this->searchCriteriaBuilder,
            $this->transactionRepository,
            new SubjectReader(),
            $this->commandPool,
            $logger
        );
    }

    /**
     * Creates mock for gateway command object
     */
    private function initCommandMock()
    {
        $this->command = $this->getMockBuilder(GatewayCommand::class)
            ->disableOriginalConstructor()
            ->setMethods(['execute'])
            ->getMock();
    }

    /**
     * Create mock for transaction repository
     */
    private function initTransactionRepositoryMock()
    {
        $this->transactionRepository = $this->getMockBuilder(TransactionRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getList', 'getTotalCount', 'delete', 'get', 'save', 'create'])
            ->getMock();
    }

    /**
     * Create mock for search criteria object
     */
    private function initSearchCriteriaBuilderMock()
    {
        $this->searchCriteriaBuilder = $this->getMockBuilder(SearchCriteriaBuilder::class)
            ->disableOriginalConstructor()
            ->setMethods(['addFilter', 'create'])
            ->getMock();
    }

    public function testSaleExecute()
    {
        $commandSubject = [
            'payment' => $this->paymentDO
        ];

        $this->payment->method('getAuthorizationTransaction')
            ->willReturn(false);

        $this->payment->method('getId')
            ->willReturn(1);

        $this->payment->method('getAdditionalInformation')
            ->with('is_active_payment_token_enabler')
            ->willReturn(null);

        $this->buildSearchCriteria();

        $this->transactionRepository->method('getTotalCount')
            ->willReturn(0);

        $this->commandPool->expects(static::once())
            ->method('get')
            ->with(CaptureStrategyCommand::SALE)
            ->willReturn($this->command);

        $this->command->method('execute')
            ->with($commandSubject)
            ->willReturn([]);

        $this->strategyCommand->execute($commandSubject);
    }

    public function testCaptureExecute()
    {
        $commandSubject = [
            'payment' => $this->paymentDO
        ];

        $this->payment->method('getAuthorizationTransaction')
            ->willReturn(true);

        $this->payment->method('getId')
            ->willReturn(1);

        $this->payment->method('getAdditionalInformation')
            ->with('is_active_payment_token_enabler')
            ->willReturn(true);

        $this->buildSearchCriteria();

        $this->transactionRepository->method('getTotalCount')
            ->willReturn(0);

        $this->commandPool->expects(static::at(0))
            ->method('get')
            ->with(CaptureStrategyCommand::CAPTURE)
            ->willReturn($this->command);

        $this->commandPool->expects(static::at(1))
            ->method('get')
            ->with(CaptureStrategyCommand::CUSTOMER)
            ->willReturn($this->command);

        $this->command->method('execute')
            ->with($commandSubject)
            ->willReturn([]);

        $this->strategyCommand->execute($commandSubject);
    }

    /**
     * Builds search criteria
     */
    private function buildSearchCriteria()
    {
        $searchCriteria = new SearchCriteria();
        $this->searchCriteriaBuilder->expects(self::exactly(2))
            ->method('addFilter')
            ->willReturnSelf();
        $this->searchCriteriaBuilder->method('create')
            ->willReturn($searchCriteria);

        $this->transactionRepository->method('getList')
            ->with($searchCriteria)
            ->willReturnSelf();
    }
}
