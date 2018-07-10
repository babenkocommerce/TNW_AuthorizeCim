<?php
/**
 * Copyright © 2018 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
namespace TNW\AuthorizeCim\Test\Unit\Gateway\Response;

use Magento\Sales\Api\Data\OrderPaymentInterface;
use TNW\AuthorizeCim\Gateway\Response\CardDetailsHandler;
use TNW\AuthorizeCim\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Data\PaymentDataObject;
use Magento\Sales\Model\Order\Payment;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Test CardDetailsHandler
 */
class CardDetailsHandlerTest extends \PHPUnit\Framework\TestCase
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
     * @var CardDetailsHandler
     */
    private $cardHandler;

    protected function setUp()
    {
        $this->paymentDO = $this->getMockBuilder(PaymentDataObject::class)
            ->setMethods(['getPayment'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->payment = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'setCcLast4',
                'setCcExpMonth',
                'setCcExpYear',
                'setCcType',
                'setAdditionalInformation',
            ])
            ->getMock();

        $this->paymentDO->expects(static::once())
            ->method('getPayment')
            ->willReturn($this->payment);

        $this->cardHandler = new CardDetailsHandler(new SubjectReader());
    }

    /**
     * @covers \TNW\AuthorizeCim\Gateway\Response\CardDetailsHandler::handle()
     */
    public function testHandle()
    {
        $subject = [
            'payment' => $this->paymentDO
        ];

        $transaction = new \net\authorize\api\contract\v1\TransactionResponseType;
        $transaction->setAccountNumber('XXXX0015');
        $transaction->setAccountType('Mastercard');

        $object = new \net\authorize\api\contract\v1\CreateTransactionResponse;
        $object->setTransactionResponse($transaction);

        $response = [
            'object' => $object
        ];

        $this->payment->expects(static::exactly(3))
            ->method('setAdditionalInformation')
            ->withConsecutive(
                [CardDetailsHandler::CARD_LAST4, '0015'],
                [CardDetailsHandler::CARD_NUMBER, 'xxxx-0015'],
                [OrderPaymentInterface::CC_TYPE, 'Mastercard']
            );

        $this->payment->expects(static::once())
            ->method('setCcLast4')
            ->with('0015');

        $this->payment->expects(static::once())
            ->method('setCcType')
            ->with('Mastercard');

        $this->cardHandler->handle($subject, $response);
    }
}
