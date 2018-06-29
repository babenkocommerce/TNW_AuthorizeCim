<?php
/**
 * Copyright © 2018 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
namespace TNW\AuthorizeCim\Test\Unit\Gateway\Request;

use TNW\AuthorizeCim\Gateway\Helper\SubjectReader;
use TNW\AuthorizeCim\Gateway\Request\ProfileDataBuilder;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Api\Data\OrderPaymentExtension;
use Magento\Sales\Model\Order\Payment;
use Magento\Vault\Model\PaymentToken;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

class ProfileDataBuilderTest extends \PHPUnit\Framework\TestCase
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
     * @var ProfileDataBuilder
     */
    private $builder;

    public function setUp()
    {
        $this->paymentDO = $this->createMock(PaymentDataObjectInterface::class);

        $this->payment = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->paymentDO->method('getPayment')
            ->willReturn($this->payment);

        $this->builder = new ProfileDataBuilder(new SubjectReader());
    }

    /**
     * @covers TokenDataBuilder::build
     */
    public function testBuild()
    {
        $expected = [
            'transaction_request' => [
                'profile' => [
                    'customer_profile_id' => 'customer_test_id',
                    'payment_profile' => [
                        'payment_profile_id' => 'payment_test_id'
                    ]
                ]
            ]
        ];

        $buildSubject = [
            'payment' => $this->paymentDO,
        ];

        $paymentExtension = $this->getMockBuilder(OrderPaymentExtension::class)
            ->setMethods(['getVaultPaymentToken'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $paymentToken = $this->getMockBuilder(PaymentToken::class)
            ->disableOriginalConstructor()
            ->getMock();

        $paymentExtension->method('getVaultPaymentToken')
            ->willReturn($paymentToken);

        $this->payment->method('getExtensionAttributes')
            ->willReturn($paymentExtension);

        $paymentToken->method('getGatewayToken')
            ->willReturn('customer_test_id/payment_test_id');

        self::assertEquals($expected, $this->builder->build($buildSubject));
    }
}
