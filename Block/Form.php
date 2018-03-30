<?php
/**
 * Copyright © 2017 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */

namespace TNW\AuthorizeCim\Block;

use Magento\Framework\View\Element\Template\Context;
use Magento\Payment\Block\Form\Cc;
use Magento\Payment\Helper\Data as Helper;
use Magento\Payment\Model\Config as PaymentConfig;
use TNW\AuthorizeCim\Gateway\Config\Config;
use TNW\AuthorizeCim\Model\Ui\ConfigProvider;

class Form extends Cc
{
    /** @var Config */
    protected $_config;

    /** @var Helper */
    protected $_helper;

    public function __construct(
        Context $context,
        PaymentConfig $paymentConfig,
        Config $config,
        Helper $helper,
        array $data = []
    ) {
        parent::__construct($context, $paymentConfig, $data);
        $this->_config = $config;
        $this->_helper = $helper;
    }

    /** @return bool */
    public function useCcv()
    {
        return $this->_config->isCcvEnabled();

    }

    /** @return bool */
    public function isVaultEnabled()
    {
        $storeId = $this->_storeManager->getStore()->getId();
        $vaultPayment = $this->getVaultPayment();
        return $vaultPayment->isActive($storeId);
    }

    /** @return \Magento\Vault\Model\VaultPaymentInterface */
    private function getVaultPayment()
    {
        return $this->_helper->getMethodInstance(ConfigProvider::CC_VAULT_CODE);
    }
}
