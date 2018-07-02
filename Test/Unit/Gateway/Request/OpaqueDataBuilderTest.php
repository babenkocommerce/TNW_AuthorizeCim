<?php
/**
 * Copyright © 2018 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
namespace TNW\AuthorizeCim\Test\Unit\Gateway\Request;

use TNW\AuthorizeCim\Gateway\Helper\SubjectReader;
use TNW\AuthorizeCim\Gateway\Request\OpaqueDataBuilder;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Model\Order\Payment;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

class OpaqueDataBuilderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var PaymentDataObjectInterface|MockObject
     */
    private $paymentDO;

    /**
     * @var Payment|MockObject
     */
    private $payment;

    /**
     * @var OpaqueDataBuilder
     */
    private $builder;

    public function setUp()
    {
        $this->paymentDO = $this->createMock(PaymentDataObjectInterface::class);

        $this->payment = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAdditionalInformation'])
            ->getMock();

        $this->paymentDO->method('getPayment')
            ->willReturn($this->payment);

        $this->builder = new OpaqueDataBuilder(new SubjectReader());
    }

    /**
     * @covers \TNW\AuthorizeCim\Gateway\Request\OpaqueDataBuilder::build()
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
     * @covers \TNW\AuthorizeCim\Gateway\Request\OpaqueDataBuilder::build()
     */
    public function testBuild()
    {
        $expected = [
            'transaction_request' => [
                'payment' => [
                    'opaque_data' => [
                        'data_descriptor' => 'test_descriptor',
                        'data_value' => 'test_value'
                    ]
                ]
            ]
        ];

        $buildSubject = [
            'payment' => $this->paymentDO,
        ];

        $this->payment
            ->method('getAdditionalInformation')
            ->willReturnMap([
                ['opaqueDescriptor', 'test_descriptor'],
                ['opaqueValue', 'test_value'],
            ]);

        self::assertEquals($expected, $this->builder->build($buildSubject));
    }
}
