<?php
/**
 * Copyright © 2018 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
namespace TNW\AuthorizeCim\Test\Unit\Gateway\Request;

use TNW\AuthorizeCim\Gateway\Helper\SubjectReader;
use TNW\AuthorizeCim\Gateway\Request\VaultDataBuilder;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Model\Order\Payment;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

class VaultDataBuilderTest extends \PHPUnit\Framework\TestCase
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
     * @var VaultDataBuilder
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

        $this->builder = new VaultDataBuilder(new SubjectReader());
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
     * @covers VaultDataBuilder::build()
     */
    public function testBuild()
    {
        $expected = [
            'transaction_request' => [
                'profile' => [
                    'create_profile' => true
                ]
            ]
        ];

        $buildSubject = [
            'payment' => $this->paymentDO,
        ];

        $this->payment->method('getAdditionalInformation')
            ->with('is_active_payment_token_enabler')
            ->willReturn(1);

        self::assertEquals($expected, $this->builder->build($buildSubject));
    }
}
