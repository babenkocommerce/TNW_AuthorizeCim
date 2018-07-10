<?php
/**
 * Copyright © 2018 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
namespace TNW\AuthorizeCim\Test\Unit\Gateway\Request;

use Magento\Payment\Gateway\Data\AddressAdapterInterface;
use Magento\Payment\Gateway\Data\OrderAdapterInterface;
use TNW\AuthorizeCim\Gateway\Helper\SubjectReader;
use TNW\AuthorizeCim\Gateway\Request\CustomerDataBuilder;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Model\Order\Payment;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

class CustomerDataBuilderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var PaymentDataObjectInterface|MockObject
     */
    private $paymentDO;

    /**
     * @var Payment|MockObject
     */
    private $address;

    /**
     * @var OrderAdapterInterface|MockObject
     */
    private $order;

    /**
     * @var CustomerDataBuilder
     */
    private $builder;

    protected function setUp()
    {
        $this->paymentDO = $this->createMock(PaymentDataObjectInterface::class);

        $this->order = $this->getMockBuilder(OrderAdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->paymentDO->method('getOrder')
            ->willReturn($this->order);

        $this->address = $this->getMockBuilder(AddressAdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->order->method('getBillingAddress')
            ->willReturn($this->address);

        $this->builder = new CustomerDataBuilder(new SubjectReader());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testBuildReadPaymentException()
    {
        $buildSubject = [
            'payment' => null,
        ];

        $this->builder->build($buildSubject);
    }

    /**
     *
     */
    public function testBuild()
    {
        $expected = [
            'transaction_request' => [
                'customer' => [
                    'type' => 'individual',
                    'email' => 'john@magento.com'
                ]
            ]
        ];

        $buildSubject = [
            'payment' => $this->paymentDO,
        ];

        $this->address
            ->method('getEmail')
            ->willReturn('john@magento.com');

        self::assertEquals($expected, $this->builder->build($buildSubject));
    }
}
