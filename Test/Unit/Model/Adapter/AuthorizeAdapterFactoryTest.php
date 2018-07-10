<?php
/**
 * Copyright © 2018 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
namespace TNW\AuthorizeCim\Test\Unit\Model\Adapter;

use Magento\Framework\ObjectManagerInterface;
use TNW\AuthorizeCim\Gateway\Config\Config;
use TNW\AuthorizeCim\Model\Adapter\AuthorizeAdapter;
use TNW\AuthorizeCim\Model\Adapter\AuthorizeAdapterFactory;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * AuthorizeAdapterFactory Test
 */
class AuthorizeAdapterFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Config|MockObject
     */
    private $config;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    private $objectManager;

    /**
     * @var AuthorizeAdapterFactory
     */
    private $adapterFactory;

    protected function setUp()
    {
        $this->config = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->setMethods(['getApiLoginId', 'getTransactionKey', 'isSandboxMode'])
            ->getMock();

        $this->objectManager = $this->createMock(ObjectManagerInterface::class);

        $this->adapterFactory = new AuthorizeAdapterFactory(
            $this->objectManager,
            $this->config
        );
    }

    /**
     * @covers \TNW\AuthorizeCim\Model\Adapter\AuthorizeAdapterFactory::create()
     */
    public function testCreate()
    {
        $expected = $this->createMock(AuthorizeAdapter::class);

        $this->objectManager
            ->method('create')
            ->with(
                AuthorizeAdapter::class,
                [
                    'apiLoginId' => 'api_login_id',
                    'transactionKey' => 'transaction_key',
                    'sandboxMode' => 'is_sandbox_mode',
                ]
            )
            ->willReturn($expected);

        $this->config
            ->method('getApiLoginId')
            ->with(5)
            ->willReturn('api_login_id');

        $this->config
            ->method('getTransactionKey')
            ->with(5)
            ->willReturn('transaction_key');

        $this->config
            ->method('isSandboxMode')
            ->with(5)
            ->willReturn('is_sandbox_mode');

        self::assertEquals($expected, $this->adapterFactory->create(5));
    }
}
