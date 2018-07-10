<?php
/**
 * Copyright © 2018 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
namespace TNW\AuthorizeCim\Test\Unit\Gateway\Request;

use TNW\AuthorizeCim\Gateway\Request\CardholderDataBuilder;
use TNW\AuthorizeCim\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Model\Order\Payment;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Test CaptureDataBuilder
 */
class CardholderDataBuilderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CardholderDataBuilder
     */
    private $builder;

    /**
     * @var Payment|MockObject
     */
    private $payment;

    /**
     * @var PaymentDataObjectInterface|MockObject
     */
    private $paymentDO;

    protected function setUp()
    {
        $this->paymentDO = $this->createMock(PaymentDataObjectInterface::class);
        $this->payment = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAdditionalInformation'])
            ->getMock();

        $this->paymentDO->method('getPayment')
            ->willReturn($this->payment);

        $this->builder = new CardholderDataBuilder(new SubjectReader());
    }

    /**
     *
     */
    public function testBuildWithEmpty()
    {
        $buildSubject = [
            'payment' => $this->paymentDO
        ];

        $this->payment->method('getAdditionalInformation')
            ->willReturnMap([
                ['ECIFlag', null],
                ['CAVV', 'key'],
            ]);

        self::assertEquals([], $this->builder->build($buildSubject));
    }

    public function testBuild()
    {
        $expected = [
            'transaction_request' => [
                'cardholder_authentication' => [
                    'authentication_indicator' => 'ai',
                    'cardholder_authentication_value' => 'cav',
                ]
            ]
        ];

        $buildSubject = [
            'payment' => $this->paymentDO
        ];

        $this->payment->method('getAdditionalInformation')
            ->willReturnMap([
                ['ECIFlag', 'ai'],
                ['CAVV', 'cav'],
            ]);

        self::assertEquals($expected, $this->builder->build($buildSubject));
    }
}
