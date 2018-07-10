<?php
/**
 * Copyright © 2018 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
namespace TNW\AuthorizeCim\Test\Unit\Block;

use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManager;
use TNW\AuthorizeCim\Block\Form;
use TNW\AuthorizeCim\Gateway\Config\Config as GatewayConfig;
use TNW\AuthorizeCim\Model\Ui\ConfigProvider;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Payment\Helper\Data;
use Magento\Vault\Model\VaultPaymentInterface;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Class FormTest
 */
class FormTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var GatewayConfig|MockObject
     */
    private $gatewayConfig;

    /**
     * @var Data|MockObject
     */
    private $paymentDataHelper;

    /**
     * @var StoreManager|MockObject
     */
    private $storeManager;

    /**
     * @var StoreManager|MockObject
     */
    private $store;

    /**
     * @var Form
     */
    private $block;

    protected function setUp()
    {
        $this->gatewayConfig = $this->getMockBuilder(GatewayConfig::class)
            ->disableOriginalConstructor()
            ->setMethods(['isCcvEnabled'])
            ->getMock();

        $this->paymentDataHelper = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->setMethods(['getMethodInstance'])
            ->getMock();

        $this->storeManager = $this->getMockBuilder(StoreManager::class)
            ->disableOriginalConstructor()
            ->setMethods(['getStore'])
            ->getMock();

        $this->store = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMock();

        $this->storeManager
            ->method('getStore')
            ->willReturn($this->store);

        $managerHelper = new ObjectManager($this);
        $this->block = $managerHelper->getObject(Form::class, [
            'config' => $this->gatewayConfig,
            'helper' => $this->paymentDataHelper,
        ]);

        $managerHelper->setBackwardCompatibleProperty($this->block, '_storeManager', $this->storeManager);
    }

    public function testUseCcv()
    {
        $this->gatewayConfig->expects(static::once())
            ->method('isCcvEnabled')
            ->willReturn(true);

        self::assertTrue($this->block->useCcv());
    }

    public function testIsVaultEnabled()
    {
        $storeId = 1;

        $this->store
            ->method('getId')
            ->willReturn($storeId);

        $vaultPayment = $this->getMockForAbstractClass(VaultPaymentInterface::class);
        $this->paymentDataHelper->method('getMethodInstance')
            ->with(ConfigProvider::VAULT_CODE)
            ->willReturn($vaultPayment);

        $vaultPayment->method('isActive')
            ->with($storeId)
            ->willReturn(true);

        self::assertTrue($this->block->isVaultEnabled());
    }
}
