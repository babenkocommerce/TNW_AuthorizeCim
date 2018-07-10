<?php
/**
 * Copyright © 2018 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
namespace TNW\AuthorizeCim\Test\Unit\Gateway\Response;

use TNW\AuthorizeCim\Gateway\Config\Config;
use TNW\AuthorizeCim\Gateway\Helper\SubjectReader;
use TNW\AuthorizeCim\Gateway\Response\VaultDetailsHandler;
use Magento\Payment\Gateway\Data\PaymentDataObject;
use Magento\Sales\Api\Data\OrderPaymentExtensionInterface;
use Magento\Sales\Api\Data\OrderPaymentExtensionInterfaceFactory;
use Magento\Sales\Model\Order\Payment;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Model\CreditCardTokenFactory;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * VaultDetailsHandler Test
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class VaultDetailsHandlerTest extends \PHPUnit\Framework\TestCase
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
     * @var CreditCardTokenFactory|MockObject
     */
    private $paymentTokenFactory;

    /**
     * @var PaymentTokenInterface|MockObject
     */
    protected $paymentToken;

    /**
     * @var \Magento\Sales\Api\Data\OrderPaymentExtension|MockObject
     */
    private $paymentExtension;

    /**
     * @var \Magento\Sales\Api\Data\OrderPaymentExtensionInterfaceFactory|MockObject
     */
    private $paymentExtensionFactory;

    /**
     * @var Config|MockObject
     */
    private $config;

    /**
     * @var VaultDetailsHandler
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
            ->setMethods(['getAdditionalInformation'])
            ->getMock();

        $this->payment->method('getAdditionalInformation')
            ->willReturnMap(
                [
                    ['cc_last4', '0001'],
                    ['cc_exp_month', '12'],
                    ['cc_exp_year', '18'],
                    ['cc_type', 'Visa'],
                    ['is_active_payment_token_enabler', 1],
                ]
            );

        $this->paymentDO->expects($this->once())
            ->method('getPayment')
            ->willReturn($this->payment);

        $this->paymentToken = $this->createMock(PaymentTokenInterface::class);

        $this->paymentTokenFactory = $this->getMockBuilder(CreditCardTokenFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->paymentTokenFactory->expects(self::once())
            ->method('create')
            ->willReturn($this->paymentToken);

        $this->paymentExtension = $this->getMockBuilder(OrderPaymentExtensionInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['setVaultPaymentToken', 'getVaultPaymentToken'])
            ->getMock();

        $this->paymentExtensionFactory = $this->getMockBuilder(OrderPaymentExtensionInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->paymentExtensionFactory->expects(self::once())
            ->method('create')
            ->willReturn($this->paymentExtension);

        $mapperArray = [
            'visa' => 'VI',
        ];

        $this->config = $this->getMockBuilder(Config::class)
            ->setMethods(['getCctypesMapper'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->config->expects(self::once())
            ->method('getCctypesMapper')
            ->willReturn($mapperArray);

        $this->paymentExtension->expects(self::once())
            ->method('getVaultPaymentToken')
            ->willReturn($this->paymentToken);

        $this->handler = new VaultDetailsHandler(
            $this->paymentTokenFactory,
            $this->paymentExtensionFactory,
            $this->config,
            new SubjectReader()
        );
    }

    /**
     * @covers \TNW\AuthorizeCim\Gateway\Response\VaultDetailsHandler::handle
     */
    public function testHandle()
    {
        $subject = [
            'payment' => $this->paymentDO
        ];

        $object = new \net\authorize\api\contract\v1\CreateCustomerProfileResponse;
        $object->setCustomerProfileId('profile_id');
        $object->setCustomerPaymentProfileIdList(['payment_profile_id']);

        $response = [
            'object' => $object
        ];

        $this->paymentToken->expects(static::once())
            ->method('setGatewayToken')
            ->with('profile_id/payment_profile_id')
            ->willReturnSelf();

        $this->paymentToken->expects(static::once())
            ->method('setExpiresAt')
            ->with('2019-01-01 00:00:00')
            ->willReturnSelf();

        $this->paymentToken->expects(static::once())
            ->method('setTokenDetails')
            ->with('{"type":"VI","maskedCC":"0001","expirationDate":"12\/18"}');

        $this->paymentExtension->expects(self::once())
            ->method('setVaultPaymentToken')
            ->with($this->paymentToken);

        $this->handler->handle($subject, $response);
        $this->assertSame($this->paymentToken, $this->payment->getExtensionAttributes()->getVaultPaymentToken());
    }
}
