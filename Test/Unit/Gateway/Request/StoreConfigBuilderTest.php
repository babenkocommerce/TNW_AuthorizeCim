<?php
/**
 * Copyright © 2018 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
namespace TNW\AuthorizeCim\Test\Unit\Gateway\Request;

use Magento\Payment\Gateway\Data\OrderAdapterInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use TNW\AuthorizeCim\Gateway\Helper\SubjectReader;
use TNW\AuthorizeCim\Gateway\Request\StoreConfigBuilder;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Test StoreDataBuilder
 */
class StoreConfigBuilderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var PaymentDataObjectInterface|MockObject
     */
    private $paymentDO;

    /**
     * @var OrderAdapterInterface|MockObject
     */
    private $order;

    /**
     * @var StoreConfigBuilder
     */
    private $builder;

    public function setUp()
    {
        $this->paymentDO = $this->createMock(PaymentDataObjectInterface::class);

        $this->order = $this->createMock(OrderAdapterInterface::class);

        $this->paymentDO->method('getOrder')
            ->willReturn($this->order);

        $this->builder = new StoreConfigBuilder(new SubjectReader());
    }

    public function testBuild()
    {
        $expected = [
            'store_id' => 4
        ];

        $buildSubject = [
            'payment' => $this->paymentDO,
        ];

        $this->order->method('getStoreId')
            ->willReturn(4);

        self::assertEquals($expected, $this->builder->build($buildSubject));
    }
}
