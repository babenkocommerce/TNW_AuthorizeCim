<?php
/**
 * Copyright © 2018 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
namespace TNW\AuthorizeCim\Test\Unit\Gateway\Http\Client;

use TNW\AuthorizeCim\Gateway\Http\Client\TransactionCapture;
use TNW\AuthorizeCim\Gateway\Helper\DataObject;
use TNW\AuthorizeCim\Model\Adapter\AuthorizeAdapter;
use TNW\AuthorizeCim\Model\Adapter\AuthorizeAdapterFactory;
use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\Payment\Model\Method\Logger;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Psr\Log\LoggerInterface;

/**
 * Test TransactionCapture
 */
class TransactionCaptureTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var TransactionCapture
     */
    private $model;

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
            ->setMethods(['transaction'])
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

        $this->model = new TransactionCapture(
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
        $transfer = $this->getTransferData();

        $this->logger->method('debug')
            ->with([
                'request' => $transfer,
                'client' => TransactionCapture::class,
                'response' => []
            ]);

        $this->criticalLogger->method('critical')
            ->with('Test messages');

        $this->adapter->method('transaction')
            ->with([
                'transaction_request' => [
                    'test_attribute' => 'test_attribute_value',
                    'transaction_type' => 'priorAuthCaptureTransaction'
                ],
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

        $this->adapter->method('transaction')
            ->with([
                'transaction_request' => [
                    'test_attribute' => 'test_attribute_value',
                    'transaction_type' => 'priorAuthCaptureTransaction'
                ],
            ])
            ->willReturn($response);

        $this->logger->method('debug')
            ->with([
                'request' => $this->getTransferData(),
                'client' => TransactionCapture::class,
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
            'transaction_request' => [
                'test_attribute' => 'test_attribute_value'
            ],
        ];
    }
}
