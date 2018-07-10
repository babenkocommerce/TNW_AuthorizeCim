<?php
/**
 * Copyright © 2018 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
namespace TNW\AuthorizeCim\Test\Unit\Gateway\Config;

use TNW\AuthorizeCim\Gateway\Config\CanVoidHandler;
use TNW\AuthorizeCim\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Model\Order\Payment;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

class CanVoidHandlerTest extends \PHPUnit\Framework\TestCase
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
     * @var CanVoidHandler
     */
    private $voidHandler;

    protected function setUp()
    {
        $this->paymentDO = $this->createMock(PaymentDataObjectInterface::class);

        $this->payment = $this->createMock(Payment::class);

        $this->paymentDO->expects(static::once())
            ->method('getPayment')
            ->willReturn($this->payment);

        $this->voidHandler = new CanVoidHandler(
            new SubjectReader()
        );
    }

    public function testHandleNotOrderPayment()
    {
        $subject = [
            'payment' => $this->paymentDO
        ];

        $this->payment->expects(static::once())
            ->method('getAmountPaid')
            ->willReturn(0.00);

        static::assertTrue($this->voidHandler->handle($subject));
    }

    public function testHandleSomeAmoutWasPaid()
    {
        $subject = [
            'payment' => $this->paymentDO
        ];

        $this->payment->expects(static::once())
            ->method('getAmountPaid')
            ->willReturn(1.00);

        static::assertFalse($this->voidHandler->handle($subject));
    }
}
