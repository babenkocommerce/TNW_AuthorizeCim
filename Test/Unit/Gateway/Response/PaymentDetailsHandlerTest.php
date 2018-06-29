<?php
/**
 * Copyright © 2018 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
namespace TNW\AuthorizeCim\Test\Unit\Gateway\Response;

use TNW\AuthorizeCim\Gateway\Response\PaymentDetailsHandler;
use TNW\AuthorizeCim\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Data\PaymentDataObject;
use Magento\Sales\Model\Order\Payment;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Class PaymentDetailsHandlerTest
 */
class PaymentDetailsHandlerTest extends \PHPUnit\Framework\TestCase
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
     * @var PaymentDetailsHandler
     */
    private $paymentHandler;

    protected function setUp()
    {
        $this->paymentDO = $this->getMockBuilder(PaymentDataObject::class)
            ->setMethods(['getPayment'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->payment = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'setCcTransId',
                'setLastTransId',
                'setAdditionalInformation',
                'unsAdditionalInformation',
            ])
            ->getMock();

        $this->paymentDO->expects(static::once())
            ->method('getPayment')
            ->willReturn($this->payment);

        $this->paymentHandler = new PaymentDetailsHandler(new SubjectReader());
    }

    /**
     * @covers PaymentDetailsHandler::handle
     */
    public function testHandle()
    {
        $subject = [
            'payment' => $this->paymentDO
        ];

        $transaction = new \net\authorize\api\contract\v1\TransactionResponseType;
        $transaction->setTransId('trans_id');
        $transaction->setAuthCode('auth_code');
        $transaction->setAvsResultCode('avs_result_code');
        $transaction->setCavvResultCode('cavv_result_code');
        $transaction->setCvvResultCode('cvv_result_code');

        $object = new \net\authorize\api\contract\v1\CreateTransactionResponse;
        $object->setTransactionResponse($transaction);

        $response = [
            'object' => $object
        ];

        $this->payment->expects(static::once())
            ->method('setCcTransId')
            ->with('trans_id');

        $this->payment->expects(static::once())
            ->method('setLastTransId')
            ->with('trans_id');

        $this->payment
            ->method('setAdditionalInformation')
            ->withConsecutive(
                ['auth_code', 'auth_code'],
                ['avs_code', 'avs_result_code'],
                ['cavv_code', 'cavv_result_code'],
                ['cvv_code', 'cvv_result_code']
            );

        $this->payment
            ->method('unsAdditionalInformation')
            ->withConsecutive(
                ['opaqueDescriptor'],
                ['opaqueValue']
            );

        $this->paymentHandler->handle($subject, $response);
    }
}
