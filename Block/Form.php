<?php
/**
 * Copyright © 2017 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
namespace TNW\AuthorizeCim\Block;

use Magento\Framework\View\Element\Template\Context;
use TNW\AuthorizeCim\Block\Form\Cc;
use Magento\Payment\Helper\Data as Helper;
use Magento\Payment\Model\Config as PaymentConfig;
use TNW\AuthorizeCim\Gateway\Config\Config;
use TNW\AuthorizeCim\Model\Ui\ConfigProvider;

class Form extends Cc
{
    /** @var Config */
    private $config;

    /** @var Helper */
    private $helper;

    /**
     * Form constructor.
     * @param Context $context
     * @param PaymentConfig $paymentConfig
     * @param Config $config
     * @param Helper $helper
     * @param array $data
     */
    public function __construct(
        Context $context,
        PaymentConfig $paymentConfig,
        Config $config,
        Helper $helper,
        array $data = []
    ) {
        parent::__construct($context, $paymentConfig, $data);
        $this->config = $config;
        $this->helper = $helper;
    }

    /** @return bool */
    public function useCcv()
    {
        return $this->config->isCcvEnabled();
    }

    /**
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function isVaultEnabled()
    {
        $storeId = $this->_storeManager->getStore()->getId();
        $vaultPayment = $this->getVaultPayment();
        return $vaultPayment->isActive($storeId);
    }

    /**
     * @return \Magento\Payment\Model\MethodInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getVaultPayment()
    {
        return $this->helper->getMethodInstance(ConfigProvider::VAULT_CODE);
    }
}
