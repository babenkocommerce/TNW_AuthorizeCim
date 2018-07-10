<?php
/**
 * Copyright © 2018 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
namespace TNW\AuthorizeCim\Test\Unit\Gateway\Http\Client;

use TNW\AuthorizeCim\Gateway\Http\Client\CreateCustomerProfileFromTransaction;
use TNW\AuthorizeCim\Gateway\Helper\DataObject;
use TNW\AuthorizeCim\Model\Adapter\AuthorizeAdapter;
use TNW\AuthorizeCim\Model\Adapter\AuthorizeAdapterFactory;
use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\Payment\Model\Method\Logger;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Psr\Log\LoggerInterface;

/**
 * Test TransactionSale
 */
class CreateCustomerProfileFromTransactionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Logger|MockObject
     */
    private $logger;

    /**
     * @var LoggerInterface|MockObject
     */
    private $criticalLogger;

    /**
     * @var DataObject|MockObject
     */
    private $dataObject;

    /**
     * @var AuthorizeAdapter|MockObject
     */
    private $adapter;

    /**
     * @var CreateCustomerProfileFromTransaction
     */
    private $model;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->criticalLogger = $this->getMockForAbstractClass(LoggerInterface::class);

        $this->logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->adapter = $this->getMockBuilder(AuthorizeAdapter::class)
            ->disableOriginalConstructor()
            ->setMethods(['createCustomerProfileFromTransaction'])
            ->getMock();

        /** @var AuthorizeAdapterFactory|MockObject $adapterFactory */
        $adapterFactory = $this->getMockBuilder(AuthorizeAdapterFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $adapterFactory->method('create')
            ->with('test-store-id')
            ->willReturn($this->adapter);

        $this->dataObject = $this->getMockBuilder(DataObject::class)
            ->disableOriginalConstructor()
            ->setMethods(['populateWithObject'])
            ->getMock();

        $this->model = new CreateCustomerProfileFromTransaction(
            $this->criticalLogger,
            $this->logger,
            $adapterFactory,
            $this->dataObject
        );
    }

    /**
     * Runs test placeRequest method (exception)
     *
     * @return void
     *
     * @expectedException \Magento\Payment\Gateway\Http\ClientException
     * @expectedExceptionMessage Test messages
     */
    public function testPlaceRequestException()
    {
        $this->logger->method('debug')
            ->with([
                'request' => $this->getTransferData(),
                'client' => CreateCustomerProfileFromTransaction::class,
                'response' => []
            ]);

        $this->criticalLogger->method('critical')
            ->with('Test messages');

        $this->adapter->method('createCustomerProfileFromTransaction')
            ->with([
                'test_attribute' => 'test_attribute_value',
            ])
            ->willThrowException(new \Exception('Test messages'));

        /** @var TransferInterface|MockObject $transferObjectMock */
        $transferObjectMock = $this->getTransferObjectMock();

        $this->model->placeRequest($transferObjectMock);
    }

    /**
     * Run test placeRequest method
     *
     * @return void
     * @throws \Magento\Payment\Gateway\Http\ClientException
     */
    public function testPlaceRequestSuccess()
    {
        $response = $this->getResponseObject();

        $this->adapter->method('createCustomerProfileFromTransaction')
            ->with([
                'test_attribute' => 'test_attribute_value',
            ])
            ->willReturn($response);

        $this->logger->method('debug')
            ->with([
                'request' => $this->getTransferData(),
                'client' => CreateCustomerProfileFromTransaction::class,
                'response' => []
            ]);

        $this->dataObject->method('populateWithObject')
            ->with([], $response);

        $actualResult = $this->model->placeRequest($this->getTransferObjectMock());

        $this->assertInternalType('object', $actualResult['object']);
        $this->assertEquals(['object' => $response], $actualResult);
    }

    /**
     * Creates mock object for TransferInterface.
     *
     * @return TransferInterface|MockObject
     */
    private function getTransferObjectMock()
    {
        $transferObjectMock = $this->createMock(TransferInterface::class);
        $transferObjectMock->method('getBody')
            ->willReturn($this->getTransferData());

        return $transferObjectMock;
    }

    /**
     * Creates stub for a response.
     *
     * @return
     */
    private function getResponseObject()
    {
        return new \StdClass;
    }

    /**
     * Creates stub request data.
     *
     * @return array
     */
    private function getTransferData()
    {
        return [
            'store_id' => 'test-store-id',
            'test_attribute' => 'test_attribute_value'
        ];
    }
}
