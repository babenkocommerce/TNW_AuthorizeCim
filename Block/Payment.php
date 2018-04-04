<?php
/**
 * Copyright © 2018 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */

namespace TNW\AuthorizeCim\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use TNW\AuthorizeCim\Model\Ui\ConfigProvider;

class Payment extends Template
{
    /**
     * @var ConfigProvider
     */
    private $config;

    /**
     * Payment constructor.
     * @param Context $context
     * @param ConfigProvider $configProvider
     * @param array $data
     */
    public function __construct(
        Context $context,
        ConfigProvider $configProvider,
        array $data = []
    ) {
        parent::__construct($context);
        $this->config = $configProvider;
    }

    /** @return string */
    public function getPaymentConfig()
    {
        $payment = $this->config->getConfig()['payment'];

        $config = $payment[$this->getCode()];
        $config['code'] = $this->getCode();

        return json_encode($config, JSON_UNESCAPED_SLASHES);
    }

    /** @return string */
    public function getCode()
    {
        return ConfigProvider::CODE;
    }
}
