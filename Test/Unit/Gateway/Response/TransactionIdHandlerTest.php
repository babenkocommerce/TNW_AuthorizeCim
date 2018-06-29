<?php
/**
 * Copyright © 2018 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
namespace TNW\AuthorizeCim\Test\Unit\Gateway\Response;

use TNW\AuthorizeCim\Gateway\Helper\SubjectReader;
use TNW\AuthorizeCim\Gateway\Response\TransactionIdHandler;
use Magento\Payment\Gateway\Data\PaymentDataObject;
use Magento\Sales\Model\Order\Payment;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

class TransactionIdHandlerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var PaymentDataObject|MockObject
     */
    private $paymentDO;

    /**
     * @var Payment|MockObject
     */
    private $payment;

    /**
     * @var TransactionIdHandler
     */
    private $handler;

    protected function setUp()
    {
        $this->paymentDO = $this->getMockBuilder(PaymentDataObject::class)
            ->setMethods(['getPayment'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->payment = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'setTransactionId',
                'setIsTransactionClosed',
                'setShouldCloseParentTransaction',
            ])
            ->getMock();

        $this->paymentDO
            ->method('getPayment')
            ->willReturn($this->payment);

        $this->handler = new TransactionIdHandler(new SubjectReader());
    }

    public function testHandle()
    {
        $handlingSubject = [
            'payment' => $this->paymentDO
        ];

        $transaction = new \net\authorize\api\contract\v1\TransactionResponseType;
        $transaction->setTransId('trans_id');

        $object = new \net\authorize\api\contract\v1\CreateTransactionResponse;
        $object->setTransactionResponse($transaction);

        $response = [
            'object' => $object
        ];

        $this->payment->expects(static::once())
            ->method('setTransactionId')
            ->with('trans_id');

        $this->payment->expects(static::once())
            ->method('setIsTransactionClosed')
            ->with(false);

        $this->payment->expects(static::once())
            ->method('setShouldCloseParentTransaction')
            ->with(false);

        $this->handler->handle($handlingSubject, $response);
    }
}
